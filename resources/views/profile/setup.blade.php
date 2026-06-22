@extends('layouts.app')
@section('title', 'Configura tu Perfil — LOBBY69')
@section('content')
<div style="max-width:800px;margin:2rem auto;padding:0 1rem;">

  <div style="text-align:center;margin-bottom:2rem;">
    <h1 style="font-size:2rem;font-weight:800;color:var(--color-text);">Configura tu Perfil</h1>
    <p style="color:#64748b;">Completa tu perfil para acceder a la comunidad LOBBY69</p>
  </div>

  @if(session('warning'))
  <div style="background:#fef3c7;border:1px solid #f59e0b;color:#92400e;padding:1rem;border-radius:10px;margin-bottom:1.5rem;">
    ⚠️ {{ session('warning') }}
  </div>
  @endif

  @if($errors->any())
  <div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:1rem;border-radius:10px;margin-bottom:1.5rem;">
    <ul style="margin:0;padding-left:1.2rem;">
      @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
  </div>
  @endif

  <form method="POST" action="{{ route('profile.store') }}" id="profileForm">
    @csrf

    {{-- SECCION 1: Info Personal --}}
    <div style="background:white;border-radius:16px;box-shadow:0 4px 16px rgba(0,0,0,.08);padding:2rem;margin-bottom:1.5rem;">
      <h2 style="font-size:1.1rem;font-weight:700;margin-bottom:1.5rem;padding-bottom:.75rem;border-bottom:2px solid #f1f5f9;">
        👤 Información Personal
      </h2>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">

        {{-- Nick --}}
        <div>
          <label style="display:block;font-weight:600;font-size:.9rem;color:#374151;margin-bottom:.4rem;">
            Nick <span style="color:#ef4444;">*</span>
            @if($profile?->nickname)
              <span style="color:#10b981;font-size:.75rem;font-weight:400;"> (fijo, no editable)</span>
            @endif
          </label>
          <input type="text" name="nickname"
                 value="{{ old('nickname', $profile?->nickname ?? '') }}"
                 {{ $profile?->nickname ? 'readonly' : '' }}
                 placeholder="ej: sexy_duo2"
                 style="width:100%;padding:.7rem 1rem;border:2px solid #e5e7eb;border-radius:10px;font-size:.95rem;box-sizing:border-box;{{ $profile?->nickname ? 'background:#f9fafb;color:#6b7280;' : '' }}">
          <span style="font-size:.75rem;color:#9ca3af;">Solo letras, números y guiones bajos</span>
        </div>

        {{-- Tipo de perfil --}}
        <div>
          <label style="display:block;font-weight:600;font-size:.9rem;color:#374151;margin-bottom:.4rem;">
            ¿Qué eres? <span style="color:#ef4444;">*</span>
          </label>
          <select name="profile_type" id="profileType"
                  style="width:100%;padding:.7rem 1rem;border:2px solid #e5e7eb;border-radius:10px;font-size:.95rem;box-sizing:border-box;"
                  onchange="togglePartner(this.value)">
            <option value="single"    {{ old('profile_type',$profile?->profile_type??'single')==='single'    ?'selected':'' }}>Single</option>
            <option value="pareja"    {{ old('profile_type',$profile?->profile_type??'')==='pareja'    ?'selected':'' }}>Pareja</option>
            <option value="unicornio" {{ old('profile_type',$profile?->profile_type??'')==='unicornio' ?'selected':'' }}>Unicornio</option>
          </select>
        </div>

        {{-- Nombre --}}
        <div>
          <label style="display:block;font-weight:600;font-size:.9rem;color:#374151;margin-bottom:.4rem;">Nombre <span style="color:#ef4444;">*</span></label>
          <input type="text" name="display_name" value="{{ old('display_name',$profile?->display_name??$user->name??'') }}"
                 style="width:100%;padding:.7rem 1rem;border:2px solid #e5e7eb;border-radius:10px;font-size:.95rem;box-sizing:border-box;">
        </div>

        {{-- Edad --}}
        <div>
          <label style="display:block;font-weight:600;font-size:.9rem;color:#374151;margin-bottom:.4rem;">Edad <span style="color:#ef4444;">*</span></label>
          <input type="number" name="age" min="18" max="99" value="{{ old('age',$profile?->age??'') }}"
                 style="width:100%;padding:.7rem 1rem;border:2px solid #e5e7eb;border-radius:10px;font-size:.95rem;box-sizing:border-box;">
        </div>

        {{-- Género --}}
        <div>
          <label style="display:block;font-weight:600;font-size:.9rem;color:#374151;margin-bottom:.4rem;">Género <span style="color:#ef4444;">*</span></label>
          <select name="gender" style="width:100%;padding:.7rem 1rem;border:2px solid #e5e7eb;border-radius:10px;font-size:.95rem;box-sizing:border-box;">
            <option value="masculino" {{ old('gender',$profile?->gender??'')==='masculino'?'selected':'' }}>Hombre</option>
            <option value="femenino"  {{ old('gender',$profile?->gender??'')==='femenino' ?'selected':'' }}>Mujer</option>
            <option value="otro"      {{ old('gender',$profile?->gender??'')==='otro'     ?'selected':'' }}>Otro</option>
          </select>
        </div>

        {{-- Orientacion --}}
        <div>
          <label style="display:block;font-weight:600;font-size:.9rem;color:#374151;margin-bottom:.4rem;">Orientación</label>
          <select name="orientation" style="width:100%;padding:.7rem 1rem;border:2px solid #e5e7eb;border-radius:10px;font-size:.95rem;box-sizing:border-box;">
            <option value="">Prefiero no decir</option>
            <option value="heterosexual" {{ old('orientation',$profile?->orientation??'')==='heterosexual'?'selected':'' }}>Heterosexual</option>
            <option value="bisexual"     {{ old('orientation',$profile?->orientation??'')==='bisexual'    ?'selected':'' }}>Bisexual</option>
            <option value="homosexual"   {{ old('orientation',$profile?->orientation??'')==='homosexual'  ?'selected':'' }}>Homosexual</option>
            <option value="otro"         {{ old('orientation',$profile?->orientation??'')==='otro'        ?'selected':'' }}>Otro</option>
          </select>
        </div>

        {{-- Bio --}}
        <div style="grid-column:1/-1;">
          <label style="display:block;font-weight:600;font-size:.9rem;color:#374151;margin-bottom:.4rem;">Descripción</label>
          <textarea name="bio" rows="3" maxlength="500" placeholder="Cuéntanos algo sobre ti..."
                    style="width:100%;padding:.7rem 1rem;border:2px solid #e5e7eb;border-radius:10px;font-size:.95rem;box-sizing:border-box;resize:vertical;">{{ old('bio',$profile?->bio??'') }}</textarea>
        </div>
      </div>
    </div>

    {{-- SECCION PAREJA --}}
    <div id="seccionPareja" style="display:none;background:white;border-radius:16px;box-shadow:0 4px 16px rgba(0,0,0,.08);padding:2rem;margin-bottom:1.5rem;">
      <h2 style="font-size:1.1rem;font-weight:700;margin-bottom:1.5rem;padding-bottom:.75rem;border-bottom:2px solid #f1f5f9;">
        💑 Información de la Pareja
      </h2>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">
        <div>
          <label style="display:block;font-weight:600;font-size:.9rem;color:#374151;margin-bottom:.4rem;">Nombre</label>
          <input type="text" name="partner_name" value="{{ old('partner_name',$profile?->partner_name??'') }}"
                 style="width:100%;padding:.7rem 1rem;border:2px solid #e5e7eb;border-radius:10px;font-size:.95rem;box-sizing:border-box;">
        </div>
        <div>
          <label style="display:block;font-weight:600;font-size:.9rem;color:#374151;margin-bottom:.4rem;">Edad</label>
          <input type="number" name="partner_age" min="18" max="99" value="{{ old('partner_age',$profile?->partner_age??'') }}"
                 style="width:100%;padding:.7rem 1rem;border:2px solid #e5e7eb;border-radius:10px;font-size:.95rem;box-sizing:border-box;">
        </div>
        <div>
          <label style="display:block;font-weight:600;font-size:.9rem;color:#374151;margin-bottom:.4rem;">Género</label>
          <select name="partner_gender" style="width:100%;padding:.7rem 1rem;border:2px solid #e5e7eb;border-radius:10px;font-size:.95rem;box-sizing:border-box;">
            <option value="">Seleccionar</option>
            <option value="masculino" {{ old('partner_gender',$profile?->partner_gender??'')==='masculino'?'selected':'' }}>Hombre</option>
            <option value="femenino"  {{ old('partner_gender',$profile?->partner_gender??'')==='femenino' ?'selected':'' }}>Mujer</option>
            <option value="otro"      {{ old('partner_gender',$profile?->partner_gender??'')==='otro'     ?'selected':'' }}>Otro</option>
          </select>
        </div>
        <div>
          <label style="display:block;font-weight:600;font-size:.9rem;color:#374151;margin-bottom:.4rem;">Descripción</label>
          <textarea name="partner_bio" rows="3"
                    style="width:100%;padding:.7rem 1rem;border:2px solid #e5e7eb;border-radius:10px;font-size:.95rem;box-sizing:border-box;resize:vertical;">{{ old('partner_bio',$profile?->partner_bio??'') }}</textarea>
        </div>
      </div>
    </div>
    {{-- SECCION UBICACION --}}
    <div x-data="ubicacionApp()" x-init="init()"
         style="background:white;border-radius:16px;box-shadow:0 4px 16px rgba(0,0,0,.08);padding:2rem;margin-bottom:1.5rem;">
      <h2 style="font-size:1.1rem;font-weight:700;margin-bottom:1.5rem;padding-bottom:.75rem;border-bottom:2px solid #f1f5f9;">
        📍 Ubicación
      </h2>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">

        {{-- PAÍS --}}
        <div>
          <label style="display:block;font-weight:600;font-size:.9rem;color:#374151;margin-bottom:.4rem;">
            País <span style="color:#ef4444;">*</span>
          </label>
          <select name="country"
                  x-model="pais"
                  @change="onPaisChange()"
                  style="width:100%;padding:.7rem 1rem;border:2px solid #e5e7eb;border-radius:10px;font-size:.95rem;box-sizing:border-box;">
            <option value="">— Selecciona un país —</option>
            <option value="Mexico">México</option>
            <option value="Argentina">Argentina</option>
            <option value="Colombia">Colombia</option>
            <option value="Chile">Chile</option>
            <option value="España">España</option>
            <option value="Estados Unidos">Estados Unidos</option>
            <option value="Otro">Otro</option>
          </select>
        </div>

        {{-- ESTADO --}}
        <div>
          <label style="display:block;font-weight:600;font-size:.9rem;color:#374151;margin-bottom:.4rem;">
            Estado / Provincia
          </label>
          <select name="state"
                  x-model="estado"
                  @change="onEstadoChange()"
                  style="width:100%;padding:.7rem 1rem;border:2px solid #e5e7eb;border-radius:10px;font-size:.95rem;box-sizing:border-box;">
            <option value="">— Selecciona un estado —</option>
            <template x-for="s in estados" :key="s">
              <option :value="s" x-text="s"></option>
            </template>
          </select>
        </div>

        {{-- CIUDAD: input libre O combo alcaldías --}}
        <div style="grid-column:1/-1;">
          <label style="display:block;font-weight:600;font-size:.9rem;color:#374151;margin-bottom:.4rem;">
            Ciudad / Alcaldía
          </label>

          {{-- Input texto (cualquier ciudad que NO sea CDMX) --}}
          <input
            x-show="!esCDMX()"
            type="text"
            name="city"
            x-model="ciudad"
            placeholder="Ej. Guadalajara"
            maxlength="100"
            style="width:100%;padding:.7rem 1rem;border:2px solid #e5e7eb;border-radius:10px;font-size:.95rem;box-sizing:border-box;">

          {{-- Select alcaldías (solo cuando estado = Ciudad de México) --}}
          <select
            x-show="esCDMX()"
            name="city"
            x-model="ciudad"
            style="width:100%;padding:.7rem 1rem;border:2px solid #e5e7eb;border-radius:10px;font-size:.95rem;box-sizing:border-box;">
            <option value="">— Selecciona alcaldía —</option>
            <template x-for="a in alcaldias" :key="a">
              <option :value="a" x-text="a"></option>
            </template>
          </select>
        </div>

      </div>
    </div>

    <script>
    function ubicacionApp() {
      return {
        pais:   '{{ old("country", $profile->country ?? "") }}',
        estado: '{{ old("state",   $profile->state   ?? "") }}',
        ciudad: '{{ old("city",    $profile->city    ?? "") }}',

        estados: [],
        alcaldias: [
          'Álvaro Obregón','Azcapotzalco','Benito Juárez','Coyoacán',
          'Cuajimalpa de Morelos','Cuauhtémoc','Gustavo A. Madero',
          'Iztacalco','Iztapalapa','La Magdalena Contreras',
          'Miguel Hidalgo','Milpa Alta','Tláhuac','Tlalpan',
          'Venustiano Carranza','Xochimilco'
        ],

        estadosPorPais: {
          'Mexico':        ['Aguascalientes','Baja California','Baja California Sur','Campeche','Chiapas','Chihuahua','Ciudad de México','Coahuila','Colima','Durango','Estado de México','Guanajuato','Guerrero','Hidalgo','Jalisco','Michoacán','Morelos','Nayarit','Nuevo León','Oaxaca','Puebla','Querétaro','Quintana Roo','San Luis Potosí','Sinaloa','Sonora','Tabasco','Tamaulipas','Tlaxcala','Veracruz','Yucatán','Zacatecas'],
          'Argentina':     ['Buenos Aires','Córdoba','Rosario','Mendoza','Tucumán','La Plata'],
          'Colombia':      ['Bogotá','Medellín','Cali','Barranquilla','Cartagena'],
          'Chile':         ['Santiago','Valparaíso','Concepción','La Serena','Antofagasta'],
          'España':        ['Madrid','Barcelona','Valencia','Sevilla','Bilbao','Málaga'],
          'Estados Unidos':['New York','Los Angeles','Chicago','Houston','Miami','Dallas'],
          'Otro':          []
        },

        init() {
          this.estados = this.estadosPorPais[this.pais] || [];
        },

        onPaisChange() {
          this.estados = this.estadosPorPais[this.pais] || [];
          this.estado  = '';
          this.ciudad  = '';
        },

        onEstadoChange() {
          if (!this.esCDMX()) this.ciudad = '';
        },

        esCDMX() {
          return this.estado === 'Ciudad de México';
        }
      };
    }
    </script>

    {{-- SECCION QUE BUSCAS --}}
    <div style="background:white;border-radius:16px;box-shadow:0 4px 16px rgba(0,0,0,.08);padding:2rem;margin-bottom:1.5rem;">
      <h2 style="font-size:1.1rem;font-weight:700;margin-bottom:1.5rem;padding-bottom:.75rem;border-bottom:2px solid #f1f5f9;">
        🔍 ¿Qué buscas?
      </h2>
      @php
        $lookingFor = json_decode($profile?->looking_for ?? '[]', true) ?? [];
        $lookingForOpts = ['Parejas heterosexuales','Parejas bisexuales','Parejas (ella bisexual)','Parejas (él bisexual)','Hombres heterosexuales','Hombres bisexuales','Mujeres heterosexuales','Mujeres bisexuales'];
      @endphp
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem;">
        @foreach($lookingForOpts as $opt)
        <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.9rem;">
          <input type="checkbox" name="looking_for[]" value="{{ $opt }}"
                 {{ in_array($opt, old('looking_for',$lookingFor)) ? 'checked' : '' }}
                 style="width:18px;height:18px;accent-color:#8b5cf6;">
          {{ $opt }}
        </label>
        @endforeach
      </div>
    </div>

    {{-- SECCION PARA QUE --}}
    <div style="background:white;border-radius:16px;box-shadow:0 4px 16px rgba(0,0,0,.08);padding:2rem;margin-bottom:1.5rem;">
      <h2 style="font-size:1.1rem;font-weight:700;margin-bottom:1.5rem;padding-bottom:.75rem;border-bottom:2px solid #f1f5f9;">
        💫 ¿Para qué?
      </h2>
      @php
        $interestsList = json_decode($profile?->interests ?? '[]', true) ?? [];
        $interestOpts = ['Intercambio completo','Tríos','Cuckold','Cybersexo','Amistad','Intercambio light','Sólo ellas','Prácticas BDSM','Sexo en grupo','Mirar y ser vistos','Compartir fetiches','Intercambio de fotos','Relaciones abiertas','Otros'];
      @endphp
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem;">
        @foreach($interestOpts as $opt)
        <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.9rem;">
          <input type="checkbox" name="interests[]" value="{{ $opt }}"
                 {{ in_array($opt, old('interests',$interestsList)) ? 'checked' : '' }}
                 style="width:18px;height:18px;accent-color:#8b5cf6;">
          {{ $opt }}
        </label>
        @endforeach
      </div>
    </div>

    {{-- SECCION PRIVACIDAD --}}
    <div style="background:white;border-radius:16px;box-shadow:0 4px 16px rgba(0,0,0,.08);padding:2rem;margin-bottom:1.5rem;">
      <h2 style="font-size:1.1rem;font-weight:700;margin-bottom:1.5rem;padding-bottom:.75rem;border-bottom:2px solid #f1f5f9;">
        🔒 No quiero que vean mi perfil...
      </h2>
      @php
        $privacy = json_decode($profile?->privacy_settings ?? '[]', true) ?? [];
        $privacyOpts = ['Chicos solos','Locales/Clubs','Miembros Basic que no pagan y que no son amigos míos','Extranjeros (miembros de otros países) que no son amigos míos'];
      @endphp
      <div style="background:#fff5f5;border:1px solid #fca5a5;border-radius:10px;padding:1rem;">
        <p style="font-weight:600;color:#991b1b;margin:0 0 1rem;font-size:.9rem;">Selecciona quién NO debe ver tu perfil:</p>
        <div style="display:grid;gap:.75rem;">
          @foreach($privacyOpts as $opt)
          <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.9rem;">
            <input type="checkbox" name="privacy_settings[]" value="{{ $opt }}"
                   {{ in_array($opt, old('privacy_settings',$privacy)) ? 'checked' : '' }}
                   style="width:18px;height:18px;accent-color:#ef4444;">
            {{ $opt }}
          </label>
          @endforeach
        </div>
      </div>
    </div>

    {{-- SECCION NOTIFICACIONES --}}
    <div style="background:white;border-radius:16px;box-shadow:0 4px 16px rgba(0,0,0,.08);padding:2rem;margin-bottom:2rem;">
      <h2 style="font-size:1.1rem;font-weight:700;margin-bottom:1.5rem;padding-bottom:.75rem;border-bottom:2px solid #f1f5f9;">
        🔔 Avisarme por e-mail
      </h2>
      @php
        $notifs = json_decode($profile?->notifications ?? '[]', true) ?? [];
        $notifOpts = ['Cuando me envíen un mensaje','Cuando alguien envíe o acepte una solicitud de amistad','Cuando alguien verifique mi perfil','Cuando a alguien le guste mi perfil o una foto','Cuando alguien escriba en mi muro','Cuando alguien comente una de mis fotos','Cuando alguien comente una foto que yo comenté','Cuando alguien interesante esté disponible HOY cerca mío'];
      @endphp
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem;">
        @foreach($notifOpts as $opt)
        <label style="display:flex;align-items:flex-start;gap:.5rem;cursor:pointer;font-size:.85rem;">
          <input type="checkbox" name="notifications[]" value="{{ $opt }}"
                 {{ in_array($opt, old('notifications',$notifs)) ? 'checked' : '' }}
                 style="width:18px;height:18px;accent-color:#8b5cf6;margin-top:2px;flex-shrink:0;">
          {{ $opt }}
        </label>
        @endforeach
      </div>
    </div>

    {{-- BOTONES --}}
    <div style="display:flex;gap:1rem;justify-content:flex-end;margin-bottom:3rem;">
      <a href="{{ route('dashboard') }}" class="btn btn--ghost">Cancelar</a>
      <button type="submit" class="btn btn--primary" style="min-width:180px;">
        💾 Guardar Cambios
      </button>
    </div>

  
</form>
</div>

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@push('scripts')
<script>
function togglePartner(type) {
    var sec = document.getElementById('seccionPareja');
    sec.style.display = (type === 'pareja') ? 'block' : 'none';
}
// Inicializar al cargar
document.addEventListener('DOMContentLoaded', function() {
    togglePartner(document.getElementById('profileType').value);
});
</script>
@endpush
@endsection