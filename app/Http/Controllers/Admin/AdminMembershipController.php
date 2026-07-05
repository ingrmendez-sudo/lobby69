<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminMembershipController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');

        $payments = DB::table('membership_payments')
            ->join(
                DB::raw('(SELECT id::text as uid, username, email, membership_type FROM users) as u'),
                'membership_payments.user_id', '=', 'u.uid'
            )
            ->leftJoin(
                DB::raw('(SELECT user_id::text as pid, nickname, display_name FROM profiles) as p'),
                'membership_payments.user_id', '=', 'p.pid'
            )
            ->where('membership_payments.status', $status)
            ->select(
                'membership_payments.*',
                'u.username', 'u.email', 'u.membership_type',
                'p.nickname', 'p.display_name'
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
            ")->first();

        return view('admin.memberships.index', compact('payments', 'status', 'counts', 'stats'));
    }

    public function approve(Request $request, $id)
    {
        $payment = DB::table('membership_payments')->where('id', $id)->first();
        abort_if(!$payment, 404);

        DB::table('membership_payments')->where('id', $id)->update([
            'status'      => 'approved',
            'reviewed_by' => (string) auth()->id(),
            'reviewed_at' => now(),
            'updated_at'  => now(),
        ]);

        $expires = now()->addDays(30)->toDateTimeString();

        DB::table('users')->whereRaw('id::text = ?', [$payment->user_id])->update([
            'membership_type'       => $payment->requested_membership,
            'membership_expires_at' => $expires,
            'membership_started_at' => now(),
        ]);

        return back()->with('success', 'Pago aprobado y membresía actualizada correctamente.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate(['reason' => 'nullable|string|max:500']);

        DB::table('membership_payments')->where('id', $id)->update([
            'status'      => 'rejected',
            'admin_note'  => $request->reason ?? 'Rechazado por administrador.',
            'reviewed_by' => (string) auth()->id(),
            'reviewed_at' => now(),
            'updated_at'  => now(),
        ]);

        return back()->with('success', 'Pago rechazado.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id'              => 'required|string',
            'requested_membership' => 'required|string',
            'amount'               => 'nullable|numeric|min:0',
            'currency'             => 'nullable|string|max:10',
            'payment_method'       => 'nullable|string|max:50',
            'payment_reference'    => 'nullable|string|max:200',
            'receipt'              => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $receiptPath = null;
        if ($request->hasFile('receipt') && $request->file('receipt')->isValid()) {
            $receiptPath = $request->file('receipt')->store('receipts', 'public');
        }

        $user = DB::table('users')->whereRaw('id::text = ?', [$request->user_id])->first();

        DB::table('membership_payments')->insert([
            'user_id'              => $request->user_id,
            'requested_membership' => $request->requested_membership,
            'current_membership'   => $user->membership_type ?? 'free',
            'amount'               => $request->amount,
            'currency'             => $request->currency ?? 'MXN',
            'payment_method'       => $request->payment_method,
            'payment_reference'    => $request->payment_reference,
            'receipt_path'         => $receiptPath,
            'status'               => 'pending',
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        return back()->with('success', 'Pago registrado correctamente.');
    }
}
