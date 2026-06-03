# ============================================================================
# VISTAS DE ADMIN - GESTIÓN DE CAMPAÑAS Y CONFIGURACIÓN
# ============================================================================

from django.views.decorators.http import require_http_methods
from django.http import JsonResponse
from django.contrib.auth.decorators import login_required, user_passes_test
from django.utils import timezone
import json
from datetime import datetime

from .models import CampaignConfig, AppSetting
from supabase import create_client
from django.conf import settings

supabase = create_client(settings.SUPABASE_URL, settings.SUPABASE_KEY)


def is_admin(user):
    """Verificar si usuario es admin"""
    # TODO: Implementar lógica de roles de admin
    # Por ahora, verificar si está en tabla admin_users
    try:
        admin_response = supabase.table('admin_users').select('*').eq(
            'account_id', str(user.id)
        ).execute()
        return bool(admin_response.data)
    except:
        return False


@login_required(login_url='pages:login')
@user_passes_test(is_admin, login_url='pages:login')
@require_http_methods(["GET"])
def admin_dashboard_view(request):
    """Dashboard principal del admin"""
    try:
        # Obtener estadísticas
        total_users = supabase.table('profiles').select('id', count='exact').execute().count
        active_subscriptions = supabase.table('user_memberships').select(
            'id', count='exact'
        ).filter('status', 'eq', 'active').execute().count

        total_revenue = supabase.table('payment_transactions').select(
            'amount'
        ).filter('status', 'eq', 'completed').execute()

        total_revenue_amount = sum([t['amount'] for t in total_revenue.data or []]) if total_revenue.data else 0

        # Obtener campañas activas
        active_campaigns = CampaignConfig.objects.filter(active=True)

        # Obtener configuraciones
        settings_response = supabase.table('app_settings').select('*').execute()
        settings_dict = {s['key']: s['value'] for s in settings_response.data or []}

        return JsonResponse({
            'success': True,
            'dashboard': {
                'total_users': total_users,
                'active_subscriptions': active_subscriptions,
                'total_revenue': float(total_revenue_amount),
                'active_campaigns_count': active_campaigns.count()
            },
            'settings': settings_dict
        })

    except Exception as e:
        print(f"[ERROR] admin_dashboard_view: {e}")
        return JsonResponse({'error': str(e)}, status=500)


@login_required(login_url='pages:login')
@user_passes_test(is_admin, login_url='pages:login')
@require_http_methods(["GET"])
def list_campaigns_view(request):
    """Listar todas las campañas"""
    try:
        campaigns = CampaignConfig.objects.all().values(
            'id', 'name', 'active', 'start_date', 'end_date',
            'discount_percent', 'referral_bonus_multiplier', 'special_offer'
        ).order_by('-created_at')

        campaigns_list = []
        for camp in campaigns:
            campaigns_list.append({
                'id': str(camp['id']),
                'name': camp['name'],
                'active': camp['active'],
                'start_date': camp['start_date'].isoformat() if camp['start_date'] else None,
                'end_date': camp['end_date'].isoformat() if camp['end_date'] else None,
                'discount_percent': camp['discount_percent'],
                'referral_bonus_multiplier': camp['referral_bonus_multiplier'],
                'special_offer': camp['special_offer'],
                'is_running': camp['active'] and camp['start_date'] and camp['end_date']
            })

        return JsonResponse({
            'success': True,
            'campaigns': campaigns_list,
            'total': len(campaigns_list)
        })

    except Exception as e:
        print(f"[ERROR] list_campaigns_view: {e}")
        return JsonResponse({'error': str(e)}, status=500)


@login_required(login_url='pages:login')
@user_passes_test(is_admin, login_url='pages:login')
@require_http_methods(["POST"])
def create_campaign_view(request):
    """Crear nueva campaña"""
    try:
        data = json.loads(request.body)

        campaign = CampaignConfig.objects.create(
            name=data.get('name'),
            active=data.get('active', False),
            start_date=datetime.fromisoformat(data['start_date']) if data.get('start_date') else None,
            end_date=datetime.fromisoformat(data['end_date']) if data.get('end_date') else None,
            discount_percent=data.get('discount_percent'),
            special_offer=data.get('special_offer'),
            referral_bonus_multiplier=data.get('referral_bonus_multiplier', 1.0),
            max_free_users=data.get('max_free_users')
        )

        return JsonResponse({
            'success': True,
            'campaign_id': str(campaign.id),
            'message': 'Campaña creada exitosamente'
        })

    except json.JSONDecodeError:
        return JsonResponse({'error': 'JSON inválido'}, status=400)
    except Exception as e:
        print(f"[ERROR] create_campaign_view: {e}")
        return JsonResponse({'error': str(e)}, status=500)


