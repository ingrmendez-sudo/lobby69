<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// ── Auth Controllers ──────────────────────────────────────────────────────────
use App\Http\Controllers\Auth\LoginController;

// ── App Controllers ───────────────────────────────────────────────────────────
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Profile\ProfileController;
use App\Http\Controllers\Verification\VerificationController;
use App\Http\Controllers\Photo\PhotoController;
use App\Http\Controllers\Photo\PhotoInteractionController;
use App\Http\Controllers\Video\VideoController;
use App\Http\Controllers\ExploreController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\SearchController;

// ── Admin Controllers ─────────────────────────────────────────────────────────
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminInvitationController;
use App\Http\Controllers\Admin\AdminVerificationController;
use App\Http\Controllers\Admin\AdminPhotoController;
use App\Http\Controllers\Admin\AdminVideoController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminStatsController;
use App\Http\Controllers\Admin\AdminEventController;
use App\Http\Controllers\Admin\AdminArticleController;
use App\Http\Controllers\Admin\AdminMembershipController;
use App\Http\Controllers\Admin\AdminArticleCommentController;

// ── Landing ───────────────────────────────────────────────────────────────────
Route::get('/', function () {
    if (Auth::check()) return redirect()->route('dashboard');
    return view('auth.landing');
})->name('landing');

// ── Auth ──────────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',  [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// ── Invitación pública ────────────────────────────────────────────────────────
Route::get('/invitacion',  [\App\Http\Controllers\Auth\InvitationController::class, 'show'])->name('invitation.show');
Route::post('/invitacion', [\App\Http\Controllers\Auth\InvitationController::class, 'store'])->name('invitation.store');

