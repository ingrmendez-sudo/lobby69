<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CheckMembershipStatus
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) return $next($request);

        $user = DB::table('users')->where('id', auth()->id())->first();
        if (!$user) return $next($request);

        // Admin siempre pasa
        if ($user->role === 'admin') return $next($request);

        // Rutas que siempre están permitidas
        $allowedRoutes = [
            'verification.show', 'verification.store',
            'membership.plans', 'membership.checkout',
            'profile.setup', 'profile.store',
            'password.change', 'password.change.store',
            'logout'
        ];
        if ($request->routeIs(...$allowedRoutes)) return $next($request);

        $membershipType = $user->membership_type ?? 'trial';
        $trialStarted   = $user->trial_started_at
                            ? Carbon::parse($user->trial_started_at)
                            : Carbon::now();
        $trialDays      = $trialStarted->diffInDays(Carbon::now());

        // TRIAL: más de 7 días sin verificar → bloquear
        if ($membershipType === 'trial' && $trialDays > 7) {
            return redirect()->route('verification.show')
                ->with('warning', 'Tu período de prueba ha expirado. Verifica tu identidad para continuar.');
        }

        // TRIAL_VERIFIED: más de 37 días (7 trial + 30 gratis) → membresía
        if ($membershipType === 'trial_verified') {
            $verifiedAt = $user->verified_at ? Carbon::parse($user->verified_at) : Carbon::now();
            if ($verifiedAt->diffInDays(Carbon::now()) > 30) {
                return redirect()->route('membership.plans')
                    ->with('warning', 'Tu mes gratuito ha terminado. Elige una membresía para continuar.');
            }
        }

        // EXPIRED
        if ($membershipType === 'expired') {
            return redirect()->route('membership.plans')
                ->with('warning', 'Tu membresía ha vencido. Renueva para continuar.');
        }

        // SUSPENDED / BANNED
        if (in_array($membershipType, ['suspended', 'banned'])) {
            auth()->logout();
            return redirect()->route('login')
                ->with('error', 'Tu cuenta ha sido suspendida. Contacta al administrador.');
        }

        return $next($request);
    }
}