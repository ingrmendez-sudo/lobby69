<?php
/**
 * fix_ubicacion.php
 * Corrige la sección de ubicación en profile/setup.blade.php
 * - Selector de país funcional
 * - Combo de alcaldías visible al elegir Ciudad de México
 */

$blade = __DIR__ . '/resources/views/profile/setup.blade.php';

if (!file_exists($blade)) {
    die("❌ No se encontró: $blade\n");
}

$content = file_get_contents($blade);

// ── 1. BLOQUE DE UBICACIÓN COMPLETO A REEMPLAZAR ──────────────────────────
// Buscamos desde el comentario/sección de ubicación hasta el cierre del fieldset o div
// Usamos un marcador claro para sustituir solo esa sección

$ubicacionNueva = <<<'BLADE'

{{-- ═══════════════════════════════════════════════════
     SECCIÓN: UBICACIÓN
     ═══════════════════════════════════════════════════ --}}
<div class="form-section" x-data="ubicacionApp()" x-init="init()">
    <h3 class="section-title">
        <span class="section-icon">📍</span> Ubicación
    </h3>

    <div class="form-row">
        {{-- PAÍS --}}
        <div class="form-group">
            <label for="country" class="form-label">País <span class="required">*</span></label>
            <select
                id="country"
                name="country"
                class="form-select"
                x-model="pais"
                @change="onPaisChange()"
                required>
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

        {{-- ESTADO / PROVINCIA --}}
        <div class="form-group" x-show="pais !== ''" x-cloak>
            <label for="state" class="form-label">Estado / Provincia</label>
            <select
                id="state"
                name="state"
                class="form-select"
                x-model="estado"
                @change="onEstadoChange()">
                <option value="">— Selecciona un estado —</option>
                <template x-for="s in estados" :key="s">
                    <option :value="s" x-text="s"></option>
                </template>
            </select>
        </div>
    </div>

    <div class="form-row">
        {{-- CIUDAD --}}
        <div class="form-group" x-show="pais !== ''" x-cloak>
            <label for="city" class="form-label">Ciudad</label>
            <template x-if="!esCDMX()">
                <input
                    type="text"
                    id="city"
                    name="city"
                    class="form-input"
                    x-model="ciudad"
                    placeholder="Ej. Guadalajara"
                    maxlength="100">
            </template>
            <template x-if="esCDMX()">
                <select
                    id="city"
                    name="city"
                    class="form-select"
                    x-model="ciudad">
                    <option value="">— Selecciona alcaldía —</option>
                    <template x-for="a in alcaldias" :key="a">
                        <option :value="a" x-text="a"></option>
                    </template>
                </select>
            </template>
        </div>

        {{-- CAMPO OCULTO para garantizar envío del valor ciudad cuando es CDMX --}}
    </div>
</div>

<script>
function ubicacionApp() {
    return {
        pais: '{{ old("country", $profile->country ?? "") }}',
        estado: '{{ old("state", $profile->state ?? "") }}',
        ciudad: '{{ old("city", $profile->city ?? "") }}',

        estados: [],
        alcaldias: [
            'Álvaro Obregón','Azcapotzalco','Benito Juárez','Coyoacán',
            'Cuajimalpa de Morelos','Cuauhtémoc','Gustavo A. Madero',
            'Iztacalco','Iztapalapa','La Magdalena Contreras',
            'Miguel Hidalgo','Milpa Alta','Tláhuac','Tlalpan',
            'Venustiano Carranza','Xochimilco'
        ],

        estadosPorPais: {
            'Mexico': [
                'Aguascalientes','Baja California','Baja California Sur','Campeche',
                'Chiapas','Chihuahua','Ciudad de México','Coahuila','Colima',
                'Durango','Guanajuato','Guerrero','Hidalgo','Jalisco',
                'Estado de México','Michoacán','Morelos','Nayarit','Nuevo León',
                'Oaxaca','Puebla','Querétaro','Quintana Roo','San Luis Potosí',
                'Sinaloa','Sonora','Tabasco','Tamaulipas','Tlaxcala',
                'Veracruz','Yucatán','Zacatecas'
            ],
            'Argentina': ['Buenos Aires','Córdoba','Rosario','Mendoza','Tucumán','La Plata'],
            'Colombia':  ['Bogotá','Medellín','Cali','Barranquilla','Cartagena'],
            'Chile':     ['Santiago','Valparaíso','Concepción','La Serena','Antofagasta'],
            'España':    ['Madrid','Barcelona','Valencia','Sevilla','Bilbao','Málaga'],
            'Estados Unidos': ['New York','Los Angeles','Chicago','Houston','Miami','Dallas'],
            'Otro':      []
        },

        init() {
            // Cargar estados según el país ya guardado
            this.estados = this.estadosPorPais[this.pais] || [];
        },

        onPaisChange() {
            this.estados = this.estadosPorPais[this.pais] || [];
            this.estado  = '';
            this.ciudad  = '';
        },

        onEstadoChange() {
            // Si cambia el estado, resetear ciudad
            if (!this.esCDMX()) {
                this.ciudad = '';
            }
        },

        esCDMX() {
            return this.estado === 'Ciudad de México';
        }
    };
}
</script>
BLADE;

