<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\InvitationController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| LOBBY69 - Web Routes
|--------------------------------------------------------------------------
*/

// Landing
Route::get('/', function () {
    return view('auth.landing');
})->name('landing');

// Autenticacion (solo invitados)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
    Route::get('/solicitar-invitacion', [InvitationController::class, 'show'])->name('invitation.show');
    Route::post('/solicitar-invitacion', [InvitationController::class, 'store'])->name('invitation.store');

    Route::get('/dashboard/feed', [App\Http\Controllers\DashboardController::class, 'feedAjax'])->name('dashboard.feed.ajax');
    Route::post('/dashboard/like/{photo}', [App\Http\Controllers\DashboardController::class, 'toggleLike'])->name('dashboard.like');
    Route::get('/dashboard/photo/{photo}', [App\Http\Controllers\DashboardController::class, 'photoModal'])->name('dashboard.photo.modal');
    
});
Route::get('/foto/{path}', function ($path) {
      $fullPath = storage_path('app/private/' . $path);
      if (!file_exists($fullPath)) abort(404);
      return response()->file($fullPath);
    })->where('path', '.*')->name('photo.serve');

// Rutas solo auth + force password (sin requerir perfil completo)
Route::middleware(['auth', 'force.password.change', 'track.seen'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Cambio de contrasena obligatorio
    Route::get('/cambiar-password',  [App\Http\Controllers\Auth\PasswordChangeController::class, 'show'])
        ->name('password.change');
    Route::post('/cambiar-password', [App\Http\Controllers\Auth\PasswordChangeController::class, 'store'])
        ->name('password.change.store');

    // Perfil setup inicial (no requiere perfil completo)
    Route::get('/perfil/configurar',  [App\Http\Controllers\Profile\ProfileController::class, 'setup'])
        ->name('profile.setup');
    Route::post('/perfil/configurar', [App\Http\Controllers\Profile\ProfileController::class, 'store'])
        ->name('profile.store');
});

// Rutas que requieren perfil completo
Route::middleware(['auth', 'force.password.change', 'profile.completed'])->group(function () {
    Route::get('/dashboard',  [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/explorar', function () {
        return view('profiles.explore');
    })->name('explore');
    Route::get('/perfil/editar',  [App\Http\Controllers\Profile\ProfileController::class, 'edit'])
        ->name('profile.edit');
    Route::post('/perfil/editar', [App\Http\Controllers\Profile\ProfileController::class, 'update'])
        ->name('profile.update');
});

// Admin
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin.only'])->group(function () {
    Route::get('invitaciones', [App\Http\Controllers\Admin\AdminInvitationController::class, 'index'])
        ->name('invitations.index');
    Route::get('invitaciones/{id}', [App\Http\Controllers\Admin\AdminInvitationController::class, 'show'])
        ->name('invitations.show');
    Route::post('invitaciones/{id}/aprobar', [App\Http\Controllers\Admin\AdminInvitationController::class, 'approve'])
        ->name('invitations.approve');
    Route::post('invitaciones/{id}/rechazar', [App\Http\Controllers\Admin\AdminInvitationController::class, 'reject'])
        ->name('invitations.reject');
});

// Debug temporal (fuera de cualquier grupo)
Route::get('/debug-auth', function () {
    if (!auth()->check()) return response()->json(['auth' => false]);
    $user = auth()->user();
    return response()->json([
        'id'         => $user->id,
        'email'      => $user->email,
        'role'       => $user->role,
        'role_type'  => gettype($user->role),
        'is_admin'   => ($user->role === 'admin'),
        'trim_check' => (trim((string)$user->role) === 'admin'),
        'active'     => $user->active,
    ]);
})->middleware('auth');

// ── VERIFICACIÓN DE IDENTIDAD ─────────────────────────────────────────────
Route::middleware(['auth', 'force.password.change', 'profile.completed'])->group(function () {
    Route::get('/verificar',
        [\App\Http\Controllers\Verification\VerificationController::class, 'show'])
        ->name('verification.show');

    Route::post('/verificar',
        [\App\Http\Controllers\Verification\VerificationController::class, 'store'])
        ->name('verification.store');

    Route::get('/verificar/pendiente',
        [\App\Http\Controllers\Verification\VerificationController::class, 'pending'])
        ->name('verification.pending');

    Route::get('/verificar/estado',
        [\App\Http\Controllers\Verification\VerificationController::class, 'status'])
        ->name('verification.status');
});

// ── ADMIN: VERIFICACIONES ─────────────────────────────────────────────────
Route::middleware(['auth', 'admin.only'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/verificaciones',
        [\App\Http\Controllers\Admin\AdminVerificationController::class, 'index'])
        ->name('verifications.index');

    Route::get('/verificaciones/{id}',
        [\App\Http\Controllers\Admin\AdminVerificationController::class, 'show'])
        ->name('verifications.show');

    Route::post('/verificaciones/{id}/aprobar',
        [\App\Http\Controllers\Admin\AdminVerificationController::class, 'approve'])
        ->name('verifications.approve');

    Route::post('/verificaciones/{id}/rechazar',
        [\App\Http\Controllers\Admin\AdminVerificationController::class, 'reject'])
        ->name('verifications.reject');

    Route::get('/verificaciones/imagen/{id}',
        [\App\Http\Controllers\Admin\AdminVerificationController::class, 'serveImage'])
        ->name('verifications.image');
});

// ── FOTOS (usuario autenticado) ───────────────────────
Route::middleware(['auth', 'force.password.change', 'profile.completed', 'check.membership'])
    ->group(function () {

    Route::get('/mis-fotos',
        [\App\Http\Controllers\Photo\PhotoController::class, 'index'])
        ->name('photos.index');

    Route::post('/mis-fotos',
        [\App\Http\Controllers\Photo\PhotoController::class, 'store'])
        ->name('photos.store');

    Route::post('/mis-fotos/{id}/perfil',
        [\App\Http\Controllers\Photo\PhotoController::class, 'setProfilePhoto'])
        ->name('photos.profile');

    Route::delete('/mis-fotos/{id}',
        [\App\Http\Controllers\Photo\PhotoController::class, 'destroy'])
        ->name('photos.destroy');

    // Ver foto (con control de acceso por membresía)
    Route::get('/fotos/{id}',
        [\App\Http\Controllers\Photo\PhotoController::class, 'serve'])
        ->name('photos.serve');

    // Perfil público de un usuario
    Route::get('/perfil/{nickname}',
        [\App\Http\Controllers\Profile\ProfileController::class, 'publicShow'])
        ->name('profile.show');
});

// ── ADMIN: FOTOS ──────────────────────────────────────
Route::middleware(['auth', 'admin.only'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/fotos',
        [\App\Http\Controllers\Admin\AdminPhotoController::class, 'index'])
        ->name('photos.index');

    Route::post('/fotos/{id}/aprobar',
        [\App\Http\Controllers\Admin\AdminPhotoController::class, 'approve'])
        ->name('photos.approve');

    Route::post('/fotos/{id}/rechazar',
        [\App\Http\Controllers\Admin\AdminPhotoController::class, 'reject'])
        ->name('photos.reject');

    Route::get('/fotos/imagen/{id}',
        [\App\Http\Controllers\Admin\AdminPhotoController::class, 'serve'])
        ->name('photos.serve');
});

