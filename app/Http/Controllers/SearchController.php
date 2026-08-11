<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    // ── Búsqueda live (AJAX navbar) ──
    public function live(Request $request)
    {
        $q = trim($request->get('q', ''));

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $results = DB::table('profiles')
            ->join('users', DB::raw('profiles.user_id::text'), '=', 'users.id')
            ->select(
                'profiles.nickname',
                'profiles.avatar_url',
                'profiles.profile_type',
                'profiles.city',
                'users.last_seen_at'
            )
            ->whereRaw('profiles.profile_completed = true')
            ->where(function($query) use ($q) {
                $query->whereRaw('LOWER(profiles.nickname) LIKE ?', ['%' . strtolower($q) . '%'])
                      ->orWhereRaw('LOWER(profiles.display_name) LIKE ?', ['%' . strtolower($q) . '%']);
            })
            ->limit(6)
            ->get()
            ->map(fn($p) => [
                'nick'         => $p->nickname,
                'avatar'       => $p->avatar_url ?? asset('img/default-avatar.svg'),
                'profile_type' => $p->profile_type ?? 'single',
                'city'         => $p->city ?? '',
                'url'          => route('profile.show', $p->nickname),
                'online'       => $p->last_seen_at
                                  && now()->diffInMinutes($p->last_seen_at) <= 10,
            ]);

        return response()->json($results);
    }

    // ── Página de resultados completos ──
    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));

        $results = collect();
        if (strlen($q) >= 2) {
            $results = DB::table('profiles')
                ->join('users', DB::raw('profiles.user_id::text'), '=', 'users.id')
                ->select('profiles.*', 'users.last_seen_at', 'users.created_at as joined_at')
                ->whereRaw('profiles.profile_completed = true')
                ->where(function($query) use ($q) {
                    $query->whereRaw('LOWER(profiles.nickname) LIKE ?', ['%' . strtolower($q) . '%'])
                          ->orWhereRaw('LOWER(profiles.bio) LIKE ?', ['%' . strtolower($q) . '%']);
                })
                ->orderByDesc('users.last_seen_at')
                ->paginate(20);
        }

        return view('search.index', compact('q', 'results'));
    }
}