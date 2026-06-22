<?php
/**
 * fix_ubicacion_v2.php
 * Reescribe setup.blade.php con la sección de ubicación correcta
 * - Elimina el bloque duplicado que quedó fuera del form
 * - Reemplaza la sección original por la versión Alpine.js
 * - Todo dentro del <form>
 */

$blade = __DIR__ . '/resources/views/profile/setup.blade.php';

if (!file_exists($blade)) {
    die("❌ No se encontró: $blade\n");
}

// ── SECCIÓN DE UBICACIÓN CORRECTA (reemplaza la original) ─────────────────
$ubicacionVieja = <<<'OLD'
    {{-- SECCION UBICACION --}}
    <div style="background:white;border-radius:16px;box-shadow:0 4px 16px rgba(0,0,0,.08);padding:2rem;margin-bottom:1.5rem;">
      <h2 style="font-size:1.1rem;font-weight:700;margin-bottom:1.5rem;padding-bottom:.75rem;border-bottom:2px solid #f1f5f9;">
        📍 Ubicación
      </h2>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">
        <div>
          <label style="display:block;font-weight:600;font-size:.9rem;color:#374151;margin-bottom:.4rem;">Estado</label>
          <select name="state" style="width:100%;padding:.7rem 1rem;border:2px solid #e5e7eb;border-radius:10px;font-size:.95rem;box-sizing:border-box;">
            <option value="">Seleccionar estado</option>
            @foreach(['Aguascalientes','Baja California','Baja California Sur','Campeche','Chiapas','Chihuahua','Ciudad de México','Coahuila','Colima','Durango','Estado de México','Guanajuato','Guerrero','Hidalgo','Jalisco','Michoacán','Morelos','Nayarit','Nuevo León','Oaxaca','Puebla','Querétaro','Quintana Roo','San Luis Potosí','Sinaloa','Sonora','Tabasco','Tamaulipas','Tlaxcala','Veracruz','Yucatán','Zacatecas'] as $estado)
            <option value="{{ $estado }}" {{ old('state',$profile?->state??'')===$estado?'selected':'' }}>{{ $estado }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label style="display:block;font-weight:600;font-size:.9rem;color:#374151;margin-bottom:.4rem;">Ciudad</label>
          <input type="text" name="city" value="{{ old('city',$profile?->city??'') }}"
                 style="width:100%;padding:.7rem 1rem;border:2px solid #e5e7eb;border-radius:10px;font-size:.95rem;box-sizing:border-box;">
        </div>
      </div>
    </div>
OLD;

$ubicacionNueva = <<<'NEW'
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
NEW;

// ── BLOQUE DUPLICADO QUE QUEDÓ FUERA DEL FORM (eliminar completo) ──────────
// Empieza en el comentario ═══ SECCIÓN: UBICACIÓN y termina en </script> antes de </form>
$patronDuplicado = '/\s*\{\{--\s*═+\s*\n\s*SECCIÓN: UBICACIÓN.*?<\/script>\s*/si';

$content = file_get_contents($blade);

// Paso 1: Reemplazar la sección original dentro del form
if (strpos($content, 'SECCION UBICACION') !== false) {
    // Extraer el bloque exacto con un patrón flexible
    $patron = '/(\s*\{\{--\s*SECCION UBICACION\s*--\}\}\s*<div[^>]*>.*?<\/div>\s*<\/div>\s*<\/div>)/si';
    if (preg_match($patron, $content)) {
        $content = preg_replace($patron, "\n" . $ubicacionNueva, $content, 1);
        echo "✅ Sección original de ubicación reemplazada\n";
    } else {
        // Fallback: buscar por texto exacto
        if (strpos($content, $ubicacionVieja) !== false) {
            $content = str_replace($ubicacionVieja, $ubicacionNueva, $content);
            echo "✅ Sección original reemplazada (coincidencia exacta)\n";
        } else {
            echo "⚠️  No se encontró la sección original exacta. Intentando patrón alternativo...\n";
            // Buscar por el h2 de ubicación original
            $pat2 = '/(<div style="background:white[^>]*>)\s*(<h2[^>]*>\s*📍 Ubicación[^<]*<\/h2>)\s*(<div style="display:grid[^>]*>)\s*(<div>.*?<\/select>\s*<\/div>)\s*(<div>.*?<\/div>)\s*(<\/div>)\s*(<\/div>)/si';
            if (preg_match($pat2, $content)) {
                $content = preg_replace($pat2, $ubicacionNueva, $content, 1);
                echo "✅ Sección reemplazada con patrón h2\n";
            } else {
                echo "❌ No se pudo reemplazar automáticamente. Mostrando contexto...\n";
                $pos = strpos($content, '📍 Ubicación');
                if ($pos) {
                    echo "   Contexto en posición $pos:\n";
                    echo substr($content, max(0, $pos-100), 300) . "\n";
                }
            }
        }
    }
} else {
    echo "⚠️  No se encontró 'SECCION UBICACION' como comentario. Buscando por contenido...\n";
}

// Paso 2: Eliminar el bloque duplicado (el que quedó fuera del form)
if (preg_match($patronDuplicado, $content)) {
    $content = preg_replace($patronDuplicado, '', $content);
    echo "✅ Bloque Alpine duplicado (fuera del form) eliminado\n";
} else {
    // Buscar por el marcador exacto que usó el script anterior
    $marcador = '{{-- ═══════════════════════════════════════════════════
     SECCIÓN: UBICACIÓN
     ═══════════════════════════════════════════════════ --}}';
    if (strpos($content, $marcador) !== false) {
        // Encontrar desde el marcador hasta el </script> siguiente
        $start = strpos($content, $marcador);
        $end   = strpos($content, '</script>', $start);
        if ($end !== false) {
            $end += strlen('</script>');
            $content = substr($content, 0, $start) . substr($content, $end);
            echo "✅ Bloque duplicado eliminado (por marcador exacto)\n";
        }
    } else {
        // Buscar el div con x-data="ubicacionApp()" que está fuera del form
        $pat3 = '/<div class="form-section" x-data="ubicacionApp\(\)".*?<\/script>/si';
        if (preg_match($pat3, $content)) {
            $content = preg_replace($pat3, '', $content);
            echo "✅ Bloque duplicado eliminado (por x-data=ubicacionApp)\n";
        } else {
            echo "ℹ️  No se encontró bloque duplicado (puede que ya esté limpio)\n";
        }
    }
}

// Paso 3: Asegurar Alpine.js en el blade (si no está en layouts)
$layoutApp = __DIR__ . '/resources/views/layouts/app.blade.php';
$alpineEnLayout = file_exists($layoutApp) && strpos(file_get_contents($layoutApp), 'alpinejs') !== false;

if (!$alpineEnLayout && strpos($content, 'alpinejs') === false) {
    // Insertar antes del @push('scripts') o antes de @endsection
    $alpineTag = '<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>';
    if (strpos($content, "@push('scripts')") !== false) {
        $content = str_replace("@push('scripts')", $alpineTag . "\n@push('scripts')", $content);
    } else {
        $content = str_replace('@endsection', $alpineTag . "\n@endsection", $content);
    }
    echo "✅ Alpine.js CDN añadido al blade\n";
} else {
    echo "✅ Alpine.js ya está disponible\n";
}

// Paso 4: Guardar
file_put_contents($blade, $content);
echo "\n✅ setup.blade.php corregido\n";
echo "   Ejecuta: C:\\php\\php.exe artisan view:clear && C:\\php\\php.exe artisan serve\n";
