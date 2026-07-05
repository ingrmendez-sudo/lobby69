@extends('layouts.admin')
@php use Illuminate\Support\Facades\Storage; @endphp

@section('title', 'Membresías')
@section('page-title', 'Gestión de Membresías')

@section('content')

{{-- Stats --}}
@if($stats)
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;margin-bottom:1.5rem;">
    <div class="adm-card" style="padding:1rem;text-align:center;">
        <div style="font-size:1.5rem;font-weight:800;color:#22c55e;">
            ${{ number_format($stats->total_aprobado, 2) }}
        </div>
        <div style="font-size:.72rem;color:var(--theme-muted);text-transform:uppercase;">Ingresos aprobados</div>
    </div>
    <div class="adm-card" style="padding:1rem;text-align:center;">
        <div style="font-size:1.5rem;font-weight:800;color:#f59e0b;">
            ${{ number_format($stats->total_pendiente, 2) }}
        </div>
        <div style="font-size:.72rem;color:var(--theme-muted);text-transform:uppercase;">Pendiente de aprobar</div>
    </div>
    <div class="adm-card" style="padding:1rem;text-align:center;">
        <div style="font-size:1.5rem;font-weight:800;color:var(--theme-accent);">
            {{ $stats->total }}
        </div>
        <div style="font-size:.72rem;color:var(--theme-muted);text-transform:uppercase;">Solicitudes totales</div>
    </div>
</div>
@endif

{{-- Notificación --}}
@if(session('success'))
<div style="background:#22c55e22;border:1px solid #22c55e;color:#22c55e;padding:.75rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.85rem;">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

{{-- Tabs de estado --}}
<div style="display:flex;gap:.5rem;margin-bottom:1.25rem;flex-wrap:wrap;">
    @foreach(['pending' => ['Pendientes','#f59e0b'], 'approved' => ['Aprobados','#22c55e'], 'rejected' => ['Rechazados','#ef4444']] as $key => [$label, $color])
    <a href="{{ route('admin.memberships.index', ['status' => $key]) }}"
       style="padding:.4rem 1rem;border-radius:20px;font-size:.82rem;text-decoration:none;font-weight:600;
              background:{{ $status === $key ? $color.'33' : 'var(--theme-card)' }};
              color:{{ $status === $key ? $color : 'var(--theme-muted)' }};
              border:1px solid {{ $status === $key ? $color : 'var(--theme-border)' }};">
        {{ $label }}
        <span style="background:{{ $color }}33;color:{{ $color }};padding:.1rem .45rem;border-radius:20px;font-size:.72rem;margin-left:.3rem;">
            {{ $counts[$key] }}
        </span>
    </a>
    @endforeach

    {{-- Botón registrar pago manual --}}
    <button onclick="document.getElementById('modalRegistrar').style.display='flex'"
            style="margin-left:auto;padding:.4rem 1rem;background:var(--theme-accent);color:#fff;border:none;border-radius:20px;font-size:.82rem;cursor:pointer;font-weight:600;">
        <i class="fas fa-plus"></i> Registrar pago manual
    </button>
</div>

{{-- Tabla pagos --}}
@if($payments->isEmpty())
<div class="adm-card" style="padding:3rem;text-align:center;color:var(--theme-muted);">
    <i class="fas fa-credit-card" style="font-size:3rem;opacity:.2;display:block;margin-bottom:1rem;"></i>
    <p style="font-size:.9rem;">No hay pagos con estado <strong>{{ $status }}</strong>.</p>
