# LOBBY69 — Documento Maestro de Arquitectura y Plan de Proyecto

> **Tipo de trabajo**: Proyecto heredado en evolución — NO desarrollo desde cero  
> **Base**: Social Django Template v1.0 (StackBros / ThemeForest)  
> **Stack confirmado**: Python 3.12 · Django 5.0 · Django Allauth 65.3 · Bootstrap 5 · SQLite → PostgreSQL · Gulp

---

## ENTREGABLE 1 — MATRIZ DE REUTILIZACIÓN

> Análisis realizado sobre el código fuente real del proyecto en `/home/user/lobby69/`

---

### 1.1 INVENTARIO COMPLETO DEL TEMPLATE

**Archivos Python existentes** (36 archivos)
```
config/
  settings/base.py        ← Django 5.0, allauth, crispy, whitenoise
  settings/local.py       ← DEBUG=True, SQLite, debug_toolbar
  settings/production.py  ← HTTPS, PostgreSQL ready
  urls.py                 ← Routing principal
social/
  users/
    models.py             ← AbstractUser con campo `name`
    views.py              ← Detail / Update / Redirect views
    forms.py              ← UserSignupForm, UserAdminCreationForm
    adapters.py           ← AccountAdapter con is_open_for_signup
    admin.py              ← User admin
    urls.py               ← /users/<username>/ routing
  pages/
    views.py              ← root_page_view + dynamic_pages_view
    urls.py               ← '' + '<str:template_name>/'
    models.py             ← vacío (listo para extender)
```

**Templates HTML existentes** (60 archivos)
```
base.html                 ← Layout raíz con dark mode, Bootstrap 5
profile-layout.html       ← Layout de 2 columnas para perfil
partials/
  navbar.html             ← Navbar responsive con dropdown, notif, avatar
  sidenav.html            ← Sidebar offcanvas con stats y nav links
  head-css.html           ← Dark mode script
pages/
  index.html              ← Feed principal (stories, posts, cards)
  landing.html            ← Landing pública
  sign-in-advance.html    ← Login con SVG hero, form card
  sign-up-advance.html    ← Registro con validación de contraseña
  forgot-password.html    ← Recuperación de contraseña
  my-profile.html         ← Perfil con cover image, avatar xxl
  my-profile-about.html   ← Pestaña Acerca de
  my-profile-connections.html ← Pestaña Conexiones
  my-profile-media.html   ← Galería de fotos/álbumes
  my-profile-videos.html  ← Galería de videos
  my-profile-events.html  ← Eventos del usuario
  my-profile-activity.html ← Actividad
  settings.html           ← Panel de configuración con tabs
  messaging.html          ← Chat completo con lista + ventana
  messaging-os.html       ← Versión simplificada del chat
  notifications.html      ← Centro de notificaciones
  events.html             ← Listado de eventos con filtros
  events-2.html           ← Vista alternativa de eventos
  event-details.html      ← Detalle de evento
  event-details-2.html    ← Detalle alternativo de evento
  groups.html             ← Listado de grupos con filtros
  group-details.html      ← Detalle de grupo
  blog.html               ← Listado de posts
  blog-details.html       ← Detalle de post
  post-details.html       ← Detalle de publicación social
  post-videos.html        ← Videos publicados
  post-video-details.html ← Detalle de video
  video-details.html      ← Reproductor de video
  albums.html             ← Álbumes de fotos
  celebration.html        ← Celebraciones
  privacy-and-terms.html  ← Términos de privacidad
  help.html               ← Centro de ayuda
  help-details.html       ← Detalle de ayuda
  error-404.html          ← Página 404
  offline.html            ← Página offline
  create-page.html        ← Crear página
allauth/
  elements/*.html         ← Componentes UI de allauth
  layouts/entrance.html   ← Layout para login/registro
```

**Assets estáticos** (vendors disponibles)
```
static/
  css/style.css           ← CSS compilado del tema (378KB)
  css/vendor.min.css      ← CSS de vendors compilado (252KB)
  css/lobby69.css         ← Override LOBBY69 (creado en fase anterior)
  scss/
    _variables.scss        ← 743 líneas: paleta, tipografía, componentes
    _dark-mode.scss        ← 307 líneas: dark mode completo
    _user-variables.scss   ← Punto de extensión para override
    components/*.scss      ← Avatars, buttons, mockups, tiny-slider
    custom/*.scss          ← Navbar, nav, buttons personalizados
  vendor/
    bootstrap (5.x)
    bootstrap-icons
    @fortawesome
    choices.js
    dropzone
    flatpickr
    glightbox
    overlayscrollbars
    plyr
    tiny-slider (stories)
    zuck.js (stories)
    password-strength-meter
```

---

### 1.2 TABLA DE REUTILIZACIÓN POR MÓDULO

