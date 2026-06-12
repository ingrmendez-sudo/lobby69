"""
Django views for Social Pages App (CLUB LOBBY69)
"""
from social.decorators import admin_required

import time
from django.shortcuts import render

from django.shortcuts import render, redirect, get_object_or_404
from django.contrib import messages
from django.contrib.auth import authenticate, login as auth_login, logout as auth_logout, get_user_model
from django.contrib.auth.decorators import login_required
from django.views.decorators.http import require_http_methods, require_GET, require_POST
from django.utils import timezone
from django.http import JsonResponse
from django.views.generic import TemplateView, FormView, ListView, DetailView
from django.utils.decorators import method_decorator
from django.db.models import Q
from django.core.mail import send_mail
from django.conf import settings

import hashlib
import uuid
from datetime import timedelta, datetime

from social.models import InvitationRequest
from social.services.supabase_service import supabase_service
from social.forms.profile_form import ProfileUpdateForm
from social.utils.mexico_locations import MEXICAN_STATES, CDMX_ALCALDIAS

User = get_user_model()

# ============================================================================
# AUTHENTICATION VIEWS
# ============================================================================

@require_http_methods(["GET", "POST"])
def invitation_request_view(request):
    """Crear solicitud de invitaciÃ³n"""
    if request.method == "POST":
        name = request.POST.get('name', '').strip()
        email = request.POST.get('email', '').strip()
        invited_by = request.POST.get('invited_by', '').strip()

        if not name or not email:
            messages.error(request, 'Nombre y email son requeridos.')
            return render(request, 'pages/solicitar_invitacion.html')

        try:
            token = str(uuid.uuid4())

            # Crear en Django
            invitation = InvitationRequest.objects.create(
                nombre_completo=name,
                email=email,
                edad=18,
                pais='Mexico',
                estado='cdmx',
                municipio='',
                tipo_perfil='single',
                motivo='Solicitud de acceso',
                status='pending',
                activation_token=token,
                invited_by=invited_by
            )
            print(f"[DEBUG] Solicitud creada en Django: {invitation.id}, invitado por: {invited_by}")

            # Sincronizar con Supabase
            try:
                from supabase import create_client
                supabase = create_client(settings.SUPABASE_URL, settings.SUPABASE_KEY)
                supabase_data = {
                    'nombre_completo': invitation.nombre_completo,
                    'email': invitation.email,
                    'edad': invitation.edad,
                    'pais': invitation.pais,
                    'estado': invitation.estado,
                    'municipio': invitation.municipio,
                    'tipo_perfil': invitation.tipo_perfil,
                    'motivo': invitation.motivo,
                    'status': invitation.status,
                    'terminos_aceptados': invitation.terminos_aceptados,
                    'privacidad_aceptada': invitation.privacidad_aceptada,
                    'activation_token': invitation.activation_token,
                    'activation_used': invitation.activation_used,
                    'invited_by': invitation.invited_by
                }
                resp = supabase.table('invitation_requests').insert(supabase_data).execute()
                print(f"[DEBUG] Solicitud guardada en Supabase")
                messages.success(request, 'Solicitud enviada correctamente.')
            except Exception as e:
                print(f"[ERROR] Error sincronizando: {e}")
                import traceback; traceback.print_exc()
                messages.warning(request, 'Solicitud guardada pero hubo error al sincronizar.')

            return redirect('pages:confirmacion')
        except Exception as e:
            print(f"[ERROR] Error creando solicitud: {e}")
            import traceback; traceback.print_exc()
            messages.error(request, f'Error al crear solicitud: {e}')
            return render(request, 'pages/solicitar_invitacion.html')

    return render(request, 'pages/solicitar_invitacion.html')


@require_http_methods(["GET"])
def invitation_confirmation_view(request):
    """PÃ¡gina de confirmaciÃ³n despuÃ©s de enviar solicitud"""
    return render(request, 'pages/confirmacion_solicitud.html')


@require_http_methods(["GET", "POST"])
def activate_account_view(request, token):
    """Activar cuenta con token de invitaciÃ³n"""
    if request.method == "POST":
        username = request.POST.get('username', '').strip()
        password = request.POST.get('password', '').strip()
        password_confirm = request.POST.get('password_confirm', '').strip()

        if not username or not password or not password_confirm:
            messages.error(request, 'Todos los campos son requeridos.')
            return render(request, 'pages/activar_cuenta.html', {'token': token})

        if password != password_confirm:
            messages.error(request, 'Las contraseÃ±as no coinciden.')
            return render(request, 'pages/activar_cuenta.html', {'token': token})

        if len(password) < 8:
            messages.error(request, 'La contraseÃ±a debe tener al menos 8 caracteres.')
            return render(request, 'pages/activar_cuenta.html', {'token': token})

        try:
            invitation = InvitationRequest.objects.get(activation_token=token)

            if invitation.activation_used:
                messages.error(request, 'Este token ya ha sido utilizado.')
                return redirect('pages:login')

            if User.objects.filter(username=username).exists():
                messages.error(request, 'El usuario ya existe.')
                return render(request, 'pages/activar_cuenta.html', {'token': token})

            user = User.objects.create_user(
                username=username,
                email=invitation.email,
                password=password,
                first_name=invitation.nombre_completo.split()[0] if invitation.nombre_completo else ''
            )

            invitation.activation_used = True
            invitation.activated_at = timezone.now()
            invitation.status = 'approved'
            invitation.save()

            try:
                from supabase import create_client
                supabase = create_client(settings.SUPABASE_URL, settings.SUPABASE_KEY)
                supabase.table('invitation_requests').update({
                    'activation_used': True,
                    'activated_at': timezone.now().isoformat(),
                    'status': 'approved'
                }).eq('activation_token', token).execute()
                print(f"[DEBUG] InvitaciÃ³n actualizada en Supabase")
            except Exception as e:
                print(f"[ERROR] Error al actualizar Supabase: {e}")

            messages.success(request, 'Cuenta creada exitosamente. Por favor inicia sesiÃ³n.')
            return redirect('pages:login')

        except InvitationRequest.DoesNotExist:
            messages.error(request, 'Token invÃ¡lido o expirado.')
            return redirect('pages:landing')
        except Exception as e:
            print(f"[ERROR] Error activando cuenta: {e}")
            import traceback; traceback.print_exc()
            messages.error(request, f'Error: {e}')
            return render(request, 'pages/activar_cuenta.html', {'token': token})

    try:
        invitation = InvitationRequest.objects.get(activation_token=token)
        if invitation.activation_used:
            messages.error(request, 'Este token ya ha sido utilizado.')
            return redirect('pages:login')
        return render(request, 'pages/activar_cuenta.html', {'token': token, 'email': invitation.email})
    except InvitationRequest.DoesNotExist:
        messages.error(request, 'Token invÃ¡lido o expirado.')
        return redirect('pages:landing')


@require_http_methods(["GET", "POST"])
def login_view(request):
    """Login view â€“ accepts username or email"""
    if request.user.is_authenticated:
        if request.user.is_staff:
            return redirect('pages:admin_hub')
        else:
            return redirect('pages:dashboard')

    if request.method == "POST":
        login_input = request.POST.get('login_input', '').strip()
        password = request.POST.get('password', '').strip()

        if not login_input or not password:
            messages.error(request, 'Por favor completa todos los campos')
            return render(request, 'pages/login.html')

        user = authenticate(request, username=login_input, password=password)

        if not user:
            try:
                user_by_email = User.objects.get(email=login_input)
                user = authenticate(request, username=user_by_email.username, password=password)
            except User.DoesNotExist:
                user = None

        if user is not None:
            auth_login(request, user)
            messages.success(request, f'Â¡Bienvenido {user.username}!')
            if user.is_staff:
                return redirect('pages:admin_hub')
            else:
                return redirect('pages:dashboard')
        else:
            messages.error(request, 'Usuario/Email o contraseÃ±a incorrectos')
            return render(request, 'pages/login.html')

    return render(request, 'pages/login.html')


@login_required(login_url='pages:login')
def logout_view(request):
    """Logout view"""
    auth_logout(request)
    messages.success(request, 'Has cerrado sesiÃ³n correctamente')
    return redirect('pages:landing')


# ============================================================================
# DASHBOARD & MAIN VIEWS
# ============================================================================

@login_required(login_url='pages:login')
def dashboard_view(request):
    """Dashboard del usuario con feed de fotos"""
    from supabase import create_client
    import json

    user = request.user
    user_id = str(user.id)

    try:
        supabase = create_client(settings.SUPABASE_URL, settings.SUPABASE_KEY)

        # 1. Obtener fotos pÃºblicas (Ãºltimas 20)
        photos_resp = supabase.table('gallery').select('*').eq('visibility', 'public').eq('status', 'approved').order('uploaded_at', desc=True).limit(20).execute()
        photos = photos_resp.data if photos_resp.data else []

        print(f"[DEBUG] Fotos pÃºblicas cargadas: {len(photos)}")

        # 2. Obtener usuarios conectados (Ãºltimas 24h activos)
        users_resp = supabase.table('profiles').select('id, nick, display_name, city, avatar_url, last_active_at').order('last_active_at', desc=True).limit(10).execute()
        connected_users = users_resp.data if users_resp.data else []

        print(f"[DEBUG] Usuarios conectados: {len(connected_users)}")

        # 3. Obtener perfiles sugeridos (aleatorios)
        suggested_resp = supabase.table('profiles').select('id, nick, display_name, city, avatar_url, bio').limit(5).execute()
        suggested_profiles = suggested_resp.data if suggested_resp.data else []

        print(f"[DEBUG] Perfiles sugeridos: {len(suggested_profiles)}")

        context = {
            'user': user,
            'photos': photos,
            'connected_users': connected_users,
            'suggested_profiles': suggested_profiles,
        }
        print(f"[DEBUG RENDER] Context photos: {context['photos']}")
        print(f"[DEBUG RENDER] Número de fotos: {len(context['photos'])}")
        if context['photos']:
            print(f"[DEBUG RENDER] Primera foto: {context['photos'][0]}")

        return render(request, 'pages/dashboard.html', context)

    except Exception as e:
        print(f"[ERROR] Error cargando dashboard: {e}")
        import traceback
        traceback.print_exc()
        return render(request, 'pages/dashboard.html', {'user': user, 'photos': [], 'connected_users': [], 'suggested_profiles': []})


@login_required(login_url='pages:login')
def home_feed_view(request):
    """Home feed view"""
    user = request.user
    return render(request, 'pages/home_feed.html', {'user': user})


@require_http_methods(["GET"])
def landing_view(request):
    """Landing page"""
    if request.user.is_authenticated:
        return redirect('pages:dashboard')
    return render(request, 'pages/landing.html')

@login_required(login_url='pages:login')
def profile_detail_view(request, nickname):
    """Ver perfil de otro usuario"""
    from supabase import create_client

    try:
        supabase = create_client(settings.SUPABASE_URL, settings.SUPABASE_KEY)

        # 1. Obtener perfil del usuario por nick
        profile_resp = supabase.table('profiles').select('*').eq('nick', nickname).execute()

        if not profile_resp.data or len(profile_resp.data) == 0:
            messages.error(request, 'Usuario no encontrado')
            return redirect('pages:explore')

        profile = profile_resp.data[0]

        # 2. Obtener fotos pÃºblicas del usuario
        photos_resp = supabase.table('gallery').select('*').eq('user_nick', nickname).eq('visibility', 'public').eq('status', 'approved').order('uploaded_at', desc=True).execute()
        photos = photos_resp.data if photos_resp.data else []

        # 3. Amigos
        friends_count = 0
        try:
            friends_resp = supabase.table('friendships').select('*').where(
                f"(user_id_1='{profile['id']}' OR user_id_2='{profile['id']}') AND status='accepted'"
            ).execute()
            friends_count = len(friends_resp.data) if friends_resp.data else 0
        except:
            friends_count = 0

        # 4. Verificar estado de amistad con usuario actual
        friendship_status = 'none'  # none, pending, accepted
        try:
            current_user_id = "1"  # ID de Lobby69
            target_id = profile['id']

            if current_user_id < target_id:
                user_id_1, user_id_2 = current_user_id, target_id
            else:
                user_id_1, user_id_2 = target_id, current_user_id

            friendship_resp = supabase.table('friendships').select('*').eq('user_id_1', user_id_1).eq('user_id_2', user_id_2).execute()

            if friendship_resp.data:
                friendship_status = friendship_resp.data[0]['status']
        except:
            pass

        context = {
            'profile': profile,
            'photos': photos,
            'friends_count': friends_count,
            'friendship_status': friendship_status,
            'user': request.user,
        }

        return render(request, 'pages/user_profile.html', context)

    except Exception as e:
        messages.error(request, 'Error al cargar perfil')
        return redirect('pages:explore')

