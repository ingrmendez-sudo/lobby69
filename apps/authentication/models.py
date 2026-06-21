from django.db import models
from django.contrib.auth.models import AbstractBaseUser, BaseUserManager
from django.utils import timezone
import uuid

# ════════════════════════════════════════════════════════════════════════════
# ACCOUNT MODEL (Custom User)
# ════════════════════════════════════════════════════════════════════════════

class AccountManager(BaseUserManager):
    """Custom manager for Account model"""
    
    def create_user(self, email, password=None, **extra_fields):
        if not email:
            raise ValueError("Email is required")
        email = self.normalize_email(email)
        user = self.model(email=email, **extra_fields)
        if password:
            user.set_password(password)
        user.save(using=self._db)
        return user
    
    def create_superuser(self, email, password=None, **extra_fields):
        extra_fields.setdefault("is_staff", True)
        extra_fields.setdefault("is_superuser", True)
        extra_fields.setdefault("is_active", True)
        return self.create_user(email, password, **extra_fields)


class Account(AbstractBaseUser):
    """Custom user account model"""
    
    id = models.UUIDField(primary_key=True, default=uuid.uuid4, editable=False)
    email = models.EmailField(unique=True, max_length=255)
    first_name = models.CharField(max_length=150, blank=True)
    last_name = models.CharField(max_length=150, blank=True)
    is_active = models.BooleanField(default=False)
    is_staff = models.BooleanField(default=False)
    is_superuser = models.BooleanField(default=False)
    email_verified = models.BooleanField(default=False)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)
    deleted_at = models.DateTimeField(null=True, blank=True)
    
    objects = AccountManager()
    
    USERNAME_FIELD = "email"
    REQUIRED_FIELDS = ["first_name", "last_name"]
    
    class Meta:
        db_table = "accounts"
        ordering = ["-created_at"]
        verbose_name = "Cuenta"
        verbose_name_plural = "Cuentas"
    
    def __str__(self):
        return f"{self.email} ({self.first_name} {self.last_name})"


# ════════════════════════════════════════════════════════════════════════════
# INVITATION REQUEST MODEL
# ════════════════════════════════════════════════════════════════════════════

class InvitationRequest(models.Model):
    """Solicitud de invitación para nuevo usuario"""
    
    STATUS_CHOICES = [
        ("pending", "Pendiente"),
        ("approved", "Aprobado"),
        ("rejected", "Rechazado"),
    ]
    
    id = models.UUIDField(primary_key=True, default=uuid.uuid4, editable=False)
    first_name = models.CharField(max_length=150)
    last_name = models.CharField(max_length=150)
    email = models.EmailField(unique=True, max_length=255)
    birthdate = models.DateField()
    gender = models.CharField(max_length=10, choices=[
        ("M", "Masculino"),
        ("F", "Femenino"),
        ("NB", "No binario"),
        ("O", "Otro"),
    ])
    seeking = models.CharField(max_length=50, choices=[
        ("relacion_seria", "Relación seria"),
        ("amistad", "Amistad"),
        ("aventura", "Aventura"),
        ("sin_compromiso", "Sin compromiso"),
    ])
    interests = models.TextField(blank=True, null=True, max_length=500)
    terms_accepted = models.BooleanField(default=False)
    privacy_accepted = models.BooleanField(default=False)
    marketing_consent = models.BooleanField(default=False)
    status = models.CharField(max_length=20, choices=STATUS_CHOICES, default="pending")
    rejection_reason = models.TextField(blank=True, null=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)
    approved_at = models.DateTimeField(null=True, blank=True)
    ip_address = models.GenericIPAddressField(null=True, blank=True)
    user_agent = models.TextField(blank=True, null=True)
    
    class Meta:
        db_table = "invitation_requests"
        ordering = ["-created_at"]
        verbose_name = "Solicitud de Invitación"
        verbose_name_plural = "Solicitudes de Invitación"
    
    def __str__(self):
        return f"{self.first_name} {self.last_name} ({self.email})"
    
    def approve(self):
        """Approve the invitation request"""
        self.status = "approved"
        self.approved_at = timezone.now()
        self.save()
