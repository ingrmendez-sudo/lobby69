<?php
/**
 * make_fase5.php
 * Fase 5 — Sistema de Verificación de Identidad
 * Crea: Middleware, Controladores, Vistas, Rutas
 */

$files = [];

// ══════════════════════════════════════════════════════
// 1. MIDDLEWARE — CheckMembershipStatus
// Controla acceso según estado de membresía/trial
// ══════════════════════════════════════════════════════
$files['app/Http/Middleware/CheckMembershipStatus.php'] = <<<'PHP'
<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CheckMembershipStatus
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) return $next($request);

        $user = DB::table('users')->where('id', auth()->id())->first();
        if (!$user) return $next($request);

        // Admin siempre pasa
        if ($user->role === 'admin') return $next($request);

        // Rutas que siempre están permitidas
        $allowedRoutes = [
            'verification.show', 'verification.store',
            'membership.plans', 'membership.checkout',
            'profile.setup', 'profile.store',
            'password.change', 'password.change.store',
            'logout'
        ];
        if ($request->routeIs(...$allowedRoutes)) return $next($request);

        $membershipType = $user->membership_type ?? 'trial';
        $trialStarted   = $user->trial_started_at
                            ? Carbon::parse($user->trial_started_at)
                            : Carbon::now();
        $trialDays      = $trialStarted->diffInDays(Carbon::now());

        // TRIAL: más de 7 días sin verificar → bloquear
        if ($membershipType === 'trial' && $trialDays > 7) {
            return redirect()->route('verification.show')
                ->with('warning', 'Tu período de prueba ha expirado. Verifica tu identidad para continuar.');
        }

        // TRIAL_VERIFIED: más de 37 días (7 trial + 30 gratis) → membresía
        if ($membershipType === 'trial_verified') {
            $verifiedAt = $user->verified_at ? Carbon::parse($user->verified_at) : Carbon::now();
            if ($verifiedAt->diffInDays(Carbon::now()) > 30) {
                return redirect()->route('membership.plans')
                    ->with('warning', 'Tu mes gratuito ha terminado. Elige una membresía para continuar.');
            }
        }

        // EXPIRED
        if ($membershipType === 'expired') {
            return redirect()->route('membership.plans')
                ->with('warning', 'Tu membresía ha vencido. Renueva para continuar.');
        }

        // SUSPENDED / BANNED
        if (in_array($membershipType, ['suspended', 'banned'])) {
            auth()->logout();
            return redirect()->route('login')
                ->with('error', 'Tu cuenta ha sido suspendida. Contacta al administrador.');
        }

        return $next($request);
    }
}
PHP;

// ══════════════════════════════════════════════════════
// 2. CONTROLADOR — VerificationController
// ══════════════════════════════════════════════════════
$files['app/Http/Controllers/Verification/VerificationController.php'] = <<<'PHP'
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
        $profile = DB::table('profiles')->where('user_id', auth()->id())->first();

        // Ver intentos previos
        $lastVerification = DB::table('verifications')
            ->where('user_id', auth()->id())
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
            ->where('user_id', $userId)
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
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->first();

        return view('verification.pending', compact('user', 'verification'));
    }

    public function status()
    {
        $user = DB::table('users')->where('id', auth()->id())->first();
        $verifications = DB::table('verifications')
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('verification.status', compact('user', 'verifications'));
    }
}
PHP;

