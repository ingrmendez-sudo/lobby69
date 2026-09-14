<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $user   = Auth::user();
        $userId = (string) $user->id;
        $now    = Carbon::now();

        $announcements = DB::table('announcements as a')
            ->join('users as u', DB::raw('u.id::text'), '=', DB::raw('a.user_id::text'))
            ->leftJoin('profiles as pr', DB::raw('pr.user_id::text'), '=', DB::raw('u.id::text'))
            ->whereRaw('a.user_id::text != ?', [$userId])
            ->where('a.status', 'active')
            ->where(function ($q) {
                $q->whereNull('a.expires_at')
                  ->orWhere('a.expires_at', '>', now());
            })
            ->orderByDesc('a.created_at')
            ->select([
                'a.id', 'a.title', 'a.looking_for', 'a.event_date',
                'a.proposal', 'a.created_at', 'a.directed_to', 'a.what_looking', 'a.user_id',
                DB::raw("COALESCE(a.expires_at, a.created_at + INTERVAL '4 days') AS expires_at"),
                DB::raw('COALESCE(pr.display_name, u.username) AS display_name'),
                'pr.nickname', 'pr.profile_type', 'pr.city', 'pr.verified_profile',
                DB::raw("(SELECT ap.id FROM photos ap WHERE ap.user_id::text = u.id::text AND ap.is_profile_photo = true AND ap.status = 'approved' LIMIT 1) AS avatar_photo_id"),
            ])
            ->limit(50)->get()
            ->map(function ($a) use ($now) {
                $a->is_expired   = Carbon::parse($a->expires_at)->lt($now);
                $a->directed_to  = $a->directed_to  ? json_decode($a->directed_to,  true) : [];
                $a->what_looking = $a->what_looking ? json_decode($a->what_looking, true) : [];
                return $a;
            });

        $myAnnouncements = DB::table('announcements')
            ->whereRaw('user_id::text = ?', [$userId])
            ->orderByDesc('created_at')->get()
            ->map(function ($a) use ($now) {
                $expires = $a->expires_at
                    ? Carbon::parse($a->expires_at)
                    : Carbon::parse($a->created_at)->addDays(4);
                $a->is_expired   = $expires->lt($now);
                $a->directed_to  = $a->directed_to  ? json_decode($a->directed_to,  true) : [];
                $a->what_looking = $a->what_looking ? json_decode($a->what_looking, true) : [];
                return $a;
            });

        return view('announcements.index', compact('announcements', 'myAnnouncements', 'userId'));
    }
}