```
╔══════════════════════════════════╦═══════════╦═══════════════════════════════════════════════════════╗
║ COMPONENTE / MÓDULO              ║ ACCIÓN    ║ DETALLE                                               ║
╠══════════════════════════════════╬═══════════╬═══════════════════════════════════════════════════════╣
║ BASE & LAYOUT                    ║           ║                                                       ║
║  base.html                       ║ CONSERVAR ║ Layout raíz intacto + inyectar lobby69.css            ║
║  profile-layout.html             ║ CONSERVAR ║ Layout de 2 cols reutilizable para perfiles           ║
║  partials/navbar.html            ║ MODIFICAR ║ Ya adaptado. Conectar con auth real Django            ║
║  partials/sidenav.html           ║ MODIFICAR ║ Ya adaptado. Conectar con user.profile real           ║
║  partials/head-css.html          ║ CONSERVAR ║ Dark mode script funciona bien                        ║
╠══════════════════════════════════╬═══════════╬═══════════════════════════════════════════════════════╣
║ AUTENTICACIÓN & CUENTAS          ║           ║                                                       ║
║  users/models.py (AbstractUser)  ║ EXTENDER  ║ Agregar campos: birth_date, age_verified, invite_code ║
║  users/adapters.py               ║ MODIFICAR ║ Bloquear signup sin invitación válida                 ║
║  users/forms.py (UserSignupForm) ║ EXTENDER  ║ Agregar: +18 checkbox, invite_code, consentimiento    ║
║  sign-in-advance.html            ║ CONSERVAR ║ Layout 100% reutilizable. Solo conectar allauth URLs  ║
║  sign-up-advance.html            ║ CONSERVAR ║ Layout 100% reutilizable. Solo conectar allauth URLs  ║
║  forgot-password-advance.html    ║ CONSERVAR ║ Layout idéntico. Solo conectar allauth URLs           ║
║  allauth/layouts/entrance.html   ║ MODIFICAR ║ Adaptar al branding LOBBY69 dorado/oscuro             ║
║  allauth/elements/*.html         ║ MODIFICAR ║ Aplicar clases Bootstrap del tema                     ║
╠══════════════════════════════════╬═══════════╬═══════════════════════════════════════════════════════╣
║ PERFIL DE USUARIO                ║           ║                                                       ║
║  my-profile.html                 ║ MODIFICAR ║ Conectar con Profile real. Tabs: Feed/Sobre/Fotos/... ║
║  my-profile-about.html           ║ MODIFICAR ║ Campos de perfil LOBBY69: orientación, tipo pareja    ║
║  my-profile-connections.html     ║ MODIFICAR ║ Conectar con sistema Follow/Connect real              ║
║  my-profile-media.html           ║ MODIFICAR ║ Conectar con Media model + subida real                ║
║  my-profile-videos.html          ║ MODIFICAR ║ Conectar con Video model                             ║
║  my-profile-events.html          ║ CONSERVAR ║ Conectar con eventos LOBBY69                          ║
║  profile-layout.html             ║ CONSERVAR ║ Reutilizable exactamente igual                        ║
║  settings.html (tabs)            ║ MODIFICAR ║ Conectar tabs con forms reales. Agregar tab Membresía ║
╠══════════════════════════════════╬═══════════╬═══════════════════════════════════════════════════════╣
║ FEED SOCIAL                      ║           ║                                                       ║
║  index.html (feed)               ║ MODIFICAR ║ Stories → Perfiles activos. Posts → Posts reales      ║
║  post-details.html               ║ MODIFICAR ║ Conectar con Post model. Likes/Comentarios reales     ║
║  index-post.html                 ║ CONSERVAR ║ Vista alternativa de feed con posts                   ║
║  index-video.html                ║ CONSERVAR ║ Vista alternativa de feed con videos                  ║
╠══════════════════════════════════╬═══════════╬═══════════════════════════════════════════════════════╣
║ GRUPOS → SALAS DE JUEGO/MEET     ║           ║                                                       ║
║  groups.html                     ║ MODIFICAR ║ Grupos → Salas / Encuentros. Filtros reales           ║
║  group-details.html              ║ MODIFICAR ║ Detalle de Sala/Encuentro con RSVP real               ║
╠══════════════════════════════════╬═══════════╬═══════════════════════════════════════════════════════╣
║ EVENTOS                          ║           ║                                                       ║
║  events.html                     ║ MODIFICAR ║ Conectar con Event model. Filtro por ciudad/estado    ║
║  events-2.html                   ║ CONSERVAR ║ Vista alternativa de eventos                          ║
║  event-details.html              ║ MODIFICAR ║ Detalle de evento con RSVP, aforo, membresía          ║
║  event-details-2.html            ║ CONSERVAR ║ Vista alternativa de evento                           ║
╠══════════════════════════════════╬═══════════╬═══════════════════════════════════════════════════════╣
║ MENSAJERÍA                       ║           ║                                                       ║
║  messaging.html                  ║ MODIFICAR ║ UI 100% reutilizable. Conectar con Message model      ║
║  messaging-os.html               ║ CONSERVAR ║ Vista simplificada, útil para mobile                  ║
╠══════════════════════════════════╬═══════════╬═══════════════════════════════════════════════════════╣
║ MEDIA (FOTOS / VIDEOS)           ║           ║                                                       ║
║  albums.html                     ║ MODIFICAR ║ Álbumes reales con visibilidad pública/privada/VIP    ║
║  post-videos.html                ║ MODIFICAR ║ Conectar con Video model + moderación                 ║
║  video-details.html              ║ MODIFICAR ║ Reproductor Plyr.js ya integrado. Conectar modelo     ║
║  my-profile-media.html           ║ MODIFICAR ║ Subida real con Dropzone.js (ya integrado en vendors) ║
╠══════════════════════════════════╬═══════════╬═══════════════════════════════════════════════════════╣
║ PÁGINAS INFORMATIVAS             ║           ║                                                       ║
║  landing.html                    ║ MODIFICAR ║ Ya adaptada con textos LOBBY69. Completar contenido   ║
║  privacy-and-terms.html          ║ MODIFICAR ║ Reemplazar con T&C reales de LOBBY69                  ║
║  help.html / help-details.html   ║ CONSERVAR ║ Reutilizar estructura. Cambiar preguntas              ║
║  error-404.html                  ║ CONSERVAR ║ Aplicar branding LOBBY69                              ║
╠══════════════════════════════════╬═══════════╬═══════════════════════════════════════════════════════╣
║ DESDE CERO (no existe en template)║          ║                                                       ║
║  Invitaciones                    ║ NUEVO     ║ Modelo Invitation + flujo request/approve             ║
║  Perfiles de pareja              ║ NUEVO     ║ CoupleProfile + link entre dos usuarios               ║
║  Verificación +18                ║ NUEVO     ║ AgeVerification model + flujo admin                   ║
║  Membresías                      ║ NUEVO     ║ Membership + MembershipPlan models                    ║
║  Pagos                           ║ NUEVO     ║ Payment + PaymentGateway (Stripe/MercadoPago)         ║
║  Geolocalización                 ║ NUEVO     ║ UserLocation + búsqueda por estado/ciudad             ║
║  Moderación de contenido         ║ NUEVO     ║ ContentReport + ModerationQueue                       ║
║  Panel de administración LOBBY69 ║ NUEVO     ║ Admin views específicas del negocio                   ║
║  Relatos (contenido adulto)      ║ NUEVO     ║ Story/Tale model + visibilidad por membresía          ║
║  Plan para Hoy                   ║ NUEVO     ║ PlanForToday model + feed especial                    ║
╚══════════════════════════════════╩═══════════╩═══════════════════════════════════════════════════════╝
```

