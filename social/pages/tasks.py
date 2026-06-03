# ============================================================================
# CELERY TASKS - AUTOMATIZACIÓN DE SUSCRIPCIONES
# ============================================================================

from celery import shared_task
from django.utils import timezone
from django.core.mail import send_mail
from django.template.loader import render_to_string
from django.conf import settings
from datetime import timedelta, datetime
from decimal import Decimal
import uuid

from .models import PaymentTransaction, Referral, ReferralReward, CampaignConfig
from .services import SubscriptionService, ReferralService
from supabase import create_client

supabase = create_client(settings.SUPABASE_URL, settings.SUPABASE_KEY)


# ============================================================================
# TAREA 1: Verificar suscripciones próximas a expirar
# ============================================================================

@shared_task(name='check_subscriptions_expiring')
def check_subscriptions_expiring():
    """
    Ejecutar cada HORA
    - Enviar email recordatorio 7 días antes
    - Enviar email URGENTE 1 día antes
    - Cobro automático cuando expira (si auto_renew=true)
    """
    print("[TASK] check_subscriptions_expiring iniciada")

    now = timezone.now()

    try:
        # 1. Suscripciones que EXPIRAN DENTRO DE 7 DÍAS
        expiring_7_days = supabase.table('user_memberships').select('*').filter(
            'end_date', 'lt', (now + timedelta(days=7)).isoformat()
        ).filter(
            'end_date', 'gte', (now + timedelta(days=6, hours=23)).isoformat()
        ).execute()

        for sub in expiring_7_days.data or []:
            if sub['status'] == 'active':
                try:
                    account = supabase.table('accounts').select('email').eq(
                        'id', sub['account_id']
                    ).execute().data[0]

                    profile = supabase.table('profiles').select('display_name, nick').eq(
                        'account_id', sub['account_id']
                    ).execute().data[0]

                    # Enviar email
                    send_reminder_email(
                        email=account['email'],
                        user_name=profile.get('display_name') or profile.get('nick'),
                        days_remaining=7,
                        renewal_date=sub['end_date']
                    )
                    print(f"[EMAIL] Recordatorio 7 días enviado a {account['email']}")

                except Exception as e:
                    print(f"[ERROR] Enviando email 7 días: {e}")

        # 2. Suscripciones que EXPIRAN DENTRO DE 1 DÍA
        expiring_1_day = supabase.table('user_memberships').select('*').filter(
            'end_date', 'lt', (now + timedelta(days=1)).isoformat()
        ).filter(
            'end_date', 'gte', (now + timedelta(hours=23)).isoformat()
        ).execute()

        for sub in expiring_1_day.data or []:
            if sub['status'] == 'active':
                try:
                    account = supabase.table('accounts').select('email').eq(
                        'id', sub['account_id']
                    ).execute().data[0]

                    profile = supabase.table('profiles').select('display_name, nick').eq(
                        'account_id', sub['account_id']
                    ).execute().data[0]

                    # Enviar email URGENTE
                    send_urgent_renewal_email(
                        email=account['email'],
                        user_name=profile.get('display_name') or profile.get('nick'),
                        renewal_date=sub['end_date']
                    )
                    print(f"[EMAIL] Recordatorio URGENTE enviado a {account['email']}")

                except Exception as e:
                    print(f"[ERROR] Enviando email urgente: {e}")

        # 3. Suscripciones que EXPIRAN HOY
        expiring_today = supabase.table('user_memberships').select('*').filter(
            'end_date', 'lt', (now + timedelta(hours=1)).isoformat()
        ).filter(
            'status', 'eq', 'active'
        ).execute()

        for sub in expiring_today.data or []:
            try:
                # Intentar cobro automático si auto_renew = True
                payment_method = supabase.table('user_memberships').select('*').eq(
                    'id', sub['id']
                ).execute().data[0]

                auto_renew = payment_method.get('auto_renew', True)

                if auto_renew:
                    # Obtener plan
                    plan = supabase.table('membership_types').select('*').eq(
                        'id', sub['membership_type_id']
                    ).execute().data[0]

                    # Intentar cobro (aquí integraría Conekta)
                    payment_success = attempt_auto_renewal(sub, plan)

                    if payment_success:
                        # Renovar suscripción
                        new_end_date = now + timedelta(days=plan['duration_days'])
                        supabase.table('user_memberships').update({
                            'status': 'active',
                            'end_date': new_end_date.isoformat(),
                            'updated_at': now.isoformat()
                        }).eq('id', sub['id']).execute()

                        # Email de éxito
                        account = supabase.table('accounts').select('email').eq(
                            'id', sub['account_id']
                        ).execute().data[0]

                        send_renewal_success_email(account['email'])
                        print(f"[SUCCESS] Renovación automática: {sub['account_id']}")

                    else:
                        # Cobro fallido
                        supabase.table('user_memberships').update({
                            'status': 'failed'
                        }).eq('id', sub['id']).execute()

                        account = supabase.table('accounts').select('email').eq(
                            'id', sub['account_id']
                        ).execute().data[0]

                        send_renewal_failed_email(account['email'])
                        print(f"[FAILED] Renovación fallida: {sub['account_id']}")

                else:
                    # Sin auto_renew, solo expirar
                    supabase.table('user_memberships').update({
                        'status': 'expired'
                    }).eq('id', sub['id']).execute()

                    account = supabase.table('accounts').select('email').eq(
                        'id', sub['account_id']
                    ).execute().data[0]

                    send_expired_email(account['email'])
                    print(f"[EXPIRED] Suscripción expirada: {sub['account_id']}")

            except Exception as e:
                print(f"[ERROR] Procesando expiración: {e}")

    except Exception as e:
        print(f"[ERROR] check_subscriptions_expiring: {e}")