@require_http_methods(["GET"])
def age_gate_view(request):
    """Age gate check"""
    return render(request, 'pages/age_gate.html')


# ============================================================================
# PROFILE VIEWS
# ============================================================================

@login_required(login_url='pages:login')
def my_profile_view(request):
    """View user profile (read-only)"""
    user = request.user
    try:
        from supabase import create_client
        supabase = create_client(settings.SUPABASE_URL, settings.SUPABASE_KEY)

        # Buscar por account_id como string
        resp = supabase.table('profiles').select('*').eq('account_id', str(user.id)).execute()

        if resp.data and len(resp.data) > 0:
            profile = resp.data[0]
        else:
            profile = {}
            print(f"[DEBUG] No se encontrÃ³ perfil para account_id: {user.id}")
    except Exception as e:
        print(f"[ERROR] Error cargando perfil: {e}")
        profile = {}

    return render(request, 'pages/my_profile.html', {'user': user, 'profile': profile})


@login_required(login_url='pages:login')
@require_http_methods(["GET", "POST"])
def edit_profile_view(request):
    """Edit user profile"""
    user = request.user

    if request.method == "POST":
        nombre = request.POST.get('nombre', '').strip()
        edad = request.POST.get('edad', '')
        genero = request.POST.get('genero', '').strip()
        descripcion = request.POST.get('descripcion', '').strip()
        profile_type = request.POST.get('profile_type', 'single').strip().lower()
        estado = request.POST.get('estado', '').strip()
        ciudad = request.POST.get('ciudad', '').strip()
        buscas = request.POST.getlist('buscas')
        para_que = request.POST.getlist('para_que')
        privacidad = request.POST.getlist('privacidad')
        notificaciones = request.POST.getlist('notificaciones')

        pareja_nombre = request.POST.get('pareja_nombre', '').strip() if profile_type == 'pareja' else ''
        pareja_edad = request.POST.get('pareja_edad', '') if profile_type == 'pareja' else ''
        pareja_genero = request.POST.get('pareja_genero', '').strip() if profile_type == 'pareja' else ''
        pareja_descripcion = request.POST.get('pareja_descripcion', '').strip() if profile_type == 'pareja' else ''

        errors = []

        if not nombre:
            errors.append('El nombre es obligatorio.')
        if not edad:
            errors.append('La edad es obligatoria.')
        else:
            try:
                edad_int = int(edad)
                if edad_int < 18 or edad_int > 99:
                    errors.append('La edad debe estar entre 18 y 99 aÃ±os.')
            except ValueError:
                errors.append('La edad debe ser un nÃºmero vÃ¡lido.')

        if not genero:
            errors.append('El gÃ©nero es obligatorio.')
        if not estado:
            errors.append('El estado es obligatorio.')
        if len(descripcion) < 50:
            errors.append(f'La descripciÃ³n debe tener mÃ­nimo 50 caracteres ({len(descripcion)}/50).')
        if estado.lower() == 'cdmx' and not ciudad:
            errors.append('La alcaldÃ­a es obligatoria para Ciudad de MÃ©xico.')

        if profile_type == 'pareja':
            if not pareja_nombre:
                errors.append('El nombre de la pareja es obligatorio.')
            if not pareja_edad:
                errors.append('La edad de la pareja es obligatoria.')
            else:
                try:
                    pareja_edad_int = int(pareja_edad)
                    if pareja_edad_int < 18 or pareja_edad_int > 99:
                        errors.append('La edad de la pareja debe estar entre 18 y 99 aÃ±os.')
                except ValueError:
                    errors.append('La edad de la pareja debe ser un nÃºmero vÃ¡lido.')
            if not pareja_genero:
                errors.append('El gÃ©nero de la pareja es obligatorio.')
            if len(pareja_descripcion) < 50:
                errors.append(f'La descripciÃ³n de la pareja debe tener mÃ­nimo 50 caracteres ({len(pareja_descripcion)}/50).')

        if errors:
            for e in errors:
                messages.error(request, e)
            states = [(s, s) for s in MEXICAN_STATES if s]
            alcaldias = [(a, a) for a in CDMX_ALCALDIAS]
            profile_data = {
                'nick': user.username,
                'display_name': nombre, 'age': edad, 'gender': genero,
                'bio': descripcion, 'profile_type': profile_type, 'state': estado,
                'city': ciudad, 'buscas': buscas, 'para_que': para_que,
                'privacidad': privacidad, 'notificaciones': notificaciones,
                'pareja_nombre': pareja_nombre, 'pareja_edad': pareja_edad,
                'pareja_genero': pareja_genero, 'pareja_descripcion': pareja_descripcion
            }
            return render(request, 'pages/edit_profile.html', {
                'user': user, 'states': states, 'alcaldias': alcaldias, 'profile': profile_data
            })

        profile_data = {
            'account_id': str(user.id),
            'nick': user.username,
            'display_name': nombre,
            'age': int(edad),
            'gender': genero,
            'bio': descripcion,
            'profile_type': profile_type,
            'state': estado,
            'city': ciudad or None,
            'buscas': buscas,
            'para_que': para_que,
            'privacidad': privacidad,
            'notificaciones': notificaciones,
            'updated_at': timezone.now().isoformat()
        }

        if profile_type == 'pareja':
            profile_data.update({
                'pareja_nombre': pareja_nombre,
                'pareja_edad': int(pareja_edad),
                'pareja_genero': pareja_genero,
                'pareja_descripcion': pareja_descripcion,
            })

        try:
            existing = supabase_service.get_profile_by_account(str(user.id))
            if existing:
                supabase_service.update_profile(str(user.id), profile_data)
                messages.success(request, 'Perfil actualizado correctamente.')
            else:
                profile_data['created_at'] = timezone.now().isoformat()
                supabase_service.create_profile(profile_data)
                messages.success(request, 'Perfil creado correctamente.')
            return redirect('pages:my_profile')
        except Exception as e:
            messages.error(request, f'Error al guardar el perfil: {str(e)}')
            import traceback; traceback.print_exc()
            states = [(s, s) for s in MEXICAN_STATES if s]
            alcaldias = [(a, a) for a in CDMX_ALCALDIAS]
            return render(request, 'pages/edit_profile.html', {
                'user': user, 'states': states, 'alcaldias': alcaldias, 'profile': profile_data
            })

    try:
        existing_profile = supabase_service.get_profile_by_account(str(user.id))
    except Exception as e:
        print(f"[DEBUG] Error cargando perfil: {e}")
        existing_profile = None

    if not existing_profile:
        existing_profile = {
            'display_name': '', 'age': '', 'gender': '', 'bio': '',
            'profile_type': 'single', 'state': '', 'city': '',
            'buscas': [], 'para_que': [], 'privacidad': [], 'notificaciones': [],
            'pareja_nombre': '', 'pareja_edad': '', 'pareja_genero': '', 'pareja_descripcion': ''
        }
    else:
        existing_profile['buscas'] = existing_profile.get('buscas', []) or []
        existing_profile['para_que'] = existing_profile.get('para_que', []) or []
        existing_profile['privacidad'] = existing_profile.get('privacidad', []) or []
        existing_profile['notificaciones'] = existing_profile.get('notificaciones', []) or []
        if existing_profile.get('age'):
            existing_profile['age'] = str(existing_profile['age'])
        if existing_profile.get('pareja_edad'):
            existing_profile['pareja_edad'] = str(existing_profile['pareja_edad'])

    states = [(s, s) for s in MEXICAN_STATES if s]
    alcaldias = [(a, a) for a in CDMX_ALCALDIAS]
    return render(request, 'pages/edit_profile.html', {
        'user': user, 'states': states, 'alcaldias': alcaldias, 'profile': existing_profile
    })

from django.http import JsonResponse

@login_required(login_url='pages:login')
def profile_detail_view(request, nickname):
    """Ver perfil de otro usuario"""
    from supabase import create_client

    try:
        supabase = create_client(settings.SUPABASE_URL, settings.SUPABASE_KEY)

        profile_resp = supabase.table('profiles').select('*').eq('nick', nickname).execute()

        if not profile_resp.data:
            messages.error(request, 'Usuario no encontrado')
            return redirect('pages:explore')

        profile = profile_resp.data[0]

        photos_resp = supabase.table('gallery').select('*').eq('user_nick', nickname).eq('visibility', 'public').eq('status', 'approved').order('uploaded_at', desc=True).execute()
        photos = photos_resp.data if photos_resp.data else []

        friends_count = 0
        try:
            friends_resp = supabase.table('friendships').select('*').where(
                f"(user_id_1='{profile['id']}' OR user_id_2='{profile['id']}') AND status='accepted'"
            ).execute()
            friends_count = len(friends_resp.data) if friends_resp.data else 0
        except:
            pass

        # Verificar estado de amistad
        friendship_status = 'none'
        try:
            current_user_id = "1"
            target_id = profile['id']

            if current_user_id < target_id:
                user_id_1, user_id_2 = current_user_id, target_id
            else:
                user_id_1, user_id_2 = target_id, current_user_id

            print(f"[DEBUG] Buscando amistad: {user_id_1} <-> {user_id_2}")

            friendship_resp = supabase.table('friendships').select('*').eq('user_id_1', user_id_1).eq('user_id_2', user_id_2).execute()
            print(f"[DEBUG] Friendship response: {friendship_resp.data}")

            if friendship_resp.data:
                friendship_status = friendship_resp.data[0]['status']
                print(f"[DEBUG] Friendship status: {friendship_status}")
        except Exception as e:
            print(f"[DEBUG] Error checking friendship: {e}")

        context = {
            'profile': profile,
            'photos': photos,
            'friends_count': friends_count,
            'friendship_status': friendship_status,
            'user': request.user,
        }

        return render(request, 'pages/user_profile.html', context)

    except Exception as e:
        messages.error(request, 'Error al cargar perfil')
        return redirect('pages:explore')

from django.http import JsonResponse

@login_required(login_url='pages:login')
def add_friend_view(request, nickname):
    """Agregar un usuario como amigo"""
    from supabase import create_client

    try:
        supabase = create_client(settings.SUPABASE_URL, settings.SUPABASE_KEY)

        # Obtener perfil del usuario a agregar
        target_profile_resp = supabase.table('profiles').select('id').eq('nick', nickname).execute()

        if not target_profile_resp.data:
            return JsonResponse({'error': 'Perfil no encontrado'}, status=404)

        target_id = target_profile_resp.data[0]['id']
        current_user_id = "1"

        # Ordenar IDs
        if current_user_id < target_id:
            user_id_1, user_id_2 = current_user_id, target_id
        else:
            user_id_1, user_id_2 = target_id, current_user_id

        # VERIFICAR SI YA EXISTE
        existing = supabase.table('friendships').select('*').eq('user_id_1', user_id_1).eq('user_id_2', user_id_2).execute()

        if existing.data:
            status = existing.data[0]['status']
            return JsonResponse({'error': f'Ya tienes una solicitud {status}'}, status=400)

        # Crear solicitud de amistad
        friendship_data = {
            'user_id_1': user_id_1,
            'user_id_2': user_id_2,
            'status': 'pending'
        }

        resp = supabase.table('friendships').insert(friendship_data).execute()

        # Crear notificaciÃ³n para el receptor
        try:
            create_notification(
                user_id=request.user.id,
                sender_nick=nickname,
                notification_type='friend_request',
                title=f'{request.user.username} te enviÃ³ una solicitud de amistad',
                message=f'Conecta con {request.user.username} y expande tu red',
                related_id=None
            )
        except Exception as e:
            print(f"Error creando notificaciÃ³n: {e}")

        return JsonResponse({'success': True, 'message': 'Solicitud de amistad enviada'})

    except Exception as e:
        print(f"Error en add_friend_view: {e}")
        import traceback
        traceback.print_exc()
        return JsonResponse({'error': str(e)}, status=500)


