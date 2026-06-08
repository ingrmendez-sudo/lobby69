"""
URL Configuration for Social Pages App
"""
from django.urls import path
from . import views
from .payment_views import (
    get_membership_plans_view,
    checkout_view,
    conekta_webhook_view,
    claim_referral_reward_view,
    payment_transactions_list_view
)
from .referral_views import (
    get_referral_code_view,
    list_referrals_view,
    get_referral_stats_view
)
from .admin_views import (
    admin_dashboard_view,
    list_campaigns_view,
    create_campaign_view,
    update_campaign_view,
    toggle_campaign_view,
    delete_campaign_view,
    app_settings_view,
    campaign_presets_view
)

app_name = 'pages'

urlpatterns = [
    # ============================================================================
    # ADMIN URLS (PRIORITY)
    # ============================================================================
    # Admin Hub (Main Entry Point)
    path('admin/', views.admin_hub_view, name='admin_hub'),
    path('admin/memberships/', views.admin_memberships_view, name='admin_memberships'),
    path('api/admin/membership-price/', views.update_membership_price_view, name='update_membership_price'),
    path('api/admin/membership-privilege/', views.update_membership_privilege_view, name='update_membership_privilege'),
    path('api/admin/moderate-content/', views.moderate_content_view, name='moderate_content'),
    path('api/admin/stats/', views.admin_stats_view, name='admin_stats'),

        path('admin/users/', views.admin_users_view, name='admin_users'),
    path('admin/users/<uuid:user_id>/', views.admin_user_detail_view, name='admin_user_detail'),
    path('admin/invitations/', views.admin_invitations_view, name='admin_invitations'),
    path('admin/invitations/<uuid:invitation_id>/', views.admin_invitation_detail_view, name='admin_invitation_detail'),
    path('api/admin/invitations/action/', views.admin_invitation_action_view, name='admin_invitation_action'),
    path('admin/support/', views.admin_support_view, name='admin_support'),
    path('admin/support/<uuid:ticket_id>/', views.admin_support_detail_view, name='admin_support_detail'),
    path('api/admin/support/action/', views.admin_support_action_view, name='admin_support_action'),
    path('admin/moderation/', views.admin_moderation_view, name='admin_moderation'),
    path('admin/moderation/<uuid:content_id>/', views.admin_moderation_detail_view, name='admin_moderation_detail'),
    path('api/admin/moderation/action/', views.admin_moderation_action_view, name='admin_moderation_action'),
    path('admin/reviews/', views.admin_reviews_view, name='admin_reviews'),
    path('admin/reviews/<uuid:review_id>/', views.admin_review_detail_view, name='admin_review_detail'),
    path('api/admin/reviews/action/', views.admin_review_action_view, name='admin_review_action'),
    path('admin/events/', views.admin_events_view, name='admin_events'),
    path('admin/events/<uuid:event_id>/', views.admin_event_detail_view, name='admin_event_detail'),
    path('api/admin/events/action/', views.admin_event_action_view, name='admin_event_action'),
        path('admin/news/create/', views.admin_news_detail_view, name='admin_news_create'),
path('admin/news/', views.admin_news_view, name='admin_news'),
    path('admin/news/<uuid:news_id>/', views.admin_news_detail_view, name='admin_news_detail'),
    path('api/admin/news/action/', views.admin_news_action_view, name='admin_news_action'),
    path('admin/analytics/', views.admin_analytics_view, name='admin_analytics'),
    path('api/admin/analytics/export/', views.admin_analytics_export_view, name='admin_analytics_export'),
    path('api/admin/users/action/', views.admin_user_action_view, name='admin_user_action'),

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
    path('mi-dashboard/', views.dashboard_view, name='dashboard'),
    path('feed/', views.home_feed_view, name='home_feed'),
    path('age-gate/', views.age_gate_view, name='age_gate'),

    # ============================================================================
    # PROFILE URLS
    # ============================================================================
    path('mi-perfil/', views.my_profile_view, name='my_profile'),
    path('mi-perfil/editar/', views.edit_profile_view, name='edit_profile'),
    path('usuario/<str:nickname>/', views.profile_detail_view, name='user_profile'),
    path('api/profile-like/<uuid:profile_id>/', views.like_profile_view, name='like_profile'),
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
    path('api/upload-photo/', views.gallery_view, name='upload_photo'),
    path('galeria/foto/<uuid:photo_id>/', views.photo_detail_view, name='photo_detail'),
    path('galeria/eliminar/<uuid:media_id>/', views.delete_media_view, name='delete_media'),
    path('galeria/like/<uuid:photo_id>/', views.like_photo_view, name='like_photo'),
    path('galeria/encantar/<uuid:photo_id>/', views.enchantment_photo_view, name='enchant_photo'),
    path('galeria/comentar/<uuid:photo_id>/', views.comment_photo_view, name='comment_photo'),
    path('galeria/foto/<uuid:photo_id>/comentarios/', views.get_photo_comments_view, name='get_photo_comments'),
    path('galeria/cambiar-visibilidad/<uuid:photo_id>/', views.toggle_visibility_view, name='toggle_visibility'),
    path('galeria/guardar/<uuid:photo_id>/', views.save_post_view, name='save_post'),
    path('galeria/guardar/<uuid:photo_id>/contador/', views.get_saves_count_view, name='get_saves_count'),
    path('membresias/', views.memberships_view, name='memberships'),
    path('configuracion/', views.settings_view, name='settings'),

    # ============================================================================
    # POST/CONTENT URLS
    # ============================================================================
    path('crear-post/', views.create_post_view, name='create_post'),
    path('post/<int:post_id>/like/', views.like_post_view, name='like_post'),

    # ============================================================================
    # MEMBERSHIP & PAYMENT URLS
    # ============================================================================
    path('api/membership-plans/', get_membership_plans_view, name='membership_plans'),
    path('api/checkout/', checkout_view, name='checkout'),
    path('webhook/conekta/', conekta_webhook_view, name='conekta_webhook'),
    path('api/claim-reward/<uuid:reward_id>/', claim_referral_reward_view, name='claim_reward'),
    path('api/payment-transactions/', payment_transactions_list_view, name='payment_transactions'),

    # ============================================================================
    # VERIFICATION URLS
    # ============================================================================
    path('verificacion/', views.verification_page_view, name='verification'),
    path('api/verification-status/', views.verification_status_view, name='verification_status'),
    path('api/upload-verification/', views.upload_verification_view, name='upload_verification'),

    # ============================================================================
    # REFERRAL URLS
    # ============================================================================
    path('api/referral-code/', get_referral_code_view, name='referral_code'),
    path('api/referrals/', list_referrals_view, name='list_referrals'),
    path('api/referral-stats/', get_referral_stats_view, name='referral_stats'),

    # ============================================================================
    # CAMPAIGN ADMIN URLS
    # ============================================================================
    path('admin/dashboard/', admin_dashboard_view, name='admin_dashboard_old'),
    path('admin/campaigns/', list_campaigns_view, name='admin_campaigns'),
    path('admin/campaigns/create/', create_campaign_view, name='create_campaign'),
    path('admin/campaigns/<uuid:campaign_id>/update/', update_campaign_view, name='update_campaign'),
    path('admin/campaigns/<uuid:campaign_id>/toggle/', toggle_campaign_view, name='toggle_campaign'),
    path('admin/campaigns/<uuid:campaign_id>/delete/', delete_campaign_view, name='delete_campaign'),
    path('admin/settings/', app_settings_view, name='app_settings'),
    path('admin/campaigns/presets/', campaign_presets_view, name='campaign_presets'),

    # ============================================================================
    # MODERATION & REPORTING URLS
    # ============================================================================
    path('reportar/', views.report_content_view, name='report_content'),

    # ============================================================================
    # DYNAMIC PAGES
    # ============================================================================
    path('usuario/<str:nickname>/agregar-amigo/', views.add_friend_view, name='add_friend'),
    path('<str:template_name>/', views.dynamic_pages_view, name='dynamic_pages'),
]







