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