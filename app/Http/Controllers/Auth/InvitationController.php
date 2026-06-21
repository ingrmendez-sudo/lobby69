<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\InvitationRequest;
use App\Services\SupabaseService;
use Carbon\Carbon;

class InvitationController extends Controller
{
    protected SupabaseService $supabase;

    public function __construct(SupabaseService $supabase)
    {
        $this->supabase = $supabase;
    }

    public function show()
    {
        return view('auth.invitation-request');
    }

    public function store(InvitationRequest $request)
    {
        $data = $request->validated();

        // Insertar en Supabase (tabla invitation_requests)
        $result = $this->supabase->insert('invitation_requests', [
            'nombre_completo' => $data['nombre_completo'],
            'email' => $data['email'],
            'edad' => (int) $data['edad'],
            'pais' => $data['pais'],
            'estado' => $data['estado'],
            'municipio' => $data['municipio'],
            'tipo_perfil' => $data['tipo_perfil'],
            'motivo' => $data['motivo'],
            'status' => 'pending',
            'terminos_aceptados' => true,
            'privacidad_aceptada' => true,
            'created_at' => Carbon::now()->toIso8601String(),
        ]);

        if (!$result) {
            return back()->withErrors(['error' => 'Error al enviar la solicitud. Intenta de nuevo.'])
                ->withInput();
        }

        return redirect()->route('landing')
            ->with('success', '¡Solicitud enviada! Te contactaremos pronto para activar tu cuenta.');
    }
}
