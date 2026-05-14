"""
Modelos de Invitaciones LOBBY69
Sistema de invitaciones exclusivas para el club privado.
"""
from django.db import models
from django.conf import settings
from django.utils.translation import gettext_lazy as _
import uuid


class InvitationStatus(models.TextChoices):
    PENDING = "PENDIENTE", _("Pendiente")
    SENT = "ENVIADA", _("Enviada")
    ACCEPTED = "ACEPTADA", _("Aceptada")
    EXPIRED = "EXPIRADA", _("Expirada")
    REVOKED = "REVOCADA", _("Revocada")


class Invitation(models.Model):
    """Invitación al club LOBBY69."""

    code = models.UUIDField(
        default=uuid.uuid4,
        unique=True,
        editable=False,
        verbose_name=_("Código de invitación"),
    )
    invited_by = models.ForeignKey(
        settings.AUTH_USER_MODEL,
        on_delete=models.SET_NULL,
        null=True,
        blank=True,
        related_name="invitations_sent",
        verbose_name=_("Invitado por"),
    )
    email = models.EmailField(
        blank=True,
        verbose_name=_("Email del invitado"),
    )
    phone = models.CharField(
        max_length=20,
        blank=True,
        verbose_name=_("Teléfono del invitado"),
    )
    message = models.TextField(
        blank=True,
        max_length=500,
        verbose_name=_("Mensaje personal"),
    )
    status = models.CharField(
        max_length=15,
        choices=InvitationStatus.choices,
        default=InvitationStatus.PENDING,
        verbose_name=_("Estado"),
    )
    used_by = models.ForeignKey(
        settings.AUTH_USER_MODEL,
        on_delete=models.SET_NULL,
        null=True,
        blank=True,
        related_name="invitation_used",
        verbose_name=_("Usado por"),
    )
    used_at = models.DateTimeField(null=True, blank=True)
    expires_at = models.DateTimeField(null=True, blank=True, verbose_name=_("Expira"))
    created_at = models.DateTimeField(auto_now_add=True)

    class Meta:
        verbose_name = _("Invitación")
        verbose_name_plural = _("Invitaciones")
        ordering = ["-created_at"]

    def __str__(self):
        return f"Invitación {str(self.code)[:8]}... ({self.get_status_display()})"

    @property
    def is_valid(self):
        from django.utils import timezone
        if self.status not in [InvitationStatus.PENDING, InvitationStatus.SENT]:
            return False
        if self.expires_at and self.expires_at < timezone.now():
            return False
        return True


class InvitationRequest(models.Model):
    """Solicitud de acceso al club (desde la landing pública)."""

    full_name = models.CharField(max_length=200, verbose_name=_("Nombre completo"))
    email = models.EmailField(unique=True, verbose_name=_("Email"))
    phone = models.CharField(max_length=20, blank=True, verbose_name=_("Teléfono"))
    profile_type = models.CharField(
        max_length=20,
        choices=[("HOMBRE", "Hombre"), ("MUJER", "Mujer"), ("PAREJA", "Pareja")],
        default="HOMBRE",
        verbose_name=_("Tipo de perfil"),
    )
    message = models.TextField(
        blank=True,
        max_length=500,
        verbose_name=_("¿Por qué quieres unirte?"),
    )
    age_confirmed = models.BooleanField(
        default=False,
        verbose_name=_("Confirma ser mayor de 18"),
    )
    terms_accepted = models.BooleanField(
        default=False,
        verbose_name=_("Acepta términos y condiciones"),
    )
    referral_code = models.CharField(
        max_length=100,
        blank=True,
        verbose_name=_("Código de referido (opcional)"),
    )

    class RequestStatus(models.TextChoices):
        PENDING = "PENDIENTE", _("Pendiente de revisión")
        APPROVED = "APROBADA", _("Aprobada")
        REJECTED = "RECHAZADA", _("Rechazada")
        WAITLIST = "LISTA_ESPERA", _("Lista de espera")

    status = models.CharField(
        max_length=15,
        choices=RequestStatus.choices,
        default=RequestStatus.PENDING,
        verbose_name=_("Estado"),
    )
    reviewed_by = models.ForeignKey(
        settings.AUTH_USER_MODEL,
        on_delete=models.SET_NULL,
        null=True,
        blank=True,
        related_name="reviewed_requests",
    )
    reviewed_at = models.DateTimeField(null=True, blank=True)
    admin_notes = models.TextField(blank=True, verbose_name=_("Notas del admin"))

    # IP y metadata
    ip_address = models.GenericIPAddressField(null=True, blank=True)
    created_at = models.DateTimeField(auto_now_add=True)

    class Meta:
        verbose_name = _("Solicitud de acceso")
        verbose_name_plural = _("Solicitudes de acceso")
        ordering = ["-created_at"]

    def __str__(self):
        return f"{self.full_name} ({self.email}) - {self.get_status_display()}"
