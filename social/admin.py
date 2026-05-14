from django.contrib import admin
from django.utils.html import format_html
from django.urls import reverse
from django.db.models import Q
from django.utils import timezone
from social.models import InvitationRequest
import hashlib

@admin.register(InvitationRequest)
class InvitationRequestAdmin(admin.ModelAdmin):
    list_display = ('nombre_completo', 'email', 'edad', 'estado_badge', 'tipo_perfil', 'created_at', 'acciones_rapidas')
    list_filter = ('status', 'created_at', 'tipo_perfil', 'estado')
    search_fields = ('nombre_completo', 'email', 'municipio')
    readonly_fields = ('id', 'created_at', 'updated_at', 'activated_at', 'activation_token', 'motivo_display', 'email_display')

    fieldsets = (
        ('📋 Información Personal', {
            'fields': ('id', 'nombre_completo', 'email_display', 'edad', 'pais')
        }),
        ('🌍 Ubicación', {
            'fields': ('estado', 'municipio')
        }),
        ('👤 Perfil', {
            'fields': ('tipo_perfil', 'motivo_display')
        }),
        ('✅ Aceptaciones', {
            'fields': ('terminos_aceptados', 'privacidad_aceptada')
        }),
        ('📊 Estado de Solicitud', {
            'fields': ('status', 'approved_by', 'rejection_reason'),
            'classes': ('wide',)
        }),
        ('🔐 Activación', {
            'fields': ('activation_token', 'activation_used', 'activated_at'),
            'classes': ('collapse',)
        }),
        ('📅 Auditoría', {
            'fields': ('created_at', 'updated_at'),
            'classes': ('collapse',)
        }),
    )

    actions = ['aprobar_y_enviar_email', 'rechazar_solicitud']

    def email_display(self, obj):
        return format_html(
            '<a href="mailto:{}">{}</a>',
            obj.email,
            obj.email
        )
    email_display.short_description = 'Email'

    def motivo_display(self, obj):
        return obj.motivo
    motivo_display.short_description = 'Motivo de Integración'

    def estado_badge(self, obj):
        colores = {
            'pending': '#f39c12',
            'approved': '#27ae60',
            'rejected': '#e74c3c'
        }
        etiquetas = {
            'pending': '⏳ Pendiente',
            'approved': '✅ Aprobado',
            'rejected': '❌ Rechazado'
        }
        color = colores.get(obj.status, '#95a5a6')
        etiqueta = etiquetas.get(obj.status, obj.status)

        return format_html(
            '<span style="background-color: {}; color: white; padding: 5px 15px; border-radius: 20px; font-weight: bold;">{}</span>',
            color,
            etiqueta
        )
    estado_badge.short_description = 'Estado'

    def acciones_rapidas(self, obj):
        if obj.status == 'pending':
            aprobar_url = f"?action=aprobar_y_enviar_email&ids={obj.id}"
            rechazar_url = f"?action=rechazar_solicitud&ids={obj.id}"

            return format_html(
                '<a class="button" href="{}" style="background-color: #27ae60;">✅ Aprobar</a> '
                '<a class="button" href="{}" style="background-color: #e74c3c;">❌ Rechazar</a>',
                aprobar_url,
                rechazar_url
            )
        elif obj.status == 'approved':
            return format_html(
                '<span style="color: #27ae60; font-weight: bold;">Aprobada - Enviando email...</span>'
            )
        else:
            return format_html(
                '<span style="color: #e74c3c; font-weight: bold;">Rechazada</span>'
            )
    acciones_rapidas.short_description = 'Acciones'

    def aprobar_y_enviar_email(self, request, queryset):
        """Aprobar solicitud y enviar email con link de activación"""
        contador = 0
        for solicitud in queryset.filter(status='pending'):
            try:
                # Generar token de activación
                token = hashlib.sha256(
                    f"{solicitud.id}{timezone.now()}".encode()
                ).hexdigest()

                solicitud.activation_token = token
                solicitud.status = 'approved'
                solicitud.approved_by = request.user
                solicitud.save()

                # Enviar email
                self.enviar_email_aprobacion(solicitud, request)
                contador += 1

            except Exception as e:
                self.message_user(
                    request,
                    f'Error al procesar {solicitud.email}: {str(e)}',
                    level=40  # ERROR
                )

        self.message_user(
            request,
            f'✅ {contador} solicitud(es) aprobada(s) y email(es) enviado(s)',
            level=25  # SUCCESS
        )
    aprobar_y_enviar_email.short_description = '✅ Aprobar y enviar email de activación'

    def rechazar_solicitud(self, request, queryset):
        """Rechazar solicitud"""
        actualizado = queryset.filter(status='pending').update(
            status='rejected',
            updated_at=timezone.now()
        )

        self.message_user(
            request,
            f'❌ {actualizado} solicitud(es) rechazada(s)',
            level=25  # SUCCESS
        )
    rechazar_solicitud.short_description = '❌ Rechazar solicitudes'

    def enviar_email_aprobacion(self, solicitud, request):
        """Enviar email con enlace de activación"""
        from django.core.mail import send_mail
        from django.conf import settings

        # Construir URL de activación
        activation_url = f"http://localhost:8000/activar/{solicitud.activation_token}/"

        asunto = "¡Tu solicitud de invitación ha sido APROBADA! 🎉 - CLUB LOBBY69"

        mensaje_html = f"""
        <html>
            <body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">
                <div style="max-width: 600px; margin: 0 auto; background-color: #fff; border-radius: 10px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <h1 style="color: #27ae60; text-align: center;">¡Bienvenido a CLUB LOBBY69! 🎉</h1>

                    <p style="color: #333; font-size: 16px;">Hola <strong>{solicitud.nombre_completo}</strong>,</p>

                    <p style="color: #666; font-size: 14px; line-height: 1.6;">
                        Nos complace informarte que tu solicitud de invitación a <strong>CLUB LOBBY69</strong> ha sido <strong style="color: #27ae60;">APROBADA</strong>.
                    </p>

                    <p style="color: #666; font-size: 14px; line-height: 1.6;">
                        Para completar tu registro y acceder al club, haz clic en el botón de abajo:
                    </p>

                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{activation_url}" style="background-color: #4682b4; color: #fff; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px; display: inline-block;">
                            Completar Registro
                        </a>
                    </div>

                    <p style="color: #666; font-size: 14px; line-height: 1.6;">
                        O copia y pega este enlace en tu navegador:
                    </p>
                    <p style="color: #4682b4; font-size: 12px; word-break: break-all; background-color: #f9f9f9; padding: 10px; border-radius: 5px;">
                        {activation_url}
                    </p>

                    <p style="color: #999; font-size: 12px; line-height: 1.6; margin-top: 20px;">
                        <strong>Nota importante:</strong> Este enlace es válido por 30 días. Si no lo utilizas en ese período, deberás solicitar una nueva invitación.
                    </p>

                    <div style="border-top: 1px solid #ddd; margin-top: 20px; padding-top: 20px; text-align: center; color: #999; font-size: 12px;">
                        <p>CLUB LOBBY69 | Comunidad Swinger Exclusiva en México</p>
                        <p>Email: admin@lobby69.com</p>
                    </div>
                </div>
            </body>
        </html>
        """

        try:
            send_mail(
                asunto,
                f"Hola {solicitud.nombre_completo}, tu solicitud ha sido aprobada. Haz clic aquí para completar tu registro: {activation_url}",
                settings.DEFAULT_FROM_EMAIL,
                [solicitud.email],
                html_message=mensaje_html,
                fail_silently=False
            )
        except Exception as e:
            print(f"Error enviando email a {solicitud.email}: {str(e)}")

#from social.users.models import GalleryAdmin  # Asegúrate de que exista este modelo

