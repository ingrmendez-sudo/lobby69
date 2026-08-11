# LOBBY69 - Guia de Mantenimiento

Ultima actualizacion: 2026-08-11
Stack: Laravel 12 - PHP 8.2 - PostgreSQL (Supabase) - PDO EMULATE_PREPARES

---

## 1. Regla de Oro - PostgreSQL + EMULATE_PREPARES

### Por que existe este problema?
La conexion a Supabase usa el Transaction Pooler (puerto 6543) con
PDO::ATTR_EMULATE_PREPARES = true en config/database.php.
Esto hace que PHP convierta true a 1 y false a 0 antes de enviar
la query, lo que PostgreSQL rechaza porque sus columnas son tipo boolean,
no integer.

### NUNCA hacer esto
    ->where('active', true)
    ->where('is_profile_photo', false)
    ->update(['read' => true])
    ->update(['password_changed' => false])
    ->insert(['is_published' => true])

### SIEMPRE hacer esto
    ->whereRaw('active = true')
    ->whereRaw('is_profile_photo = false')
    ->update(['read' => DB::raw('true')])
    ->update(['password_changed' => DB::raw('false')])
    ->insert(['is_published' => DB::raw('true')])

### Controladores ya corregidos (Agosto 2026)
- DashboardController.php      : active, profile_completed, public
- ExploreController.php        : profile_completed, public, is_profile_photo
- MessagesController.php       : read
- ProfileController.php        : active, profile_completed
- FollowController.php         : is_profile_photo
- AdminStatsController.php     : active, verified_profile
- AdminUserController.php      : active, verified_profile
- ArticleController.php        : published
- AvailabilityController.php   : active
- EventController.php          : is_published
- MembershipController.php     : is_active
- SearchController.php         : profile_completed
- VideoGalleryController.php   : is_profile_photo, status, album_type
- PhotoController.php          : is_profile_photo
- PasswordChangeController.php : password_changed
- sidebar-left.blade.php       : is_profile_photo, status, album_type

---

## 2. Arquitectura de Conexion - Supabase

config/database.php - pgsql
  host: aws-1-us-west-2.pooler.supabase.com
  port: 6543  (Transaction Pooler - NO Session Pooler)
  options:
    PDO::ATTR_EMULATE_PREPARES => true   <- CRITICO, no eliminar

### Por que EMULATE_PREPARES = true?
El Transaction Pooler de Supabase no soporta prepared statements nativos de PDO.
Sin esta opcion aparece el error:
  prepared statement "pdo_stmt_00000001" does not exist

### Que pasa si se desactiva?
- Error: SQLSTATE[26000]: Invalid sql statement name
- Queries de diagnostico y tinker fallan
- El sitio deja de funcionar completamente

---

## 3. Sistema de Feed - DashboardController

### Logica de ranking
  score_final = RANDOM() * 10.0 + score_base * 0.3

Donde score_base considera:
  +2 misma ciudad que el usuario
  +3 si el usuario sigue al perfil
  +1 perfil verificado
  +2 foto reciente (menos de 7 dias)
  decay negativo por antiguedad

### Por que simplePaginate en lugar de paginate?
paginate() ejecuta un COUNT(*) adicional en cada request.
Con Supabase (~838ms/round-trip) esto anade ~800ms innecesarios.
simplePaginate() elimina ese COUNT y reduce la latencia total.

### Latencias medidas (conexion Supabase)
  Auth User::find(id)       : ~278ms
  City + Follows fusionado  : ~259ms
  Availability sidebar      : ~275ms
  Feed paginado             : ~476ms
  Total estimado            : ~1.3s

---

## 4. Sistema de Temas - Variables CSS

Todas las vistas deben usar variables --theme-* definidas en
public/css/00-vivid-nights.css.
NUNCA usar colores hardcodeados rgba(255,255,255,...).

### Variables disponibles
  --theme-bg          Fondo principal de pagina
  --theme-card        Fondo de tarjetas
  --theme-card-alt    Fondo alternativo / inputs
  --theme-text        Texto principal
  --theme-text-soft   Texto secundario
  --theme-muted       Texto tenue / metadatos
  --theme-border      Bordes de tarjetas
  --theme-shadow      Sombras
  --theme-hover       Fondo al hacer hover
  --theme-navbar      Fondo del navbar
  --theme-overlay     Overlay de modales
  --theme-input       Fondo de inputs

