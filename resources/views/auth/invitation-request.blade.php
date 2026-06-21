@extends('layouts.app')

@section('title', 'Solicitar Invitación')

@section('content')
<section class="auth-section">
    <div class="container">
        <div class="auth-card card">
            <div class="auth-card__header">
                <img src="{{ asset('img/logo-lobby69.png') }}" alt="LOBBY69" class="auth-card__logo"
                     onerror="this.style.display='none'">
                <h1>Solicitar Invitación</h1>
                <p>Completa el formulario para unirte a la comunidad LOBBY69</p>
            </div>

            <form method="POST" action="{{ route('invitation.store') }}" class="auth-form">
                @csrf
                {{-- Mostrar errores generales --}}
                @if($errors->any())
                    <div style="background:#fef2f2; border:1px solid #fca5a5; border-radius:8px; padding:1rem; margin-bottom:1rem;">
                        <p style="color:#dc2626; font-weight:600; margin-bottom:.5rem;">
                            <i class="fas fa-exclamation-triangle"></i> Por favor corrige los siguientes errores:
                        </p>
                        <ul style="list-style:disc; padding-left:1.5rem; color:#dc2626;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Código de invitación (opcional) -->
                <div class="form-group">
                    <label for="invitation_code" class="form-label">
                        ¿Tienes código de invitación?
                        <span class="text-xs text-muted">(Opcional)</span>
                    </label>
                    <input type="text" id="invitation_code" name="invitation_code"
                           class="form-control @error('invitation_code') form-control--error @enderror"
                           value="{{ old('invitation_code') }}"
                           placeholder="Ej: LOBBY69-ABC123" maxlength="20">
                    @error('invitation_code')
                        <span class="form-error">{{ $message }}</span>
                    @else
                        <span class="form-error text-xs text-muted">Si no tienes código, déjalo vacío. Un administrador revisará tu solicitud.</span>
                    @enderror
                </div>

                <hr style="border: none; border-top: 1px solid rgba(44,62,80,0.08); margin: 1rem 0;">

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
                        <label class="radio-label">
                            <input type="radio" name="tipo_perfil" value="single"
                                   {{ old('tipo_perfil') === 'single' ? 'checked' : '' }} required>
                            <span><strong>Single</strong> — Chico solo</span>
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="tipo_perfil" value="unicornio"
                                   {{ old('tipo_perfil') === 'unicornio' ? 'checked' : '' }}>
                            <span><strong>Unicornio</strong> — Chica sola</span>
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="tipo_perfil" value="pareja"
                                   {{ old('tipo_perfil') === 'pareja' ? 'checked' : '' }}>
                            <span><strong>Pareja</strong> — HM / MM / HH</span>
                        </label>
                    </div>
                    @error('tipo_perfil')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- ── País + Edad ── --}}
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
                <select id="pais" name="pais"
                        class="form-control @error('pais') form-control--error @enderror" required>
                    <option value="">Selecciona</option>
                    <option value="México"         {{ old('pais') === 'México'         ? 'selected' : '' }}>México</option>
                    <option value="Estados Unidos" {{ old('pais') === 'Estados Unidos' ? 'selected' : '' }}>Estados Unidos</option>
                    <option value="Canadá"         {{ old('pais') === 'Canadá'         ? 'selected' : '' }}>Canadá</option>
                    <option value="Colombia"       {{ old('pais') === 'Colombia'       ? 'selected' : '' }}>Colombia</option>
                    <option value="Argentina"      {{ old('pais') === 'Argentina'      ? 'selected' : '' }}>Argentina</option>
                    <option value="España"         {{ old('pais') === 'España'         ? 'selected' : '' }}>España</option>
                    <option value="otro"           {{ old('pais') === 'otro'           ? 'selected' : '' }}>Otro país</option>
                </select>
                @error('pais')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>
        </div>

        {{-- ── Estado dinámico: catálogo México / texto libre otros países ── --}}
        <div class="form-row">

            {{-- Bloque México (select con catálogo) --}}
            <div class="form-group" id="bloque-estado-mexico">
                <label for="estado_mx" class="form-label">Estado</label>
                <select id="estado_mx" name="estado"
                        class="form-control @error('estado') form-control--error @enderror">
                    <option value="">Selecciona un estado</option>
                    @foreach([
                        'Aguascalientes','Baja California','Baja California Sur','Campeche',
                        'CDMX','Chiapas','Chihuahua','Coahuila','Colima','Durango',
                        'Estado de México','Guanajuato','Guerrero','Hidalgo','Jalisco',
                        'Michoacán','Morelos','Nayarit','Nuevo León','Oaxaca','Puebla',
                        'Querétaro','Quintana Roo','San Luis Potosí','Sinaloa','Sonora',
                        'Tabasco','Tamaulipas','Tlaxcala','Veracruz','Yucatán','Zacatecas'
                    ] as $edo)
                        <option value="{{ $edo }}" {{ old('estado') === $edo ? 'selected' : '' }}>
                            {{ $edo }}
                        </option>
                    @endforeach
                </select>
                @error('estado')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- Bloque Otro País (input texto libre) --}}
            <div class="form-group" id="bloque-estado-otro" style="display:none;">
                <label for="estado_otro" class="form-label">Estado / Región</label>
                <input type="text" id="estado_otro"
                    class="form-control"
                    placeholder="Escribe tu estado o región"
                    value="{{ old('pais') !== 'México' ? old('estado') : '' }}">
                {{-- Este input NO tiene name; el JS lo copia al hidden cuando aplica --}}
            </div>

            {{-- Campo oculto real que se envía cuando el país NO es México --}}
            <input type="hidden" id="estado_hidden" name="estado_hidden" value="">

            <div class="form-group">
                <label for="municipio" class="form-label">Municipio / Ciudad</label>
                <input type="text" id="municipio" name="municipio"
                    class="form-control @error('municipio') form-control--error @enderror"
                    value="{{ old('municipio') }}" placeholder="Ej: Benito Juárez">
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
                        <span>Acepto los <a href="#" class="link" data-modal="modalTerminos">Términos y Condiciones</a></span>
                    </label>
                    @error('terminos_aceptados')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="privacidad_aceptada" value="1"
                               {{ old('privacidad_aceptada') ? 'checked' : '' }} required>
                        <span>Acepto la <a href="#" class="link" data-modal="modalPrivacidad">Política de Privacidad</a></span>
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
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const paisSelect        = document.getElementById('pais');
    const bloqueEstadoMx    = document.getElementById('bloque-estado-mexico');
    const bloqueEstadoOtro  = document.getElementById('bloque-estado-otro');
    const selectMx          = document.getElementById('estado_mx');
    const inputOtro         = document.getElementById('estado_otro');
    const hiddenEstado      = document.getElementById('estado_hidden');

    function toggleEstado() {
        const pais = paisSelect.value;

        if (pais === 'México') {
            // Mostrar catálogo México
            bloqueEstadoMx.style.display   = 'block';
            bloqueEstadoOtro.style.display = 'none';

            // El select de México tiene name="estado", el hidden no se usa
            selectMx.name   = 'estado';
            hiddenEstado.name = '';
            inputOtro.value = '';

        } else if (pais !== '') {
            // Mostrar campo libre
            bloqueEstadoMx.style.display   = 'none';
            bloqueEstadoOtro.style.display = 'block';

            // Quitamos name al select de México para que no se envíe
            selectMx.name     = '';
            // El hidden tomará el valor del texto libre
            hiddenEstado.name = 'estado';
            hiddenEstado.value = inputOtro.value;

        } else {
            bloqueEstadoMx.style.display   = 'block';
            bloqueEstadoOtro.style.display = 'none';
            selectMx.name   = 'estado';
            hiddenEstado.name = '';
        }
    }

    // Sincronizar el input libre → hidden en tiempo real
    inputOtro.addEventListener('input', function () {
        hiddenEstado.value = this.value;
    });

    paisSelect.addEventListener('change', toggleEstado);

    // Ejecutar al cargar por si hay old() values
    toggleEstado();
});
</script>
@endpush

@endsection
