"""
Modelos de Pagos LOBBY69
Registro de transacciones y validación de membresías.
"""
from django.db import models
from django.conf import settings
from django.utils.translation import gettext_lazy as _
import uuid


class Payment(models.Model):
    """Registro de un pago de membresía."""

    class PaymentMethod(models.TextChoices):
        CARD = "TARJETA", _("Tarjeta de crédito/débito")
        TRANSFER = "TRANSFERENCIA", _("Transferencia bancaria")
        OXXO = "OXXO", _("OXXO Pay")
        PAYPAL = "PAYPAL", _("PayPal")
        CRYPTO = "CRIPTO", _("Criptomoneda")

    class PaymentStatus(models.TextChoices):
        PENDING = "PENDIENTE", _("Pendiente")
        PROCESSING = "PROCESANDO", _("Procesando")
        COMPLETED = "COMPLETADO", _("Completado")
        FAILED = "FALLIDO", _("Fallido")
        REFUNDED = "REEMBOLSADO", _("Reembolsado")
        CANCELLED = "CANCELADO", _("Cancelado")

    reference = models.UUIDField(
        default=uuid.uuid4,
        unique=True,
        editable=False,
        verbose_name=_("Referencia"),
    )
    user = models.ForeignKey(
        settings.AUTH_USER_MODEL,
        on_delete=models.CASCADE,
        related_name="payments",
        verbose_name=_("Usuario"),
    )
    plan = models.ForeignKey(
        "memberships.MembershipPlan",
        on_delete=models.PROTECT,
        verbose_name=_("Plan"),
    )
    amount_mxn = models.DecimalField(
        max_digits=10,
        decimal_places=2,
        verbose_name=_("Monto MXN"),
    )
    method = models.CharField(
        max_length=15,
        choices=PaymentMethod.choices,
        verbose_name=_("Método de pago"),
    )
    status = models.CharField(
        max_length=12,
        choices=PaymentStatus.choices,
        default=PaymentStatus.PENDING,
        verbose_name=_("Estado"),
    )

    # Datos del proveedor de pago (Stripe/Conekta/etc.)
    provider_ref = models.CharField(
        max_length=255,
        blank=True,
        verbose_name=_("Referencia del proveedor"),
    )
    provider_response = models.JSONField(
        default=dict,
        blank=True,
        verbose_name=_("Respuesta del proveedor"),
    )

    # Comprobante manual (para transferencias/OXXO)
    receipt_image = models.ImageField(
        upload_to="payments/receipts/%Y/%m/",
        null=True,
        blank=True,
        verbose_name=_("Comprobante"),
    )
    receipt_verified = models.BooleanField(default=False)
    receipt_verified_by = models.ForeignKey(
        settings.AUTH_USER_MODEL,
        on_delete=models.SET_NULL,
        null=True,
        blank=True,
        related_name="verified_payments",
    )
    receipt_verified_at = models.DateTimeField(null=True, blank=True)

    notes = models.TextField(blank=True, verbose_name=_("Notas"))
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        verbose_name = _("Pago")
        verbose_name_plural = _("Pagos")
        ordering = ["-created_at"]

    def __str__(self):
        return f"Pago {str(self.reference)[:8]}... - {self.user} - ${self.amount_mxn} MXN"