**Resumen de acciones:**
- ✅ **CONSERVAR sin tocar**: 18 archivos (base layout, vendors, partials head, páginas de error)
- 🔧 **MODIFICAR / CONECTAR**: 22 archivos (templates ya existentes que necesitan datos reales)
- 🆕 **NUEVO desde cero**: 10 módulos de lógica de negocio (sin equivalente en el template)

---

## ENTREGABLE 2 — ARQUITECTURA DEL PROYECTO

### 2.1 ESTRUCTURA DE DIRECTORIOS PROPUESTA

```
lobby69/                              ← Raíz del proyecto (existente)
├── config/
│   ├── settings/
│   │   ├── base.py                   ← MODIFICAR: agregar apps nuevas, PostgreSQL config
│   │   ├── local.py                  ← MODIFICAR: variables locales
│   │   └── production.py            ← MODIFICAR: storage, email, Stripe keys
│   └── urls.py                      ← MODIFICAR: incluir URLs de todos los módulos
│
├── social/                          ← Paquete principal existente
│   ├── users/                       ← EXTENDER (existente)
│   │   ├── models.py                ← Agregar campos age_verified, invite_code, etc.
│   │   ├── forms.py                 ← Agregar UserSignupForm con +18 y consentimiento
│   │   ├── adapters.py              ← Modificar para validar invitación en signup
│   │   └── ...                     ← (resto intacto)
│   │
│   ├── pages/                       ← MANTENER (existente, conectar vistas reales)
│   │   ├── views.py                 ← Evolucionar de dynamic_pages a vistas con contexto
│   │   └── ...
│   │
│   ├── profiles/                    ← NUEVO — Gestión de perfiles individuales y parejas
│   │   ├── models.py
│   │   ├── views.py
│   │   ├── forms.py
│   │   ├── urls.py
│   │   └── managers.py
│   │
│   ├── invitations/                 ← NUEVO — Sistema de invitaciones
│   │   ├── models.py
│   │   ├── views.py
│   │   ├── forms.py
│   │   └── urls.py
│   │
│   ├── social_feed/                 ← NUEVO — Posts, likes, comentarios, relatos
│   │   ├── models.py
│   │   ├── views.py
│   │   ├── forms.py
│   │   └── urls.py
│   │
│   ├── media_content/               ← NUEVO — Fotos y videos con moderación
│   │   ├── models.py
│   │   ├── views.py
│   │   ├── storage.py
│   │   └── urls.py
│   │
│   ├── messaging/                   ← NUEVO — Mensajería privada
│   │   ├── models.py
│   │   ├── views.py
│   │   ├── consumers.py             ← WebSocket (Django Channels, fase 2)
│   │   └── urls.py
│   │
│   ├── events/                      ← NUEVO — Eventos y encuentros
│   │   ├── models.py
│   │   ├── views.py
│   │   ├── forms.py
│   │   └── urls.py
│   │
│   ├── geo/                         ← NUEVO — Geolocalización y búsqueda
│   │   ├── models.py
│   │   ├── views.py
│   │   └── utils.py
│   │
│   ├── memberships/                 ← NUEVO — Planes y suscripciones
│   │   ├── models.py
│   │   ├── views.py
│   │   └── urls.py
│   │
│   ├── payments/                    ← NUEVO — Pagos y validación
│   │   ├── models.py
│   │   ├── views.py
│   │   ├── gateways.py
│   │   └── urls.py
│   │
│   ├── verification/                ← NUEVO — Verificación de edad e identidad
│   │   ├── models.py
│   │   ├── views.py
│   │   └── urls.py
│   │
│   ├── moderation/                  ← NUEVO — Moderación de contenido
│   │   ├── models.py
│   │   ├── views.py
│   │   └── admin.py
│   │
│   ├── admin_panel/                 ← NUEVO — Panel admin personalizado LOBBY69
│   │   ├── views.py
│   │   ├── urls.py
│   │   └── mixins.py
│   │
│   ├── static/                      ← CONSERVAR + AGREGAR
│   │   ├── css/
│   │   │   ├── style.css            ← CONSERVAR (no modificar)
│   │   │   ├── vendor.min.css       ← CONSERVAR (no modificar)
│   │   │   └── lobby69.css          ← Override de branding (existente, expandir)
│   │   └── scss/
│   │       ├── _variables.scss      ← CONSERVAR (no modificar)
│   │       ├── _user-variables.scss ← PUNTO DE ENTRADA para ajustes de marca
│   │       └── _user.scss           ← Estilos custom adicionales
│   │
│   └── templates/                   ← CONSERVAR ESTRUCTURA + agregar subcarpetas
│       ├── base.html                ← CONSERVAR + inyección lobby69.css ya hecha
│       ├── profile-layout.html      ← CONSERVAR intacto
│       ├── partials/                ← MODIFICADOS (navbar/sidenav ya adaptados)
│       ├── pages/                   ← CONSERVAR + conectar con datos reales
│       ├── profiles/                ← NUEVO — templates de perfil extendidos
│       ├── invitations/             ← NUEVO
│       ├── social_feed/             ← NUEVO
│       ├── messaging/               ← NUEVO (conectar messaging.html)
│       ├── events/                  ← NUEVO (conectar events.html)
│       ├── memberships/             ← NUEVO
│       ├── payments/                ← NUEVO
│       ├── verification/            ← NUEVO
│       └── admin_panel/             ← NUEVO
```