</div>
@else
<div class="adm-card" style="overflow-x:auto;">
    <table style="width:100%;border-collapse:collapse;font-size:.83rem;">
        <thead>
            <tr style="border-bottom:2px solid var(--theme-border);">
                <th style="padding:.6rem .8rem;color:var(--theme-muted);font-weight:600;text-align:left;">Usuario</th>
                <th style="padding:.6rem .8rem;color:var(--theme-muted);font-weight:600;text-align:left;">Solicitud</th>
                <th style="padding:.6rem .8rem;color:var(--theme-muted);font-weight:600;text-align:right;">Monto</th>
                <th style="padding:.6rem .8rem;color:var(--theme-muted);font-weight:600;text-align:left;">Método</th>
                <th style="padding:.6rem .8rem;color:var(--theme-muted);font-weight:600;text-align:left;">Referencia</th>
                <th style="padding:.6rem .8rem;color:var(--theme-muted);font-weight:600;text-align:left;">Comprobante</th>
                <th style="padding:.6rem .8rem;color:var(--theme-muted);font-weight:600;text-align:left;">Fecha</th>
                @if($status === 'pending')
                <th style="padding:.6rem .8rem;color:var(--theme-muted);font-weight:600;text-align:center;">Acciones</th>
                @endif
                @if($status === 'rejected')
                <th style="padding:.6rem .8rem;color:var(--theme-muted);font-weight:600;text-align:left;">Motivo</th>
                @endif
            </tr>
        </thead>
        <tbody>
        @foreach($payments as $payment)
        <tr style="border-bottom:1px solid var(--theme-border);">

            {{-- Usuario --}}
            <td style="padding:.6rem .8rem;">
                <div style="font-weight:600;color:var(--theme-text);">
                    {{ $payment->nickname ?? $payment->display_name ?? $payment->username }}
                </div>
                <div style="font-size:.72rem;color:var(--theme-muted);">{{ $payment->email }}</div>
            </td>

            {{-- Solicitud --}}
            <td style="padding:.6rem .8rem;">
                <div style="display:flex;align-items:center;gap:.4rem;font-size:.8rem;">
                    <span style="color:var(--theme-muted);">{{ $payment->current_membership ?? '—' }}</span>
                    <i class="fas fa-arrow-right" style="color:var(--theme-muted);font-size:.65rem;"></i>
                    <span style="font-weight:700;color:var(--theme-accent);">{{ $payment->requested_membership }}</span>
                </div>
            </td>

            {{-- Monto --}}
            <td style="padding:.6rem .8rem;text-align:right;font-weight:700;color:var(--theme-text);">
                @if($payment->amount)
                    ${{ number_format($payment->amount, 2) }}
                    <span style="font-size:.7rem;color:var(--theme-muted);">{{ $payment->currency }}</span>
                @else
                    <span style="color:var(--theme-muted);">—</span>
                @endif
            </td>

            {{-- Método --}}
            <td style="padding:.6rem .8rem;color:var(--theme-muted);font-size:.8rem;">
                {{ $payment->payment_method ?? '—' }}
            </td>

            {{-- Referencia --}}
            <td style="padding:.6rem .8rem;color:var(--theme-muted);font-size:.78rem;max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                {{ $payment->payment_reference ?? '—' }}
            </td>

            {{-- Comprobante --}}
            <td style="padding:.6rem .8rem;text-align:center;">
                @if($payment->receipt_path)
                <a href="{{ Storage::url($payment->receipt_path) }}" target="_blank"
                   style="color:var(--theme-accent);font-size:.78rem;text-decoration:none;">
                    <i class="fas fa-file-alt"></i> Ver
                </a>
                @else
                <span style="color:var(--theme-muted);font-size:.75rem;">—</span>
                @endif
            </td>

            {{-- Fecha --}}
            <td style="padding:.6rem .8rem;color:var(--theme-muted);font-size:.75rem;">
                {{ \Carbon\Carbon::parse($payment->created_at)->format('d/m/Y H:i') }}
            </td>

            {{-- Acciones pendientes --}}
            @if($status === 'pending')
            <td style="padding:.6rem .8rem;text-align:center;">
                <div style="display:flex;gap:.35rem;justify-content:center;">
                    <form method="POST" action="{{ route('admin.memberships.approve', $payment->id) }}">
                        @csrf
                        <button type="submit"
                                onclick="return confirm('¿Aprobar y actualizar membresía de {{ addslashes($payment->username) }}?')"
                                style="padding:.3rem .7rem;background:#22c55e22;color:#22c55e;border:1px solid #22c55e;border-radius:6px;font-size:.75rem;cursor:pointer;font-weight:600;">
                            <i class="fas fa-check"></i> Aprobar
                        </button>
                    </form>
                    <button onclick="abrirRechazo({{ $payment->id }})"
                            style="padding:.3rem .7rem;background:#ef444422;color:#ef4444;border:1px solid #ef4444;border-radius:6px;font-size:.75rem;cursor:pointer;font-weight:600;">
                        <i class="fas fa-times"></i> Rechazar
                    </button>
                </div>
            </td>
            @endif

            {{-- Motivo rechazo --}}
            @if($status === 'rejected')
            <td style="padding:.6rem .8rem;color:#ef4444;font-size:.75rem;max-width:200px;">
                {{ $payment->admin_note ?? '—' }}
            </td>
            @endif

        </tr>
        @endforeach
        </tbody>
    </table>