@login_required(login_url='pages:login')
def profile_detail_view(request, nickname):
    """Ver perfil de otro usuario"""
    print(f"\n[DEBUG] âœ… profile_detail_view EJECUTADA")
    print(f"[DEBUG] nickname recibido: {nickname}")
    print(f"[DEBUG] request.path: {request.path}")

    from supabase import create_client

    try:
        supabase = create_client(settings.SUPABASE_URL, settings.SUPABASE_KEY)

        # 1. Obtener perfil del usuario por nick
        print(f"[DEBUG] Buscando perfil con nick: {nickname}")
        profile_resp = supabase.table('profiles').select('*').eq('nick', nickname).execute()
        print(f"[DEBUG] Respuesta de Supabase: {profile_resp.data}")

        if not profile_resp.data or len(profile_resp.data) == 0:
            print(f"[DEBUG] âŒ Perfil no encontrado")
            messages.error(request, 'Usuario no encontrado')
            return redirect('pages:explore')

        profile = profile_resp.data[0]
        account_id = profile['account_id']
        print(f"[DEBUG] âœ… Perfil encontrado: {nickname}, account_id: {account_id}")

        # 2. Obtener fotos pÃºblicas del usuario
        photos_resp = supabase.table('gallery').select('*').eq('user_nick', nickname).eq('visibility', 'public').eq('status', 'approved').order('uploaded_at', desc=True).execute()
        photos = photos_resp.data if photos_resp.data else []
        print(f"[DEBUG] Fotos pÃºblicas: {len(photos)}")

        # 3. Obtener amigos del usuario (opcional)
        # Tabla friendships no existe o tiene estructura diferente
        try:
            friends_resp = supabase.table('friendships').select('*').where(
                f"(user_id_1='{profile['id']}' OR user_id_2='{profile['id']}') AND status='accepted'"
            ).execute()
            friends_count = len(friends_resp.data) if friends_resp.data else 0
        except:
            friends_count = 0

        print(f"[DEBUG] Amigos: {friends_count}")

        context = {
            'profile': profile,
            'photos': photos,
            'friends_count': friends_count,
            'user': request.user,
        }

        print(f"[DEBUG] âœ… Renderizando user_profile.html")
        return render(request, 'pages/user_profile.html', context)

    except Exception as e:
        print(f"[DEBUG] âŒ ERROR: {e}")
        import traceback
        traceback.print_exc()
        messages.error(request, 'Error al cargar perfil')
        return redirect('pages:explore')


# ============================================================================
# MAIN FEATURE VIEWS
# ============================================================================

@login_required(login_url='pages:login')
def explore_view(request):
    """Explore profiles with filtering"""
    from supabase import create_client
    from django.core.paginator import Paginator

    try:
        supabase = create_client(settings.SUPABASE_URL, settings.SUPABASE_KEY)

        # 1. Obtener TODOS los perfiles
        profiles_resp = supabase.table('profiles').select('*').order('created_at', desc=True).execute()
        all_profiles = profiles_resp.data if profiles_resp.data else []

        print(f"[DEBUG] Total perfiles sin filtrar: {len(all_profiles)}")

        # 2. APLICAR FILTROS
        profile_type = request.GET.get('profile_type', '').strip()
        state = request.GET.get('state', '').strip()
        age_min = request.GET.get('age_min', '').strip()
        age_max = request.GET.get('age_max', '').strip()
        membership = request.GET.get('membership', '').strip()
        rating = request.GET.get('rating', '').strip()
        search = request.GET.get('search', '').strip()

        filtered_profiles = all_profiles

        # Filtro por tipo de perfil
        if profile_type:
            filtered_profiles = [p for p in filtered_profiles if p.get('profile_type', '').lower() == profile_type.lower()]
            print(f"[DEBUG] DespuÃ©s filtro profile_type: {len(filtered_profiles)}")

        # Filtro por estado
        if state:
            filtered_profiles = [p for p in filtered_profiles if p.get('state', '').lower() == state.lower()]
            print(f"[DEBUG] DespuÃ©s filtro state: {len(filtered_profiles)}")

        # Filtro por edad mÃ­nima
        if age_min:
            try:
                age_min_int = int(age_min)
                filtered_profiles = [p for p in filtered_profiles if p.get('age', 0) >= age_min_int]
                print(f"[DEBUG] DespuÃ©s filtro age_min: {len(filtered_profiles)}")
            except ValueError:
                pass

        # Filtro por edad mÃ¡xima
        if age_max:
            try:
                age_max_int = int(age_max)
                filtered_profiles = [p for p in filtered_profiles if p.get('age', 0) <= age_max_int]
                print(f"[DEBUG] DespuÃ©s filtro age_max: {len(filtered_profiles)}")
            except ValueError:
                pass

        # Filtro por membresÃ­a
        if membership:
            filtered_profiles = [p for p in filtered_profiles if p.get('membership_type', '').lower() == membership.lower()]
            print(f"[DEBUG] DespuÃ©s filtro membership: {len(filtered_profiles)}")

        # Filtro por calificaciÃ³n
        if rating:
            try:
                rating_float = float(rating)
                filtered_profiles = [p for p in filtered_profiles if p.get('rating', 0) >= rating_float]
                print(f"[DEBUG] DespuÃ©s filtro rating: {len(filtered_profiles)}")
            except ValueError:
                pass

        # BÃºsqueda por nombre o ciudad
        if search:
            filtered_profiles = [p for p in filtered_profiles
                               if search.lower() in p.get('display_name', '').lower() or
                                  search.lower() in p.get('city', '').lower()]
            print(f"[DEBUG] DespuÃ©s bÃºsqueda: {len(filtered_profiles)}")

        # 3. PAGINAR (20 por pÃ¡gina)
        paginator = Paginator(filtered_profiles, 20)
        page_number = request.GET.get('page', 1)
        profiles_page = paginator.get_page(page_number)

        # 4. Obtener usuarios conectados
        connected_resp = supabase.table('profiles').select('id, nick, display_name, city, avatar_url, last_active_at').order('last_active_at', desc=True).limit(10).execute()
        connected_users = connected_resp.data if connected_resp.data else []

        # 5. Obtener perfiles sugeridos
        suggested_resp = supabase.table('profiles').select('id, nick, display_name, city, avatar_url, bio').limit(5).execute()
        suggested_profiles = suggested_resp.data if suggested_resp.data else []

        # 6. Contar estadÃ­sticas
        profiles_today = len([p for p in all_profiles if p.get('last_active_at')])  # Aproximado
        likes_count = 0  # TODO: Implementar contador de likes reales

        context = {
            'user': request.user,
            'profiles_page': profiles_page,
            'connected_users': connected_users,
            'suggested_profiles': suggested_profiles,
            'paginator': paginator,
            'profiles_count': profiles_today,
            'likes_count': likes_count,
            'connected_count': len(connected_users),
        }

        return render(request, 'pages/explore.html', context)

    except Exception as e:
        print(f"[ERROR] Error en explore_view: {e}")
        import traceback
        traceback.print_exc()
        return render(request, 'pages/explore.html', {
            'user': request.user,
            'profiles_page': [],
            'connected_users': [],
            'suggested_profiles': [],
            'paginator': None
        })


@login_required(login_url='pages:login')
@require_http_methods(["POST"])
def toggle_visibility_view(request, photo_id):
    """Cambiar visibilidad de foto (pÃºblico/privado)"""
    import json
    from supabase import create_client

    try:
        user_id = str(request.user.id)
        data = json.loads(request.body)
        new_visibility = data.get('visibility', 'public')

        if new_visibility not in ['public', 'private']:
            return JsonResponse({'error': 'Visibilidad invÃ¡lida'}, status=400)

        # Actualizar en Supabase
        supabase = create_client(settings.SUPABASE_URL, settings.SUPABASE_KEY)
        resp = supabase.table('gallery').update({
            'visibility': new_visibility
        }).eq('id', str(photo_id)).execute()

        print(f"[DEBUG] Foto {photo_id} visibilidad cambiada a {new_visibility}")

        if resp.data:
            return JsonResponse({'success': True, 'message': f'Visibilidad cambiada a {new_visibility}'})
        else:
            return JsonResponse({'error': 'No se encontrÃ³ la foto'}, status=404)

    except Exception as e:
        print(f"[ERROR] {e}")
        return JsonResponse({'error': str(e)}, status=500)

@login_required(login_url='pages:login')
def conversations_view(request):
    """Conversations/Messaging"""
    return render(request, 'pages/conversations.html', {'user': request.user})

@login_required(login_url='pages:login')
@require_http_methods(["POST"])
def like_profile_view(request, profile_id):
    """Like/Unlike a profile"""
    try:
        import json
        from supabase import create_client

        supabase = create_client(settings.SUPABASE_URL, settings.SUPABASE_KEY)

        data = json.loads(request.body)
        action = data.get('action', 'like')

        user_id = str(request.user.id)

        # Verificar si ya existe el like
        existing = supabase.table('profile_likes').select('*').eq('profile_id', str(profile_id)).eq('user_id', user_id).execute()

        if action and existing.data:
            # Eliminar like
            supabase.table('profile_likes').delete().eq('profile_id', str(profile_id)).eq('user_id', user_id).execute()
            return JsonResponse({'success': True, 'action': 'unlike'})
        elif action:
            # AÃ±adir like
            supabase.table('profile_likes').insert({
                'profile_id': str(profile_id),
                'user_id': user_id
            }).execute()
            return JsonResponse({'success': True, 'action': 'like'})
        else:
            return JsonResponse({'success': False, 'error': 'Invalid action'}, status=400)

    except Exception as e:
        print(f"[ERROR] {e}")
        return JsonResponse({'error': str(e)}, status=500)


@login_required(login_url='pages:login')
@require_http_methods(["POST"])
def delete_media_view(request, media_id):
    """Eliminar foto de galerÃ­a"""
    try:
        from supabase import create_client
        supabase = create_client(settings.SUPABASE_URL, settings.SUPABASE_KEY)

        print(f"[DEBUG] Eliminando foto: {media_id}")

        # Eliminar directamente sin verificación (por ahora)
        resp = supabase.table('gallery').delete().eq('id', str(media_id)).execute()
        print(f"[DEBUG] Respuesta eliminar: {resp}")

        return JsonResponse({'success': True, 'message': 'Foto eliminada'})
    except Exception as e:
        print(f"[ERROR] Error eliminando: {e}")
        import traceback
        traceback.print_exc()
        return JsonResponse({'error': str(e)}, status=500)


@login_required(login_url='pages:login')
def memberships_view(request):
    """Memberships/Subscription"""
    return render(request, 'pages/memberships.html', {'user': request.user})


@login_required(login_url='pages:login')
def notifications_view(request):
    """Notifications"""
    return render(request, 'pages/notifications.html', {'user': request.user})


@login_required(login_url='pages:login')
def settings_view(request):
    """User settings"""
    return render(request, 'pages/settings.html', {'user': request.user})


# ============================================================================
# POST/CONTENT VIEWS
# ============================================================================

@login_required(login_url='pages:login')
def create_post_view(request):
    """Create a new post"""
    if request.method == "POST":
        content = request.POST.get('content', '').strip()
        if not content:
            return JsonResponse({'error': 'Content is required'}, status=400)
        return JsonResponse({'success': True, 'message': 'Post created'})
    return render(request, 'pages/create_post.html', {'user': request.user})


@login_required(login_url='pages:login')
def like_post_view(request, post_id):
    """Like a post"""
    if request.method == "POST":
        return JsonResponse({'success': True, 'message': 'Post liked'})
    return JsonResponse({'error': 'Method not allowed'}, status=405)


# ============================================================================
# MEMBERSHIP & PAYMENT VIEWS
# ============================================================================

@login_required(login_url='pages:login')
def checkout_view(request, plan_id):
    """Membership checkout (Stripe integration)"""
    return render(request, 'pages/checkout.html', {'plan_id': plan_id, 'user': request.user})


@login_required(login_url='pages:login')
def verification_view(request):
    """Account verification"""
    return render(request, 'pages/verification.html', {'user': request.user})


