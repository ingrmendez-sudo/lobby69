# ============================================================================
# SERVICIOS DE LÓGICA DE NEGOCIO
# ============================================================================

from django.utils import timezone
from datetime import timedelta
from .models import (
    PaymentTransaction, ReferralCode, Referral, ReferralReward,
    CampaignConfig, PlanFeature, FeatureAccessLog
)
from supabase import create_client
from django.conf import settings
import uuid

supabase = create_client(settings.SUPABASE_URL, settings.SUPABASE_KEY)


class SubscriptionService:
    """Servicio para gestionar suscripciones de usuarios"""

    @staticmethod
    def create_free_subscription(account_id, membership_type_id):
        """Crear suscripción gratuita por 30 días"""
        start_date = timezone.now()
        end_date = start_date + timedelta(days=30)

        response = supabase.table('user_memberships').insert({
            'id': str(uuid.uuid4()),
            'account_id': str(account_id),
            'membership_type_id': str(membership_type_id),
            'start_date': start_date.isoformat(),
            'end_date': end_date.isoformat(),
            'status': 'active',
            'is_paid': False,
            'created_at': start_date.isoformat()
        }).execute()

        return response.data[0] if response.data else None

    @staticmethod
    def get_user_subscription(account_id):
        """Obtener suscripción actual del usuario"""
        response = supabase.table('user_memberships').select('*').eq(
            'account_id', str(account_id)
        ).order('created_at', desc=True).limit(1).execute()

        return response.data[0] if response.data else None

    @staticmethod
    def is_subscription_active(account_id):
        """Verificar si la suscripción es activa"""
        sub = SubscriptionService.get_user_subscription(account_id)
        if not sub:
            return False
        return (
            sub['status'] == 'active' and
            timezone.now() < timezone.make_aware(
                timezone.datetime.fromisoformat(sub['end_date'].replace('Z', '+00:00'))
            )
        )


class ReferralService:
    """Servicio para gestionar referencia de usuarios"""

    @staticmethod
    def generate_referral_code(account_id, user_nick):
        """Generar código de referencia único para usuario"""
        code = f"{user_nick[:6].upper()}_{uuid.uuid4().hex[:8].upper()}"

        ref_code = ReferralCode.objects.create(
            account_id=account_id,
            code=code,
            active=True
        )
        return ref_code

    @staticmethod
    def use_referral_code(code, referred_account_id):
        """Usar código de referencia al registrarse"""
        try:
            ref_code = ReferralCode.objects.get(code=code)

            if not ref_code.is_valid():
                return None, "Código inválido o expirado"

            # Crear referral
            referral = Referral.objects.create(
                referrer_account_id=ref_code.account_id,
                referred_account_id=referred_account_id,
                code_used=code,
                status='pending'
            )

            # Incrementar contador de usos
            ref_code.uses_count += 1
            ref_code.save()

            return referral, None

        except ReferralCode.DoesNotExist:
            return None, "Código no encontrado"

    @staticmethod
    def mark_referral_as_paid(referred_account_id):
        """Marcar referral como pagado cuando el referido paga"""
        try:
            referral = Referral.objects.get(referred_account_id=referred_account_id)
            referral.status = 'paid'
            referral.referred_payment_date = timezone.now()
            referral.save()

            # Verificar si referrer desbloqueó recompensa
            ReferralService.check_and_create_reward(referral.referrer_account_id)

            return referral
        except Referral.DoesNotExist:
            return None

    @staticmethod
    def check_and_create_reward(referrer_account_id):
        """Verificar si referrer alcanzó hito de recompensa"""
        paid_referrals = Referral.objects.filter(
            referrer_account_id=referrer_account_id,
            status='paid'
        ).count()

        # Si tiene 5 referidos pagos y no tiene recompensa reclamada
        if paid_referrals == 5:
            existing = ReferralReward.objects.filter(
                referrer_account_id=referrer_account_id,
                required_paid_referrals=5
            ).exists()

            if not existing:
                ReferralReward.objects.create(
                    referrer_account_id=referrer_account_id,
                    required_paid_referrals=5,
                    reward_type='free_month',
                    reward_value='1'
                )


class FeatureAccessService:
    """Servicio para verificar acceso a features según plan"""

    @staticmethod
    def has_feature_access(account_id, feature_key):
        """Verificar si usuario tiene acceso a un feature"""

        # Obtener suscripción del usuario
        sub = SubscriptionService.get_user_subscription(account_id)

        if not sub or sub['status'] != 'active':
            # Usuario expirado = usa plan FREE
            membership_type_id = supabase.table('membership_types').select('id').eq(
                'name', 'FREE'
            ).execute().data[0]['id']
        else:
            membership_type_id = sub['membership_type_id']

        # Buscar feature en plan
        try:
            feature = PlanFeature.objects.get(
                membership_type_id=membership_type_id,
                feature_key=feature_key
            )

            FeatureAccessLog.objects.create(
                account_id=account_id,
                feature_key=feature_key,
                status='allowed'
            )

            return True, feature.feature_limit

        except PlanFeature.DoesNotExist:
            FeatureAccessLog.objects.create(
                account_id=account_id,
                feature_key=feature_key,
                status='denied'
            )
            return False, None

    @staticmethod
    def check_feature_limit(account_id, feature_key):
        """Verificar si usuario alcanzó límite diario de feature"""
        from django.utils.timezone import now
        from datetime import date

        today = date.today()
        count = FeatureAccessLog.objects.filter(
            account_id=account_id,
            feature_key=feature_key,
            status='allowed',
            created_at__date=today
        ).count()

        # Obtener límite del plan
        has_access, limit = FeatureAccessService.has_feature_access(account_id, feature_key)

        if limit == 'unlimited':
            return True, None

        # Parse limit: "10/day" → 10
        limit_number = int(limit.split('/')[0])

        if count >= limit_number:
            FeatureAccessLog.objects.create(
                account_id=account_id,
                feature_key=feature_key,
                status='limit_exceeded'
            )
            return False, limit_number

        return True, limit_number
