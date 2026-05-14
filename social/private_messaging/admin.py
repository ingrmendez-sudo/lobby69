from django.contrib import admin
from .models import Conversation, Message

@admin.register(Conversation)
class ConversationAdmin(admin.ModelAdmin):
    list_display = ["id", "created_at", "last_message_at"]
    readonly_fields = ["created_at", "updated_at"]

@admin.register(Message)
class MessageAdmin(admin.ModelAdmin):
    list_display = ["sender", "message_type", "is_read", "created_at"]
    list_filter = ["message_type", "is_read"]
    search_fields = ["sender__username", "content"]
    readonly_fields = ["created_at"]
