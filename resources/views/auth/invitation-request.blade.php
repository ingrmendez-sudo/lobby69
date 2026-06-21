@extends('layouts.app')

@section('title', 'Solicitar Invitación')

@section('content')
<section class="auth-section">
    <div class="container">
        <div class="auth-card card">
            <div class="auth-card__header">
                <img src="{{ asset('img/logo-lobby69_.png') }}" alt="LOBBY69" class="auth-card__logo"
                     onerror="this.style.display='none'">
                <h1>Solicitar Invitación</h1>
                <p>Completa el formulario para unirte a la comunidad LOBBY69</p>
            </div>

            <form method="POST" action="{{ route('invitation.store') }}" class="auth-form">
                @csrf

                <div class="form-group">
                    <label for="nombre_completo" class="form-label">Nick con el que te gustaría darte de alta</label>
                    <input type="text" id="nombre_completo" name="nombre_completo"
                           class="form-control @error('nombre_completo') form-control--error @enderror"
                           value="{{ old('nombre_completo') }}" placeholder="Ej: Carlos_CDMX" required>
                    @error('nombre_completo')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Correo electrónico</label>
                    <input type="email" id="email" name="email"
                           class="form-control @error('email') form-control--error @enderror"
                           value="{{ old('email') }}" placeholder="tu@correo.com" required>
                    @error('email')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Tipo de perfil</label>
                    <div class="radio-group">
                        @foreach(['single' => 'Single (Chico solo)', 'unicornio' => 'Unicornio (Chica sola)', 'pareja' => 'Pareja'] as $value => $label)
                        <label class="radio-label">
                            <input type="radio" name="tipo_perfil" value="{{ $value }}"
                                   {{ old('tipo_perfil') === $value ? 'checked' : '' }} required>
                            <span>{{ $label }}</span>
                        </label>
                        @endforeach
                    </div>
                    @error('tipo_perfil')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edad" class="form-label">Edad</label>
                        <input type="number" id="edad" name="edad"
                               class="form-control @error('edad') form-control--error @enderror"
                               value="{{ old('edad') }}" min="18" max="120" required>
                        @error('edad')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="pais" class="form-label">País</label>
                        <input type="text" id="pais" name="pais"
                               class="form-control @error('pais') form-control--error @enderror"
                               value="{{ old('pais', 'México') }}" required>
                        @error('pais')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="estado" class="form-label">Estado</label>
                        <input type="text" id="estado" name="estado"
                               class="form-control @error('estado') form-control--error @enderror"
                               value="{{ old('estado') }}" placeholder="Ej: CDMX" required>
                        @error('estado')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="municipio" class="form-label">Municipio</label>
                        <input type="text" id="municipio" name="municipio"
                               class="form-control @error('municipio') form-control--error @enderror"
                               value="{{ old('municipio') }}" placeholder="Ej: Benito Juárez" required>
                        @error('municipio')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="motivo" class="form-label">Motivo por el que quieres acceder a la plataforma</label>
                    <textarea id="motivo" name="motivo" rows="4"
                              class="form-control @error('motivo') form-control--error @enderror"
                              placeholder="Cuéntanos sobre ti y por qué te gustaría formar parte de LOBBY69..." required>{{ old('motivo') }}</textarea>
                    @error('motivo')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="terminos_aceptados" value="1"
                               {{ old('terminos_aceptados') ? 'checked' : '' }} required>
                        <span>Acepto los <a href="#" class="link">Términos y Condiciones</a></span>
                    </label>
                    @error('terminos_aceptados')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="privacidad_aceptada" value="1"
                               {{ old('privacidad_aceptada') ? 'checked' : '' }} required>
                        <span>Acepto la <a href="#" class="link">Política de Privacidad</a></span>
                    </label>
                    @error('privacidad_aceptada')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="btn btn--primary btn--lg btn--block">
                    <i class="fas fa-paper-plane"></i> Enviar Solicitud
                </button>
            </form>

            <div class="auth-card__footer">
                <p>¿Ya tienes cuenta? <a href="{{ route('login') }}" class="link">Inicia sesión</a></p>
            </div>
        </div>
    </div>
</section>
@endsection
