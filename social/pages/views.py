"""
Django views for Social Pages App (CLUB LOBBY69)
"""

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

        # Eliminar directamente sin verificaciÃ³n (por ahora)
        resp = supabase.table('gallery').delete().eq('id', str(media_id)).execute()
        print(f"[DEBUG] Respuesta eliminar: {resp}")

        return JsonResponse({'success': True, 'message': 'Foto eliminada'})
    except Exception as e:
        print(f"[ERROR] Error eliminando: {e}")
        import traceback
        traceback.print_exc()
        return JsonResponse({'error': str(e)}, status=500)


@login_required(login_url='pages:login')
@require_http_methods(["POST"])
def comment_photo_view(request, photo_id):
    """AÃ±adir comentario a una foto"""
    try:
        from social.utils.validators import validate_comment
        from supabase import create_client
        supabase = create_client(settings.SUPABASE_URL, settings.SUPABASE_KEY)

        comment_text = request.POST.get('comment', '').strip()

        if not comment_text:
            return JsonResponse({'error': 'Comentario vacÃ­o'}, status=400)

        # Validar comentario
        try:
            validate_comment(comment_text)
        except Exception as e:
            return JsonResponse({'error': str(e)}, status=400)

        # Guardar comentario
        supabase.table('photo_comments').insert({
            'photo_id': str(photo_id),
            'user_id': str(request.user.id),
            'comment_text': comment_text
        }).execute()

        # Actualizar contador
        comments_resp = supabase.table('photo_comments').select('*', count='exact').eq('photo_id', str(photo_id)).execute()
        comments_count = comments_resp.count

        supabase.table('gallery').update({'comments_count': comments_count}).eq('id', str(photo_id)).execute()

        return JsonResponse({'success': True, 'message': 'Comentario aÃ±adido', 'comments_count': comments_count})
    except Exception as e:
        print(f"[ERROR] Error en comentario: {e}")
        import traceback; traceback.print_exc()
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
        return JsonResponse({'error': 'Permission denied'}, status=403)
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
        messages.error(request, 'PÃ¡gina no encontrada')
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
        supabase = create_client(settings.SUPABASE_URL, settings.SUPABASE_KEY)

        user_id = str(request.user.id)  # "7" como string

        # Obtener la foto para verificar quiÃ©n es el dueÃ±o
        photo_resp = supabase.table('gallery').select('account_id').eq('id', str(photo_id)).execute()

        if not photo_resp.data:
            return JsonResponse({'error': 'Foto no encontrada'}, status=404)

        photo_owner = str(photo_resp.data[0]['account_id'])

        # Verificar que NO sea el dueÃ±o
        if photo_owner == user_id:
            return JsonResponse({'error': 'No puedes dar like a tu propia foto'}, status=403)

        # Verificar si ya existe el like
        existing = supabase.table('photo_likes').select('*').eq('photo_id', str(photo_id)).eq('user_id', user_id).execute()

        if existing.data and len(existing.data) > 0:
            # Eliminar like
            supabase.table('photo_likes').delete().eq('photo_id', str(photo_id)).eq('user_id', user_id).execute()
            action = 'unlike'
        else:
            # AÃ±adir like
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
        return JsonResponse({'error': str(e)}, status=500)

from django.http import JsonResponse
import uuid

@login_required(login_url='pages:login')
@require_http_methods(["POST"])
def comment_photo_view(request, photo_id):
    """Crear comentario en una foto"""
    try:
        comment_text = request.POST.get('comment_text', '').strip()

        if not comment_text or len(comment_text) < 2:
            return JsonResponse({'error': 'El comentario debe tener al menos 2 caracteres'}, status=400)

        if len(comment_text) > 500:
            return JsonResponse({'error': 'El comentario no puede exceder 500 caracteres'}, status=400)

        from supabase import create_client
        supabase = create_client(settings.SUPABASE_URL, settings.SUPABASE_KEY)

        # Crear comentario en Supabase
        comment_data = {
            'id': str(uuid.uuid4()),
            'photo_id': str(photo_id),
            'user_id': str(request.user.id),
            'user_nick': request.user.username,
            'comment_text': comment_text,
            'created_at': timezone.now().isoformat()
        }

        resp = supabase.table('photo_comments').insert(comment_data).execute()

        if resp.data:
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
        else:
            return JsonResponse({'error': 'Error al guardar comentario'}, status=500)

    except Exception as e:
        print(f"[ERROR] {e}")
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



