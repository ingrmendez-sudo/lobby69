<?php
// ── Admin ─────────────────────────────────────────────────────────────────────
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

Route::middleware(['auth', 'admin.only'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

    // Dashboard
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

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

    // Usuarios
    Route::get('usuarios',                       [AdminUserController::class, 'index'])->name('users.index');
    Route::post('usuarios/{id}/suspender',       [AdminUserController::class, 'suspend'])->name('users.suspend');
    Route::post('usuarios/{id}/activar',         [AdminUserController::class, 'activate'])->name('users.activate');
    Route::post('usuarios/{id}/membresia',       [AdminUserController::class, 'changeMembership'])->name('users.membership');
    Route::get('usuarios/exportar',          [AdminUserController::class, 'exportCsv'])->name('users.export');
    Route::get('usuarios/{id}/detalle',      [AdminUserController::class, 'show'])->name('users.show');
    Route::post('usuarios/{id}/reset-pass',  [AdminUserController::class, 'resetPassword'])->name('users.reset-password');
    Route::delete('usuarios/{id}/eliminar',  [AdminUserController::class, 'destroy'])->name('users.destroy');


    // Estadísticas
    Route::get('estadisticas', [AdminStatsController::class, 'index'])->name('stats.index');

    // Eventos
    Route::get('eventos',              [AdminEventController::class, 'index'])->name('events.index');
    Route::get('eventos/crear',        [AdminEventController::class, 'create'])->name('events.create');
    Route::post('eventos',             [AdminEventController::class, 'store'])->name('events.store');
    Route::get('eventos/{id}/editar',  [AdminEventController::class, 'edit'])->name('events.edit');
    Route::put('eventos/{id}',         [AdminEventController::class, 'update'])->name('events.update');
    Route::delete('eventos/{id}',      [AdminEventController::class, 'destroy'])->name('events.destroy');

    // Artículos/Noticias
    Route::get('articulos',              [AdminArticleController::class, 'index'])->name('articles.index');
    Route::get('articulos/crear',        [AdminArticleController::class, 'create'])->name('articles.create');
    Route::post('articulos',             [AdminArticleController::class, 'store'])->name('articles.store');
    Route::get('articulos/{id}/editar',  [AdminArticleController::class, 'edit'])->name('articles.edit');
    Route::put('articulos/{id}',         [AdminArticleController::class, 'update'])->name('articles.update');
    Route::delete('articulos/{id}',      [AdminArticleController::class, 'destroy'])->name('articles.destroy');

    // Membresías y pagos
    Route::get('membresias',                    [AdminMembershipController::class, 'index'])->name('memberships.index');
    Route::post('membresias/{id}/aprobar',      [AdminMembershipController::class, 'approve'])->name('memberships.approve');
    Route::post('membresias/{id}/rechazar',     [AdminMembershipController::class, 'reject'])->name('memberships.reject');
    Route::post('membresias/registrar', [AdminMembershipController::class, 'store'])->name('memberships.store');


});