# ============================================================================
# TAREA 2: Generar reporte mensual
# ============================================================================

@shared_task(name='generate_monthly_report')
def generate_monthly_report():
    """Ejecutar cada 1 de mes a las 00:00"""
    print("[TASK] generate_monthly_report iniciada")

    try:
        now = timezone.now()

        # Obtener estadísticas
        total_users = supabase.table('profiles').select('id', count='exact').execute()
        total_subscriptions = supabase.table('user_memberships').select(
            'id', count='exact'
        ).filter('status', 'eq', 'active').execute()

        total_paid = PaymentTransaction.objects.filter(
            status='completed',
            created_at__month=now.month,
            created_at__year=now.year
        ).aggregate(total=models.Sum('amount'))['total'] or Decimal('0')

        referral_count = Referral.objects.filter(
            status='paid',
            referred_payment_date__month=now.month,
            referred_payment_date__year=now.year
        ).count()

        report = {
            'date': now.isoformat(),
            'total_users': total_users.count,
            'active_subscriptions': total_subscriptions.count,
            'monthly_revenue': float(total_paid),
            'referrals_paid': referral_count
        }

        print(f"[REPORT] {report}")
        # Aquí podrías guardar en una tabla de reportes o enviar por email

    except Exception as e:
        print(f"[ERROR] generate_monthly_report: {e}")


# ============================================================================
# TAREA 3: Retry de pagos fallidos
# ============================================================================

@shared_task(name='retry_failed_payments')
def retry_failed_payments():
    """Ejecutar cada día a las 8 AM"""
    print("[TASK] retry_failed_payments iniciada")

    try:
        # Obtener transacciones fallidas de ayer
        yesterday = timezone.now() - timedelta(days=1)
        failed_transactions = PaymentTransaction.objects.filter(
            status='failed',
            created_at__date=yesterday.date()
        )

        for transaction in failed_transactions:
            try:
                # Intentar cobro nuevamente
                payment_success = attempt_auto_renewal_by_transaction(transaction)

                if payment_success:
                    transaction.status = 'completed'
                    transaction.save()
                    print(f"[RETRY SUCCESS] {transaction.id}")

            except Exception as e:
                print(f"[RETRY ERROR] {transaction.id}: {e}")

    except Exception as e:
        print(f"[ERROR] retry_failed_payments: {e}")