### Ejemplo correcto
  .mi-tarjeta {
      background : var(--theme-card);
      border     : 1px solid var(--theme-border);
      color      : var(--theme-text);
  }

### Ejemplo incorrecto - solo funciona en modo noche
  .mi-tarjeta {
      background : rgba(255,255,255,.04);
      border     : 1px solid rgba(255,255,255,.08);
      color      : rgba(226,217,243,.92);
  }

---

## 5. Restaurar Archivos desde Git

### Ver historial de un archivo especifico
  git log --oneline -- resources/views/availability/index.blade.php

### Ver contenido de un commit antes de restaurar
  git show <hash>:resources/views/availability/index.blade.php | head -30

### Restaurar a un commit especifico
  git checkout <hash> -- resources/views/availability/index.blade.php

### Commits de referencia importantes
  2008106  availability/index.blade.php completamente funcional
  4a23de7  DashboardController optimizado (simplePaginate + RANDOM)
  b654585  whereRaw en 6 controladores principales
  8c5de56  whereRaw en 8 controladores adicionales

---

## 6. Comandos de Mantenimiento Frecuentes

### Limpiar cache (ejecutar siempre despues de cambios)
  php artisan config:clear
  php artisan cache:clear
  php artisan view:clear
  php artisan route:clear

### Verificar booleanos problematicos en controladores
  Get-ChildItem -Path "app\Http\Controllers" -Filter "*.php" -Recurse |
      Select-String -Pattern "->where\(.+,\s*(true|false)\)" |
      Select-Object Path, LineNumber, Line

### Servidor con multiples workers (evitar timeouts)
  PHP_CLI_SERVER_WORKERS=4 php artisan serve

---

## 7. Estructura de Vistas - Convenciones

### Layouts disponibles
  layouts.app            Vistas autenticadas (navbar + sidebars)
  layouts.admin          Panel de administracion
  layouts.sidebar-left   Sidebar izquierdo con mini-perfil
  layouts.sidebar-right  Sidebar derecho (disponibilidad, sugerencias)
  components.navbar      Navbar principal
  components.footer      Footer
  components.legal-modals Modales de terminos y privacidad

### Partials que NO existen - no usar
  @include('layouts.head-assets')  -> No existe, genera InvalidArgumentException
  @include('layouts.navbar')       -> Usar components.navbar

---

## 8. Convencion de Commits

  feat:     nueva funcionalidad
  fix:      correccion de bug
  refactor: refactorizacion sin cambio de comportamiento
  perf:     mejora de rendimiento
  chore:    tareas de mantenimiento
  docs:     documentacion

---

## 9. Errores Frecuentes y Soluciones

### operator does not exist: boolean = integer
  Causa   : ->where('columna', true/false) con EMULATE_PREPARES activo
  Solucion: Reemplazar con ->whereRaw('columna = true')

### prepared statement pdo_stmt_00000001 does not exist
  Causa   : EMULATE_PREPARES desactivado con Transaction Pooler
  Solucion: Verificar PDO::ATTR_EMULATE_PREPARES => true en config/database.php

### Namespace declaration has to be the very first statement
  Causa   : BOM (Byte Order Mark) en el archivo PHP
  Solucion:
    bytes = [System.IO.File]::ReadAllBytes(path)
    if bytes[0]=0xEF y bytes[1]=0xBB y bytes[2]=0xBF:
      [System.IO.File]::WriteAllBytes(path, bytes[3..end])

### View [layouts.head-assets] not found
  Causa   : @include de partial inexistente
  Solucion: Usar @extends('layouts.app') o copiar head de layouts/app.blade.php

### Maximum execution time of 30 seconds exceeded
  Causa   : max_execution_time muy bajo o servidor single-thread
  Solucion:
    En php.ini -> max_execution_time = 120
    Servidor  -> PHP_CLI_SERVER_WORKERS=4 php artisan serve