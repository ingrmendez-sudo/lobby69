from django.db import models
from django.conf import settings
from django.utils import timezone
import uuid


class InvitationRequest(models.Model):
    STATUS_CHOICES = [
        ('pending', 'Pendiente'),
        ('approved', 'Aprobado'),
        ('rejected', 'Rechazado'),
    ]

    invited_by = models.CharField(max_length=255, blank=True, null=True)
    PROFILE_TYPE_CHOICES = [
        ('single', 'Single'),
        ('unicornio', 'Unicornio'),
        ('pareja', 'Pareja'),
    ]

    ESTADO_CHOICES = [
        ('aguascalientes', 'Aguascalientes'),
        ('baja_california', 'Baja California'),
        ('cdmx', 'Ciudad de México'),
        # ... (agregar otros estados)
    ]

    id = models.UUIDField(primary_key=True, default=uuid.uuid4, editable=False)
    nombre_completo = models.CharField(max_length=255)
    email = models.EmailField(unique=True)
    edad = models.IntegerField()
    pais = models.CharField(max_length=50, default='Mexico')
    estado = models.CharField(max_length=50, choices=ESTADO_CHOICES)
    municipio = models.CharField(max_length=255)
    tipo_perfil = models.CharField(max_length=50, choices=PROFILE_TYPE_CHOICES)
    motivo = models.TextField()
    status = models.CharField(max_length=20, choices=STATUS_CHOICES, default='pending')
    terminos_aceptados = models.BooleanField(default=False)
    privacidad_aceptada = models.BooleanField(default=False)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)
    approved_by = models.ForeignKey(settings.AUTH_USER_MODEL, null=True, blank=True, on_delete=models.SET_NULL, related_name='approved_invitations')
    rejection_reason = models.TextField(blank=True, null=True)
    activation_token = models.CharField(max_length=255, unique=True, blank=True)
    activation_used = models.BooleanField(default=False)
    activated_at = models.DateTimeField(null=True, blank=True)

    class Meta:
        db_table = 'invitation_requests'
        verbose_name = 'Solicitud de Invitación'
        verbose_name_plural = 'Solicitudes de Invitación'
        ordering = ['-created_at']

    def __str__(self):
        return f"{self.nombre_completo} - {self.status}"

    @staticmethod
    def generate_activation_token():
        import hashlib
        token_input = f"{uuid.uuid4()}{timezone.now().isoformat()}"
        token = hashlib.sha256(token_input.encode()).hexdigest()
        return token


class PhotoLike(models.Model):
    """Like en foto de galería"""
    id = models.UUIDField(primary_key=True, default=uuid.uuid4, editable=False)
    photo_id = models.CharField(max_length=255)
    user_id = models.CharField(max_length=255)
    created_at = models.DateTimeField(auto_now_add=True)

    class Meta:
        db_table = 'photo_likes'
        unique_together = ['photo_id', 'user_id']

    def __str__(self):
        return f"Like: {self.photo_id} by {self.user_id}"


class PhotoComment(models.Model):
    """Comentario en foto de galería"""
    id = models.UUIDField(primary_key=True, default=uuid.uuid4, editable=False)
    photo_id = models.CharField(max_length=255)
    user_id = models.CharField(max_length=255)
    comment_text = models.TextField()
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        db_table = 'photo_comments'
        ordering = ['-created_at']

    def __str__(self):
        return f"Comment on {self.photo_id}: {self.comment_text[:50]}"


class PhotoAlbum(models.Model):
    """Álbum de fotos"""
    id = models.UUIDField(primary_key=True, default=uuid.uuid4, editable=False)
    account_id = models.CharField(max_length=255)
    album_name = models.CharField(max_length=100)
    description = models.TextField(blank=True, null=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        db_table = 'photo_albums'
        ordering = ['-created_at']

    def __str__(self):
        return f"{self.album_name} by {self.account_id}"


# Gallery model moved to social/users/models.py - Do not duplicate here
