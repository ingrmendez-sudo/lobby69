<?php

namespace App\Http\Controllers\Video;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VideoController extends Controller
{
    const ALBUM_TYPES    = ['public', 'private', 'vip'];
    const MAX_DURATION   = 300;  // 5 minutos en segundos
    const MIN_DURATION   = 30;   // 30 segundos
    const MAX_SIZE_BYTES = 104857600; // 100 MB

    // ── Listado de videos del usuario ──
    public function index()
    {
        $userId = auth()->id();

        $videos = DB::table('videos')
            ->whereRaw('user_id::text = ?', [$userId])
            ->orderBy('album_type')
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->get();

        $grouped = [
            'public'  => $videos->where('album_type', 'public'),
            'private' => $videos->where('album_type', 'private'),
            'vip'     => $videos->where('album_type', 'vip'),
        ];

        $user    = auth()->user();
        $profile = DB::table('profiles')
            ->whereRaw('user_id::text = ?', [$userId])
            ->first();

        return view('videos.index', compact('grouped', 'user', 'profile'));
    }

    // ── Subir video ──
    public function store(Request $request)
    {
        $request->validate([
            'video'      => 'required|file|mimes:mp4,mov,avi,webm|max:102400',
            'album_type' => 'required|in:public,private,vip',
            'caption'    => 'nullable|string|max:200',
        ], [
            'video.required' => 'Selecciona un video.',
            'video.file'     => 'El archivo no es válido.',
            'video.mimes'    => 'Solo se permiten videos MP4, MOV, AVI o WEBM.',
            'video.max'      => 'El video no puede superar 100MB.',
        ]);

        $userId    = auth()->id();
        $albumType = $request->input('album_type', 'public');
        $caption   = $request->input('caption', '');
        $file      = $request->file('video');

        // Verificar tamaño
        if ($file->getSize() > self::MAX_SIZE_BYTES) {
            return back()->with('error', 'El video supera el límite de 100MB.');
        }

        try {
            // Ruta de almacenamiento privado
            $folder   = 'videos/' . $userId;
            $filename = 'video_' . $userId . '_' . time() . '.' . $file->getClientOriginalExtension();
            $filePath = $folder . '/' . $filename;

            // Crear carpeta si no existe

            Storage::disk('private')->makeDirectory($folder);

            Storage::disk('private')->putFileAs($folder, $file, $filename);

            DB::table('videos')->insert([
                'video_uuid'      => (string) Str::uuid(),
                'user_id'         => (string) $userId,
                'album_type'      => $albumType,
                'file_path'       => $filePath,
                'thumbnail_path'  => null,
                'duration_seconds'=> null, // se puede calcular con ffprobe si está disponible
                'file_size_bytes' => $file->getSize(),
                'caption'         => $caption,
                'status'          => 'pending',
                'sort_order'      => 0,
                'views_count'     => 0,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            return back()->with('success', 'Video subido correctamente. Será revisado antes de publicarse.');

        } catch (\Throwable $e) {
            Log::error('VideoController@store: ' . $e->getMessage());
            return back()->with('error', 'Error al subir el video. Intenta de nuevo.');
        }
    }

    // ── Servir video (streaming privado) ──
    public function serve(int $id)
    {
        $userId = auth()->id();

        $video = DB::table('videos')->where('id', $id)->first();

        if (!$video) abort(404);

        // Solo el dueño puede ver videos pendientes o rechazados
        if ($video->status !== 'approved') {
            if ((string)$video->user_id !== (string)$userId) {
                abort(403);
            }
        }

        // Control de acceso por álbum
        $user = auth()->user();
        if ($video->album_type === 'private') {
            $allowed = ['connectors', 'influencer', 'vip_elite', 'vitalicio'];
            if (!in_array($user->membership_type ?? '', $allowed)) {
                abort(403, 'Membresía insuficiente.');
            }
        }
        if ($video->album_type === 'vip') {
            $allowed = ['vip_elite', 'vitalicio'];
            if (!in_array($user->membership_type ?? '', $allowed)) {
                abort(403, 'Membresía insuficiente.');
            }
        }

        if (!Storage::disk('private')->exists($video->file_path)) {
            abort(404, 'Archivo no encontrado.');
        }

        // Incrementar vistas solo en videos aprobados
        if ($video->status === 'approved') {
            DB::table('videos')->where('id', $id)->increment('views_count');
        }

        $ext      = pathinfo($video->file_path, PATHINFO_EXTENSION);
        $mimeMap  = [
            'mp4'  => 'video/mp4',
            'mov'  => 'video/quicktime',
            'avi'  => 'video/x-msvideo',
            'webm' => 'video/webm',
        ];
        $mimeType = $mimeMap[strtolower($ext)] ?? 'video/mp4';

        return response()->stream(function () use ($video) {
            $stream = Storage::disk('private')->readStream($video->file_path);
            fpassthru($stream);
            if (is_resource($stream)) fclose($stream);
        }, 200, [
            'Content-Type'        => $mimeType,
            'Content-Disposition' => 'inline',
            'Cache-Control'       => 'no-store',
            'Accept-Ranges'       => 'bytes',
        ]);
    }

    // ── Eliminar video ──
    public function destroy(int $id)
    {
        $userId = auth()->id();

        $video = DB::table('videos')
            ->where('id', $id)
            ->whereRaw('user_id::text = ?', [$userId])
            ->first();

        if (!$video) abort(404);

        try {
            if (Storage::disk('private')->exists($video->file_path)) {
                Storage::disk('private')->delete($video->file_path);
            }
            if ($video->thumbnail_path && Storage::disk('private')->exists($video->thumbnail_path)) {
                Storage::disk('private')->delete($video->thumbnail_path);
            }

            DB::table('videos')->where('id', $id)->delete();

            return back()->with('success', 'Video eliminado correctamente.');

        } catch (\Throwable $e) {
            Log::error('VideoController@destroy: ' . $e->getMessage());
            return back()->with('error', 'Error al eliminar el video.');
        }
    }
}