// ══════════════════════════════════════════════════════
// 3. CONTROLADOR ADMIN — AdminVerificationController
// ══════════════════════════════════════════════════════
$files['app/Http/Controllers/Admin/AdminVerificationController.php'] = <<<'PHP'
<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminVerificationController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');

        $verifications = DB::table('verifications as v')
            ->join('users as u', 'u.id', '=', 'v.user_id')
            ->leftJoin('profiles as p', 'p.user_id', '=', 'v.user_id')
            ->select(
                'v.*',
                'u.email',
                'u.name',
                'u.membership_type',
                'p.nickname',
                'p.profile_type',
                'p.city',
                'p.state'
            )
            ->where('v.status', $status)
            ->orderBy('v.created_at', 'asc')
            ->paginate(20);

        $counts = [
            'pending'  => DB::table('verifications')->where('status', 'pending')->count(),
            'approved' => DB::table('verifications')->where('status', 'approved')->count(),
            'rejected' => DB::table('verifications')->where('status', 'rejected')->count(),
        ];

        return view('admin.verifications.index', compact('verifications', 'counts', 'status'));
    }

    public function show($id)
    {
        $verification = DB::table('verifications as v')
            ->join('users as u', 'u.id', '=', 'v.user_id')
            ->leftJoin('profiles as p', 'p.user_id', '=', 'v.user_id')
            ->select('v.*', 'u.email', 'u.name', 'u.membership_type',
                     'u.trial_started_at', 'p.nickname', 'p.display_name',
                     'p.profile_type', 'p.gender', 'p.age', 'p.city', 'p.state')
            ->where('v.id', $id)
            ->first();

        if (!$verification) abort(404);

        return view('admin.verifications.show', compact('verification'));
    }

    public function approve(Request $request, $id)
    {
        $verification = DB::table('verifications')->where('id', $id)->first();
        if (!$verification) abort(404);

        DB::table('verifications')->where('id', $id)->update([
            'status'      => 'approved',
            'admin_note'  => $request->input('note', ''),
            'reviewed_by' => auth()->id(),
            'reviewed_at' => Carbon::now(),
            'updated_at'  => Carbon::now(),
        ]);

        // Actualizar usuario: verificado
        DB::table('users')->where('id', $verification->user_id)->update([
            'verification_status' => 'approved',
            'verified_at'         => Carbon::now(),
            'membership_type'     => 'trial_verified',
            'updated_at'          => Carbon::now(),
        ]);

        // Generar código de referido si no tiene
        $user = DB::table('users')->where('id', $verification->user_id)->first();
        if (!$user->referral_code) {
            $profile = DB::table('profiles')->where('user_id', $verification->user_id)->first();
            $nick    = $profile->nickname ?? 'user';
            $code    = strtoupper(substr($nick, 0, 4)) . rand(1000, 9999);
            DB::table('users')->where('id', $verification->user_id)
                ->update(['referral_code' => $code, 'updated_at' => Carbon::now()]);
        }

        return redirect()->route('admin.verifications.index')
            ->with('success', "✅ Verificación #{$id} aprobada. Usuario activado con mes gratuito.");
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'note' => 'required|min:10',
        ], [
            'note.required' => 'Debes indicar el motivo del rechazo.',
            'note.min'      => 'El motivo debe tener al menos 10 caracteres.',
        ]);

        $verification = DB::table('verifications')->where('id', $id)->first();
        if (!$verification) abort(404);

        DB::table('verifications')->where('id', $id)->update([
            'status'      => 'rejected',
            'admin_note'  => $request->input('note'),
            'reviewed_by' => auth()->id(),
            'reviewed_at' => Carbon::now(),
            'updated_at'  => Carbon::now(),
        ]);

        DB::table('users')->where('id', $verification->user_id)->update([
            'verification_status' => 'rejected',
            'updated_at'          => Carbon::now(),
        ]);

        return redirect()->route('admin.verifications.index')
            ->with('success', "Verificación #{$id} rechazada. El usuario fue notificado.");
    }
}
PHP;

// ══════════════════════════════════════════════════════
// 4. VISTA — verification/show.blade.php
// ══════════════════════════════════════════════════════
$files['resources/views/verification/show.blade.php'] = <<<'BLADE'
@extends('layouts.app')
@section('title', 'Verificar Identidad — LOBBY69')
@section('content')

