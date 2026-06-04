# ============================================================================
# VISTAS DE PAGOS E INTEGRACIÓN CONEKTA
# ============================================================================

from django.views.decorators.http import require_http_methods
from django.http import JsonResponse
from django.views.decorators.csrf import csrf_exempt
from django.contrib.auth.decorators import login_required
from django.conf import settings
from django.utils import timezone
import json
import requests
import uuid
from datetime import timedelta

from .models import PaymentTransaction, ReferralCode, Referral, CampaignConfig
from .services import SubscriptionService, ReferralService, FeatureAccessService
from supabase import create_client

supabase = create_client(settings.SUPABASE_URL, settings.SUPABASE_KEY)


class ConektaService:
    """Servicio para integración con Conekta (proveedor de pagos)"""

    BASE_URL = "https://api.conekta.io"

    def __init__(self, api_key=None):
        self.api_key = api_key or settings.CONEKTA_API_KEY
        self.headers = {
            "Authorization": f"Bearer {self.api_key}",
            "Content-Type": "application/json",
            "Accept-Language": "es"
        }

    def create_order(self, amount, currency, customer_email, description, customer_id=None):
        """Crear orden en Conekta"""
        try:
            payload = {
                "line_items": [
                    {
                        "name": description,
                        "unit_price": int(amount * 100),  # Conekta en centavos
                        "quantity": 1
                    }
                ],
                "currency": currency,
                "customer_info": {
                    "email": customer_email,
                    "phone": "0000000000"  # Requerido pero puede ser dummy
                },
                "metadata": {
                    "customer_id": str(customer_id),
                    "anonymous": True  # No mostrar nombre receptor
                }
            }

            response = requests.post(
                f"{self.BASE_URL}/orders",
                json=payload,
                headers=self.headers
            )

            if response.status_code == 200:
                return response.json()
            else:
                return None

        except Exception as e:
            print(f"[ERROR] Conekta create_order: {e}")
            return None

    def get_order(self, order_id):
        """Obtener detalles de orden"""
        try:
            response = requests.get(
                f"{self.BASE_URL}/orders/{order_id}",
                headers=self.headers
            )

            if response.status_code == 200:
                return response.json()
            else:
                return None

        except Exception as e:
            print(f"[ERROR] Conekta get_order: {e}")
            return None


@login_required(login_url='pages:login')
@require_http_methods(["GET"])
def get_membership_plans_view(request):
    """Obtener todos los planes disponibles con precios y campaña activa"""
    try:
        # Obtener planes de Supabase
        plans_response = supabase.table('membership_types').select('*').execute()
        plans = plans_response.data if plans_response.data else []
        print(f"[DEBUG] Planes cargados: {len(plans)}")

        # Obtener campaña activa desde Supabase - SINTAXIS CORRECTA
        campaigns_response = supabase.table('campaign_configs')\
            .select('*')\
            .eq('active', True)\
            .execute()

        active_campaign = campaigns_response.data[0] if campaigns_response.data else None
        print(f"[DEBUG] Campaña activa: {active_campaign['name'] if active_campaign else 'Ninguna'}")

        plans_with_pricing = []
        for plan in plans:
            plan_data = {
                'id': str(plan['id']),
                'name': plan['name'],
                'price': float(plan['price']) if plan['price'] else 0,
                'duration_days': plan['duration_days'],
                'features': plan['features'],
                'discount_percent': 0
            }

            # Aplicar descuento de campaña si existe
            if active_campaign and active_campaign.get('discount_percent'):
                plan_data['original_price'] = plan_data['price']
                plan_data['price'] = plan_data['price'] * (1 - active_campaign['discount_percent'] / 100)
                plan_data['discount_percent'] = active_campaign['discount_percent']
                plan_data['campaign_name'] = active_campaign['name']

            plans_with_pricing.append(plan_data)

        return JsonResponse({
            'success': True,
            'plans': plans_with_pricing,
            'active_campaign': active_campaign['name'] if active_campaign else None
        })

    except Exception as e:
        print(f"[ERROR] get_membership_plans_view: {e}")
        import traceback
        traceback.print_exc()
        return JsonResponse({'error': str(e)}, status=500)



@login_required(login_url='pages:login')
@require_http_methods(["POST"])
def checkout_view(request):
    """Iniciar proceso de checkout"""
    try:
        data = json.loads(request.body)
        membership_type_id = data.get('membership_type_id')
        payment_method = data.get('payment_method', 'conekta')  # conekta, stripe, etc

        # Obtener plan
        plan_response = supabase.table('membership_types').select('*').eq(
            'id', membership_type_id
        ).execute()

        if not plan_response.data:
            return JsonResponse({'error': 'Plan no encontrado'}, status=404)

        plan = plan_response.data[0]
        amount = float(plan['price'])

        # Aplicar descuento de campaña si existe
        active_campaign = CampaignConfig.objects.filter(active=True).first()
        if active_campaign and active_campaign.discount_percent:
            amount = amount * (1 - active_campaign.discount_percent / 100)

        # Obtener email del usuario
        account_response = supabase.table('accounts').select('email').eq(
            'id', str(request.user.id)
        ).execute()

        email = account_response.data[0]['email'] if account_response.data else request.user.email

        # Crear orden en Conekta
        conekta = ConektaService()
        order = conekta.create_order(
            amount=amount,
            currency='MXN',
            customer_email=email,
            description=plan['name'],
            customer_id=request.user.id
        )

        if not order:
            return JsonResponse({'error': 'Error creando orden'}, status=500)

        # Guardar transacción como pendiente
        transaction = PaymentTransaction.objects.create(
            account_id=request.user.id,
            membership_type_id=membership_type_id,
            amount=amount,
            currency='MXN',
            status='pending',
            payment_method=payment_method,
            transaction_id=order['id']
        )

        return JsonResponse({
            'success': True,
            'order_id': order['id'],
            'amount': amount,
            'checkout_url': order.get('checkout_url'),
            'payment_url': order.get('payment_url')
        })

    except json.JSONDecodeError:
        return JsonResponse({'error': 'JSON inválido'}, status=400)
    except Exception as e:
        print(f"[ERROR] checkout_view: {e}")
        return JsonResponse({'error': str(e)}, status=500)


