<?php
/**
 * fix_photos_feed.php
 * Resuelve:
 *   1. withCount UUID mismatch → usa selectRaw con cast
 *   2. Fotos no aparecen en el feed del dashboard
 *   3. feedAjax también corregido
 */

$base = __DIR__;

// ══════════════════════════════════════════════════════════════
// 1. REEMPLAZAR DashboardController completo con versión segura
// ══════════════════════════════════════════════════════════════
echo "[1/3] Corrigiendo DashboardController...\n";

$controller = <<<'PHP'
<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Photo;
use App\Models\ProfileView;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user    = auth()->user();
        $profile = $user->profile;
        $tab     = $request->get('tab', 'new');

        // ── Feed de fotos (sin withCount para evitar UUID mismatch) ──
        $feedQuery = Photo::with(['user.profile'])
            ->approved()
            ->where('album_type', 'public')
            ->where(DB::raw('user_id::text'), '!=', (string) $user->id)
            ->addSelect([
                '*',
                DB::raw('(SELECT COUNT(*) FROM photo_likes
                          WHERE photo_likes.photo_id::text = photos.id::text) as likes_count'),
                DB::raw('(SELECT COUNT(*) FROM photo_comments
                          WHERE photo_comments.photo_id::text = photos.id::text
                          AND photo_comments.status = \'approved\') as comments_count'),
            ]);

        if ($tab === 'likes') {
            $feedQuery->orderByDesc('likes_count');
        } else {
            $feedQuery->orderByDesc('created_at');
        }

        $feed = $feedQuery->paginate(12);

        // ── Quién vio mi perfil (últimas 5) ──
        $whoViewedMe = collect();
        $whoViewedMeCount = 0;
        try {
            $whoViewedMe = ProfileView::with('viewer.profile')
                ->where(DB::raw('viewed_id::text'), '=', (string) $user->id)
                ->where(DB::raw('viewer_id::text'), '!=', (string) $user->id)
                ->orderByDesc('viewed_at')
                ->limit(5)
                ->get();

            $whoViewedMeCount = ProfileView::where(DB::raw('viewed_id::text'), '=', (string) $user->id)
                ->where(DB::raw('viewer_id::text'), '!=', (string) $user->id)
                ->count();
        } catch (\Exception $e) {}

        // ── A quién vi (últimas 5) ──
        $iViewed = collect();
        $iViewedCount = 0;
        try {
            $iViewed = ProfileView::with('viewed.profile')
                ->where(DB::raw('viewer_id::text'), '=', (string) $user->id)
                ->orderByDesc('viewed_at')
                ->limit(5)
                ->get();

            $iViewedCount = ProfileView::where(DB::raw('viewer_id::text'), '=', (string) $user->id)
                ->count();
        } catch (\Exception $e) {}

        // ── Usuarios en línea ──
        $onlineUsers = collect();
        try {
            $onlineUsers = DB::table('users')
                ->join('profiles', DB::raw('users.id::text'), '=', DB::raw('profiles.user_id::text'))
                ->select('profiles.nickname', 'profiles.avatar_url', 'users.last_seen_at')
                ->where('users.last_seen_at', '>=', now()->subMinutes(10))
                ->where(DB::raw('users.id::text'), '!=', (string) $user->id)
                ->orderByDesc('users.last_seen_at')
                ->limit(12)
                ->get();
        } catch (\Exception $e) {}

        // ── Nuevos usuarios ──
        $newUsers = collect();
        try {
            $newUsers = DB::table('users')
                ->join('profiles', DB::raw('users.id::text'), '=', DB::raw('profiles.user_id::text'))
                ->select('profiles.nickname', 'profiles.avatar_url',
                         'profiles.profile_type', 'users.created_at')
                ->where('profiles.profile_completed', true)
                ->where(DB::raw('users.id::text'), '!=', (string) $user->id)
                ->orderByDesc('users.created_at')
                ->limit(5)
                ->get();
        } catch (\Exception $e) {}

        return view('dashboard.index', compact(
            'user', 'profile', 'feed', 'tab',
            'whoViewedMe', 'whoViewedMeCount',
            'iViewed', 'iViewedCount',
            'onlineUsers', 'newUsers'
        ));
    }

    // ── Feed AJAX (load more / tab switch) ──────────────────────
    public function feedAjax(Request $request)
    {
        $user = auth()->user();
        $tab  = $request->get('tab', 'new');
        $page = $request->get('page', 1);

        $feedQuery = Photo::with(['user.profile'])
            ->approved()
            ->where('album_type', 'public')
            ->where(DB::raw('user_id::text'), '!=', (string) $user->id)
            ->addSelect([
                '*',
                DB::raw('(SELECT COUNT(*) FROM photo_likes
                          WHERE photo_likes.photo_id::text = photos.id::text) as likes_count'),
                DB::raw('(SELECT COUNT(*) FROM photo_comments
                          WHERE photo_comments.photo_id::text = photos.id::text
                          AND photo_comments.status = \'approved\') as comments_count'),
            ]);

        if ($tab === 'likes') {
            $feedQuery->orderByDesc('likes_count');
        } else {
            $feedQuery->orderByDesc('created_at');
        }

        $feed = $feedQuery->paginate(12, ['*'], 'page', $page);

        // Renderizar tarjetas como HTML parcial
        $html = '';
        foreach ($feed as $photo) {
            $src       = $photo->thumbnail_path
                ? asset('storage/' . $photo->thumbnail_path)
                : asset('storage/' . $photo->file_path);
            $nick      = optional(optional($photo->user)->profile)->nickname ?? 'Usuario';
            $avatar    = optional(optional($photo->user)->profile)->avatar_url
                ? asset('storage/' . optional(optional($photo->user)->profile)->avatar_url)
                : asset('img/default-avatar.svg');
            $likes     = (int) $photo->likes_count;
            $comments  = (int) $photo->comments_count;
            $photoId   = $photo->id;

            $html .= <<<HTML
<div class="feed-card" data-photo-id="{$photoId}">
    <div class="feed-card-img-wrap">
        <img src="{$src}" alt="Foto" loading="lazy"
             onclick="openPhotoModal('{$photoId}')" />
    </div>
    <div class="feed-card-footer">
        <a href="/perfil/{$nick}" class="feed-card-user">
            <img src="{$avatar}" class="feed-card-avatar"
                 onerror="this.src='/img/default-avatar.svg'" />
            <span>{$nick}</span>
        </a>
        <div class="feed-card-actions">
            <button onclick="toggleLike('{$photoId}', this)" class="btn-like">
                ♥ <span class="like-count">{$likes}</span>
            </button>
            <button onclick="openPhotoModal('{$photoId}')" class="btn-comment">
                💬 {$comments}
            </button>
        </div>
    </div>
</div>
HTML;
        }

        return response()->json([
            'html'     => $html,
            'hasMore'  => $feed->hasMorePages(),
            'nextPage' => $feed->currentPage() + 1,
        ]);
    }

    // ── Like toggle ─────────────────────────────────────────────
    public function toggleLike(Request $request, string $photoId)
    {
        $userId = (string) auth()->id();

        $exists = DB::table('photo_likes')
            ->where(DB::raw('photo_id::text'), $photoId)
            ->where(DB::raw('user_id::text'), $userId)
            ->exists();

        if ($exists) {
            DB::table('photo_likes')
                ->where(DB::raw('photo_id::text'), $photoId)
                ->where(DB::raw('user_id::text'), $userId)
                ->delete();
            $liked = false;
        } else {
            DB::table('photo_likes')->insert([
                'photo_id'   => $photoId,
                'user_id'    => $userId,
                'created_at' => now(),
            ]);
            $liked = true;
        }

        $count = DB::table('photo_likes')
            ->where(DB::raw('photo_id::text'), $photoId)
            ->count();

        return response()->json(['liked' => $liked, 'count' => $count]);
    }

    // ── Datos del modal de foto ──────────────────────────────────
    public function photoModal(Request $request, string $photoId)
    {
        $photo = Photo::with(['user.profile'])
            ->addSelect([
                '*',
                DB::raw('(SELECT COUNT(*) FROM photo_likes
                          WHERE photo_likes.photo_id::text = photos.id::text) as likes_count'),
                DB::raw('(SELECT COUNT(*) FROM photo_comments
                          WHERE photo_comments.photo_id::text = photos.id::text
                          AND photo_comments.status = \'approved\') as comments_count'),
            ])
            ->where(DB::raw('id::text'), $photoId)
            ->firstOrFail();

        $comments = DB::table('photo_comments')
            ->join('users', DB::raw('photo_comments.user_id::text'), '=', DB::raw('users.id::text'))
            ->join('profiles', DB::raw('users.id::text'), '=', DB::raw('profiles.user_id::text'))
            ->select(
                'photo_comments.id',
                'photo_comments.body',
                'photo_comments.created_at',
                'profiles.nickname',
                'profiles.avatar_url'
            )
            ->where(DB::raw('photo_comments.photo_id::text'), $photoId)
            ->where('photo_comments.status', 'approved')
            ->orderBy('photo_comments.created_at')
            ->get();

        $userLiked = DB::table('photo_likes')
            ->where(DB::raw('photo_id::text'), $photoId)
            ->where(DB::raw('user_id::text'), (string) auth()->id())
            ->exists();

        return response()->json([
            'photo'     => [
                'id'        => $photo->id,
                'url'       => asset('storage/' . $photo->file_path),
                'caption'   => $photo->caption,
                'likes'     => (int) $photo->likes_count,
                'comments'  => (int) $photo->comments_count,
                'userLiked' => $userLiked,
                'nick'      => optional(optional($photo->user)->profile)->nickname,
                'avatar'    => optional(optional($photo->user)->profile)->avatar_url
                    ? asset('storage/' . optional(optional($photo->user)->profile)->avatar_url)
                    : asset('img/default-avatar.svg'),
            ],
            'comments' => $comments,
        ]);
    }
}
PHP;

