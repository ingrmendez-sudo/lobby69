<?php

namespace App\Services;

use App\Models\Account;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthService
{
    protected SupabaseService $supabase;

    public function __construct(SupabaseService $supabase)
    {
        $this->supabase = $supabase;
    }

    public function attemptLogin(string $email, string $password, ?string $ip = null, ?string $userAgent = null): array
    {
        try {
            $account = Account::where('email', $email)->first();

            if (!$account) {
                $this->logLoginAttempt($email, false, 'Cuenta no encontrada', $ip, $userAgent);
                return ['success' => false, 'message' => 'Credenciales inválidas.'];
            }

            if (!$account->is_active || $account->status !== 'active') {
                $this->logLoginAttempt($email, false, 'Cuenta inactiva', $ip, $userAgent, $account->id);
                return ['success' => false, 'message' => 'Tu cuenta está suspendida o pendiente de activación.'];
            }

            if (!Hash::check($password, $account->password)) {
                $this->logLoginAttempt($email, false, 'Contraseña incorrecta', $ip, $userAgent, $account->id);
                return ['success' => false, 'message' => 'Credenciales inválidas.'];
            }

            Auth::login($account, true);

            $account->update([
                'last_login_at' => Carbon::now(),
                'last_seen_at' => Carbon::now(),
            ]);

            $this->supabase->insert('audit_logs', [
                'account_id' => $account->id,
                'action' => 'login',
                'resource_type' => 'account',
                'resource_id' => $account->id,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'metadata' => json_encode(['email' => $email]),
                'created_at' => Carbon::now()->toIso8601String(),
            ]);

            $this->logLoginAttempt($email, true, 'Éxito', $ip, $userAgent, $account->id);

            return ['success' => true, 'account' => $account];

        } catch (\Exception $e) {
            Log::error('Error en login: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno. Intenta de nuevo.'];
        }
    }

    protected function logLoginAttempt(string $email, bool $success, ?string $reason = null, ?string $ip = null, ?string $userAgent = null, ?string $accountId = null): void
    {
        try {
            $this->supabase->insert('login_audit', [
                'account_id' => $accountId,
                'email' => $email,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'success' => $success,
                'failure_reason' => $reason,
                'created_at' => Carbon::now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            Log::warning('No se pudo registrar login_audit: ' . $e->getMessage());
        }
    }

    public function logout(): void
    {
        $account = Auth::user();
        if ($account) {
            $account->update(['last_seen_at' => Carbon::now()]);
        }
        Auth::logout();
    }
}
