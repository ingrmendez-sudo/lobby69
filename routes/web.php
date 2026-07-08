<?php
use Illuminate\Support\Facades\Auth;

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

// ── Landing ───────────────────────────────────────────────────────────────────
Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : redirect()->route('login');
})->name('landing');

// ── Auth ──────────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',  [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Eventos públicos
Route::get('/eventos',      [EventController::class, 'index'])->name('events.index');
Route::get('/eventos/{id}', [EventController::class, 'show'])->name('events.show');

// Artículos/Noticias públicos
Route::get('/noticias',      [ArticleController::class, 'index'])->name('articles.index');
Route::get('/noticias/{id}', [ArticleController::class, 'show'])->name('articles.show');


// ── App (requiere auth) ───────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/feed', [DashboardController::class, 'getFeed'])->name('dashboard.feed');
    Route::get('/dashboard/feed-ajax', [DashboardController::class, 'feedAjax'])->name('dashboard.feedAjax');

    // Perfil
    Route::get('/perfil/configurar',  [ProfileController::class, 'setup'])->name('profile.setup');
    Route::post('/perfil/configurar', [ProfileController::class, 'store'])->name('profile.store');
    Route::get('/perfil/editar',      [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/perfil/editar',      [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/u/{nickname}',       [ProfileController::class, 'publicShow'])->name('profile.show');

    // Verificación
    Route::get('/verificacion',        [VerificationController::class, 'show'])->name('verification.show');
    Route::post('/verificacion',       [VerificationController::class, 'store'])->name('verification.store');
    Route::get('/verificacion/estado', [VerificationController::class, 'status'])->name('verification.status');

    // Fotos
    Route::get('/mis-fotos',                    [PhotoController::class, 'index'])->name('photos.index');
    Route::post('/fotos',                       [PhotoController::class, 'store'])->name('photos.store');
    Route::post('/fotos/{id}/perfil',           [PhotoController::class, 'setProfilePhoto'])->name('photos.setProfile');
    Route::delete('/fotos/{id}',                [PhotoController::class, 'destroy'])->name('photos.destroy');
    Route::get('/fotos/{id}/ver',               [PhotoController::class, 'serve'])->name('photos.serve');
    Route::get('/fotos/{id}/info',              [DashboardController::class, 'photoModal'])->name('photos.info');
    Route::post('/fotos/{id}/like',             [DashboardController::class, 'toggleLike'])->name('photos.like');
    Route::post('/fotos/{id}/comentar',         [DashboardController::class, 'storeComment'])->name('photos.comment');

    // Videos
    Route::get('/mis-videos',     [VideoController::class, 'index'])->name('videos.index');
    Route::post('/videos',        [VideoController::class, 'store'])->name('videos.store');
    Route::delete('/videos/{id}', [VideoController::class, 'destroy'])->name('videos.destroy');

    // Explorar
    Route::get('/explorar', [ExploreController::class, 'index'])->name('explore');

    // Eventos públicos
    Route::get('/eventos',      [\App\Http\Controllers\EventController::class, 'index'])->name('events.public.index');
    Route::get('/eventos/{id}', [\App\Http\Controllers\EventController::class, 'show'])->name('events.public.show');

    // Noticias públicas
    Route::get('/noticias',               [\App\Http\Controllers\ArticleController::class, 'index'])->name('articles.public.index');
    Route::get('/noticias/{id}',          [\App\Http\Controllers\ArticleController::class, 'show'])->name('articles.public.show');
    Route::post('/noticias/{id}/like',    [\App\Http\Controllers\ArticleController::class, 'toggleLike'])->name('articles.like');
    Route::post('/noticias/{id}/comentar',[\App\Http\Controllers\ArticleController::class, 'storeComment'])->name('articles.comment');

    // Follows
    Route::post('/seguir/{id}',   [FollowController::class, 'follow'])->name('follow');
    Route::delete('/seguir/{id}', [FollowController::class, 'unfollow'])->name('unfollow');

    // Buscar
    Route::get('/buscar', [SearchController::class, 'index'])->name('search');

    // Notificaciones
    Route::get('/notificaciones',            [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notificaciones/sin-leer',   [\App\Http\Controllers\NotificationController::class, 'unreadCount'])->name('notifications.unread');
    Route::post('/notificaciones/leer-todo', [\App\Http\Controllers\NotificationController::class, 'markRead'])->name('notifications.markRead');

});

// Rutas públicas de invitación
Route::get('/invitacion', [\App\Http\Controllers\Auth\InvitationController::class, 'show'])->name('invitation.show');
Route::post('/invitacion', [\App\Http\Controllers\Auth\InvitationController::class, 'store'])->name('invitation.store');

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
    Route::get('verificaciones/{id}',           [AdminVerificationController::class, 'show'])->name('verifications.show');
    Route::post('verificaciones/{id}/aprobar',  [AdminVerificationController::class, 'approve'])->name('verifications.approve');
    Route::post('verificaciones/{id}/rechazar', [AdminVerificationController::class, 'reject'])->name('verifications.reject');
    Route::get('verificaciones/imagen/{id}',    [AdminVerificationController::class, 'serveImage'])->name('verifications.image');

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

    Route::get('comentarios-articulos',                    [\App\Http\Controllers\Admin\AdminArticleCommentController::class, 'index'])->name('article-comments.index');
    Route::post('comentarios-articulos/{id}/aprobar',      [\App\Http\Controllers\Admin\AdminArticleCommentController::class, 'approve'])->name('article-comments.approve');
    Route::post('comentarios-articulos/{id}/rechazar',     [\App\Http\Controllers\Admin\AdminArticleCommentController::class, 'reject'])->name('article-comments.reject');
    Route::delete('comentarios-articulos/{id}',            [\App\Http\Controllers\Admin\AdminArticleCommentController::class, 'destroy'])->name('article-comments.destroy');

});








