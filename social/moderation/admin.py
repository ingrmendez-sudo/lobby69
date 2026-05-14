from django.contrib import admin
from .models import Report, ModerationLog

@admin.register(Report)
class ReportAdmin(admin.ModelAdmin):
    list_display = ["reporter", "reason", "status", "created_at"]
    list_filter = ["reason", "status"]
    search_fields = ["reporter__email"]
    readonly_fields = ["created_at"]
    list_editable = ["status"]

@admin.register(ModerationLog)
class ModerationLogAdmin(admin.ModelAdmin):
    list_display = ["moderator", "action", "target_user", "created_at"]
    list_filter = ["action"]
    readonly_fields = ["created_at"]
