@extends('layouts.admin')

@section('title', 'Invitaciones')
@section('page-title', 'Gestión de Invitaciones')

@section('content')

@if(session('success'))
<div style="background:#22c55e22;border:1px solid #22c55e;color:#22c55e;padding:.75rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.85rem;">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

@if(session('error'))
<div style="background:#ef444422;border:1px solid #ef4444;color:#ef4444;padding:.75rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.85rem;">
    <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
</div>
@endif

{{-- Stats --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:1rem;margin-bottom:1.5rem;">
    <div class="adm-card" style="padding:1rem;text-align:center;">
        <div style="font-size:1.5rem;font-weight:800;color:var(--theme-accent);">{{ $counts['total'] }}</div>
        <div style="font-size:.72rem;color:var(--theme-muted);text-transform:uppercase;">Total</div>
    </div>
    <div class="adm-card" style="padding:1rem;text-align:center;">
        <div style="font-size:1.5rem;font-weight:800;color:#f59e0b;">{{ $counts['pending'] }}</div>
        <div style="font-size:.72rem;color:var(--theme-muted);text-transform:uppercase;">Pendientes</div>
    </div>
    <div class="adm-card" style="padding:1rem;text-align:center;">
        <div style="font-size:1.5rem;font-weight:800;color:#22c55e;">{{ $counts['approved'] }}</div>
        <div style="font-size:.72rem;color:var(--theme-muted);text-transform:uppercase;">Aprobadas</div>
    </div>
    <div class="adm-card" style="padding:1rem;text-align:center;">
        <div style="font-size:1.5rem;font-weight:800;color:#ef4444;">{{ $counts['rejected'] }}</div>
        <div style="font-size:.72rem;color:var(--theme-muted);text-transform:uppercase;">Rechazadas</div>
    </div>
</div>

{{-- Filtros --}}
<div class="adm-card" style="padding:1rem;margin-bottom:1.25rem;">
    <form method="GET" action="{{ route('admin.invitations.index') }}"
          style="display:flex;flex-wrap:wrap;gap:.75rem;align-items:flex-end;">
        <div style="flex:1;min-width:200px;">
            <label style="font-size:.72rem;color:var(--theme-muted);display:block;margin-bottom:.2rem;">Buscar</label>
            <input type="text" name="search" value="{{ $search }}"
                   placeholder="Email o nombre…"
                   style="width:100%;padding:.4rem .7rem;border-radius:6px;border:1px solid var(--theme-border);background:var(--theme-bg);color:var(--theme-text);font-size:.83rem;">
        </div>
        <div style="min-width:140px;">
            <label style="font-size:.72rem;color:var(--theme-muted);display:block;margin-bottom:.2rem;">Estado</label>
            <select name="status"
                    style="width:100%;padding:.4rem .7rem;border-radius:6px;border:1px solid var(--theme-border);background:var(--theme-bg);color:var(--theme-text);font-size:.83rem;">
                <option value="all"      {{ $status==='all'      ?'selected':'' }}>Todos</option>
                <option value="pending"  {{ $status==='pending'  ?'selected':'' }}>Pendientes</option>
                <option value="approved" {{ $status==='approved' ?'selected':'' }}>Aprobados</option>
                <option value="rejected" {{ $status==='rejected' ?'selected':'' }}>Rechazados</option>
            </select>
        </div>
        <button type="submit"
                style="padding:.4rem 1rem;background:var(--theme-accent);color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:.83rem;">
            <i class="fas fa-search"></i> Filtrar
        </button>
        @if($search || $status !== 'pending')
        <a href="{{ route('admin.invitations.index') }}"
           style="padding:.4rem .9rem;background:var(--theme-muted);color:#fff;border-radius:6px;font-size:.83rem;text-decoration:none;">
            <i class="fas fa-times"></i> Limpiar
        </a>
        @endif
    </form>
</div>

{{-- Tabla --}}
<div class="adm-card" style="overflow-x:auto;">
    <table style="width:100%;border-collapse:collapse;font-size:.83rem;">
        <thead>
            <tr style="border-bottom:2px solid var(--theme-border);">
                <th style="padding:.6rem .8rem;color:var(--theme-muted);font-weight:600;text-align:left;">Solicitante</th>
                <th style="padding:.6rem .8rem;color:var(--theme-muted);font-weight:600;text-align:left;">Perfil</th>
                <th style="padding:.6rem .8rem;color:var(--theme-muted);font-weight:600;text-align:left;">Motivo</th>
                <th style="padding:.6rem .8rem;color:var(--theme-muted);font-weight:600;text-align:center;">Estado</th>
                <th style="padding:.6rem .8rem;color:var(--theme-muted);font-weight:600;text-align:left;">Fecha</th>
                <th style="padding:.6rem .8rem;color:var(--theme-muted);font-weight:600;text-align:center;">Acciones</th>
            </tr>
        </thead>
        <tbody>
        @forelse($invitations as $inv)
        <tr style="border-bottom:1px solid var(--theme-border);">

            {{-- Solicitante --}}
            <td style="padding:.6rem .8rem;">
                <div style="font-weight:600;color:var(--theme-text);">{{ $inv->nombre ?? '—' }}</div>
                <div style="font-size:.72rem;color:var(--theme-muted);">{{ $inv->email }}</div>
                @if($inv->entidad)
                <div style="font-size:.7rem;color:var(--theme-muted);">
                    <i class="fas fa-map-marker-alt"></i> {{ $inv->entidad }}
                </div>
                @endif
            </td>

            {{-- Perfil --}}
            <td style="padding:.6rem .8rem;">
                <div style="font-size:.8rem;color:var(--theme-text);">
                    {{ $inv->genero ?? '—' }}
                    @if($inv->tipo_perfil)
                        · <span style="color:var(--theme-accent);">{{ $inv->tipo_perfil }}</span>
                    @endif
                </div>
                @if($inv->preferencias)
                @php
                    $prefs = is_string($inv->preferencias)
                        ? json_decode($inv->preferencias, true)
                        : (array)$inv->preferencias;
                @endphp
                @if(!empty($prefs))
                <div style="font-size:.7rem;color:var(--theme-muted);margin-top:.2rem;">
                    {{ implode(', ', array_slice($prefs, 0, 3)) }}
                    @if(count($prefs) > 3) <span>+{{ count($prefs) - 3 }} más</span> @endif
                </div>
                @endif
                @endif
            </td>

            {{-- Motivo --}}
            <td style="padding:.6rem .8rem;max-width:220px;">
                <div style="font-size:.78rem;color:var(--theme-muted);overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;line-height:1.4;">
                    {{ $inv->motivo ?? '—' }}
                </div>
            </td>

            {{-- Estado --}}
            <td style="padding:.6rem .8rem;text-align:center;">
                @if($inv->status === 'approved')
                    <span style="background:#22c55e22;color:#22c55e;padding:.2rem .55rem;border-radius:20px;font-size:.72rem;font-weight:600;">
                        <i class="fas fa-check"></i> Aprobado
                    </span>
                @elseif($inv->status === 'rejected')
                    <span style="background:#ef444422;color:#ef4444;padding:.2rem .55rem;border-radius:20px;font-size:.72rem;font-weight:600;">
                        <i class="fas fa-times"></i> Rechazado
                    </span>
                @else
                    <span style="background:#f59e0b22;color:#f59e0b;padding:.2rem .55rem;border-radius:20px;font-size:.72rem;font-weight:600;">
                        <i class="fas fa-clock"></i> Pendiente
                    </span>
                @endif
                @if($inv->terminos_aceptados)
                    <div style="font-size:.65rem;color:#22c55e;margin-top:.2rem;">
                        <i class="fas fa-check-circle"></i> T&C aceptados
                    </div>
                @endif
            </td>

            {{-- Fecha --}}
            <td style="padding:.6rem .8rem;color:var(--theme-muted);font-size:.75rem;">
                {{ \Carbon\Carbon::parse($inv->created_at)->format('d/m/Y H:i') }}
                <div style="font-size:.68rem;">
                    {{ \Carbon\Carbon::parse($inv->created_at)->diffForHumans() }}
                </div>
            </td>

            {{-- Acciones --}}
            <td style="padding:.6rem .8rem;text-align:center;">
                <div style="display:flex;gap:.35rem;justify-content:center;">

                    {{-- Ver detalle --}}
                    <a href="{{ route('admin.invitations.show', $inv->id) }}"
                       style="padding:.25rem .6rem;background:var(--theme-accent);color:#fff;border-radius:5px;font-size:.75rem;text-decoration:none;"
                       title="Ver detalle">
                        <i class="fas fa-eye"></i>
                    </a>

                    @if($inv->status === 'pending')
                    {{-- Aprobar --}}
                    <form method="POST" action="{{ route('admin.invitations.approve', $inv->id) }}">
                        @csrf
                        <button type="submit"
                                onclick="return confirm('¿Aprobar a {{ addslashes($inv->nombre ?? $inv->email) }}? Se creará su cuenta.')"
                                style="padding:.25rem .6rem;background:#22c55e22;color:#22c55e;border:1px solid #22c55e;border-radius:5px;font-size:.75rem;cursor:pointer;"
                                title="Aprobar">
                            <i class="fas fa-check"></i>
                        </button>
                    </form>

                    {{-- Rechazar --}}
                    <button onclick="abrirRechazo('{{ $inv->id }}')"
                            style="padding:.25rem .6rem;background:#ef444422;color:#ef4444;border:1px solid #ef4444;border-radius:5px;font-size:.75rem;cursor:pointer;"
                            title="Rechazar">
                        <i class="fas fa-times"></i>
                    </button>
                    @endif

                </div>
            </td>

        </tr>
        @empty
        <tr>
            <td colspan="6" style="padding:2.5rem;text-align:center;color:var(--theme-muted);">
                <i class="fas fa-envelope-open" style="font-size:2rem;opacity:.2;display:block;margin-bottom:.5rem;"></i>
                No hay invitaciones con el filtro seleccionado.
            </td>
        </tr>
        @endforelse
        </tbody>
    </table>
</div>

@if($invitations->hasPages())
<div style="margin-top:1rem;display:flex;justify-content:center;">
    {{ $invitations->appends(request()->query())->links() }}
</div>
@endif

{{-- Modal rechazo --}}
<div id="modalRechazo" style="display:none;position:fixed;inset:0;background:#000a;z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--theme-card);border-radius:12px;padding:1.5rem;max-width:460px;width:90%;">
        <h3 style="margin:0 0 1rem;font-size:1rem;color:var(--theme-text);">
            <i class="fas fa-times-circle" style="color:#ef4444;"></i> Rechazar invitación
        </h3>
        <form method="POST" id="formRechazo" action="">
            @csrf
            <div style="margin-bottom:1rem;">
                <label style="display:block;font-size:.8rem;color:var(--theme-muted);margin-bottom:.4rem;">
                    Motivo del rechazo <span style="font-weight:400;">(opcional)</span>
                </label>
                <textarea name="reason" rows="3"
                          placeholder="Explica el motivo del rechazo…"
                          style="width:100%;padding:.55rem .85rem;border-radius:8px;border:1px solid var(--theme-border);background:var(--theme-bg);color:var(--theme-text);font-size:.88rem;resize:none;"></textarea>
            </div>
            <div style="display:flex;gap:.75rem;justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('modalRechazo').style.display='none'"
                        style="padding:.45rem 1rem;border:1px solid var(--theme-border);color:var(--theme-muted);border-radius:8px;background:none;cursor:pointer;font-size:.85rem;">
                    Cancelar
                </button>
                <button type="submit"
                        style="padding:.45rem 1.2rem;background:#ef4444;color:#fff;border:none;border-radius:8px;cursor:pointer;font-size:.85rem;font-weight:600;">
                    <i class="fas fa-times"></i> Rechazar
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function abrirRechazo(id) {
    document.getElementById('formRechazo').action = '/admin/invitaciones/' + id + '/rechazar';
    document.getElementById('modalRechazo').style.display = 'flex';
}
document.getElementById('modalRechazo').addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
});
</script>
@endpush
