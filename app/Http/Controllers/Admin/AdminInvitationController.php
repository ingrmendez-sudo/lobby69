<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\InvitationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminInvitationController extends Controller
{
    protected InvitationService $invitationService;

    public function __construct(InvitationService $invitationService)
    {
        $this->invitationService = $invitationService;
    }

    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');
        $search = $request->get('search', '');
        $tipo   = $request->get('tipo_perfil', '');

        $query = DB::table('invitation_requests')->orderBy('created_at', 'desc');

        if ($status !== 'all') {
            $query->where('status', $status);
        }
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%");
            });
        }
        if (!empty($tipo)) {
            $query->where('tipo_perfil', $tipo);
        }

        $solicitudes = $query->paginate(20)->withQueryString();

        $contadores = [
            'pending'  => DB::table('invitation_requests')->where('status', 'pending')->count(),
            'approved' => DB::table('invitation_requests')->where('status', 'approved')->count(),
            'rejected' => DB::table('invitation_requests')->where('status', 'rejected')->count(),
            'total'    => DB::table('invitation_requests')->count(),
        ];

        return view('admin.invitations.index', compact('solicitudes', 'contadores', 'status', 'search', 'tipo'));
    }

    public function show(string $id)
    {
        $solicitud = DB::table('invitation_requests')->where('id', $id)->first();
        if (!$solicitud) {
            return redirect()->route('admin.invitations.index')->with('error', 'Solicitud no encontrada.');
        }
        $preferencias = $solicitud->preferencias ? json_decode($solicitud->preferencias, true) : [];
        return view('admin.invitations.show', compact('solicitud', 'preferencias'));
    }

    public function approve(Request $request, string $id)
    {
        $request->validate(['admin_notes' => ['nullable', 'string', 'max:500']]);

        $solicitud = DB::table('invitation_requests')->where('id', $id)->first();
        if (!$solicitud) {
            return back()->withErrors(['error' => 'Solicitud no encontrada.']);
        }
        if ($solicitud->status !== 'pending') {
            return back()->withErrors(['error' => 'Esta solicitud ya fue procesada.']);
        }

        try {
            $this->invitationService->approveInvitation(
                $solicitud,
                auth()->id(),
                $request->input('admin_notes')
            );
            return redirect()->route('admin.invitations.index')
                ->with('success', "Solicitud de {$solicitud->nombre} aprobada correctamente.");
        } catch (\Exception $e) {
            Log::error('Error al aprobar invitacion: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function reject(Request $request, string $id)
    {
        $request->validate([
            'admin_notes' => ['required', 'string', 'max:500', 'min:10'],
        ], [
            'admin_notes.required' => 'Debes indicar el motivo del rechazo.',
            'admin_notes.min'      => 'El motivo debe tener al menos 10 caracteres.',
        ]);

        $solicitud = DB::table('invitation_requests')->where('id', $id)->first();
        if (!$solicitud) {
            return back()->withErrors(['error' => 'Solicitud no encontrada.']);
        }
        if ($solicitud->status !== 'pending') {
            return back()->withErrors(['error' => 'Esta solicitud ya fue procesada.']);
        }

        DB::table('invitation_requests')->where('id', $id)->update([
            'status'      => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => Carbon::now(),
            'admin_notes' => $request->input('admin_notes'),
            'updated_at'  => Carbon::now(),
        ]);

        return redirect()->route('admin.invitations.index')
            ->with('success', "Solicitud de {$solicitud->nombre} rechazada.");
    }
}