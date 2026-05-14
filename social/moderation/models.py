"""
Modelos de Moderación LOBBY69
Reportes de contenido y cola de moderación.
"""
from django.db import models
from django.conf import settings
from django.contrib.contenttypes.fields import GenericForeignKey
from django.contrib.contenttypes.models import ContentType
from django.utils.translation import gettext_lazy as _


class Report(models.Model):
    """Reporte de contenido inapropiado."""

    class ReportReason(models.TextChoices):
        UNDERAGE = "MENOR_EDAD", _("Contenido con menores de edad")
        SPAM = "SPAM", _("Spam o contenido no deseado")
        HARASSMENT = "ACOSO", _("Acoso o intimidación")
        FAKE_PROFILE = "PERFIL_FALSO", _("Perfil falso")
        INAPPROPRIATE = "INAPROPIADO", _("Contenido inapropiado")
        OTHER = "OTRO", _("Otro")

    class ReportStatus(models.TextChoices):
        PENDING = "PENDIENTE", _("Pendiente")
        REVIEWING = "EN_REVISION", _("En revisión")
        RESOLVED = "RESUELTO", _("Resuelto")
        DISMISSED = "DESCARTADO", _("Descartado")

    reporter = models.ForeignKey(
        settings.AUTH_USER_MODEL,
        on_delete=models.CASCADE,
        related_name="reports_made",
        verbose_name=_("Reportado por"),
    )

    # Contenido genérico reportado (puede ser Post, Perfil, Mensaje, etc.)
    content_type = models.ForeignKey(ContentType, on_delete=models.CASCADE, null=True, blank=True)
    object_id = models.PositiveBigIntegerField(null=True, blank=True)
    reported_object = GenericForeignKey("content_type", "object_id")

    reason = models.CharField(
        max_length=15,
        choices=ReportReason.choices,
        verbose_name=_("Motivo"),
    )
    description = models.TextField(
        blank=True,
        max_length=1000,
        verbose_name=_("Descripción"),
    )
    status = models.CharField(
        max_length=12,
        choices=ReportStatus.choices,
        default=ReportStatus.PENDING,
        verbose_name=_("Estado"),
    )
    reviewed_by = models.ForeignKey(
        settings.AUTH_USER_MODEL,
        on_delete=models.SET_NULL,
        null=True,
        blank=True,
        related_name="reports_reviewed",
    )
    reviewed_at = models.DateTimeField(null=True, blank=True)
    moderator_notes = models.TextField(blank=True)

    created_at = models.DateTimeField(auto_now_add=True)

    class Meta:
        verbose_name = _("Reporte")
        verbose_name_plural = _("Reportes")
        ordering = ["-created_at"]

    def __str__(self):
        return f"Reporte de {self.reporter} - {self.get_reason_display()}"


class ModerationLog(models.Model):
    """Registro de acciones de moderación."""

    class ActionType(models.TextChoices):
        APPROVE = "APROBAR", _("Aprobó contenido")
        REJECT = "RECHAZAR", _("Rechazó contenido")
        SUSPEND = "SUSPENDER", _("Suspendió usuario")
        BAN = "BANEAR", _("Baneó usuario")
        WARN = "ADVERTIR", _("Advirtió usuario")
        RESTORE = "RESTAURAR", _("Restauró contenido")

    moderator = models.ForeignKey(
        settings.AUTH_USER_MODEL,
        on_delete=models.CASCADE,
        related_name="moderation_actions",
    )
    action = models.CharField(max_length=10, choices=ActionType.choices)
    target_user = models.ForeignKey(
        settings.AUTH_USER_MODEL,
        on_delete=models.CASCADE,
        null=True,
        blank=True,
        related_name="moderation_received",
    )
    notes = models.TextField(blank=True)
    created_at = models.DateTimeField(auto_now_add=True)

    class Meta:
        verbose_name = _("Log de moderación")
        verbose_name_plural = _("Logs de moderación")
        ordering = ["-created_at"]

    def __str__(self):
        return f"{self.moderator} → {self.get_action_display()} ({self.created_at:%Y-%m-%d})"
