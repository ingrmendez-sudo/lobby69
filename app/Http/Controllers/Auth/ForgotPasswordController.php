<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    public function show()
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'El email es obligatorio.',
            'email.email'    => 'Ingresa un email válido.',
        ]);

        $user = DB::table('users')->where('email', $request->email)->first();

        // Siempre mostrar el mismo mensaje (seguridad)
        if (!$user) {
            return back()->with('success', '📧 Si el email existe, recibirás instrucciones en breve.');
        }

        // Generar token
        $token = Str::random(64);
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'email'      => $request->email,
                'token'      => hash('sha256', $token),
                'created_at' => Carbon::now(),
            ]
        );

        $resetUrl = url('/reset-password/' . $token . '?email=' . urlencode($request->email));

        // Log del link (en produccion seria un email)
        Log::channel('single')->info('=== LOBBY69 RESET PASSWORD ===', [
            'email'     => $request->email,
            'reset_url' => $resetUrl,
            'expires'   => Carbon::now()->addHour()->toDateTimeString(),
            'message'   => "Link de recuperacion para {$request->email}: {$resetUrl}",
        ]);

        return back()->with('success', '📧 Si el email existe, recibirás instrucciones en breve. (Dev: revisa storage/logs/laravel.log)');
    }

    public function showReset(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'token'    => 'required',
            'password' => [
                'required', 'string', 'min:8', 'confirmed',
                function ($attribute, $value, $fail) {
                    if (!preg_match('/[A-Z]/', $value)) $fail('Debe tener al menos una mayúscula.');
                    if (!preg_match('/[a-z]/', $value)) $fail('Debe tener al menos una minúscula.');
                    if (!preg_match('/[0-9]/', $value)) $fail('Debe tener al menos un número.');
                },
            ],
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record || !hash_equals($record->token, hash('sha256', $request->token))) {
            return back()->withErrors(['email' => 'Token inválido o expirado.']);
        }

        if (Carbon::parse($record->created_at)->addHour()->isPast()) {
            return back()->withErrors(['email' => 'El link ha expirado. Solicita uno nuevo.']);
        }

        DB::table('users')->where('email', $request->email)->update([
            'password'            => \Illuminate\Support\Facades\Hash::make($request->password),
            'password_changed'    => true,
            'password_changed_at' => Carbon::now()->toDateTimeString(),
            'updated_at'          => Carbon::now()->toDateTimeString(),
        ]);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')
            ->with('success', '✅ Contraseña actualizada. Ya puedes iniciar sesión.');
    }
}