---

### 2.2 MODELOS DE DATOS CLAVE

#### `social/users/models.py` — EXTENDER (no reemplazar)
```python
class User(AbstractUser):
    # EXISTENTE
    name = CharField(max_length=255, blank=True)
    
    # AGREGAR
    birth_date = DateField(null=True, blank=True)
    age_verified = BooleanField(default=False)
    age_verified_at = DateTimeField(null=True, blank=True)
    invited_by = ForeignKey('invitations.Invitation', null=True, on_delete=SET_NULL)
    has_accepted_terms = BooleanField(default=False)
    terms_accepted_at = DateTimeField(null=True, blank=True)
    is_active_member = BooleanField(default=False)   # tiene membresía activa
    membership_tier = CharField(choices=TIER_CHOICES, default='free')
```

#### `social/profiles/models.py` — NUEVO
```python
class Profile(models.Model):
    """Perfil extendido de usuario individual"""
    user = OneToOneField(User, on_delete=CASCADE, related_name='profile')
    nick = CharField(max_length=60, unique=True)
    bio = TextField(blank=True, max_length=500)
    avatar = ImageField(upload_to='avatars/', blank=True)
    cover = ImageField(upload_to='covers/', blank=True)
    
    # Datos de ubicación
    country = CharField(max_length=60, default='México')
    state = CharField(max_length=60, blank=True)       # Estado/Provincia
    city = CharField(max_length=60, blank=True)
    latitude = DecimalField(null=True, blank=True, ...)
    longitude = DecimalField(null=True, blank=True, ...)
    
    # Datos de perfil swinger
    PROFILE_TYPE_CHOICES = [
        ('individual_m', 'Hombre solo'),
        ('individual_f', 'Mujer sola'),
        ('couple_mf', 'Pareja H+M'),
        ('couple_ff', 'Pareja M+M'),
        ('couple_mm', 'Pareja H+H'),
        ('group', 'Grupo'),
    ]
    profile_type = CharField(max_length=20, choices=PROFILE_TYPE_CHOICES)
    orientation = CharField(max_length=30, blank=True)
    
    # Privacidad
    VISIBILITY = [('public','Público'), ('members','Solo Miembros'), ('vip','Solo VIP')]
    visibility = CharField(max_length=10, choices=VISIBILITY, default='members')
    show_location = BooleanField(default=True)
    blur_photos = BooleanField(default=False)  # fotos difuminadas hasta conectar
    
    # Estado actual
    is_verified = BooleanField(default=False)
    is_online = BooleanField(default=False)
    last_seen = DateTimeField(null=True, blank=True)
    
    # Stats
    connections_count = IntegerField(default=0)
    posts_count = IntegerField(default=0)
    views_count = IntegerField(default=0)

class CoupleProfile(models.Model):
    """Perfil de pareja — vincula dos usuarios"""
    profile_a = OneToOneField(Profile, on_delete=CASCADE, related_name='couple_as_a')
    profile_b = OneToOneField(Profile, on_delete=CASCADE, related_name='couple_as_b')
    couple_nick = CharField(max_length=80, unique=True)
    bio = TextField(blank=True)
    avatar = ImageField(upload_to='couple_avatars/', blank=True)
    created_at = DateTimeField(auto_now_add=True)
    is_active = BooleanField(default=True)
```

#### `social/invitations/models.py` — NUEVO
```python
class Invitation(models.Model):
    STATUS = [('pending','Pendiente'), ('accepted','Aceptada'), ('expired','Expirada')]
    code = CharField(max_length=32, unique=True)
    created_by = ForeignKey(User, on_delete=CASCADE, related_name='invitations_sent')
    used_by = OneToOneField(User, null=True, on_delete=SET_NULL, related_name='invitation_used')
    email = EmailField(blank=True)
    status = CharField(max_length=10, choices=STATUS, default='pending')
    expires_at = DateTimeField()
    created_at = DateTimeField(auto_now_add=True)
    used_at = DateTimeField(null=True, blank=True)
    max_uses = IntegerField(default=1)
    uses_count = IntegerField(default=0)
    notes = TextField(blank=True)  # Notas del admin
```

