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