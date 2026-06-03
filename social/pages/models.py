from django.db import models

# Create your models here.
# ============================================================================
# MODELOS DE MEMBRESÍA, PAGOS Y REFERENCIA
# ============================================================================

from django.db import models
from django.utils import timezone
from datetime import timedelta
import uuid

class PaymentTransaction(models.Model):
    """Tabla: payment_transactions"""

    STATUS_CHOICES = [
        ('pending', 'Pendiente'),
        ('completed', 'Completado'),
        ('failed', 'Fallido'),
        ('refunded', 'Reembolsado'),
    ]

    id = models.UUIDField(primary_key=True, default=uuid.uuid4, editable=False)
    account_id = models.UUIDField()  # FK a accounts
    membership_type_id = models.UUIDField(null=True, blank=True)  # FK a membership_types
    amount = models.DecimalField(max_digits=10, decimal_places=2)
    currency = models.CharField(max_length=3, default='MXN')
    status = models.CharField(max_length=20, choices=STATUS_CHOICES, default='pending')
    payment_method = models.CharField(max_length=50, blank=True)
    transaction_id = models.CharField(max_length=255, unique=True, blank=True, null=True)
    invoice_url = models.TextField(blank=True, null=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        db_table = 'payment_transactions'
        verbose_name = 'Transacción de Pago'
        verbose_name_plural = 'Transacciones de Pago'
        ordering = ['-created_at']

    def __str__(self):
        return f"{self.account_id} - {self.amount} MXN - {self.status}"


class ReferralCode(models.Model):
    """Tabla: referral_codes"""

    id = models.UUIDField(primary_key=True, default=uuid.uuid4, editable=False)
    account_id = models.UUIDField(unique=True)  # FK a accounts
    code = models.CharField(max_length=50, unique=True)
    created_at = models.DateTimeField(auto_now_add=True)
    expires_at = models.DateTimeField(null=True, blank=True)
    active = models.BooleanField(default=True)
    max_uses = models.IntegerField(null=True, blank=True)
    uses_count = models.IntegerField(default=0)

    class Meta:
        db_table = 'referral_codes'
        verbose_name = 'Código de Referencia'
        verbose_name_plural = 'Códigos de Referencia'

    def __str__(self):
        return f"{self.code} ({self.uses_count} usos)"

    def is_valid(self):
        """Verificar si el código es válido"""
        if not self.active:
            return False
        if self.expires_at and self.expires_at < timezone.now():
            return False
        if self.max_uses and self.uses_count >= self.max_uses:
            return False
        return True


class Referral(models.Model):
    """Tabla: referrals"""

    STATUS_CHOICES = [
        ('pending', 'Pendiente'),
        ('paid', 'Pagado'),
        ('converted', 'Convertido'),
    ]

    id = models.UUIDField(primary_key=True, default=uuid.uuid4, editable=False)
    referrer_account_id = models.UUIDField()  # Quién invitó
    referred_account_id = models.UUIDField(unique=True)  # Quién fue invitado
    code_used = models.CharField(max_length=50, blank=True)
    created_at = models.DateTimeField(auto_now_add=True)
    status = models.CharField(max_length=20, choices=STATUS_CHOICES, default='pending')
    referred_payment_date = models.DateTimeField(null=True, blank=True)
    reward_claimed = models.BooleanField(default=False)
    claimed_date = models.DateTimeField(null=True, blank=True)

    class Meta:
        db_table = 'referrals'
        verbose_name = 'Referencia'
        verbose_name_plural = 'Referencias'
        ordering = ['-created_at']

    def __str__(self):
        return f"{self.referrer_account_id} → {self.referred_account_id}"


class ReferralReward(models.Model):
    """Tabla: referral_rewards"""

    REWARD_TYPE_CHOICES = [
        ('free_month', 'Mes Gratis'),
        ('discount_percent', 'Descuento %'),
        ('vip_upgrade', 'Upgrade VIP'),
    ]

    id = models.UUIDField(primary_key=True, default=uuid.uuid4, editable=False)
    referrer_account_id = models.UUIDField()  # Quién recibe la recompensa
    required_paid_referrals = models.IntegerField()  # 5 referidos pagos
    reward_type = models.CharField(max_length=50, choices=REWARD_TYPE_CHOICES)
    reward_value = models.CharField(max_length=50)  # "1" mes, "15" %, etc
    claimed = models.BooleanField(default=False)
    claimed_date = models.DateTimeField(null=True, blank=True)
    created_at = models.DateTimeField(auto_now_add=True)

    class Meta:
        db_table = 'referral_rewards'
        verbose_name = 'Recompensa de Referencia'
        verbose_name_plural = 'Recompensas de Referencia'

    def __str__(self):
        return f"{self.referrer_account_id} - {self.reward_type}"


class CampaignConfig(models.Model):
    """Tabla: campaign_configs"""

    id = models.UUIDField(primary_key=True, default=uuid.uuid4, editable=False)
    name = models.CharField(max_length=255)
    active = models.BooleanField(default=False)
    start_date = models.DateTimeField(null=True, blank=True)
    end_date = models.DateTimeField(null=True, blank=True)
    discount_percent = models.IntegerField(null=True, blank=True)
    special_offer = models.JSONField(null=True, blank=True)  # {"founding_members": true, ...}
    referral_bonus_multiplier = models.FloatField(default=1.0)
    max_free_users = models.IntegerField(null=True, blank=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        db_table = 'campaign_configs'
        verbose_name = 'Configuración de Campaña'
        verbose_name_plural = 'Configuraciones de Campaña'

    def __str__(self):
        return self.name

    def is_active(self):
        """Verificar si la campaña está activa ahora"""
        now = timezone.now()
        return (
            self.active and
            (self.start_date is None or self.start_date <= now) and
            (self.end_date is None or self.end_date >= now)
        )


class PlanFeature(models.Model):
    """Tabla: plan_features"""

    id = models.UUIDField(primary_key=True, default=uuid.uuid4, editable=False)
    membership_type_id = models.UUIDField()  # FK a membership_types
    feature_key = models.CharField(max_length=100)
    feature_limit = models.CharField(max_length=50, default='unlimited')
    created_at = models.DateTimeField(auto_now_add=True)

    class Meta:
        db_table = 'plan_features'
        verbose_name = 'Feature del Plan'
        verbose_name_plural = 'Features del Plan'
        unique_together = ('membership_type_id', 'feature_key')

    def __str__(self):
        return f"{self.membership_type_id} - {self.feature_key}"


class FeatureAccessLog(models.Model):
    """Tabla: feature_access_log"""

    STATUS_CHOICES = [
        ('allowed', 'Permitido'),
        ('denied', 'Denegado'),
        ('limit_exceeded', 'Límite Excedido'),
    ]

    id = models.UUIDField(primary_key=True, default=uuid.uuid4, editable=False)
    account_id = models.UUIDField()  # FK a accounts
    feature_key = models.CharField(max_length=100)
    status = models.CharField(max_length=20, choices=STATUS_CHOICES)
    created_at = models.DateTimeField(auto_now_add=True)

    class Meta:
        db_table = 'feature_access_log'
        verbose_name = 'Log de Acceso a Feature'
        verbose_name_plural = 'Logs de Acceso a Features'
        ordering = ['-created_at']

    def __str__(self):
        return f"{self.account_id} - {self.feature_key} - {self.status}"
