<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthService
{
    public function attemptLogin(string $email, string $password): array
    {
        try {
            $user = User::where('email', $email)->first();

            if (!$user) {
                return ['success' => false, 'message' => 'Credenciales inv├ílidas.'];
            }

            if (!$user->active) {
                return ['success' => false, 'message' => 'Tu cuenta est├í desactivada.'];
            }

            if (!Hash::check($password, $user->password)) {
                return ['success' => false, 'message' => 'Credenciales inv├ílidas.'];
            }

            Auth::login($user, true);

            $user->update([
                'last_login_at' => Carbon::now(),
            ]);

            return ['success' => true, 'user' => $user];

        } catch (\Exception $e) {
            Log::error('Error en login: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno. Intenta de nuevo.'];
        }
    }

    public function logout(): void
    {
        Auth::logout();
    }
}