@login_required(login_url='pages:login')
@user_passes_test(is_admin, login_url='pages:login')
@require_http_methods(["POST"])
def update_campaign_view(request, campaign_id):
    """Actualizar campaña existente"""
    try:
        campaign = CampaignConfig.objects.get(id=campaign_id)
        data = json.loads(request.body)

        campaign.name = data.get('name', campaign.name)
        campaign.active = data.get('active', campaign.active)
        campaign.discount_percent = data.get('discount_percent', campaign.discount_percent)
        campaign.referral_bonus_multiplier = data.get('referral_bonus_multiplier', campaign.referral_bonus_multiplier)
        campaign.max_free_users = data.get('max_free_users', campaign.max_free_users)

        if data.get('start_date'):
            campaign.start_date = datetime.fromisoformat(data['start_date'])
        if data.get('end_date'):
            campaign.end_date = datetime.fromisoformat(data['end_date'])

        campaign.save()

        return JsonResponse({
            'success': True,
            'message': 'Campaña actualizada exitosamente'
        })

    except CampaignConfig.DoesNotExist:
        return JsonResponse({'error': 'Campaña no encontrada'}, status=404)
    except Exception as e:
        print(f"[ERROR] update_campaign_view: {e}")
        return JsonResponse({'error': str(e)}, status=500)


@login_required(login_url='pages:login')
@user_passes_test(is_admin, login_url='pages:login')
@require_http_methods(["POST"])
def toggle_campaign_view(request, campaign_id):
    """Activar/desactivar campaña"""
    try:
        campaign = CampaignConfig.objects.get(id=campaign_id)
        campaign.active = not campaign.active
        campaign.save()

        return JsonResponse({
            'success': True,
            'active': campaign.active,
            'message': f"Campaña {'activada' if campaign.active else 'desactivada'}"
        })

    except CampaignConfig.DoesNotExist:
        return JsonResponse({'error': 'Campaña no encontrada'}, status=404)
    except Exception as e:
        print(f"[ERROR] toggle_campaign_view: {e}")
        return JsonResponse({'error': str(e)}, status=500)


@login_required(login_url='pages:login')
@user_passes_test(is_admin, login_url='pages:login')
@require_http_methods(["POST"])
def delete_campaign_view(request, campaign_id):
    """Eliminar campaña"""
    try:
        campaign = CampaignConfig.objects.get(id=campaign_id)
        campaign_name = campaign.name
        campaign.delete()

        return JsonResponse({
            'success': True,
            'message': f"Campaña '{campaign_name}' eliminada"
        })

    except CampaignConfig.DoesNotExist:
        return JsonResponse({'error': 'Campaña no encontrada'}, status=404)
    except Exception as e:
        print(f"[ERROR] delete_campaign_view: {e}")
        return JsonResponse({'error': str(e)}, status=500)


@login_required(login_url='pages:login')
@user_passes_test(is_admin, login_url='pages:login')
@require_http_methods(["GET", "POST"])
def app_settings_view(request):
    """Obtener/actualizar configuración global"""
    try:
        if request.method == 'GET':
            settings_response = supabase.table('app_settings').select('*').execute()
            settings_dict = {s['key']: s['value'] for s in settings_response.data or []}

            return JsonResponse({
                'success': True,
                'settings': settings_dict
            })

        elif request.method == 'POST':
            data = json.loads(request.body)

            for key, value in data.items():
                supabase.table('app_settings').update({
                    'value': str(value),
                    'updated_at': timezone.now().isoformat()
                }).eq('key', key).execute()

            return JsonResponse({
                'success': True,
                'message': 'Configuraciones actualizadas'
            })

    except json.JSONDecodeError:
        return JsonResponse({'error': 'JSON inválido'}, status=400)
    except Exception as e:
        print(f"[ERROR] app_settings_view: {e}")
        return JsonResponse({'error': str(e)}, status=500)


@login_required(login_url='pages:login')
@user_passes_test(is_admin, login_url='pages:login')
@require_http_methods(["GET"])
def campaign_presets_view(request):
    """Obtener presets de campañas predefinidas"""
    try:
        presets = {
            'black_friday': {
                'name': 'Black Friday 2024',
                'discount_percent': 30,
                'referral_bonus_multiplier': 1.5,
                'special_offer': {
                    'description': 'Black Friday - 30% descuento en todos los planes'
                }
            },
            'christmas': {
                'name': 'Navidad 2024',
                'discount_percent': 25,
                'referral_bonus_multiplier': 1.5,
                'special_offer': {
                    'description': 'Navidad - 25% descuento en todos los planes'
                }
            },
            'spring': {
                'name': 'Primavera 2025',
                'discount_percent': 15,
                'referral_bonus_multiplier': 1.2,
                'special_offer': {
                    'description': 'Primavera - 15% descuento'
                }
            },
            'summer': {
                'name': 'Verano 2025',
                'discount_percent': 20,
                'referral_bonus_multiplier': 1.3,
                'special_offer': {
                    'description': 'Verano - 20% descuento'
                }
            },
            'independence_day': {
                'name': 'Mes Patrio 2025',
                'discount_percent': 18,
                'referral_bonus_multiplier': 1.4,
                'special_offer': {
                    'description': 'Mes Patrio - 18% descuento'
                }
            },
            'founding_members': {
                'name': 'Miembros Fundadores',
                'discount_percent': 0,
                'special_offer': {
                    'founding_members': True,
                    'founding_price': 2499,
                    'max_members': 100,
                    'description': 'Acceso de por vida con precio especial'
                }
            }
        }

        return JsonResponse({
            'success': True,
            'presets': presets
        })

    except Exception as e:
        print(f"[ERROR] campaign_presets_view: {e}")
        return JsonResponse({'error': str(e)}, status=500)
