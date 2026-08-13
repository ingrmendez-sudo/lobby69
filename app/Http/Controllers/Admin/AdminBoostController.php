<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminBoostController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('q');

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

        return view('admin.boost.index', compact('profiles', 'search'));
    }

    public function apply(Request $request, string $userId)
    {
        $request->validate([
            'boost_amount' => 'required|numeric|min:0|max:5',
            'boost_until'  => 'required|date|after:now',
        ]);

        DB::table('profiles')
            ->whereRaw('user_id::text = ?', [$userId])
            ->update([
                'boost_amount' => (float) $request->boost_amount,
                'boost_until'  => Carbon::parse($request->boost_until),
                'updated_at'   => now(),
            ]);

        return back()->with('success', 'Boost aplicado correctamente.');
    }

    public function remove(string $userId)
    {
        DB::table('profiles')
            ->whereRaw('user_id::text = ?', [$userId])
            ->update([
                'boost_amount' => 0,
                'boost_until'  => null,
                'updated_at'   => now(),
            ]);

        return back()->with('success', 'Boost eliminado.');
    }
}