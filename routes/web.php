<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\InvitationController;
use App\Http\Controllers\Dashboard\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| LOBBY69 - Web Routes
|--------------------------------------------------------------------------
*/

// ── Landing (página principal) ──
Route::get('/', function () {
    return view('auth.landing');
})->name('landing');

// ── Autenticación (solo invitados) ──
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');

    Route::get('/solicitar-invitacion', [InvitationController::class, 'show'])->name('invitation.show');
    Route::post('/solicitar-invitacion', [InvitationController::class, 'store'])->name('invitation.store');
});

// ── Rutas protegidas (requieren autenticación) ──
Route::middleware('auth')->group(function () {
    Route::get('/explorar', function () {
            return view('profiles.explore');
        })->name('explore');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});
// ── Admin ────────────────────────────────────────────────
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
