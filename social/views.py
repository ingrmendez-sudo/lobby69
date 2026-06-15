"""
FILE: social/views.py
VERSION: 1.2.0
DESCRIPCIÓN: Views actualizados con conexión a Supabase

CAMBIOS:
- ProfileDetailView: Obtener datos de Supabase
- ProfileEditView: Guardar cambios en Supabase
- Manejo de errores
"""

from django.shortcuts import render, redirect
from django.views import View
from django.contrib.auth.mixins import LoginRequiredMixin
from django.http import JsonResponse
from django.views.decorators.http import require_http_methods
from django.utils.decorators import method_decorator
import logging

from .services.supabase_service import supabase_service
from .forms import ProfileEditForm

logger = logging.getLogger(__name__)


class ProfileDetailView(LoginRequiredMixin, View):
    """
    Ver detalles del perfil del usuario

    MÉTODO: GET
    TEMPLATE: pages/my_profile.html
    CONTEXTO:
        - profile: Dict con datos del perfil
        - user: Usuario autenticado
        - is_own_profile: Boolean
    """

    login_url = 'login'

    def get(self, request, user_id=None):
        """Obtener perfil"""

        # Si no se proporciona user_id, usar el del usuario autenticado
        if not user_id:
            user_id = request.user.id

        # Obtener perfil de Supabase
        profile = supabase_service.get_profile(user_id)

        if not profile:
            logger.warning(f"Perfil {user_id} no encontrado")
            return render(request, 'error.html', {
                'error_message': 'Perfil no encontrado'
            }, status=404)

        # Determinar si es el perfil propio
        is_own_profile = (request.user.id == user_id)

        context = {
            'profile': profile,
            'user': request.user,
            'is_own_profile': is_own_profile,
            'page_title': f"Perfil de {profile.get('display_name', 'Usuario')}",
            'currentPage': 'profile'
        }

        return render(request, 'pages/my_profile.html', context)


class ProfileEditView(LoginRequiredMixin, View):
    """
    Editar perfil del usuario

    MÉTODOS: GET, POST
    GET: Mostrar formulario de edición
    POST: Guardar cambios en Supabase
    """

    login_url = 'login'

    def get(self, request):
        """Mostrar formulario de edición"""

        # Obtener perfil actual
        profile = supabase_service.get_profile(request.user.id)

        if not profile:
            return redirect('profile')

        # Inicializar formulario con datos actuales
        form = ProfileEditForm(initial=profile)

        context = {
            'form': form,
            'profile': profile,
            'page_title': 'Editar Perfil',
            'currentPage': 'edit_profile'
        }

        return render(request, 'pages/edit_profile.html', context)

    def post(self, request):
        """Guardar cambios de perfil"""

        form = ProfileEditForm(request.POST)

        if not form.is_valid():
            logger.warning(f"Formulario inválido: {form.errors}")

            profile = supabase_service.get_profile(request.user.id)
            context = {
                'form': form,
                'profile': profile,
                'errors': form.errors
            }

            return render(request, 'pages/edit_profile.html', context)

        # Actualizar en Supabase
        success = supabase_service.update_profile(
            request.user.id,
            form.cleaned_data
        )

        if success:
            logger.info(f"Perfil {request.user.id} actualizado")
            return redirect('profile')
        else:
            logger.error(f"Error actualizando perfil {request.user.id}")

            context = {
                'form': form,
                'error_message': 'Error al guardar los cambios. Intenta de nuevo.'
            }

            return render(request, 'pages/edit_profile.html', context)


class ProfileAPIView(LoginRequiredMixin, View):
    """API endpoint para obtener perfil en formato JSON"""

    login_url = 'login'

    def get(self, request):
        """GET /api/profile/"""

        profile = supabase_service.get_profile(request.user.id)

        if not profile:
            return JsonResponse({'error': 'Perfil no encontrado'}, status=404)

        return JsonResponse({
            'success': True,
            'profile': profile
        })


# API: Actualizar privacidad
@method_decorator(require_http_methods(["POST"]), name='dispatch')
class UpdatePrivacyView(LoginRequiredMixin, View):
    """POST /api/privacy/ - Actualizar nivel de privacidad"""

    login_url = 'login'

    def post(self, request):
        import json

        try:
            data = json.loads(request.body)
            privacy_level = data.get('privacy_level')

            success = supabase_service.update_profile(
                request.user.id,
                {'privacy_level': privacy_level}
            )

            return JsonResponse({
                'success': success,
                'privacy_level': privacy_level
            })

        except Exception as e:
            logger.error(f"Error actualizando privacidad: {str(e)}")
            return JsonResponse({
                'success': False,
                'error': str(e)
            }, status=400)


# API: Búsqueda global
class SearchAPIView(LoginRequiredMixin, View):
    """GET /api/search/?q=query"""

    login_url = 'login'

    def get(self, request):
        query = request.GET.get('q', '').strip()

        if len(query) < 2:
            return JsonResponse({'results': []})

        results = supabase_service.search_profiles(query)

        return JsonResponse({
            'results': results,
            'count': len(results)
        })
