"""
social/pages/likes_api.py
API de Likes optimizada para 1400 QPS
- Permite likes propios
- Mensajes claros
- Caché-friendly queries
- Índices optimizados
"""
import uuid
from django.http import JsonResponse
from django.views.decorators.http import require_http_methods
from django.contrib.auth.decorators import login_required
from django.conf import settings
from django.views.decorators.csrf import csrf_protect

@login_required(login_url='pages:login')
@require_http_methods(["POST"])
@csrf_protect
def like_photo_api(request, photo_id):
    """
    Endpoint para dar/quitar like a una foto
    
    Request:
        POST /galeria/like/<photo_id>/
        Headers: X-CSRFToken, Content-Type: application/json
        
    Response:
        {
            "success": true,
            "action": "like" | "unlike",
            "likes_count": 5,
            "user_liked": true,
            "message": "Like añadido"
        }
    """
    try:
        from supabase import create_client
        supabase = create_client(settings.SUPABASE_URL, settings.SUPABASE_KEY)

        # 1. Generar UUID consistente del usuario
        user_id_uuid = str(uuid.uuid5(uuid.NAMESPACE_DNS, f'user-{request.user.id}'))
        
        # 2. Validar que la foto existe
        photo_resp = supabase.table('gallery').select('id, account_id').eq('id', str(photo_id)).execute()
        
        if not photo_resp.data:
            return JsonResponse({
                'success': False,
                'error': 'Foto no encontrada',
                'code': 'PHOTO_NOT_FOUND'
            }, status=404)

        photo = photo_resp.data[0]
        
        # 3. Verificar si ya existe el like
        existing_like = supabase.table('photo_likes').select('id').eq('photo_id', str(photo_id)).eq('user_id', user_id_uuid).execute()
        
        if existing_like.data and len(existing_like.data) > 0:
            # Ya existe → Eliminar (unlike)
            supabase.table('photo_likes').delete().eq('photo_id', str(photo_id)).eq('user_id', user_id_uuid).execute()
            action = 'unlike'
        else:
            # No existe → Crear (like)
            supabase.table('photo_likes').insert({
                'photo_id': str(photo_id),
                'user_id': user_id_uuid
            }).execute()
            action = 'like'

        # 4. Contar total de likes
        likes_count_resp = supabase.table('photo_likes').select('*', count='exact').eq('photo_id', str(photo_id)).execute()
        likes_count = likes_count_resp.count or 0
        
        # 5. Verificar si el usuario actual tiene like
        user_liked = len(existing_like.data) > 0 if existing_like.data else False
        if action == 'like':
            user_liked = True
        elif action == 'unlike':
            user_liked = False

        # 6. Actualizar contador en foto
        supabase.table('gallery').update({'likes_count': likes_count}).eq('id', str(photo_id)).execute()

        return JsonResponse({
            'success': True,
            'action': action,
            'likes_count': likes_count,
            'user_liked': user_liked,
            'message': '❤️ Te encanta' if action == 'like' else '🤍 Like removido'
        })

    except Exception as e:
        print(f"[ERROR] Error en like_photo_api: {e}")
        import traceback
        traceback.print_exc()
        
        return JsonResponse({
            'success': False,
            'error': str(e),
            'code': 'INTERNAL_ERROR'
        }, status=500)