// ── App (requiere auth) ───────────────────────────────────────────────────────
// Videos — servir archivo (público, fuera de auth)
Route::get('/videos/{id}/ver', [VideoController::class, 'serve'])->name('videos.serve.public');

Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard',      [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/feed', [DashboardController::class, 'feedAjax'])->name('dashboard.feed');
    Route::get('/dashboard/feed-ajax', [DashboardController::class, 'feedAjax'])->name('dashboard.feedAjax');

    // Mensajes
    Route::get('/mensajes',                                    [\App\Http\Controllers\MessagesController::class, 'index'])->name('messages.index');
    Route::get('/mensajes/conversacion/{partnerId}',           [\App\Http\Controllers\MessagesController::class, 'conversation'])->name('messages.conversation');
    Route::post('/mensajes/enviar',                            [\App\Http\Controllers\MessagesController::class, 'send'])->name('messages.send');
    Route::post('/mensajes/amistad/{friendshipId}',            [\App\Http\Controllers\MessagesController::class, 'friendAction'])->name('messages.friend.action');
    Route::post('/amigos/solicitud',                           [\App\Http\Controllers\MessagesController::class, 'sendFriendRequest'])->name('friends.request');
    Route::post('/mensajes/recomendar',                        [\App\Http\Controllers\MessagesController::class, 'review'])->name('messages.review');
    Route::post('/mensajes/anuncio',                           [\App\Http\Controllers\MessagesController::class, 'storeAnnouncement'])->name('messages.announcement.store');
    Route::patch('/mensajes/anuncio/{id}/cerrar',              [\App\Http\Controllers\MessagesController::class, 'closeAnnouncement'])->name('messages.announcement.close');

    // Perfil
    Route::get('/perfil/configurar',  [ProfileController::class, 'setup'])->name('profile.setup');
    Route::post('/perfil/configurar', [ProfileController::class, 'store'])->name('profile.store');
    Route::get('/perfil/editar',      [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/perfil/editar',      [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/u/{nickname}',       [ProfileController::class, 'publicShow'])->name('profile.show');
    Route::get('/mis-visitas',        [ProfileController::class, 'visitors'])->name('profile.visitors');

    // Verificación
    Route::get('/verificacion',           [VerificationController::class, 'show'])->name('verification.show');
    Route::post('/verificacion',          [VerificationController::class, 'store'])->name('verification.store');
    Route::get('/verificacion/estado',    [VerificationController::class, 'status'])->name('verification.status');
    Route::get('/verificacion/pendiente', [VerificationController::class, 'pending'])->name('verification.pending');

    // Fotos
    Route::get('/mis-fotos',                     [PhotoController::class, 'index'])->name('photos.index');
    Route::post('/fotos',                         [PhotoController::class, 'store'])->name('photos.store');
    Route::post('/fotos/{id}/perfil',             [PhotoController::class, 'setProfilePhoto'])->name('photos.setProfile');
    Route::delete('/fotos/{id}',                  [PhotoController::class, 'destroy'])->name('photos.destroy');
    Route::get('/fotos/{id}/ver',                 [PhotoController::class, 'serve'])->name('photos.serve');
    Route::get('/fotos/{id}/thumb',               [PhotoController::class, 'serveThumb'])->name('photos.thumb');
    Route::get('/fotos/{id}/info',                [DashboardController::class, 'photoModal'])->name('photos.info');
    Route::post('/fotos/{id}/like',               [DashboardController::class, 'toggleLike'])->name('photos.like');
    Route::post('/fotos/{id}/comentar',           [DashboardController::class, 'storeComment'])->name('photos.comment');
    Route::post('/fotos/{photoId}/comentarios/{commentId}/reply', [DashboardController::class, 'replyComment'])->name('photos.comment.reply');

    // Videos — gestión
    Route::get('/mis-videos',          [VideoController::class, 'index'])->name('videos.index');
    Route::post('/videos',             [VideoController::class, 'store'])->name('videos.store');
    Route::delete('/videos/{id}',      [VideoController::class, 'destroy'])->name('videos.destroy');

    // Videos — galería pública
    Route::get('/videos', [\App\Http\Controllers\Video\VideoGalleryController::class, 'index'])->name('videos.gallery');

    // Videos — stream (byte-range streaming)
    Route::get('/videos/{id}/stream', function ($id) {
        $user  = auth()->user();
        $video = DB::table('videos')->where('id', $id)->first();

        if (!$video) {
            return response('Not Found', 404)->header('Content-Type', 'text/plain');
        }

        // Control de acceso por tipo de álbum
        if ($video->album_type === 'private') {
            if (!\App\Services\MembershipService::can($user->id, 'can_view_private_photos')) {
                return response('Forbidden: necesitas membresia Connectors.', 403)
                    ->header('Content-Type', 'text/plain');
            }
        }
        if ($video->album_type === 'vip') {
            if (!\App\Services\MembershipService::hasMinLevel($user->id, 'vip_elite')) {
                return response('Forbidden: necesitas membresia VIP Elite.', 403)
                    ->header('Content-Type', 'text/plain');
            }
        }
        if ($video->status !== 'approved') {
            if ((string) $video->user_id !== (string) $user->id) {
                return response('Forbidden: video pendiente de aprobacion.', 403)
                    ->header('Content-Type', 'text/plain');
            }
        }

        // Generar URL firmada de Supabase (expira en 5 minutos)
                // Generar URL firmada de Supabase — cacheada 4 min para evitar latencia
        $cacheKey = 'stream_url_' . $video->id . '_' . $user->id;

        try {
            $signedUrl = \Illuminate\Support\Facades\Cache::remember(
                $cacheKey,
                now()->addMinutes(4),
                function () use ($video) {
                    return \Illuminate\Support\Facades\Storage::disk('supabase')
                        ->temporaryUrl($video->file_path, now()->addMinutes(5));
                }
            );
        } catch (\Throwable $e) {

            // Fallback: intentar servir desde disco local si existe
            $localPath = storage_path('app/private/' . ltrim($video->file_path, '/'));
            if (!file_exists($localPath)) {
                return response('Not Found: archivo no existe.', 404)
                    ->header('Content-Type', 'text/plain');
            }
            $signedUrl = null;
        }

        if ($signedUrl) {
            return redirect($signedUrl);
        }

        // Fallback local (solo desarrollo)
        $path = storage_path('app/private/' . ltrim($video->file_path, '/'));
        $ext     = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mimeMap = ['mp4' => 'video/mp4', 'mov' => 'video/quicktime', 'avi' => 'video/x-msvideo', 'webm' => 'video/webm'];
        $mime    = $mimeMap[$ext] ?? 'video/mp4';
        $size    = filesize($path);

        $start  = 0;
        $end    = $size - 1;
        $status = 200;

        $headers = [
            'Content-Type'           => $mime,
            'Accept-Ranges'          => 'bytes',
            'Cache-Control'          => 'private, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
            'X-Accel-Buffering'      => 'no',
        ];

        if (request()->hasHeader('Range')) {
            preg_match('/bytes=(\d+)-(\d*)/', request()->header('Range'), $m);
            $start  = (int) ($m[1] ?? 0);
            $end    = (isset($m[2]) && $m[2] !== '') ? (int) $m[2] : $size - 1;
            $end    = min($end, $size - 1);
            $status = 206;
            $headers['Content-Range'] = "bytes {$start}-{$end}/{$size}";
        }

        $length = $end - $start + 1;
        $headers['Content-Length'] = $length;

        return response()->stream(function () use ($path, $start, $length) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            $f   = fopen($path, 'rb');
            fseek($f, $start);
            $rem = $length;
            while (!feof($f) && $rem > 0) {
                $chunk = min(65536, $rem);
                echo fread($f, $chunk);
                $rem -= $chunk;
                flush();
            }
            fclose($f);
        }, $status, $headers);

    })->name('videos.stream');

    // Videos — contadores e interacciones
    Route::post('/videos/{id}/view',    [\App\Http\Controllers\Video\VideoInteractionController::class, 'recordView'])->name('videos.view');
    Route::get('/videos/{id}/likes',    [\App\Http\Controllers\Video\VideoInteractionController::class, 'likesStatus'])->name('videos.likes.status');
    Route::post('/videos/{id}/like',    [\App\Http\Controllers\Video\VideoInteractionController::class, 'toggleLike'])->name('videos.like');
    Route::post('/videos/{id}/comments',              [\App\Http\Controllers\Video\VideoInteractionController::class, 'storeComment'])->name('videos.comments.store');
    Route::post('/videos/{id}/comments/{cid}/reply',  [\App\Http\Controllers\Video\VideoInteractionController::class, 'storeReply'])->name('videos.comments.reply');
    Route::delete('/videos/{id}/comments/{cid}',      [\App\Http\Controllers\Video\VideoInteractionController::class, 'deleteComment'])->name('videos.comments.destroy');

    // Explorar
    Route::get('/explorar', [ExploreController::class, 'index'])->name('explore');

    // Eventos
    Route::get('/eventos',      [\App\Http\Controllers\EventController::class, 'index'])->name('events.public.index');
    Route::get('/eventos/{id}', [\App\Http\Controllers\EventController::class, 'show'])->name('events.public.show');

    // Noticias
    Route::get('/noticias',                [\App\Http\Controllers\ArticleController::class, 'index'])->name('articles.public.index');
    Route::get('/noticias/{id}',           [\App\Http\Controllers\ArticleController::class, 'show'])->name('articles.public.show');
    Route::post('/noticias/{id}/like',     [\App\Http\Controllers\ArticleController::class, 'toggleLike'])->name('articles.like');
    Route::post('/noticias/{id}/comentar', [\App\Http\Controllers\ArticleController::class, 'storeComment'])->name('articles.comment');

    // Follows
    Route::post('/seguir/{id}',   [FollowController::class, 'follow'])->name('follow');
    Route::delete('/seguir/{id}', [FollowController::class, 'unfollow'])->name('unfollow');

    // Buscar
    Route::get('/buscar', [SearchController::class, 'index'])->name('search');

    // Notificaciones
    Route::get('/notificaciones',            [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notificaciones/sin-leer',   [\App\Http\Controllers\NotificationController::class, 'unreadCount'])->name('notifications.unread');
    Route::post('/notificaciones/leer-todo', [\App\Http\Controllers\NotificationController::class, 'markRead'])->name('notifications.markRead');

    // Membresías
    Route::prefix('membresia')->name('membership.')->group(function () {
        Route::get('/',           [\App\Http\Controllers\MembershipController::class, 'index'])->name('index');
        Route::get('/solicitar',  [\App\Http\Controllers\MembershipController::class, 'request'])->name('request');
        Route::post('/solicitar', [\App\Http\Controllers\MembershipController::class, 'submit'])->name('submit');
        Route::get('/estado',     [\App\Http\Controllers\MembershipController::class, 'status'])->name('status');
    });

    // Video Sessions (WebRTC)
    Route::prefix('video')->name('video.')->group(function () {
        Route::post('/initiate', [\App\Http\Controllers\VideoSessionController::class, 'initiate'])->name('initiate');
        Route::post('/respond',  [\App\Http\Controllers\VideoSessionController::class, 'respond'])->name('respond');
        Route::post('/signal',   [\App\Http\Controllers\VideoSessionController::class, 'signal'])->name('signal');
        Route::post('/end',      [\App\Http\Controllers\VideoSessionController::class, 'end'])->name('end');
    });

});

