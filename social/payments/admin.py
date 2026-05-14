from django.contrib import admin
from .models import Payment

@admin.register(Payment)
class PaymentAdmin(admin.ModelAdmin):
    list_display = ["reference", "user", "plan", "amount_mxn", "method", "status", "created_at"]
    list_filter = ["status", "method"]
    search_fields = ["user__email", "reference"]
    readonly_fields = ["reference", "created_at", "updated_at"]
    list_editable = ["status"]
    actions = ["verify_payment"]

    def verify_payment(self, request, queryset):
        from django.utils import timezone
        queryset.update(
            status="COMPLETADO",
            receipt_verified=True,
            receipt_verified_by=request.user,
            receipt_verified_at=timezone.now()
        )
        self.message_user(request, f"{queryset.count()} pagos verificados.")
    verify_payment.short_description = "Verificar y completar pagos"