<div style="max-width:680px;margin:2rem auto;padding:0 1rem;">

  {{-- Header --}}
  <div style="text-align:center;margin-bottom:2rem;">
    <div style="font-size:4rem;margin-bottom:1rem;">🛡️</div>
    <h1 style="font-size:1.8rem;font-weight:800;color:var(--color-text);">Verifica tu Identidad</h1>
    <p style="color:#64748b;font-size:1rem;max-width:480px;margin:0 auto;">
      LOBBY69 es una comunidad de personas reales. La verificación garantiza la seguridad y confianza de todos los miembros.
    </p>
  </div>

  {{-- Alerta si fue rechazado --}}
  @if($lastVerification && $lastVerification->status === 'rejected')
  <div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:1.25rem;border-radius:12px;margin-bottom:1.5rem;">
    <strong>⚠️ Tu verificación anterior fue rechazada</strong>
    <p style="margin:.5rem 0 0;font-size:.9rem;">
      Motivo: <em>{{ $lastVerification->admin_note ?? 'Sin nota adicional' }}</em>
    </p>
    <p style="margin:.5rem 0 0;font-size:.9rem;">Por favor lee las instrucciones y envía una nueva foto.</p>
  </div>
  @endif

  {{-- Mensajes --}}
  @if(session('warning'))
  <div style="background:#fef3c7;border:1px solid #f59e0b;color:#92400e;padding:1rem;border-radius:10px;margin-bottom:1.5rem;">
    ⚠️ {{ session('warning') }}
  </div>
  @endif

  @if($errors->any())
  <div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:1rem;border-radius:10px;margin-bottom:1.5rem;">
    @foreach($errors->all() as $e)<p style="margin:.2rem 0;">{{ $e }}</p>@endforeach
  </div>
  @endif

  {{-- Instrucciones --}}
  <div style="background:white;border-radius:16px;box-shadow:0 4px 16px rgba(0,0,0,.08);padding:2rem;margin-bottom:1.5rem;">
    <h2 style="font-size:1.1rem;font-weight:700;margin-bottom:1.25rem;">
      📋 Cómo tomar la foto de verificación
    </h2>

    <div style="display:grid;gap:1rem;">

      <div style="display:flex;gap:1rem;align-items:flex-start;">
        <div style="background:#8b5cf6;color:white;border-radius:50%;width:32px;height:32px;display:flex;align-items:center;justify-content:center;font-weight:700;flex-shrink:0;">1</div>
        <div>
          <strong>Escribe en un papel:</strong>
          <div style="background:#f8fafc;border:2px dashed #8b5cf6;border-radius:8px;padding:.75rem 1rem;margin-top:.5rem;font-family:monospace;font-size:1rem;text-align:center;color:#6d28d9;">
            LOBBY69 · @{{ 'Tu Nick' }} · {{ date('d/m/Y') }}
          </div>
          <p style="font-size:.85rem;color:#6b7280;margin-top:.4rem;">Escribe exactamente ese texto con bolígrafo, letra clara y legible.</p>
        </div>
      </div>

      <div style="display:flex;gap:1rem;align-items:flex-start;">
        <div style="background:#8b5cf6;color:white;border-radius:50%;width:32px;height:32px;display:flex;align-items:center;justify-content:center;font-weight:700;flex-shrink:0;">2</div>
        <div>
          <strong>Tómate una selfie</strong>
          <p style="font-size:.85rem;color:#6b7280;margin:.4rem 0 0;">Sostén el papel junto a tu rostro. Tu cara y el texto deben verse claramente en la misma foto.</p>
        </div>
      </div>

      <div style="display:flex;gap:1rem;align-items:flex-start;">
        <div style="background:#8b5cf6;color:white;border-radius:50%;width:32px;height:32px;display:flex;align-items:center;justify-content:center;font-weight:700;flex-shrink:0;">3</div>
        <div>
          <strong>Sube la foto</strong>
          <p style="font-size:.85rem;color:#6b7280;margin:.4rem 0 0;">Formatos aceptados: JPG o PNG. Máximo 5MB. La foto es confidencial y solo la ve el equipo de administración.</p>
        </div>
      </div>
    </div>

    {{-- Ejemplo visual --}}
    <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:10px;padding:1rem;margin-top:1.25rem;text-align:center;">
      <p style="font-size:.85rem;color:#166534;margin:0;">
        🔒 <strong>Confidencialidad garantizada:</strong> Tu foto de verificación nunca será publicada ni compartida. Se usa exclusivamente para validar que eres una persona real.
      </p>
    </div>
  </div>

  {{-- Formulario --}}
  @if($canRetry)
  <div style="background:white;border-radius:16px;box-shadow:0 4px 16px rgba(0,0,0,.08);padding:2rem;margin-bottom:1.5rem;">
    <h2 style="font-size:1.1rem;font-weight:700;margin-bottom:1.25rem;">
      📸 Subir foto de verificación
      @if($attemptNumber > 1)
        <span style="font-size:.8rem;color:#f59e0b;font-weight:400;">(Intento #{{ $attemptNumber }})</span>
      @endif
    </h2>

    <form method="POST" action="{{ route('verification.store') }}" enctype="multipart/form-data">
      @csrf

      {{-- Preview de imagen --}}
      <div id="previewContainer" style="display:none;margin-bottom:1rem;text-align:center;">
        <img id="previewImg" src="" alt="Preview"
             style="max-width:100%;max-height:300px;border-radius:10px;border:2px solid #8b5cf6;">
        <p style="font-size:.8rem;color:#6b7280;margin-top:.5rem;">Vista previa de tu foto</p>
      </div>

      <div style="border:2px dashed #e5e7eb;border-radius:10px;padding:2rem;text-align:center;cursor:pointer;transition:border-color .2s;"
           id="dropzone"
           onclick="document.getElementById('selfieInput').click()"
           ondragover="event.preventDefault();this.style.borderColor='#8b5cf6'"
           ondragleave="this.style.borderColor='#e5e7eb'"
           ondrop="handleDrop(event)">
        <div id="dropzoneContent">
          <div style="font-size:2.5rem;margin-bottom:.5rem;">📷</div>
          <p style="font-weight:600;color:#374151;margin:0;">Haz clic o arrastra tu foto aquí</p>
          <p style="font-size:.85rem;color:#9ca3af;margin:.25rem 0 0;">JPG o PNG · Máximo 5MB</p>
        </div>
        <input type="file" id="selfieInput" name="selfie" accept="image/jpeg,image/png"
               style="display:none;" onchange="previewImage(this)">
      </div>

      <button type="submit" id="submitBtn"
              style="width:100%;margin-top:1.25rem;padding:1rem;background:linear-gradient(135deg,#8b5cf6,#ec4899);color:white;border:none;border-radius:10px;font-size:1rem;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.5rem;">
        🛡️ Enviar para verificación
      </button>
    </form>
  </div>
  @endif

  {{-- Tiempo estimado --}}
  <div style="text-align:center;color:#9ca3af;font-size:.85rem;margin-bottom:2rem;">
    ⏱️ Tiempo de revisión estimado: <strong style="color:#6b7280;">24 a 48 horas</strong>
  </div>

</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        if (file.size > 5 * 1024 * 1024) {
            alert('La imagen supera los 5MB. Por favor elige una más pequeña.');
            input.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('previewContainer').style.display = 'block';
            document.getElementById('dropzoneContent').innerHTML =
                '<p style="color:#10b981;font-weight:600;">✅ ' + file.name + '</p><p style="font-size:.8rem;color:#9ca3af;">Haz clic para cambiar la foto</p>';
            document.getElementById('dropzone').style.borderColor = '#10b981';
        };
        reader.readAsDataURL(file);
    }
}