#### `social/social_feed/models.py` — NUEVO
```python
class Post(models.Model):
    POST_TYPES = [
        ('text', 'Texto'),
        ('photo', 'Foto'),
        ('video', 'Video'),
        ('story', 'Relato'),        # contenido narrativo adulto
        ('plan', 'Plan para Hoy'),  # busco/propongo encuentro
        ('event', 'Evento'),
    ]
    VISIBILITY = [('public','Público'),('members','Solo Miembros'),('vip','Solo VIP'),('connections','Conexiones')]
    
    author = ForeignKey(Profile, on_delete=CASCADE, related_name='posts')
    post_type = CharField(max_length=10, choices=POST_TYPES, default='text')
    content = TextField(blank=True)
    visibility = CharField(max_length=12, choices=VISIBILITY, default='members')
    
    is_approved = BooleanField(default=False)  # pasa por moderación
    is_flagged = BooleanField(default=False)
    created_at = DateTimeField(auto_now_add=True)
    updated_at = DateTimeField(auto_now=True)
    
    likes_count = IntegerField(default=0)
    comments_count = IntegerField(default=0)

class Comment(models.Model):
    post = ForeignKey(Post, on_delete=CASCADE, related_name='comments')
    author = ForeignKey(Profile, on_delete=CASCADE)
    content = TextField(max_length=500)
    created_at = DateTimeField(auto_now_add=True)
    is_approved = BooleanField(default=True)

class Like(models.Model):
    post = ForeignKey(Post, on_delete=CASCADE, related_name='likes')
    profile = ForeignKey(Profile, on_delete=CASCADE)
    created_at = DateTimeField(auto_now_add=True)
    class Meta:
        unique_together = ['post', 'profile']
```

#### `social/memberships/models.py` — NUEVO
```python
class MembershipPlan(models.Model):
    name = CharField(max_length=50)           # "Gratis", "Básico", "VIP", "Premium"
    slug = SlugField(unique=True)
    price_mxn = DecimalField(max_digits=8, decimal_places=2)
    billing_period = CharField(choices=[('monthly','Mensual'),('annual','Anual')], ...)
    
    # Permisos del plan
    can_send_messages = BooleanField(default=False)
    can_view_photos = BooleanField(default=False)
    can_view_videos = BooleanField(default=False)
    can_post_media = BooleanField(default=False)
    can_view_relatos = BooleanField(default=False)
    can_create_events = BooleanField(default=False)
    can_view_location = BooleanField(default=False)
    max_photos_per_month = IntegerField(default=0)
    max_messages_per_day = IntegerField(default=0)

class Membership(models.Model):
    user = ForeignKey(User, on_delete=CASCADE, related_name='memberships')
    plan = ForeignKey(MembershipPlan, on_delete=PROTECT)
    status = CharField(choices=[('active','Activa'),('expired','Expirada'),('cancelled','Cancelada')], ...)
    started_at = DateTimeField()
    expires_at = DateTimeField()
    payment = ForeignKey('payments.Payment', null=True, on_delete=SET_NULL)
    auto_renew = BooleanField(default=False)
```

#### `social/messaging/models.py` — NUEVO
```python
class Conversation(models.Model):
    participants = ManyToManyField(Profile, related_name='conversations')
    created_at = DateTimeField(auto_now_add=True)
    last_message_at = DateTimeField(null=True)
    is_archived = BooleanField(default=False)

class Message(models.Model):
    conversation = ForeignKey(Conversation, on_delete=CASCADE, related_name='messages')
    sender = ForeignKey(Profile, on_delete=CASCADE)
    content = TextField()
    content_type = CharField(choices=[('text','Texto'),('image','Imagen'),('audio','Audio')], ...)
    media_file = FileField(upload_to='messages/', blank=True)
    is_read = BooleanField(default=False)
    read_at = DateTimeField(null=True, blank=True)
    created_at = DateTimeField(auto_now_add=True)
    is_deleted_by_sender = BooleanField(default=False)
    is_deleted_by_recipient = BooleanField(default=False)
```

#### `social/events/models.py` — NUEVO
```python
class Event(models.Model):
    EVENT_TYPES = [
        ('meet', 'Encuentro privado'),
        ('party', 'Fiesta/Swingers party'),
        ('online', 'Evento online'),
        ('social', 'Social (sin contenido adulto)'),
    ]
    organizer = ForeignKey(Profile, on_delete=CASCADE, related_name='organized_events')
    title = CharField(max_length=200)
    description = TextField()
    event_type = CharField(max_length=10, choices=EVENT_TYPES)
    
    # Lugar
    venue_name = CharField(max_length=200, blank=True)
    address = CharField(max_length=300, blank=True)
    city = CharField(max_length=60)
    state = CharField(max_length=60)
    is_location_private = BooleanField(default=True)  # dirección exacta solo a confirmados
    
    # Fechas
    start_date = DateTimeField()
    end_date = DateTimeField()
    
    # Cupo
    max_attendees = IntegerField(null=True, blank=True)
    attendees_count = IntegerField(default=0)
    requires_membership = BooleanField(default=True)
    min_membership_tier = CharField(max_length=20, default='basic')
    
    # Privacidad
    VISIBILITY = [('public','Público'),('members','Solo Miembros'),('vip','Solo VIP'),('invite','Solo Invitados')]
    visibility = CharField(max_length=10, choices=VISIBILITY, default='members')
    
    cover_image = ImageField(upload_to='events/', blank=True)
    is_approved = BooleanField(default=False)
    created_at = DateTimeField(auto_now_add=True)

class EventAttendee(models.Model):
    event = ForeignKey(Event, on_delete=CASCADE, related_name='attendees')
    profile = ForeignKey(Profile, on_delete=CASCADE)
    STATUS = [('going','Asistiré'),('maybe','Quizás'),('not_going','No asistiré')]
    status = CharField(max_length=10, choices=STATUS)
    confirmed_at = DateTimeField(null=True, blank=True)
    location_revealed_at = DateTimeField(null=True, blank=True)
```

