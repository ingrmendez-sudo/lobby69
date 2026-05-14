"""
Modelos de Mensajería Privada LOBBY69
Conversaciones y mensajes entre miembros.
"""
from django.db import models
from django.conf import settings
from django.utils.translation import gettext_lazy as _


class Conversation(models.Model):
    """Conversación privada entre dos miembros."""

    participants = models.ManyToManyField(
        settings.AUTH_USER_MODEL,
        related_name="conversations",
        verbose_name=_("Participantes"),
    )
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)
    last_message_at = models.DateTimeField(null=True, blank=True)

    class Meta:
        verbose_name = _("Conversación")
        verbose_name_plural = _("Conversaciones")
        ordering = ["-last_message_at"]

    def __str__(self):
        names = " & ".join(p.username for p in self.participants.all()[:2])
        return f"Conversación: {names}"


class Message(models.Model):
    """Mensaje dentro de una conversación."""

    class MessageType(models.TextChoices):
        TEXT = "TEXTO", _("Texto")
        IMAGE = "IMAGEN", _("Imagen")
        AUDIO = "AUDIO", _("Audio")
        STICKER = "STICKER", _("Sticker")

    conversation = models.ForeignKey(
        Conversation,
        on_delete=models.CASCADE,
        related_name="messages",
        verbose_name=_("Conversación"),
    )
    sender = models.ForeignKey(
        settings.AUTH_USER_MODEL,
        on_delete=models.CASCADE,
        related_name="sent_messages",
        verbose_name=_("Remitente"),
    )
    message_type = models.CharField(
        max_length=10,
        choices=MessageType.choices,
        default=MessageType.TEXT,
    )
    content = models.TextField(blank=True, max_length=2000, verbose_name=_("Contenido"))
    image = models.ImageField(upload_to="messages/images/%Y/%m/", null=True, blank=True)
    audio = models.FileField(upload_to="messages/audio/%Y/%m/", null=True, blank=True)

    is_read = models.BooleanField(default=False, verbose_name=_("Leído"))
    read_at = models.DateTimeField(null=True, blank=True)
    is_deleted_sender = models.BooleanField(default=False)
    is_deleted_receiver = models.BooleanField(default=False)

    created_at = models.DateTimeField(auto_now_add=True)

    class Meta:
        verbose_name = _("Mensaje")
        verbose_name_plural = _("Mensajes")
        ordering = ["created_at"]

    def __str__(self):
        return f"{self.sender}: {self.content[:50]}"
