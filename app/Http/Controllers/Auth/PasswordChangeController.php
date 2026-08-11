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
        // Si ya cambio contrasena, redirigir al dashboard
        if (auth()->user()->password_changed) {
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
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
            ],
        ], [
            'password.required'  => 'La contraseña es obligatoria.',
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.regex'     => 'La contraseña debe tener mayusculas, minusculas y numeros.',
        ]);

        $user = auth()->user();

        DB::table('users')
            ->where('id', $user->id)
            ->update([
                'password'            => Hash::make($request->password),
                'password_changed'    => DB::raw('true'),
                'password_changed_at' => Carbon::now(),
                'updated_at'          => Carbon::now(),
            ]);

        // Cerrar sesion y redirigir al login con mensaje
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Contrasena actualizada correctamente. Inicia sesion con tu nueva contrasena.');
    }
}