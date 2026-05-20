"""
api_utils.py - Utilidades centralizadas para APIs
Manejo de Supabase, validaciones y respuestas JSON
"""
from django.http import JsonResponse
from django.conf import settings
from supabase import create_client
import json
import logging

logger = logging.getLogger(__name__)

def get_supabase():
    """Obtener cliente de Supabase"""
    try:
        return create_client(settings.SUPABASE_URL, settings.SUPABASE_KEY)
    except Exception as e:
        logger.error(f"Error conectando a Supabase: {e}")
        return None

def api_response(success, message, data=None, status_code=200):
    """Respuesta JSON estándar para APIs"""
    response = {
        'success': success,
        'message': message,
    }
    if data:
        response.update(data)
    return JsonResponse(response, status=status_code)

# =====================
# PROFILE LIKES
# =====================

def like_profile(user_id, profile_id):
    """Toggle like en perfil"""
    try:
        supabase = get_supabase()
        if not supabase:
            return False, None, "Error de conexión"
        
        # Verificar si existe
        existing = supabase.table('profile_likes').select('*').eq(
            'user_id', str(user_id)
        ).eq('profile_id', str(profile_id)).execute()
        
        if existing.data:
            # Unlike
            supabase.table('profile_likes').delete().eq(
                'user_id', str(user_id)
            ).eq('profile_id', str(profile_id)).execute()
            logger.info(f"User {user_id} unliked profile {profile_id}")
            return True, False, "Like removido"
        else:
            # Like
            supabase.table('profile_likes').insert({
                'user_id': str(user_id),
                'profile_id': str(profile_id)
            }).execute()
            logger.info(f"User {user_id} liked profile {profile_id}")
            return True, True, "Like agregado"
    
    except Exception as e:
        logger.error(f"Error en like_profile: {e}")
        return False, None, f"Error: {str(e)}"

# =====================
# FRIEND REQUESTS
# =====================

def send_friend_request(from_user_id, to_user_id):
    """Enviar solicitud de amistad"""
    try:
        supabase = get_supabase()
        if not supabase:
            return False, "Error de conexión"
        
        # Verificar si ya existe
        existing = supabase.table('friend_requests').select('*').eq(
            'from_user_id', str(from_user_id)
        ).eq('to_user_id', str(to_user_id)).eq(
            'status', 'pending'
        ).execute()
        
        if existing.data:
            return False, "Ya enviaste solicitud a este usuario"
        
        # Crear solicitud
        supabase.table('friend_requests').insert({
            'from_user_id': str(from_user_id),
            'to_user_id': str(to_user_id),
            'status': 'pending'
        }).execute()
        
        logger.info(f"Friend request from {from_user_id} to {to_user_id}")
        return True, "Solicitud de amistad enviada"
    
    except Exception as e:
        logger.error(f"Error en send_friend_request: {e}")
        return False, f"Error: {str(e)}"

# =====================
# PHOTO LIKES
# =====================

def like_photo(user_id, photo_id):
    """Toggle like en foto"""
    try:
        supabase = get_supabase()
        if not supabase:
            return False, None, "Error de conexión"
        
        existing = supabase.table('photo_likes').select('*').eq(
            'user_id', str(user_id)
        ).eq('photo_id', str(photo_id)).execute()
        
        if existing.data:
            supabase.table('photo_likes').delete().eq(
                'user_id', str(user_id)
            ).eq('photo_id', str(photo_id)).execute()
            return True, False, "Like removido"
        else:
            supabase.table('photo_likes').insert({
                'user_id': str(user_id),
                'photo_id': str(photo_id)
            }).execute()
            return True, True, "Like agregado"
    
    except Exception as e:
        logger.error(f"Error en like_photo: {e}")
        return False, None, f"Error: {str(e)}"

def get_photo_likes_count(photo_id):
    """Obtener cantidad de likes de una foto"""
    try:
        supabase = get_supabase()
        result = supabase.table('photo_likes').select('*', count='exact').eq(
            'photo_id', str(photo_id)
        ).execute()
        return result.count or 0
    except Exception as e:
        logger.error(f"Error obteniendo likes: {e}")
        return 0

# =====================
# POST REACTIONS
# =====================

def react_to_post(user_id, post_id, reaction_type='LIKE'):
    """Agregar reacción a post"""
    try:
        supabase = get_supabase()
        if not supabase:
            return False, "Error de conexión"
        
        # Verificar si existe reacción
        existing = supabase.table('post_reactions').select('*').eq(
            'user_id', str(user_id)
        ).eq('post_id', str(post_id)).execute()
        
        if existing.data:
            # Eliminar reacción existente
            supabase.table('post_reactions').delete().eq(
                'user_id', str(user_id)
            ).eq('post_id', str(post_id)).execute()
            return True, "Reacción removida"
        else:
            # Agregar reacción
            supabase.table('post_reactions').insert({
                'user_id': str(user_id),
                'post_id': str(post_id),
                'reaction_type': reaction_type
            }).execute()
            return True, "Reacción agregada"
    
    except Exception as e:
        logger.error(f"Error en react_to_post: {e}")
        return False, f"Error: {str(e)}"

# =====================
# COMMENTS
# =====================

def add_comment(user_id, post_id, content):
    """Agregar comentario a post"""
    try:
        supabase = get_supabase()
        if not supabase:
            return False, "Error de conexión", None
        
        # Validar contenido
        if not content or len(content.strip()) < 1:
            return False, "Comentario vacío", None
        
        # Crear comentario
        result = supabase.table('comments').insert({
            'user_id': str(user_id),
            'post_id': str(post_id),
            'content': content.strip()
        }).execute()
        
        if result.data:
            logger.info(f"Comment added by {user_id} on post {post_id}")
            return True, "Comentario agregado", result.data[0]
        else:
            return False, "No se pudo crear el comentario", None
    
    except Exception as e:
        logger.error(f"Error en add_comment: {e}")
        return False, f"Error: {str(e)}", None

# =====================
# BLOCK USER
# =====================

def block_user(user_id, blocked_user_id):
    """Bloquear usuario"""
    try:
        supabase = get_supabase()
        if not supabase:
            return False, "Error de conexión"
        
        # Verificar si ya está bloqueado
        existing = supabase.table('blocked_users').select('*').eq(
            'user_id', str(user_id)
        ).eq('blocked_user_id', str(blocked_user_id)).execute()
        
        if existing.data:
            return False, "Usuario ya está bloqueado"
        
        supabase.table('blocked_users').insert({
            'user_id': str(user_id),
            'blocked_user_id': str(blocked_user_id)
        }).execute()
        
        logger.info(f"User {user_id} blocked {blocked_user_id}")
        return True, "Usuario bloqueado"
    
    except Exception as e:
        logger.error(f"Error en block_user: {e}")
        return False, f"Error: {str(e)}"
