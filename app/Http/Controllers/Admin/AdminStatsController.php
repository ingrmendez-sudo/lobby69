<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class AdminStatsController extends Controller
{
    public function index()
    {
        // ── Registros por día últimos 30 días ──
        $usersByDay = DB::table('users')
            ->where('role', '!=', 'admin')
            ->where('created_at', '>=', now()->subDays(29))
            ->selectRaw("TO_CHAR(created_at, 'DD/MM') as day, count(*) as total")
            ->groupByRaw("TO_CHAR(created_at, 'DD/MM'), DATE(created_at)")
            ->orderByRaw("DATE(created_at)")
            ->get();

        // ── Fotos por día últimos 30 días ──
        $photosByDay = DB::table('photos')
            ->where('created_at', '>=', now()->subDays(29))
            ->selectRaw("TO_CHAR(created_at, 'DD/MM') as day, count(*) as total")
            ->groupByRaw("TO_CHAR(created_at, 'DD/MM'), DATE(created_at)")
            ->orderByRaw("DATE(created_at)")
            ->get();

        // ── Visitas de perfil por día últimos 30 días ──
        $viewsByDay = DB::table('profile_views')
            ->where('viewed_at', '>=', now()->subDays(29))
            ->selectRaw("TO_CHAR(viewed_at, 'DD/MM') as day, count(*) as total")
            ->groupByRaw("TO_CHAR(viewed_at, 'DD/MM'), DATE(viewed_at)")
            ->orderByRaw("DATE(viewed_at)")
            ->get();

        // ── Visitas por semana últimas 12 semanas ──
        $viewsByWeek = DB::table('profile_views')
            ->where('viewed_at', '>=', now()->subWeeks(11))
            ->selectRaw("TO_CHAR(DATE_TRUNC('week', viewed_at), 'DD/MM') as week, count(*) as total")
            ->groupByRaw("DATE_TRUNC('week', viewed_at)")
            ->orderByRaw("DATE_TRUNC('week', viewed_at)")
            ->get();

        // ── Visitas por mes últimos 12 meses ──
        $viewsByMonth = DB::table('profile_views')
            ->where('viewed_at', '>=', now()->subMonths(11))
            ->selectRaw("TO_CHAR(viewed_at, 'MM/YYYY') as month, count(*) as total")
            ->groupByRaw("TO_CHAR(viewed_at, 'MM/YYYY'), DATE_TRUNC('month', viewed_at)")
            ->orderByRaw("DATE_TRUNC('month', viewed_at)")
            ->get();

        // ── Comparativa visitas: semana actual vs anterior ──
        $viewsThisWeek = DB::table('profile_views')
            ->where('viewed_at', '>=', now()->startOfWeek())->count();
        $viewsLastWeek = DB::table('profile_views')
            ->whereBetween('viewed_at', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()])->count();
        $viewsThisMonth = DB::table('profile_views')
            ->where('viewed_at', '>=', now()->startOfMonth())->count();
        $viewsLastMonth = DB::table('profile_views')
            ->whereBetween('viewed_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])->count();

        // ── Membresías ──
        $membershipStats = DB::table('users')
            ->where('role', '!=', 'admin')
            ->selectRaw('membership_type, count(*) as total')
            ->groupBy('membership_type')
            ->orderByDesc('total')
            ->get();

        // ── Membresías por mes últimos 6 meses (conversión) ──
        $membershipsByMonth = DB::table('users')
            ->where('role', '!=', 'admin')
            ->whereNotNull('membership_started_at')
            ->where('membership_started_at', '>=', now()->subMonths(5))
            ->whereNotIn('membership_type', ['invitado'])
            ->selectRaw("TO_CHAR(membership_started_at, 'MM/YYYY') as month, count(*) as total")
            ->groupByRaw("TO_CHAR(membership_started_at, 'MM/YYYY'), DATE_TRUNC('month', membership_started_at)")
            ->orderByRaw("DATE_TRUNC('month', membership_started_at)")
            ->get();

        // ── Totales generales ──
        $totals = [
            'users'        => DB::table('users')->where('role', '!=', 'admin')->count(),
            'photos'       => DB::table('photos')->where('status', 'approved')->count(),
            'videos'       => DB::table('videos')->where('status', 'approved')->count(),
            'likes'        => DB::table('photo_likes')->count(),
            'comments'     => DB::table('photo_comments')->where('status', 'approved')->count(),
            'follows'      => DB::table('follows')->count(),
            'profile_views'=> DB::table('profile_views')->count(),
        ];

        // ── Conversión invitado vs pagados ──
        $paidCount  = DB::table('users')->where('role', '!=', 'admin')
            ->whereNotIn('membership_type', ['invitado'])->count();
        $invitadoCount = DB::table('users')->where('role', '!=', 'admin')
            ->whereIn('membership_type', ['invitado'])->count();

        // ── Funnel de conversión ──
        $totalRegistered    = DB::table('users')->where('role', '!=', 'admin')->count();
        $profileCompleted   = DB::table('profiles')->whereRaw('profile_completed = true')->count();
        $uploadedPhoto      = DB::table('photos')->distinct('user_id')->count('user_id');
        $paidMembership     = DB::table('users')->where('role', '!=', 'admin')
            ->whereNotIn('membership_type', ['invitado'])->count();

        $funnel = [
            ['label' => 'Registrados',       'value' => $totalRegistered,  'color' => '#6C3FC5'],
            ['label' => 'Perfil completo',    'value' => $profileCompleted, 'color' => '#a855f7'],
            ['label' => 'Subió foto',         'value' => $uploadedPhoto,    'color' => '#ec4899'],
            ['label' => 'Membresía paga',     'value' => $paidMembership,   'color' => '#22c55e'],
        ];

        // ── Usuarios por estado (normalizado) ──
        $usersByState = DB::table('profiles')
            ->selectRaw("
                CASE
                    WHEN LOWER(TRIM(state)) IN ('cdmx','ciudad de mexico','ciudad de méxico','df','d.f.') THEN 'CDMX'
                    WHEN LOWER(TRIM(state)) IN ('jalisco','jal') THEN 'Jalisco'
                    WHEN LOWER(TRIM(state)) IN ('nuevo leon','nuevo león','nl') THEN 'Nuevo León'
                    WHEN LOWER(TRIM(state)) IN ('puebla','pue') THEN 'Puebla'
                    WHEN LOWER(TRIM(state)) IN ('queretaro','querétaro','qro') THEN 'Querétaro'
                    WHEN TRIM(state) = '' OR state IS NULL THEN 'Sin especificar'
                    ELSE INITCAP(TRIM(state))
                END as estado,
                count(*) as total
            ")
            ->groupByRaw("
                CASE
                    WHEN LOWER(TRIM(state)) IN ('cdmx','ciudad de mexico','ciudad de méxico','df','d.f.') THEN 'CDMX'
                    WHEN LOWER(TRIM(state)) IN ('jalisco','jal') THEN 'Jalisco'
                    WHEN LOWER(TRIM(state)) IN ('nuevo leon','nuevo león','nl') THEN 'Nuevo León'
                    WHEN LOWER(TRIM(state)) IN ('puebla','pue') THEN 'Puebla'
                    WHEN LOWER(TRIM(state)) IN ('queretaro','querétaro','qro') THEN 'Querétaro'
                    WHEN TRIM(state) = '' OR state IS NULL THEN 'Sin especificar'
                    ELSE INITCAP(TRIM(state))
                END
            ")
            ->orderByDesc('total')
            ->get();

        // ── Actividad por hora del día (últimos 30 días) ──
        $activityByHour = DB::table('profile_views')
            ->where('viewed_at', '>=', now()->subDays(29))
            ->selectRaw("EXTRACT(HOUR FROM viewed_at) as hour, count(*) as total")
            ->groupByRaw("EXTRACT(HOUR FROM viewed_at)")
            ->orderByRaw("EXTRACT(HOUR FROM viewed_at)")
            ->get();

        // ── Top uploaders ──
        $topUploaders = DB::table('photos')
        ->join(
            DB::raw('(SELECT id::text as uid, username FROM users) as u'),
            DB::raw('photos.user_id::text'), '=', 'u.uid'      // ← ::text aquí
        )
        ->leftJoin(
            DB::raw('(SELECT user_id::text as pid, nickname FROM profiles) as p'),
            DB::raw('photos.user_id::text'), '=', 'p.pid'      // ← ::text aquí
        )
        ->where('photos.status', 'approved')
        ->selectRaw('COALESCE(p.nickname, u.username) as name, count(*) as total')
        ->groupBy('photos.user_id', 'p.nickname', 'u.username')
        ->orderByDesc('total')
        ->limit(10)
        ->get();


        // ── Retención: usuarios activos últimos 7/30 días ──
        $activeUsers7d  = DB::table('users')
            ->where('role', '!=', 'admin')
            ->where('last_seen_at', '>=', now()->subDays(7))->count();
        $activeUsers30d = DB::table('users')
            ->where('role', '!=', 'admin')
            ->where('last_seen_at', '>=', now()->subDays(30))->count();


        // ── Metricas de referidos ──
        $totalReferidos = DB::table('users')
            ->whereNotNull('referral_code')
            ->whereRaw('LENGTH("referral_code") > 0')
            ->count();

        $referidosConPago = DB::table('users')
            ->whereNotNull('referral_code')
            ->whereRaw('LENGTH("referral_code") > 0')
            ->whereExists(function($q) {
                $q->select(DB::raw(1))
                   ->from('membership_payments')
                   ->whereRaw('membership_payments.user_id::text = users.id::text')
                   ->where('membership_payments.status', 'approved');
            })
            ->distinct()
            ->count('id');

        $ingresosReferidos = (float) DB::table('membership_payments')
            ->join('users', function($join) {
                $join->whereRaw('users.id::text = membership_payments.user_id');
            })
            ->whereNotNull('users.referral_code')
            ->whereRaw('LENGTH(users.referral_code) > 0')
            ->where('membership_payments.status', 'approved')
            ->sum('membership_payments.amount');

        $topCodigos = DB::table('referral_codes')
            ->leftJoin('users as u', 'referral_codes.owner_user_id', '=', 'u.id')
            ->orderByDesc('uses_count')
            ->limit(5)
            ->get(['referral_codes.code', 'referral_codes.uses_count', 'referral_codes.max_uses', 'u.username as owner_name']);

        $conversionReferidos = $totalReferidos > 0
            ? round(($referidosConPago / $totalReferidos) * 100, 1)
            : 0;
        return view('admin.stats.index', compact(
            'usersByDay', 'photosByDay', 'viewsByDay', 'viewsByWeek', 'viewsByMonth',
            'viewsThisWeek', 'viewsLastWeek', 'viewsThisMonth', 'viewsLastMonth',
            'membershipStats', 'membershipsByMonth',
            'totals', 'paidCount', 'invitadoCount',
            'funnel', 'usersByState', 'activityByHour', 'topUploaders',
            'activeUsers7d', 'activeUsers30d', 'totalRegistered',
            'totalReferidos', 'referidosConPago', 'ingresosReferidos', 'topCodigos', 'conversionReferidos'
        ));
    }
}