// ── Videos — comentarios públicos (sin auth para lectura) ────────────────────
Route::get('videos/{id}/comments', [\App\Http\Controllers\Video\VideoInteractionController::class, 'comments'])->name('videos.comments');

// ── Admin ─────────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'admin.only'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::get('invitaciones',                [AdminInvitationController::class, 'index'])->name('invitations.index');
    Route::get('invitaciones/{id}',           [AdminInvitationController::class, 'show'])->name('invitations.show');
    Route::post('invitaciones/{id}/aprobar',  [AdminInvitationController::class, 'approve'])->name('invitations.approve');
    Route::post('invitaciones/{id}/rechazar', [AdminInvitationController::class, 'reject'])->name('invitations.reject');

    Route::get('verificaciones',                [AdminVerificationController::class, 'index'])->name('verifications.index');
    Route::get('verificaciones/imagen/{id}',    [AdminVerificationController::class, 'serveImage'])->name('verifications.image');
    Route::get('verificaciones/{id}',           [AdminVerificationController::class, 'show'])->name('verifications.show');
    Route::post('verificaciones/{id}/aprobar',  [AdminVerificationController::class, 'approve'])->name('verifications.approve');
    Route::post('verificaciones/{id}/rechazar', [AdminVerificationController::class, 'reject'])->name('verifications.reject');

    Route::get('fotos',                [AdminPhotoController::class, 'index'])->name('photos.index');
    Route::post('fotos/{id}/aprobar',  [AdminPhotoController::class, 'approve'])->name('photos.approve');
    Route::post('fotos/{id}/rechazar', [AdminPhotoController::class, 'reject'])->name('photos.reject');
    Route::get('fotos/{id}/ver',       [AdminPhotoController::class, 'serve'])->name('photos.serve');

    Route::get('videos',                [AdminVideoController::class, 'index'])->name('videos.index');
    Route::post('videos/{id}/aprobar',  [AdminVideoController::class, 'approve'])->name('videos.approve');
    Route::post('videos/{id}/rechazar', [AdminVideoController::class, 'reject'])->name('videos.reject');
    Route::get('videos/{id}/ver',       [AdminVideoController::class, 'serve'])->name('videos.serve');

    Route::get('usuarios',                   [AdminUserController::class, 'index'])->name('users.index');
    Route::post('usuarios/{id}/suspender',   [AdminUserController::class, 'suspend'])->name('users.suspend');
    Route::post('usuarios/{id}/activar',     [AdminUserController::class, 'activate'])->name('users.activate');
    Route::post('usuarios/{id}/membresia',   [AdminUserController::class, 'changeMembership'])->name('users.membership');
    Route::get('usuarios/exportar',          [AdminUserController::class, 'exportCsv'])->name('users.export');
    Route::get('usuarios/{id}/detalle',      [AdminUserController::class, 'show'])->name('users.show');
    Route::post('usuarios/{id}/reset-pass',  [AdminUserController::class, 'resetPassword'])->name('users.reset-password');
    Route::delete('usuarios/{id}/eliminar',  [AdminUserController::class, 'destroy'])->name('users.destroy');
    Route::get('usuarios/buscar',            [AdminUserController::class, 'search'])->name('users.search');

    Route::get('estadisticas', [AdminStatsController::class, 'index'])->name('stats.index');

    Route::get('eventos',             [AdminEventController::class, 'index'])->name('events.index');
    Route::get('eventos/crear',       [AdminEventController::class, 'create'])->name('events.create');
    Route::post('eventos',            [AdminEventController::class, 'store'])->name('events.store');
    Route::get('eventos/{id}/editar', [AdminEventController::class, 'edit'])->name('events.edit');
    Route::put('eventos/{id}',        [AdminEventController::class, 'update'])->name('events.update');
    Route::delete('eventos/{id}',     [AdminEventController::class, 'destroy'])->name('events.destroy');

    Route::get('articulos',              [AdminArticleController::class, 'index'])->name('articles.index');
    Route::get('articulos/crear',        [AdminArticleController::class, 'create'])->name('articles.create');
    Route::post('articulos',             [AdminArticleController::class, 'store'])->name('articles.store');
    Route::get('articulos/{id}',         [AdminArticleController::class, 'show'])->name('articles.show');
    Route::get('articulos/{id}/editar',  [AdminArticleController::class, 'edit'])->name('articles.edit');
    Route::put('articulos/{id}',         [AdminArticleController::class, 'update'])->name('articles.update');
    Route::delete('articulos/{id}',      [AdminArticleController::class, 'destroy'])->name('articles.destroy');

    Route::get('membresias',                [AdminMembershipController::class, 'index'])->name('memberships.index');
    Route::post('membresias/{id}/aprobar',  [AdminMembershipController::class, 'approve'])->name('memberships.approve');
    Route::post('membresias/{id}/rechazar', [AdminMembershipController::class, 'reject'])->name('memberships.reject');
    Route::post('membresias/registrar',     [AdminMembershipController::class, 'store'])->name('memberships.store');

    Route::get('planes',                            [AdminMembershipController::class, 'planes'])->name('memberships.planes');
    Route::put('planes/{slug}',                     [AdminMembershipController::class, 'updatePlan'])->name('memberships.plans.update');
    Route::post('planes/{slug}/toggle-promo',       [AdminMembershipController::class, 'togglePromo'])->name('memberships.plans.toggle-promo');

    Route::get('comentarios-articulos',                [AdminArticleCommentController::class, 'index'])->name('article-comments.index');
    Route::post('comentarios-articulos/{id}/aprobar',  [AdminArticleCommentController::class, 'approve'])->name('article-comments.approve');
    Route::post('comentarios-articulos/{id}/rechazar', [AdminArticleCommentController::class, 'reject'])->name('article-comments.reject');
    Route::delete('comentarios-articulos/{id}',        [AdminArticleCommentController::class, 'destroy'])->name('article-comments.destroy');

});


