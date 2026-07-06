<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        /** @var User $user */
        $user = auth()->user();
        $tab  = $request->get('tab', 'new');

        $feedQuery = DB::table('photos')
            ->join('users as u', function ($join) {
                $join->on(DB::raw('u.id::text'), '=', DB::raw('photos.user_id::text'));
            })
            ->leftJoin('profiles as p', function ($join) {
                $join->on(DB::raw('p.user_id::text'), '=', DB::raw('u.id::text'));
            })
            ->where('photos.status', 'approved')
            ->where('u.active', true)
            ->select([
                'photos.id',
                'photos.user_id',
                'photos.file_path',
                'photos.thumbnail_path',
                'photos.caption',
                'photos.photo_uuid',
                'photos.is_profile_photo',
                'photos.created_at',
                DB::raw('COALESCE(p.nickname, u.username) as nickname'),
                DB::raw('COALESCE(p.display_name, u.username) as display_name'),
                DB::raw('p.avatar_url as avatar_url'),
                DB::raw('p.verified_profile as verified_profile'),
                DB::raw('p.city as city'),
                DB::raw('p.state as state'),
                DB::raw('0 as likes_count'),
                DB::raw('0 as comments_count'),
            ]);

        if ($tab === 'following') {
            $feedQuery->whereIn(DB::raw('photos.user_id::text'), function ($sub) use ($user) {
                $sub->select(DB::raw('following_id::text'))
                    ->from('follows')
                    ->where(DB::raw('follower_id::text'), '=', (string) $user->id);
            });
        }

        $feed = $feedQuery
            ->orderByDesc('photos.created_at')
            ->paginate(24);

        return view('dashboard.index', [
            'user'    => $user,
            'profile' => $user->profile,
            'tab'     => $tab,
            'feed'    => $feed,
        ]);
    }
}
