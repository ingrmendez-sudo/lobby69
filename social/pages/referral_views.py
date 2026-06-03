# ============================================================================
# VISTAS DE SISTEMA DE REFERENCIA
# ============================================================================

from django.views.decorators.http import require_http_methods
from django.http import JsonResponse
from django.contrib.auth.decorators import login_required
from django.utils import timezone
import json

from .models import ReferralCode, Referral, ReferralReward
from .services import ReferralService
from supabase import create_client
from django.conf import settings

supabase = create_client(settings.SUPABASE_URL, settings.SUPABASE_KEY)


@login_required(login_url='pages:login')
@require_http_methods(["GET"])
def get_referral_code_view(request):
    """Obtener código de referencia del usuario"""
    try:
        ref_code = ReferralCode.objects.get(account_id=request.user.id)

        return JsonResponse({
            'success': True,
            'code': ref_code.code,
            'uses_count': ref_code.uses_count,
            'created_at': ref_code.created_at.isoformat(),
            'active': ref_code.active
        })

    except ReferralCode.DoesNotExist:
        # Generar nuevo código
        profile_response = supabase.table('profiles').select('nick').eq(
            'account_id', str(request.user.id)
        ).execute()

        nick = profile_response.data[0]['nick'] if profile_response.data else 'USER'
        ref_code = ReferralService.generate_referral_code(request.user.id, nick)

        return JsonResponse({
            'success': True,
            'code': ref_code.code,
            'uses_count': 0,
            'created_at': ref_code.created_at.isoformat(),
            'active': True,
            'new': True
        })

    except Exception as e:
        print(f"[ERROR] get_referral_code_view: {e}")
        return JsonResponse({'error': str(e)}, status=500)


@login_required(login_url='pages:login')
@require_http_methods(["GET"])
def list_referrals_view(request):
    """Listar todos los referidos del usuario"""
    try:
        referrals = Referral.objects.filter(
            referrer_account_id=request.user.id
        ).values('id', 'referred_account_id', 'status', 'created_at', 'referred_payment_date')

        # Obtener info de referidos
        referrals_with_info = []
        for ref in referrals:
            profile_response = supabase.table('profiles').select('nick, display_name').eq(
                'account_id', str(ref['referred_account_id'])
            ).execute()

            profile = profile_response.data[0] if profile_response.data else {}

            referrals_with_info.append({
                'id': str(ref['id']),
                'referred_account_id': str(ref['referred_account_id']),
                'referred_nick': profile.get('nick'),
                'referred_name': profile.get('display_name'),
                'status': ref['status'],
                'created_at': ref['created_at'].isoformat() if ref['created_at'] else None,
                'paid_date': ref['referred_payment_date'].isoformat() if ref['referred_payment_date'] else None
            })

        # Contar referidos pagos
        paid_referrals = Referral.objects.filter(
            referrer_account_id=request.user.id,
            status='paid'
        ).count()

        # Obtener recompensas disponibles
        rewards = ReferralReward.objects.filter(
            referrer_account_id=request.user.id,
            claimed=False
        ).values('id', 'required_paid_referrals', 'reward_type', 'reward_value')

        return JsonResponse({
            'success': True,
            'referrals': referrals_with_info,
            'paid_count': paid_referrals,
            'pending_rewards': list(rewards)
        })

    except Exception as e:
        print(f"[ERROR] list_referrals_view: {e}")
        return JsonResponse({'error': str(e)}, status=500)


@login_required(login_url='pages:login')
@require_http_methods(["GET"])
def get_referral_stats_view(request):
    """Obtener estadísticas de referencia"""
    try:
        total_referrals = Referral.objects.filter(
            referrer_account_id=request.user.id
        ).count()

        paid_referrals = Referral.objects.filter(
            referrer_account_id=request.user.id,
            status='paid'
        ).count()

        pending_referrals = total_referrals - paid_referrals

        available_rewards = ReferralReward.objects.filter(
            referrer_account_id=request.user.id,
            claimed=False
        ).count()

        return JsonResponse({
            'success': True,
            'total_referrals': total_referrals,
            'paid_referrals': paid_referrals,
            'pending_referrals': pending_referrals,
            'available_rewards': available_rewards,
            'progress_to_reward': f"{paid_referrals}/5"  # Próxima recompensa en 5
        })

    except Exception as e:
        print(f"[ERROR] get_referral_stats_view: {e}")
        return JsonResponse({'error': str(e)}, status=500)
