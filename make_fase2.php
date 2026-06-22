<?php
$files = [];

$files['app/Http/Middleware/AdminOnly.php'] = <<<'PHP'
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Acceso restringido a administradores.');
        }
        return $next($request);
    }
}
PHP;

$files['app/Http/Controllers/Admin/AdminInvitationController.php'] = <<<'PHP'
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
PHP;

$files['app/Services/InvitationService.php'] = <<<'PHP'
<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InvitationService
{
    public function approveInvitation(object $solicitud, string $adminId, ?string $notes = null): array
    {
        $existing = DB::table('users')->where('email', $solicitud->email)->first();
        if ($existing) {
            throw new \Exception("El email {$solicitud->email} ya tiene una cuenta.");
        }

        $tempPassword = $this->generateTempPassword();
        $userId       = (string) Str::uuid();

        DB::beginTransaction();
        try {
            DB::table('users')->insert([
                'id'                => $userId,
                'email'             => $solicitud->email,
                'username'          => $this->generateUsername($solicitud->nombre),
                'name'              => $solicitud->nombre,
                'password'          => Hash::make($tempPassword),
                'role'              => 'user',
                'active'            => true,
                'age_verified'      => true,
                'terms_accepted'    => true,
                'terms_accepted_at' => Carbon::now(),
                'created_at'        => Carbon::now(),
                'updated_at'        => Carbon::now(),
            ]);

            DB::table('invitation_requests')->where('id', $solicitud->id)->update([
                'status'      => 'approved',
                'reviewed_by' => $adminId,
                'reviewed_at' => Carbon::now(),
                'approved_at' => Carbon::now(),
                'admin_notes' => $notes,
                'updated_at'  => Carbon::now(),
            ]);

            DB::commit();

            $this->sendWelcomeMail($solicitud->email, $solicitud->nombre, $tempPassword);

            Log::info('Usuario creado desde invitacion', [
                'user_id'       => $userId,
                'email'         => $solicitud->email,
                'temp_password' => $tempPassword,
            ]);

            return ['success' => true, 'user_id' => $userId, 'temp_password' => $tempPassword];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function generateTempPassword(): string
    {
        $words   = ['Lobby', 'Noche', 'Fiesta', 'Club', 'Elite', 'Vivid'];
        $special = ['!', '@', '#', '$'];
        return $words[array_rand($words)] . rand(100, 999) . $special[array_rand($special)];
    }

    private function generateUsername(string $nombre): string
    {
        $base     = strtolower(substr(preg_replace('/[^a-zA-Z0-9_]/', '', str_replace(' ', '_', $nombre)), 0, 15));
        $username = $base;
        $counter  = 1;
        while (DB::table('users')->where('username', $username)->exists()) {
            $username = $base . $counter++;
        }
        return $username;
    }

    private function sendWelcomeMail(string $email, string $nombre, string $tempPassword): void
    {
        try {
            Mail::send('emails.invitation-approved', compact('nombre', 'email', 'tempPassword'), function ($mail) use ($email, $nombre) {
                $mail->to($email, $nombre)->subject('Tu acceso a LOBBY69 ha sido aprobado');
            });
        } catch (\Exception $e) {
            Log::warning('Mail no enviado: ' . $e->getMessage());
        }
    }
}
PHP;

$files['resources/views/emails/invitation-approved.blade.php'] = <<<'HTML'
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
body{font-family:Arial,sans-serif;background:#FAF9F6;margin:0;padding:40px 20px;}
.wrap{max-width:560px;margin:0 auto;background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 10px 40px rgba(0,0,0,.1);}
.hdr{background:linear-gradient(135deg,#FF1493,#9B59B6);padding:40px;text-align:center;}
.hdr h1{color:#fff;margin:0;font-size:2rem;letter-spacing:2px;}
.hdr p{color:rgba(255,255,255,.85);margin:8px 0 0;}
.bod{padding:40px;}
.bod h2{color:#2C3E50;}
.bod p{color:#555;line-height:1.7;}
.creds{background:#FAF9F6;border-radius:12px;padding:24px;margin:24px 0;border-left:4px solid #FF6B6B;}
.creds p{margin:6px 0;font-size:.95rem;}
.creds code{background:#fff;padding:4px 10px;border-radius:6px;font-size:1.1rem;font-weight:700;color:#FF1493;border:1px solid #eee;}
.btn{display:block;width:fit-content;margin:24px auto;background:#FF6B6B;color:#fff;padding:14px 36px;border-radius:50px;text-decoration:none;font-weight:700;}
.warn{background:#FFF3CD;border-radius:10px;padding:16px;margin-top:20px;font-size:.85rem;color:#856404;}
.ftr{background:#2C3E50;padding:24px;text-align:center;color:rgba(255,255,255,.5);font-size:.8rem;}
</style>
</head>
<body>
<div class="wrap">
    <div class="hdr"><h1>LOBBY69</h1><p>Club Privado — Acceso Aprobado</p></div>
    <div class="bod">
        <h2>¡Bienvenido/a, {{ $nombre }}!</h2>
        <p>Tu solicitud de acceso a <strong>CLUB LOBBY69</strong> ha sido aprobada.</p>
        <div class="creds">
            <p><strong>Email:</strong> {{ $email }}</p>
            <p><strong>Contraseña temporal:</strong> <code>{{ $tempPassword }}</code></p>
        </div>
        <a href="{{ url('/login') }}" class="btn">Iniciar Sesión</a>
        <div class="warn">⚠️ Al iniciar sesión deberás completar tu perfil y cambiar tu contraseña. Esta contraseña expira en 48 horas.</div>
    </div>
    <div class="ftr">© 2026 CLUB LOBBY69 — Plataforma privada para adultos +18</div>
</div>
</body>
</html>
HTML;

$files['resources/views/admin/invitations/index.blade.php'] = <<<'BLADE'
@extends('layouts.app')
@section('title', 'Admin — Invitaciones')
@section('content')
<div class="container" style="padding-top:2rem;padding-bottom:4rem;">

    <div class="section-header">
        <div>
            <h1 class="h2">Panel Admin</h1>
            <p class="text-muted">Gestión de solicitudes de invitación</p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn--ghost btn--sm">
            <i class="fas fa-arrow-left"></i> Dashboard
        </a>
    </div>

    @if(session('success'))
        <div class="toast toast--success" style="position:relative;top:0;right:0;margin-bottom:1rem;animation:none;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="toast toast--error" style="position:relative;top:0;right:0;margin-bottom:1rem;animation:none;">
            <i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}
        </div>
    @endif

    {{-- Contadores --}}
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:2rem;">
        <div class="card" style="padding:1.5rem;text-align:center;border-top:3px solid #F39C12;">
            <div style="font-size:2rem;font-weight:700;color:#F39C12;">{{ $contadores['pending'] }}</div>
            <div class="text-sm text-muted">Pendientes</div>
        </div>
        <div class="card" style="padding:1.5rem;text-align:center;border-top:3px solid #27AE60;">
            <div style="font-size:2rem;font-weight:700;color:#27AE60;">{{ $contadores['approved'] }}</div>
            <div class="text-sm text-muted">Aprobadas</div>
        </div>
        <div class="card" style="padding:1.5rem;text-align:center;border-top:3px solid #E74C3C;">
            <div style="font-size:2rem;font-weight:700;color:#E74C3C;">{{ $contadores['rejected'] }}</div>
            <div class="text-sm text-muted">Rechazadas</div>
        </div>
        <div class="card" style="padding:1.5rem;text-align:center;border-top:3px solid #3498DB;">
            <div style="font-size:2rem;font-weight:700;color:#3498DB;">{{ $contadores['total'] }}</div>
            <div class="text-sm text-muted">Total</div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="card" style="padding:1.5rem;margin-bottom:1.5rem;">
        <form method="GET" action="{{ route('admin.invitations.index') }}" style="display:flex;gap:1rem;flex-wrap:wrap;align-items:flex-end;">
            <div class="form-group" style="margin:0;flex:1;min-width:180px;">
                <label class="form-label">Buscar</label>
                <input type="text" name="search" class="form-control" placeholder="Nombre o email..." value="{{ $search }}">
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label">Estado</label>
                <select name="status" class="form-control">
                    <option value="all"      {{ $status==='all'      ?'selected':'' }}>Todos</option>
                    <option value="pending"  {{ $status==='pending'  ?'selected':'' }}>Pendientes</option>
                    <option value="approved" {{ $status==='approved' ?'selected':'' }}>Aprobados</option>
                    <option value="rejected" {{ $status==='rejected' ?'selected':'' }}>Rechazados</option>
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label">Tipo</label>
                <select name="tipo_perfil" class="form-control">
                    <option value="">Todos</option>
                    <option value="single"    {{ $tipo==='single'    ?'selected':'' }}>Single</option>
                    <option value="unicornio" {{ $tipo==='unicornio' ?'selected':'' }}>Unicornio</option>
                    <option value="pareja"    {{ $tipo==='pareja'    ?'selected':'' }}>Pareja</option>
                </select>
            </div>
            <button type="submit" class="btn btn--primary btn--sm"><i class="fas fa-search"></i> Filtrar</button>
            <a href="{{ route('admin.invitations.index') }}" class="btn btn--ghost btn--sm">Limpiar</a>
        </form>
    </div>

    {{-- Tabla --}}
    <div class="card">
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:2px solid rgba(44,62,80,.08);">
                        <th style="padding:1rem;text-align:left;font-size:.8rem;color:#7F8C8D;text-transform:uppercase;">Nick</th>
                        <th style="padding:1rem;text-align:left;font-size:.8rem;color:#7F8C8D;text-transform:uppercase;">Email</th>
                        <th style="padding:1rem;text-align:left;font-size:.8rem;color:#7F8C8D;text-transform:uppercase;">Tipo</th>
                        <th style="padding:1rem;text-align:left;font-size:.8rem;color:#7F8C8D;text-transform:uppercase;">Estado</th>
                        <th style="padding:1rem;text-align:left;font-size:.8rem;color:#7F8C8D;text-transform:uppercase;">Fecha</th>
                        <th style="padding:1rem;text-align:left;font-size:.8rem;color:#7F8C8D;text-transform:uppercase;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($solicitudes as $s)
                    <tr style="border-bottom:1px solid rgba(44,62,80,.05);"
                        onmouseover="this.style.background='#FAF9F6'"
                        onmouseout="this.style.background=''">
                        <td style="padding:1rem;font-weight:600;">{{ $s->nombre }}</td>
                        <td style="padding:1rem;font-size:.9rem;color:#7F8C8D;">{{ $s->email }}</td>
                        <td style="padding:1rem;font-size:.85rem;">
                            @php $tipos=['single'=>'👤 Single','unicornio'=>'🦄 Unicornio','pareja'=>'👫 Pareja']; @endphp
                            {{ $tipos[$s->tipo_perfil] ?? $s->tipo_perfil }}
                        </td>
                        <td style="padding:1rem;">
                            @if($s->status==='pending')
                                <span class="badge" style="background:rgba(243,156,18,.12);color:#F39C12;">⏳ Pendiente</span>
                            @elseif($s->status==='approved')
                                <span class="badge" style="background:rgba(39,174,96,.12);color:#27AE60;">✅ Aprobado</span>
                            @else
                                <span class="badge" style="background:rgba(231,76,60,.12);color:#E74C3C;">❌ Rechazado</span>
                            @endif
                        </td>
                        <td style="padding:1rem;font-size:.85rem;color:#7F8C8D;">
                            {{ \Carbon\Carbon::parse($s->created_at)->format('d/m/Y H:i') }}
                        </td>
                        <td style="padding:1rem;">
                            <div style="display:flex;gap:.5rem;align-items:center;">
                                <a href="{{ route('admin.invitations.show', $s->id) }}" class="btn btn--ghost btn--sm" title="Ver detalle">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($s->status==='pending')
                                <form method="POST" action="{{ route('admin.invitations.approve', $s->id) }}"
                                      onsubmit="return confirm('Aprobar a {{ $s->nombre }} y crear su cuenta?')">
                                    @csrf
                                    <button type="submit" class="btn btn--sm"
                                            style="background:rgba(39,174,96,.15);color:#27AE60;border:1px solid #27AE60;">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                <button class="btn btn--sm"
                                        style="background:rgba(231,76,60,.15);color:#E74C3C;border:1px solid #E74C3C;"
                                        onclick="document.getElementById('modal-{{ $s->id }}').style.display='flex';document.body.style.overflow='hidden';">
                                    <i class="fas fa-times"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding:3rem;text-align:center;color:#7F8C8D;">
                            <i class="fas fa-inbox" style="font-size:2rem;display:block;margin-bottom:1rem;"></i>
                            No hay solicitudes{{ $status!=='all' ? " con estado '$status'" : '' }}.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($solicitudes->hasPages())
        <div style="padding:1rem 1.5rem;border-top:1px solid rgba(44,62,80,.08);">
            {{ $solicitudes->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Modales de rechazo (fuera de la tabla) --}}
@foreach($solicitudes as $s)
@if($s->status==='pending')
<div class="modal-overlay" id="modal-{{ $s->id }}" role="dialog">
    <div class="modal">
        <div class="modal__header">
            <h3 class="h4">Rechazar — {{ $s->nombre }}</h3>
            <button type="button" class="btn btn--ghost btn--sm"
                    onclick="document.getElementById('modal-{{ $s->id }}').style.display='none';document.body.style.overflow='';">
                &#x2715;
            </button>
        </div>
        <form method="POST" action="{{ route('admin.invitations.reject', $s->id) }}">
            @csrf
            <div class="modal__body">
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Motivo del rechazo <span style="color:#E74C3C;">*</span></label>
                    <textarea name="admin_notes" class="form-control" rows="3"
                              placeholder="Indica el motivo..." required minlength="10"></textarea>
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn--ghost"
                        onclick="document.getElementById('modal-{{ $s->id }}').style.display='none';document.body.style.overflow='';">
                    Cancelar
                </button>
                <button type="submit" class="btn" style="background:#E74C3C;color:#fff;">
                    <i class="fas fa-times"></i> Rechazar
                </button>
            </div>
        </form>
    </div>
</div>
@endif
@endforeach
@endsection
BLADE;

$files['resources/views/admin/invitations/show.blade.php'] = <<<'BLADE'
@extends('layouts.app')
@section('title', 'Detalle Solicitud')
@section('content')
<div class="container" style="padding-top:2rem;padding-bottom:4rem;max-width:760px;">

    <div class="section-header" style="margin-bottom:2rem;">
        <div>
            <a href="{{ route('admin.invitations.index') }}" class="text-muted text-sm">← Volver</a>
            <h1 class="h2" style="margin-top:.5rem;">{{ $solicitud->nombre }}</h1>
        </div>
        @if($solicitud->status==='pending')
        <div style="display:flex;gap:.75rem;">
            <form method="POST" action="{{ route('admin.invitations.approve', $solicitud->id) }}"
                  onsubmit="return confirm('Aprobar y crear cuenta para {{ $solicitud->nombre }}?')">
                @csrf
                <button type="submit" class="btn btn--sm" style="background:#27AE60;color:#fff;">
                    <i class="fas fa-check"></i> Aprobar
                </button>
            </form>
            <button class="btn btn--sm" style="background:#E74C3C;color:#fff;"
                    onclick="document.getElementById('modalRechazar').style.display='flex';document.body.style.overflow='hidden';">
                <i class="fas fa-times"></i> Rechazar
            </button>
        </div>
        @else
            <span class="badge" style="font-size:.9rem;padding:.5rem 1rem;
                {{ $solicitud->status==='approved'?'background:rgba(39,174,96,.12);color:#27AE60;':'background:rgba(231,76,60,.12);color:#E74C3C;' }}">
                {{ $solicitud->status==='approved'?'✅ Aprobado':'❌ Rechazado' }}
            </span>
        @endif
    </div>

    <div class="card" style="padding:2rem;margin-bottom:1.5rem;">
        <h3 class="h4" style="margin-bottom:1.5rem;padding-bottom:.75rem;border-bottom:1px solid rgba(44,62,80,.08);">Datos del solicitante</h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">
            <div><p class="text-sm text-muted">Nick</p><p style="font-weight:600;">{{ $solicitud->nombre }}</p></div>
            <div><p class="text-sm text-muted">Email</p><p style="font-weight:600;">{{ $solicitud->email }}</p></div>
            <div><p class="text-sm text-muted">Tipo de perfil</p><p style="font-weight:600;text-transform:capitalize;">{{ $solicitud->tipo_perfil }}</p></div>
            <div><p class="text-sm text-muted">Género</p><p style="font-weight:600;text-transform:capitalize;">{{ $solicitud->genero }}</p></div>
            <div><p class="text-sm text-muted">Estado / Entidad</p><p style="font-weight:600;">{{ $solicitud->entidad }}</p></div>
            <div><p class="text-sm text-muted">Código invitación</p><p style="font-weight:600;">{{ $solicitud->invitation_code ?? '— sin código —' }}</p></div>
            @if(!empty($preferencias['edad']))<div><p class="text-sm text-muted">Edad</p><p style="font-weight:600;">{{ $preferencias['edad'] }} años</p></div>@endif
            @if(!empty($preferencias['pais']))<div><p class="text-sm text-muted">País</p><p style="font-weight:600;">{{ $preferencias['pais'] }}</p></div>@endif
        </div>
        <div style="margin-top:1.5rem;padding-top:1.5rem;border-top:1px solid rgba(44,62,80,.08);">
            <p class="text-sm text-muted" style="margin-bottom:.5rem;">Motivo</p>
            <p style="line-height:1.7;background:#FAF9F6;padding:1rem;border-radius:10px;">{{ $solicitud->motivo }}</p>
        </div>
    </div>

    <div class="card" style="padding:1.5rem;">
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;">
            <div><p class="text-sm text-muted">Enviada</p><p class="text-sm">{{ \Carbon\Carbon::parse($solicitud->created_at)->format('d/m/Y H:i') }}</p></div>
            <div><p class="text-sm text-muted">Revisada</p><p class="text-sm">{{ $solicitud->reviewed_at ? \Carbon\Carbon::parse($solicitud->reviewed_at)->format('d/m/Y H:i') : '—' }}</p></div>
            <div><p class="text-sm text-muted">Estado</p><p class="text-sm" style="font-weight:600;text-transform:capitalize;">{{ $solicitud->status }}</p></div>
        </div>
        @if($solicitud->admin_notes)
        <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid rgba(44,62,80,.08);">
            <p class="text-sm text-muted">Notas admin</p>
            <p class="text-sm">{{ $solicitud->admin_notes }}</p>
        </div>
        @endif
    </div>
</div>

@if($solicitud->status==='pending')
<div class="modal-overlay" id="modalRechazar" role="dialog">
    <div class="modal">
        <div class="modal__header">
            <h3 class="h4">Rechazar solicitud</h3>
            <button type="button" class="btn btn--ghost btn--sm"
                    onclick="document.getElementById('modalRechazar').style.display='none';document.body.style.overflow='';">&#x2715;</button>
        </div>
        <form method="POST" action="{{ route('admin.invitations.reject', $solicitud->id) }}">
            @csrf
            <div class="modal__body">
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Motivo <span style="color:#E74C3C;">*</span></label>
                    <textarea name="admin_notes" class="form-control" rows="4" required minlength="10" placeholder="Indica el motivo..."></textarea>
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn--ghost"
                        onclick="document.getElementById('modalRechazar').style.display='none';document.body.style.overflow='';">Cancelar</button>
                <button type="submit" class="btn" style="background:#E74C3C;color:#fff;">Confirmar Rechazo</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
BLADE;

// Escribir archivos
$ok = 0; $errors = 0;
foreach ($files as $path => $content) {
    $fullPath = __DIR__ . '/' . $path;
    $dir = dirname($fullPath);
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    if (file_put_contents($fullPath, $content) !== false) {
        $b = array_values(unpack('C*', substr($content, 0, 3)));
        echo ($b[0]===239 ? '[BOM!]' : '[OK]  ') . " {$path}" . PHP_EOL;
        $ok++;
    } else {
        echo "[ERR]  {$path}" . PHP_EOL;
        $errors++;
    }
}
echo PHP_EOL . "Creados: {$ok} | Errores: {$errors}" . PHP_EOL;
