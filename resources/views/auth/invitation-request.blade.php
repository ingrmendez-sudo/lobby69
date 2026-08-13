@extends('layouts.minimal')

@section('title', 'Solicitar Invitación')

@section('content')
<section class="auth-section" style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem 1rem;">
    <div style="width:100%;max-width:520px;">
        <div class="auth-card card" style="padding:2rem 2.5rem;">
            <div class="auth-card__header" style="text-align:center;margin-bottom:1.5rem;">
                <img loading="eager" src="{{ asset('img/logo-lobby69.png') }}" alt="LOBBY69"
                     style="height:48px;margin-bottom:1rem;"
                     onerror="this.style.display='none'">
                <h1 style="font-size:1.6rem;font-weight:700;margin-bottom:.3rem;">Solicitar Invitación</h1>
                <p style="color:var(--theme-muted);font-size:.9rem;">Completa el formulario para unirte a la comunidad LOBBY69</p>
            </div>

            <form method="POST" action="{{ route('invitation.store') }}" class="auth-form" id="invitation-form">
                @csrf

                @if($errors->any())
                    <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;padding:1rem;margin-bottom:1.2rem;">
                        <p style="color:#dc2626;font-weight:600;margin-bottom:.5rem;">
                            <i class="fas fa-exclamation-triangle"></i> Por favor corrige los siguientes errores:
                        </p>
                        <ul style="list-style:disc;padding-left:1.5rem;color:#dc2626;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Código de invitación --}}
                <div class="form-group" style="margin-bottom:1.2rem;">
                    @if(!empty($refCode))
                        <input type="hidden" name="invitation_code" value="{{ $refCode }}">
                        <div style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);border:1.5px solid #86efac;border-radius:.6rem;padding:.85rem 1.1rem;display:flex;align-items:center;gap:.6rem;">
                            <span style="font-size:1.2rem;">🔑</span>
                            <div>
                                <div style="font-weight:600;color:#166534;font-size:.9rem;">Invitación con código activo</div>
                                <div style="color:#15803d;font-size:.8rem;margin-top:.1rem;">
                                    Código: <strong style="font-family:monospace;">{{ $refCode }}</strong>
                                    — Tu solicitud tendrá prioridad de aprobación.
                                </div>
                            </div>
                        </div>
                    @else
                        <label for="invitation_code" class="form-label">
                            ¿Tienes código de invitación? <span style="color:var(--theme-muted);font-size:.8rem;">(Opcional)</span>
                        </label>
                        <input type="text" id="invitation_code" name="invitation_code"
                               class="form-control @error('invitation_code') form-control--error @enderror"
                               value="{{ old('invitation_code') }}"
                               placeholder="Ej: LOBBY69-ABC123" maxlength="20">
                        <span style="font-size:.75rem;color:var(--theme-muted);">Si no tienes código, déjalo vacío. Un administrador revisará tu solicitud.</span>
                    @endif
                </div>

                <hr style="border:none;border-top:1px solid rgba(0,0,0,.08);margin:1rem 0;">

                {{-- Nombre completo --}}
                <div class="form-group" style="margin-bottom:1rem;">
                    <label for="nombre_completo" class="form-label">Nombre completo</label>
                    <input type="text" id="nombre_completo" name="nombre_completo"
                           class="form-control @error('nombre_completo') form-control--error @enderror"
                           value="{{ old('nombre_completo') }}"
                           placeholder="Ej: Carlos Hernández" required>
                    @error('nombre_completo')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                {{-- Nick --}}
                <div class="form-group" style="margin-bottom:1rem;">
                    <label for="nombre" class="form-label">Nick con el que te gustaría darte de alta. Este dato NO se podrá cambiar en la plataforma.</label>
                    <input type="text" id="nombre" name="nombre"
                           class="form-control @error('nombre') form-control--error @enderror"
                           value="{{ old('nombre') }}"
                           placeholder="Ej: Carlos_CDMX" required maxlength="30">
                    @error('nombre')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                {{-- Email --}}
                <div class="form-group" style="margin-bottom:1rem;">
                    <label for="email" class="form-label">Correo electrónico</label>
                    <input type="email" id="email" name="email"
                           class="form-control @error('email') form-control--error @enderror"
                           value="{{ old('email') }}"
                           placeholder="tu@correo.com" required>
                    @error('email')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                {{-- Tipo de perfil --}}
                <div class="form-group" style="margin-bottom:1rem;">
                    <label class="form-label">Tipo de perfil</label>
                    <div style="display:flex;flex-direction:column;gap:.5rem;margin-top:.3rem;">
                        @foreach([
                            'single'    => 'Single — Chico solo',
                            'unicornio' => 'Unicornio — Chica sola',
                            'pareja'    => 'Pareja — Pareja mixta o del mismo sexo',
                        ] as $val => $label)
                            <label style="display:flex;align-items:center;gap:.6rem;padding:.6rem .9rem;border:1.5px solid {{ old('tipo_perfil') === $val ? '#7c3aed' : 'rgba(0,0,0,.1)' }};border-radius:.5rem;cursor:pointer;font-size:.9rem;">
                                <input type="radio" name="tipo_perfil" value="{{ $val }}"
                                       {{ old('tipo_perfil') === $val ? 'checked' : '' }} required>
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                    @error('tipo_perfil')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                {{-- Edad --}}
                <div class="form-group" style="margin-bottom:1rem;">
                    <label for="edad" class="form-label">Edad</label>
                    <input type="number" id="edad" name="edad"
                           class="form-control @error('edad') form-control--error @enderror"
                           value="{{ old('edad') }}"
                           placeholder="Ej: 28" min="18" max="99" required>
                    @error('edad')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                {{-- País --}}
                <div class="form-group" style="margin-bottom:1rem;">
                    <label for="pais" class="form-label">País</label>
                    <select id="pais" name="pais"
                            class="form-control @error('pais') form-control--error @enderror">
                        <option value="México" {{ old('pais','México') === 'México' ? 'selected' : '' }}>México</option>
                        <option value="Argentina" {{ old('pais') === 'Argentina' ? 'selected' : '' }}>Argentina</option>
                        <option value="Colombia" {{ old('pais') === 'Colombia' ? 'selected' : '' }}>Colombia</option>
                        <option value="España" {{ old('pais') === 'España' ? 'selected' : '' }}>España</option>
                        <option value="Chile" {{ old('pais') === 'Chile' ? 'selected' : '' }}>Chile</option>
                        <option value="Otro" {{ old('pais') === 'Otro' ? 'selected' : '' }}>Otro</option>
                    </select>
                    @error('pais')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                {{-- Estado / Región --}}
                <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
                    <div class="form-group" id="bloque-estado-mexico">
                        <label for="estado_mx" class="form-label">Estado</label>
                        <select id="estado_mx" name="estado"
                                class="form-control @error('estado') form-control--error @enderror">
                            <option value="">Selecciona un estado</option>
                            @foreach(['Aguascalientes','Baja California','Baja California Sur','Campeche',
                                'CDMX','Chiapas','Chihuahua','Coahuila','Colima','Durango',
                                'Estado de México','Guanajuato','Guerrero','Hidalgo','Jalisco',
                                'Michoacán','Morelos','Nayarit','Nuevo León','Oaxaca','Puebla',
                                'Querétaro','Quintana Roo','San Luis Potosí','Sinaloa','Sonora',
                                'Tabasco','Tamaulipas','Tlaxcala','Veracruz','Yucatán','Zacatecas'
                            ] as $edo)
                                <option value="{{ $edo }}" {{ old('estado') === $edo ? 'selected' : '' }}>{{ $edo }}</option>
                            @endforeach
                        </select>
                        @error('estado')<span class="form-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group" id="bloque-estado-otro" style="display:none;">
                        <label for="estado_otro" class="form-label">Estado / Región</label>
                        <input type="text" id="estado_otro" class="form-control"
                               placeholder="Tu estado o región"
                               value="{{ old('pais') !== 'México' ? old('estado') : '' }}">
                    </div>

                    <input type="hidden" id="estado_hidden" name="estado_hidden" value="">

                    <div class="form-group">
                        <label for="municipio" class="form-label">Municipio / Ciudad</label>
                        <input type="text" id="municipio" name="municipio"
                               class="form-control @error('municipio') form-control--error @enderror"
                               value="{{ old('municipio') }}" placeholder="Ej: Benito Juárez">
                        @error('municipio')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                {{-- Motivo con contador --}}
                <div class="form-group" style="margin-bottom:1rem;">
                    <label for="motivo" class="form-label">Motivo por el que quieres acceder a la plataforma</label>
                    <textarea id="motivo" name="motivo" rows="4"
                              class="form-control @error('motivo') form-control--error @enderror"
                              placeholder="Cuéntanos sobre ti y por qué te gustaría formar parte de LOBBY69... (mínimo 50 caracteres)"
                              minlength="50" maxlength="500" required
                              oninput="updateMotivoCounter(this)">{{ old('motivo') }}</textarea>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:.35rem;">
                        <span id="motivo-hint" style="font-size:.78rem;font-weight:600;color:#e74c3c;">
                            ⚠️ Mínimo 50 caracteres requeridos
                        </span>
                        <span style="font-size:.78rem;color:var(--theme-muted);">
                            <span id="motivo-current">0</span>/500
                        </span>
                    </div>
                    @error('motivo')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                {{-- Checkboxes --}}
                <div class="form-group" style="margin-bottom:.7rem;">
                    <label class="checkbox-label">
                        <input type="checkbox" name="terminos_aceptados" value="1"
                               {{ old('terminos_aceptados') ? 'checked' : '' }} required>
                        <span>Acepto los <a href="#" class="link" data-modal="modalTerminos">Términos y Condiciones</a></span>
                    </label>
                    @error('terminos_aceptados')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                <div class="form-group" style="margin-bottom:1.5rem;">
                    <label class="checkbox-label">
                        <input type="checkbox" name="privacidad_aceptada" value="1"
                               {{ old('privacidad_aceptada') ? 'checked' : '' }} required>
                        <span>Acepto la <a href="#" class="link" data-modal="modalPrivacidad">Política de Privacidad</a></span>
                    </label>
                    @error('privacidad_aceptada')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                <button type="submit" class="btn btn--primary btn--lg btn--block">
                    <i class="fas fa-paper-plane"></i> Enviar Solicitud
                </button>
            </form>

            <div class="auth-card__footer" style="text-align:center;margin-top:1.2rem;">
                <p style="font-size:.85rem;color:var(--theme-muted);">¿Ya tienes cuenta? <a href="{{ route('login') }}" class="link">Inicia sesión</a></p>
            </div>
        </div>
    </div>
