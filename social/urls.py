"""
social/urls.py - URL Configuration Principal
"""
from django.contrib import admin
from django.urls import path, include
from django.conf import settings
from django.conf.urls.static import static

urlpatterns = [
    path('admin/', admin.site.urls),
    
    # Apps principales
    path('', include('social.pages.urls')),
    path('', include('social.users.urls')),
    path('', include('social.social_feed.urls')),
    path('', include('social.invitations.urls')),
    path('', include('social.memberships.urls')),
    path('', include('social.payments.urls')),
    path('', include('social.moderation.urls')),
    path('', include('social.verification.urls')),
    path('', include('social.media_content.urls')),
    path('', include('social.private_messaging.urls')),
    path('', include('social.geo.urls')),
]

# Media files en desarrollo
if settings.DEBUG:
    urlpatterns += static(settings.MEDIA_URL, document_root=settings.MEDIA_ROOT)
    urlpatterns += static(settings.STATIC_URL, document_root=settings.STATIC_ROOT)
