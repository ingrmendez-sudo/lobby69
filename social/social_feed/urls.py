# social_feed/urls.py - URLs para APIs de likes y comentarios
from django.urls import path
from . import views_api

app_name = 'social_feed_api'

urlpatterns = [
    # APIs de likes y comentarios
    path('api/photo/<str:photo_id>/like/', views_api.like_photo_api, name='like_photo'),
    path('api/photo/<str:photo_id>/comment/', views_api.comment_photo_api, name='comment_photo'),
    path('api/photo/<str:photo_id>/comments/', views_api.get_photo_comments_api, name='get_comments'),
    path('api/photo/<str:photo_id>/likes/count/', views_api.get_photo_likes_count_api, name='likes_count'),
]
