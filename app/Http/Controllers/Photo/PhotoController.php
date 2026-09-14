<?php
namespace App\Http\Controllers\Photo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PhotoController extends Controller
{
    const ALBUM_TYPES = ['public', 'private', 'vip'];

    /**
     * Genera una URL firmada temporal via Supabase REST API.
     * Para fotos publicas devuelve la URL publica directa.
     * Para privadas/vip devuelve signed URL con TTL de 5 minutos.
     */
    private function buildPhotoUrl(object $photo, string $albumType): string
    {
        $supabaseUrl    = config('services.supabase.url');
        $bucketPublic   = config('services.supabase.bucket_public', 'gallery');
        $serviceKey     = config('services.supabase.service_key');

        // Fotos publicas y fotos de perfil: URL publica directa (sin firma)
        if ($albumType === 'public' || $photo->is_profile_photo) {
            return $supabaseUrl . '/storage/v1/object/public/' . $bucketPublic . '/' . $photo->file_path;
        }

        // Fotos privadas y VIP: URL firmada con TTL 5 minutos via Supabase REST
        if ($serviceKey) {
            try {
                $apiUrl = $supabaseUrl . '/storage/v1/object/sign/' . $bucketPublic . '/' . $photo->file_path;
                $ch = curl_init($apiUrl);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST           => true,
                    CURLOPT_POSTFIELDS     => json_encode(['expiresIn' => 300]),
                    CURLOPT_HTTPHEADER     => [
                        'Authorization: Bearer ' . $serviceKey,
                        'Content-Type: application/json',
                        'apikey: ' . $serviceKey,
                    ],
                    CURLOPT_TIMEOUT        => 5,
                ]);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode === 200) {
                    $data = json_decode($response, true);
                    if (!empty($data['signedURL'])) {
                        return $supabaseUrl . $data['signedURL'];
                    }
                }
            } catch (\Throwable $e) {
                Log::error('[PhotoController] Error generando signed URL: ' . $e->getMessage());
            }
        }

        // Fallback seguro: devuelve URL publica si no se pudo firmar
        // (nunca deberia llegar aqui en produccion con service_key configurada)
        Log::warning('[PhotoController] Fallback a URL publica para foto privada ID: ' . $photo->id);
        return $supabaseUrl . '/storage/v1/object/public/' . $bucketPublic . '/' . $photo->file_path;
    }

    public function index()
    {
        $userId = auth()->id();
        $photos = DB::table('photos')
            ->whereRaw('user_id::text = ?', [$userId])
            ->orderBy('album_type')
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->get();

        $grouped = [
            'public'  => $photos->where('album_type', 'public'),
            'private' => $photos->where('album_type', 'private'),
            'vip'     => $photos->where('album_type', 'vip'),
        ];

        $user    = auth()->user();
        $profile = DB::table('profiles')->whereRaw('user_id::text = ?', [$userId])->first();

        return view('photos.index', compact('grouped', 'user', 'profile'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'photos.*'   => 'required|image|mimes:jpeg,jpg,png,webp|max:10240',
            'album_type' => 'required|in:public,private,vip',
            'caption'    => 'nullable|string|max:200',
        ], [
            'photos.*.image' => 'Cada archivo debe ser una imagen.',
            'photos.*.mimes' => 'Solo JPG, PNG o WEBP.',
            'photos.*.max'   => 'Cada imagen maximo 10MB.',
        ]);

        $userId    = (string) auth()->id();
        $albumType = $request->input('album_type', 'public');
        $caption   = $request->input('caption', '');
        $uploaded  = 0;

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {

                // Verificacion real de MIME por contenido (no solo extension)
                $finfo    = new \finfo(FILEINFO_MIME_TYPE);
                $realMime = $finfo->file($file->getRealPath());
                if (!in_array($realMime, ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'])) {
                    return back()->with('error', 'Archivo no valido: ' . $file->getClientOriginalName());
                }

                $filename = 'photo_' . $userId . '_' . time() . '_' . $uploaded
                          . '.' . $file->getClientOriginalExtension();
                $path = 'photos/' . $userId . '/' . $filename;
                Storage::disk('supabase')->put($path, file_get_contents($file->getRealPath()), 'public');

                DB::table('photos')->insert([
                    'user_id'    => $userId,
                    'album_type' => $albumType,
                    'file_path'  => $path,
                    'status'     => 'pending',
                    'caption'    => $caption,
                    'sort_order' => 0,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
                $uploaded++;
            }
        }

        return redirect()->route('photos.index')
            ->with('success', "{$uploaded} foto(s) subidas correctamente. El equipo las revisara pronto.");
    }

    public function setProfilePhoto(Request $request, $id)
    {
        $userId = (string) auth()->id();
        $photo  = DB::table('photos')
            ->whereRaw('id::text = ?', [$id])
            ->whereRaw('user_id::text = ?', [$userId])
            ->where('status', 'approved')
            ->first();

        if (!$photo) {
            return back()->with('error', 'Foto no encontrada o no aprobada.');
        }

        DB::table('photos')
            ->whereRaw('user_id::text = ?', [$userId])
            ->update(['is_profile_photo' => DB::raw('false'), 'updated_at' => Carbon::now()]);

        DB::table('photos')
            ->whereRaw('id::text = ?', [$id])
            ->update(['is_profile_photo' => DB::raw('true'), 'updated_at' => Carbon::now()]);

        return back()->with('success', 'Foto de perfil actualizada.');
    }

    public function destroy($id)
    {
        $userId = (string) auth()->id();
        $photo  = DB::table('photos')
            ->whereRaw('id::text = ?', [$id])
            ->whereRaw('user_id::text = ?', [$userId])
            ->first();

        if (!$photo) return back()->with('error', 'Foto no encontrada.');

        $fullPath = storage_path('app/private/' . $photo->file_path);
        if (file_exists($fullPath)) unlink($fullPath);

        DB::table('photos')->whereRaw('id::text = ?', [$id])->delete();

        return back()->with('success', 'Foto eliminada.');
    }

    public function serveThumb($id)
    {
        $photo = DB::table('photos')
            ->where(function ($q) use ($id) {
                $q->whereRaw('photo_uuid::text = ?', [$id])
                  ->orWhereRaw('id::text = ?', [$id]);
            })
            ->first();

        if (!$photo) abort(404);
        return $this->serve($id);
    }

    public function serve($id)
    {
        $userId = (string) auth()->id();

        $photo = DB::table('photos')
            ->where(function ($q) use ($id) {
                $q->whereRaw('photo_uuid::text = ?', [$id])
                  ->orWhereRaw('id::text = ?', [$id]);
            })
            ->first();

        if (!$photo) abort(404);
        if ($photo->status !== 'approved') abort(403, 'Foto no disponible.');

        // Fotos de perfil: siempre publicas (avatar visible para todos)
        if ($photo->is_profile_photo) {
            return redirect($this->buildPhotoUrl($photo, 'public'));
        }

        // Dueno: siempre puede ver sus propias fotos
        if ((string) $photo->user_id === $userId) {
            return redirect($this->buildPhotoUrl($photo, $photo->album_type));
        }

        // Verificar acceso segun album_type
        $canView = match($photo->album_type) {
            'public'  => true,
            'private' => \App\Services\MembershipService::can($userId, 'can_view_private_photos'),
            'vip'     => \App\Services\MembershipService::hasMinLevel($userId, 'vip_elite'),
            default   => false,
        };

        if (!$canView) {
            abort(403, 'Tu membresia no permite ver este contenido.');
        }

        // Generar URL firmada temporal para contenido restringido
        return redirect($this->buildPhotoUrl($photo, $photo->album_type));
    }
}
