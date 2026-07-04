<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminMembershipController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');

        $payments = DB::table('membership_payments')
            ->joinSub(
                DB::table('users')->selectRaw('"id"::text as uid, "username", "email", "membership_type"'),
                'u', 'membership_payments.user_id', '=', 'u.uid'
            )
            ->leftJoinSub(
                DB::table('profiles')->selectRaw('"user_id"::text as pid, "nickname", "display_name"'),
                'p', 'membership_payments.user_id', '=', 'p.pid'
            )
            ->where('membership_payments.status', $status)
            ->select('membership_payments.*', 'u.username', 'u.email', 'u.membership_type',
                     'p.nickname', 'p.display_name')
            ->orderByDesc('membership_payments.created_at')
            ->paginate(20);

        $counts = [
            'pending'  => DB::table('membership_payments')->where('status','pending')->count(),
            'approved' => DB::table('membership_payments')->where('status','approved')->count(),
            'rejected' => DB::table('membership_payments')->where('status','rejected')->count(),
        ];

        return view('admin.memberships.index', compact('payments', 'status', 'counts'));
    }

    public function approve(Request $request, $id)
    {
        $payment = DB::table('membership_payments')->where('id', $id)->first();
        abort_if(!$payment, 404);

        DB::table('membership_payments')->where('id', $id)->update([
            'status'      => 'approved',
            'reviewed_by' => (string) auth()->id(),
            'reviewed_at' => now(),
        ]);

        DB::table('users')->whereRaw('id::text = ?', [$payment->user_id])->update([
            'membership_type' => $payment->requested_membership,
        ]);

        return back()->with('success', 'Pago aprobado y membresía actualizada.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string|max:500']);

        DB::table('membership_payments')->where('id', $id)->update([
            'status'      => 'rejected',
            'admin_note'  => $request->reason,
            'reviewed_by' => (string) auth()->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Pago rechazado.');
    }
}