function handleDrop(e) {
    e.preventDefault();
    document.getElementById('dropzone').style.borderColor = '#e5e7eb';
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        const input = document.getElementById('selfieInput');
        const dt    = new DataTransfer();
        dt.items.add(files[0]);
        input.files = dt.files;
        previewImage(input);
    }
}

document.getElementById('submitBtn').addEventListener('click', function() {
    const input = document.getElementById('selfieInput');
    if (!input.files || input.files.length === 0) {
        alert('Por favor selecciona una foto antes de enviar.');
        return false;
    }
    this.innerHTML = '⏳ Enviando...';
    this.disabled = true;
});
</script>
@endsection
BLADE;

// ══════════════════════════════════════════════════════
// 5. VISTA — verification/pending.blade.php
// ══════════════════════════════════════════════════════
$files['resources/views/verification/pending.blade.php'] = <<<'BLADE'
@extends('layouts.app')
@section('title', 'Verificación Pendiente — LOBBY69')
@section('content')

<div style="max-width:560px;margin:4rem auto;padding:0 1rem;text-align:center;">

  <div style="font-size:5rem;margin-bottom:1.5rem;">⏳</div>
  <h1 style="font-size:1.8rem;font-weight:800;color:var(--color-text);">¡Foto recibida!</h1>
  <p style="color:#64748b;font-size:1rem;margin:1rem 0 2rem;">
    El equipo de LOBBY69 revisará tu verificación en las próximas
    <strong>24 a 48 horas</strong>. Te notificaremos por email cuando sea aprobada.
  </p>

  @if(session('success'))
  <div style="background:#d1fae5;border:1px solid #6ee7b7;color:#065f46;padding:1rem;border-radius:10px;margin-bottom:1.5rem;">
    ✅ {{ session('success') }}
  </div>
  @endif

  {{-- Estado actual --}}
  <div style="background:white;border-radius:16px;box-shadow:0 4px 16px rgba(0,0,0,.08);padding:2rem;margin-bottom:1.5rem;text-align:left;">
    <h3 style="font-size:1rem;font-weight:700;margin-bottom:1rem;">Estado de tu verificación</h3>

    <div style="display:flex;gap:1rem;align-items:center;padding:.75rem;background:#fef3c7;border-radius:8px;">
      <span style="font-size:1.5rem;">🔍</span>
      <div>
        <strong style="color:#92400e;">En revisión</strong>
        <p style="font-size:.85rem;color:#78350f;margin:.2rem 0 0;">
          Enviada el {{ $verification ? \Carbon\Carbon::parse($verification->created_at)->format('d/m/Y H:i') : 'ahora' }}
        </p>
      </div>
    </div>

    <div style="margin-top:1rem;padding:.75rem;background:#f8fafc;border-radius:8px;font-size:.85rem;color:#6b7280;">
      📧 Recibirás un email en <strong>{{ auth()->user()->email }}</strong> cuando tu verificación sea procesada.
    </div>
  </div>

  {{-- Qué puedes hacer mientras tanto --}}
  <div style="background:white;border-radius:16px;box-shadow:0 4px 16px rgba(0,0,0,.08);padding:2rem;margin-bottom:2rem;text-align:left;">
    <h3 style="font-size:1rem;font-weight:700;margin-bottom:1rem;">Mientras tanto puedes...</h3>
    <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:.75rem;">
      <li style="display:flex;gap:.75rem;align-items:center;">
        <span style="font-size:1.2rem;">👤</span>
        <span style="font-size:.9rem;color:#4b5563;"><a href="{{ route('profile.setup') }}" style="color:#8b5cf6;">Completar tu perfil</a> con más información</span>
      </li>
      <li style="display:flex;gap:.75rem;align-items:center;">
        <span style="font-size:1.2rem;">🏠</span>
        <span style="font-size:.9rem;color:#4b5563;"><a href="{{ route('dashboard') }}" style="color:#8b5cf6;">Explorar el dashboard</a> y conocer la plataforma</span>
      </li>
    </ul>
  </div>

  <a href="{{ route('dashboard') }}"
     style="display:inline-block;padding:.85rem 2rem;background:linear-gradient(135deg,#8b5cf6,#ec4899);color:white;border-radius:10px;font-weight:700;text-decoration:none;">
    Ir al Dashboard
  </a>