</div>

@if($payments->hasPages())
<div style="margin-top:1rem;display:flex;justify-content:center;">
    {{ $payments->appends(request()->query())->links() }}
</div>
@endif
@endif

{{-- Modal rechazo --}}
<div id="modalRechazo" style="display:none;position:fixed;inset:0;background:#000a;z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--theme-card);border-radius:12px;padding:1.5rem;max-width:460px;width:90%;position:relative;">
        <h3 style="margin:0 0 1rem;font-size:1rem;color:var(--theme-text);">
            <i class="fas fa-times-circle" style="color:#ef4444;"></i> Rechazar pago
        </h3>
        <form method="POST" id="formRechazo" action="">
            @csrf
            <div style="margin-bottom:1rem;">
                <label style="display:block;font-size:.8rem;color:var(--theme-muted);margin-bottom:.4rem;">
                    Motivo del rechazo
                </label>
                <textarea name="reason" rows="3" placeholder="Explica el motivo del rechazo al usuario…"
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

{{-- Modal registrar pago manual --}}
<div id="modalRegistrar" style="display:none;position:fixed;inset:0;background:#000a;z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--theme-card);border-radius:12px;padding:1.5rem;max-width:540px;width:90%;max-height:85vh;overflow-y:auto;position:relative;">
        <button onclick="document.getElementById('modalRegistrar').style.display='none'"
                style="position:absolute;top:.75rem;right:.75rem;background:none;border:none;color:var(--theme-muted);font-size:1.2rem;cursor:pointer;">
            <i class="fas fa-times"></i>
        </button>
        <h3 style="margin:0 0 1.25rem;font-size:1rem;color:var(--theme-text);">
            <i class="fas fa-plus-circle" style="color:var(--theme-accent);"></i> Registrar pago manual
        </h3>
        <form method="POST" action="{{ route('admin.memberships.store') }}" enctype="multipart/form-data">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem;">

                <div style="grid-column:1/-1;">
                    <label style="display:block;font-size:.78rem;color:var(--theme-muted);margin-bottom:.3rem;">
                        ID o username del usuario <span style="color:#ef4444;">*</span>
                    </label>
                    <input type="text" name="user_id" required placeholder="UUID del usuario"
                           style="width:100%;padding:.45rem .75rem;border-radius:7px;border:1px solid var(--theme-border);background:var(--theme-bg);color:var(--theme-text);font-size:.85rem;">
                </div>

                <div>
                    <label style="display:block;font-size:.78rem;color:var(--theme-muted);margin-bottom:.3rem;">
                        Membresía solicitada <span style="color:#ef4444;">*</span>
                    </label>
                    <select name="requested_membership" required
                            style="width:100%;padding:.45rem .75rem;border-radius:7px;border:1px solid var(--theme-border);background:var(--theme-bg);color:var(--theme-text);font-size:.85rem;">
                        <option value="basic">Basic</option>
                        <option value="premium">Premium</option>
                        <option value="vip">VIP</option>
                    </select>
                </div>

                <div>
                    <label style="display:block;font-size:.78rem;color:var(--theme-muted);margin-bottom:.3rem;">Monto</label>
                    <input type="number" name="amount" step="0.01" min="0" placeholder="0.00"
                           style="width:100%;padding:.45rem .75rem;border-radius:7px;border:1px solid var(--theme-border);background:var(--theme-bg);color:var(--theme-text);font-size:.85rem;">
                </div>

                <div>
                    <label style="display:block;font-size:.78rem;color:var(--theme-muted);margin-bottom:.3rem;">Moneda</label>
                    <select name="currency"
                            style="width:100%;padding:.45rem .75rem;border-radius:7px;border:1px solid var(--theme-border);background:var(--theme-bg);color:var(--theme-text);font-size:.85rem;">
                        <option value="MXN">MXN</option>
                        <option value="USD">USD</option>
                        <option value="EUR">EUR</option>
                    </select>
                </div>

                <div>
                    <label style="display:block;font-size:.78rem;color:var(--theme-muted);margin-bottom:.3rem;">Método de pago</label>
                    <select name="payment_method"
                            style="width:100%;padding:.45rem .75rem;border-radius:7px;border:1px solid var(--theme-border);background:var(--theme-bg);color:var(--theme-text);font-size:.85rem;">
                        <option value="">— Seleccionar —</option>
                        <option value="transferencia">Transferencia bancaria</option>
                        <option value="oxxo">OXXO</option>
                        <option value="tarjeta">Tarjeta</option>
                        <option value="paypal">PayPal</option>
                        <option value="crypto">Criptomoneda</option>
                        <option value="efectivo">Efectivo</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>

                <div>
                    <label style="display:block;font-size:.78rem;color:var(--theme-muted);margin-bottom:.3rem;">Referencia / Folio</label>
                    <input type="text" name="payment_reference" placeholder="Número de referencia"
                           style="width:100%;padding:.45rem .75rem;border-radius:7px;border:1px solid var(--theme-border);background:var(--theme-bg);color:var(--theme-text);font-size:.85rem;">
                </div>

                <div style="grid-column:1/-1;">
                    <label style="display:block;font-size:.78rem;color:var(--theme-muted);margin-bottom:.3rem;">
                        Comprobante de pago <span style="font-weight:400;">— JPG, PNG o PDF, máx. 5MB</span>
                    </label>
                    <input type="file" name="receipt" accept="image/jpg,image/jpeg,image/png,application/pdf"
                           style="width:100%;padding:.45rem;border-radius:7px;border:1px solid var(--theme-border);background:var(--theme-bg);color:var(--theme-text);font-size:.82rem;">
                </div>

            </div>

            <div style="display:flex;gap:.75rem;justify-content:flex-end;margin-top:1.25rem;border-top:1px solid var(--theme-border);padding-top:1rem;">
                <button type="button" onclick="document.getElementById('modalRegistrar').style.display='none'"
                        style="padding:.45rem 1rem;border:1px solid var(--theme-border);color:var(--theme-muted);border-radius:8px;background:none;cursor:pointer;font-size:.85rem;">
                    Cancelar
                </button>
                <button type="submit"
                        style="padding:.45rem 1.2rem;background:var(--theme-accent);color:#fff;border:none;border-radius:8px;cursor:pointer;font-size:.85rem;font-weight:600;">
                    <i class="fas fa-save"></i> Registrar pago
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function abrirRechazo(id) {
    document.getElementById('formRechazo').action = '/admin/membresias/' + id + '/rechazar';
    document.getElementById('modalRechazo').style.display = 'flex';
}

document.getElementById('modalRechazo').addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
});
document.getElementById('modalRegistrar').addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
});
</script>
@endpush
