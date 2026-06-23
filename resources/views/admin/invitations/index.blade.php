@extends('layouts.app')
@section('title','Admin — Solicitudes de Invitación')
@section('content')
<div style="max-width:1100px;margin:2rem auto;padding:0 1rem;">

  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:2rem;">
    <h1 style="font-size:1.8rem;font-weight:700;color:var(--color-text);">
      🛡️ Panel Admin</h1>
    <a href="{{ route('admin.verifications.index') }}"
       style="display:inline-block;margin-top:.5rem;padding:.5rem 1rem;background:#8b5cf6;color:white;border-radius:8px;font-size:.85rem;text-decoration:none;font-weight:600;">
        🛡️ Cola de Verificaciones
        @php $pending = \Illuminate\Support\Facades\DB::table('verifications')->where('status','pending')->count(); @endphp
        @if($pending > 0)
            <span style="background:#ef4444;color:white;border-radius:50%;padding:.1rem .4rem;font-size:.75rem;margin-left:.3rem;">{{ $pending }}</span>
        @endif
    </a>
    <span style="display:none — Invitaciones
    </h1>
    <a href="{{ route('dashboard') }}" class="btn btn--ghost btn--sm">← Dashboard</a>
  </div>

  {{-- CONTADORES --}}
  <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:2rem;">
    @foreach(['pending'=>['label'=>'Pendientes','color'=>'#f59e0b'],'approved'=>['label'=>'Aprobadas','color'=>'#10b981'],'rejected'=>['label'=>'Rechazadas','color'=>'#ef4444'],'total'=>['label'=>'Total','color'=>'#8b5cf6']] as $key=>$meta)
    <a href="{{ route('admin.invitations.index',['status'=>$key]) }}"
       style="background:white;border-radius:12px;padding:1.2rem;text-align:center;text-decoration:none;box-shadow:0 2px 8px rgba(0,0,0,.06);border-top:4px solid {{ $meta['color'] }};">
      <div style="font-size:2rem;font-weight:800;color:{{ $meta['color'] }};">{{ $counts[$key] }}</div>
      <div style="font-size:.85rem;color:#64748b;margin-top:.25rem;">{{ $meta['label'] }}</div>
    </a>
    @endforeach
  </div>

  {{-- FLASH --}}
  @if(session('success'))
    <div style="background:#d1fae5;border:1px solid #6ee7b7;color:#065f46;padding:1rem;border-radius:8px;margin-bottom:1rem;">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:1rem;border-radius:8px;margin-bottom:1rem;">{{ session('error') }}</div>
  @endif

  {{-- FILTROS --}}
  <form method="GET" style="display:flex;gap:1rem;margin-bottom:1.5rem;flex-wrap:wrap;">
    <input type="text" name="search" value="{{ $search }}" placeholder="Buscar por nombre o email..."
           style="flex:1;min-width:200px;padding:.6rem 1rem;border:1px solid #e2e8f0;border-radius:8px;font-size:.9rem;">
    <select name="status" style="padding:.6rem 1rem;border:1px solid #e2e8f0;border-radius:8px;font-size:.9rem;">
      <option value="all"     {{ $status==='all'     ?'selected':'' }}>Todos</option>
      <option value="pending" {{ $status==='pending' ?'selected':'' }}>Pendientes</option>
      <option value="approved"{{ $status==='approved'?'selected':'' }}>Aprobadas</option>
      <option value="rejected"{{ $status==='rejected'?'selected':'' }}>Rechazadas</option>
    </select>
    <button type="submit" class="btn btn--primary btn--sm">Filtrar</button>
  </form>

  {{-- TABLA --}}
  <div style="background:white;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.06);overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;font-size:.9rem;">
      <thead>
        <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
          <th style="padding:.75rem 1rem;text-align:left;color:#475569;font-weight:600;">Nombre</th>
          <th style="padding:.75rem 1rem;text-align:left;color:#475569;font-weight:600;">Email</th>
          <th style="padding:.75rem 1rem;text-align:left;color:#475569;font-weight:600;">Perfil</th>
          <th style="padding:.75rem 1rem;text-align:left;color:#475569;font-weight:600;">Estado</th>
          <th style="padding:.75rem 1rem;text-align:left;color:#475569;font-weight:600;">Fecha</th>
          <th style="padding:.75rem 1rem;text-align:center;color:#475569;font-weight:600;">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse($invitations as $inv)
        <tr style="border-bottom:1px solid #f1f5f9;transition:background .15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
          <td style="padding:.75rem 1rem;font-weight:500;">{{ $inv->nombre }}</td>
          <td style="padding:.75rem 1rem;color:#64748b;">{{ $inv->email }}</td>
          <td style="padding:.75rem 1rem;">
            <span style="background:#ede9fe;color:#7c3aed;padding:.2rem .6rem;border-radius:20px;font-size:.8rem;font-weight:600;">
              {{ ucfirst($inv->tipo_perfil ?? 'N/A') }}
            </span>
          </td>
          <td style="padding:.75rem 1rem;">
            @php $colors=['pending'=>['#fef3c7','#92400e'],'approved'=>['#d1fae5','#065f46'],'rejected'=>['#fee2e2','#991b1b']]; $c=$colors[$inv->status]??['#f1f5f9','#475569']; @endphp
            <span style="background:{{ $c[0] }};color:{{ $c[1] }};padding:.2rem .6rem;border-radius:20px;font-size:.8rem;font-weight:600;">
              {{ ucfirst($inv->status) }}
            </span>
          </td>
          <td style="padding:.75rem 1rem;color:#94a3b8;font-size:.85rem;">
            {{ \Carbon\Carbon::parse($inv->created_at)->format('d/m/Y H:i') }}
          </td>
          <td style="padding:.75rem 1rem;text-align:center;">
            <a href="{{ route('admin.invitations.show',$inv->id) }}"
               style="background:#e0e7ff;color:#3730a3;padding:.3rem .7rem;border-radius:6px;font-size:.8rem;font-weight:600;text-decoration:none;margin-right:.25rem;">
              Ver
            </a>
            @if($inv->status === 'pending')
            <form method="POST" action="{{ route('admin.invitations.approve',$inv->id) }}" style="display:inline;">
              @csrf
              <button type="submit"
                      style="background:#d1fae5;color:#065f46;padding:.3rem .7rem;border-radius:6px;font-size:.8rem;font-weight:600;border:none;cursor:pointer;"
                      onclick="return confirm('¿Aprobar a {{ $inv->email }}?')">
                ✅ Aprobar
              </button>
            </form>
            @endif
          </td>
        </tr>
        @empty
        <tr><td colspan="6" style="padding:3rem;text-align:center;color:#94a3b8;">No hay solicitudes con este filtro.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- PAGINACION --}}
  <div style="margin-top:1rem;">{{ $invitations->appends(request()->query())->links() }}</div>
</div>
@endsection