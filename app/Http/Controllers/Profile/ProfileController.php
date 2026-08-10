<?php
namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function setup()
    {
        $profile = DB::table('profiles')->where('user_id', auth()->id())->first();
        $user    = auth()->user();
        return view('profile.setup', compact('profile', 'user'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nickname'     => 'required|string|min:3|max:30|alpha_dash',
            'profile_type' => 'required|in:single,pareja,unicornio',
            'display_name' => 'required|string|max:100',
            'age'          => 'required|integer|min:18|max:99',
            'gender'       => 'required|in:masculino,femenino,otro',
            'bio'          => 'nullable|string|max:500',
            'state'        => 'nullable|string|max:100',
            'city'         => 'nullable|string|max:100',
            'orientation'  => 'nullable|string|max:50',
        ], [
            'nickname.required'     => 'El nick es obligatorio.',
            'nickname.alpha_dash'   => 'El nick solo puede contener letras, numeros, guiones y guiones bajos.',
            'nickname.min'          => 'El nick debe tener al menos 3 caracteres.',
            'age.min'               => 'Debes tener al menos 18 anos.',
            'display_name.required' => 'El nombre es obligatorio.',
        ]);

        $userId  = auth()->id();
        $profile = DB::table('profiles')->where('user_id', $userId)->first();

        $nickFijo = $profile?->nickname ?? null;
        if (!$nickFijo) {
            $nickExists = DB::table('profiles')
                ->where('nickname', $request->nickname)
                ->where('user_id', '!=', $userId)
                ->exists();
            if ($nickExists) {
                return back()->withErrors(['nickname' => 'Este nick ya esta en uso. Elige otro.'])->withInput();
            }
        }

        $lookingFor      = $request->input('looking_for', []);
        $interests       = $request->input('interests', []);
        $privacySettings = $request->input('privacy_settings', []);
        $notifications   = $request->input('notifications', []);

        $data = [
            'nickname'             => $nickFijo ?? $request->nickname,
            'profile_type'         => $request->profile_type,
            'display_name'         => $request->display_name,
            'age'                  => (int) $request->age,
            'gender'               => $request->gender,
            'bio'                  => $request->bio ?? '',
            'state'                => $request->state ?? '',
            'city'                 => $request->city ?? '',
            'country'              => $request->country ?? 'Mexico',
            'orientation'          => $request->orientation ?? '',
            'looking_for'          => json_encode($lookingFor),
            'interests'            => json_encode($interests),
            'privacy_settings'     => json_encode($privacySettings),
            'notifications'        => json_encode($notifications),
            'profile_completed'    => true,
            'profile_completed_at' => Carbon::now()->toDateTimeString(),
            'updated_at'           => Carbon::now()->toDateTimeString(),
        ];

        if ($request->profile_type === 'pareja') {
            $data['partner_name']   = $request->partner_name ?? '';
            $data['partner_age']    = $request->partner_age ? (int)$request->partner_age : null;
            $data['partner_gender'] = $request->partner_gender ?? '';
            $data['partner_bio']    = $request->partner_bio ?? '';
        } else {
            $data['partner_name']   = null;
            $data['partner_age']    = null;
            $data['partner_gender'] = null;
            $data['partner_bio']    = null;
        }

        if ($profile) {
            DB::table('profiles')->where('user_id', $userId)->update($data);
        } else {
            $data['id']         = (string) Str::uuid();
            $data['user_id']    = $userId;
            $data['created_at'] = Carbon::now()->toDateTimeString();
            DB::table('profiles')->insert($data);
        }

        return redirect()->route('dashboard')
            ->with('success', 'Perfil guardado correctamente.');
    }

    public function edit()
    {
        $profile = DB::table('profiles')->where('user_id', auth()->id())->first();
        $user    = auth()->user();
        if (!$profile) return redirect()->route('profile.setup');
        return view('profile.edit', compact('profile', 'user'));
    }

    public function update(Request $request)
    {
        return $this->store($request);
    }

    public function publicShow($nickname)
    {
        $profile = DB::table('profiles')
            ->where('nickname', $nickname)
            ->whereRaw('profile_completed = true')
            ->first();

        if (!$profile) abort(404, 'Perfil no encontrado.');

        // Respetar privacidad: perfil marcado como no-público
        if (!$profile->public) {
            abort(404, 'Este perfil no está disponible.');
        }

        $user = DB::table('users')
            ->where('id', $profile->user_id)
            ->first();

        if (!$user || in_array($user->membership_type, ['banned', 'suspended'])) {
            abort(404);
        }

        $me           = auth()->id();
        $isOwnProfile = $me && (string)$me === (string)$profile->user_id;

        // Follows
        $isFollowing = false;
        if ($me && !$isOwnProfile) {
            $isFollowing = DB::table('follows')
                ->where('follower_id', $me)
                ->where('following_id', $profile->user_id)
                ->exists();
        }

        $followersCount = Cache::remember('followers_' . $profile->user_id, 300, function () use ($profile) {
            return DB::table('follows')->where('following_id', $profile->user_id)->count();
        });

        $followingCount = Cache::remember('following_' . $profile->user_id, 300, function () use ($profile) {
            return DB::table('follows')->where('follower_id', $profile->user_id)->count();
        });

        // Avatar
        $profilePhoto = DB::table('photos')
            ->where('user_id', $profile->user_id)
            ->whereRaw('is_profile_photo = true')
            ->where('status', 'approved')
            ->first();

        if (!$profilePhoto) {
            $profilePhoto = DB::table('photos')
                ->where('user_id', $profile->user_id)
                ->where('album_type', 'public')
                ->where('status', 'approved')
                ->orderBy('sort_order')
                ->orderBy('created_at')
                ->first();
        }

        $avatarPhotoId = $profilePhoto?->id ?? null;
        $avatarUrl     = $avatarPhotoId
            ? route('photos.serve', $avatarPhotoId)
            : asset('img/default-avatar.svg');

        // Fotos publicas
        $photos = DB::table('photos')
            ->where('user_id', $profile->user_id)
            ->where('album_type', 'public')
            ->where('status', 'approved')
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->limit(24)
            ->get();

        $photosCount = $photos->count();

        // Likes
        $photoUuids = $photos->pluck('photo_uuid')
            ->map(fn($u) => (string)$u)
            ->filter()
            ->toArray();

        $likesCount = count($photoUuids)
            ? DB::table('photo_likes')
                ->whereIn('photo_id', $photoUuids)
                ->count()
            : 0;

        $likeCounts = count($photoUuids)
            ? DB::table('photo_likes')
                ->whereIn('photo_id', $photoUuids)
                ->selectRaw('photo_id AS puuid, COUNT(*) AS cnt')
                ->groupBy('photo_id')
                ->pluck('cnt', 'puuid')
            : collect();

        $myLikes = collect();
        if ($me && count($photoUuids)) {
            $myLikes = DB::table('photo_likes')
                ->where('user_id', $me)
                ->whereIn('photo_id', $photoUuids)
                ->selectRaw('photo_id AS puuid')
                ->pluck('puuid')
                ->flip();
        }

        // Videos publicos
        $videos = DB::table('videos')
            ->where('user_id', $profile->user_id)
            ->where('album_type', 'public')
            ->where('status', 'approved')
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->limit(24)
            ->get();
        $videosCount = $videos->count();

        // Stats sidebar
        $sbPhotosCount = $photosCount;

        $sbReviews = Cache::remember('reviews_' . $profile->user_id, 300, function () use ($profile) {
            return DB::table('profile_reviews')->where('reviewed_id', $profile->user_id)->get();
        });

        $sbPos = $sbReviews->where('type', 'positive')->count();
        $sbNeg = $sbReviews->where('type', 'negative')->count();

        // Amigos en comun
        $commonFriends = collect();
        if ($me && !$isOwnProfile) {
            $meId = (string)$me;
            $uid  = (string)$profile->user_id;

            $profileFriendIds = DB::table('friendships')
                ->where('status', 'accepted')
                ->where(function ($q) use ($uid) {
                    $q->whereRaw('sender_id = ?', [$uid])
                      ->orWhereRaw('receiver_id = ?', [$uid]);
                })
                ->get()
                ->map(fn($f) => (string)$f->sender_id === $uid
                    ? (string)$f->receiver_id
                    : (string)$f->sender_id
                )
                ->toArray();

            $myFriendIds = DB::table('friendships')
                ->where('status', 'accepted')
                ->where(function ($q) use ($meId) {
                    $q->whereRaw('sender_id = ?', [$meId])
                      ->orWhereRaw('receiver_id = ?', [$meId]);
                })
                ->get()
                ->map(fn($f) => (string)$f->sender_id === $meId
                    ? (string)$f->receiver_id
                    : (string)$f->sender_id
                )
                ->toArray();

            $commonIds = array_values(array_intersect($profileFriendIds, $myFriendIds));

            if (count($commonIds)) {
                $commonFriends = DB::table('users as u')
                    ->leftJoin('profiles as pr', 'pr.user_id', '=', 'u.id')
                    ->whereIn('u.id', $commonIds)
                    ->select([
                        'u.id AS user_id',
                        DB::raw('COALESCE(pr.display_name, u.username) AS display_name'),
                        'pr.nickname',
                        DB::raw("(SELECT ap.id FROM photos ap
                                  WHERE ap.user_id = u.id
                                    AND ap.is_profile_photo = true
                                    AND ap.status = 'approved'
                                  LIMIT 1) AS avatar_id"),
                    ])
                    ->limit(6)
                    ->get();
            }
        }

        // Estado de amistad con el perfil visitado
        $friendshipStatus = null;
        $friendshipId     = null;
        if ($me && !$isOwnProfile) {
            $meId = (string)$me;
            $uid  = (string)$profile->user_id;
            $fr   = DB::table('friendships')
                ->where(function($q) use ($meId, $uid) {
                    $q->whereRaw('sender_id = ?', [$meId])
                      ->whereRaw('receiver_id = ?', [$uid]);
                })
                ->orWhere(function($q) use ($meId, $uid) {
                    $q->whereRaw('sender_id = ?', [$uid])
                      ->whereRaw('receiver_id = ?', [$meId]);
                })
                ->select(['id', 'status', 'sender_id'])
                ->first();
            if ($fr) {
                $friendshipStatus = $fr->status;
                $friendshipId     = (string)$fr->id;
            }
        }

        // Registrar visita
        if ($me && !$isOwnProfile) {
            try {
                DB::table('profile_views')->insert([
                    'id'        => (string) Str::uuid(),
                    'viewer_id' => (string) $me,
                    'viewed_id' => (string) $profile->user_id,
                    'viewed_at' => Carbon::now(),
                ]);
            } catch (\Exception $e) {}
        }

        // Ultimos perfiles visitados por el usuario logueado
        $recentlyVisited = collect();
        if ($me) {
            try {
                $recentlyVisited = DB::table('profile_views as pv')
                    ->join('profiles as pr', 'pr.user_id', '=', 'pv.viewed_id')
                    ->join('users as u',     'u.id', '=', 'pv.viewed_id')
                    ->whereRaw('pv.viewer_id = ?', [$me])
                    ->whereRaw('pv.viewed_id != ?', [$profile->user_id])
                    ->where('u.active', true)
                    ->select([
                        DB::raw('DISTINCT ON (pv.viewed_id) pv.viewed_id'),
                        'pr.nickname',
                        DB::raw('COALESCE(pr.display_name, u.username) AS display_name'),
                        'pr.profile_type',
                        'pr.verified_profile',
                        DB::raw("(SELECT ap.id FROM photos ap
                                  WHERE ap.user_id = pv.viewed_id
                                    AND ap.is_profile_photo = true
                                    AND ap.status = 'approved'
                                  LIMIT 1) AS avatar_id"),
                        'pv.viewed_at',
                    ])
                    ->orderByRaw('pv.viewed_id, pv.viewed_at DESC')
                    ->limit(6)
                    ->get();
            } catch (\Exception $e) {}
        }

        // Perfiles recomendados
        $recommendedProfiles = collect();
        if ($me) {
            try {
                $meCity = DB::table('profiles')
                    ->where('user_id', $me)
                    ->value('city');

                $alreadyFollowing = DB::table('follows')
                    ->where('follower_id', $me)
                    ->pluck('following_id')
                    ->toArray();
                $alreadyFollowing[] = (string)$me;
                $alreadyFollowing[] = (string)$profile->user_id;

                $recommendedProfiles = DB::table('profiles as pr')
                    ->join('users as u', 'u.id', '=', 'pr.user_id')
                    ->where('pr.profile_completed', true)
                    ->where('u.active', true)
                    ->whereNotIn('pr.user_id', $alreadyFollowing)
                    ->when($meCity, fn($q) => $q->where('pr.city', 'ilike', '%'.$meCity.'%'))
                    ->select([
                        'pr.nickname',
                        DB::raw('COALESCE(pr.display_name, u.username) AS display_name'),
                        'pr.profile_type',
                        'pr.city',
                        'pr.verified_profile',
                        DB::raw("(SELECT ap.id FROM photos ap
                                  WHERE ap.user_id = pr.user_id
                                    AND ap.is_profile_photo = true
                                    AND ap.status = 'approved'
                                  LIMIT 1) AS avatar_id"),
                    ])
                    ->orderByDesc('pr.last_active_at')
                    ->limit(5)
                    ->get();
            } catch (\Exception $e) {}
        }

        // Variables de presentacion
        $verificationStatus = $user->verification_status ?? null;

        $typeLabel = match($profile->profile_type ?? '') {
            'pareja'    => 'Pareja',
            'unicornio' => 'Unicornio',
            default     => 'Single',
        };

        $memberLabel = ucfirst($user->membership_type ?? 'trial');

        $memberIcon = match($user->membership_type ?? 'trial') {
            'explorer'   => asset('img/membership/explorer.png'),
            'connectors' => asset('img/membership/connectors.png'),
            'influencer' => asset('img/membership/influencer.png'),
            'vip_elite'  => asset('img/membership/vip-elite.png'),
            'Fundador'  => asset('img/membership/Fundador.png'),
            default      => asset('img/membership/trial.png'),
        };

        $isPairing = $profile->profile_type === 'pareja';
        $isUnicorn = $profile->profile_type === 'unicornio';

        $lookingFor = json_decode($profile->looking_for ?? '[]', true) ?? [];
        $interests  = json_decode($profile->interests   ?? '[]', true) ?? [];

        $allLookingFor = [
            'Parejas heterosexuales', 'Parejas bisexuales', 'Parejas (ella bisexual)',
            'Parejas (el bisexual)',  'Hombres heterosexuales', 'Hombres bisexuales',
            'Mujeres heterosexuales', 'Mujeres bisexuales',
        ];

        $allInterests = [
            'Intercambio completo', 'Intercambio light', 'Sexo en grupo', 'Trios',
            'Solo ellas', 'Mirar y ser vistos', 'Cuckold', 'Practicas BDSM',
            'Compartir fetiches', 'Cybersexo', 'Intercambio de fotos',
            'Sexo por separado', 'Relaciones abiertas', 'Amistad', 'Otros',
        ];

        return view('profile.show', compact(
            'profile', 'user', 'isOwnProfile', 'isFollowing',
            'friendshipStatus', 'friendshipId',
            'followersCount', 'followingCount',
            'avatarPhotoId', 'avatarUrl',
            'photos', 'photosCount',
            'videos', 'videosCount',
            'likesCount', 'likeCounts', 'myLikes',
            'sbPhotosCount', 'sbReviews', 'sbPos', 'sbNeg',
            'commonFriends',
            'recentlyVisited', 'recommendedProfiles',
            'verificationStatus', 'typeLabel', 'memberLabel', 'memberIcon',
            'isPairing', 'isUnicorn',
            'lookingFor', 'interests', 'allLookingFor', 'allInterests'
        ));
    }

    /**
     * GET /mis-visitas
     * Lista paginada de usuarios que visitaron el perfil del autenticado.
     * Avatares via photos.serve (nunca avatar_url legacy).
     */
    public function visitors()
    {
        $user = auth()->user();
        $uid  = (string) $user->id;

        $userProfile = DB::table('profiles')
            ->where('user_id', $uid)
            ->first();

        // Total de visitantes únicos (excluye visitas propias)
        $totalVisitors = DB::table('profile_views')
            ->where('viewed_id', $uid)
            ->where('viewer_id', '!=', $uid)
            ->distinct()
            ->count('viewer_id');

        // Una fila por visitante: la visita más reciente de cada uno
        // avatar_photo_id via subquery correlacionada (evita JOIN + GROUP BY con JSON)
        $visitors = DB::table('profile_views as pv')
            ->join('profiles as pr', 'pr.user_id', '=', 'pv.viewer_id')
            ->join('users as u',     'u.id', '=', 'pv.viewer_id')
            ->whereRaw('pv.viewed_id = ?', [$uid])
            ->whereRaw('pv.viewer_id != ?', [$uid])
            ->whereRaw('pv.viewed_at = (
                SELECT MAX(pv2.viewed_at)
                FROM profile_views pv2
                WHERE pv2.viewer_id = pv.viewer_id
                  AND pv2.viewed_id = pv.viewed_id
            )')
            ->where('u.active', true)
            ->select([
                'pr.nickname',
                'pr.profile_type',
                'pr.city',
                'pr.verified_profile',
                'pv.viewed_at',
                DB::raw("(
                    SELECT ph.id
                    FROM photos ph
                    WHERE ph.user_id = pv.viewer_id
                      AND ph.is_profile_photo = true
                      AND ph.status = 'approved'
                    LIMIT 1
                ) AS avatar_photo_id"),
            ])
            ->orderByDesc('pv.viewed_at')
            ->paginate(30);

        return view('profiles.visitors', compact('userProfile', 'visitors', 'totalVisitors'));
    }
}

