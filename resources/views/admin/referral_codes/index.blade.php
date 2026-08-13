@extends('layouts.admin')

@section('title', 'Codigos de Referido')
@section('page-title', 'Codigos de Referido')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1" style="color:var(--theme-text);font-weight:600;">Codigos de Referido</h4>
        <p class="mb-0" style="color:var(--theme-muted);font-size:.875rem;">Gestiona los codigos de invitacion del sistema</p>
    </div>
    <a href="{{ route('admin.admin.referral-codes.create') }}" class="btn btn-primary px-4">+ Nuevo codigo</a>
</div>

<div style="background:var(--theme-card);border:1px solid var(--theme-border);border-radius:12px;overflow:hidden;">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr style="background:rgba(0,0,0,.04);border-bottom:2px solid var(--theme-border);">
                    <th style="color:var(--theme-muted);font-size:.7rem;text-transform:uppercase;letter-spacing:.08em;padding:1rem 1.25rem;">Codigo</th>
                    <th style="color:var(--theme-muted);font-size:.7rem;text-transform:uppercase;letter-spacing:.08em;padding:1rem 1.25rem;">Propietario</th>
                    <th style="color:var(--theme-muted);font-size:.7rem;text-transform:uppercase;letter-spacing:.08em;padding:1rem 1.25rem;">Usos</th>
                    <th style="color:var(--theme-muted);font-size:.7rem;text-transform:uppercase;letter-spacing:.08em;padding:1rem 1.25rem;">Estado</th>
                    <th style="color:var(--theme-muted);font-size:.7rem;text-transform:uppercase;letter-spacing:.08em;padding:1rem 1.25rem;">Expira</th>
                    <th style="color:var(--theme-muted);font-size:.7rem;text-transform:uppercase;letter-spacing:.08em;padding:1rem 1.25rem;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($codes as $code)
                <tr style="border-bottom:1px solid var(--theme-border);">
                    <td style="padding:1rem 1.25rem;vertical-align:middle;">
                        <code style="background:rgba(212,175,55,.12);color:#d4af37;border:1px solid rgba(212,175,55,.25);padding:.3rem .7rem;border-radius:6px;font-size:.85rem;font-weight:600;letter-spacing:.06em;">{{ $code->code }}</code>
                    </td>
                    <td style="padding:1rem 1.25rem;vertical-align:middle;color:var(--theme-text);font-size:.9rem;">
                        {{ $code->owner?->username ?? '— Sistema —' }}
                    </td>
                    <td style="padding:1rem 1.25rem;vertical-align:middle;min-width:150px;">
                        @php
                            $pct = $code->max_uses > 0 ? round(($code->uses_count / $code->max_uses) * 100) : 0;
                            $bar = $pct >= 90 ? '#e74c3c' : ($pct >= 60 ? '#f39c12' : '#2ecc71');
                        @endphp
                        <div style="color:var(--theme-text);font-size:.875rem;margin-bottom:.4rem;">
                            <strong>{{ $code->uses_count }}</strong><span style="color:var(--theme-muted);"> / {{ $code->max_uses }}</span>
                            <small style="color:var(--theme-muted);"> ({{ $pct }}%)</small>
                        </div>
                        <div style="height:5px;background:var(--theme-border);border-radius:3px;">
                            <div style="height:5px;background:{{ $bar }};border-radius:3px;width:{{ $pct }}%;"></div>
                        </div>
                    </td>
                    <td style="padding:1rem 1.25rem;vertical-align:middle;">
                        @if($code->is_active && $code->isValid())
                            <span style="display:inline-flex;align-items:center;gap:.3rem;background:rgba(46,204,113,.12);color:#27ae60;border:1px solid rgba(46,204,113,.3);padding:.3rem .75rem;border-radius:20px;font-size:.8rem;font-weight:600;">
                                <span style="width:6px;height:6px;background:#27ae60;border-radius:50%;display:inline-block;"></span> Activo
                            </span>
                        @elseif(!$code->is_active)
                            <span style="display:inline-flex;align-items:center;gap:.3rem;background:rgba(231,76,60,.12);color:#e74c3c;border:1px solid rgba(231,76,60,.3);padding:.3rem .75rem;border-radius:20px;font-size:.8rem;font-weight:600;">
                                <span style="width:6px;height:6px;background:#e74c3c;border-radius:50%;display:inline-block;"></span> Inactivo
                            </span>
                        @else
                            <span style="display:inline-flex;align-items:center;gap:.3rem;background:rgba(243,156,18,.12);color:#e67e22;border:1px solid rgba(243,156,18,.3);padding:.3rem .75rem;border-radius:20px;font-size:.8rem;font-weight:600;">
                                <span style="width:6px;height:6px;background:#e67e22;border-radius:50%;display:inline-block;"></span> Agotado
                            </span>
                        @endif
                    </td>
                    <td style="padding:1rem 1.25rem;vertical-align:middle;font-size:.875rem;">
                        @if($code->expires_at)
                            @if(now()->gt($code->expires_at))
                                <span style="color:#e74c3c;font-weight:500;">{{ $code->expires_at->format('d/m/Y') }}<br><small>Vencido</small></span>
                            @else
                                <span style="color:var(--theme-text);">{{ $code->expires_at->format('d/m/Y') }}</span>
                            @endif
                        @else
                            <span style="color:var(--theme-muted);">Sin limite</span>
                        @endif
                    </td>
                    <td style="padding:1rem 1.25rem;vertical-align:middle;">
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.admin.referral-codes.edit', $code) }}"
                               style="padding:.35rem .8rem;border-radius:6px;border:1px solid var(--theme-border);color:var(--theme-text);font-size:.825rem;text-decoration:none;">
                               Editar
                            </a>
                            <form method="POST" action="{{ route('admin.admin.referral-codes.destroy', $code) }}"
                                  onsubmit="return confirm('Eliminar {{ $code->code }}?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        style="padding:.35rem .8rem;border-radius:6px;border:1px solid rgba(231,76,60,.4);color:#e74c3c;font-size:.825rem;background:transparent;cursor:pointer;">
                                    Borrar
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="padding:3rem;text-align:center;color:var(--theme-muted);">
                        No hay codigos aun.<br>
                        <a href="{{ route('admin.admin.referral-codes.create') }}" class="btn btn-primary mt-3">Crear el primero</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $codes->links() }}</div>
@endsection