// ── 2. DETECTAR y REEMPLAZAR la sección de ubicación existente ─────────────
// Buscamos desde "SECCIÓN: UBICACIÓN" o el bloque del select country hasta el cierre

// Patrón amplio para capturar el bloque de ubicación actual (sea como sea)
$patron = '/(<!--.*?UBICACI[ÓO]N.*?-->|{{--.*?UBICACI[ÓO]N.*?--}}|<div[^>]*ubicacion[^>]*>).*?(<\/script>\s*(?=\s*{{--|<div|<section|@section|@endsection))/si';

// Si no encuentra por marcador, buscamos por el select name="country"
if (!preg_match($patron, $content)) {
    // Buscar bloque que contiene el select country
    $patron2 = '/(<div[^>]*class="form-section"[^>]*>(?:(?!<div[^>]*class="form-section").)*?name="country".*?<\/div>\s*<\/div>)/si';
    if (preg_match($patron2, $content, $m)) {
        $content = str_replace($m[0], trim($ubicacionNueva), $content);
        echo "✅ Bloque de ubicación reemplazado (patrón 2 - por select country)\n";
    } else {
        // Reemplazar entre marcadores BEGIN/END si existen
        $patBegin = '/\{\{--\s*BEGIN[:\s]UBICACION\s*--\}\}/i';
        $patEnd   = '/\{\{--\s*END[:\s]UBICACION\s*--\}\}/i';
        if (preg_match($patBegin, $content) && preg_match($patEnd, $content)) {
            $content = preg_replace(
                '/\{\{--\s*BEGIN[:\s]UBICACION\s*--\}\}.*?\{\{--\s*END[:\s]UBICACION\s*--\}\}/si',
                trim($ubicacionNueva),
                $content
            );
            echo "✅ Bloque de ubicación reemplazado (marcadores BEGIN/END)\n";
        } else {
            // Fallback: insertar antes de </form>
            if (strpos($content, 'name="country"') !== false) {
                echo "⚠️  No se pudo localizar el bloque exacto. Se intentará reemplazo por 'name=\"country\"' context.\n";
                // Extraer desde <div que contiene el primer select[name=country]
                $pos = strpos($content, 'name="country"');
                // Retroceder hasta el <div class="form-section"
                $start = strrpos(substr($content, 0, $pos), '<div');
                // Avanzar hasta cerrar el script o el siguiente form-section
                $end = strpos($content, '</script>', $pos);
                if ($end !== false) {
                    $end += strlen('</script>');
                    $content = substr($content, 0, $start) . trim($ubicacionNueva) . substr($content, $end);
                    echo "✅ Bloque reemplazado por contexto\n";
                } else {
                    echo "❌ No se encontró el cierre del bloque. Revisa manualmente.\n";
                }
            } else {
                echo "⚠️  No se encontró 'name=\"country\"' en la vista. Insertando antes de </form>\n";
                $content = str_replace('</form>', trim($ubicacionNueva) . "\n</form>", $content);
            }
        }
    }
} else {
    $content = preg_replace($patron, trim($ubicacionNueva), $content, 1);
    echo "✅ Bloque de ubicación reemplazado (patrón 1)\n";
}

// ── 3. ASEGURARSE de que x-cloak esté en el CSS del layout ────────────────
$layoutApp = __DIR__ . '/resources/views/layouts/app.blade.php';
$layoutGuest = __DIR__ . '/resources/views/layouts/guest.blade.php';

foreach ([$layoutApp, $layoutGuest] as $layout) {
    if (file_exists($layout)) {
        $lContent = file_get_contents($layout);
        if (strpos($lContent, '[x-cloak]') === false) {
            $lContent = str_replace('</head>', "<style>[x-cloak] { display: none !important; }</style>\n</head>", $lContent);
            file_put_contents($layout, $lContent);
            echo "✅ [x-cloak] CSS añadido en: " . basename($layout) . "\n";
        } else {
            echo "ℹ️  [x-cloak] ya existe en: " . basename($layout) . "\n";
        }
    }
}

// ── 4. VERIFICAR que Alpine.js esté cargado ───────────────────────────────
$setupBlade = file_get_contents($blade);
$hasAlpine = strpos($setupBlade, 'alpinejs') !== false || strpos($setupBlade, 'alpine.js') !== false;

// Buscar en layouts
$alpineEnLayout = false;
foreach ([$layoutApp, $layoutGuest] as $layout) {
    if (file_exists($layout) && strpos(file_get_contents($layout), 'alpine') !== false) {
        $alpineEnLayout = true;
        break;
    }
}

if (!$hasAlpine && !$alpineEnLayout) {
    echo "⚠️  Alpine.js NO detectado en layouts. Añadiendo CDN al blade...\n";
    $content = str_replace(
        '</body>',
        '<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>' . "\n</body>",
        $content
    );
    echo "✅ Alpine.js CDN añadido al final del blade\n";
} else {
    echo "✅ Alpine.js detectado\n";
}

// ── 5. GUARDAR ─────────────────────────────────────────────────────────────
file_put_contents($blade, $content);
echo "\n✅ setup.blade.php actualizado correctamente\n";
echo "📋 Próximos pasos:\n";
echo "   php artisan view:clear\n";
echo "   php artisan route:clear\n";
echo "   php artisan serve\n";
echo "   Visitar: http://localhost:8000/perfil/configurar\n";