</div>
@endsection
BLADE;

// ══════════════════════════════════════════════════════
// 6. VISTA ADMIN — admin/verifications/index.blade.php
// ══════════════════════════════════════════════════════
$files['resources/views/admin/verifications/index.blade.php'] = <<<'BLADE'
@extends('layouts.app')
@section('title', 'Verificaciones — Admin LOBBY69')
@section('content')

<div style="max-width:1100px;margin:2rem auto;padding:0 1rem;">

  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;">
    <div>
      <h1 style="font-size:1.6rem;font-weight:800;color:var(--color-text);margin:0;">🛡️ Cola de Verificaciones</h1>
      <p style="color:#64748b;margin:.25rem 0 0;">Revisa y aprueba las solicitudes de verificación de identidad</p>
    </div>
    <a href="{{ route('admin.invitations.index') }}"
       style="padding:.6rem 1rem;border:1px solid #e5e7eb;border-radius:8px;font-size:.9rem;color:#6b7280;text-decoration:none;">
      ← Panel Admin
    </a>
  </div>

  {{-- Mensajes --}}
  @if(session('success'))
  <div style="background:#d1fae5;border:1px solid #6ee7b7;color:#065f46;padding:1rem;border-radius:10px;margin-bottom:1.5rem;">
    ✅ {{ session('success') }}
  </div>
  @endif

  {{-- Contadores --}}
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1.5rem;">
    <a href="{{ route('admin.verifications.index', ['status'=>'pending']) }}"
       style="background:white;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.06);padding:1.25rem;text-decoration:none;text-align:center;border-top:3px solid {{ $status==='pending'?'#f59e0b':'#e5e7eb' }};">
      <div style="font-size:2rem;font-weight:800;color:#f59e0b;">{{ $counts['pending'] }}</div>
      <div style="font-size:.85rem;color:#6b7280;">Pendientes</div>
    </a>
    <a href="{{ route('admin.verifications.index', ['status'=>'approved']) }}"
       style="background:white;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.06);padding:1.25rem;text-decoration:none;text-align:center;border-top:3px solid {{ $status==='approved'?'#10b981':'#e5e7eb' }};">
      <div style="font-size:2rem;font-weight:800;color:#10b981;">{{ $counts['approved'] }}</div>
      <div style="font-size:.85rem;color:#6b7280;">Aprobadas</div>
    </a>
    <a href="{{ route('admin.verifications.index', ['status'=>'rejected']) }}"
       style="background:white;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.06);padding:1.25rem;text-decoration:none;text-align:center;border-top:3px solid {{ $status==='rejected'?'#ef4444':'#e5e7eb' }};">
      <div style="font-size:2rem;font-weight:800;color:#ef4444;">{{ $counts['rejected'] }}</div>
      <div style="font-size:.85rem;color:#6b7280;">Rechazadas</div>
    </a>
  </div>

  {{-- Tabla --}}
  <div style="background:white;border-radius:16px;box-shadow:0 4px 16px rgba(0,0,0,.08);overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;">
      <thead>
        <tr style="background:#f8fafc;border-bottom:2px solid #f1f5f9;">
          <th style="padding:1rem;text-align:left;font-size:.8rem;color:#6b7280;font-weight:600;text-transform:uppercase;">#</th>
          <th style="padding:1rem;text-align:left;font-size:.8rem;color:#6b7280;font-weight:600;text-transform:uppercase;">Usuario</th>
          <th style="padding:1rem;text-align:left;font-size:.8rem;color:#6b7280;font-weight:600;text-transform:uppercase;">Tipo</th>
          <th style="padding:1rem;text-align:left;font-size:.8rem;color:#6b7280;font-weight:600;text-transform:uppercase;">Intento</th>
          <th style="padding:1rem;text-align:left;font-size:.8rem;color:#6b7280;font-weight:600;text-transform:uppercase;">Fecha</th>
          <th style="padding:1rem;text-align:left;font-size:.8rem;color:#6b7280;font-weight:600;text-transform:uppercase;">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse($verifications as $v)
        <tr style="border-bottom:1px solid #f1f5f9;transition:background .15s;" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='white'">
          <td style="padding:1rem;font-size:.85rem;color:#9ca3af;">{{ $v->id }}</td>
          <td style="padding:1rem;">
            <div style="font-weight:600;font-size:.9rem;color:#374151;">{{ $v->nickname ?? $v->name }}</div>
            <div style="font-size:.8rem;color:#9ca3af;">{{ $v->email }}</div>
            @if($v->city)
            <div style="font-size:.78rem;color:#9ca3af;">📍 {{ $v->city }}, {{ $v->state }}</div>
            @endif
          </td>
          <td style="padding:1rem;">
            <span style="background:#f3f4f6;color:#374151;padding:.2rem .7rem;border-radius:20px;font-size:.8rem;">
              {{ ucfirst($v->profile_type ?? 'N/A') }}
            </span>
          </td>
          <td style="padding:1rem;text-align:center;">
            <span style="background:{{ $v->attempt_number > 1 ? '#fef3c7' : '#f0fdf4' }};color:{{ $v->attempt_number > 1 ? '#92400e' : '#166534' }};padding:.2rem .7rem;border-radius:20px;font-size:.8rem;">
              #{{ $v->attempt_number }}
            </span>
          </td>
          <td style="padding:1rem;font-size:.85rem;color:#6b7280;">
            {{ \Carbon\Carbon::parse($v->created_at)->format('d/m/Y H:i') }}
          </td>
          <td style="padding:1rem;">
            <a href="{{ route('admin.verifications.show', $v->id) }}"
               style="display:inline-block;padding:.4rem .9rem;background:#8b5cf6;color:white;border-radius:6px;font-size:.8rem;text-decoration:none;font-weight:600;">
              Ver foto →
            </a>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="6" style="padding:3rem;text-align:center;color:#9ca3af;">
            No hay verificaciones {{ $status === 'pending' ? 'pendientes' : ($status === 'approved' ? 'aprobadas' : 'rechazadas') }}.
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
    @if($verifications->hasPages())
    <div style="padding:1rem;">{{ $verifications->links() }}</div>
    @endif
  </div>