---

### 2.3 FLUJO DE DATOS CRÍTICOS

```
FLUJO DE REGISTRO CON INVITACIÓN
─────────────────────────────────
Usuario → landing.html → formulario solicitud de invitación
  ↓
Admin aprueba → genera Invitation(code=uuid, expires_at=+7días)
  ↓
Email con link: /registro/?code=XXXX
  ↓
sign-up-advance.html (ya existe) + campos:
  - código de invitación (se valida vs Invitation model)
  - fecha de nacimiento (cálculo de +18 en el servidor)
  - checkbox consentimiento explícito (required)
  ↓
AccountAdapter.is_open_for_signup() → verifica Invitation válida
  ↓
Usuario creado → Profile.create() auto-signal
  ↓
Redirigir a /verificar-edad/ → subir documento opcional
  ↓
Feed principal (index.html)

FLUJO DE MEMBRESÍA
──────────────────
Usuario (free) intenta ver foto privada
  ↓
@requires_membership decorator → redirect a /membresias/
  ↓
memberships/plans.html (usa layout de landing.html)
  ↓
Selecciona plan → POST /pagos/checkout/
  ↓
Redirect a Stripe/MercadoPago
  ↓
Webhook confirm → Membership.activate() → User.is_active_member=True
  ↓
Redirect a contenido original
```

---

### 2.4 SISTEMA DE PERMISOS Y VISIBILIDAD

```
NIVELES DE ACCESO
─────────────────
Nivel 0 — ANÓNIMO
  • Landing pública únicamente
  • Formulario de solicitud de invitación
  • T&C y Privacidad

Nivel 1 — REGISTRADO (free, sin membresía activa)
  • Feed con posts públicos (difuminados)
  • Ver perfiles básicos (sin fotos privadas)
  • Solicitar conexiones (sin mensajería)
  • Ver eventos públicos (sin dirección exacta)
  • Perfil propio básico

Nivel 2 — BÁSICO (membresía activa mínima)
  • Feed completo (fotos sin difuminar de miembros)
  • Mensajería privada (hasta 5/día)
  • Ver ubicación de otros miembros (estado/ciudad)
  • Asistir a eventos públicos
  • Subir hasta 10 fotos/mes

Nivel 3 — VIP
  • Todo lo de Básico
  • Relatos de contenido adulto
  • Plan para Hoy (publicar y ver)
  • Mensajería ilimitada
  • Ver fotos privadas de perfiles VIP
  • Crear eventos
  • Subida ilimitada de fotos/videos

Nivel 4 — PREMIUM
  • Todo lo de VIP
  • Dirección exacta de eventos privados
  • Grupos/Salas privadas exclusivos
  • Videollamada (fase 2)
  • Badge especial en perfil
```

---

## ENTREGABLE 3 — ROADMAP POR SPRINTS

