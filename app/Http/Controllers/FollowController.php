<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
class FollowController extends Controller
{
    /**
     * Seguir a un usuario.
     * POST /seguir/{nickname}
     */
    public function follow(string $nickname)
    {
        // Buscar por nickname en profiles, luego obtener el user
        $profile = DB::table('profiles')->where('nickname', $nickname)->first();
        $target  = $profile
            ? DB::table('users')->where('id', $profile->user_id)->first()
            : null;

        if (!$target) {
            return back()->with('error', 'Usuario no encontrado.');
        }

        $me = Auth::id();

        if ($me === $target->id) {
            return back()->with('error', 'No puedes seguirte a ti mismo.');
        }

        // Insertar solo si no existe (evita duplicados)
        $exists = DB::table('follows')
            ->where('follower_id', $me)
            ->where('following_id', $target->id)
            ->exists();

        if (!$exists) {
            DB::table('follows')->insert([
                'follower_id'  => $me,
                'following_id' => $target->id,
                'created_at'   => now(),
            ]);

            // Notificar al usuario seguido
            $followerNick = DB::table('profiles')
                ->where('user_id', $me)
                ->value('nickname');
            Cache::forget('followers_' . $target->id);
            Cache::forget('following_' . $me);
            NotificationController::create((string)$target->id, 'follow', [
                'from_nick'   => $followerNick ?? 'Alguien',
                'follower_id' => (string)$me,
            ]);
        }

        return back()->with('success', 'Ahora sigues a @' . $nickname . '.');
    }

    /**
     * Dejar de seguir a un usuario.
     * DELETE /seguir/{nickname}
     */
    public function unfollow(string $nickname)
    {
        $profile = DB::table('profiles')->where('nickname', $nickname)->first();
        $target  = $profile
            ? DB::table('users')->where('id', $profile->user_id)->first()
            : null;

        if (!$target) {
            return back()->with('error', 'Usuario no encontrado.');
        }

        $me = Auth::id();

        DB::table('follows')
            ->where('follower_id', $me)
            ->where('following_id', $target->id)
            ->delete();

        Cache::forget('followers_' . $target->id);
        Cache::forget('following_' . $me);

        return back()->with('success', 'Dejaste de seguir a @' . $nickname . '.');
    }

    /**
     * Lista de seguidores del usuario autenticado.
     * GET /mis-seguidores
     */
    public function followers()
    {
        $me = Auth::id();

        $followers = DB::table('follows')
            ->join('users', 'follows.follower_id', '=', 'users.id')
            ->join('profiles', 'users.id', '=', 'profiles.user_id')
            ->where('follows.following_id', $me)
            ->select(
                'profiles.nickname',
                'profiles.profile_type',
                'profiles.city',
                'profiles.is_profile_photo',
                'users.id as user_id',
                'follows.created_at as followed_at'
            )
            ->orderByDesc('follows.created_at')
            ->get();

        // Avatar para cada seguidor
        $followers = $followers->map(function ($f) {
            $avatar = DB::table('photos')
                ->where('user_id', $f->user_id)
                ->where('is_profile_photo', true)
                ->where('status', 'approved')
                ->value('file_path');

            if (!$avatar) {
                $avatar = DB::table('photos')
                    ->where('user_id', $f->user_id)
                    ->where('album_type', 'public')
                    ->where('status', 'approved')
                    ->orderBy('sort_order')
                    ->value('file_path');
            }

            $f->avatar_url = $avatar
                ? route('photos.serve', DB::table('photos')
                    ->where('file_path', $avatar)
                    ->value('id'))
                : null;

            return $f;
        });

        // Contar seguidos
        $followingCount = DB::table('follows')
            ->where('follower_id', $me)
            ->count();

        return view('follows.index', compact('followers', 'followingCount'));
    }

    /**
     * Lista de usuarios que sigue el autenticado.
     * GET /siguiendo
     */
    public function following()
    {
        $me = Auth::id();

        $following = DB::table('follows')
            ->join('users', 'follows.following_id', '=', 'users.id')
            ->join('profiles', 'users.id', '=', 'profiles.user_id')
            ->where('follows.follower_id', $me)
            ->select(
                'profiles.nickname',
                'profiles.profile_type',
                'profiles.city',
                'users.id as user_id',
                'follows.created_at as followed_at'
            )
            ->orderByDesc('follows.created_at')
            ->get();

        // Avatar para cada seguido
        $following = $following->map(function ($f) {
            $avatar = DB::table('photos')
                ->where('user_id', $f->user_id)
                ->where('is_profile_photo', true)
                ->where('status', 'approved')
                ->value('file_path');

            if (!$avatar) {
                $avatar = DB::table('photos')
                    ->where('user_id', $f->user_id)
                    ->where('album_type', 'public')
                    ->where('status', 'approved')
                    ->orderBy('sort_order')
                    ->value('file_path');
            }

            $f->avatar_url = $avatar
                ? route('photos.serve', DB::table('photos')
                    ->where('file_path', $avatar)
                    ->value('id'))
                : null;

            return $f;
        });

        $followersCount = DB::table('follows')
            ->where('following_id', $me)
            ->count();

        return view('follows.index', compact('following', 'followersCount'));
    }
}

