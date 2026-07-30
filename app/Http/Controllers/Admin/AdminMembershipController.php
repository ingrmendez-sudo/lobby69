<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminMembershipController extends Controller
{
    // ──────────────────────────────────────────────────────────────────────
    // Lista de pagos filtrada por status + stats generales
    // ──────────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');

        $payments = DB::table('membership_payments')
            ->join(
                DB::raw('(SELECT id::text as uid, username, email, membership_type FROM users) as u'),
                DB::raw('membership_payments.user_id::text'), '=', 'u.uid'
            )
            ->leftJoin(
                DB::raw('(SELECT user_id::text as pid, nickname, display_name FROM profiles) as p'),
                DB::raw('membership_payments.user_id::text'), '=', 'p.pid'
            )
            ->where('membership_payments.status', $status)
            ->select(
                'membership_payments.*',
                'u.username',
                'u.email',
                'u.membership_type',
                'p.nickname',
                'p.display_name'
            )
            ->orderByDesc('membership_payments.created_at')
            ->paginate(20);

        $counts = [
            'pending'  => DB::table('membership_payments')->where('status', 'pending')->count(),
            'approved' => DB::table('membership_payments')->where('status', 'approved')->count(),
            'rejected' => DB::table('membership_payments')->where('status', 'rejected')->count(),
        ];

        $stats = DB::table('membership_payments')
            ->selectRaw("
                count(*) as total,
                coalesce(sum(case when status = 'approved' then amount else 0 end), 0) as total_aprobado,
                coalesce(sum(case when status = 'pending'  then amount else 0 end), 0) as total_pendiente
            ")
            ->first();

        $plans = \App\Models\MembershipPlan::where('is_active', true)
                     ->orderBy('sort_order')
                     ->get();

        return view('admin.memberships.index', compact(
            'payments', 'status', 'counts', 'stats', 'plans'
        ));
    }

    // ──────────────────────────────────────────────────────────────────────
    // Aprobar pago → activa membresía via Membership::activateForUser()
    // ──────────────────────────────────────────────────────────────────────
    public function approve(Request $request, $id): \Illuminate\Http\RedirectResponse
    {
        $payment = \App\Models\MembershipPayment::findOrFail($id);

        if ($payment->status !== 'pending') {
            return back()->with('error', 'Este pago ya fue procesado.');
        }

        // 1. Marcar el pago como aprobado
        $payment->update([
            'status'      => 'approved',
            'reviewed_by' => auth()->user()->username ?? auth()->id(),
            'reviewed_at' => now(),
        ]);

        // 2. Resolver datos necesarios
        $user = \App\Models\User::findOrFail($payment->user_id);
        $plan = \App\Models\MembershipPlan::where('slug', $payment->requested_membership)->first();

        if ($plan) {
            // Calcular duración en días según el plan
            $durationDays = ($plan->slug === 'Fundador') ? null : ($plan->duration_days ?? 30);

            // Llamar correctamente: (string userId, string tier, ?int days, float price)
            \App\Models\Membership::activateForUser(
                (string) $user->id,
                (string) $plan->slug,
                $durationDays,
                (float)($payment->amount ?? ($plan->promo_active ? $plan->price_promo : $plan->price_normal) ?? 0),
                (string) ($payment->payment_method ?? 'manual'),
                (string) ($payment->payment_reference ?? '')
            );
        } else {
            // Fallback: plan no encontrado en membership_plans
            $expiry = ($payment->requested_membership === 'Fundador')
                ? null
                : now()->addDays(30);

            $user->update([
                'membership_type'       => $payment->requested_membership,
                'membership_expires_at' => $expiry,
                'membership_started_at' => now(),
            ]);
        }

        return redirect()
            ->route('admin.memberships.index', ['status' => 'pending'])
            ->with('success', "Membresía de {$user->username} activada como «{$payment->requested_membership}».");
    }


    // ──────────────────────────────────────────────────────────────────────
    // Rechazar pago con motivo opcional
    // ──────────────────────────────────────────────────────────────────────
    public function reject(Request $request, $id): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        DB::table('membership_payments')->where('id', $id)->update([
            'status'      => 'rejected',
            'admin_note'  => $request->reason ?? 'Rechazado por administrador.',
            'reviewed_by' => (string) auth()->id(),
            'reviewed_at' => now(),
            'updated_at'  => now(),
        ]);

        return back()->with('success', 'Pago rechazado.');
    }

    // ──────────────────────────────────────────────────────────────────────
    // Registrar pago manual desde el panel admin
    // Acepta UUID, email o username como user_id
    // ──────────────────────────────────────────────────────────────────────
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'user_id'              => ['required', 'string'],
            'requested_membership' => ['required', 'string'],
            'amount'               => ['nullable', 'numeric', 'min:0'],
            'currency'             => ['required', 'string', 'in:MXN,USD,EUR'],
            'payment_method'       => ['nullable', 'string'],
            'payment_reference'    => ['nullable', 'string', 'max:200'],
            'receipt'              => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        // Buscar usuario por UUID, email o username
        $input = trim($request->user_id);
        $user  = \App\Models\User::where('email', $input)
                     ->orWhere('username', $input)
                     ->orWhereRaw('"id"::text = ?', [$input])
                     ->first();

        if (! $user) {
            return back()
                ->withInput()
                ->withErrors(['user_id' => 'Usuario no encontrado. Verifica el email, username o UUID.']);
        }

        // Subir comprobante si se adjuntó
        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('receipts', 'public');
        }

        \App\Models\MembershipPayment::create([
            'user_id'              => $user->id,
            'requested_membership' => $request->requested_membership,
            'current_membership'   => $user->membership_type ?? 'invitado',
            'amount'               => $request->amount,
            'currency'             => $request->currency,
            'payment_method'       => $request->payment_method,
            'payment_reference'    => $request->payment_reference,
            'receipt_path'         => $receiptPath,
            'status'               => 'pending',
        ]);

        return redirect()
            ->route('admin.memberships.index', ['status' => 'pending'])
            ->with('success', "Pago manual registrado para {$user->username}.");
    }
    // ──────────────────────────────────────────────────────────────────────
    // Vista de gestión de planes y precios
    // ──────────────────────────────────────────────────────────────────────
    public function planes(): \Illuminate\View\View
    {
        $plans = \App\Models\MembershipPlan::orderBy('sort_order')->get();
        return view('admin.memberships.planes', compact('plans'));
    }

    // ──────────────────────────────────────────────────────────────────────
    // Actualizar precio y configuración de un plan
    // ──────────────────────────────────────────────────────────────────────
    public function updatePlan(Request $request, string $slug): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'price_promo'   => ['required', 'numeric', 'min:0'],
            'price_normal'  => ['required', 'numeric', 'min:0'],
            'duration_days' => ['nullable', 'integer', 'min:1'],
            'is_active'     => ['boolean'],
            'promo_active'  => ['boolean'],
            'description'   => ['nullable', 'string', 'max:500'],
        ]);

        $features = $request->input('features', []);
        $boolKeys = ['can_view_private_photos','can_video_call','can_see_visitors',
                      'can_send_friend_request','profile_boost','priority_support'];
        foreach ($boolKeys as $k) {
            $features[$k] = isset($features[$k]) && $features[$k] == '1';
        }

        \App\Models\MembershipPlan::where('slug', $slug)->update([
            'price_promo'   => $request->price_promo,
            'price_normal'  => $request->price_normal,
            'duration_days' => $request->duration_days,
            'is_active'     => $request->boolean('is_active'),
            'promo_active'  => $request->boolean('promo_active'),
            'description'   => $request->description,
            'features'      => json_encode($features),
            'updated_at'    => now(),
        ]);

        // Invalidar cache de usuarios con este plan
        $userIds = \DB::table('memberships')->where('tier', $slug)->where('status','active')->pluck('user_id');
        foreach ($userIds as $uid) {
            \App\Services\MembershipService::clearCache($uid);
        }

        return back()->with('success', "Plan «{$slug}» actualizado correctamente.");
    }

    // ──────────────────────────────────────────────────────────────────────
    // Toggle rápido de promoción activa/inactiva
    // ──────────────────────────────────────────────────────────────────────
    public function togglePromo(Request $request, string $slug): \Illuminate\Http\JsonResponse
    {
        $plan = \App\Models\MembershipPlan::where('slug', $slug)->firstOrFail();
        $plan->update(['promo_active' => !$plan->promo_active]);

        return response()->json([
            'ok'          => true,
            'promo_active' => $plan->fresh()->promo_active,
        ]);
    }
}