$path = $base . '/app/Http/Controllers/DashboardController.php';
file_put_contents($path, $controller);
echo "  [OK] DashboardController.php reescrito\n";

// ══════════════════════════════════════════════════════════════
// 2. AGREGAR RUTAS para toggleLike y photoModal si no existen
// ══════════════════════════════════════════════════════════════
echo "\n[2/3] Verificando rutas de feed...\n";

$routesPath = $base . '/routes/web.php';
$routes     = file_get_contents($routesPath);

$routesToAdd = '';

if (strpos($routes, 'dashboard.feed.ajax') === false) {
    $routesToAdd .= "\n    Route::get('/dashboard/feed', [App\Http\Controllers\DashboardController::class, 'feedAjax'])->name('dashboard.feed.ajax');";
}
if (strpos($routes, 'dashboard.like') === false) {
    $routesToAdd .= "\n    Route::post('/dashboard/like/{photo}', [App\Http\Controllers\DashboardController::class, 'toggleLike'])->name('dashboard.like');";
}
if (strpos($routes, 'dashboard.photo.modal') === false) {
    $routesToAdd .= "\n    Route::get('/dashboard/photo/{photo}', [App\Http\Controllers\DashboardController::class, 'photoModal'])->name('dashboard.photo.modal');";
}

if ($routesToAdd) {
    // Insertar antes del último cierre de grupo auth
    $routes = preg_replace(
        '/(Route::middleware\([^\)]+\)->group\(function\s*\(\)\s*\{)([\s\S]*?)(\}\);?\s*$)/m',
        '$1$2' . $routesToAdd . "\n$3",
        $routes,
        1
    );
    file_put_contents($routesPath, $routes);
    echo "  [OK] Rutas agregadas: feedAjax, toggleLike, photoModal\n";
} else {
    echo "  [INFO] Rutas ya existen\n";
}

// ══════════════════════════════════════════════════════════════
// 3. LIMPIAR CACHÉ
// ══════════════════════════════════════════════════════════════
echo "\n[3/3] Limpiando caché...\n";
shell_exec('C:\\php\\php.exe artisan view:clear 2>&1');
shell_exec('C:\\php\\php.exe artisan route:clear 2>&1');
shell_exec('C:\\php\\php.exe artisan config:clear 2>&1');
echo "  [OK] Caché limpiado\n";

echo "\n══════════════════════════════════════════\n";
echo "  LISTO — Ejecuta: php artisan serve\n";
echo "  Luego abre: http://localhost:8000/dashboard\n";
echo "══════════════════════════════════════════\n";
