"""
URL Configuration for Social Pages App
"""
from django.urls import path
from . import views

app_name = 'pages'

urlpatterns = [
    # ============================================================================
    # AUTHENTICATION URLS
    # ============================================================================
    path('solicitar-invitacion/', views.invitation_request_view, name='solicitar'),
    path('confirmacion/', views.invitation_confirmation_view, name='confirmacion'),
    path('activar/<str:token>/', views.activate_account_view, name='activate'),
    path('login/', views.login_view, name='login'),
    path('logout/', views.logout_view, name='logout'),

    # ============================================================================
    # DASHBOARD & MAIN URLS
    # ============================================================================
    path('', views.landing_view, name='landing'),
    path('dashboard/', views.dashboard_view, name='dashboard'),
    path('feed/', views.home_feed_view, name='home_feed'),
    path('age-gate/', views.age_gate_view, name='age_gate'),

    # ============================================================================
    # PROFILE URLS
    # ============================================================================
    path('mi-perfil/', views.my_profile_view, name='my_profile'),
    path('mi-perfil/editar/', views.edit_profile_view, name='edit_profile'),
    path('usuario/<str:nickname>/', views.profile_detail_view, name='user_profile'),
    path('solicitudes-amistad/', views.friend_requests_view, name='friend_requests'),
    path('solicitud/<uuid:friendship_id>/aceptar/', views.accept_friend_request_view, name='accept_friend_request'),
    path('solicitud/<uuid:friendship_id>/rechazar/', views.reject_friend_request_view, name='reject_friend_request'),
    path('notificaciones/', views.notifications_view, name='notifications'),
    path('notificacion/<uuid:notification_id>/leer/', views.mark_notification_as_read_view, name='mark_notification_read'),
    path('notificaciones/limpiar/', views.clear_notifications_view, name='clear_notifications'),

    # ============================================================================
    # MAIN FEATURE URLS
    # ============================================================================
    path('explorar/', views.explore_view, name='explore'),
    path('mensajes/', views.conversations_view, name='conversations'),
    path('galeria/', views.gallery_view, name='gallery'),
    path('galeria/foto/<uuid:photo_id>/', views.photo_detail_view, name='photo_detail'),
    path('galeria/eliminar/<uuid:media_id>/', views.delete_media_view, name='delete_media'),
    path('galeria/like/<uuid:photo_id>/', views.like_photo_view, name='like_photo'),
    path('galeria/comentar/<uuid:photo_id>/', views.comment_photo_view, name='comment_photo'),
    path('galeria/foto/<uuid:photo_id>/comentarios/', views.get_photo_comments_view, name='get_photo_comments'),
    path('galeria/cambiar-visibilidad/<uuid:photo_id>/', views.toggle_visibility_view, name='toggle_visibility'),
    path('galeria/guardar/<uuid:photo_id>/', views.save_post_view, name='save_post'),
    path('galeria/guardar/<uuid:photo_id>/contador/', views.get_saves_count_view, name='get_saves_count'),
    path('membresias/', views.memberships_view, name='memberships'),
    path('notificaciones/', views.notifications_view, name='notifications'),
    path('configuracion/', views.settings_view, name='settings'),

    # ============================================================================
    # POST/CONTENT URLS
    # ============================================================================
    path('crear-post/', views.create_post_view, name='create_post'),
    path('post/<int:post_id>/like/', views.like_post_view, name='like_post'),

    # ============================================================================
    # MEMBERSHIP & PAYMENT URLS
    # ============================================================================
    path('checkout/<int:plan_id>/', views.checkout_view, name='checkout'),
    path('verificacion/', views.verification_view, name='verification'),
    path('plan-diario/', views.daily_plan_view, name='daily_plan'),

    # ============================================================================
    # MODERATION & REPORTING URLS
    # ============================================================================
    path('reportar/', views.report_content_view, name='report_content'),
    path('admin/dashboard/', views.admin_dashboard_view, name='admin_dashboard'),
    path('admin/moderar/<int:post_id>/', views.admin_moderate_post_view, name='admin_moderate_post'),

    # ============================================================================
    # DYNAMIC PAGES
    # ============================================================================
    path('usuario/<str:nickname>/agregar-amigo/', views.add_friend_view, name='add_friend'),

    path('<str:template_name>/', views.dynamic_pages_view, name='dynamic_pages'),
]
