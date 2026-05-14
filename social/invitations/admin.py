from django.contrib import admin
from .models import Invitation, InvitationRequest

@admin.register(Invitation)
class InvitationAdmin(admin.ModelAdmin):
    list_display = ["code", "invited_by", "email", "status", "created_at", "expires_at"]
    list_filter = ["status"]
    search_fields = ["email", "invited_by__username"]
    readonly_fields = ["code", "created_at", "used_at"]

@admin.register(InvitationRequest)
class InvitationRequestAdmin(admin.ModelAdmin):
    list_display = ["full_name", "email", "profile_type", "status", "created_at"]
    list_filter = ["status", "profile_type"]
    search_fields = ["full_name", "email"]
    readonly_fields = ["created_at", "ip_address"]
    list_editable = ["status"]
    actions = ["approve_requests", "reject_requests"]

    def approve_requests(self, request, queryset):
        from django.utils import timezone
        queryset.update(status="APROBADA", reviewed_by=request.user, reviewed_at=timezone.now())
        self.message_user(request, f"{queryset.count()} solicitudes aprobadas.")
    approve_requests.short_description = "Aprobar solicitudes"

    def reject_requests(self, request, queryset):
        from django.utils import timezone
        queryset.update(status="RECHAZADA", reviewed_by=request.user, reviewed_at=timezone.now())
        self.message_user(request, f"{queryset.count()} solicitudes rechazadas.")
    reject_requests.short_description = "Rechazar solicitudes"
