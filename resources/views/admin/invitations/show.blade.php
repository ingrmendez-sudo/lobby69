@extends('layouts.admin')
@section('title','Detalle Solicitud')
@section('content')
<div style="max-width:700px;margin:2rem auto;padding:0 1rem;">

  <div style="display:flex;align-items:center;gap:1rem;margin-bottom:2rem;">
    <a href="{{ route('admin.invitations.index') }}" class="btn btn--ghost btn--sm">← Volver</a>
    <h1 style="font-size:1.5rem;font-weight:700;color:var(--color-text);">Detalle de Solicitud</h1>
  </div>

  @if(session('success'))
    <div style="background:#d1fae5;border:1px solid #6ee7b7;color:#065f46;padding:1rem;border-radius:8px;margin-bottom:1rem;">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:1rem;border-radius:8px;margin-bottom:1rem;">{{ session('error') }}</div>
  @endif

  <div style="background:white;border-radius:16px;box-shadow:0 4px 16px rgba(0,0,0,.08);padding:2rem;">
    @php $pref = json_decode($invitation->preferencias ?? '{}', true); @endphp

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem;">
      <div><div style="font-size:.75rem;color:#94a3b8;margin-bottom:.25rem;">NOMBRE</div><div style="font-weight:600;">{{ $invitation->nombre }}</div></div>
      <div><div style="font-size:.75rem;color:#94a3b8;margin-bottom:.25rem;">EMAIL</div><div>{{ $invitation->email }}</div></div>
      <div><div style="font-size:.75rem;color:#94a3b8;margin-bottom:.25rem;">TIPO PERFIL</div><div>{{ ucfirst($invitation->tipo_perfil ?? 'N/A') }}</div></div>
      <div><div style="font-size:.75rem;color:#94a3b8;margin-bottom:.25rem;">GÉNERO</div><div>{{ ucfirst($invitation->genero ?? 'N/A') }}</div></div>
      <div><div style="font-size:.75rem;color:#94a3b8;margin-bottom:.25rem;">ENTIDAD</div><div>{{ $invitation->entidad ?? 'N/A' }}</div></div>
      <div><div style="font-size:.75rem;color:#94a3b8;margin-bottom:.25rem;">EDAD</div><div>{{ $pref['edad'] ?? 'N/A' }} años</div></div>
      <div><div style="font-size:.75rem;color:#94a3b8;margin-bottom:.25rem;">PAÍS</div><div>{{ $pref['pais'] ?? 'N/A' }}</div></div>
      <div><div style="font-size:.75rem;color:#94a3b8;margin-bottom:.25rem;">MUNICIPIO</div><div>{{ $pref['municipio'] ?? 'N/A' }}</div></div>
      <div><div style="font-size:.75rem;color:#94a3b8;margin-bottom:.25rem;">CÓDIGO USADO</div><div>{{ $invitation->invitation_code ?? 'Ninguno' }}</div></div>
      <div><div style="font-size:.75rem;color:#94a3b8;margin-bottom:.25rem;">FECHA</div><div>{{ \Carbon\Carbon::parse($invitation->created_at)->format('d/m/Y H:i') }}</div></div>
    </div>

    <div style="margin-bottom:1.5rem;">
      <div style="font-size:.75rem;color:#94a3b8;margin-bottom:.5rem;">MOTIVO</div>
      <div style="background:#f8fafc;padding:1rem;border-radius:8px;line-height:1.6;">{{ $invitation->motivo }}</div>
    </div>

    @if($invitation->status === 'pending')
    <div style="display:flex;gap:1rem;flex-wrap:wrap;">
      <form method="POST" action="{{ route('admin.invitations.approve',$invitation->id) }}">
        @csrf
        <button type="submit" class="btn btn--primary"
                onclick="return confirm('¿Aprobar esta solicitud? Se creará el usuario y se registrarán las credenciales en el log.')">
          ✅ Aprobar y Crear Usuario
        </button>
      </form>

      <button class="btn btn--ghost" onclick="document.getElementById('formRechazo').style.display='block';this.style.display='none'">
        ❌ Rechazar
      </button>
    </div>

    <div id="formRechazo" style="display:none;margin-top:1.5rem;background:#fff5f5;border:1px solid #fca5a5;border-radius:12px;padding:1.5rem;">
      <form method="POST" action="{{ route('admin.invitations.reject',$invitation->id) }}">
        @csrf
        <label style="display:block;font-weight:600;margin-bottom:.5rem;">Motivo de rechazo (opcional):</label>
        <textarea name="reason" rows="3"
                  style="width:100%;padding:.75rem;border:1px solid #e2e8f0;border-radius:8px;font-size:.9rem;resize:vertical;"
                  placeholder="Ej: Perfil incompleto, información sospechosa..."></textarea>
        <div style="display:flex;gap:1rem;margin-top:1rem;">
          <button type="submit" style="background:#ef4444;color:white;padding:.6rem 1.5rem;border-radius:8px;border:none;cursor:pointer;font-weight:600;">
            Confirmar Rechazo
          </button>
          <button type="button" onclick="document.getElementById('formRechazo').style.display='none';document.querySelector('.btn--ghost').style.display='inline-flex';"
                  class="btn btn--ghost btn--sm">Cancelar</button>
        </div>
      </form>
    </div>
    @else
    <div style="background:#f8fafc;padding:1rem;border-radius:8px;">
      <strong>Estado:</strong> {{ ucfirst($invitation->status) }}
      @if($invitation->admin_notes)
        <br><strong>Nota admin:</strong> {{ $invitation->admin_notes }}
      @endif
    </div>
    @endif
  </div>
</div>
@endsection
