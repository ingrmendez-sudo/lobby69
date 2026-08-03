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

        if ($file->getSize() > self::MAX_SIZE_BYTES) {
            return back()->with('error', 'El video supera el límite de 100MB.');
        }

        try {
            // ── 1. Guardar temporalmente en disco local ──────────────────────
            $ext      = strtolower($file->getClientOriginalExtension());
            $basename = 'video_' . $userId . '_' . time();
            $filename = $basename . '.' . $ext;
            $folder   = 'videos/' . $userId;
            $filePath = $folder . '/' . $filename;

            Storage::disk('private')->makeDirectory($folder);
            Storage::disk('private')->putFileAs($folder, $file, $filename);
            $localPath = storage_path('app/private/' . $filePath);

            // ── 2. FFmpeg: faststart + extracción de duración ────────────────
            $duration      = null;
            $thumbnailPath = null;
            $tmpPath       = $localPath . '.tmp.mp4';

            // faststart (mover moov atom al inicio para streaming inmediato)
            $cmd = sprintf(
                'ffmpeg -i %s -c copy -movflags +faststart %s -y -loglevel error 2>&1',
                escapeshellarg($localPath),
                escapeshellarg($tmpPath)
            );
            exec($cmd, $out, $ret);
            if ($ret === 0 && file_exists($tmpPath) && filesize($tmpPath) > 0) {
                @unlink($localPath);
                rename($tmpPath, $localPath);
            } elseif (file_exists($tmpPath)) {
                @unlink($tmpPath);
            }

            // duración con ffprobe
            $probeCmd = sprintf(
                'ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 %s 2>&1',
                escapeshellarg($localPath)
            );
            exec($probeCmd, $probeOut, $probeRet);
            if ($probeRet === 0 && !empty($probeOut[0]) && is_numeric(trim($probeOut[0]))) {
                $duration = (int) round((float) trim($probeOut[0]));
            }

            // thumbnail: extraer frame del segundo 2 (o el primero disponible)
            $thumbFilename = $basename . '_thumb.jpg';
            $thumbLocal    = storage_path('app/private/' . $folder . '/' . $thumbFilename);
            $thumbCmd = sprintf(
                'ffmpeg -i %s -ss 00:00:02 -vframes 1 -vf scale=640:-1 -q:v 3 %s -y -loglevel error 2>&1',
                escapeshellarg($localPath),
                escapeshellarg($thumbLocal)
            );
            exec($thumbCmd, $thumbOut, $thumbRet);
            // Si el video es muy corto, intentar con el frame 0
            if ($thumbRet !== 0 || !file_exists($thumbLocal) || filesize($thumbLocal) === 0) {
                $thumbCmd2 = sprintf(
                    'ffmpeg -i %s -vframes 1 -vf scale=640:-1 -q:v 3 %s -y -loglevel error 2>&1',
                    escapeshellarg($localPath),
                    escapeshellarg($thumbLocal)
                );
                exec($thumbCmd2, $thumbOut2, $thumbRet2);
            }

            // ── 3. Subir video a Supabase Storage ───────────────────────────
            $supabasePath = $filePath; // videos/{userId}/video_{userId}_{time}.mp4
            Storage::disk('supabase')->put(
                $supabasePath,
                fopen($localPath, 'rb'),
                ['ContentType' => 'video/' . ($ext === 'mov' ? 'quicktime' : $ext)]
            );

            // ── 4. Subir thumbnail a Supabase (si se generó) ────────────────
            $supabaseThumbPath = null;
            if (file_exists($thumbLocal) && filesize($thumbLocal) > 0) {
                $supabaseThumbPath = $folder . '/' . $thumbFilename;
                Storage::disk('supabase')->put(
                    $supabaseThumbPath,
                    fopen($thumbLocal, 'rb'),
                    ['ContentType' => 'image/jpeg']
                );
                // URL pública del thumbnail (no necesita firma)
                $supabaseThumbPath = Storage::disk('supabase')->url($supabaseThumbPath);
                @unlink($thumbLocal);
            }

            // ── 5. Eliminar archivo local (ya está en Supabase) ─────────────
            @unlink($localPath);

            // ── 6. Registrar en base de datos ───────────────────────────────
            DB::table('videos')->insert([
                'video_uuid'       => (string) Str::uuid(),
                'user_id'          => (string) $userId,
                'album_type'       => $albumType,
                'file_path'        => $supabasePath,
                'thumbnail_path'   => $supabaseThumbPath,
                'duration_seconds' => $duration,
                'file_size_bytes'  => $file->getSize(),
                'caption'          => $caption ?: null,
                'status'           => 'pending',
                'sort_order'       => 0,
                'views_count'      => 0,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            return back()->with('success', 'Video subido correctamente. Será revisado antes de publicarse.');

        } catch (\Throwable $e) {
            Log::error('VideoController@store error: ' . $e->getMessage(), [
                'user_id' => $userId,
                'trace'   => $e->getTraceAsString(),
            ]);
            // Limpiar archivos temporales si quedaron
            if (isset($localPath) && file_exists($localPath)) @unlink($localPath);
            if (isset($tmpPath)   && file_exists($tmpPath))   @unlink($tmpPath);
            if (isset($thumbLocal) && file_exists($thumbLocal)) @unlink($thumbLocal);

            return back()->with('error', 'Error al subir el video: ' . $e->getMessage());
        }
    }



    // ── Servir video (streaming con soporte Supabase + local) ──
    public function serve(int $id)
    {
        $video = DB::table('videos')->where('id', $id)->first();
        if (!$video) abort(404);

        $userId = auth()->id();
        $user   = auth()->user();

        // ── Control de acceso ──────────────────────────────────────────────
        if ($video->status !== 'approved') {
            if (!$userId || (string)$video->user_id !== (string)$userId) {
                abort(403, 'Video pendiente de aprobación.');
            }
        }

        if ($video->album_type === 'private') {
            if (!$user || !\App\Services\MembershipService::can($user->id, 'can_view_private_photos'))
                abort(403, 'Necesitas membresía Connectors o superior.');
        }
        if ($video->album_type === 'vip') {
            if (!$user || !\App\Services\MembershipService::hasMinLevel($user->id, 'vip_elite'))
                abort(403, 'Necesitas membresía VIP Elite o superior.');
        }

        // ── Servir thumbnail ───────────────────────────────────────────────
        if (request()->has('thumb')) {
            // thumbnail_path puede ser URL absoluta (Supabase) o ruta relativa
            $thumbPath = $video->thumbnail_path ?? null;
            if ($thumbPath) {
                if (filter_var($thumbPath, FILTER_VALIDATE_URL)) {
                    // URL de Supabase — redirigir directamente
                    return redirect($thumbPath);
                }
                $thumbLocal = storage_path('app/private/' . $thumbPath);
                if (file_exists($thumbLocal)) {
                    return response()->file($thumbLocal, [
                        'Content-Type'  => 'image/jpeg',
                        'Cache-Control' => 'public, max-age=86400',
                    ]);
                }
                // Intentar desde Supabase
                try {
                    if (Storage::disk('supabase')->exists($thumbPath)) {
                        return redirect(Storage::disk('supabase')->url($thumbPath));
                    }
                } catch (\Throwable $e) {}
            }
            // Fallback: thumbnail placeholder SVG
            return response(
                '<svg xmlns="http://www.w3.org/2000/svg" width="640" height="360" viewBox="0 0 640 360">
                    <rect width="640" height="360" fill="#1f2937"/>
                    <text x="320" y="190" font-family="sans-serif" font-size="48"
                          fill="#6b7280" text-anchor="middle">▶</text>
                </svg>',
                200,
                ['Content-Type' => 'image/svg+xml', 'Cache-Control' => 'public, max-age=3600']
            );
        }

        // ── Servir video (local preferido, Supabase como fallback) ─────────
        $localPath = storage_path('app/private/' . $video->file_path);

        if (!file_exists($localPath)) {
            // Intentar desde Supabase
            try {
                if (Storage::disk('supabase')->exists($video->file_path)) {
                    $url = Storage::disk('supabase')->temporaryUrl($video->file_path, now()->addMinutes(60));
                    if ($video->status === 'approved') {
                        DB::table('videos')->where('id', $id)->increment('views_count');
                    }
                    return redirect($url);
                }
            } catch (\Throwable $e) {
                Log::error('VideoController@serve Supabase fallback failed: ' . $e->getMessage());
            }
            abort(404, 'Archivo no encontrado.');
        }

        // ── Incrementar vistas ─────────────────────────────────────────────
        if ($video->status === 'approved') {
            DB::table('videos')->where('id', $id)->increment('views_count');
        }

        // ── HTTP Range streaming ───────────────────────────────────────────
        $ext      = strtolower(pathinfo($video->file_path, PATHINFO_EXTENSION));
        $mimeMap  = ['mp4'=>'video/mp4','mov'=>'video/quicktime','avi'=>'video/x-msvideo','webm'=>'video/webm'];
        $mimeType = $mimeMap[$ext] ?? 'video/mp4';
        $fileSize = filesize($localPath);

        $start  = 0;
        $end    = $fileSize - 1;
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

        return response()->stream(function () use ($localPath, $start, $end) {
            $fp = fopen($localPath, 'rb');
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



