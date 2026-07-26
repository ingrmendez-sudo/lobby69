<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\MembershipPlan;
use App\Models\MembershipPayment;
use App\Services\MembershipAccessService;

class MembershipController extends Controller
{
    public function __construct(
        private MembershipAccessService $access
    ) {}

    /**
     * Página principal: muestra todos los planes y el estado actual del usuario.
     */
    public function index(Request $request)
    {
        $user    = $request->user();
        $plans   = MembershipPlan::where('is_active', true)
                      ->orderBy('sort_order')
                      ->get();

        // Pago pendiente activo (si existe)
        $pendingPayment = MembershipPayment::where('user_id', $user->id)
                            ->where('status', 'pending')
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
        $plan = $slug ? MembershipPlan::where('slug', $slug)->where('is_active', true)->first() : null;

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
                    ->where('is_active', true)
                    ->firstOrFail();

        // Verificar que no tenga ya una solicitud pendiente para este plan
        $exists = MembershipPayment::where('user_id', $user->id)
                    ->where('requested_membership', $plan->slug)
                    ->where('status', 'pending')
                    ->exists();

        if ($exists) {
            return back()->with('error', 'Ya tienes una solicitud pendiente para este plan. Espera a que sea revisada.');
        }

        // Subir comprobante si se adjuntó
        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')
                              ->store('receipts', 'public');
        }

        MembershipPayment::create([
            'user_id'              => $user->id,
            'requested_membership' => $plan->slug,
            'current_membership'   => $user->membership_type ?? 'invitado',
            'amount'               => $plan->price,
            'currency'             => 'MXN',
            'payment_method'       => $request->payment_method,
            'payment_reference'    => $request->payment_reference,
            'receipt_path'         => $receiptPath,
            'status'               => 'pending',
        ]);

        return redirect()->route('membership.status')
                         ->with('success', '¡Solicitud enviada! La revisaremos en menos de 24 horas.');
    }

    /**
     * Página de estado: historial de solicitudes del usuario.
     */
    public function status(Request $request)
    {
        $user     = $request->user();
        $payments = MembershipPayment::where('user_id', $user->id)
                        ->orderByDesc('created_at')
                        ->paginate(10);

        return view('memberships.status', [
            'user'     => $user,
            'payments' => $payments,
        ]);
    }
}
