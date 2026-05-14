"""
Modelos del Feed Social LOBBY69
Posts, stories, comentarios, reacciones y plan diario.
"""
from django.db import models
from django.conf import settings
from django.utils.translation import gettext_lazy as _


class Post(models.Model):
    """Publicación en el feed del club."""

    class PostType(models.TextChoices):
        TEXT = "TEXTO", _("Texto")
        PHOTO = "FOTO", _("Foto")
        VIDEO = "VIDEO", _("Video")
        EVENT = "EVENTO", _("Evento")
        STORY = "STORY", _("Story")
        POLL = "ENCUESTA", _("Encuesta")

    class VisibilityChoices(models.TextChoices):
        PUBLIC = "PUBLICO", _("Público (miembros)")
        CONNECTIONS = "CONEXIONES", _("Solo conexiones")
        PRIVATE = "PRIVADO", _("Solo yo")

    author = models.ForeignKey(
        settings.AUTH_USER_MODEL,
        on_delete=models.CASCADE,
        related_name="posts",
        verbose_name=_("Autor"),
    )
    post_type = models.CharField(
        max_length=10,
        choices=PostType.choices,
        default=PostType.TEXT,
        verbose_name=_("Tipo"),
    )
    content = models.TextField(blank=True, max_length=2000, verbose_name=_("Contenido"))
    visibility = models.CharField(
        max_length=12,
        choices=VisibilityChoices.choices,
        default=VisibilityChoices.PUBLIC,
        verbose_name=_("Visibilidad"),
    )

    # Media adjunta
    image = models.ImageField(
        upload_to="feed/images/%Y/%m/",
        null=True,
        blank=True,
        verbose_name=_("Imagen"),
    )
    video = models.FileField(
        upload_to="feed/videos/%Y/%m/",
        null=True,
        blank=True,
        verbose_name=_("Video"),
    )

    # Moderación
    is_approved = models.BooleanField(default=False, verbose_name=_("Aprobado"))
    is_nsfw = models.BooleanField(default=False, verbose_name=_("Contenido adulto"))
    moderated_by = models.ForeignKey(
        settings.AUTH_USER_MODEL,
        on_delete=models.SET_NULL,
        null=True,
        blank=True,
        related_name="moderated_posts",
    )
    moderated_at = models.DateTimeField(null=True, blank=True)

    # Métricas
    likes_count = models.PositiveIntegerField(default=0)
    comments_count = models.PositiveIntegerField(default=0)
    views_count = models.PositiveIntegerField(default=0)

    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)
    is_deleted = models.BooleanField(default=False)

    class Meta:
        verbose_name = _("Publicación")
        verbose_name_plural = _("Publicaciones")
        ordering = ["-created_at"]

    def __str__(self):
        return f"{self.author} - {self.get_post_type_display()} ({self.created_at:%Y-%m-%d})"


class PostReaction(models.Model):
    """Reacción (like/fuego/corazón) a una publicación."""

    class ReactionType(models.TextChoices):
        LIKE = "LIKE", "👍 Me gusta"
        FIRE = "FUEGO", "🔥 Fuego"
        HEART = "CORAZON", "❤️ Me encanta"
        WOW = "WOW", "😮 Wow"

    post = models.ForeignKey(Post, on_delete=models.CASCADE, related_name="reactions")
    user = models.ForeignKey(settings.AUTH_USER_MODEL, on_delete=models.CASCADE)
    reaction_type = models.CharField(max_length=10, choices=ReactionType.choices, default=ReactionType.LIKE)
    created_at = models.DateTimeField(auto_now_add=True)

    class Meta:
        unique_together = ("post", "user")
        verbose_name = _("Reacción")

    def __str__(self):
        return f"{self.user} → {self.reaction_type} en Post {self.post_id}"


class Comment(models.Model):
    """Comentario en un post."""

    post = models.ForeignKey(Post, on_delete=models.CASCADE, related_name="comments")
    author = models.ForeignKey(settings.AUTH_USER_MODEL, on_delete=models.CASCADE)
    content = models.TextField(max_length=500, verbose_name=_("Comentario"))
    parent = models.ForeignKey(
        "self",
        null=True,
        blank=True,
        on_delete=models.CASCADE,
        related_name="replies",
    )
    is_approved = models.BooleanField(default=True)
    created_at = models.DateTimeField(auto_now_add=True)

    class Meta:
        verbose_name = _("Comentario")
        verbose_name_plural = _("Comentarios")
        ordering = ["created_at"]

    def __str__(self):
        return f"{self.author} en Post {self.post_id}"


class Story(models.Model):
    """Story (expira en 24h) del feed del club."""

    author = models.ForeignKey(
        settings.AUTH_USER_MODEL,
        on_delete=models.CASCADE,
        related_name="stories",
    )
    image = models.ImageField(upload_to="stories/%Y/%m/", null=True, blank=True)
    video = models.FileField(upload_to="stories/videos/%Y/%m/", null=True, blank=True)
    caption = models.CharField(max_length=200, blank=True)
    is_nsfw = models.BooleanField(default=False)
    views_count = models.PositiveIntegerField(default=0)
    created_at = models.DateTimeField(auto_now_add=True)
    expires_at = models.DateTimeField(verbose_name=_("Expira"))

    class Meta:
        verbose_name = _("Story")
        verbose_name_plural = _("Stories")
        ordering = ["-created_at"]

    def __str__(self):
        return f"Story de {self.author} ({self.created_at:%Y-%m-%d %H:%M})"

    @property
    def is_expired(self):
        from django.utils import timezone
        return self.expires_at < timezone.now()


class DailyPlan(models.Model):
    """Plan del día: agenda de actividades del club."""

    class PlanType(models.TextChoices):
        TOURNAMENT = "TORNEO", _("Torneo")
        THEME_NIGHT = "NOCHE_TEMATICA", _("Noche temática")
        PRIVATE_EVENT = "EVENTO_PRIVADO", _("Evento privado")
        WORKSHOP = "TALLER", _("Taller")
        MEETING = "REUNIÓN", _("Reunión")

    title = models.CharField(max_length=200, verbose_name=_("Título"))
    plan_type = models.CharField(max_length=20, choices=PlanType.choices, default=PlanType.TOURNAMENT)
    description = models.TextField(blank=True)
    date = models.DateField(verbose_name=_("Fecha"))
    start_time = models.TimeField(verbose_name=_("Hora inicio"))
    end_time = models.TimeField(null=True, blank=True, verbose_name=_("Hora fin"))
    location = models.CharField(max_length=200, blank=True, verbose_name=_("Lugar"))
    max_participants = models.PositiveIntegerField(null=True, blank=True)
    is_members_only = models.BooleanField(default=True)
    min_membership = models.CharField(max_length=10, blank=True, verbose_name=_("Membresía mínima"))
    image = models.ImageField(upload_to="plans/%Y/%m/", null=True, blank=True)
    created_at = models.DateTimeField(auto_now_add=True)

    class Meta:
        verbose_name = _("Plan del día")
        verbose_name_plural = _("Planes del día")
        ordering = ["date", "start_time"]

    def __str__(self):
        return f"{self.title} - {self.date} {self.start_time}"
