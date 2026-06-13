@login_required(login_url='pages:login')
@require_http_methods(["POST"])
def create_review_view(request):
    """Create or update a review"""
    try:
        resource_type = request.POST.get('resource_type', '').strip()
        resource_id = request.POST.get('resource_id', '').strip()
        rating = int(request.POST.get('rating', 0))
        comment = request.POST.get('comment', '').strip()
        
        if not resource_type or not resource_id or rating < 1 or rating > 5:
            return JsonResponse({'success': False, 'error': 'Datos inválidos'}, status=400)
        
        try:
            user_profile = supabase.table('profiles').select('id').eq('user_id', request.user.id).single().execute().data
            user_uuid = user_profile.get('id')
        except:
            return JsonResponse({'success': False, 'error': 'Perfil no encontrado'}, status=400)
        
        review_data = {
            'user_id': user_uuid,
            'resource_type': resource_type,
            'resource_id': resource_id,
            'rating': rating,
            'comment': comment
        }
        
        try:
            existing = supabase.table('reviews').select('id').eq('user_id', user_uuid).eq('resource_id', resource_id).eq('resource_type', resource_type).single().execute().data
            supabase.table('reviews').update(review_data).eq('id', existing['id']).execute()
            message = 'Reseña actualizada'
        except:
            supabase.table('reviews').insert(review_data).execute()
            message = 'Reseña creada'
        
        return JsonResponse({'success': True, 'message': message})
    except Exception as e:
        print(f"[ERROR] create_review_view: {e}")
        return JsonResponse({'success': False, 'error': str(e)}, status=500)

@require_http_methods(["GET"])
def get_reviews_view(request, resource_type, resource_id):
    """Get reviews for a resource"""
    try:
        reviews = supabase.table('reviews').select('*').eq('resource_type', resource_type).eq('resource_id', resource_id).order('created_at', desc=True).execute().data
        
        for review in reviews:
            try:
                profile = supabase.table('profiles').select('username, avatar_url').eq('id', review['user_id']).single().execute().data
                review['username'] = profile.get('username', 'Anónimo')
                review['avatar_url'] = profile.get('avatar_url', '')
            except:
                review['username'] = 'Anónimo'
                review['avatar_url'] = ''
        
        avg_rating = sum([r['rating'] for r in reviews]) / len(reviews) if reviews else 0
        
        return JsonResponse({
            'success': True,
            'reviews': reviews,
            'average_rating': round(avg_rating, 1),
            'total_reviews': len(reviews)
        })
    except Exception as e:
        print(f"[ERROR] get_reviews_view: {e}")
        return JsonResponse({'success': False, 'error': str(e)}, status=500)

@login_required(login_url='pages:login')
@require_http_methods(["POST"])
def delete_review_view(request, review_id):
    """Delete a review"""
    try:
        review = supabase.table('reviews').select('user_id').eq('id', str(review_id)).single().execute().data
        
        try:
            user_profile = supabase.table('profiles').select('id').eq('user_id', request.user.id).single().execute().data
            user_uuid = user_profile.get('id')
        except:
            return JsonResponse({'success': False, 'error': 'No autorizado'}, status=403)
        
        if review['user_id'] != user_uuid:
            return JsonResponse({'success': False, 'error': 'No autorizado'}, status=403)
        
        supabase.table('reviews').delete().eq('id', str(review_id)).execute()
        return JsonResponse({'success': True, 'message': 'Reseña eliminada'})
    except Exception as e:
        print(f"[ERROR] delete_review_view: {e}")
        return JsonResponse({'success': False, 'error': str(e)}, status=500)
