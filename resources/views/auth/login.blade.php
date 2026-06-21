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

            <form method="POST" action="{{ route('login.store') }}" class="auth-form">
                @csrf

                <div class="form-group">
                    <label for="email" class="form-label">Correo electrónico</label>
                    <input type="email" id="email" name="email"
                           class="form-control @error('email') form-control--error @enderror"
                           value="{{ old('email') }}" placeholder="tu@correo.com" required autofocus>
                    @error('email')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" id="password" name="password"
                           class="form-control @error('password') form-control--error @enderror"
                           placeholder="••••••••" required>
                    @error('password')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

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
@endsection
