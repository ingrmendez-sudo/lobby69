from django.contrib import admin
from .models import AgeVerification

@admin.register(AgeVerification)
class AgeVerificationAdmin(admin.ModelAdmin):
    list_display = ["user", "method", "status", "consent_given", "created_at"]
    list_filter = ["status", "method"]
    search_fields = ["user__email", "declared_name"]
    readonly_fields = ["created_at", "updated_at", "consent_ip", "consent_at"]
    list_editable = ["status"]