@login_required(login_url='pages:login')
def daily_plan_view(request):
    """Daily plan view"""
    return render(request, 'pages/daily_plan.html', {'user': request.user})


# ============================================================================
# MODERATION & REPORTING VIEWS
# ============================================================================

@login_required(login_url='pages:login')
def report_content_view(request):
    """Report inappropriate content"""
    if request.method == "POST":
        reported_user_id = request.POST.get('reported_user_id')
        reason = request.POST.get('reason', '').strip()
        if not reported_user_id or not reason:
            return JsonResponse({'error': 'Missing fields'}, status=400)
        return JsonResponse({'success': True, 'message': 'Report submitted'})
    return render(request, 'pages/report_content.html', {'user': request.user})


@login_required(login_url='pages:login')
def admin_dashboard_view(request):
    """Admin dashboard"""
    if not request.user.is_staff:
        messages.error(request, 'Acceso denegado')
        return redirect('pages:dashboard')
    return render(request, 'pages/admin_dashboard.html', {'user': request.user})


@login_required(login_url='pages:login')
def admin_moderate_post_view(request, post_id):
    """Moderate a post (admin only)"""
    if not request.user.is_staff:
        return JsonResponse({'error': 'Permiso Denegado'}, status=403)
    if request.method == "POST":
        action = request.POST.get('action')
        return JsonResponse({'success': True, 'message': f'Post {action}ed'})
    return JsonResponse({'error': 'Method not allowed'}, status=405)


@login_required(login_url='pages:login')
@require_http_methods(["GET", "POST"])
def dynamic_pages_view(request, template_name):
    """Render dynamic template pages"""
    safe_templates = ['about', 'contact', 'terms', 'privacy', 'faq', 'dashboard']
    if template_name not in safe_templates:
        messages.error(request, 'Página no encontrada')
        return redirect('pages:dashboard')
    return render(request, 'pages/{}.html'.format(template_name), {'user': request.user})

@login_required(login_url='pages:login')
@require_http_methods(["GET"])
def photo_detail_view(request, photo_id):
    """PÃ¡gina de detalle de una foto"""
    from supabase import create_client

    try:
        supabase = create_client(settings.SUPABASE_URL, settings.SUPABASE_KEY)

        # Obtener la foto
        resp = supabase.table('gallery').select('*').eq('id', str(photo_id)).execute()

        if not resp.data or len(resp.data) == 0:
            return redirect('pages:gallery')

        photo = resp.data[0]

        # Obtener likes
        likes_resp = supabase.table('photo_likes').select('*', count='exact').eq('photo_id', str(photo_id)).execute()
        likes_count = likes_resp.count if likes_resp.count else 0

        # Obtener comentarios
        comments_resp = supabase.table('photo_comments').select('*').eq('photo_id', str(photo_id)).order('created_at', desc=True).execute()
        comments = comments_resp.data if comments_resp.data else []

        # Verificar si el usuario ya le dio like
        import uuid
        user_id = str(uuid.uuid5(uuid.NAMESPACE_DNS, f'user-{request.user.id}'))
        user_like = supabase.table('photo_likes').select('*').eq('photo_id', str(photo_id)).eq('user_id', user_id).execute()
        user_liked = len(user_like.data) > 0 if user_like.data else False

        return render(request, 'pages/photo_detail.html', {
            'photo': photo,
            'likes_count': likes_count,
            'comments': comments,
            'user_liked': user_liked
        })
    except Exception as e:
        print(f"[ERROR] {e}")
        return redirect('pages:gallery')

@login_required(login_url='pages:login')
@require_http_methods(["POST"])
def like_photo_view(request, photo_id):
    """Dar/quitar like a una foto"""
    try:
        from supabase import create_client
        import uuid

        supabase = create_client(settings.SUPABASE_URL, settings.SUPABASE_KEY)

        # Generar UUID consistente para el usuario
        user_id = str(uuid.uuid5(uuid.NAMESPACE_DNS, f'user-{request.user.id}'))
        print(f"[DEBUG] user_id (UUID): {user_id}")

        # Obtener la foto para verificar quién es el dueño
        photo_resp = supabase.table('gallery').select('account_id').eq('id', str(photo_id)).execute()

        if not photo_resp.data:
            return JsonResponse({'error': 'Foto no encontrada'}, status=404)

        photo_owner = str(photo_resp.data[0]['account_id'])
        print(f"[DEBUG] photo_owner: {photo_owner}, user_id: {user_id}")

        # Verificar que NO sea el dueño
        if photo_owner == user_id:
            return JsonResponse({'error': 'No puedes dar like a tu propia foto'}, status=403)

        # Verificar si ya existe el like
        existing = supabase.table('photo_likes').select('*').eq('photo_id', str(photo_id)).eq('user_id', user_id).execute()

        if existing.data and len(existing.data) > 0:
            # Eliminar like
            supabase.table('photo_likes').delete().eq('photo_id', str(photo_id)).eq('user_id', user_id).execute()
            action = 'unlike'
        else:
            # Añadir like
            supabase.table('photo_likes').insert({
                'photo_id': str(photo_id),
                'user_id': user_id
            }).execute()
            action = 'like'

        # Actualizar contador
        likes_resp = supabase.table('photo_likes').select('*', count='exact').eq('photo_id', str(photo_id)).execute()
        likes_count = likes_resp.count

        supabase.table('gallery').update({'likes_count': likes_count}).eq('id', str(photo_id)).execute()

        return JsonResponse({'success': True, 'action': action, 'likes_count': likes_count})
    except Exception as e:
        print(f"[ERROR] Error en like: {e}")
        import traceback
        traceback.print_exc()
        return JsonResponse({'error': str(e)}, status=500)

@require_http_methods(["POST"])
def enchantment_photo_view(request, photo_id):
    """Dar/quitar 'Me encanta' a una foto"""
    try:
        from supabase import create_client
        import uuid

        supabase = create_client(settings.SUPABASE_URL, settings.SUPABASE_KEY)

        # Generar UUID consistente para el usuario
        user_id = str(uuid.uuid5(uuid.NAMESPACE_DNS, f'user-{request.user.id}'))
        print(f"[DEBUG ENCHANTMENT] user_id (UUID): {user_id}")

        # Obtener la foto
        photo_resp = supabase.table('gallery').select('id').eq('id', str(photo_id)).execute()

        if not photo_resp.data:
            return JsonResponse({'error': 'Foto no encontrada'}, status=404)

        # Verificar si ya existe el enchantment
        existing = supabase.table('photo_enchantments').select('*').eq('photo_id', str(photo_id)).eq('user_id', user_id).execute()

        if existing.data and len(existing.data) > 0:
            # Eliminar enchantment
            supabase.table('photo_enchantments').delete().eq('photo_id', str(photo_id)).eq('user_id', user_id).execute()
            action = 'unenchant'
        else:
            # Añadir enchantment
            supabase.table('photo_enchantments').insert({
                'photo_id': str(photo_id),
                'user_id': user_id
            }).execute()
            action = 'enchant'

        # Actualizar contador
        enchantments_resp = supabase.table('photo_enchantments').select('*', count='exact').eq('photo_id', str(photo_id)).execute()
        enchantment_count = enchantments_resp.count

        supabase.table('gallery').update({'enchantment_count': enchantment_count}).eq('id', str(photo_id)).execute()

        return JsonResponse({'success': True, 'action': action, 'enchantment_count': enchantment_count})
    except Exception as e:
        print(f"[ERROR] Error en enchantment: {e}")
        import traceback
        traceback.print_exc()
        return JsonResponse({'error': str(e)}, status=500)

@login_required(login_url='pages:login')
@require_http_methods(["POST"])
def comment_photo_view(request, photo_id):
    """Crear comentario en una foto"""
    try:
        import json
        import uuid as uuid_module
        from django.utils import timezone
        from supabase import create_client

        data = json.loads(request.body)
        comment_text = data.get('comment_text', '').strip()

        if not comment_text or len(comment_text) < 2:
            return JsonResponse({'error': 'El comentario debe tener al menos 2 caracteres'}, status=400)

        if len(comment_text) > 500:
            return JsonResponse({'error': 'El comentario no puede exceder 500 caracteres'}, status=400)

        supabase = create_client(settings.SUPABASE_URL, settings.SUPABASE_KEY)

        user_uuid = str(uuid_module.uuid5(uuid_module.NAMESPACE_DNS, f'user-{request.user.id}'))

        comment_data = {
            'id': str(uuid_module.uuid4()),
            'photo_id': str(photo_id),
            'user_id': user_uuid,
            'user_nick': request.user.username,
            'comment_text': comment_text,
            'created_at': timezone.now().isoformat()
        }

        print(f"[DEBUG] Insertando comentario: {comment_data}")
        supabase.table('photo_comments').insert(comment_data).execute()

        # ⭐ ACTUALIZAR CONTADOR DE COMENTARIOS
        comments_count_resp = supabase.table('photo_comments').select('*', count='exact').eq('photo_id', str(photo_id)).execute()
        comments_count = comments_count_resp.count if comments_count_resp.count else 0
        supabase.table('gallery').update({'comments_count': comments_count}).eq('id', str(photo_id)).execute()
        print(f"[DEBUG] Contador comentarios actualizado: {comments_count}")

        return JsonResponse({
            'success': True,
            'message': 'Comentario publicado',
            'comment': {
                'id': comment_data['id'],
                'user_nick': comment_data['user_nick'],
                'comment_text': comment_text,
                'created_at': comment_data['created_at']
            }
        })

    except Exception as e:
        print(f"[ERROR] {e}")
        import traceback
        traceback.print_exc()
        return JsonResponse({'error': str(e)}, status=500)

@login_required(login_url='pages:login')
@require_http_methods(["GET"])
def get_photo_comments_view(request, photo_id):
    """Obtener comentarios de una foto"""
    try:
        from supabase import create_client
        supabase = create_client(settings.SUPABASE_URL, settings.SUPABASE_KEY)

        # Obtener comentarios ordenados por fecha
        resp = supabase.table('photo_comments').select('*').eq('photo_id', str(photo_id)).order('created_at', desc=False).execute()

        comments = resp.data if resp.data else []

        return JsonResponse({
            'success': True,
            'comments': comments,
            'total': len(comments)
        })

    except Exception as e:
        print(f"[ERROR] {e}")
        return JsonResponse({'error': str(e)}, status=500)

@login_required(login_url='pages:login')
def save_post_view(request, photo_id):
    """Guardar foto en favoritos"""
    from supabase import create_client
    supabase = create_client(settings.SUPABASE_URL, settings.SUPABASE_KEY)
    user_id = request.user.id

    try:
        # Verificar si ya estÃ¡ guardada
        existing = supabase.table('photo_saves').select('*').eq('photo_id', str(photo_id)).eq('user_id', user_id).execute()

        if existing.data:
            # Si ya existe, eliminarla
            supabase.table('photo_saves').delete().eq('photo_id', str(photo_id)).eq('user_id', user_id).execute()
            saved = False
        else:
            # Si no existe, crearla
            supabase.table('photo_saves').insert({
                'photo_id': str(photo_id),
                'user_id': user_id
            }).execute()
            saved = True

        # Contar total de guardadas
        count = supabase.table('photo_saves').select('*', count='exact').eq('photo_id', str(photo_id)).execute()

        return JsonResponse({
            'success': True,
            'saved': saved,
            'count': count.count or 0
        })
    except Exception as e:
        return JsonResponse({'error': str(e)}, status=500)

@login_required(login_url='pages:login')
def get_saves_count_view(request, photo_id):
    """Obtener contador de guardadas"""
    from supabase import create_client
    supabase = create_client(settings.SUPABASE_URL, settings.SUPABASE_KEY)

    try:
        count = supabase.table('photo_saves').select('*', count='exact').eq('photo_id', str(photo_id)).execute()
        is_saved = False

        if request.user.is_authenticated:
            existing = supabase.table('photo_saves').select('*').eq('photo_id', str(photo_id)).eq('user_id', request.user.id).execute()
            is_saved = len(existing.data) > 0

        return JsonResponse({
            'success': True,
            'count': count.count or 0,
            'is_saved': is_saved
        })
    except Exception as e:
        return JsonResponse({'error': str(e)}, status=500)