```
╔══════════════════════════════════════════════════════════════════════════════╗
║  SPRINT 0 — SEMANA 1-2: Infraestructura y Base del Proyecto (YA EN CURSO)  ║
╚══════════════════════════════════════════════════════════════════════════════╝
✅ Descomprimir y analizar template
✅ Instalar dependencias Python
✅ Adaptar branding LOBBY69 (CSS, logo, textos)
✅ Adaptar navbar, sidenav, base.html
✅ Adaptar páginas de autenticación
✅ Servidor Django levantado y funcionando

→ PENDIENTE Sprint 0:
  □ Migrar de SQLite a PostgreSQL
  □ Configurar variables de entorno (.env)
  □ Configurar almacenamiento de archivos (S3 o local con estructura)
  □ Setup de estructura de directorios de apps nuevas

╔══════════════════════════════════════════════════════════════════════════════╗
║  SPRINT 1 — SEMANA 2-4: Autenticación y Perfiles                           ║
╚══════════════════════════════════════════════════════════════════════════════╝
□ Extender User model con campos LOBBY69
□ Extender UserSignupForm: invite_code, birth_date, consentimiento
□ Modificar AccountAdapter para validar invitaciones
□ Crear app `invitations` (Invitation model + admin básico)
□ Crear app `profiles` (Profile + CoupleProfile models)
□ Señales: auto-crear Profile en post_save de User
□ Conectar my-profile.html con Profile real
□ Conectar settings.html con forms de perfil reales
□ Flujo de solicitud de invitación desde landing.html
□ Conectar partials/navbar.html con auth de Django ({% if user.is_authenticated %})
□ Conectar partials/sidenav.html con profile.nick, profile.avatar

╔══════════════════════════════════════════════════════════════════════════════╗
║  SPRINT 2 — SEMANA 4-6: Feed Social y Contenido Básico                     ║
╚══════════════════════════════════════════════════════════════════════════════╝
□ Crear app `social_feed` (Post, Comment, Like models)
□ Conectar index.html con QuerySet de Posts reales
□ Vistas CRUD para posts (create/detail/delete con permisos)
□ Sistema de Likes vía AJAX/HTMX
□ Conectar messaging.html con modelos de mensajería
□ Crear app `messaging` (Conversation + Message models)
□ Vistas de inbox y conversación individual

╔══════════════════════════════════════════════════════════════════════════════╗
║  SPRINT 3 — SEMANA 6-8: Media y Moderación                                 ║
╚══════════════════════════════════════════════════════════════════════════════╝
□ Crear app `media_content` (Photo, Video, Album models)
□ Subida de fotos con Dropzone.js (ya integrado en vendors)
□ Subida de videos con validación de tipo/tamaño
□ Conectar my-profile-media.html con fotos reales
□ Conectar albums.html con Album model
□ Crear app `moderation` (ContentReport + ModerationQueue)
□ Lógica de visibilidad por nivel de membresía
□ Difuminado automático de fotos para nivel free

╔══════════════════════════════════════════════════════════════════════════════╗
║  SPRINT 4 — SEMANA 8-10: Membresías y Pagos                                ║
╚══════════════════════════════════════════════════════════════════════════════╝
□ Crear app `memberships` (MembershipPlan + Membership models)
□ Vista de planes de membresía (página pública)
□ Decorator @requires_membership para contenido restringido
□ Crear app `payments` (Payment model + gateway abstraction)
□ Integración MercadoPago (prioridad México)
□ Webhooks de confirmación de pago
□ Auto-activación de membresía tras pago
□ Panel de gestión de membresía en settings.html

╔══════════════════════════════════════════════════════════════════════════════╗
║  SPRINT 5 — SEMANA 10-12: Eventos y Geolocalización                        ║
╚══════════════════════════════════════════════════════════════════════════════╝
□ Crear app `events` (Event + EventAttendee models)
□ Conectar events.html con Event QuerySet real
□ Conectar event-details.html con Event model
□ Flujo de RSVP / asistencia a evento
□ Revelación de dirección exacta solo a confirmados VIP
□ Crear app `geo` (UserLocation + índice geoespacial)
□ Búsqueda por estado/ciudad en perfiles
□ Filtro de cercanía en eventos y perfiles

╔══════════════════════════════════════════════════════════════════════════════╗
║  SPRINT 6 — SEMANA 12-14: Verificación y Panel Admin                       ║
╚══════════════════════════════════════════════════════════════════════════════╝
□ Crear app `verification` (AgeVerification model)
□ Flujo de subida de documento de identidad
□ Dashboard de verificación para moderadores
□ Crear app `admin_panel` con vistas de gestión
□ Dashboard de métricas básico (usuarios, membresías, reportes)
□ Gestión de invitaciones desde admin
□ Gestión de moderación de contenido
□ Cola de aprobación de eventos

╔══════════════════════════════════════════════════════════════════════════════╗
║  SPRINT 7 — SEMANA 14-16: Relatos y Plan para Hoy                          ║
╚══════════════════════════════════════════════════════════════════════════════╝
□ Modelo Story/Tale (relato narrativo adulto — solo VIP)
□ Vista de feed de relatos (reutilizar blog.html adaptado)
□ Vista de detalle de relato (reutilizar blog-details.html adaptado)
□ Editor de relatos con contador de caracteres
□ Modelo PlanForToday (encuentro propuesto para hoy)
□ Vista de feed de "Plan para Hoy"
□ Búsqueda de planes por ciudad/tipo de perfil

╔══════════════════════════════════════════════════════════════════════════════╗
║  SPRINT 8 — SEMANA 16-18: Pulido, Testing y Lanzamiento                    ║
╚══════════════════════════════════════════════════════════════════════════════╝
□ Testing exhaustivo de flujos críticos
□ Revisión de seguridad (CSRF, XSS, rate limiting)
□ Términos de Servicio y Aviso de Privacidad legal LOBBY69
□ Configurar sistema de emails transaccionales
□ Configurar dominio, HTTPS, deployment en producción
□ Configurar backup automático de PostgreSQL
□ Configurar monitoreo de errores (Sentry)
□ Soft launch con usuarios beta (invitaciones)
```

---

## ENTREGABLE 4 — PROPUESTA DE ADAPTACIÓN VISUAL

### 4.1 FILOSOFÍA VISUAL: "EVOLUCIÓN, NO REVOLUCIÓN"

```
PRINCIPIO RECTOR
─────────────────
El template Social tiene un diseño premium y moderno.
No se toca. Se adapta. Se colorea. Se renombra.

Lo que YA EXISTE y está intacto:
  ✅ Grid system Bootstrap 5 (12 cols, gutter, breakpoints)
  ✅ Sistema de cards (card, card-header, card-body, card-footer)
  ✅ Navbar responsive con offcanvas en mobile
  ✅ Sistema de tabs (nav-pills, nav-tabs)
  ✅ Sistema de dropdowns
  ✅ Sistema de modales
  ✅ Stories carousel (zuck.js)
  ✅ Dark mode nativo (localStorage + data-bs-theme)
  ✅ Avatares con tamaños (xs, sm, md, lg, xl, xxl)
  ✅ Sistema de notificaciones
  ✅ Profile layout de 2 columnas
  ✅ Chat layout con sidebar + ventana

Lo que SE ADAPTA (solo CSS override en lobby69.css):
  🎨 Paleta de colores
  🎨 Logo
  🎨 Textos y labels
  🎨 Botones (clases mantienen nombre Bootstrap)
  🎨 Fondo oscuro como predeterminado
```

### 4.2 PALETA DE COLORES LOBBY69

