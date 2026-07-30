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
        DB::table('videos')
            ->where('id', $id)
            ->update([
                'status'      => 'approved',
                'reviewed_by' => (string) Auth::id(),
                'reviewed_at' => now(),
                'admin_note'  => null,
                'updated_at'  => now(),
            ]);

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

        $path = $video->file_path;

        if (!Storage::disk('private')->exists($path)) {
            abort(404, 'Archivo no encontrado');
        }

        $fullPath = Storage::disk('private')->path($path);

        return response()->file($fullPath, [
            'Content-Type'        => mime_content_type($fullPath),
            'Content-Length'      => filesize($fullPath),
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
            'Cache-Control'       => 'no-store',
        ]);
    }
}