@login_required(login_url='pages:login')
def friend_requests_view(request):
    """Ver solicitudes de amistad pendientes"""
    from supabase import create_client
    supabase = create_client(settings.SUPABASE_URL, settings.SUPABASE_KEY)

    try:
        # Obtener UUID del usuario actual
        current_user_profile = supabase.table('profiles').select('id').eq('user_nick', request.user.username).execute()
        if not current_user_profile.data:
            return render(request, 'pages/friend_requests.html', {'pending_requests': [], 'accepted_friends': []})

        current_user_id = current_user_profile.data[0]['id']

        # Obtener solicitudes pendientes (donde el usuario actual es user_id_2)
        pending = supabase.table('friendships').select('*').eq('user_id_2', current_user_id).eq('status', 'pending').execute()

        # Obtener amigos aceptados
        accepted = supabase.table('friendships').select('*').where(
            f"(user_id_1='{current_user_id}' OR user_id_2='{current_user_id}') AND status='accepted'"
        ).execute()

        # Obtener informaciÃ³n de los que enviaron solicitudes
        pending_requests = []
        if pending.data:
            for req in pending.data:
                requester_id = req['user_id_1']
                requester = supabase.table('profiles').select('*').eq('id', requester_id).execute()
                if requester.data:
                    pending_requests.append({
                        'friendship_id': req['id'],
                        'profile': requester.data[0],
                        'sent_at': req['created_at']
                    })

        # Obtener informaciÃ³n de amigos
        friends = []
        if accepted.data:
            for friendship in accepted.data:
                friend_id = friendship['user_id_2'] if friendship['user_id_1'] == current_user_id else friendship['user_id_1']
                friend = supabase.table('profiles').select('*').eq('id', friend_id).execute()
                if friend.data:
                    friends.append(friend.data[0])

        context = {
            'pending_requests': pending_requests,
            'accepted_friends': friends
        }
        return render(request, 'pages/friend_requests.html', context)
    except Exception as e:
        print(f"Error: {e}")
        import traceback; traceback.print_exc()
        return render(request, 'pages/friend_requests.html', {'pending_requests': [], 'accepted_friends': []})

@login_required(login_url='pages:login')
def accept_friend_request_view(request, friendship_id):
    """Aceptar solicitud de amistad"""
    from supabase import create_client
    supabase = create_client(settings.SUPABASE_URL, settings.SUPABASE_KEY)

    try:
        supabase.table('friendships').update({'status': 'accepted'}).eq('id', friendship_id).execute()
        messages.success(request, 'Solicitud aceptada')
        return redirect('pages:friend_requests')
    except Exception as e:
        messages.error(request, 'Error al aceptar solicitud')
        return redirect('pages:friend_requests')

@login_required(login_url='pages:login')
def reject_friend_request_view(request, friendship_id):
    """Rechazar solicitud de amistad"""
    from supabase import create_client
    supabase = create_client(settings.SUPABASE_URL, settings.SUPABASE_KEY)

    try:
        supabase.table('friendships').delete().eq('id', friendship_id).execute()
        messages.success(request, 'Solicitud rechazada')
        return redirect('pages:friend_requests')
    except Exception as e:
        messages.error(request, 'Error al rechazar solicitud')
        return redirect('pages:friend_requests')


from django.http import JsonResponse
import uuid as uuid_lib

@login_required(login_url='pages:login')
def notifications_view(request):
    """Ver notificaciones del usuario"""
    from supabase import create_client
    supabase = create_client(settings.SUPABASE_URL, settings.SUPABASE_KEY)

    try:
        user_id = request.user.id
        filter_type = request.GET.get('filter', 'todas')

        query = supabase.table('notifications').select('*').eq('user_id', user_id)

        if filter_type == 'no_leidas':
            query = query.eq('is_read', False)
        elif filter_type == 'importantes':
            query = query.in_('type', ['friend_request', 'me_gusta'])

        notifications = query.order('created_at', desc=True).execute()
        notif_data = notifications.data if notifications.data else []

        # Contar por tipo
        total = len(notif_data)
        no_leidas = len([n for n in notif_data if not n['is_read']])
        importantes = len([n for n in notif_data if n['type'] in ['friend_request', 'me_gusta']])

        context = {
            'notifications': notif_data,
            'total': total,
            'no_leidas': no_leidas,
            'importantes': importantes,
            'filter': filter_type
        }

        return render(request, 'pages/notifications.html', context)
    except Exception as e:
        print(f"Error: {e}")
        import traceback; traceback.print_exc()
        return render(request, 'pages/notifications.html', {'notifications': [], 'total': 0, 'no_leidas': 0, 'importantes': 0})

@login_required(login_url='pages:login')
def mark_notification_as_read_view(request, notification_id):
    """Marcar notificaciÃ³n como leÃ­da"""
    from supabase import create_client
    supabase = create_client(settings.SUPABASE_URL, settings.SUPABASE_KEY)

    try:
        supabase.table('notifications').update({'is_read': True}).eq('id', notification_id).execute()
        return JsonResponse({'success': True})
    except Exception as e:
        return JsonResponse({'error': str(e)}, status=500)

@login_required(login_url='pages:login')
def clear_notifications_view(request):
    """Limpiar todas las notificaciones"""
    from supabase import create_client
    supabase = create_client(settings.SUPABASE_URL, settings.SUPABASE_KEY)

    try:
        supabase.table('notifications').delete().eq('user_id', request.user.id).execute()
        messages.success(request, 'Notificaciones eliminadas')
        return redirect('pages:notifications')
    except Exception as e:
        messages.error(request, 'Error al eliminar notificaciones')
        return redirect('pages:notifications')

def create_notification(user_id, sender_nick, notification_type, title, message, related_id=None):
    """FunciÃ³n auxiliar para crear notificaciones"""
    from supabase import create_client
    supabase = create_client(settings.SUPABASE_URL, settings.SUPABASE_KEY)

    try:
        notif_data = {
            'user_id': user_id,
            'sender_nick': sender_nick,
            'type': notification_type,
            'title': title,
            'message': message,
            'related_id': str(related_id) if related_id else None,
            'is_read': False
        }
        supabase.table('notifications').insert(notif_data).execute()
    except Exception as e:
        print(f"Error creando notificaciÃ³n: {e}")

@login_required
def gallery_view(request):
    """Gallery view - shows user's photos"""
    from django.shortcuts import render
    user = request.user
    context = {
        'user': user,
        'photos': [],
    }
    return render(request, 'pages/gallery.html', context)

import json
from django.http import JsonResponse
from django.views.decorators.http import require_http_methods
from django.contrib.auth.decorators import login_required
from django.views.decorators.csrf import csrf_exempt
from .models import UserVerification
from supabase import create_client
from django.conf import settings

supabase = create_client(settings.SUPABASE_URL, settings.SUPABASE_KEY)

# ============================================================================
# VISTAS SPRINT 2: VERIFICACIÓN DE IDENTIDAD
# ============================================================================

@login_required(login_url='pages:login')
@require_http_methods(["GET"])
def verification_status_view(request):
    """Obtener estado de verificación actual del usuario"""
    try:
        user_id = str(request.user.id)
        print(f"[DEBUG] Verificación status para user: {user_id}")

        # Consultar Supabase
        verification_response = supabase.table('user_verifications')\
            .select('*')\
            .eq('user_id', user_id)\
            .execute()

        verification = verification_response.data[0] if verification_response.data else None

        if not verification:
            return JsonResponse({
                'success': True,
                'status': 'not_started',
                'verified': False,
                'message': 'No has iniciado el proceso de verificación'
            })

        return JsonResponse({
            'success': True,
            'status': verification['status'],
            'verified': verification['status'] == 'approved',
            'attempt_count': verification['attempt_count'],
            'rejection_reason': verification['rejection_reason'],
            'verified_at': verification['verified_at'],
            'message': f"Estado: {verification['status']}"
        })

    except Exception as e:
        print(f"[ERROR] verification_status_view: {e}")
        return JsonResponse({'error': str(e)}, status=500)


@login_required(login_url='pages:login')
@require_http_methods(["POST"])
def upload_verification_view(request):
    """Subir selfie para verificación"""
    try:
        user_id = str(request.user.id)
        print(f"[DEBUG] Upload verification para user: {user_id}")

        # Obtener archivo
        selfie = request.FILES.get('selfie')

        if not selfie:
            return JsonResponse({
                'success': False,
                'error': 'Se requiere una selfie'
            }, status=400)

        # Validar tipo de archivo
        allowed_formats = ['image/jpeg', 'image/png', 'image/webp']
        if selfie.content_type not in allowed_formats:
            return JsonResponse({
                'success': False,
                'error': 'Solo se permiten JPG, PNG o WebP'
            }, status=400)

        # Validar tamaño (máx 5MB)
        if selfie.size > 5 * 1024 * 1024:
            return JsonResponse({
                'success': False,
                'error': 'El tamaño máximo es 5MB'
            }, status=400)

        # Subir a Supabase Storage
        selfie_path = f"verifications/{user_id}/selfie_{int(time.time())}.jpg"

        try:
            supabase.storage.from_('verifications')\
                .upload(selfie_path, selfie.read(), {'content-type': selfie.content_type})
            print(f"[DEBUG] Selfie subida a Supabase")
        except Exception as e:
            print(f"[ERROR] Upload a Supabase: {e}")
            return JsonResponse({'success': False, 'error': 'Error subiendo archivo'}, status=500)

        # Crear o actualizar registro de verificación
        verification_data = {
            'user_id': user_id,
            'selfie_url': selfie_path,
            'status': 'pending',
            'attempt_count': 1
        }

        # Verificar si ya existe
        existing = supabase.table('user_verifications')\
            .select('id')\
            .eq('user_id', user_id)\
            .execute()

        if existing.data:
            # Actualizar
            supabase.table('user_verifications')\
                .update(verification_data)\
                .eq('user_id', user_id)\
                .execute()
            print(f"[DEBUG] Verificación actualizada")
        else:
            # Crear
            supabase.table('user_verifications')\
                .insert(verification_data)\
                .execute()
            print(f"[DEBUG] Verificación creada")

        return JsonResponse({
            'success': True,
            'message': 'Selfie subida exitosamente. En revisión...',
            'status': 'pending'
        })

    except Exception as e:
        print(f"[ERROR] upload_verification_view: {e}")
        return JsonResponse({'error': str(e)}, status=500)


@login_required(login_url='pages:login')
@require_http_methods(["GET"])
def verification_page_view(request):
    """Página de verificación"""
    try:
        user_id = str(request.user.id)

        # Obtener estado actual
        verification_response = supabase.table('user_verifications')\
            .select('*')\
            .eq('user_id', user_id)\
            .execute()

        verification = verification_response.data[0] if verification_response.data else None

        context = {
            'verification': verification,
            'verified': verification and verification['status'] == 'approved',
            'status': verification['status'] if verification else 'not_started'
        }

        return render(request, 'pages/verification.html', context)

    except Exception as e:
        print(f"[ERROR] verification_page_view: {e}")
        return render(request, 'pages/verification.html', {'error': str(e)})

# ============================================================================
# SPRINT 3: ADMIN DASHBOARD - MEMBRESÍAS Y MODERACIÓN
# ============================================================================

from django.contrib.admin.views.decorators import staff_member_required

@admin_required
@require_http_methods(["GET"])
def admin_memberships_view(request):
    """Solo accesible para staff/admin"""
    try:
        memberships = supabase.table('membership_types').select('*').execute()
        context = {'memberships': memberships.data or [], 'page': 'memberships'}
        return render(request, 'admin/memberships_dashboard.html', context)
    except Exception as e:
        print(f"[ERROR] admin_memberships_view: {e}")
        return render(request, 'admin/memberships_dashboard.html', {'error': str(e)})



@login_required(login_url='pages:login')
@require_http_methods(["POST"])
def update_membership_price_view(request):
    """Actualizar precio de una membresía"""
    if not request.user.is_staff:
        return JsonResponse({'error': 'No autorizado'}, status=403)

    try:
        data = json.loads(request.body)
        membership_id = data.get('membership_id')
        price_promotion = data.get('price_promotion')
        price_normal = data.get('price_normal')

        if not all([membership_id, price_promotion, price_normal]):
            return JsonResponse({'error': 'Datos incompletos'}, status=400)

        # Actualizar en Supabase
        response = supabase.table('membership_types').update({
            'price_promotion': float(price_promotion),
            'price_normal': float(price_normal)
        }).eq('id', membership_id).execute()

        print(f"[INFO] Membresía actualizada: {membership_id}")
        return JsonResponse({'success': True, 'message': 'Precio actualizado'})
    except Exception as e:
        print(f"[ERROR] update_membership_price_view: {e}")
        return JsonResponse({'error': str(e)}, status=500)


