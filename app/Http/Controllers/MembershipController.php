<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MembershipPlan;
use App\Models\MembershipPayment;
use App\Mail\InstruccionesPagoSpei;
use App\Models\PaymentEvent;
use App\Services\MembershipAccessService;
use App\Services\SpeiService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MembershipController extends Controller
{
    public function __construct(
        private MembershipAccessService $access,
        private SpeiService $spei
    ) {}

    /**
     * Página principal: muestra todos los planes y el estado actual del usuario.
     */
    public function index(Request $request)
    {
        $user    = $request->user();
        $plans   = MembershipPlan::whereRaw('is_active = true')
                      ->orderBy('sort_order')
                      ->get();

        // Pago pendiente activo (si existe). Se incluye under_review: una orden
        // con comprobante ya subido sigue siendo una gestión abierta.
        $pendingPayment = MembershipPayment::where('user_id', $user->id)
                            ->whereIn('status', ['pending', 'under_review'])
                            ->latest()
                            ->first();

        return view('memberships.index', [
            'user'           => $user,
            'plans'          => $plans,
            'currentTier'    => $user->membership_type ?? 'invitado',
            'pendingPayment' => $pendingPayment,
            'access'         => $this->access,
        ]);
    }

    /**
     * Formulario de solicitud para un plan específico.
     */
    public function request(Request $request)
    {
        $slug = $request->query('plan');
        $plan = $slug ? MembershipPlan::where('slug', $slug)->whereRaw('is_active = true')->first() : null;

        if (! $plan) {
            return redirect()->route('membership.index')
                             ->with('error', 'Plan no encontrado.');
        }

        // Bloquear si ya tiene ese plan activo
        $user = $request->user();
        if ($user->membership_type === $plan->slug) {
            return redirect()->route('membership.index')
                             ->with('info', 'Ya tienes este plan activo.');
        }

        return view('memberships.request', [
            'user' => $user,
            'plan' => $plan,
        ]);
    }

    /**
     * Procesa el formulario de solicitud de pago.
     *
     * Si el método es transferencia SPEI no se espera comprobante todavía: se
     * genera la orden con referencia y monto exacto, y el usuario va a la
     * pantalla de instrucciones con la CLABE y el temporizador.
     */
    public function submit(Request $request)
    {
        $request->validate([
            'plan_slug'        => ['required', 'string', 'exists:membership_plans,slug'],
            'payment_method'   => ['required', 'string', 'in:transferencia,oxxo,tarjeta,paypal,crypto,efectivo,otro'],
            'payment_reference'=> ['nullable', 'string', 'max:200'],
            'receipt'          => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $user = $request->user();
        $plan = MembershipPlan::where('slug', $request->plan_slug)
                    ->whereRaw('is_active = true')
                    ->firstOrFail();

        // Verificar que no tenga ya una solicitud abierta para este plan
        $exists = MembershipPayment::where('user_id', $user->id)
                    ->where('requested_membership', $plan->slug)
                    ->whereIn('status', ['pending', 'under_review'])
                    ->exists();

        if ($exists) {
            return back()->with('error', 'Ya tienes una solicitud pendiente para este plan. Espera a que sea revisada.');
        }

        $isSpei = $request->payment_method === 'transferencia';

        // Subir comprobante si se adjuntó
        $receiptPath = $request->hasFile('receipt')
            ? $request->file('receipt')->store('receipts', 'private')
            : null;

        $base = (float) $plan->active_price;

        // membership_payments_amount_check exige amount > 0. Un plan sin costo
        // (p. ej. 'invitado') NO debe pasar por el flujo de pago: antes el
        // INSERT reventaba con un error de CHECK de PostgreSQL.
        if ($base <= 0) {
            return back()->with('error', 'Este plan no requiere pago. Elige otro plan o contacta al administrador.');
        }

        $payment = MembershipPayment::create([
            'user_id'              => $user->id,
            'requested_membership' => $plan->slug,
            'current_membership'   => $user->membership_type ?? 'invitado',
            'amount'               => $base,
            'currency'             => 'MXN',
            'payment_method'       => $request->payment_method,
            'payment_reference'    => $request->payment_reference,
            'receipt_path'         => $receiptPath,
            'status'               => 'pending',
            'email'                => $user->email,
            'referral_code'        => $request->session()->get('referral_code'),
            'plan_snapshot'        => [
                'slug'          => $plan->slug,
                'name'          => $plan->name,
                'price_normal'  => (float) $plan->price_normal,
                'price_promo'   => (float) $plan->price_promo,
                'price_charged' => $base,
                'promo_active'  => (bool) $plan->promo_active,
                'duration_days' => $plan->duration_days,
                'is_lifetime'   => (bool) $plan->is_lifetime,
                'features'      => $plan->features,
            ],
            'metadata'             => [
                'ip'         => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 500),
            ],
        ]);

        if ($isSpei) {
            // Segunda pasada: ya conocemos el id del pedido, así que pueden
            // generarse la referencia pública y el monto con centavos
            // discriminadores que hacen única esta transferencia.
            $exact = $this->spei->exactAmount($base, (int) $payment->id);

            $payment->update([
                'public_code' => $this->spei->publicCode((int) $payment->id),
                'amount'      => $exact['total'],
                'expires_at'  => $this->spei->expiresAt(),
                'metadata'    => array_merge($payment->metadata ?? [], [
                    'base_amount'         => $exact['base'],
                    'discriminator_cents' => $exact['cents'],
                ]),
            ]);
        }

        PaymentEvent::log((int) $payment->id, 'created', 'customer', (string) $user->id, [
            'plan'        => $plan->slug,
            'method'      => $request->payment_method,
            'base_amount' => $base,
            'amount'      => (float) $payment->amount,
            'public_code' => $payment->public_code,
            'expires_at'  => $payment->expires_at ? (string) $payment->expires_at : null,
            'has_receipt' => (bool) $receiptPath,
        ]);

        if ($isSpei) {
            $this->enviarInstrucciones($payment, $plan, $user);

            return redirect()
                ->route('membership.pay', ['code' => $payment->public_code])
                ->with('success', 'Orden creada. Transfiere el monto exacto dentro de las próximas '
                                  . $this->spei->windowHours() . ' horas.');
        }

        return redirect()->route('membership.status')
                         ->with('success', '¡Solicitud enviada! La revisaremos en menos de 24 horas.');
    }

    /**
     * Pantalla de instrucciones SPEI: CLABE, monto exacto, referencia y
     * temporizador de la ventana de pago.
     */
    public function pay(Request $request, string $code)
    {
        $payment = MembershipPayment::where('public_code', $code)->firstOrFail();

        abort_unless((string) $payment->user_id === (string) $request->user()->id, 403);

        $this->expireIfNeeded($payment);
        $payment->refresh();

        if (! in_array($payment->status, ['pending', 'under_review'], true)) {
            return redirect()->route('membership.status')
                             ->with('info', 'Esta orden ya no está activa.');
        }

        $plan = MembershipPlan::where('slug', $payment->requested_membership)->first();

        return view('memberships.pay', [
            'user'    => $request->user(),
            'payment' => $payment,
            'plan'    => $plan,
            'spei'    => $this->spei->instructions($payment),
        ]);
    }

    /**
     * Devuelve al usuario a su orden SPEI abierta más reciente. Es el camino
     * de regreso cuando cierra la pantalla de instrucciones.
     */
    public function payCurrent(Request $request)
    {
        $payment = MembershipPayment::where('user_id', $request->user()->id)
            ->whereIn('status', ['pending', 'under_review'])
            ->whereNotNull('public_code')
            ->latest()
            ->first();

        if (! $payment) {
            return redirect()->route('membership.index')
                             ->with('info', 'No tienes ninguna orden de pago abierta.');
        }

        return redirect()->route('membership.pay', ['code' => $payment->public_code]);
    }

    /**
     * Cierra el ciclo SPEI: el usuario sube el comprobante y/o la clave de
     * rastreo, y la orden pasa a revisión.
     */
    public function attachProof(Request $request, string $code)
    {
        $request->validate([
            'payment_reference' => ['nullable', 'string', 'max:200'],
            'bank_tracking_key' => ['nullable', 'string', 'max:64'],
            'receipt'           => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $payment = MembershipPayment::where('public_code', $code)->firstOrFail();
        abort_unless((string) $payment->user_id === (string) $request->user()->id, 403);

        $this->expireIfNeeded($payment);
        $payment->refresh();

        if (in_array($payment->status, ['approved', 'rejected'], true)) {
            return redirect()->route('membership.status');
        }

        if ($payment->status === 'expired') {
            return redirect()->route('membership.index')
                             ->with('error', 'La ventana de pago venció. Crea una orden nueva.');
        }

        $cambios = [];

        if ($request->hasFile('receipt')) {
            $cambios['receipt_path'] = $request->file('receipt')->store('receipts', 'private');
        }
        if ($request->filled('payment_reference')) {
            $cambios['payment_reference'] = $request->payment_reference;
        }
        if ($request->filled('bank_tracking_key')) {
            $cambios['bank_tracking_key'] = strtoupper(trim($request->bank_tracking_key));
        }

        if (! $cambios) {
            return back()->with('error', 'Adjunta el comprobante o escribe la referencia de tu transferencia.');
        }

        $cambios['status'] = 'under_review';

        try {
            $payment->update($cambios);
        } catch (\Illuminate\Database\QueryException $e) {
            // 23505 = unique_violation (índice membership_payments_tracking_key)
            if (($e->errorInfo[0] ?? null) === '23505') {
                return back()->with('error', 'Esa clave de rastreo ya está registrada en otro pago. Verifícala.');
            }
            throw $e;
        }

        PaymentEvent::log((int) $payment->id, 'proof_uploaded', 'customer', (string) $request->user()->id, [
            'has_receipt' => isset($cambios['receipt_path']),
            'reference'   => $cambios['payment_reference'] ?? $payment->payment_reference,
        ]);
        PaymentEvent::log((int) $payment->id, 'review_started', 'system', null, []);

        return redirect()->route('membership.status')
                         ->with('success', '¡Listo! Recibimos tu comprobante y lo revisaremos en menos de 24 horas.');
    }

    /**
     * Página de estado: historial de solicitudes del usuario.
     */
    public function status(Request $request)
    {
        $user = $request->user();

        // Barrido perezoso: vence las órdenes SPEI cuya ventana ya expiró.
        MembershipPayment::where('user_id', $user->id)
            ->where('status', 'pending')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->get()
            ->each(fn ($p) => $this->expireIfNeeded($p));

        $payments = MembershipPayment::where('user_id', $user->id)
                        ->orderByDesc('created_at')
                        ->paginate(10);

        return view('memberships.status', [
            'user'     => $user,
            'payments' => $payments,
        ]);
    }

    /**
     * Envía al socio las instrucciones de transferencia por correo.
     *
     * Nunca interrumpe la creación de la orden: si el correo falla (SMTP caído,
     * MAIL_MAILER mal configurado), el usuario igual llega a la pantalla con la
     * CLABE y queda un aviso en el log. El envío se registra en la bitácora.
     */
    private function enviarInstrucciones(MembershipPayment $payment, ?MembershipPlan $plan, $user): void
    {
        $destino = $payment->email ?: ($user->email ?? null);

        if (! $destino) {
            Log::warning('Orden SPEI sin correo de destino', ['payment_id' => $payment->id]);
            return;
        }

        try {
            Mail::to($destino)->send(new InstruccionesPagoSpei(
                $payment,
                $this->spei->instructions($payment),
                $plan?->name,
                $user->username ?? null,
            ));

            PaymentEvent::log((int) $payment->id, 'email_sent', 'system', null, [
                'to'       => $destino,
                'template' => 'emails.instrucciones-spei',
            ]);
        } catch (\Throwable $e) {
            Log::warning('No se pudieron enviar las instrucciones SPEI', [
                'payment_id' => $payment->id,
                'to'         => $destino,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    /**
     * Marca como vencida una orden cuya ventana de pago terminó.
     * Solo aplica a 'pending': si ya subió comprobante está en revisión.
     */
    private function expireIfNeeded(MembershipPayment $payment): void
    {
        if ($payment->status !== 'pending' || ! $payment->expires_at) {
            return;
        }

        if ($payment->expires_at->isFuture()) {
            return;
        }

        $payment->update(['status' => 'expired']);

        PaymentEvent::log((int) $payment->id, 'expired', 'system', null, [
            'expires_at' => (string) $payment->expires_at,
        ]);
    }
}