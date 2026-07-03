<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExploreController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('profiles')
            ->where('profile_completed', true)
            ->where(function($q) {
                $q->where('public', true)->orWhereNull('public');
            })
            ->whereNotNull('nickname')
            ->where('nickname', '!=', '');
            ->whereNotExists(function($q) {
                $q->select(DB::raw(1))
                ->from('users')
                ->whereRaw('users.id::text = profiles.user_id::text')
                ->where('users.role', 'admin');
            })

            // Excluir cuentas admin del explorador
            ->whereNotExists(function($q) {
                $q->select(DB::raw(1))
                ->from('users')
                ->whereRaw('users.id::text = profiles.user_id::text')
                ->where('users.role', 'admin');
})



        // ── Filtros ──
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

        // Ordenar — más recientes primero, luego verificados
        $query->orderByRaw("
            CASE WHEN verified_profile = true THEN 0 ELSE 1 END ASC,
            last_active_at DESC NULLS LAST
        ");

        $profiles = $query->paginate(24)->withQueryString();

        // Obtener avatar de cada perfil
        $userIds = $profiles->pluck('user_id')->toArray();

        // Primero: fotos de perfil designadas
        $avatars = DB::table('photos')
            ->whereIn(DB::raw('user_id::text'), $userIds)
            ->where('is_profile_photo', true)
            ->where('status', 'approved')
            ->select('user_id', 'file_path')
            ->get()
            ->keyBy('user_id');

        // Fallback: primera foto pública para quienes no tienen foto de perfil
        $missingAvatars = collect($userIds)->filter(function($uid) use ($avatars) {
            return !isset($avatars[$uid]);
        })->values()->toArray();

        if (!empty($missingAvatars)) {
            $fallbacks = DB::table('photos')
                ->whereIn(DB::raw('user_id::text'), $missingAvatars)
                ->where('album_type', 'public')
                ->where('status', 'approved')
                ->orderBy('sort_order')
                ->orderBy('created_at')
                ->select('user_id', 'file_path')
                ->get()
                ->unique('user_id')
                ->keyBy('user_id');

            $avatars = $avatars->merge($fallbacks);
        }

        // Ciudades únicas para el filtro
        $ciudades = DB::table('profiles')
            ->where('profile_completed', true)
            ->where('public', true)
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->distinct()
            ->orderBy('city')
            ->pluck('city');

        return view('profiles.explore', compact(
            'profiles', 'avatars', 'ciudades'
        ));
    }
}
