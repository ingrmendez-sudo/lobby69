<?php
/**
 * fix_login_dashboard.php
 * 1) Login: añade "remember me", autocomplete y link "¿Olvidaste tu contraseña?"
 * 2) Dashboard: añade botón "Editar Perfil" siempre visible en el sidebar
 */

// ══════════════════════════════════════════════════════
// ARCHIVO 1: login.blade.php
// ══════════════════════════════════════════════════════
$login = __DIR__ . '/resources/views/auth/login.blade.php';

$loginContent = <<<'BLADE'
@extends('layouts.app')

@section('title', 'Iniciar Sesión')

@section('content')
<section class="auth-section">
    <div class="container">
        <div class="auth-card card">
            <div class="auth-card__header">
                <img src="{{ asset('img/LOGO LOBBY69 BCO2.jpeg') }}" alt="LOBBY69" class="auth-card__logo"
                     onerror="this.style.display='none'">
                <h1>Iniciar Sesión</h1>
                <p>Bienvenido de vuelta a LOBBY69</p>
            </div>

            <form method="POST" action="{{ route('login.store') }}" class="auth-form" autocomplete="on">
                @csrf

                {{-- Email --}}
                <div class="form-group">
                    <label for="email" class="form-label">Correo electrónico</label>
                    <input type="email"
                           id="email"
                           name="email"
                           class="form-control @error('email') form-control--error @enderror"
                           value="{{ old('email') }}"
                           placeholder="tu@correo.com"
                           autocomplete="email"
                           required
                           autofocus>
                    @error('email')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="form-group">
                    <label for="password" class="form-label">Contraseña</label>
                    <div style="position:relative;">
                        <input type="password"
                               id="password"
                               name="password"
                               class="form-control @error('password') form-control--error @enderror"
                               placeholder="••••••••"
                               autocomplete="current-password"
                               required>
                        <button type="button"
                                onclick="togglePass()"
                                style="position:absolute;right:.85rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#9ca3af;font-size:.9rem;"
                                tabindex="-1">
                            <i id="passIcon" class="fas fa-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Remember me + Forgot password --}}
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
                    <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.9rem;color:#4b5563;">
                        <input type="checkbox"
                               name="remember"
                               id="remember"
                               value="1"
                               {{ old('remember') ? 'checked' : '' }}
                               style="width:16px;height:16px;accent-color:#8b5cf6;">
                        Recordar mis datos
                    </label>
                    <a href="{{ route('password.forgot') }}"
                       style="font-size:.85rem;color:#8b5cf6;text-decoration:none;"
                       onmouseover="this.style.textDecoration='underline'"
                       onmouseout="this.style.textDecoration='none'">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>

                {{-- Error general --}}
                @if(session('error'))
                <div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:.85rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.9rem;">
                    ⚠️ {{ session('error') }}
                </div>
                @endif

                <button type="submit" class="btn btn--primary btn--lg btn--block">
                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                </button>
            </form>

            <div class="auth-card__footer">
                <p>¿No tienes cuenta? <a href="{{ route('invitation.show') }}" class="link">Solicita una invitación</a></p>
            </div>
        </div>
    </div>
</section>

