<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\InvitationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExploreController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\Photo\PhotoController;
use App\Http\Controllers\Video\VideoController;
use App\Http\Controllers\Profile\ProfileController;
use App\Http\Controllers\Verification\VerificationController;
use App\Http\Controllers\Auth\PasswordChangeController;
use App\Http\Controllers\Admin\AdminInvitationController;
use App\Http\Controllers\Admin\AdminVerificationController;
use App\Http\Controllers\Admin\AdminPhotoController;
use App\Http\Controllers\Admin\AdminVideoController;

// ── Landing ──────────────────────────────────────────────────────────────────
Route::get('/', fn() => view('auth.landing'))->name('landing');

// ── Foto privada (sin auth, protegida por path) ───────────────────────────────
Route::get('/foto/{path}', function ($path) {
    $fullPath = storage_path('app/private/' . $path);
    if (!file_exists($fullPath)) abort(404);
    return response()->file($fullPath);
})->where('path', '.*')->name('photo.serve');

// ── Debug temporal ────────────────────────────────────────────────────────────
Route::get('/debug-auth', function () {
    if (!auth()->check()) return response()->json(['auth' => false]);
    $user = auth()->user();
    return response()->json([
        'id'       => $user->id,
        'email'    => $user->email,
        'role'     => $user->role,
        'is_admin' => ($user->role === 'admin'),
        'active'   => $user->active,
    ]);
})->middleware('auth');

// ── Solo invitados ────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',  [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
    Route::get('/solicitar-invitacion',  [InvitationController::class, 'show'])->name('invitation.show');
    Route::post('/solicitar-invitacion', [InvitationController::class, 'store'])->name('invitation.store');
});

// ── Auth básico (sin requerir perfil completo) ────────────────────────────────
Route::middleware(['auth', 'force.password.change', 'track.seen'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/cambiar-password',  [PasswordChangeController::class, 'show'])->name('password.change');
    Route::post('/cambiar-password', [PasswordChangeController::class, 'store'])->name('password.change.store');
    Route::get('/perfil/configurar',  [ProfileController::class, 'setup'])->name('profile.setup');
    Route::post('/perfil/configurar', [ProfileController::class, 'store'])->name('profile.store');
});

// ── Auth + perfil completo ────────────────────────────────────────────────────
Route::middleware(['auth', 'force.password.change', 'profile.completed'])->group(function () {

    // Dashboard
    Route::get('/dashboard',      [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/feed', [DashboardController::class, 'feedAjax'])->name('dashboard.feed');

    // Fotos (acciones sociales)
    Route::post('/fotos/{id}/like',       [DashboardController::class, 'toggleLike'])->name('photo.like');
    Route::get('/fotos/{id}/info',        [DashboardController::class, 'photoModal'])->name('photo.info');
    Route::post('/fotos/{id}/comentario', [DashboardController::class, 'storeComment'])->name('photo.comment');

    // Explorar
    Route::get('/explorar', [ExploreController::class, 'index'])->name('explore');

    // Perfil
    Route::get('/perfil/editar',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/perfil/editar', [ProfileController::class, 'update'])->name('profile.update');

    // Verificación
    Route::get('/verificar',           [VerificationController::class, 'show'])->name('verification.show');
    Route::post('/verificar',          [VerificationController::class, 'store'])->name('verification.store');
    Route::get('/verificar/pendiente', [VerificationController::class, 'pending'])->name('verification.pending');
    Route::get('/verificar/estado',    [VerificationController::class, 'status'])->name('verification.status');

    // Follows
    Route::post('/seguir/{nickname}',   [FollowController::class, 'follow'])->name('follow.follow');
    Route::delete('/seguir/{nickname}', [FollowController::class, 'unfollow'])->name('follow.unfollow');
    Route::get('/mis-seguidores',       [FollowController::class, 'followers'])->name('follow.followers');
    Route::get('/siguiendo',            [FollowController::class, 'following'])->name('follow.following');

    // Videos
    Route::get('/mis-videos',         [VideoController::class, 'index'])->name('videos.index');
    Route::post('/mis-videos',        [VideoController::class, 'store'])->name('videos.store');
    Route::get('/videos/{id}',        [VideoController::class, 'serve'])->name('videos.serve');
    Route::delete('/mis-videos/{id}', [VideoController::class, 'destroy'])->name('videos.destroy');
});

// ── Auth + perfil + membresía ─────────────────────────────────────────────────
Route::middleware(['auth', 'force.password.change', 'profile.completed', 'check.membership'])->group(function () {
    Route::get('/mis-fotos',              [PhotoController::class, 'index'])->name('photos.index');
    Route::post('/mis-fotos',             [PhotoController::class, 'store'])->name('photos.store');
    Route::post('/mis-fotos/{id}/perfil', [PhotoController::class, 'setProfilePhoto'])->name('photos.profile');
    Route::delete('/mis-fotos/{id}',      [PhotoController::class, 'destroy'])->name('photos.destroy');
    Route::get('/fotos/{id}',             [PhotoController::class, 'serve'])->name('photos.serve');
    Route::get('/perfil/{nickname}',      [ProfileController::class, 'publicShow'])->name('profile.show');
});

// ── Admin ─────────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'admin.only'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

    // Invitaciones
    Route::get('invitaciones',                [AdminInvitationController::class, 'index'])->name('invitations.index');
    Route::get('invitaciones/{id}',           [AdminInvitationController::class, 'show'])->name('invitations.show');
    Route::post('invitaciones/{id}/aprobar',  [AdminInvitationController::class, 'approve'])->name('invitations.approve');
    Route::post('invitaciones/{id}/rechazar', [AdminInvitationController::class, 'reject'])->name('invitations.reject');

    // Verificaciones
    Route::get('verificaciones',                [AdminVerificationController::class, 'index'])->name('verifications.index');
    Route::get('verificaciones/{id}',           [AdminVerificationController::class, 'show'])->name('verifications.show');
    Route::post('verificaciones/{id}/aprobar',  [AdminVerificationController::class, 'approve'])->name('verifications.approve');
    Route::post('verificaciones/{id}/rechazar', [AdminVerificationController::class, 'reject'])->name('verifications.reject');
    Route::get('verificaciones/imagen/{id}',    [AdminVerificationController::class, 'serveImage'])->name('verifications.image');

    // Fotos
    Route::get('fotos',                [AdminPhotoController::class, 'index'])->name('photos.index');
    Route::post('fotos/{id}/aprobar',  [AdminPhotoController::class, 'approve'])->name('photos.approve');
    Route::post('fotos/{id}/rechazar', [AdminPhotoController::class, 'reject'])->name('photos.reject');
    Route::get('fotos/{id}/ver',       [AdminPhotoController::class, 'serve'])->name('photos.serve');

    // Videos
    Route::get('videos',                [AdminVideoController::class, 'index'])->name('videos.index');
    Route::post('videos/{id}/aprobar',  [AdminVideoController::class, 'approve'])->name('videos.approve');
    Route::post('videos/{id}/rechazar', [AdminVideoController::class, 'reject'])->name('videos.reject');
    Route::get('videos/{id}/ver',       [AdminVideoController::class, 'serve'])->name('videos.serve');
});
