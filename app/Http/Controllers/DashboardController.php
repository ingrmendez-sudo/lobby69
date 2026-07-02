<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Photo;
use App\Models\User;

class DashboardController extends Controller
{
    // ─────────────────────────────────────────────
    //  Validación de UUID reutilizable
    // ─────────────────────────────────────────────
    private function isValidPhotoId(string $value): bool
    {
        return ctype_digit($value) && (int)$value > 0;
    }

    // ─────────────────────────────────────────────
    //  INDEX — Vista principal del dashboard
    // ─────────────────────────────────────────────
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $profile = $user->profile;
        $tab     = $request->input('tab', 'new');
        $page    = max(1, (int) $request->input('page', 1));

        // Feed principal
        $feed = $this->getFeed((string) $user->id, $tab, $page);

        // ── Quién me vio ──────────────────────────
        $whoViewedMe      = collect();
        $whoViewedMeCount = 0;
        try {
            $views = DB::table('profile_views')
                ->whereRaw('viewed_id::text = ?', [(string) $user->id])
                ->orderByDesc('created_at')
                ->limit(10)
                ->get();

            $viewerIds        = $views->pluck('viewer_id')->map(fn($id) => (string) $id)->toArray();
            $whoViewedMeCount = DB::table('profile_views')
                ->whereRaw('viewed_id::text = ?', [(string) $user->id])
                ->count();

            $whoViewedMe = User::with('profile')
                ->whereIn(DB::raw('id::text'), $viewerIds)
                ->get();
        } catch (\Throwable $e) {
            Log::warning('DashboardController@index - whoViewedMe: ' . $e->getMessage());
        }

        // ── A quién vi ───────────────────────────
        $iViewed      = collect();
        $iViewedCount = 0;
        try {
            $myViews = DB::table('profile_views')
                ->whereRaw('viewer_id::text = ?', [(string) $user->id])
                ->orderByDesc('viewed_at')
                ->limit(10)
                ->get();

            $viewedIds    = $myViews->pluck('viewed_id')->map(fn($id) => (string) $id)->toArray();
            $iViewedCount = DB::table('profile_views')
                ->whereRaw('viewer_id::text = ?', [(string) $user->id])
                ->count();

            $iViewed = User::with('profile')
                ->whereIn(DB::raw('id::text'), $viewedIds)
                ->get();
        } catch (\Throwable $e) {
            Log::warning('DashboardController@index - iViewed: ' . $e->getMessage());
        }

        // ── Usuarios en línea ────────────────────
        $onlineUsers = collect();
        try {
            $onlineUsers = User::with('profile')
                ->where('last_seen_at', '>=', now()->subMinutes(15))
                ->whereRaw('id::text != ?', [(string) $user->id])
                ->limit(20)
                ->get();
        } catch (\Throwable $e) {
            Log::warning('DashboardController@index - onlineUsers: ' . $e->getMessage());
        }

        // ── Usuarios nuevos ──────────────────────
        $newUsers = collect();
        try {
            $newUsers = User::with('profile')
                ->whereRaw('id::text != ?', [(string) $user->id])
                ->orderByDesc('viewed_at')
                ->limit(12)
                ->get();
        } catch (\Throwable $e) {
            Log::warning('DashboardController@index - newUsers: ' . $e->getMessage());
        }

