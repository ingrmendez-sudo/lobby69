<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AdminBoostController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('q');
        $tab    = $request->input('tab', 'profiles');

        $profiles = DB::table('profiles')
            ->join('users', DB::raw('users.id::text'), '=', DB::raw('profiles.user_id::text'))
            ->where('users.role', '!=', 'admin')
            ->whereRaw('users.active = true')
            ->when($search, fn($q) => $q->where('profiles.nickname', 'ilike', '%'.$search.'%'))
            ->orderByDesc('profiles.recommendation_score')
            ->select([
                'profiles.user_id',
                'profiles.nickname',
                'profiles.profile_type',
                'profiles.recommendation_score',
                'profiles.boost_amount',
                'profiles.boost_until',
                'users.email',
            ])
            ->paginate(20)
            ->withQueryString();

        $history = DB::table('boost_history as bh')
            ->join('profiles as p', DB::raw('p.user_id::text'), '=', DB::raw('bh.user_id::text'))
            ->join('users as a',    DB::raw('a.id::text'),      '=', DB::raw('bh.admin_id::text'))
            ->orderByDesc('bh.created_at')
            ->select([
                'bh.id',
                'bh.action',
                'bh.boost_amount',
                'bh.boost_until',
                'bh.notes',
                'bh.created_at',
                'p.nickname as profile_nick',
                'a.email as admin_email',
            ])
            ->limit(50)
            ->get();

        return view('admin.boost.index', compact('profiles', 'search', 'tab', 'history'));
    }

    public function apply(Request $request, string $userId)
    {
        $request->validate([
            'boost_amount' => 'required|numeric|min:0|max:5',
            'boost_until'  => 'required|date|after:now',
            'notes'        => 'nullable|string|max:255',
        ]);

        DB::table('profiles')
            ->whereRaw('user_id::text = ?', [$userId])
            ->update([
                'boost_amount' => (float) $request->boost_amount,
                'boost_until'  => Carbon::parse($request->boost_until),
                'updated_at'   => now(),
            ]);

        DB::table('boost_history')->insert([
            'id'           => (string) Str::uuid(),
            'user_id'      => $userId,
            'admin_id'     => (string) auth()->id(),
            'action'       => 'applied',
            'boost_amount' => (float) $request->boost_amount,
            'boost_until'  => Carbon::parse($request->boost_until),
            'notes'        => $request->notes,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return back()->with('success', 'Boost aplicado correctamente.');
    }

    public function remove(Request $request, string $userId)
    {
        $profile = DB::table('profiles')
            ->whereRaw('user_id::text = ?', [$userId])
            ->first();

        DB::table('profiles')
            ->whereRaw('user_id::text = ?', [$userId])
            ->update([
                'boost_amount' => 0,
                'boost_until'  => null,
                'updated_at'   => now(),
            ]);

        DB::table('boost_history')->insert([
            'id'           => (string) Str::uuid(),
            'user_id'      => $userId,
            'admin_id'     => (string) auth()->id(),
            'action'       => 'removed',
            'boost_amount' => 0,
            'boost_until'  => null,
            'notes'        => 'Boost eliminado manualmente',
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return back()->with('success', 'Boost eliminado.');
    }
}
