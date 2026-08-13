@extends('layouts.admin')
@section('title', 'Panel de Boost — Admin')

@section('content')
<div class="adm-content">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
        <div>
            <h1 style="font-size:1.3rem;font-weight:800;color:var(--theme-text);margin:0;">
                <i class="fas fa-star" style="color:#f59e0b;"></i> Panel de Boost
            </h1>
            <p style="font-size:.82rem;color:var(--theme-muted);margin:.25rem 0 0;">
                Asigna puntos extra temporales a perfiles destacados.
            </p>
        </div>
    </div>

    @if(session('success'))
    <div style="background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.3);border-radius:8px;
                padding:.75rem 1rem;margin-bottom:1rem;color:#22c55e;font-size:.85rem;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    {{-- Buscador --}}
    <form method="GET" style="display:flex;gap:.5rem;margin-bottom:1.25rem;">
        <input type="text" name="q" value="{{ $search }}"
               placeholder="Buscar por nickname…"
               style="flex:1;background:var(--theme-bg);border:1px solid var(--theme-border);
                      border-radius:8px;padding:.5rem .85rem;color:var(--theme-text);font-size:.85rem;">
        <button type="submit"
                style="background:linear-gradient(135deg,#6C3FC5,#e056a0);border:none;border-radius:8px;
                       color:#fff;padding:.5rem 1.25rem;font-size:.85rem;font-weight:700;cursor:pointer;">
            <i class="fas fa-search"></i> Buscar
        </button>
    </form>

    {{-- Tabla --}}
    <div style="background:var(--theme-card);border:1px solid var(--theme-border);border-radius:12px;overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;font-size:.82rem;">
            <thead>
                <tr style="background:rgba(108,63,197,.15);color:var(--theme-muted);text-align:left;">
                    <th style="padding:.65rem 1rem;">Perfil</th>
                    <th style="padding:.65rem 1rem;">Score actual</th>
                    <th style="padding:.65rem 1rem;">Boost activo</th>
                    <th style="padding:.65rem 1rem;">Aplicar boost</th>
                    <th style="padding:.65rem 1rem;"></th>
                </tr>
            </thead>
            <tbody>
            @forelse($profiles as $p)
            @php
                $hasBoost = $p->boost_until && \Carbon\Carbon::parse($p->boost_until)->isFuture();
                $totalScore = min(5.0, floatval($p->recommendation_score));
                $bFull  = (int) floor($totalScore);
                $bHalf  = ($totalScore - $bFull) >= 0.4 ? 1 : 0;
            @endphp
            <tr style="border-top:1px solid var(--theme-border);">
                {{-- Perfil --}}
                <td style="padding:.65rem 1rem;">
                    <div style="font-weight:700;color:var(--theme-text);">{{ $p->nickname ?? '—' }}</div>
                    <div style="font-size:.72rem;color:var(--theme-muted);">{{ $p->email }} · {{ $p->profile_type ?? '—' }}</div>
                </td>

                {{-- Score --}}
                <td style="padding:.65rem 1rem;">
                    <div style="color:#f59e0b;font-size:.9rem;">
                        @for($i=0;$i<$bFull;$i++)<i class="fa fa-star"></i>@endfor
                        @if($bHalf)<i class="fa fa-star-half-o"></i>@endif
                    </div>
                    <div style="font-size:.72rem;color:var(--theme-muted);">{{ number_format($totalScore,2) }} / 5.00</div>
                </td>

                {{-- Boost activo --}}
                <td style="padding:.65rem 1rem;">
                    @if($hasBoost)
                    <span style="background:rgba(245,158,11,.15);color:#f59e0b;border:1px solid rgba(245,158,11,.3);
                                 border-radius:20px;padding:.2rem .6rem;font-size:.72rem;font-weight:700;">
                        +{{ number_format($p->boost_amount,2) }} ⭐
                    </span>
                    <div style="font-size:.68rem;color:var(--theme-muted);margin-top:.2rem;">
                        Hasta {{ \Carbon\Carbon::parse($p->boost_until)->format('d/m/Y H:i') }}
                    </div>
                    @else
                    <span style="color:var(--theme-muted);font-size:.75rem;">Sin boost</span>
                    @endif
                </td>

                {{-- Formulario boost --}}
                <td style="padding:.65rem 1rem;">
                    <form method="POST" action="{{ route('admin.boost.apply', $p->user_id) }}"
                          style="display:flex;gap:.4rem;align-items:center;">
                        @csrf
                        <input type="number" name="boost_amount" step="0.25" min="0" max="5"
                               value="{{ $hasBoost ? $p->boost_amount : 0.5 }}"
                               style="width:60px;background:var(--theme-bg);border:1px solid var(--theme-border);
                                      border-radius:6px;padding:.3rem .5rem;color:var(--theme-text);font-size:.8rem;">
                        <input type="datetime-local" name="boost_until"
                               value="{{ $hasBoost ? \Carbon\Carbon::parse($p->boost_until)->format('Y-m-d\TH:i') : \Carbon\Carbon::now()->addDays(7)->format('Y-m-d\TH:i') }}"
                               style="background:var(--theme-bg);border:1px solid var(--theme-border);
                                      border-radius:6px;padding:.3rem .5rem;color:var(--theme-text);font-size:.75rem;">
                        <button type="submit"
                                style="background:rgba(245,158,11,.2);border:1px solid rgba(245,158,11,.4);
                                       border-radius:6px;color:#f59e0b;padding:.3rem .65rem;font-size:.75rem;
                                       font-weight:700;cursor:pointer;white-space:nowrap;">
                            <i class="fas fa-bolt"></i> Aplicar
                        </button>
                    </form>
                </td>

                {{-- Quitar boost --}}
                <td style="padding:.65rem 1rem;">
                    @if($hasBoost)
                    <form method="POST" action="{{ route('admin.boost.remove', $p->user_id) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);
                                       border-radius:6px;color:#f87171;padding:.3rem .65rem;
                                       font-size:.75rem;cursor:pointer;"
                                onclick="return confirm('¿Quitar boost a {{ $p->nickname }}?')">
                            <i class="fas fa-times"></i> Quitar
                        </button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="padding:2rem;text-align:center;color:var(--theme-muted);">
                    No se encontraron perfiles.
                </td>
            </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginación --}}
    @if($profiles->hasPages())
    <div style="margin-top:1rem;display:flex;justify-content:center;gap:.5rem;">
        @if($profiles->onFirstPage())
            <span style="padding:.4rem .75rem;border-radius:6px;background:var(--theme-bg);color:var(--theme-muted);font-size:.8rem;">← Anterior</span>
        @else
            <a href="{{ $profiles->previousPageUrl() }}" style="padding:.4rem .75rem;border-radius:6px;background:var(--theme-card);border:1px solid var(--theme-border);color:var(--theme-text);font-size:.8rem;text-decoration:none;">← Anterior</a>
        @endif
        @if($profiles->hasMorePages())
            <a href="{{ $profiles->nextPageUrl() }}" style="padding:.4rem .75rem;border-radius:6px;background:var(--theme-card);border:1px solid var(--theme-border);color:var(--theme-text);font-size:.8rem;text-decoration:none;">Siguiente →</a>
        @else
            <span style="padding:.4rem .75rem;border-radius:6px;background:var(--theme-bg);color:var(--theme-muted);font-size:.8rem;">Siguiente →</span>
        @endif
    </div>
    @endif
</div>
@endsection