</div>
@endsection
BLADE;

// ══════════════════════════════════════════════════════
// 7. VISTA ADMIN — admin/verifications/show.blade.php
// ══════════════════════════════════════════════════════
$files['resources/views/admin/verifications/show.blade.php'] = <<<'BLADE'
@extends('layouts.app')
@section('title', 'Revisar Verificación — Admin LOBBY69')
@section('content')

<div style="max-width:900px;margin:2rem auto;padding:0 1rem;">

  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;">
    <h1 style="font-size:1.4rem;font-weight:800;color:var(--color-text);margin:0;">
      🛡️ Verificación #{{ $verification->id }}
    </h1>
    <a href="{{ route('admin.verifications.index') }}"
       style="padding:.6rem 1rem;border:1px solid #e5e7eb;border-radius:8px;font-size:.9rem;color:#6b7280;text-decoration:none;">
      ← Volver a la cola
    </a>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">

    {{-- Foto de verificación --}}
    <div style="background:white;border-radius:16px;box-shadow:0 4px 16px rgba(0,0,0,.08);padding:1.5rem;">
      <h3 style="font-size:1rem;font-weight:700;margin-bottom:1rem;">📸 Foto enviada</h3>
      <img src="{{ route('admin.verifications.image', $verification->id) }}"
           alt="Foto de verificación"
           style="width:100%;border-radius:10px;border:2px solid #f1f5f9;"
           onerror="this.src='';this.alt='No se pudo cargar la imagen'">
      <p style="font-size:.8rem;color:#9ca3af;margin-top:.5rem;text-align:center;">
        Intento #{{ $verification->attempt_number }} ·
        {{ \Carbon\Carbon::parse($verification->created_at)->format('d/m/Y H:i') }}
      </p>
    </div>

    {{-- Datos del usuario --}}
    <div style="display:flex;flex-direction:column;gap:1rem;">

      <div style="background:white;border-radius:16px;box-shadow:0 4px 16px rgba(0,0,0,.08);padding:1.5rem;">
        <h3 style="font-size:1rem;font-weight:700;margin-bottom:1rem;">👤 Datos del usuario</h3>
        <table style="width:100%;font-size:.9rem;">
          <tr><td style="color:#9ca3af;padding:.3rem 0;">Nick</td><td style="font-weight:600;">{{ $verification->nickname ?? 'N/A' }}</td></tr>
          <tr><td style="color:#9ca3af;padding:.3rem 0;">Nombre</td><td>{{ $verification->display_name ?? $verification->name }}</td></tr>
          <tr><td style="color:#9ca3af;padding:.3rem 0;">Email</td><td>{{ $verification->email }}</td></tr>
          <tr><td style="color:#9ca3af;padding:.3rem 0;">Tipo</td><td>{{ ucfirst($verification->profile_type ?? 'N/A') }}</td></tr>
          <tr><td style="color:#9ca3af;padding:.3rem 0;">Género</td><td>{{ ucfirst($verification->gender ?? 'N/A') }}</td></tr>
          <tr><td style="color:#9ca3af;padding:.3rem 0;">Edad</td><td>{{ $verification->age ?? 'N/A' }} años</td></tr>
          <tr><td style="color:#9ca3af;padding:.3rem 0;">Ciudad</td><td>{{ $verification->city }}, {{ $verification->state }}</td></tr>
          <tr><td style="color:#9ca3af;padding:.3rem 0;">Estado</td><td>{{ ucfirst($verification->membership_type ?? 'trial') }}</td></tr>
        </table>
      </div>

      {{-- Acciones --}}
      @if($verification->status === 'pending')
      <div style="background:white;border-radius:16px;box-shadow:0 4px 16px rgba(0,0,0,.08);padding:1.5rem;">
        <h3 style="font-size:1rem;font-weight:700;margin-bottom:1rem;">⚡ Decisión</h3>

        {{-- Aprobar --}}
        <form method="POST" action="{{ route('admin.verifications.approve', $verification->id) }}" style="margin-bottom:1rem;">
          @csrf
          <input type="hidden" name="note" value="Verificación aprobada por el equipo de LOBBY69.">
          <button type="submit"
                  onclick="return confirm('¿Confirmas la aprobación de esta verificación?')"
                  style="width:100%;padding:.85rem;background:linear-gradient(135deg,#10b981,#059669);color:white;border:none;border-radius:10px;font-weight:700;cursor:pointer;font-size:.95rem;">
            ✅ Aprobar Verificación
          </button>
        </form>

        {{-- Rechazar --}}
        <form method="POST" action="{{ route('admin.verifications.reject', $verification->id) }}">
          @csrf
          <textarea name="note" rows="3" required placeholder="Motivo del rechazo (mínimo 10 caracteres)..."
                    style="width:100%;padding:.7rem;border:2px solid #e5e7eb;border-radius:8px;font-size:.9rem;margin-bottom:.75rem;box-sizing:border-box;resize:vertical;"></textarea>
          <button type="submit"
                  onclick="return confirm('¿Confirmas el rechazo? El usuario podrá reintentar.')"
                  style="width:100%;padding:.85rem;background:#fee2e2;color:#991b1b;border:2px solid #fca5a5;border-radius:10px;font-weight:700;cursor:pointer;font-size:.95rem;">
            ❌ Rechazar y notificar
          </button>
        </form>
      </div>
      @else
      <div style="background:white;border-radius:16px;box-shadow:0 4px 16px rgba(0,0,0,.08);padding:1.5rem;">
        <h3 style="font-size:1rem;font-weight:700;margin-bottom:.75rem;">Estado final</h3>
        <div style="background:{{ $verification->status==='approved'?'#d1fae5':'#fee2e2' }};padding:1rem;border-radius:8px;">
          <strong style="color:{{ $verification->status==='approved'?'#065f46':'#991b1b' }};">
            {{ $verification->status === 'approved' ? '✅ Aprobada' : '❌ Rechazada' }}
          </strong>
          @if($verification->admin_note)
          <p style="font-size:.85rem;margin:.5rem 0 0;color:#4b5563;">{{ $verification->admin_note }}</p>
          @endif
        </div>
      </div>
      @endif
    </div>
  </div>
</div>
@endsection
BLADE;

// ══════════════════════════════════════════════════════
// CREAR ARCHIVOS
// ══════════════════════════════════════════════════════
$ok = 0; $fail = 0;
foreach ($files as $path => $content) {
    $fullPath = __DIR__ . '/' . $path;
    $dir = dirname($fullPath);
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    // Eliminar BOM si existe
    $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
    if (file_put_contents($fullPath, $content) !== false) {
        echo "✅ $path\n"; $ok++;
    } else {
        echo "❌ $path\n"; $fail++;
    }
}

echo "\n📊 Resultado: $ok OK · $fail errores\n";
echo "\nEjecuta:\n";
echo "  C:\\php\\php.exe artisan view:clear\n";
echo "  C:\\php\\php.exe artisan route:clear\n";
echo "  C:\\php\\php.exe fix_fase5_routes.php\n";
