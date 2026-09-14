@extends('layouts.admin')
@section('title', 'Moderación de Anuncios')
@section('page-title', 'Moderación de Anuncios')

@section('content')

@if(session('success'))
<div style="background:rgba(46,204,113,.12);border:1px solid rgba(46,204,113,.3);color:#2ecc71;
            padding:.75rem 1rem;border-radius:8px;margin-bottom:1.25rem;font-size:.875rem;">
    <i class="fas fa-check-circle" style="margin-right:.4rem;"></i>{{ session('success') }}
</div>
@endif

{{-- Stats --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.75rem;">
    @foreach([
        ['label'=>'Total',    'value'=>$stats['total'],   'color'=>'#a78bfa', 'icon'=>'fa-bullhorn'],
        ['label'=>'Activos',  'value'=>$stats['active'],  'color'=>'#2ecc71', 'icon'=>'fa-check-circle'],
        ['label'=>'Cerrados', 'value'=>$stats['closed'],  'color'=>'#e67e22', 'icon'=>'fa-times-circle'],
        ['label'=>'Expirados','value'=>$stats['expired'], 'color'=>'#e74c3c', 'icon'=>'fa-clock'],
    ] as $s)
    <div style="background:var(--adm-card);border:1px solid var(--adm-border);border-radius:12px;padding:1rem;text-align:center;">
        <i class="fas {{ $s['icon'] }}" style="font-size:1.4rem;color:{{ $s['color'] }};margin-bottom:.4rem;display:block;"></i>
        <div style="font-size:1.5rem;font-weight:800;color:var(--adm-text);">{{ $s['value'] }}</div>
        <div style="font-size:.75rem;color:var(--adm-muted);">{{ $s['label'] }}</div>
    </div>
    @endforeach
</div>

{{-- Tabla --}}
<div class="adm-card" style="overflow:hidden;">
    <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--adm-border);display:flex;align-items:center;justify-content:space-between;">
        <h2 style="margin:0;font-size:1rem;font-weight:700;color:var(--adm-text);">
            <i class="fas fa-list" style="color:#e056a0;margin-right:.5rem;"></i>Todos los anuncios
        </h2>
        <span style="font-size:.8rem;color:var(--adm-muted);">{{ $announcements->total() }} total</span>
    </div>

    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:.85rem;">
            <thead>
                <tr style="background:var(--adm-bg);">
                    <th style="padding:.75rem 1rem;text-align:left;font-size:.72rem;font-weight:600;color:var(--adm-muted);text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--adm-border);">Usuario</th>
                    <th style="padding:.75rem 1rem;text-align:left;font-size:.72rem;font-weight:600;color:var(--adm-muted);text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--adm-border);">Anuncio</th>
                    <th style="padding:.75rem 1rem;text-align:center;font-size:.72rem;font-weight:600;color:var(--adm-muted);text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--adm-border);">Estado</th>
                    <th style="padding:.75rem 1rem;text-align:center;font-size:.72rem;font-weight:600;color:var(--adm-muted);text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--adm-border);">Expira</th>
                    <th style="padding:.75rem 1rem;text-align:center;font-size:.72rem;font-weight:600;color:var(--adm-muted);text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--adm-border);">Publicado</th>
                    <th style="padding:.75rem 1rem;text-align:center;font-size:.72rem;font-weight:600;color:var(--adm-muted);text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--adm-border);">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($announcements as $a)
                @php
                    $expires   = \Carbon\Carbon::parse($a->expires_at);
                    $isExpired = $expires->lt($now);
                    $rowBg     = $loop->even ? 'background:rgba(255,255,255,.02);' : '';
                @endphp
                <tr style="{{ $rowBg }}border-bottom:1px solid var(--adm-border);">

                    {{-- Usuario --}}
                    <td style="padding:.75rem 1rem;">
                        <div style="font-weight:600;color:var(--adm-text);">{{ $a->display_name }}</div>
                        <div style="font-size:.72rem;color:var(--adm-muted);">{{ $a->email }}</div>
                        @if($a->profile_type)
                        <div style="font-size:.7rem;color:#a78bfa;margin-top:.15rem;">{{ ucfirst($a->profile_type) }}</div>
                        @endif
                    </td>

                    {{-- Título --}}
                    <td style="padding:.75rem 1rem;max-width:260px;">
                        <div style="font-weight:600;color:var(--adm-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:240px;">
                            {{ $a->title }}
                        </div>
                        <div style="font-size:.72rem;color:var(--adm-muted);margin-top:.15rem;">
                            ID: {{ substr($a->id, 0, 8) }}...
                        </div>
                    </td>

                    {{-- Estado --}}
                    <td style="padding:.75rem 1rem;text-align:center;">
                        @if($isExpired && $a->status === 'active')
                            <span style="padding:.2rem .65rem;border-radius:20px;font-size:.7rem;font-weight:700;background:rgba(231,76,60,.12);color:#e74c3c;border:1px solid rgba(231,76,60,.25);">
                                Expirado
                            </span>
                        @elseif($a->status === 'active')
                            <span style="padding:.2rem .65rem;border-radius:20px;font-size:.7rem;font-weight:700;background:rgba(46,204,113,.1);color:#2ecc71;border:1px solid rgba(46,204,113,.25);">
                                Activo
                            </span>
                        @else
                            <span style="padding:.2rem .65rem;border-radius:20px;font-size:.7rem;font-weight:700;background:rgba(230,126,34,.1);color:#e67e22;border:1px solid rgba(230,126,34,.25);">
                                Cerrado
                            </span>
                        @endif
                    </td>

                    {{-- Expira --}}
                    <td style="padding:.75rem 1rem;text-align:center;font-size:.8rem;color:{{ $isExpired ? '#e74c3c' : 'var(--adm-muted)' }};">
                        {{ $expires->format('d M Y') }}
                    </td>

                    {{-- Fecha --}}
                    <td style="padding:.75rem 1rem;text-align:center;font-size:.8rem;color:var(--adm-muted);">
                        {{ \Carbon\Carbon::parse($a->created_at)->format('d M Y') }}<br>
                        <span style="font-size:.7rem;">{{ \Carbon\Carbon::parse($a->created_at)->format('H:i') }}</span>
                    </td>

                    {{-- Acciones --}}
                    <td style="padding:.75rem 1rem;text-align:center;">
                        <div style="display:flex;gap:.5rem;justify-content:center;align-items:center;">

                            {{-- Cerrar (solo si activo y no expirado) --}}
                            @if($a->status === 'active' && !$isExpired)
                            <form method="POST" action="{{ route('admin.announcements.close', $a->id) }}"
                                  onsubmit="return confirm('¿Cerrar este anuncio?')">
                                @csrf @method('PATCH')
                                <button type="submit"
                                    title="Cerrar anuncio"
                                    style="padding:.3rem .7rem;border-radius:7px;border:1px solid rgba(230,126,34,.4);
                                           color:#e67e22;background:transparent;font-size:.78rem;cursor:pointer;
                                           display:inline-flex;align-items:center;gap:.3rem;">
                                    <i class="fas fa-ban"></i> Cerrar
                                </button>
                            </form>
                            @endif

                            {{-- Eliminar --}}
                            <form method="POST" action="{{ route('announcements.destroy', $a->id) }}"
                                  onsubmit="return confirm('¿Eliminar este anuncio permanentemente? Esta acción no se puede deshacer.')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    title="Eliminar permanentemente"
                                    style="padding:.3rem .7rem;border-radius:7px;border:1px solid rgba(231,76,60,.4);
                                           color:#e74c3c;background:transparent;font-size:.78rem;cursor:pointer;
                                           display:inline-flex;align-items:center;gap:.3rem;">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="padding:3rem;text-align:center;color:var(--adm-muted);">
                        <i class="fas fa-inbox" style="font-size:2rem;display:block;margin-bottom:.75rem;opacity:.4;"></i>
                        No hay anuncios registrados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginación --}}
    @if($announcements->hasPages())
    <div style="padding:1rem 1.25rem;border-top:1px solid var(--adm-border);">
        {{ $announcements->links() }}
    </div>
    @endif
</div>

@endsection
