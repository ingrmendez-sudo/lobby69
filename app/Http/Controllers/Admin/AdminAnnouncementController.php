<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminAnnouncementController extends Controller
{
    public function index()
    {
        $now = Carbon::now();

        $announcements = DB::table('announcements as a')
            ->join('users as u',  DB::raw('u.id::text'), '=', DB::raw('a.user_id::text'))
            ->leftJoin('profiles as pr', DB::raw('pr.user_id::text'), '=', DB::raw('u.id::text'))
            ->orderByDesc('a.created_at')
            ->select([
                'a.id', 'a.title', 'a.status', 'a.created_at', 'a.user_id',
                DB::raw("COALESCE(a.expires_at, a.created_at + INTERVAL '4 days') AS expires_at"),
                DB::raw('COALESCE(pr.display_name, u.username) AS display_name'),
                'pr.profile_type', 'u.username', 'u.email',
            ])
            ->paginate(30);

        $stats = [
            'total'   => DB::table('announcements')->count(),
            'active'  => DB::table('announcements')->where('status', 'active')->count(),
            'closed'  => DB::table('announcements')->where('status', 'closed')->count(),
            'expired' => DB::table('announcements')
                ->whereRaw("COALESCE(expires_at, created_at + INTERVAL '4 days') < NOW()")
                ->where('status', 'active')
                ->count(),
        ];

        return view('admin.announcements.index', compact('announcements', 'stats', 'now'));
    }

    public function close($id)
    {
        DB::table('announcements')
            ->whereRaw('id::text = ?', [$id])
            ->update(['status' => 'closed', 'updated_at' => now()]);

        return back()->with('success', 'Anuncio cerrado correctamente.');
    }

    public function destroy($id)
    {
        DB::table('announcements')
            ->whereRaw('id::text = ?', [$id])
            ->delete();

        return back()->with('success', 'Anuncio eliminado correctamente.');
    }
}
