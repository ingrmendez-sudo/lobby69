"""
Modelos de Verificación LOBBY69
Verificación de edad e identidad de miembros.
"""
from django.db import models
from django.conf import settings
from django.utils.translation import gettext_lazy as _


class AgeVerification(models.Model):
    """Verificación de edad del miembro."""

    class VerificationMethod(models.TextChoices):
        ID_CARD = "INE", _("INE / Credencial")
        PASSPORT = "PASAPORTE", _("Pasaporte")
        DRIVERS_LICENSE = "LICENCIA", _("Licencia de conducir")
        SELFIE_ID = "SELFIE_ID", _("Selfie con ID")

    class VerificationStatus(models.TextChoices):
        PENDING = "PENDIENTE", _("Pendiente de revisión")
        APPROVED = "APROBADA", _("Aprobada")
        REJECTED = "RECHAZADA", _("Rechazada")
        RESUBMIT = "REENVIAR", _("Reenviar documentos")

    user = models.OneToOneField(
        settings.AUTH_USER_MODEL,
        on_delete=models.CASCADE,
        related_name="age_verification",
        verbose_name=_("Usuario"),
    )
    method = models.CharField(
        max_length=15,
        choices=VerificationMethod.choices,
        verbose_name=_("Método"),
    )
    status = models.CharField(
        max_length=10,
        choices=VerificationStatus.choices,
        default=VerificationStatus.PENDING,
        verbose_name=_("Estado"),
    )

    # Documentos subidos (almacenados cifrados)
    document_front = models.ImageField(
        upload_to="verification/documents/%Y/%m/",
        null=True,
        blank=True,
        verbose_name=_("Documento (frente)"),
    )
    document_back = models.ImageField(
        upload_to="verification/documents/%Y/%m/",
        null=True,
        blank=True,
        verbose_name=_("Documento (reverso)"),
    )
    selfie_with_id = models.ImageField(
        upload_to="verification/selfies/%Y/%m/",
        null=True,
        blank=True,
        verbose_name=_("Selfie con ID"),
    )

    # Datos extraídos / declarados
    declared_birth_date = models.DateField(null=True, blank=True, verbose_name=_("Fecha de nacimiento declarada"))
    declared_name = models.CharField(max_length=200, blank=True)

    # Revisión por admin
    reviewed_by = models.ForeignKey(
        settings.AUTH_USER_MODEL,
        on_delete=models.SET_NULL,
        null=True,
        blank=True,
        related_name="verified_users",
    )
    reviewed_at = models.DateTimeField(null=True, blank=True)
    rejection_reason = models.TextField(blank=True, verbose_name=_("Motivo de rechazo"))

    # Consentimiento
    consent_given = models.BooleanField(default=False, verbose_name=_("Consentimiento dado"))
    consent_ip = models.GenericIPAddressField(null=True, blank=True)
    consent_at = models.DateTimeField(null=True, blank=True)

    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        verbose_name = _("Verificación de edad")
        verbose_name_plural = _("Verificaciones de edad")
        ordering = ["-created_at"]

    def __str__(self):
        return f"Verificación de {self.user} - {self.get_status_display()}"
