@extends('layouts.app')

@section('title', 'Iniciar Sesión')

@section('content')
<section class="auth-section">
    <div class="container">
        <div class="auth-card card">
            <div class="auth-card__header">
                <img loading="eager" src="{{ asset('img/LOGO LOBBY69 BCO2.jpeg') }}" alt="LOBBY69" class="auth-card__logo"
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

