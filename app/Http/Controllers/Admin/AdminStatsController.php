<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class AdminStatsController extends Controller
{
    public function index()
    {
        // Registros por día últimos 30 días
        $usersByDay = DB::table('users')
            ->where('role', '!=', 'admin')
            ->where('created_at', '>=', now()->subDays(29))
            ->selectRaw("TO_CHAR(created_at, 'DD/MM') as day, count(*) as total")
            ->groupByRaw("TO_CHAR(created_at, 'DD/MM'), DATE(created_at)")
            ->orderByRaw("DATE(created_at)")
            ->get();

        // Fotos por día
        $photosByDay = DB::table('photos')
            ->where('created_at', '>=', now()->subDays(29))
            ->selectRaw("TO_CHAR(created_at, 'DD/MM') as day, count(*) as total")
            ->groupByRaw("TO_CHAR(created_at, 'DD/MM'), DATE(created_at)")
            ->orderByRaw("DATE(created_at)")
            ->get();

        // Membresías
        $membershipStats = DB::table('users')
            ->where('role', '!=', 'admin')
            ->selectRaw('membership_type, count(*) as total')
            ->groupBy('membership_type')
            ->orderByDesc('total')
            ->get();

        // Totales generales
        $totals = [
            'users'    => DB::table('users')->where('role','!=','admin')->count(),
            'photos'   => DB::table('photos')->where('status','approved')->count(),
            'videos'   => DB::table('videos')->where('status','approved')->count(),
            'likes'    => DB::table('photo_likes')->count(),
            'comments' => DB::table('photo_comments')->where('status','approved')->count(),
            'follows'  => DB::table('follows')->count(),
        ];

        // Conversión: trial vs pagados
        $paidCount  = DB::table('users')->where('role','!=','admin')
            ->whereNotIn('membership_type', ['trial','trial_verified'])->count();
        $trialCount = DB::table('users')->where('role','!=','admin')
            ->whereIn('membership_type', ['trial','trial_verified'])->count();

        // Usuarios más activos (más fotos)
        $topUploaders = DB::table('photos')
            ->joinSub(
                DB::table('users')->selectRaw('"id"::text as uid, "username"'),
                'u', 'photos.user_id', '=', 'u.uid'
            )
            ->leftJoinSub(
                DB::table('profiles')->selectRaw('"user_id"::text as pid, "nickname"'),
                'p', 'photos.user_id', '=', 'p.pid'
            )
            ->where('photos.status', 'approved')
            ->selectRaw('COALESCE(p.nickname, u.username) as name, count(*) as total')
            ->groupBy('photos.user_id', 'p.nickname', 'u.username')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return view('admin.stats.index', compact(
            'usersByDay', 'photosByDay', 'membershipStats',
            'totals', 'paidCount', 'trialCount', 'topUploaders'
        ));
    }
}
