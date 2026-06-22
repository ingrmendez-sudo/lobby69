<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PasswordChangeController extends Controller
{
    public function show()
    {
        $user = DB::table('users')->where('id', auth()->id())->first();
        if ($user && $user->password_changed) {
            return redirect()->route('dashboard');
        }
        return view('auth.password-change');
    }

    public function store(Request $request)
    {
        $request->validate([
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                function ($attribute, $value, $fail) {
                    if (!preg_match('/[A-Z]/', $value)) {
                        $fail('Debe tener al menos una mayúscula.');
                    }
                    if (!preg_match('/[a-z]/', $value)) {
                        $fail('Debe tener al menos una minúscula.');
                    }
                    if (!preg_match('/[0-9]/', $value)) {
                        $fail('Debe tener al menos un número.');
                    }
                },
            ],
            'password_confirmation' => 'required',
        ]);

        $userId = auth()->id();
        $user   = DB::table('users')->where('id', $userId)->first();

        if (!$user) {
            return back()->withErrors(['password' => 'Usuario no encontrado.']);
        }

        if (Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'password' => 'La nueva contraseña debe ser diferente a la contraseña temporal.'
            ]);
        }

        // Usar DB::table directamente para evitar problemas de fillable
        $updated = DB::table('users')->where('id', $userId)->update([
            'password'            => Hash::make($request->password),
            'password_changed'    => true,
            'password_changed_at' => Carbon::now()->toDateTimeString(),
            'updated_at'          => Carbon::now()->toDateTimeString(),
        ]);

        if (!$updated) {
            return back()->withErrors(['password' => 'Error al guardar. Intenta de nuevo.']);
        }

        // Refrescar la sesion del usuario
        auth()->setUser(
            \App\Models\User::find($userId)
        );

        return redirect()->route('dashboard')
            ->with('success', '✅ Contraseña actualizada. ¡Bienvenido a LOBBY69!');
    }
}