@login_required(login_url='pages:login')
@require_http_methods(["POST"])
def update_membership_privilege_view(request):
    """Actualizar privilegio de una membresía"""
    if not request.user.is_staff:
        return JsonResponse({'error': 'No autorizado'}, status=403)

    try:
        data = json.loads(request.body)
        membership_id = data.get('membership_id')
        privilege_key = data.get('privilege_key')
        privilege_value = data.get('privilege_value')

        if not all([membership_id, privilege_key, privilege_value]):
            return JsonResponse({'error': 'Datos incompletos'}, status=400)

        # Verificar si ya existe
        existing = supabase.table('membership_privileges').select('id').eq('membership_type_id', membership_id).eq('privilege_key', privilege_key).execute()

        if existing.data:
            # Actualizar
            supabase.table('membership_privileges').update({
                'privilege_value': privilege_value
            }).eq('membership_type_id', membership_id).eq('privilege_key', privilege_key).execute()
        else:
            # Crear
            supabase.table('membership_privileges').insert({
                'membership_type_id': membership_id,
                'privilege_key': privilege_key,
                'privilege_value': privilege_value
            }).execute()

        print(f"[INFO] Privilegio actualizado: {privilege_key} para {membership_id}")
        return JsonResponse({'success': True, 'message': 'Privilegio actualizado'})
    except Exception as e:
        print(f"[ERROR] update_membership_privilege_view: {e}")
        return JsonResponse({'error': str(e)}, status=500)


# ============================================================================
# MODERACIÓN DE CONTENIDO
# ============================================================================

@admin_required
@require_http_methods(["GET"])
def admin_moderation_queue_view(request):
    """Solo accesible para staff/admin"""
    try:
        pending_content = supabase.table('photos').select('*').eq('status', 'pending')\
            .order('created_at', desc=True).execute()
        context = {'pending_content': pending_content.data or [], 'page': 'moderation'}
        return render(request, 'admin/moderation_queue.html', context)
    except Exception as e:
        return render(request, 'admin/moderation_queue.html', {'error': str(e)})

@admin_required
@require_http_methods(["POST"])
def update_membership_price_view(request):
    """Solo accesible para staff/admin"""
    try:
        data = json.loads(request.body)
        membership_id = data.get('membership_id')
        price_promotion = data.get('price_promotion')
        price_normal = data.get('price_normal')
        if not all([membership_id, price_promotion, price_normal]):
            return JsonResponse({'error': 'Datos incompletos'}, status=400)
        supabase.table('membership_types').update({
            'price_promotion': float(price_promotion),
            'price_normal': float(price_normal)
        }).eq('id', membership_id).execute()
        return JsonResponse({'success': True, 'message': 'Precio actualizado'})
    except Exception as e:
        return JsonResponse({'error': str(e)}, status=500)

@admin_required
@require_http_methods(["POST"])
def moderate_content_view(request):
    """Solo accesible para staff/admin"""
    try:
        data = json.loads(request.body)
        photo_id = data.get('photo_id')
        action = data.get('action')
        reason = data.get('reason', '')
        if action not in ['approve', 'reject']:
            return JsonResponse({'error': 'Acción inválida'}, status=400)
        update_data = {
            'status': 'approved' if action == 'approve' else 'rejected',
            'moderated_by_admin': str(request.user.id),
            'moderated_at': timezone.now().isoformat()
        }
        if action == 'reject':
            update_data['rejection_reason'] = reason
        supabase.table('photos').update(update_data).eq('id', photo_id).execute()
        return JsonResponse({'success': True, 'message': f'Contenido {action}do'})
    except Exception as e:
        return JsonResponse({'error': str(e)}, status=500)

@admin_required
@require_http_methods(["GET"])
def admin_stats_view(request):
    """Solo accesible para staff/admin"""
    try:
        users = supabase.table('profiles').select('id', count='exact').execute()
        total_users = len(users.data) if users.data else 0
        subscriptions = supabase.table('payment_transactions').select('id', count='exact')\
            .eq('status', 'completed').execute()
        total_subscriptions = len(subscriptions.data) if subscriptions.data else 0
        pending = supabase.table('photos').select('id', count='exact').eq('status', 'pending').execute()
        pending_count = len(pending.data) if pending.data else 0
        tickets = supabase.table('support_tickets').select('id', count='exact').eq('status', 'open').execute()
        open_tickets = len(tickets.data) if tickets.data else 0
        return JsonResponse({'success': True, 'stats': {
            'total_users': total_users,
            'total_subscriptions': total_subscriptions,
            'pending_moderation': pending_count,
            'open_support_tickets': open_tickets
        }})
    except Exception as e:
        return JsonResponse({'error': str(e)}, status=500)



@login_required(login_url='pages:login')
@require_http_methods(["POST"])
def moderate_content_view(request):
    """Aprobar o rechazar contenido"""
    if not request.user.is_staff:
        return JsonResponse({'error': 'No autorizado'}, status=403)

    try:
        data = json.loads(request.body)
        photo_id = data.get('photo_id')
        action = data.get('action')  # 'approve' o 'reject'
        reason = data.get('reason', '')

        if action not in ['approve', 'reject']:
            return JsonResponse({'error': 'Acción inválida'}, status=400)

        update_data = {
            'status': 'approved' if action == 'approve' else 'rejected',
            'moderated_by_admin': str(request.user.id),
            'moderated_at': timezone.now().isoformat()
        }

        if action == 'reject':
            update_data['rejection_reason'] = reason

        # Actualizar foto
        supabase.table('photos').update(update_data).eq('id', photo_id).execute()

        print(f"[INFO] Contenido {action}: {photo_id}")
        return JsonResponse({'success': True, 'message': f'Contenido {action}do'})
    except Exception as e:
        print(f"[ERROR] moderate_content_view: {e}")
        return JsonResponse({'error': str(e)}, status=500)


@login_required(login_url='pages:login')
@require_http_methods(["GET"])
def admin_stats_view(request):
    """Estadísticas generales del admin"""
    if not request.user.is_staff:
        return JsonResponse({'error': 'No autorizado'}, status=403)

    try:
        # Contar usuarios
        users = supabase.table('profiles').select('id', count='exact').execute()
        total_users = len(users.data) if users.data else 0

        # Contar suscripciones activas
        subscriptions = supabase.table('payment_transactions').select('id', count='exact').eq('status', 'completed').execute()
        total_subscriptions = len(subscriptions.data) if subscriptions.data else 0

        # Contenido pendiente
        pending = supabase.table('photos').select('id', count='exact').eq('status', 'pending').execute()
        pending_count = len(pending.data) if pending.data else 0

        # Tickets de soporte
        tickets = supabase.table('support_tickets').select('id', count='exact').eq('status', 'open').execute()
        open_tickets = len(tickets.data) if tickets.data else 0

        return JsonResponse({
            'success': True,
            'stats': {
                'total_users': total_users,
                'total_subscriptions': total_subscriptions,
                'pending_moderation': pending_count,
                'open_support_tickets': open_tickets
            }
        })
    except Exception as e:
        print(f"[ERROR] admin_stats_view: {e}")
        return JsonResponse({'error': str(e)}, status=500)

@admin_required
@require_http_methods(["GET"])
def admin_hub_view(request):
    """Admin Dashboard Hub - Página principal del administrador"""
    context = {'page': 'dashboard'}
    return render(request, 'admin/dashboard_hub.html', context)





# ============================================================================
# ADMIN: USERS MANAGEMENT
# ============================================================================

@login_required(login_url='pages:login')
@admin_required
@require_http_methods(["GET"])
def admin_users_view(request):
    """Gestión de usuarios - lista, búsqueda y filtros"""
    from social.users.models import User

    try:
        # Parámetros de búsqueda y filtrado
        search_query = request.GET.get('search', '').strip()
        status_filter = request.GET.get('status', '')  # active, inactive, suspended
        page = int(request.GET.get('page', 1))

        # Query base
        users = User.objects.all().order_by('-date_joined')

        # Búsqueda por username, email o nombre
        if search_query:
            from django.db.models import Q
            users = users.filter(
                Q(username__icontains=search_query) |
                Q(email__icontains=search_query) |
                Q(first_name__icontains=search_query)
            )

        # Filtro por estado
        if status_filter == 'active':
            users = users.filter(is_active=True)
        elif status_filter == 'inactive':
            users = users.filter(is_active=False)

        # Paginación (20 por página)
        total_users = users.count()
        items_per_page = 20
        start_idx = (page - 1) * items_per_page
        end_idx = start_idx + items_per_page
        paginated_users = users[start_idx:end_idx]
        total_pages = (total_users + items_per_page - 1) // items_per_page

        context = {
            'page': 'users',
            'users': paginated_users,
            'total_users': total_users,
            'current_page': page,
            'total_pages': total_pages,
            'search_query': search_query,
            'status_filter': status_filter,
        }

        return render(request, 'admin/users_list.html', context)

    except Exception as e:
        print(f"[ERROR] admin_users_view: {e}")
        import traceback; traceback.print_exc()
        return render(request, 'admin/users_list.html', {'error': str(e)})


@login_required(login_url='pages:login')
@admin_required
@require_http_methods(["GET"])
def admin_user_detail_view(request, user_id):
    """Detalle de usuario - información y acciones"""
    from social.users.models import User

    try:
        user = User.objects.get(id=user_id)

        context = {
            'page': 'users',
            'user': user,
            'is_staff': user.is_staff,
            'is_active': user.is_active,
            'created_at': user.date_joined,
            'last_login': user.last_login,
        }

        return render(request, 'admin/user_detail.html', context)

    except User.DoesNotExist:
        return render(request, 'admin/user_detail.html', {'error': 'Usuario no encontrado'}, status=404)
    except Exception as e:
        print(f"[ERROR] admin_user_detail_view: {e}")
        return render(request, 'admin/user_detail.html', {'error': str(e)})


@login_required(login_url='pages:login')
@admin_required
@require_http_methods(["POST"])
def admin_user_action_view(request):
    """Acciones sobre usuarios: activar, desactivar, suspender"""
    from social.users.models import User
    from django.http import JsonResponse
    import json

    try:
        data = json.loads(request.body)
        user_id = data.get('user_id')
        action = data.get('action')  # activate, deactivate, make_staff, remove_staff

        user = User.objects.get(id=user_id)

        if action == 'activate':
            user.is_active = True
            message = f'Usuario {user.username} activado'
        elif action == 'deactivate':
            user.is_active = False
            message = f'Usuario {user.username} desactivado'
        elif action == 'make_staff':
            user.is_staff = True
            message = f'Usuario {user.username} promovido a staff'
        elif action == 'remove_staff':
            user.is_staff = False
            message = f'Usuario {user.username} removido de staff'
        else:
            return JsonResponse({'error': 'Acción no válida'}, status=400)

        user.save()
        return JsonResponse({'success': True, 'message': message})

    except User.DoesNotExist:
        return JsonResponse({'error': 'Usuario no encontrado'}, status=404)
    except Exception as e:
        print(f"[ERROR] admin_user_action_view: {e}")
        return JsonResponse({'error': str(e)}, status=500)


# ============================================================================
# ADMIN: USERS MANAGEMENT
# ============================================================================

