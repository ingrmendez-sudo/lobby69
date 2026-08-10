<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Jobs\SendFollowNotification;
use Illuminate\Support\Facades\Log;


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
            ? DB::table('users')->whereRaw('id::text = ?', [$profile->user_id])->first()
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
            try {
                DB::table('notifications')->insert([
                    'id'              => \Illuminate\Support\Str::uuid(),
                    'type'            => 'follow',
                    'notifiable_type' => 'App\\Models\\User',
                    'notifiable_id'   => (string)$target->id,
                    'data'            => json_encode([
                        'from_nick'   => $followerNick ?? 'Alguien',
                        'follower_id' => (string)$me,
                    ]),
                    'read_at'    => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Throwable $e) {
                Log::warning('Follow notification failed: ' . $e->getMessage());
            }

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
            ? DB::table('users')->whereRaw('id::text = ?', [$profile->user_id])->first()
            : null;

        if (!$target) {
            return back()->with('error', 'Usuario no encontrado.');
        }

        $me = Auth::id();

        DB::table('follows')
            ->where('follower_id', $me)
            ->where('following_id', $target->id)
            ->delete();

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

        // Avatar en una sola query con LEFT JOIN
        $userIds = $followers->pluck('user_id')->map(fn($id) => (string)$id)->toArray();
        $avatars = DB::table('photos')
            ->whereRaw('user_id::text IN (' . implode(',', array_fill(0, count($userIds), '?')) . ')', $userIds)
            ->whereRaw('is_profile_photo = true')
            ->where('status', 'approved')
            ->select(['id', DB::raw('user_id::text as uid'), 'file_path'])
            ->get()
            ->keyBy('uid');

        $followers = $followers->map(function ($f) use ($avatars) {
            $uid = (string)$f->user_id;
            $av  = $avatars->get($uid);
            $f->avatar_url = $av ? route('photos.serve', $av->id) : null;
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

        // Avatar en una sola query con LEFT JOIN
        $userIds = $following->pluck('user_id')->map(fn($id) => (string)$id)->toArray();
        $avatars = count($userIds) ? DB::table('photos')
            ->whereRaw('user_id::text IN (' . implode(',', array_fill(0, count($userIds), '?')) . ')', $userIds)
            ->whereRaw('is_profile_photo = true')
            ->where('status', 'approved')
            ->select(['id', DB::raw('user_id::text as uid'), 'file_path'])
            ->get()
            ->keyBy('uid') : collect();

        $following = $following->map(function ($f) use ($avatars) {
            $uid = (string)$f->user_id;
            $av  = $avatars->get($uid);
            $f->avatar_url = $av ? route('photos.serve', $av->id) : null;
            return $f;
        });

        $followersCount = DB::table('follows')
            ->where('following_id', $me)
            ->count();

        return view('follows.index', compact('following', 'followersCount'));
    }
}

