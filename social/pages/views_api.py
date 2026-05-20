"""
Views refactorizadas que usan api_utils.py
"""
from django.shortcuts import render, redirect
from django.contrib.auth.decorators import login_required
from django.views.decorators.http import require_http_methods
from django.http import JsonResponse
from django.conf import settings
from social.pages.api_utils import (
    api_response, like_profile, send_friend_request,
    like_photo, get_photo_likes_count, react_to_post,
    add_comment, block_user, get_supabase
)
import json
import logging

logger = logging.getLogger(__name__)

# =====================
# PROFILE ACTIONS
# =====================

@login_required(login_url='pages:login')
@require_http_methods(["POST"])
def like_profile_view(request, profile_id):
    """Like/Unlike perfil"""
    try:
        user_id = request.user.id
        success, liked, message = like_profile(user_id, profile_id)
        
        if success:
            return api_response(True, message, {'liked': liked})
        else:
            return api_response(False, message, status_code=400)
    
    except Exception as e:
        logger.error(f"Error en like_profile_view: {e}")
        return api_response(False, str(e), status_code=500)

@login_required(login_url='pages:login')
@require_http_methods(["POST"])
def add_friend_view(request, nickname):
    """Agregar usuario como amigo"""
    try:
        supabase = get_supabase()
        if not supabase:
            return api_response(False, "Error de conexión", status_code=500)
        
        # Obtener ID del usuario target
        target_resp = supabase.table('profiles').select('id').eq('nick', nickname).execute()
        if not target_resp.data:
            return api_response(False, "Perfil no encontrado", status_code=404)
        
        target_id = target_resp.data[0]['id']
        user_id = request.user.id
        
        # Enviar solicitud
        success, message = send_friend_request(user_id, target_id)
        
        if success:
            return api_response(True, message)
        else:
            return api_response(False, message, status_code=400)
    
    except Exception as e:
        logger.error(f"Error en add_friend_view: {e}")
        return api_response(False, str(e), status_code=500)

# =====================
# PHOTO ACTIONS
# =====================

@login_required(login_url='pages:login')
@require_http_methods(["POST"])
def like_photo_view(request, photo_id):
    """Like/Unlike foto"""
    try:
        user_id = request.user.id
        success, liked, message = like_photo(user_id, photo_id)
        
        if success:
            likes_count = get_photo_likes_count(photo_id)
            return api_response(True, message, {
                'liked': liked,
                'likes_count': likes_count
            })
        else:
            return api_response(False, message, status_code=400)
    
    except Exception as e:
        logger.error(f"Error en like_photo_view: {e}")
        return api_response(False, str(e), status_code=500)

@login_required(login_url='pages:login')
@require_http_methods(["POST"])
def comment_photo_view(request, photo_id):
    """Agregar comentario a foto"""
    try:
        comment_text = request.POST.get('comment_text', '').strip()
        
        if not comment_text or len(comment_text) < 2:
            return api_response(False, "El comentario debe tener al menos 2 caracteres", status_code=400)
        
        if len(comment_text) > 500:
            return api_response(False, "El comentario no puede exceder 500 caracteres", status_code=400)
        
        user_id = request.user.id
        success, message, comment_data = add_comment(user_id, photo_id, comment_text)
        
        if success:
            return api_response(True, message, {'comment': comment_data})
        else:
            return api_response(False, message, status_code=400)
    
    except Exception as e:
        logger.error(f"Error en comment_photo_view: {e}")
        return api_response(False, str(e), status_code=500)

@login_required(login_url='pages:login')
@require_http_methods(["POST"])
def delete_media_view(request, media_id):
    """Eliminar foto/media"""
    try:
        supabase = get_supabase()
        if not supabase:
            return api_response(False, "Error de conexión", status_code=500)
        
        # Verificar que sea dueño
        media_resp = supabase.table('gallery').select('account_id').eq('id', str(media_id)).execute()
        
        if not media_resp.data:
            return api_response(False, "Foto no encontrada", status_code=404)
        
        if str(media_resp.data[0]['account_id']) != str(request.user.id):
            return api_response(False, "No tienes permisos", status_code=403)
        
        # Eliminar
        supabase.table('gallery').delete().eq('id', str(media_id)).execute()
        logger.info(f"Foto {media_id} eliminada por {request.user.id}")
        
        return api_response(True, "Foto eliminada")
    
    except Exception as e:
        logger.error(f"Error en delete_media_view: {e}")
        return api_response(False, str(e), status_code=500)

# =====================
# POST ACTIONS
# =====================

@login_required(login_url='pages:login')
@require_http_methods(["POST"])
def like_post_view(request, post_id):
    """Like/Unlike post"""
    try:
        user_id = request.user.id
        success, message = react_to_post(user_id, post_id, 'LIKE')
        
        if success:
            return api_response(True, message)
        else:
            return api_response(False, message, status_code=400)
    
    except Exception as e:
        logger.error(f"Error en like_post_view: {e}")
        return api_response(False, str(e), status_code=500)

# =====================
# USER BLOCK
# =====================

@login_required(login_url='pages:login')
@require_http_methods(["POST"])
def block_user_view(request, user_id):
    """Bloquear usuario"""
    try:
        if str(user_id) == str(request.user.id):
            return api_response(False, "No puedes bloquearte a ti mismo", status_code=400)
        
        success, message = block_user(request.user.id, user_id)
        
        if success:
            return api_response(True, message)
        else:
            return api_response(False, message, status_code=400)
    
    except Exception as e:
        logger.error(f"Error en block_user_view: {e}")
        return api_response(False, str(e), status_code=500)
