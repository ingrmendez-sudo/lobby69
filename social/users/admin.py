from allauth.account.decorators import secure_admin_login
from django.conf import settings
from django.contrib import admin
from django.contrib.auth import admin as auth_admin
from django.utils.translation import gettext_lazy as _
from django.utils.html import format_html
from .models import User, GalleryAdmin
from .forms import UserAdminChangeForm, UserAdminCreationForm

if settings.DJANGO_ADMIN_FORCE_ALLAUTH:
    admin.autodiscover()
    admin.site.login = secure_admin_login(admin.site.login)

@admin.register(User)
class UserAdmin(auth_admin.UserAdmin):
    form = UserAdminChangeForm
    add_form = UserAdminCreationForm
    fieldsets = (
        (None, {"fields": ("username", "password")}),
        (_("Personal info"), {"fields": ("name", "email")}),
        (
            _("Permissions"),
            {
                "fields": (
                    "is_active",
                    "is_staff",
                    "is_superuser",
                    "groups",
                    "user_permissions",
                ),
            },
        ),
        (_("Important dates"), {"fields": ("last_login", "date_joined")}),
    )
    list_display = ["username", "name", "is_superuser"]
    search_fields = ["name"]

admin.site.site_header = "LOBBY69"
admin.site.site_title = "Admin LOBBY69"
admin.site.enable_nav_sidebar = True

@admin.register(GalleryAdmin)
class GalleryAdminCustom(admin.ModelAdmin):
    list_display = ['thumbnail', 'user_nick', 'status', 'visibility', 'uploaded_at']
    list_filter = ['status', 'visibility', 'uploaded_at']
    search_fields = ['user_nick', 'caption']
    list_editable = ['status']
    readonly_fields = ['supabase_id', 'image_preview', 'caption', 'user_nick', 'visibility', 'uploaded_at']

    fieldsets = (
        ('Información', {
            'fields': ('supabase_id', 'user_nick', 'caption', 'image_preview')
        }),
        ('Estado', {
            'fields': ('status', 'visibility')
        }),
        ('Fecha', {
            'fields': ('uploaded_at',)
        }),
    )

    def thumbnail(self, obj):
        if obj.image_url:
            return format_html(
                '<img src="{}" width="50" height="50" style="border-radius: 4px;" />',
                obj.image_url
            )
        return '-'
    thumbnail.short_description = 'Foto'

    def image_preview(self, obj):
        if obj.image_url:
            return format_html(
                '<img src="{}" width="300" style="border-radius: 4px;" />',
                obj.image_url
            )
        return '-'
    image_preview.short_description = 'Vista previa'

    def get_queryset(self, request):
        """Sincronizar con Supabase cada vez que se carga la lista"""
        from supabase import create_client

        supabase = create_client(settings.SUPABASE_URL, settings.SUPABASE_KEY)

        # Obtener fotos pendientes de Supabase
        resp = supabase.table('gallery').select('*').eq('status', 'pending').order('uploaded_at', desc=True).execute()

        # Limpiar tabla mirror
        GalleryAdmin.objects.all().delete()

        # Crear registros espejo
        if resp.data:
            for photo in resp.data:
                GalleryAdmin.objects.create(
                    supabase_id=photo['id'],
                    user_nick=photo.get('user_nick'),
                    caption=photo.get('caption'),
                    image_url=photo['image_url'],
                    status=photo.get('status', 'pending'),
                    visibility=photo.get('visibility', 'public'),
                    uploaded_at=photo.get('uploaded_at')
                )

        return super().get_queryset(request)

    def save_model(self, request, obj, form, change):
        """Guardar cambios en Supabase"""
        from supabase import create_client

        supabase = create_client(settings.SUPABASE_URL, settings.SUPABASE_KEY)

        # Actualizar en Supabase
        supabase.table('gallery').update({
            'status': obj.status,
            'visibility': obj.visibility
        }).eq('id', obj.supabase_id).execute()

        super().save_model(request, obj, form, change)