@csrf_exempt
@require_http_methods(["POST"])
def conekta_webhook_view(request):
    """Webhook de Conekta para confirmación de pagos"""
    try:
        data = json.loads(request.body)
        event_type = data.get('type')

        print(f"[WEBHOOK] Conekta event: {event_type}")

        if event_type == 'order.paid':
            order_id = data['data']['object']['id']

            # Obtener transacción
            transaction = PaymentTransaction.objects.get(transaction_id=order_id)

            # Marcar como completada
            transaction.status = 'completed'
            transaction.save()

            # Crear suscripción en user_memberships
            membership = supabase.table('membership_types').select('*').eq(
                'id', str(transaction.membership_type_id)
            ).execute().data[0]

            start_date = timezone.now()
            end_date = start_date + timedelta(days=membership['duration_days'])

            supabase.table('user_memberships').insert({
                'id': str(uuid.uuid4()),
                'account_id': str(transaction.account_id),
                'membership_type_id': str(transaction.membership_type_id),
                'start_date': start_date.isoformat(),
                'end_date': end_date.isoformat(),
                'status': 'active',
                'is_paid': True,
                'created_at': start_date.isoformat()
            }).execute()

            # Si fue referido, marcar como pagado
            try:
                referral = Referral.objects.get(referred_account_id=transaction.account_id)
                ReferralService.mark_referral_as_paid(transaction.account_id)
            except:
                pass

            print(f"[SUCCESS] Pago completado: {order_id}")
            return JsonResponse({'success': True})

        elif event_type == 'order.payment.failed':
            order_id = data['data']['object']['id']
            transaction = PaymentTransaction.objects.get(transaction_id=order_id)
            transaction.status = 'failed'
            transaction.save()

            print(f"[ERROR] Pago fallido: {order_id}")
            return JsonResponse({'success': True})

        return JsonResponse({'success': True})

    except Exception as e:
        print(f"[ERROR] conekta_webhook_view: {e}")
        return JsonResponse({'error': str(e)}, status=500)


@login_required(login_url='pages:login')
@require_http_methods(["POST"])
def claim_referral_reward_view(request):
    """Reclamar recompensa de referencia"""
    try:
        from .models import ReferralReward

        reward_id = request.POST.get('reward_id')
        reward = ReferralReward.objects.get(id=reward_id, referrer_account_id=request.user.id)

        if reward.claimed:
            return JsonResponse({'error': 'Recompensa ya fue reclamada'}, status=400)

        # Aplicar recompensa (mes gratis)
        if reward.reward_type == 'free_month':
            # Obtener plan FREE
            free_plan = supabase.table('membership_types').select('*').eq(
                'name', 'FREE'
            ).execute().data[0]

            start_date = timezone.now()
            end_date = start_date + timedelta(days=30)

            supabase.table('user_memberships').insert({
                'id': str(uuid.uuid4()),
                'account_id': str(request.user.id),
                'membership_type_id': free_plan['id'],
                'start_date': start_date.isoformat(),
                'end_date': end_date.isoformat(),
                'status': 'active',
                'is_paid': False,
                'created_at': start_date.isoformat()
            }).execute()

        reward.claimed = True
        reward.claimed_date = timezone.now()
        reward.save()

        return JsonResponse({'success': True, 'message': 'Recompensa reclamada'})

    except Exception as e:
        print(f"[ERROR] claim_referral_reward_view: {e}")
        return JsonResponse({'error': str(e)}, status=500)


@login_required(login_url='pages:login')
@require_http_methods(["GET"])
def payment_transactions_list_view(request):
    """Obtener historial de transacciones del usuario desde Supabase"""
    try:
        # Obtener el account_id (UUID) del perfil del usuario desde Supabase
        profile_response = supabase.table('profiles')\
            .select('id')\
            .eq('account_id', str(request.user.id))\
            .execute()

        if not profile_response.data:
            print(f"[DEBUG] No se encontró perfil para account_id: {request.user.id}")
            return JsonResponse({'success': True, 'transactions': []})

        account_uuid = profile_response.data[0]['id']
        print(f"[DEBUG] account_uuid encontrado: {account_uuid}")

        # Ahora sí, buscar transacciones con el UUID correcto
        transactions_response = supabase.table('payment_transactions')\
            .select('*')\
            .eq('account_id', account_uuid)\
            .order('created_at', desc=True)\
            .limit(10)\
            .execute()

        transactions = transactions_response.data if transactions_response.data else []
        print(f"[DEBUG] Transacciones encontradas: {len(transactions)}")

        return JsonResponse({
            'success': True,
            'transactions': transactions
        })
    except Exception as e:
        print(f"[ERROR] payment_transactions_list_view: {e}")
        import traceback
        traceback.print_exc()
        return JsonResponse({'error': str(e)}, status=500)