@login_required(login_url='pages:login')
@admin_required
@require_http_methods(["GET"])
def admin_users_view(request):
    """Gestión de usuarios - lista, búsqueda y filtros"""
    from social.users.models import User

    try:
        search_query = request.GET.get('search', '').strip()
        status_filter = request.GET.get('status', '')
        page = int(request.GET.get('page', 1))

        users = User.objects.all().order_by('-date_joined')

        if search_query:
            from django.db.models import Q
            users = users.filter(
                Q(username__icontains=search_query) |
                Q(email__icontains=search_query) |
                Q(first_name__icontains=search_query)
            )

        if status_filter == 'active':
            users = users.filter(is_active=True)
        elif status_filter == 'inactive':
            users = users.filter(is_active=False)

        total_users = users.count()
        items_per_page = 20
        start_idx = (page - 1) * items_per_page
        paginated_users = users[start_idx:start_idx + items_per_page]
        total_pages = (total_users + items_per_page - 1) // items_per_page

        context = {
            'page': 'users',
            'users': paginated_users,
            'total_users': total_users,
            'current_page': page,
            'total_pages': total_pages,
            'search_query': search_query,
            'status_filter': status_filter,
        }

        return render(request, 'admin/users_list.html', context)

    except Exception as e:
        print(f"[ERROR] admin_users_view: {e}")
        return render(request, 'admin/users_list.html', {'error': str(e)})


@login_required(login_url='pages:login')
@admin_required
@require_http_methods(["GET"])
def admin_user_detail_view(request, user_id):
    """Detalle de usuario"""
    from social.users.models import User

    try:
        user = User.objects.get(id=user_id)
        context = {
            'page': 'users',
            'user': user,
            'is_staff': user.is_staff,
            'is_active': user.is_active,
        }
        return render(request, 'admin/user_detail.html', context)
    except User.DoesNotExist:
        return render(request, 'admin/user_detail.html', {'error': 'Usuario no encontrado'}, status=404)
    except Exception as e:
        return render(request, 'admin/user_detail.html', {'error': str(e)})


@login_required(login_url='pages:login')
@admin_required
@require_http_methods(["POST"])
def admin_user_action_view(request):
    """Acciones sobre usuarios"""
    from social.users.models import User
    from django.http import JsonResponse
    import json

    try:
        data = json.loads(request.body)
        user_id = data.get('user_id')
        action = data.get('action')

        user = User.objects.get(id=user_id)

        if action == 'activate':
            user.is_active = True
            message = f'Usuario {user.username} activado'
        elif action == 'deactivate':
            user.is_active = False
            message = f'Usuario {user.username} desactivado'
        elif action == 'make_staff':
            user.is_staff = True
            message = f'Usuario {user.username} promovido a staff'
        elif action == 'remove_staff':
            user.is_staff = False
            message = f'Usuario {user.username} removido de staff'
        else:
            return JsonResponse({'error': 'Acción no válida'}, status=400)

        user.save()
        return JsonResponse({'success': True, 'message': message})

    except Exception as e:
        return JsonResponse({'error': str(e)}, status=500)


# ============================================================================
# ADMIN: INVITATIONS MANAGEMENT
# ============================================================================

@login_required(login_url='pages:login')
@admin_required
@require_http_methods(["GET"])
def admin_invitations_view(request):
    """Gestión de solicitudes de invitación"""
    from social.invitations.models import InvitationRequest
    from django.db.models import Q

    try:
        search_query = request.GET.get('search', '').strip()
        status_filter = request.GET.get('status', '')
        page = int(request.GET.get('page', 1))

        invitations = InvitationRequest.objects.all().order_by('-created_at')

        if search_query:
            invitations = invitations.filter(
                Q(nombre_completo__icontains=search_query) |
                Q(email__icontains=search_query)
            )

        if status_filter:
            invitations = invitations.filter(status=status_filter)

        total = invitations.count()
        items_per_page = 20
        start_idx = (page - 1) * items_per_page
        paginated = invitations[start_idx:start_idx + items_per_page]
        total_pages = (total + items_per_page - 1) // items_per_page

        context = {
            'page': 'invitations',
            'invitations': paginated,
            'total': total,
            'current_page': page,
            'total_pages': total_pages,
            'search_query': search_query,
            'status_filter': status_filter,
        }

        return render(request, 'admin/invitations_list.html', context)
    except Exception as e:
        return render(request, 'admin/invitations_list.html', {'error': str(e)})


@login_required(login_url='pages:login')
@admin_required
@require_http_methods(["GET"])
def admin_invitation_detail_view(request, invitation_id):
    """Detalle de invitación"""
    from social.invitations.models import InvitationRequest
    try:
        invitation = InvitationRequest.objects.get(id=invitation_id)
        return render(request, 'admin/invitation_detail.html', {'page': 'invitations', 'invitation': invitation})
    except InvitationRequest.DoesNotExist:
        return render(request, 'admin/invitation_detail.html', {'error': 'No encontrada'}, status=404)
    except Exception as e:
        return render(request, 'admin/invitation_detail.html', {'error': str(e)})


@login_required(login_url='pages:login')
@admin_required
@require_http_methods(["POST"])
def admin_invitation_action_view(request):
    """Acciones: aprobar, rechazar"""
    from social.invitations.models import InvitationRequest
    from django.http import JsonResponse
    import json
    try:
        data = json.loads(request.body)
        invitation = InvitationRequest.objects.get(id=data.get('invitation_id'))

        if data.get('action') == 'approve':
            invitation.status = 'approved'
            msg = f'Invitación de {invitation.nombre_completo} aprobada'
        elif data.get('action') == 'reject':
            invitation.status = 'rejected'
            invitation.rejection_reason = data.get('reason', '')
            msg = f'Invitación rechazada'
        else:
            return JsonResponse({'error': 'Acción no válida'}, status=400)

        invitation.save()
        return JsonResponse({'success': True, 'message': msg})
    except Exception as e:
        return JsonResponse({'error': str(e)}, status=500)


# ============ SUPPORT MODULE ============
@login_required(login_url='pages:login')
@admin_required
@require_http_methods(["GET"])
def admin_support_view(request):
    """Admin support tickets list"""
    page = int(request.GET.get('page', 1))
    search_query = request.GET.get('search', '').strip()
    status_filter = request.GET.get('status', '')

    try:
        tickets = supabase.table('support_tickets').select('*').execute().data
        if search_query:
            tickets = [t for t in tickets if search_query.lower() in t.get('titulo', '').lower() or search_query.lower() in t.get('email', '').lower()]
        if status_filter:
            tickets = [t for t in tickets if t.get('status') == status_filter]

        total = len(tickets)
        per_page = 20
        total_pages = (total + per_page - 1) // per_page
        start = (page - 1) * per_page
        tickets = tickets[start:start + per_page]

        context = {
            'page': 'support',
            'tickets': tickets,
            'total': total,
            'current_page': page,
            'total_pages': total_pages,
            'search_query': search_query,
            'status_filter': status_filter
        }
        return render(request, 'admin/support_list.html', context)
    except Exception as e:
        print(f"[ERROR] admin_support_view: {e}")
        return render(request, 'admin/support_list.html', {'error': str(e), 'page': 'support'})

@login_required(login_url='pages:login')
@admin_required
@require_http_methods(["GET"])
def admin_support_detail_view(request, ticket_id):
    """Admin support ticket detail"""
    try:
        ticket = supabase.table('support_tickets').select('*').eq('id', ticket_id).single().execute().data
        context = {'page': 'support', 'ticket': ticket}
        return render(request, 'admin/support_detail.html', context)
    except Exception as e:
        print(f"[ERROR] admin_support_detail_view: {e}")
        return render(request, 'admin/support_detail.html', {'error': str(e), 'page': 'support'})

@login_required(login_url='pages:login')
@admin_required
@require_http_methods(["POST"])
def admin_support_action_view(request):
    """Admin support ticket actions"""
    import json
    try:
        data = json.loads(request.body)
        ticket_id = data.get('ticket_id')
        action = data.get('action')
        response_text = data.get('response', '')

        if action == 'close':
            supabase.table('support_tickets').update({'status': 'closed', 'response': response_text}).eq('id', ticket_id).execute()
            return JsonResponse({'success': True, 'message': 'Ticket cerrado'})
        elif action == 'respond':
            supabase.table('support_tickets').update({'response': response_text, 'status': 'responded'}).eq('id', ticket_id).execute()
            return JsonResponse({'success': True, 'message': 'Respuesta enviada'})
    except Exception as e:
        print(f"[ERROR] admin_support_action_view: {e}")
        return JsonResponse({'error': str(e)}, status=500)

# ============ MODERATION MODULE ============
@login_required(login_url='pages:login')
@admin_required
@require_http_methods(["GET"])
def admin_moderation_view(request):
    """Admin moderation queue"""
    page = int(request.GET.get('page', 1))
    status_filter = request.GET.get('status', 'pending')

    try:
        content = supabase.table('moderation_queue').select('*').eq('status', status_filter).execute().data
        total = len(content)
        per_page = 20
        total_pages = (total + per_page - 1) // per_page
        start = (page - 1) * per_page
        content = content[start:start + per_page]

        context = {
            'page': 'moderation',
            'content': content,
            'total': total,
            'current_page': page,
            'total_pages': total_pages,
            'status_filter': status_filter
        }
        return render(request, 'admin/moderation_list.html', context)
    except Exception as e:
        print(f"[ERROR] admin_moderation_view: {e}")
        return render(request, 'admin/moderation_list.html', {'error': str(e), 'page': 'moderation'})

@login_required(login_url='pages:login')
@admin_required
@require_http_methods(["GET"])
def admin_moderation_detail_view(request, content_id):
    """Admin moderation item detail"""
    try:
        item = supabase.table('moderation_queue').select('*').eq('id', content_id).single().execute().data
        context = {'page': 'moderation', 'item': item}
        return render(request, 'admin/moderation_detail.html', context)
    except Exception as e:
        print(f"[ERROR] admin_moderation_detail_view: {e}")
        return render(request, 'admin/moderation_detail.html', {'error': str(e), 'page': 'moderation'})

@login_required(login_url='pages:login')
@admin_required
@require_http_methods(["POST"])
def admin_moderation_action_view(request):
    """Admin moderation actions"""
    import json
    try:
        data = json.loads(request.body)
        item_id = data.get('item_id')
        action = data.get('action')
        reason = data.get('reason', '')

        if action == 'approve':
            supabase.table('moderation_queue').update({'status': 'approved'}).eq('id', item_id).execute()
            return JsonResponse({'success': True, 'message': 'Contenido aprobado'})
        elif action == 'reject':
            supabase.table('moderation_queue').update({'status': 'rejected', 'reason': reason}).eq('id', item_id).execute()
            return JsonResponse({'success': True, 'message': 'Contenido rechazado'})
        elif action == 'remove':
            supabase.table('moderation_queue').update({'status': 'removed'}).eq('id', item_id).execute()
            return JsonResponse({'success': True, 'message': 'Contenido removido'})
    except Exception as e:
        print(f"[ERROR] admin_moderation_action_view: {e}")
        return JsonResponse({'error': str(e)}, status=500)

# ============ REVIEWS MODULE ============
@login_required(login_url='pages:login')
@admin_required
@require_http_methods(["GET"])
def admin_reviews_view(request):
    """Admin reviews management"""
    page = int(request.GET.get('page', 1))
    status_filter = request.GET.get('status', '')

    try:
        reviews = supabase.table('reviews').select('*').execute().data
        if status_filter:
            reviews = [r for r in reviews if r.get('status') == status_filter]

        total = len(reviews)
        per_page = 20
        total_pages = (total + per_page - 1) // per_page
        start = (page - 1) * per_page
        reviews = reviews[start:start + per_page]

        context = {
            'page': 'reviews',
            'reviews': reviews,
            'total': total,
            'current_page': page,
            'total_pages': total_pages,
            'status_filter': status_filter
        }
        return render(request, 'admin/reviews_list.html', context)
    except Exception as e:
        print(f"[ERROR] admin_reviews_view: {e}")
        return render(request, 'admin/reviews_list.html', {'error': str(e), 'page': 'reviews'})

@login_required(login_url='pages:login')
@admin_required
@require_http_methods(["GET"])
def admin_review_detail_view(request, review_id):
    """Admin review detail"""
    try:
        review = supabase.table('reviews').select('*').eq('id', review_id).single().execute().data
        context = {'page': 'reviews', 'review': review}
        return render(request, 'admin/review_detail.html', context)
    except Exception as e:
        print(f"[ERROR] admin_review_detail_view: {e}")
        return render(request, 'admin/review_detail.html', {'error': str(e), 'page': 'reviews'})

