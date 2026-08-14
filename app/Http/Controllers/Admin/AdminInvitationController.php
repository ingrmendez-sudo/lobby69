<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Services\InvitationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminInvitationController extends Controller
{
    public function __construct(private InvitationService $svc) {}

    public function index(Request $request)
    {
        $status  = $request->get('status', 'all');
        $search  = $request->get('search', '');

        $query = DB::table('invitation_requests')->orderBy('created_at', 'desc');

        if ($status !== 'all') {
            $query->where('status', $status);
        }
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('email', 'ilike', "%{$search}%")
                  ->orWhere('nombre', 'ilike', "%{$search}%");
            });
        }

        $invitations = $query->paginate(20);

        $counts = [
            'pending'  => DB::table('invitation_requests')->where('status','pending')->count(),
            'approved' => DB::table('invitation_requests')->where('status','approved')->count(),
            'rejected' => DB::table('invitation_requests')->where('status','rejected')->count(),
            'total'    => DB::table('invitation_requests')->count(),
        ];

        return view('admin.invitations.index', compact('invitations','counts','status','search'));
    }

    public function show(string $id)
    {
        $invitation = DB::table('invitation_requests')->where('id', $id)->first();
        abort_if(!$invitation, 404);
        return view('admin.invitations.show', compact('invitation'));
    }

    public function approve(Request $request, string $id)
    {
        $result = $this->svc->approve($id, auth()->id());
        if ($result['success']) {
            return redirect()->route('admin.invitations.index')
                ->with('success', "✅ {$result['email']} aprobado. Contraseña temporal enviada al log.");
        }
        return back()->with('error', $result['message']);
    }

    public function reject(Request $request, string $id)
    {
        $request->validate(['reason' => 'nullable|string|max:500']);
        $result = $this->svc->reject($id, auth()->id(), $request->reason ?? '');
        if ($result['success']) {
            return redirect()->route('admin.invitations.index')
                ->with('success', '❌ Solicitud rechazada.');
        }
        return back()->with('error', $result['message']);
    }
}
