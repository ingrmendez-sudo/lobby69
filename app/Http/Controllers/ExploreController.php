<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExploreController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('profiles')
            ->whereRaw('profile_completed = true')
            ->where(function($q) {
                $q->whereRaw('public = true')->orWhereNull('public');
            })
            ->whereNotNull('nickname')
            ->where('nickname', '!=', '')
            ->whereNotExists(function($q) {
                $q->select(DB::raw(1))
                  ->from('users')
                  ->whereRaw('users.id::text = profiles.user_id::text')
                  ->where('users.role', 'admin');
            })
            ->select(
                'user_id', 'nickname', 'profile_type', 'gender',
                'city', 'state', 'orientation', 'age',
                'verified_profile', 'last_active_at', 'created_at',
                DB::raw('COALESCE(recommendation_score, 0) as recommendation_score')
            );

        if ($request->filled('tipo')) {
            $query->where('profile_type', $request->tipo);
        }
        if ($request->filled('genero')) {
            $query->where('gender', $request->genero);
        }
        if ($request->filled('ciudad')) {
            $query->where(function($q) use ($request) {
                $q->where('city',  'ilike', '%' . $request->ciudad . '%')
                  ->orWhere('state', 'ilike', '%' . $request->ciudad . '%');
            });
        }
        if ($request->filled('orientacion')) {
            $query->where('orientation', $request->orientacion);
        }

        $orden = $request->get('orden', 'destacados');

        if ($orden === 'score') {
            $query->orderByRaw('COALESCE(recommendation_score, 0) DESC, last_active_at DESC NULLS LAST');
        } elseif ($orden === 'recientes') {
            $query->orderBy('created_at', 'desc');
        } elseif ($orden === 'activos') {
            $query->orderBy('last_active_at', 'desc');
        } else {
            // 'destacados' — verificados primero, luego por score, luego por actividad
            $query->orderByRaw("
                CASE WHEN verified_profile = true THEN 0 ELSE 1 END ASC,
                COALESCE(recommendation_score, 0) DESC,
                last_active_at DESC NULLS LAST
            ");
        }

        $profiles = $query->paginate(24)->withQueryString();

        $userIds = $profiles->pluck('user_id')->toArray();

        $avatars = DB::table('photos')
            ->whereIn(DB::raw('user_id::text'), $userIds)
            ->whereRaw('is_profile_photo = true')
            ->whereRaw("status = 'approved'")
            ->select('user_id', 'file_path', 'id')
            ->get()
            ->keyBy(fn($r) => (string)$r->user_id);

        $missingAvatars = collect($userIds)
            ->filter(fn($uid) => !isset($avatars[(string)$uid]))
            ->values()
            ->toArray();

        if (!empty($missingAvatars)) {
            $fallbacks = DB::table('photos')
                ->whereIn(DB::raw('user_id::text'), $missingAvatars)
                ->where('album_type', 'public')
                ->whereRaw("status = 'approved'")
                ->orderBy('sort_order')
                ->orderBy('created_at')
                ->select('user_id', 'file_path', 'id')
                ->get()
                ->unique('user_id')
                ->keyBy(fn($r) => (string)$r->user_id);

            $avatars = $avatars->merge($fallbacks);
        }

        $ciudades = DB::table('profiles')
            ->whereRaw('profile_completed = true')
            ->whereRaw('public = true')
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->distinct()
            ->orderBy('city')
            ->pluck('city');

        return view('profiles.explore', compact('profiles', 'avatars', 'ciudades', 'orden'));
    }
}