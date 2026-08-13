<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\InvitationRequest;
use App\Models\ReferralCode;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class InvitationController extends Controller
{
    public function show(\Illuminate\Http\Request $request)
    {
        $refCode = $request->query('ref');
        return view('auth.invitation-request', compact('refCode'));
    }

    public function store(InvitationRequest $request)
    {
        $data = $request->validated();

        DB::beginTransaction();

        try {
            $referredByUserId = null;
            $status = 'pending';

            if (!empty($data['invitation_code'])) {
                $referralCode = ReferralCode::where('code', $data['invitation_code'])->first();
                if ($referralCode && $referralCode->isValid()) {
                    $status = 'approved';
                    $referredByUserId = $referralCode->owner_user_id;
                    $referralCode->increment('uses_count');
                }
            }

            $generoMap = [
                'single'    => 'masculino',
                'unicornio' => 'femenino',
                'pareja'    => 'otro',
            ];
            $genero = $generoMap[$data['tipo_perfil']] ?? 'otro';

            $preferencias = json_encode([
                'edad'                => (int) ($data['edad'] ?? 0),
                'pais'                => $data['pais'] ?? 'Mexico',
                'municipio'           => $data['municipio'] ?? '',
                'terminos_aceptados'  => DB::raw('true'),
                'privacidad_aceptada' => DB::raw('true'),
            ]);

            DB::table('invitation_requests')->insert([
                'id'                  => (string) Str::uuid(),
                'nombre'              => $data['nombre_completo'],
                'email'               => $data['email'],
                'genero'              => $genero,
                'tipo_perfil'         => $data['tipo_perfil'],
                'motivo'              => $data['motivo'],
                'entidad'             => $data['estado'],
                'preferencias'        => $preferencias,
                'invitation_code'     => $data['invitation_code'] ?? null,
                'referred_by_user_id' => $referredByUserId,
                'status'              => $status,
                'terminos_aceptados'  => DB::raw('true'),
                'privacidad_aceptada' => DB::raw('true'),
                'created_at'          => Carbon::now(),
                'updated_at'          => Carbon::now(),
            ]);

            DB::commit();

            Log::info('Invitacion guardada', ['email' => $data['email'], 'status' => $status]);

            $mensaje = $status === 'approved'
                ? 'Codigo valido. En breve recibiras un correo con tus credenciales.'
                : 'Solicitud enviada. Te contactaremos a ' . $data['email'] . ' pronto.';

            return redirect()->route('invitation.success')->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar invitacion: ' . $e->getMessage());
            return back()
                ->withErrors(['error' => 'Error: ' . $e->getMessage()])
                ->withInput();
        }
    }
}