<script>
function togglePass() {
    var input = document.getElementById('password');
    var icon  = document.getElementById('passIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}
</script>
@endsection
BLADE;

file_put_contents($login, $loginContent);
echo "✅ login.blade.php actualizado\n";
echo "   + autocomplete=\"on\" en el form\n";
echo "   + autocomplete=\"email\" y \"current-password\" en inputs\n";
echo "   + checkbox 'Recordar mis datos' (name=remember)\n";
echo "   + link '¿Olvidaste tu contraseña?'\n";
echo "   + botón mostrar/ocultar contraseña\n";
echo "   + muestra session('error') si existe\n\n";


// ══════════════════════════════════════════════════════
// ARCHIVO 2: dashboard/index.blade.php
// ══════════════════════════════════════════════════════
$dashboard = __DIR__ . '/resources/views/dashboard/index.blade.php';

$dashContent = <<<'BLADE'
@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<section class="dashboard">

    {{-- ── SIDEBAR IZQUIERDO ── --}}
    <aside class="dashboard__sidebar dashboard__sidebar--left">

        {{-- Tarjeta de perfil --}}
        <article class="profile-summary-card card">
            <div class="profile-summary-card__header">
                <img src="{{ $profile->avatar_url ?? asset('img/default-avatar.svg') }}"
                     alt="{{ $profile->display_name ?? $user->name ?? $user->email }}"
                     class="avatar avatar--lg"
                     onerror="this.onerror=null; this.src='{{ asset('img/default-avatar.svg') }}'">
                <div>
                    <h2>{{ $profile->display_name ?? $user->name ?? $user->email }}</h2>
                    <p class="text-sm text-muted">
                        @if($user->isAdmin())
                            <span class="badge badge--vip">
                                <i class="fas fa-crown"></i> Administrador
                            </span>
                        @else
                            <span class="badge badge--verified">
                                <i class="fas fa-check-circle"></i> Miembro verificado
                            </span>
                        @endif
                    </p>
                    @if($profile?->nickname)
                    <p class="text-sm text-muted" style="margin-top:.3rem;">
                        @{{ $profile->nickname }}
                    </p>
                    @endif
                </div>
            </div>

            {{-- Botones de acción del perfil --}}
            <div style="display:flex;flex-direction:column;gap:.6rem;margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid #f1f5f9;">

                @if($profile && $profile->profile_completed)
                    {{-- Perfil completo: botón editar --}}
                    <a href="{{ route('profile.edit') }}"
                       class="btn btn--primary"
                       style="text-align:center;font-size:.9rem;">
                        <i class="fas fa-user-edit"></i> Editar Perfil
                    </a>
                @else
                    {{-- Sin perfil o incompleto: botón completar --}}
                    <a href="{{ route('profile.setup') }}"
                       class="btn btn--primary"
                       style="text-align:center;font-size:.9rem;background:linear-gradient(135deg,#f59e0b,#ef4444);">
                        <i class="fas fa-user-plus"></i> Completar Perfil
                    </a>
                @endif

                @if($user->isAdmin())
                <a href="{{ route('admin.invitations.index') }}"
                   class="btn btn--ghost"
                   style="text-align:center;font-size:.9rem;">
                    <i class="fas fa-shield-alt"></i> Panel Admin
                </a>
                @endif

                {{-- Logout --}}
                <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                    @csrf
                    <button type="submit"
                            class="btn btn--ghost"
                            style="width:100%;font-size:.9rem;color:#ef4444;border-color:#fca5a5;">
                        <i class="fas fa-sign-out-alt"></i> Cerrar sesión
                    </button>
                </form>
            </div>
        </article>

        {{-- Info rápida del perfil --}}
        @if($profile)
        <article class="card" style="padding:1.25rem;margin-top:1rem;">
            <h4 style="font-size:.85rem;font-weight:700;color:#374151;margin-bottom:.75rem;text-transform:uppercase;letter-spacing:.05em;">
                Mi Perfil
            </h4>
            <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:.5rem;">
                @if($profile->profile_type)
                <li style="font-size:.85rem;color:#6b7280;">
                    <i class="fas fa-tag" style="width:1.2rem;color:#8b5cf6;"></i>
                    {{ ucfirst($profile->profile_type) }}
                </li>
                @endif
                @if($profile->city || $profile->state)
                <li style="font-size:.85rem;color:#6b7280;">
                    <i class="fas fa-map-marker-alt" style="width:1.2rem;color:#8b5cf6;"></i>
                    {{ implode(', ', array_filter([$profile->city, $profile->state])) }}
                </li>
                @endif
                @if($profile->age)
                <li style="font-size:.85rem;color:#6b7280;">
                    <i class="fas fa-birthday-cake" style="width:1.2rem;color:#8b5cf6;"></i>
                    {{ $profile->age }} años
                </li>
                @endif
            </ul>
        </article>
        @endif

    </aside>

    {{-- ── FEED CENTRAL ── --}}
    <section class="dashboard__feed">
        <h1 class="h2">Bienvenido a LOBBY69</h1>
        <p class="text-lg text-muted">
            ¡Hola <strong>{{ $profile->display_name ?? $user->name ?? $user->email }}</strong>!
            Explora la comunidad, conecta con personas afines y disfruta de una experiencia exclusiva.
        </p>

        {{-- Alerta si perfil incompleto --}}
        @if(!$profile || !$profile->profile_completed)
        <div class="card" style="padding:2rem;margin-top:1.5rem;text-align:center;border:2px dashed #f59e0b;">
            <i class="fas fa-user-edit" style="font-size:3rem;color:#f59e0b;margin-bottom:1rem;"></i>
            <h3>{{ $profile ? 'Completa tu perfil' : 'Crea tu perfil' }}</h3>
            <p style="margin-bottom:1rem;color:#6b7280;">
                Para aprovechar al máximo LOBBY69, completa tu perfil con fotos y una descripción.
            </p>
            <a href="{{ route('profile.setup') }}" class="btn btn--primary">
                <i class="fas fa-arrow-right"></i>
                {{ $profile ? 'Continuar configuración' : 'Crear Perfil' }}
            </a>
        </div>
        @endif

        {{-- Mensajes de sesión --}}
        @if(session('success'))
        <div style="background:#d1fae5;border:1px solid #6ee7b7;color:#065f46;padding:1rem;border-radius:10px;margin-top:1.5rem;">
            ✅ {{ session('success') }}
        </div>
        @endif

        {{-- Placeholder feed --}}
        <div class="card" style="padding:2rem;margin-top:1.5rem;text-align:center;color:#9ca3af;">
            <i class="fas fa-stream" style="font-size:2.5rem;margin-bottom:1rem;"></i>
            <p>El feed de actividad estará disponible próximamente.</p>
        </div>
    </section>

    {{-- ── SIDEBAR DERECHO ── --}}
    <aside class="dashboard__sidebar dashboard__sidebar--right">
        <section class="card" style="padding:1.5rem;">
            <h3 class="h4">Conectados</h3>
            <p class="text-sm text-muted">Próximamente: usuarios en línea</p>
        </section>
    </aside>

</section>
@endsection
BLADE;

file_put_contents($dashboard, $dashContent);
echo "✅ dashboard/index.blade.php actualizado\n";
echo "   + Botón 'Editar Perfil' siempre visible en sidebar\n";
echo "   + Botón 'Completar Perfil' si perfil incompleto\n";
echo "   + Botón 'Panel Admin' solo para admins\n";
echo "   + Logout como form POST (corregido)\n";
echo "   + Info rápida: tipo, ciudad, edad\n";
echo "   + Muestra session('success')\n\n";

echo "══════════════════════════════\n";
echo "Ejecuta ahora:\n";
echo "  C:\\php\\php.exe artisan view:clear\n";
echo "  C:\\php\\php.exe artisan serve\n";
