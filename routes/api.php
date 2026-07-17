<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('web')->group(function () {

    // Badge mensajes no leídos
    Route::get('/messages/unread-count', function () {
        $userId = Auth::id();
        if (!$userId) return response()->json(['count' => 0]);
        try {
            $count = DB::table('messages')
                ->whereRaw('receiver_id::text = ?', [(string)$userId])
                ->where('read', false)
                ->count();
        } catch(\Exception $e) {
            $count = 0;
        }
        return response()->json(['count' => $count]);
    });

    // Badge notificaciones no leídas
    Route::get('/notifications/unread-count', function () {
        $userId = Auth::id();
        if (!$userId) return response()->json(['count' => 0]);
        try {
            $count = DB::table('notifications')
                ->whereRaw('user_id::text = ?', [(string)$userId])
                ->whereNull('read_at')
                ->count();
        } catch(\Exception $e) {
            $count = 0;
        }
        return response()->json(['count' => $count]);
    });

    // Búsqueda de perfiles
    Route::get('/search/profiles', function () {
        $q = request('q', '');
        if (strlen($q) < 2) return response()->json([]);

        $results = DB::table('profiles')
            ->where('nickname', 'ilike', '%' . $q . '%')
            ->select('nickname', 'city', 'avatar_url', 'profile_type')
            ->limit(8)
            ->get()
            ->map(function ($p) {
                return [
                    'nickname' => $p->nickname,
                    'city'     => $p->city ?? '',
                    'avatar'   => $p->avatar_url
                        ? 'https://kjhaquimghhejqznleyn.supabase.co/storage/v1/object/public/gallery/' . $p->avatar_url
                        : '/img/default-avatar.svg',
                    'is_online' => false,
                ];
            });

        return response()->json($results);
    });

});



