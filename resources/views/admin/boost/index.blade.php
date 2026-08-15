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

    {{-- Tabs --}}
    <div style="display:flex;gap:.5rem;margin-bottom:1.25rem;border-bottom:1px solid var(--theme-border);padding-bottom:.75rem;">
        <a href="?tab=profiles&q={{ $search }}"
           style="padding:.4rem 1rem;border-radius:8px;font-size:.82rem;font-weight:700;text-decoration:none;
                  {{ $tab === 'profiles' ? 'background:linear-gradient(135deg,#6C3FC5,#e056a0);color:#fff;' : 'color:var(--theme-muted);background:var(--theme-card);border:1px solid var(--theme-border);' }}">
            <i class="fas fa-users"></i> Perfiles
        </a>
        <a href="?tab=history"
           style="padding:.4rem 1rem;border-radius:8px;font-size:.82rem;font-weight:700;text-decoration:none;
                  {{ $tab === 'history' ? 'background:linear-gradient(135deg,#6C3FC5,#e056a0);color:#fff;' : 'color:var(--theme-muted);background:var(--theme-card);border:1px solid var(--theme-border);' }}">
            <i class="fas fa-history"></i> Historial
            @if($history->count() > 0)
            <span style="background:rgba(255,255,255,.2);border-radius:999px;padding:0 6px;font-size:.75rem;">
                {{ $history->count() }}
            </span>
            @endif
        </a>
    </div>

    {{-- TAB: Perfiles --}}
    @if($tab === 'profiles')

    <form method="GET" style="display:flex;gap:.5rem;margin-bottom:1.25rem;">
        <input type="hidden" name="tab" value="profiles">
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

    <div style="background:var(--theme-card);border:1px solid var(--theme-border);border-radius:12px;overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;font-size:.82rem;">
            <thead>
                <tr style="background:rgba(108,63,197,.08);border-bottom:1px solid var(--theme-border);">
                    <th style="padding:.65rem 1rem;text-align:left;color:var(--theme-muted);font-weight:600;">Perfil</th>
                    <th style="padding:.65rem 1rem;text-align:center;color:var(--theme-muted);font-weight:600;">Score</th>
                    <th style="padding:.65rem 1rem;text-align:center;color:var(--theme-muted);font-weight:600;">Boost activo</th>
                    <th style="padding:.65rem 1rem;text-align:center;color:var(--theme-muted);font-weight:600;">Aplicar boost</th>
                </tr>
            </thead>
            <tbody>
            @forelse($profiles as $p)
            @php
                $hasBoost = $p->boost_until && \Carbon\Carbon::parse($p->boost_until)->isFuture();
            @endphp
            <tr style="border-bottom:1px solid var(--theme-border);">
                <td style="padding:.65rem 1rem;">
                    <div style="font-weight:700;color:var(--theme-text);">{{ $p->nickname ?? '—' }}</div>
                    <div style="font-size:.75rem;color:var(--theme-muted);">{{ $p->email }}</div>
                </td>
                <td style="padding:.65rem 1rem;text-align:center;color:#f59e0b;font-weight:700;">
                    {{ number_format($p->recommendation_score ?? 0, 2) }} ★
                </td>
                <td style="padding:.65rem 1rem;text-align:center;">
                    @if($hasBoost)
                        <span style="color:#22c55e;font-weight:700;font-size:.8rem;">
                            +{{ $p->boost_amount }} pts<br>
                            <span style="font-size:.7rem;color:var(--theme-muted);">
                                hasta {{ \Carbon\Carbon::parse($p->boost_until)->format('d/m H:i') }}
                            </span>
                        </span>
                        <form method="POST" action="{{ route('admin.boost.remove', $p->user_id) }}" style="margin-top:.3rem;">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    style="background:rgba(239,68,68,.15);color:#ef4444;border:1px solid rgba(239,68,68,.3);
                                           border-radius:6px;padding:.2rem .6rem;font-size:.72rem;cursor:pointer;">
                                <i class="fas fa-times"></i> Quitar
                            </button>
                        </form>
                    @else
                        <span style="color:var(--theme-muted);font-size:.78rem;">Sin boost</span>
                    @endif
                </td>
                <td style="padding:.65rem 1rem;">
                    <form method="POST" action="{{ route('admin.boost.apply', $p->user_id) }}"
                          style="display:flex;flex-wrap:wrap;gap:.35rem;align-items:center;justify-content:center;">
                        @csrf
                        <input type="number" name="boost_amount" step="0.1" min="0.1" max="5"
                               placeholder="Pts" value="0.5"
                               style="width:60px;background:var(--theme-bg);border:1px solid var(--theme-border);
                                      border-radius:6px;padding:.3rem .5rem;color:var(--theme-text);font-size:.8rem;">
                        <input type="datetime-local" name="boost_until"
                               style="background:var(--theme-bg);border:1px solid var(--theme-border);
                                      border-radius:6px;padding:.3rem .5rem;color:var(--theme-text);font-size:.8rem;">
                        <input type="text" name="notes" placeholder="Nota (opcional)"
                               style="width:120px;background:var(--theme-bg);border:1px solid var(--theme-border);
                                      border-radius:6px;padding:.3rem .5rem;color:var(--theme-text);font-size:.8rem;">
                        <button type="submit"
                                style="background:linear-gradient(135deg,#6C3FC5,#e056a0);border:none;
                                       border-radius:6px;color:#fff;padding:.3rem .75rem;font-size:.8rem;
                                       font-weight:700;cursor:pointer;">
                            <i class="fas fa-bolt"></i> Aplicar
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="padding:2rem;text-align:center;color:var(--theme-muted);">
                    No se encontraron perfiles.
                </td>
            </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:1rem;">{{ $profiles->links() }}</div>

    {{-- TAB: Historial --}}
    @else

    <div style="background:var(--theme-card);border:1px solid var(--theme-border);border-radius:12px;overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;font-size:.82rem;">
            <thead>
                <tr style="background:rgba(108,63,197,.08);border-bottom:1px solid var(--theme-border);">
                    <th style="padding:.65rem 1rem;text-align:left;color:var(--theme-muted);font-weight:600;">Perfil</th>
                    <th style="padding:.65rem 1rem;text-align:center;color:var(--theme-muted);font-weight:600;">Acción</th>
                    <th style="padding:.65rem 1rem;text-align:center;color:var(--theme-muted);font-weight:600;">Boost</th>
                    <th style="padding:.65rem 1rem;text-align:left;color:var(--theme-muted);font-weight:600;">Admin</th>
                    <th style="padding:.65rem 1rem;text-align:left;color:var(--theme-muted);font-weight:600;">Nota</th>
                    <th style="padding:.65rem 1rem;text-align:center;color:var(--theme-muted);font-weight:600;">Fecha</th>
                </tr>
            </thead>
            <tbody>
            @forelse($history as $h)
            <tr style="border-bottom:1px solid var(--theme-border);">
                <td style="padding:.65rem 1rem;font-weight:700;color:var(--theme-text);">
                    {{ $h->profile_nick ?? '—' }}
                </td>
                <td style="padding:.65rem 1rem;text-align:center;">
                    @if($h->action === 'applied')
                        <span style="background:rgba(34,197,94,.12);color:#22c55e;border-radius:999px;
                                     padding:.2rem .7rem;font-size:.75rem;font-weight:700;">
                            <i class="fas fa-bolt"></i> Aplicado
                        </span>
                    @else
                        <span style="background:rgba(239,68,68,.12);color:#ef4444;border-radius:999px;
                                     padding:.2rem .7rem;font-size:.75rem;font-weight:700;">
                            <i class="fas fa-times"></i> Eliminado
                        </span>
                    @endif
                </td>
                <td style="padding:.65rem 1rem;text-align:center;color:#f59e0b;font-weight:700;">
                    @if($h->boost_amount > 0)
                        +{{ $h->boost_amount }} pts
                        @if($h->boost_until)
                        <div style="font-size:.7rem;color:var(--theme-muted);">
                            hasta {{ \Carbon\Carbon::parse($h->boost_until)->format('d/m H:i') }}
                        </div>
                        @endif
                    @else
                        <span style="color:var(--theme-muted);">—</span>
                    @endif
                </td>
                <td style="padding:.65rem 1rem;font-size:.78rem;color:var(--theme-muted);">
                    {{ $h->admin_email ?? '—' }}
                </td>
                <td style="padding:.65rem 1rem;font-size:.78rem;color:var(--theme-muted);">
                    {{ $h->notes ?? '—' }}
                </td>
                <td style="padding:.65rem 1rem;text-align:center;font-size:.78rem;color:var(--theme-muted);">
                    {{ \Carbon\Carbon::parse($h->created_at)->format('d/m/y H:i') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="padding:2rem;text-align:center;color:var(--theme-muted);">
                    No hay historial de boosts aún.
                </td>
            </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @endif

</div>
@endsection
