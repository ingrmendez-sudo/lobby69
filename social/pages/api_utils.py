"""
api_utils.py - Utilidades para APIs
Centralizar llamadas a Supabase y respuestas JSON
"""
from django.http import JsonResponse
from django.conf import settings
from supabase import create_client
import json

def get_supabase():
    """Obtener cliente de Supabase"""
    return create_client(settings.SUPABASE_URL, settings.SUPABASE_KEY)

def api_response(success, message, data=None, status_code=200):
    """Respuesta JSON estándar"""
    return JsonResponse({
        'success': success,
        'message': message,
        'data': data or {}
    }, status=status_code)

def like_profile_api(user_id, profile_id, action='like'):
    """Like/Unlike perfil en Supabase"""
    try:
        supabase = get_supabase()
        
        # Verificar si ya existe el like
        existing = supabase.table('profile_likes').select('*').eq(
            'user_id', str(user_id)
        ).eq('profile_id', str(profile_id)).execute()
        
        if existing.data:
            if action == 'unlike':
                # Eliminar like
                supabase.table('profile_likes').delete().eq(
                    'user_id', str(user_id)
                ).eq('profile_id', str(profile_id)).execute()
                return True, False, 'Like removido'
            else:
                return False, True, 'Ya habías dado like a este perfil'
        else:
            if action == 'like':
                # Crear like
                supabase.table('profile_likes').insert({
                    'user_id': str(user_id),
                    'profile_id': str(profile_id),
                    'created_at': 'now()'
                }).execute()
                return True, True, 'Like agregado'
            else:
                return False, False, 'No habías dado like a este perfil'
    
    except Exception as e:
        return False, None, f'Error: {str(e)}'

def add_friend_api(user_id, friend_id):
    """Agregar amigo"""
    try:
        supabase = get_supabase()
        
        # Verificar si ya existe solicitud
        existing = supabase.table('friend_requests').select('*').eq(
            'from_user_id', str(user_id)
        ).eq('to_user_id', str(friend_id)).execute()
        
        if existing.data:
            return False, None, 'Ya enviaste solicitud a este usuario'
        
        # Crear solicitud
        supabase.table('friend_requests').insert({
            'from_user_id': str(user_id),
            'to_user_id': str(friend_id),
            'status': 'pending',
            'created_at': 'now()'
        }).execute()
        
        return True, None, 'Solicitud de amistad enviada'
    
    except Exception as e:
        return False, None, f'Error: {str(e)}'

def like_photo_api(user_id, photo_id):
    """Like a foto"""
    try:
        supabase = get_supabase()
        
        existing = supabase.table('photo_likes').select('*').eq(
            'user_id', str(user_id)
        ).eq('photo_id', str(photo_id)).execute()
        
        if existing.data:
            # Unlike
            supabase.table('photo_likes').delete().eq(
                'user_id', str(user_id)
            ).eq('photo_id', str(photo_id)).execute()
            return True, False, 'Like removido'
        else:
            # Like
            supabase.table('photo_likes').insert({
                'user_id': str(user_id),
                'photo_id': str(photo_id),
                'created_at': 'now()'
            }).execute()
            return True, True, 'Like agregado'
    
    except Exception as e:
        return False, None, f'Error: {str(e)}'

def get_likes_count(photo_id):
    """Obtener cantidad de likes de una foto"""
    try:
        supabase = get_supabase()
        result = supabase.table('photo_likes').select('count', count='exact').eq(
            'photo_id', str(photo_id)
        ).execute()
        return result.count
    except:
        return 0