@login_required(login_url='pages:login')
@admin_required
@require_http_methods(["POST"])
def admin_review_action_view(request):
    """Admin review actions"""
    import json
    try:
        data = json.loads(request.body)
        review_id = data.get('review_id')
        action = data.get('action')

        if action == 'approve':
            supabase.table('reviews').update({'status': 'approved'}).eq('id', review_id).execute()
            return JsonResponse({'success': True, 'message': 'Resena aprobada'})
        elif action == 'reject':
            supabase.table('reviews').update({'status': 'rejected'}).eq('id', review_id).execute()
            return JsonResponse({'success': True, 'message': 'Resena rechazada'})
        elif action == 'remove':
            supabase.table('reviews').delete().eq('id', review_id).execute()
            return JsonResponse({'success': True, 'message': 'Resena removida'})
    except Exception as e:
        print(f"[ERROR] admin_review_action_view: {e}")
        return JsonResponse({'error': str(e)}, status=500)

# ============ EVENTS MODULE ============
@login_required(login_url='pages:login')
@admin_required
@require_http_methods(["GET"])
def admin_events_view(request):
    """Admin events management"""
    page = int(request.GET.get('page', 1))
    search_query = request.GET.get('search', '').strip()

    try:
        events = supabase.table('events').select('*').execute().data
        if search_query:
            events = [e for e in events if search_query.lower() in e.get('nombre', '').lower()]

        total = len(events)
        per_page = 20
        total_pages = (total + per_page - 1) // per_page
        start = (page - 1) * per_page
        events = events[start:start + per_page]

        context = {
            'page': 'events',
            'events': events,
            'total': total,
            'current_page': page,
            'total_pages': total_pages,
            'search_query': search_query
        }
        return render(request, 'admin/events_list.html', context)
    except Exception as e:
        print(f"[ERROR] admin_events_view: {e}")
        return render(request, 'admin/events_list.html', {'error': str(e), 'page': 'events'})

@login_required(login_url='pages:login')
@admin_required
@require_http_methods(["GET"])
def admin_event_detail_view(request, event_id):
    """Admin event detail"""
    try:
        event = supabase.table('events').select('*').eq('id', event_id).single().execute().data
        context = {'page': 'events', 'event': event}
        return render(request, 'admin/event_detail.html', context)
    except Exception as e:
        print(f"[ERROR] admin_event_detail_view: {e}")
        return render(request, 'admin/event_detail.html', {'error': str(e), 'page': 'events'})

@login_required(login_url='pages:login')
@admin_required
@require_http_methods(["POST"])
def admin_event_action_view(request):
    """Admin event actions"""
    import json
    try:
        data = json.loads(request.body)
        event_id = data.get('event_id')
        action = data.get('action')

        if action == 'publish':
            supabase.table('events').update({'status': 'published'}).eq('id', event_id).execute()
            return JsonResponse({'success': True, 'message': 'Evento publicado'})
        elif action == 'archive':
            supabase.table('events').update({'status': 'archived'}).eq('id', event_id).execute()
            return JsonResponse({'success': True, 'message': 'Evento archivado'})
        elif action == 'delete':
            supabase.table('events').delete().eq('id', event_id).execute()
            return JsonResponse({'success': True, 'message': 'Evento eliminado'})
    except Exception as e:
        print(f"[ERROR] admin_event_action_view: {e}")
        return JsonResponse({'error': str(e)}, status=500)

# ============ NEWS MODULE ============
@login_required(login_url='pages:login')
@admin_required
@require_http_methods(["GET"])
def admin_news_view(request):
    """Admin news management"""
    page = int(request.GET.get('page', 1))
    search_query = request.GET.get('search', '').strip()

    try:
        news = supabase.table('news').select('*').order('created_at', desc=True).execute().data

        if search_query:
            news = [n for n in news if search_query.lower() in n.get('title', '').lower()]

        total = len(news)
        per_page = 20
        total_pages = (total + per_page - 1) // per_page
        start = (page - 1) * per_page
        news_page = news[start:start + per_page]

        context = {
            'page': 'news',
            'news_items': news_page,
            'total': total,
            'current_page': page,
            'total_pages': total_pages,
            'search_query': search_query
        }
        return render(request, 'admin/news_list.html', context)
    except Exception as e:
        print(f"[ERROR] admin_news_view: {e}")
        return render(request, 'admin/news_list.html', {'error': str(e), 'page': 'news', 'news_items': []})


@login_required(login_url='pages:login')
@admin_required
@require_http_methods(["GET", "POST"])
def admin_news_detail_view(request, news_id=None):
    """Create or edit news - handles both GET (form) and POST (save)"""
    from social.pages.forms import NewsForm
    from datetime import datetime

    article = None
    if news_id:
        try:
            article = supabase.table('news').select('*').eq('id', str(news_id)).single().execute().data
        except:
            pass

    if request.method == "POST":
        form = NewsForm(request.POST)
        if form.is_valid():
            news_data = {
                'title': form.cleaned_data['title'],
                'content': form.cleaned_data['content'],
                'category': form.cleaned_data['category'],
                'image_url': form.cleaned_data['image_url'],
                'published': form.cleaned_data['published'],
                'updated_at': datetime.now().isoformat()
            }

            try:
                if news_id:
                    supabase.table('news').update(news_data).eq('id', str(news_id)).execute()
                    return redirect('pages:admin_news')
                else:
                    news_data['autor'] = request.user.username
                    news_data['created_at'] = datetime.now().isoformat()
                    supabase.table('news').insert(news_data).execute()
                    return redirect('pages:admin_news')
            except Exception as e:
                form.add_error(None, f'Error: {str(e)}')
    else:
        initial_data = {}
        if article:
            initial_data = {
                'title': article.get('title', ''),
                'category': article.get('category', ''),
                'content': article.get('content', ''),
                'image_url': article.get('image_url', ''),
                'published': article.get('published', False)
            }
        form = NewsForm(initial=initial_data)

    context = {
        'page': 'news',
        'form': form,
        'article': article,
        'is_edit': bool(news_id)
    }
    return render(request, 'admin/news_form.html', context)


@login_required(login_url='pages:login')
@admin_required
@require_http_methods(["POST"])
def admin_news_action_view(request):
    """Admin news actions - delete"""
    try:
        news_id = request.POST.get('news_id')
        action = request.POST.get('action')

        if action == 'delete':
            supabase.table('news').delete().eq('id', news_id).execute()
            return redirect('pages:admin_news')

        return redirect('pages:admin_news')
    except Exception as e:
        print(f"[ERROR] admin_news_action_view: {e}")
        return redirect('pages:admin_news')


# ============ ANALYTICS MODULE ============
@login_required(login_url='pages:login')
@admin_required
@require_http_methods(["GET"])
def admin_analytics_view(request):
    """Admin analytics dashboard"""
    period = request.GET.get('period', 'daily')

    try:
        # Simulamos datos de trafico desde Supabase
        traffic_data = supabase.table('traffic_logs').select('*').execute().data

        # Procesamos por periodo
        daily_stats = {}
        weekly_stats = {}
        monthly_stats = {}
        yearly_stats = {}

        for log in traffic_data:
            date = log.get('date', '')
            views = log.get('views', 0)

            # Daily
            if date not in daily_stats:
                daily_stats[date] = 0
            daily_stats[date] += views

            # Weekly
            week_key = date[:7]  # YYYY-MM
            if week_key not in weekly_stats:
                weekly_stats[week_key] = 0
            weekly_stats[week_key] += views

            # Monthly
            month_key = date[:7]
            if month_key not in monthly_stats:
                monthly_stats[month_key] = 0
            monthly_stats[month_key] += views

            # Yearly
            year_key = date[:4]
            if year_key not in yearly_stats:
                yearly_stats[year_key] = 0
            yearly_stats[year_key] += views

        # Seleccionar datos segun periodo
        if period == 'daily':
            stats = daily_stats
            labels = sorted(stats.keys())[-30:]  # Ultimos 30 dias
        elif period == 'weekly':
            stats = weekly_stats
            labels = sorted(stats.keys())[-12:]  # Ultimas 12 semanas
        elif period == 'monthly':
            stats = monthly_stats
            labels = sorted(stats.keys())[-12:]  # Ultimos 12 meses
        else:  # yearly
            stats = yearly_stats
            labels = sorted(stats.keys())

        data_values = [stats.get(label, 0) for label in labels]
        total_views = sum(data_values)
        avg_views = total_views // len(data_values) if data_values else 0

        context = {
            'page': 'analytics',
            'period': period,
            'labels': labels,
            'data': data_values,
            'total_views': total_views,
            'avg_views': avg_views,
            'peak_views': max(data_values) if data_values else 0
        }
        return render(request, 'admin/analytics.html', context)
    except Exception as e:
        print(f"[ERROR] admin_analytics_view: {e}")
        return render(request, 'admin/analytics.html', {'error': str(e), 'page': 'analytics'})

@login_required(login_url='pages:login')
@admin_required
@require_http_methods(["GET"])
def admin_analytics_export_view(request):
    """Export analytics as CSV"""
    import csv
    from django.http import HttpResponse

    try:
        period = request.GET.get('period', 'daily')
        traffic_data = supabase.table('traffic_logs').select('*').execute().data

        response = HttpResponse(content_type='text/csv')
        response['Content-Disposition'] = f'attachment; filename="analytics_{period}.csv"'

        writer = csv.writer(response)
        writer.writerow(['Fecha', 'Vistas'])

        for log in traffic_data:
            writer.writerow([log.get('date'), log.get('views', 0)])

        return response
    except Exception as e:
        print(f"[ERROR] admin_analytics_export_view: {e}")
        return JsonResponse({'error': str(e)}, status=500)

@login_required(login_url='pages:login')
@admin_required
@require_http_methods(["POST"])
def admin_upload_image_view(request):
    """Upload image to Supabase storage"""
    try:
        file = request.FILES.get('file')
        if not file:
            return JsonResponse({'success': False, 'error': 'No file provided'}, status=400)

        # Validar tamaño (5 MB max)
        if file.size > 5 * 1024 * 1024:
            return JsonResponse({'success': False, 'error': 'File too large (max 5 MB)'}, status=400)

        # Validar tipo
        if not file.content_type.startswith('image/'):
            return JsonResponse({'success': False, 'error': 'File must be an image'}, status=400)

        import uuid
        filename = f"news-{uuid.uuid4()}.{file.name.split('.')[-1]}"

        # Subir a Supabase Storage
        from django.conf import settings
        from supabase import create_client
        supabase = create_client(settings.SUPABASE_URL, settings.SUPABASE_KEY)

        try:
            supabase.storage.from_('news-images').upload(filename, file.read())
            # Generar URL pública
            url = f"{settings.SUPABASE_URL}/storage/v1/object/public/news-images/{filename}"
            return JsonResponse({'success': True, 'url': url})
        except Exception as e:
            return JsonResponse({'success': False, 'error': f'Upload failed: {str(e)}'}, status=500)

    except Exception as e:
        print(f"[ERROR] admin_upload_image_view: {e}")
        return JsonResponse({'success': False, 'error': str(e)}, status=500)

@require_http_methods(["GET"])
def public_news_view(request):
    """Public news listing page"""
    try:
        page = int(request.GET.get('page', 1))
        search = request.GET.get('search', '').strip()

        # Obtener solo noticias publicadas
        news = supabase.table('news').select('*').eq('published', True).order('published_at', desc=True).execute().data

        if search:
            news = [n for n in news if search.lower() in n.get('title', '').lower()]

        total = len(news)
        per_page = 9
        total_pages = (total + per_page - 1) // per_page
        start = (page - 1) * per_page
        news_page = news[start:start + per_page]

        context = {
            'news_items': news_page,
            'total': total,
            'current_page': page,
            'total_pages': total_pages,
            'search': search
        }
        return render(request, 'pages/news_public.html', context)
    except Exception as e:
        print(f"[ERROR] public_news_view: {e}")
        return render(request, 'pages/news_public.html', {'error': str(e), 'news_items': []})

@require_http_methods(["GET"])
def public_news_detail_view(request, news_id):
    """Public news detail page"""
    try:
        article = supabase.table('news').select('*').eq('id', str(news_id)).eq('published', True).single().execute().data
        context = {
            'article': article,
            'page': 'news_detail'
        }
        return render(request, 'pages/news_detail.html', context)
    except Exception as e:
        print(f"[ERROR] public_news_detail_view: {e}")
        return redirect('pages:public_news')
