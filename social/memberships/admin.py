from django.contrib import admin
from .models import MembershipPlan, UserMembership

@admin.register(MembershipPlan)
class MembershipPlanAdmin(admin.ModelAdmin):
    list_display = ["name", "plan_type", "price_mxn_monthly", "max_photos", "is_active"]
    list_editable = ["price_mxn_monthly", "is_active"]
    ordering = ["sort_order"]

@admin.register(UserMembership)
class UserMembershipAdmin(admin.ModelAdmin):
    list_display = ["user", "plan", "status", "starts_at", "expires_at", "auto_renew"]
    list_filter = ["status", "plan"]
    search_fields = ["user__email", "user__username"]
    readonly_fields = ["created_at", "updated_at"]
