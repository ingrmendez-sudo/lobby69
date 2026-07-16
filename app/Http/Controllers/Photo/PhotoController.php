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

    public function serve($id)
    {
        $userId = auth()->id();

        $photo = DB::table('photos')
            ->where(function($q) use ($id) {
                $q->whereRaw('photo_uuid::text = ?', [$id])
                  ->orWhereRaw('id::text = ?', [$id]);
            })
            ->first();
        if (!$photo) abort(404);

        // El dueño SIEMPRE puede ver sus propias fotos
        // Comparación como string para evitar fallos de tipo
        if ((string)$photo->user_id === (string)$userId) {
            $url = 'https://kjhaquimghhejqznleyn.supabase.co/storage/v1/object/public/gallery/' . $photo->file_path;
            return redirect($url);
        }






        // Verificar membresía para terceros
        $membershipType = DB::table('users')
            ->whereRaw('id::text = ?', [$userId])
            ->value('membership_type') ?? 'trial';

        // Todos los usuarios autenticados pueden ver fotos públicas aprobadas
        // trial = primer mes gratis, tiene acceso a fotos públicas
        $allMembers = ['trial','trial_verified','explorer','connectors',
                    'influencer','vip_elite','vitalicio','admin'];

        $canView = false;
        switch ($photo->album_type) {
            case 'public':
                $canView = in_array($membershipType, $allMembers)
                        && $photo->status === 'approved';
                break;
            case 'private':
                $canView = in_array($membershipType,
                    ['connectors','influencer','vip_elite','vitalicio','admin'])
                        && $photo->status === 'approved';
                break;
            case 'vip':
                $canView = in_array($membershipType,
                    ['vip_elite','vitalicio','admin'])
                        && $photo->status === 'approved';
                break;
        }

        if (!$canView) abort(403, 'No tienes acceso a esta foto.');

        $url = 'https://kjhaquimghhejqznleyn.supabase.co/storage/v1/object/public/gallery/' . $photo->file_path;
        return redirect($url);
    }
}











