"""
Modelos de Membresías LOBBY69
Planes gratuito, básico y premium con control de acceso.
"""
from django.db import models
from django.conf import settings
from django.utils.translation import gettext_lazy as _


class MembershipPlan(models.Model):
    """Planes de membresía disponibles."""

    class PlanType(models.TextChoices):
        FREE = "GRATIS", _("Acceso libre")
        BASIC = "BASICO", _("Básico")
        VIP = "VIP", _("VIP")
        ELITE = "ELITE", _("Élite")

    name = models.CharField(max_length=50, verbose_name=_("Nombre"))
    plan_type = models.CharField(
        max_length=10,
        choices=PlanType.choices,
        unique=True,
        verbose_name=_("Tipo"),
    )
    price_mxn = models.DecimalField(
        max_digits=8,
        decimal_places=2,
        default=0,
        verbose_name=_("Precio MXN"),
    )
    price_mxn_monthly = models.DecimalField(
        max_digits=8,
        decimal_places=2,
        default=0,
        verbose_name=_("Precio mensual MXN"),
    )
    description = models.TextField(blank=True, verbose_name=_("Descripción"))
    features = models.JSONField(default=list, verbose_name=_("Características"))

    # Límites
    max_photos = models.PositiveIntegerField(default=5, verbose_name=_("Máx fotos"))
    max_videos = models.PositiveIntegerField(default=0, verbose_name=_("Máx videos"))
    max_messages_per_day = models.PositiveIntegerField(default=10, verbose_name=_("Mensajes/día"))
    can_view_private_photos = models.BooleanField(default=False)
    can_send_invitations = models.BooleanField(default=False)
    can_join_events = models.BooleanField(default=False)
    can_create_events = models.BooleanField(default=False)
    profile_badge = models.CharField(max_length=50, blank=True, verbose_name=_("Insignia"))

    is_active = models.BooleanField(default=True)
    sort_order = models.PositiveSmallIntegerField(default=0)

    class Meta:
        verbose_name = _("Plan de membresía")
        verbose_name_plural = _("Planes de membresía")
        ordering = ["sort_order"]

    def __str__(self):
        return f"{self.name} (${self.price_mxn_monthly}/mes)"


class UserMembership(models.Model):
    """Membresía activa de un usuario."""

    class StatusChoices(models.TextChoices):
        ACTIVE = "ACTIVO", _("Activo")
        EXPIRED = "EXPIRADO", _("Expirado")
        CANCELLED = "CANCELADO", _("Cancelado")
        GRACE = "GRACIA", _("Período de gracia")

    user = models.OneToOneField(
        settings.AUTH_USER_MODEL,
        on_delete=models.CASCADE,
        related_name="membership",
        verbose_name=_("Usuario"),
    )
    plan = models.ForeignKey(
        MembershipPlan,
        on_delete=models.PROTECT,
        verbose_name=_("Plan"),
    )
    status = models.CharField(
        max_length=10,
        choices=StatusChoices.choices,
        default=StatusChoices.ACTIVE,
        verbose_name=_("Estado"),
    )
    starts_at = models.DateTimeField(verbose_name=_("Inicio"))
    expires_at = models.DateTimeField(null=True, blank=True, verbose_name=_("Vencimiento"))
    auto_renew = models.BooleanField(default=False, verbose_name=_("Renovación automática"))
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        verbose_name = _("Membresía de usuario")
        verbose_name_plural = _("Membresías de usuarios")

    def __str__(self):
        return f"{self.user} → {self.plan.name}"

    @property
    def is_active(self):
        from django.utils import timezone
        if self.status != self.StatusChoices.ACTIVE:
            return False
        if self.expires_at and self.expires_at < timezone.now():
            return False
        return True
