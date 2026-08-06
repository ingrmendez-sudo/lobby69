<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\DB;

Broadcast::channel('chat.{userId}', function ($user, $userId) {
    return (string) $user->id === (string) $userId;
});

Broadcast::channel('presence-lobby', function ($user) {
    $profile = DB::table('profiles')->where('user_id', $user->id)->first();
    $photo   = DB::table('photos')
        ->whereRaw('user_id::text = ?', [(string)$user->id])
        ->where('is_profile_photo', true)
        ->where('status', 'approved')
        ->first(['id', 'file_path']);

    $avatarUrl = $photo
        ? 'https://kjhaquimghhejqznleyn.supabase.co/storage/v1/object/public/gallery/' . $photo->file_path
        : null;

    return [
        'id'     => (string) $user->id,
        'name'   => $profile->nickname ?? $profile->display_name ?? $user->username ?? 'Usuario',
        'avatar' => $avatarUrl,
    ];
});
Broadcast::channel('presence-sala-general', function ($user) {
    $profile = DB::table('profiles')->where('user_id', $user->id)->first();
    return [
        'id'   => (string) $user->id,
        'name' => $profile->nickname ?? $profile->display_name ?? $user->username ?? 'Usuario',
    ];
});


// Canal privado para senalizacion de videollamadas
Broadcast::channel('video.user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
