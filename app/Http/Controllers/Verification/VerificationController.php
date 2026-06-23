<?php
namespace App\Http\Controllers\Verification;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VerificationController extends Controller
{
    public function show()
    {
        $user = DB::table('users')->where('id', auth()->id())->first();
        $profile = DB::table('profiles')->whereRaw('user_id::text = ?', [auth()->id()])->first();

        // Ver intentos previos
        $lastVerification = DB::table('verifications')
            ->whereRaw('user_id::text = ?', [auth()->id()])
            ->orderBy('created_at', 'desc')
            ->first();

        $attemptNumber = $lastVerification ? ($lastVerification->attempt_number + 1) : 1;
        $canRetry = !$lastVerification || $lastVerification->status === 'rejected';

        return view('verification.show', compact(
            'user', 'profile', 'lastVerification', 'attemptNumber', 'canRetry'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'selfie' => 'required|image|mimes:jpeg,jpg,png|max:5120',
        ], [
            'selfie.required' => 'Debes subir una foto de verificación.',
            'selfie.image'    => 'El archivo debe ser una imagen.',
            'selfie.mimes'    => 'Solo se aceptan formatos JPG o PNG.',
            'selfie.max'      => 'La imagen no debe superar 5MB.',
        ]);

        $userId = auth()->id();

        // Obtener número de intento
        $lastVerification = DB::table('verifications')
            ->whereRaw('user_id::text = ?', [$userId])
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastVerification && $lastVerification->status === 'pending') {
            return back()->with('warning', 'Ya tienes una verificación pendiente de revisión. El equipo la revisará en 24-48 horas.');
        }

        $attemptNumber = $lastVerification ? ($lastVerification->attempt_number + 1) : 1;

        // Guardar imagen
        $file      = $request->file('selfie');
        $filename  = 'verify_' . $userId . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path      = $file->storeAs('verifications', $filename, 'private');

        // Insertar registro
        DB::table('verifications')->insert([
            'user_id'        => $userId,
            'selfie_path'    => $path,
            'status'         => 'pending',
            'attempt_number' => $attemptNumber,
            'created_at'     => Carbon::now(),
            'updated_at'     => Carbon::now(),
        ]);

        // Actualizar estado del usuario
        DB::table('users')
            ->where('id', $userId)
            ->update([
                'verification_status' => 'pending',
                'updated_at'          => Carbon::now(),
            ]);

        return redirect()->route('verification.pending')
            ->with('success', '¡Foto enviada! El equipo de LOBBY69 la revisará en las próximas 24-48 horas.');
    }

    public function pending()
    {
        $user = DB::table('users')->where('id', auth()->id())->first();
        $verification = DB::table('verifications')
            ->whereRaw('user_id::text = ?', [auth()->id()])
            ->orderBy('created_at', 'desc')
            ->first();

        return view('verification.pending', compact('user', 'verification'));
    }

    public function status()
    {
        $user = DB::table('users')->where('id', auth()->id())->first();
        $verifications = DB::table('verifications')
            ->whereRaw('user_id::text = ?', [auth()->id()])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('verification.status', compact('user', 'verifications'));
    }
}