</section>
@push('scripts')
<script>
// ── Contador de motivo ──────────────────────────────────────────────────────
function updateMotivoCounter(el) {
    var len  = el.value.length;
    var hint = document.getElementById('motivo-hint');
    document.getElementById('motivo-current').textContent = len;
    if (len < 50) {
        el.style.borderColor = '#e74c3c';
        hint.style.color     = '#e74c3c';
        hint.textContent     = '\u26A0\uFE0F Faltan ' + (50 - len) + ' caracteres';
    } else if (len >= 480) {
        el.style.borderColor = '#f39c12';
        hint.style.color     = '#f39c12';
        hint.textContent     = '\u26A1 Casi en el límite';
    } else {
        el.style.borderColor = '#27ae60';
        hint.style.color     = '#27ae60';
        hint.textContent     = '\u2705 Descripción válida';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    // ── País / Estado ────────────────────────────────────────────────────────
    var paisSelect       = document.getElementById('pais');
    var bloqueEstadoMx   = document.getElementById('bloque-estado-mexico');
    var bloqueEstadoOtro = document.getElementById('bloque-estado-otro');
    var selectMx         = document.getElementById('estado_mx');
    var inputOtro        = document.getElementById('estado_otro');
    var hiddenEstado     = document.getElementById('estado_hidden');

    function toggleEstado() {
        var pais = paisSelect.value;
        if (pais === 'México' || pais === '') {
            bloqueEstadoMx.style.display   = 'block';
            bloqueEstadoOtro.style.display = 'none';
            selectMx.name     = 'estado';
            hiddenEstado.name = '';
            inputOtro.value   = '';
        } else {
            bloqueEstadoMx.style.display   = 'none';
            bloqueEstadoOtro.style.display = 'block';
            selectMx.name     = '';
            hiddenEstado.name = 'estado';
            hiddenEstado.value = inputOtro.value;
        }
    }
    inputOtro.addEventListener('input', function () { hiddenEstado.value = this.value; });
    paisSelect.addEventListener('change', toggleEstado);
    toggleEstado();

    // ── Inicializar contador si hay old() ────────────────────────────────────
    var motivo = document.getElementById('motivo');
    if (motivo && motivo.value.length > 0) {
        updateMotivoCounter(motivo);
    }

    // ── Validación extra al enviar ───────────────────────────────────────────
    var form = document.getElementById('invitation-form');
    if (form && motivo) {
        form.addEventListener('submit', function (e) {
            if (motivo.value.trim().length < 50) {
                e.preventDefault();
                motivo.style.borderColor = '#e74c3c';
                motivo.scrollIntoView({ behavior: 'smooth', block: 'center' });
                var hint = document.getElementById('motivo-hint');
                hint.style.color = '#e74c3c';
                hint.textContent = '\u274C Escribe al menos 50 caracteres antes de enviar';
            }
        });
    }
});
</script>
@endpush
@endsection

