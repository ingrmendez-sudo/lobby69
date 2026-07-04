@extends('layouts.admin')
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
