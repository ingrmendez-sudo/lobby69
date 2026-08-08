<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminVideoController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');

        $videos = DB::table('videos')
            ->joinSub(
                DB::table('users')->selectRaw('"id"::text as uid, "username", "membership_type", "role"'),
                'u',
                DB::raw('videos.user_id::text'), '=', 'u.uid'
            )
            ->leftJoinSub(
                DB::table('profiles')->selectRaw('"user_id"::text as pid, "display_name", "nickname"'),
                'p',
                DB::raw('videos.user_id::text'), '=', 'p.pid'
            )
            ->select(
                'videos.id',
                'videos.user_id',
                'videos.album_type',
                'videos.file_path',
                'videos.caption',
                'videos.status',
                'videos.admin_note',
                'videos.duration_seconds',
                'videos.file_size_bytes',
                'videos.created_at',
                'u.username',
                'u.membership_type',
                'p.display_name',
                'p.nickname'
            )
            ->where('videos.status', $status)
            ->where('u.role', '!=', 'admin')
            ->orderBy('videos.created_at', $status === 'pending' ? 'asc' : 'desc')
            ->paginate(20);

        $counts = [
            'pending'  => $this->countByStatus('pending'),
            'approved' => $this->countByStatus('approved'),
            'rejected' => $this->countByStatus('rejected'),
        ];

        return view('admin.videos.index', compact('videos', 'status', 'counts'));
    }

    private function countByStatus(string $status): int
    {
        return DB::table('videos')
            ->joinSub(
                DB::table('users')->selectRaw('"id"::text as uid, "role"'),
                'u',
                DB::raw('videos.user_id::text'), '=', 'u.uid'
            )
            ->where('videos.status', $status)
            ->where('u.role', '!=', 'admin')
            ->count();
    }

    public function approve(Request $request, $id)
    {
        $video = DB::table('videos')->where('id', $id)->first();
        if (!$video) return back()->with('error', 'Video no encontrado.');

        DB::table('videos')
            ->where('id', $id)
            ->update([
                'status'      => 'approved',
                'reviewed_by' => (string) Auth::id(),
                'reviewed_at' => now(),
                'admin_note'  => null,
                'updated_at'  => now(),
            ]);

        // Generar thumbnail con FFmpeg si no tiene uno
        if (empty($video->thumbnail_path)) {
            $srcPath   = storage_path('app/private/' . $video->file_path);
            $thumbDir  = storage_path('app/public/thumbs/videos');
            $thumbName = 'thumb_video_' . $video->id . '.jpg';
            $thumbPath = $thumbDir . '/' . $thumbName;
            $thumbDB   = 'thumbs/videos/' . $thumbName;

            if (!is_dir($thumbDir)) mkdir($thumbDir, 0755, true);

            if (file_exists($srcPath)) {
                $cmd = 'ffmpeg -y -i ' . escapeshellarg($srcPath) .
                       ' -ss 00:00:01 -vframes 1 -vf scale=640:360 ' .
                       escapeshellarg($thumbPath) . ' 2>&1';
                exec($cmd, $out, $code);

                if ($code === 0 && file_exists($thumbPath)) {
                    DB::table('videos')->where('id', $id)
                        ->update(['thumbnail_path' => $thumbDB]);
                }
            }
        }

        return back()->with('success', 'Video aprobado correctamente.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string|max:500']);

        DB::table('videos')
            ->where('id', $id)
            ->update([
                'status'      => 'rejected',
                'reviewed_by' => (string) Auth::id(),
                'reviewed_at' => now(),
                'admin_note'  => $request->reason,
                'updated_at'  => now(),
            ]);

        return back()->with('success', 'Video rechazado.');
    }

    public function serve($id)
    {
        abort_if(Auth::user()->role !== 'admin', 403);

        $video = DB::table('videos')->where('id', $id)->first();
        abort_if(!$video, 404);

        // Intentar primero desde Supabase (videos nuevos)
        try {
            $signedUrl = Storage::disk('supabase')
                ->temporaryUrl($video->file_path, now()->addMinutes(30));
            return redirect($signedUrl);
        } catch (\Throwable $e) {
            // Fallback: disco local (videos pre-migración)
        }

        // Fallback local
        $localPath = storage_path('app/private/' . ltrim($video->file_path, '/'));
        if (!file_exists($localPath)) {
            abort(404, 'Archivo no encontrado en disco ni en Supabase.');
        }

        return response()->file($localPath, [
            'Content-Type'        => mime_content_type($localPath),
            'Content-Length'      => filesize($localPath),
            'Content-Disposition' => 'inline; filename="' . basename($localPath) . '"',
            'Cache-Control'       => 'no-store',
        ]);
    }

}


