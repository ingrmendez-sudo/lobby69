\# LOBBY69 — Contexto del Proyecto



\## Stack

\- Laravel 12, PHP 8.2, PostgreSQL (Supabase), Tailwind NO (CSS propio)

\- Ruta local: C:\\web\\lobby69-v3

\- PHP: C:\\php\\php.exe



\## Estado actual (Junio 2026)

\- Navbar: restaurado con dark mode toggle, búsqueda, dropdown usuario

\- CSS: sistema de temas en public/css/00-vivid-nights.css

&#x20; variables --bg-\*, --text-\*, --theme-\* para light/dark mode

\- Dashboard: 3 columnas (sidebar-left, feed central, sidebar-right)

\- Feed: fotos públicas aprobadas, likes y comments con cast ::text (UUID fix)

\- Modelos: Photo (HasUuids), ProfileView, PhotoLike, PhotoComment

\- UUID fix global: siempre usar DB::raw('campo::text') en joins PostgreSQL



\## Rutas importantes

\- dashboard: App\\Http\\Controllers\\DashboardController@index

\- profile.show: /perfil/{nickname}

\- profile.edit: /perfil/editar

\- photos.index: /mis-fotos

\- dashboard.feed.ajax: /dashboard/feed

\- dashboard.like: /dashboard/like/{photo}

\- dashboard.photo.modal: /dashboard/photo/{photo}



\## Pendiente

1\. Verificar feed de fotos visible en dashboard

2\. Dark mode toggle funcional

3\. Campos de perfil faltantes (orientación, nacionalidad, etc.)

4\. Sistema de visitas de perfil (profile\_views)

5\. Página /explorar con filtros

6\. Perfil de pareja con mismos campos

7\. Sistema de amistad/mensajes (Fase 7D)



\## Problemas resueltos

\- UUID vs bigint en PostgreSQL: usar ::text cast en ambos lados

\- @auth desbalanceado en navbar: cortar archivo en último @endauth

\- hasMorePages() en Collection: usar LengthAwarePaginator con try/catch

\- Navbar sin HTML: restaurar manualmente con notepad

\- dark mode flash: script anti-flash en <head> antes del render



\## Archivos clave modificados

\- resources/views/components/navbar.blade.php

\- resources/views/layouts/app.blade.php

\- resources/views/dashboard/index.blade.php

\- app/Http/Controllers/DashboardController.php

\- app/Models/Photo.php

\- public/css/00-vivid-nights.css



