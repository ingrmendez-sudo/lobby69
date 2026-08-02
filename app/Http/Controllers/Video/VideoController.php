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

            // 2. Aplicar faststart DESPUÉS de que el archivo existe en disco
            $fullPath = storage_path('app/private/' . $filePath);
            $tmpPath  = $fullPath . '.tmp.mp4';
            $ffmpeg   = 'ffmpeg';
            $cmd = sprintf('%s -i %s -c copy -movflags +faststart %s -y -loglevel error 2>&1',
                $ffmpeg,
                escapeshellarg($fullPath),
                escapeshellarg($tmpPath)
            );
            exec($cmd, $out, $ret);
            if ($ret === 0 && file_exists($tmpPath) && filesize($tmpPath) > 0) {
                @unlink($fullPath);
                rename($tmpPath, $fullPath);
            } elseif (file_exists($tmpPath)) {
                @unlink($tmpPath);
            }

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
        $video  = DB::table('videos')->where('id', $id)->first();

        if (!$video) abort(404);

        if ($video->status !== 'approved') {
            if ((string)$video->user_id !== (string)$userId) abort(403);
        }

        $user = auth()->user();
        if ($video->album_type === 'private') {
            if (!\App\Services\MembershipService::can($user->id, 'can_view_private_photos'))
                abort(403, 'Necesitas membresia Connectors o superior.');
        }
        if ($video->album_type === 'vip') {
            if (!\App\Services\MembershipService::hasMinLevel($user->id, 'vip_elite'))
                abort(403, 'Necesitas membresia VIP Elite o superior.');
        }

        $fullPath = storage_path('app/private/' . $video->file_path);
        if (!file_exists($fullPath)) abort(404, 'Archivo no encontrado.');

        if ($video->status === 'approved') {
            DB::table('videos')->where('id', $id)->increment('views_count');
        }

        $ext      = strtolower(pathinfo($video->file_path, PATHINFO_EXTENSION));
        $mimeMap  = ['mp4'=>'video/mp4','mov'=>'video/quicktime','avi'=>'video/x-msvideo','webm'=>'video/webm'];
        $mimeType = $mimeMap[$ext] ?? 'video/mp4';
        $fileSize = filesize($fullPath);

        // HTTP Range Request support (streaming real)
        $start = 0;
        $end   = $fileSize - 1;
        $status = 200;
        $headers = [
            'Content-Type'        => $mimeType,
            'Content-Disposition' => 'inline',
            'Accept-Ranges'       => 'bytes',
            'Cache-Control'       => 'private, max-age=3600',
        ];

        if (request()->hasHeader('Range')) {
            $range = request()->header('Range');
            preg_match('/bytes=(\d+)-(\d*)/', $range, $matches);
            $start  = (int) $matches[1];
            $end    = !empty($matches[2]) ? (int)$matches[2] : $fileSize - 1;
            $status = 206;
            $headers['Content-Range']  = "bytes {$start}-{$end}/{$fileSize}";
            $headers['Content-Length'] = $end - $start + 1;
        } else {
            $headers['Content-Length'] = $fileSize;
        }

        return response()->stream(function () use ($fullPath, $start, $end) {
            $fp = fopen($fullPath, 'rb');
            fseek($fp, $start);
            $remaining = $end - $start + 1;
            while (!feof($fp) && $remaining > 0) {
                $chunk = min(8192, $remaining);
                echo fread($fp, $chunk);
                $remaining -= $chunk;
                flush();
            }
            fclose($fp);
        }, $status, $headers);
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


