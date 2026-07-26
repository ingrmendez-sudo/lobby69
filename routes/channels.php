<?php

use Illuminate\Support\Facades\Broadcast;

/*
 * Canal privado de chat — solo el propietario del canal puede suscribirse
 * Formato: chat.{userId}
 */
Broadcast::channel('chat.{userId}', function ($user, $userId) {
    return (string) $user->id === (string) $userId;
});
