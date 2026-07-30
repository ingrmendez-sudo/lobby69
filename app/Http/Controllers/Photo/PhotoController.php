<?php
namespace App\Http\Controllers\Photo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PhotoController extends Controller
{
    // Límites por álbum (sin límite real, pero controlable)
    const ALBUM_TYPES = ['public', 'private', 'vip'];

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
            'photos.*.image'  => 'Cada archivo debe ser una imagen.',
            'photos.*.mimes'  => 'Solo JPG, PNG o WEBP.',
            'photos.*.max'    => 'Cada imagen máximo 10MB.',
        ]);

        $userId    = auth()->id();
        $albumType = $request->input('album_type', 'public');
        $caption   = $request->input('caption', '');
        $uploaded  = 0;

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
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
            ->with('success', "✅ {$uploaded} foto(s) subidas correctamente. El equipo las revisará pronto.");
    }

    public function setProfilePhoto(Request $request, $id)
    {
        $userId = auth()->id();
        $photo  = DB::table('photos')
            ->whereRaw('id = ?', [$id])
            ->whereRaw('user_id::text = ?', [$userId])
            ->where('status', 'approved')
            ->first();

        if (!$photo) {
            return back()->with('error', 'Foto no encontrada o no aprobada.');
        }

        // Quitar foto de perfil anterior
        DB::table('photos')
            ->whereRaw('user_id::text = ?', [$userId])
            ->update(['is_profile_photo' => false, 'updated_at' => Carbon::now()]);

        // Establecer nueva — NO guardar URL en profiles, se construye dinámicamente
        DB::table('photos')
            ->where('id', $id)
            ->update(['is_profile_photo' => true, 'updated_at' => Carbon::now()]);

        return back()->with('success', '✅ Foto de perfil actualizada.');
    }


    public function destroy($id)
    {
        $userId = auth()->id();
        $photo  = DB::table('photos')
            ->where('id', $id)
            ->whereRaw('user_id::text = ?', [$userId])
            ->first();

        if (!$photo) return back()->with('error', 'Foto no encontrada.');

        // Eliminar archivo
        $fullPath = storage_path('app/private/' . $photo->file_path);
        if (file_exists($fullPath)) unlink($fullPath);

        DB::table('photos')->where('id', $id)->delete();

        return back()->with('success', 'Foto eliminada.');
    }

    public function serveThumb($id)
    {
        $photo = DB::table('photos')
            ->where(function($q) use ($id) {
                $q->whereRaw('photo_uuid::text = ?', [$id])
                  ->orWhereRaw('id::text = ?', [$id]);
            })
            ->first();
        if (!$photo) abort(404);

        if ($photo->thumbnail_path && file_exists(storage_path('app/public/' . $photo->thumbnail_path))) {
            return response()->file(
                storage_path('app/public/' . $photo->thumbnail_path),
                ['Cache-Control' => 'public, max-age=86400',
                 'Content-Type'  => 'image/jpeg']
            );
        }
        // Fallback al serve normal
        return $this->serve($id);
    }

    public function serve($id)
    {
        $userId = (string) auth()->id();

        $photo = DB::table('photos')
            ->where(function($q) use ($id) {
                $q->whereRaw('photo_uuid::text = ?', [$id])
                  ->orWhereRaw('id::text = ?', [$id]);
            })
            ->first();

        if (!$photo) abort(404);
        if ($photo->status !== 'approved') abort(403, 'Foto no disponible.');

        // El dueño siempre puede ver sus propias fotos
        if ((string) $photo->user_id === $userId) {
            return redirect(
                'https://kjhaquimghhejqznleyn.supabase.co/storage/v1/object/public/gallery/' . $photo->file_path
            );
        }

        // Verificar acceso según album_type usando MembershipService
        $canView = match($photo->album_type) {
            'public'  => true,
            'private' => \App\Services\MembershipService::can($userId, 'can_view_private_photos'),
            'vip'     => \App\Services\MembershipService::hasMinLevel($userId, 'vip_elite'),
            default   => false,
        };

        if (!$canView) {
            abort(403, 'Tu membresía no permite ver este contenido.');
        }

        return redirect(
            'https://kjhaquimghhejqznleyn.supabase.co/storage/v1/object/public/gallery/' . $photo->file_path
        );
    }
}













