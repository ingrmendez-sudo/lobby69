from django.contrib.auth.models import AbstractUser
from django.db import models
from django.urls import reverse
from django.utils.translation import gettext_lazy as _


class User(AbstractUser):
    """Custom user model for Lobby69"""
    name = models.CharField(_("Name of User"), blank=True, max_length=255)
    first_name = None
    last_name = None

    def get_absolute_url(self) -> str:
        return reverse("users:detail", kwargs={"username": self.username})

    def __str__(self):
        return self.username


class GalleryAdmin(models.Model):
    """Modelo espejo para administrar fotos de Supabase desde Django"""
    supabase_id = models.CharField(max_length=36, primary_key=True)
    user_nick = models.CharField(max_length=255, null=True, blank=True)
    caption = models.TextField(blank=True, null=True)
    image_url = models.URLField()
    status = models.CharField(max_length=20, choices=[
        ('pending', 'Pendiente'),
        ('approved', 'Aprobada'),
        ('rejected', 'Rechazada'),
    ], default='pending')
    visibility = models.CharField(max_length=20, default='public')
    uploaded_at = models.DateTimeField(auto_now_add=True)

    class Meta:
        db_table = 'gallery_admin_mirror'
        verbose_name = 'Foto (Admin)'
        verbose_name_plural = 'Fotos (Admin)'

    def __str__(self):
        return f"{self.user_nick} - {self.caption or 'Sin título'}"