```
TEMA OSCURO (predeterminado para plataforma adulta)
──────────────────────────────────────────────────
--lobby-dark:        #0d0d1a   ← Body/fondo principal
--lobby-dark-2:      #14142a   ← Navbar, headers
--lobby-dark-3:      #1e1e3a   ← Elementos interactivos
--lobby-dark-card:   #181830   ← Cards

GOLD (identidad de marca LOBBY69)
──────────────────────────────────
--lobby-gold:        #c8a84b   ← Primary (override de --bs-primary)
--lobby-gold-light:  #f0d080   ← Hover states, highlights
--lobby-gold-dark:   #9a7a2e   ← Borders, shadows

ROJO (CTAs urgentes, alertas, indicadores activos)
──────────────────────────────────────────────────
--lobby-red:         #e84040   ← Badge online, alertas, peligro
--lobby-red-dark:    #b02020   ← Hover de elementos rojos

TEXTO
──────────────────────────────────────────────────
body text:           #d0d0e0   ← Texto principal
muted text:          #7070a0   ← Texto secundario
headings:            #e8e8f8   ← H1-H6
links:               #c8a84b   ← Override de Bootstrap links
```

### 4.3 ESTRATEGIA DE IMPLEMENTACIÓN CSS

```
REGLA DE ORO: 
Solo se modifica lobby69.css (creado).
NUNCA se toca style.css ni vendor.min.css.

METODOLOGÍA:
1. Variables CSS (:root) → override de Bootstrap CSS vars
2. Selectores específicos → override de clases temáticas  
3. Nuevas clases .lobby-* → componentes nuevos sin conflicto
4. Media queries propios → ajustes responsive adicionales

ESTRUCTURA DE lobby69.css (ya existente, se expande):
  :root {}                    ← CSS vars override
  body/background             ← Fondo global
  .navbar-light               ← Override navbar
  .card                       ← Override cards
  .btn-primary                ← Override botón principal
  .form-control               ← Override inputs
  .dropdown-menu              ← Override dropdowns
  .offcanvas                  ← Override sidebar
  .lobby-*                    ← Nuevas clases específicas
```

### 4.4 MAPA DE TEMPLATES EXISTENTES → PÁGINAS LOBBY69

```
TEMPLATE ORIGINAL              PÁGINA LOBBY69             ACCIÓN
──────────────────────────────────────────────────────────────────
landing.html               →  Landing / Inicio público    Modificar textos + conectar form
sign-in-advance.html       →  Iniciar Sesión              ✅ Conectar allauth
sign-up-advance.html       →  Registro                    ✅ Conectar allauth + campos nuevos
forgot-password-adv.html   →  Recuperar contraseña        Conectar allauth
index.html                 →  Feed principal del club      Conectar datos reales
my-profile.html            →  Mi Perfil (feed)            Conectar Profile
my-profile-about.html      →  Perfil > Sobre Mí           Conectar campos swinger
my-profile-connections.html→  Mis Conexiones              Conectar Follow model
my-profile-media.html      →  Mis Fotos                   Conectar Media model
my-profile-videos.html     →  Mis Videos                  Conectar Video model
my-profile-events.html     →  Mis Eventos                 Conectar Event model
settings.html              →  Configuración / Cuenta       Conectar forms reales + tab Membresía
messaging.html             →  Mensajes Privados           Conectar Conversation model
notifications.html         →  Notificaciones              Conectar Notification model
events.html                →  Eventos y Fiestas           Conectar Event QuerySet
event-details.html         →  Detalle de Evento           Conectar Event model
groups.html                →  Salas / Encuentros          Reutilizar como "Buscar"
group-details.html         →  Detalle de Sala/Encuentro   Conectar modelo
blog.html                  →  Relatos (Contenido VIP)     Reutilizar layout
blog-details.html          →  Detalle de Relato           Reutilizar layout  
albums.html                →  Álbumes Públicos            Conectar Album model
video-details.html         →  Ver Video                   Conectar Video + Plyr.js
privacy-and-terms.html     →  Aviso Legal LOBBY69         Reemplazar contenido
help.html                  →  Centro de Ayuda             Reutilizar estructura
error-404.html             →  Error 404 LOBBY69           Conservar layout
```

### 4.5 COMPONENTES NUEVOS NECESARIOS (CSS mínimo)

```
1. BADGE DE MEMBRESÍA en avatar
   .lobby-badge-free {}
   .lobby-badge-basic {}  
   .lobby-badge-vip {}
   .lobby-badge-premium {}

2. CARD DE PERFIL DE PAREJA
   .lobby-couple-card {} (doble avatar superpuesto)

3. PILL DE TIPO DE PERFIL
   .lobby-profile-type {} (color diferente por tipo)

4. INDICADOR "PLAN PARA HOY"
   .lobby-plan-today {} (badge urgente pulsante)

5. FOTO DIFUMINADA (nivel free)
   .lobby-blurred-photo { filter: blur(12px); }
   .lobby-unlock-overlay {} (CTA de membresía sobre foto)

6. NIVEL DE PRIVACIDAD BADGE
   .lobby-visibility-badge {} (colored by: public/members/vip)
```

---

## RESUMEN EJECUTIVO

| Dimensión | Detalle |
|---|---|
| **Templates reutilizables** | 40 de 44 páginas HTML (91%) |
| **Código Python conservado** | 100% del código existente |
| **Código Python nuevo** | ~13 apps nuevas + modelos + vistas |
| **CSS nuevo** | ~400 líneas adicionales en lobby69.css |
| **CSS original** | 0 líneas modificadas (intacto) |
| **Sprints estimados** | 8 sprints × 2 semanas = 16 semanas |
| **Stack adicional requerido** | PostgreSQL, Pillow, django-storages, MercadoPago SDK |
| **Riesgo de rompimiento visual** | Mínimo (override CSS no destructivo) |

---

*Documento generado tras análisis del código fuente real del proyecto LOBBY69.*  
*Ubicación del proyecto: `/home/user/lobby69/`*  
*Servidor activo: `http://localhost:5060/`*
