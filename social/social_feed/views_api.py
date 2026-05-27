# social_feed/views_api.py - APIs para likes y comentarios
from django.http import JsonResponse
from django.views.decorators.http import require_http_methods
from django.contrib.auth.decorators import login_required
import json

from .models import Post, PostReaction, Comment


@login_required
@require_http_methods(["POST"])
def like_photo_api(request, photo_id):
    """POST /api/photo/<photo_id>/like/ - Like/Unlike a una foto"""
    try:
        data = json.loads(request.body) if request.body else {}
        action = data.get('action', 'like')
        
        try:
            post = Post.objects.get(image=photo_id)
        except Post.DoesNotExist:
            return JsonResponse({
                'success': False,
                'message': 'Foto no encontrada',
                'data': {}
            }, status=404)
        
        reaction = PostReaction.objects.filter(
            user=request.user,
            post=post,
            reaction_type='LIKE'
        ).first()
        
        if action == 'like' and not reaction:
            PostReaction.objects.create(
                user=request.user,
                post=post,
                reaction_type='LIKE'
            )
            likes_count = PostReaction.objects.filter(
                post=post,
                reaction_type='LIKE'
            ).count()
            return JsonResponse({
                'success': True,
                'message': 'Like agregado',
                'data': {'likes_count': likes_count, 'liked': True}
            })
        
        elif action == 'unlike' and reaction:
            reaction.delete()
            likes_count = PostReaction.objects.filter(
                post=post,
                reaction_type='LIKE'
            ).count()
            return JsonResponse({
                'success': True,
                'message': 'Like removido',
                'data': {'likes_count': likes_count, 'liked': False}
            })
        
        else:
            return JsonResponse({
                'success': False,
                'message': 'Accion no valida',
                'data': {}
            }, status=400)
    
    except json.JSONDecodeError:
        return JsonResponse({
            'success': False,
            'message': 'JSON invalido',
            'data': {}
        }, status=400)
    except Exception as e:
        return JsonResponse({
            'success': False,
            'message': f'Error: {str(e)}',
            'data': {}
        }, status=500)


@login_required
@require_http_methods(["POST"])
def comment_photo_api(request, photo_id):
    """POST /api/photo/<photo_id>/comment/ - Agregar comentario a una foto"""
    try:
        data = json.loads(request.body)
        text = data.get('text', '').strip()
        
        if not text or len(text) > 500:
            return JsonResponse({
                'success': False,
                'message': 'Comentario vacio o muy largo',
                'data': {}
            }, status=400)
        
        try:
            post = Post.objects.get(image=photo_id)
        except Post.DoesNotExist:
            return JsonResponse({
                'success': False,
                'message': 'Foto no encontrada',
                'data': {}
            }, status=404)
        
        comment = Comment.objects.create(
            author=request.user,
            post=post,
            content=text
        )
        
        comments_count = Comment.objects.filter(post=post).count()
        
        return JsonResponse({
            'success': True,
            'message': 'Comentario publicado',
            'data': {
                'comment_id': str(comment.id),
                'author': request.user.username,
                'text': text,
                'comments_count': comments_count,
                'created_at': comment.created_at.isoformat()
            }
        })
    
    except json.JSONDecodeError:
        return JsonResponse({
            'success': False,
            'message': 'JSON invalido',
            'data': {}
        }, status=400)
    except Exception as e:
        return JsonResponse({
            'success': False,
            'message': f'Error: {str(e)}',
            'data': {}
        }, status=500)


@login_required
@require_http_methods(["GET"])
def get_photo_comments_api(request, photo_id):
    """GET /api/photo/<photo_id>/comments/ - Obtener comentarios de una foto"""
    try:
        try:
            post = Post.objects.get(image=photo_id)
        except Post.DoesNotExist:
            return JsonResponse({
                'success': False,
                'message': 'Foto no encontrada',
                'data': {}
            }, status=404)
        
        comments = Comment.objects.filter(post=post).values(
            'id', 'author__username', 'content', 'created_at'
        ).order_by('-created_at')
        
        comments_list = [
            {
                'id': str(c['id']),
                'author': c['author__username'],
                'text': c['content'],
                'created_at': c['created_at'].isoformat()
            }
            for c in comments
        ]
        
        return JsonResponse({
            'success': True,
            'message': 'Comentarios obtenidos',
            'data': {
                'comments': comments_list,
                'count': len(comments_list)
            }
        })
    
    except Exception as e:
        return JsonResponse({
            'success': False,
            'message': f'Error: {str(e)}',
            'data': {}
        }, status=500)


@login_required
@require_http_methods(["GET"])
def get_photo_likes_count_api(request, photo_id):
    """GET /api/photo/<photo_id>/likes/count/ - Obtener cantidad de likes"""
    try:
        try:
            post = Post.objects.get(image=photo_id)
        except Post.DoesNotExist:
            return JsonResponse({
                'success': False,
                'message': 'Foto no encontrada',
                'data': {}
            }, status=404)
        
        likes_count = PostReaction.objects.filter(
            post=post,
            reaction_type='LIKE'
        ).count()
        
        user_liked = PostReaction.objects.filter(
            user=request.user,
            post=post,
            reaction_type='LIKE'
        ).exists()
        
        return JsonResponse({
            'success': True,
            'message': 'Likes obtenidos',
            'data': {
                'likes_count': likes_count,
                'user_liked': user_liked
            }
        })
    
    except Exception as e:
        return JsonResponse({
            'success': False,
            'message': f'Error: {str(e)}',
            'data': {}
        }, status=500)
