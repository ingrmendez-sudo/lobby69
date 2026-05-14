from django.contrib import admin
from .models import Post, PostReaction, Comment, Story, DailyPlan

@admin.register(Post)
class PostAdmin(admin.ModelAdmin):
    list_display = ["author", "post_type", "visibility", "is_approved", "is_nsfw", "likes_count", "created_at"]
    list_filter = ["post_type", "visibility", "is_approved", "is_nsfw"]
    search_fields = ["author__username", "content"]
    list_editable = ["is_approved", "is_nsfw"]
    readonly_fields = ["created_at", "updated_at", "likes_count", "comments_count"]
    actions = ["approve_posts"]

    def approve_posts(self, request, queryset):
        from django.utils import timezone
        queryset.update(is_approved=True, moderated_by=request.user, moderated_at=timezone.now())
        self.message_user(request, f"{queryset.count()} publicaciones aprobadas.")
    approve_posts.short_description = "Aprobar publicaciones"

@admin.register(DailyPlan)
class DailyPlanAdmin(admin.ModelAdmin):
    list_display = ["title", "plan_type", "date", "start_time", "is_members_only"]
    list_filter = ["plan_type", "is_members_only"]
    ordering = ["-date"]