# ============================================================================
# FUNCIONES AUXILIARES
# ============================================================================

def attempt_auto_renewal(subscription, plan):
    """
    Intentar cobro automático
    Aquí iría la integración real con Conekta
    """
    try:
        # TODO: Implementar cobro real con Conekta
        # Por ahora retorna True como ejemplo
        print(f"[PAYMENT] Intentando cobro para {subscription['account_id']}")
        return True
    except Exception as e:
        print(f"[PAYMENT ERROR] {e}")
        return False


def attempt_auto_renewal_by_transaction(transaction):
    """Intentar cobro de transacción fallida"""
    try:
        # TODO: Implementar cobro real con Conekta
        return True
    except Exception as e:
        print(f"[PAYMENT ERROR] {e}")
        return False


def send_reminder_email(email, user_name, days_remaining, renewal_date):
    """Email recordatorio de renovación"""
    try:
        subject = f"Tu membresía vence en {days_remaining} días"
        html_message = f"""
        <h2>¡Hola {user_name}!</h2>
        <p>Tu membresía vencerá en <strong>{days_remaining} días</strong> ({renewal_date}).</p>
        <p>No pierdas acceso a todas las funciones premium.</p>
        <a href="{settings.SITE_URL}/memberships">Renovar ahora</a>
        """
        send_mail(subject, '', settings.DEFAULT_FROM_EMAIL, [email], html_message=html_message)
    except Exception as e:
        print(f"[EMAIL ERROR] {e}")


def send_urgent_renewal_email(email, user_name, renewal_date):
    """Email URGENTE de renovación"""
    try:
        subject = "⚠️ Tu membresía vence MAÑANA"
        html_message = f"""
        <h2>¡ÚLTIMAS 24 HORAS, {user_name}!</h2>
        <p>Tu membresía vencerá <strong>MAÑANA</strong> ({renewal_date}).</p>
        <p style="color: red; font-weight: bold;">Renueva ahora o perderás acceso a premium.</p>
        <a href="{settings.SITE_URL}/memberships">Renovar AHORA</a>
        """
        send_mail(subject, '', settings.DEFAULT_FROM_EMAIL, [email], html_message=html_message)
    except Exception as e:
        print(f"[EMAIL ERROR] {e}")


def send_renewal_success_email(email):
    """Email de renovación exitosa"""
    try:
        subject = "✅ Tu membresía fue renovada"
        html_message = f"""
        <h2>✅ ¡Renovación exitosa!</h2>
        <p>Tu membresía ha sido renovada automáticamente.</p>
        <p>Continúa disfrutando de todas las funciones premium.</p>
        """
        send_mail(subject, '', settings.DEFAULT_FROM_EMAIL, [email], html_message=html_message)
    except Exception as e:
        print(f"[EMAIL ERROR] {e}")


def send_renewal_failed_email(email):
    """Email de renovación fallida"""
    try:
        subject = "❌ Falló la renovación de tu membresía"
        html_message = f"""
        <h2>❌ Falló la renovación</h2>
        <p>No pudimos procesar tu renovación automática.</p>
        <p>Por favor, actualiza tu método de pago:</p>
        <a href="{settings.SITE_URL}/settings/payment-methods">Actualizar pago</a>
        """
        send_mail(subject, '', settings.DEFAULT_FROM_EMAIL, [email], html_message=html_message)
    except Exception as e:
        print(f"[EMAIL ERROR] {e}")


def send_expired_email(email):
    """Email de suscripción expirada"""
    try:
        subject = "Tu membresía ha expirado"
        html_message = f"""
        <h2>Tu membresía ha expirado</h2>
        <p>Acceso limitado a plan FREE.</p>
        <p>Vuelve a Premium para desbloquear todas las funciones.</p>
        <a href="{settings.SITE_URL}/memberships">Elegir plan</a>
        """
        send_mail(subject, '', settings.DEFAULT_FROM_EMAIL, [email], html_message=html_message)
    except Exception as e:
        print(f"[EMAIL ERROR] {e}")
