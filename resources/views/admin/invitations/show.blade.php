@extends('layouts.admin')

@section('title', 'Detalle Invitación')
@section('page-title', 'Detalle de Invitación')

@section('content')
<div style="max-width:680px;margin:0 auto;">

    <div style="margin-bottom:1.5rem;">
        <a href="{{ route('admin.invitations.index') }}"
           style="color:var(--theme-muted);font-size:.85rem;text-decoration:none;display:inline-flex;align-items:center;gap:.4rem;">
            <i class="fas fa-arrow-left"></i> Volver a invitaciones
        </a>
    </div>

    @if(session('success'))
    <div style="background:#22c55e22;border:1px solid #22c55e;color:#22c55e;padding:.75rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.85rem;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    <div class="adm-card" style="padding:1.5rem;margin-bottom:1rem;">

        {{-- Header --}}
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.25rem;padding-bottom:1rem;border-bottom:1px solid var(--theme-border);">
            <div>
                <h2 style="margin:0 0 .25rem;font-size:1.1rem;color:var(--theme-text);">
                    {{ $invitation->nombre ?? 'Sin nombre' }}
                </h2>
                <div style="font-size:.85rem;color:var(--theme-muted);">{{ $invitation->email }}</div>
            </div>
            <div>
                @if($invitation->status === 'approved')
                    <span style="background:#22c55e22;color:#22c55e;padding:.3rem .8rem;border-radius:20px;font-size:.8rem;font-weight:700;">
                        <i class="fas fa-check"></i> Aprobado
                    </span>
                @elseif($invitation->status === 'rejected')
                    <span style="background:#ef444422;color:#ef4444;padding:.3rem .8rem;border-radius:20px;font-size:.8rem;font-weight:700;">
                        <i class="fas fa-times"></i> Rechazado
                    </span>
                @else
                    <span style="background:#f59e0b22;color:#f59e0b;padding:.3rem .8rem;border-radius:20px;font-size:.8rem;font-weight:700;">
                        <i class="fas fa-clock"></i> Pendiente
                    </span>
                @endif
            </div>
        </div>

        {{-- Datos en grid --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem 1.5rem;font-size:.85rem;margin-bottom:1.25rem;">

            <div>
                <div style="font-size:.72rem;color:var(--theme-muted);margin-bottom:.2rem;">Género</div>
                <div style="color:var(--theme-text);font-weight:600;">{{ $invitation->genero ?? '—' }}</div>
            </div>

            <div>
                <div style="font-size:.72rem;color:var(--theme-muted);margin-bottom:.2rem;">Tipo de perfil</div>
                <div style="color:var(--theme-text);font-weight:600;">{{ $invitation->tipo_perfil ?? '—' }}</div>
            </div>

            <div>
                <div style="font-size:.72rem;color:var(--theme-muted);margin-bottom:.2rem;">Entidad / Estado</div>
                <div style="color:var(--theme-text);font-weight:600;">{{ $invitation->entidad ?? '—' }}</div>
            </div>

            <div>
                <div style="font-size:.72rem;color:var(--theme-muted);margin-bottom:.2rem;">Fecha de solicitud</div>
                <div style="color:var(--theme-text);font-weight:600;">
                    {{ \Carbon\Carbon::parse($invitation->created_at)->format('d/m/Y H:i') }}
                </div>
            </div>

            <div>
                <div style="font-size:.72rem;color:var(--theme-muted);margin-bottom:.2rem;">Términos aceptados</div>
                <div style="color:{{ $invitation->terminos_aceptados ? '#22c55e' : '#ef4444' }};font-weight:600;">
                    {{ $invitation->terminos_aceptados ? '✅ Sí' : '❌ No' }}
                </div>
            </div>

            <div>
                <div style="font-size:.72rem;color:var(--theme-muted);margin-bottom:.2rem;">Privacidad aceptada</div>
                <div style="color:{{ $invitation->privacidad_aceptada ? '#22c55e' : '#ef4444' }};font-weight:600;">
                    {{ $invitation->privacidad_aceptada ? '✅ Sí' : '❌ No' }}
                </div>
            </div>

            @if($invitation->invitation_code)
            <div style="grid-column:1/-1;">
                <div style="font-size:.72rem;color:var(--theme-muted);margin-bottom:.2rem;">Código de invitación usado</div>
                <div style="color:var(--theme-accent);font-weight:600;font-family:monospace;">
                    {{ $invitation->invitation_code }}
                </div>
            </div>
            @endif

        </div>

        {{-- Datos extra del formulario (preferencias JSON) --}}
        @php
            $extraFields = [
                'edad'      => 'Edad',
                'pais'      => 'País',
                'municipio' => 'Municipio / Ciudad',
            ];
            $extras = [];
            if ($invitation->preferencias) {
                $raw = is_string($invitation->preferencias)
                    ? json_decode($invitation->preferencias, true)
                    : (array) $invitation->preferencias;
                foreach ($extraFields as $key => $label) {
                    if (!empty($raw[$key])) {
                        $extras[$label] = $raw[$key];
                    }
                }
            }
        @endphp

        @if(!empty($extras))
        <div style="margin-bottom:1.25rem;padding:.9rem;background:var(--theme-bg);border:1px solid var(--theme-border);border-radius:8px;">
            <div style="font-size:.72rem;color:var(--theme-muted);margin-bottom:.75rem;text-transform:uppercase;letter-spacing:.05em;font-weight:700;">
                <i class="fas fa-info-circle"></i> Datos adicionales
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:.6rem .5rem;">
                @foreach($extras as $label => $value)
                <div>
                    <div style="font-size:.68rem;color:var(--theme-muted);margin-bottom:.15rem;">{{ $label }}</div>
                    <div style="font-size:.85rem;color:var(--theme-text);font-weight:600;">{{ $value }}</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Motivo --}}
        @if($invitation->motivo)
        <div style="margin-bottom:1.25rem;">
            <div style="font-size:.72rem;color:var(--theme-muted);margin-bottom:.5rem;text-transform:uppercase;letter-spacing:.05em;">
                Motivo de solicitud
            </div>
            <div style="background:var(--theme-bg);border-radius:8px;padding:.9rem;font-size:.87rem;color:var(--theme-text);line-height:1.6;border:1px solid var(--theme-border);">
                {{ $invitation->motivo }}
            </div>
        </div>
        @endif

        {{-- Nota admin --}}
        @if($invitation->admin_notes)
        <div style="margin-bottom:1.25rem;padding:.9rem;background:#ef444411;border:1px solid #ef444433;border-radius:8px;">
            <div style="font-size:.72rem;color:#ef4444;margin-bottom:.3rem;font-weight:600;">
                <i class="fas fa-exclamation-triangle"></i> Nota del administrador
            </div>
            <div style="font-size:.85rem;color:var(--theme-text);">{{ $invitation->admin_notes }}</div>
        </div>
        @endif

        {{-- Acciones --}}
        @if($invitation->status === 'pending')
        <div style="display:flex;gap:.75rem;padding-top:1rem;border-top:1px solid var(--theme-border);">
            <form method="POST" action="{{ route('admin.invitations.approve', $invitation->id) }}" style="flex:1;">
                @csrf
                <button type="submit"
                        onclick="return confirm('¿Aprobar a {{ addslashes($invitation->nombre ?? $invitation->email) }}? Se creará su cuenta.')"
                        style="width:100%;padding:.55rem;background:#22c55e;color:#fff;border:none;border-radius:8px;cursor:pointer;font-size:.88rem;font-weight:600;">
                    <i class="fas fa-check"></i> Aprobar — Crear cuenta
                </button>
            </form>

            <form method="POST" action="{{ route('admin.invitations.reject', $invitation->id) }}" style="flex:1;">
                @csrf
                <div style="margin-bottom:.5rem;">
                    <textarea name="reason" rows="2" placeholder="Motivo del rechazo (opcional)"
                              style="width:100%;padding:.45rem .7rem;border-radius:8px;border:1px solid var(--theme-border);background:var(--theme-bg);color:var(--theme-text);font-size:.82rem;resize:none;"></textarea>
                </div>
                <button type="submit"
                        style="width:100%;padding:.55rem;background:#ef4444;color:#fff;border:none;border-radius:8px;cursor:pointer;font-size:.88rem;font-weight:600;">
                    <i class="fas fa-times"></i> Rechazar solicitud
                </button>
            </form>
        </div>
        @endif

    </div>
</div>
@endsection