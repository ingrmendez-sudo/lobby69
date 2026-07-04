@extends('layouts.admin')
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