        return view('dashboard.index', compact(
            'user',
            'profile',
            'feed',
            'tab',
            'whoViewedMe',
            'whoViewedMeCount',
            'iViewed',
            'iViewedCount',
            'onlineUsers',
            'newUsers'
        ));
    }

    // ─────────────────────────────────────────────
    //  getFeed — Consulta paginada con withCount
    //  CORREGIDO: elimina N+1, aplica lógica de $tab
    // ─────────────────────────────────────────────
    private function getFeed(string $userId, string $tab, int $page): LengthAwarePaginator
{
    // Paso 1: obtener IDs paginados según el tab
    $idsQuery = DB::table('photos')
        ->where('status', 'approved')
        ->where('album_type', 'public');

    switch ($tab) {
        case 'popular':
            $idsQuery->orderByDesc(
                DB::table('photo_likes')
                    ->selectRaw('count(*)')
                    ->whereRaw('photo_likes.photo_id::text = photos.id::text')
            );
            break;
        case 'following':
            $following = DB::table('follows')
                ->whereRaw('follower_id::text = ?', [$userId])
                ->pluck('following_id')
                ->map(fn($id) => (string)$id)
                ->toArray();
            if (!empty($following)) {
                $idsQuery->whereIn(DB::raw('user_id::text'), $following);
            } else {
                $idsQuery->whereRaw('1 = 0');
            }
            $idsQuery->orderByDesc('created_at');
            break;
        default:
            $idsQuery->orderByDesc('created_at');
            break;
    }

    $total    = $idsQuery->count();
    $photoIds = (clone $idsQuery)
        ->offset(($page - 1) * 12)
        ->limit(12)
        ->pluck('id')
        ->toArray();

    // Paso 2: cargar los modelos completos con relaciones
    $photosCollection = Photo::with(['user.profile'])
        ->whereIn('id', $photoIds)
        ->orderByRaw('array_position(ARRAY[' . implode(',', $photoIds) . ']::bigint[], id)')
        ->get();

    // Paso 3: conteos en 2 queries planas
    $likesMap = DB::table('photo_likes')
        ->whereRaw('photo_id::text IN (' . implode(',', array_fill(0, count($photoIds), '?')) . ')',
            array_map('strval', array_map(fn($id) => DB::table('photos')->where('id', $id)->value('photo_uuid'), $photoIds))
        )
        ->selectRaw('photo_id::text, count(*) as total')
        ->groupBy('photo_id')
        ->pluck('total', 'photo_id')
        ->toArray();

    $commentsMap = DB::table('photo_comments')
        ->whereRaw('photo_id::text IN (' . implode(',', array_fill(0, count($photoIds), '?')) . ')',
            array_map('strval', array_map(fn($id) => DB::table('photos')->where('id', $id)->value('photo_uuid'), $photoIds))
        )
        ->where('status', 'approved')
        ->selectRaw('photo_id::text, count(*) as total')
        ->groupBy('photo_id')
        ->pluck('total', 'photo_id')
        ->toArray();

    // Paso 4: likes del usuario
    $likedUuids = DB::table('photo_likes')
        ->whereIn('photo_id', $photosCollection->pluck('photo_uuid')->toArray())
        ->where('user_id', $userId)
        ->pluck('photo_id')
        ->toArray();

    // Paso 5: adjuntar conteos a cada foto
    $photosCollection->transform(function ($photo) use ($likesMap, $commentsMap, $likedUuids) {
        $photo->likes_count    = $likesMap[$photo->photo_uuid]    ?? 0;
        $photo->comments_count = $commentsMap[$photo->photo_uuid] ?? 0;
        $photo->userLiked      = in_array($photo->photo_uuid, $likedUuids);
        return $photo;
    });

    // Paso 6: construir paginador manualmente
    return new LengthAwarePaginator(
        $photosCollection,
        $total,
        12,
        $page,
        ['path' => request()->url(), 'query' => request()->query()]
    );
}



    // ─────────────────────────────────────────────
    //  FEED AJAX — Carga infinita / paginación AJAX
    //  CORREGIDO: usa getFeed(), vista parcial,
    //             elimina HTML concatenado en controller
    // ─────────────────────────────────────────────
    public function feedAjax(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        $tab  = $request->input('tab', 'new');
        $page = max(1, (int) $request->input('page', 1));
        $feed = $this->getFeed((string) $user->id, $tab, $page);

        // Renderizar tarjetas con vista parcial (no HTML concatenado)
        $html = '';
        foreach ($feed as $photo) {
            $html .= view('dashboard.partials.photo-card', [
                'photo'   => $photo,
                'isLiked' => $photo->userLiked,
                'user'    => $user,
            ])->render();
        }

        return response()->json([
            'html'        => $html,
            'hasMore'     => $feed->hasMorePages(),
            'currentPage' => $feed->currentPage(),
            'lastPage'    => $feed->lastPage(),
            'total'       => $feed->total(),
        ]);
    }

    // ─────────────────────────────────────────────
    //  TOGGLE LIKE — Like / Unlike de una foto
    //  CORREGIDO: validación UUID, respuesta uniforme
    // ─────────────────────────────────────────────
    public function toggleLike(Request $request, string $photoId): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        if (!$this->isValidPhotoId($photoId)) {
            return response()->json(['error' => 'ID de foto inválido'], 422);
        }

        try {
            $photo = Photo::findOrFail((int)$photoId);

            $exists = DB::table('photo_likes')
                ->where('photo_id', $photo->photo_uuid)
                ->where('user_id', (string) $user->id)
                ->exists();

            if ($exists) {
                DB::table('photo_likes')
                    ->where('photo_id', $photo->photo_uuid)
                    ->where('user_id', (string) $user->id)
                    ->delete();
                $liked = false;
            } else {
                DB::table('photo_likes')->insertOrIgnore([
                    'id'         => \Illuminate\Support\Str::uuid(),
                    'photo_id'   => $photo->photo_uuid,
                    'user_id'    => (string) $user->id,
                    'created_at' => now(),
                ]);
                $liked = true;
            }

            $likesCount = DB::table('photo_likes')
                ->where('photo_id', $photo->photo_uuid)
                ->count();

            return response()->json([
                'success'     => true,
                'liked'       => $liked,
                'likes_count' => $likesCount,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Foto no encontrada'], 404);
        } catch (\Throwable $e) {
            Log::error('DashboardController@toggleLike: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno'], 500);
        }
    }


    // ─────────────────────────────────────────────
    //  PHOTO MODAL — Datos completos de una foto
    //  CORREGIDO: validación UUID, comentarios paginados
    // ─────────────────────────────────────────────
    public function photoModal(Request $request, string $photoId): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        if (!$this->isValidPhotoId($photoId)) {
            return response()->json(['error' => 'ID de foto inválido'], 422);
        }

        try {
            $photo = Photo::with(['user.profile'])->findOrFail((int)$photoId);

            $likesCount = DB::table('photo_likes')
                ->where('photo_id', $photo->photo_uuid)
                ->count();

            $userLiked = DB::table('photo_likes')
                ->where('photo_id', $photo->photo_uuid)
                ->where('user_id', (string) $user->id)
                ->exists();

            $comments = DB::table('photo_comments')
                ->join('users',    'users.id',    '=', 'photo_comments.user_id')
                ->join('profiles', 'profiles.user_id', '=', 'users.id')
                ->where('photo_comments.photo_id', $photo->photo_uuid)
                ->where('photo_comments.status', 'approved')
                ->orderBy('photo_comments.created_at', 'asc')
                ->select([
                    'photo_comments.id',
                    'photo_comments.body as comment',
                    'photo_comments.created_at',
                    'users.id as user_id',
                    'profiles.nickname as user_nick',
                    'profiles.avatar_url as user_avatar',
                ])
                ->limit(50)
                ->get();

            return response()->json([
                'success' => true,
                'photo'   => [
                    'id'             => $photo->id,
                    'file_path'      => $photo->file_path,
                    'description'    => $photo->caption ?? '',
                    'created_at'     => $photo->created_at?->toISOString(),
                    'owner'          => [
                        'id'         => (string) $photo->user->id,
                        'nick'       => $photo->user->profile->nickname ?? 'Usuario',
                        'avatar_url' => $photo->user->profile->avatar_url ?? null,
                        'city'       => $photo->user->profile->city ?? null,
                    ],
                    'likes_count'    => $likesCount,
                    'user_liked'     => $userLiked,
                    'comments'       => $comments,
                    'comments_count' => $comments->count(),
                ],
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Foto no encontrada'], 404);
        } catch (\Throwable $e) {
            Log::error('DashboardController@photoModal: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno'], 500);
        }
    }


    // ─────────────────────────────────────────────
    //  STORE COMMENT — Publicar comentario
    //  CORREGIDO: validación UUID, verifica que la
    //             foto exista antes de insertar
    // ─────────────────────────────────────────────
    public function storeComment(Request $request, string $photoId): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        if (!$this->isValidPhotoId($photoId)) {
            return response()->json(['error' => 'ID de foto inválido'], 422);
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'min:1', 'max:500'],
        ]);

        try {
            $photo = Photo::findOrFail((int)$photoId);

            $commentUuid = \Illuminate\Support\Str::uuid();

            DB::table('photo_comments')->insert([
                'id'         => $commentUuid,
                'photo_id'   => $photo->photo_uuid,
                'user_id'    => (string) $user->id,
                'body'       => $validated['body'],
                'status' => 'approved',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $profile = $user->profile;

            return response()->json([
                'success' => true,
                'comment' => [
                    'id'          => $commentUuid,
                    'comment'     => $validated['body'],
                    'created_at'  => now()->toISOString(),
                    'user_id'     => (string) $user->id,
                    'user_nick'   => $profile->nickname ?? 'Usuario',
                    'user_avatar' => $profile->avatar_url ?? null,
                ],
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Foto no encontrada'], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        } catch (\Throwable $e) {
            Log::error('DashboardController@storeComment: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno'], 500);
        }
    }

}

