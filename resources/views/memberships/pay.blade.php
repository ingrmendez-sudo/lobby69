@extends('layouts.app')

@section('title', 'Transferencia SPEI')

@push('sidebar-left')
    @include('layouts.sidebar-left')
@endpush

@section('content')

@php
    $vencida = $payment->expires_at && $payment->expires_at->isPast();
@endphp

<div style="margin-bottom:1.25rem;font-size:.8rem;color:var(--theme-muted);">
    <a href="{{ route('membership.index') }}" style="color:var(--theme-accent);text-decoration:none;">
        <i class="fas fa-crown"></i> Membresías
    </a>
    <span style="margin:0 .4rem;">&rsaquo;</span>
    <span>Transferencia SPEI</span>
</div>

@if(session('success'))
<div style="background:#22c55e22;border:1px solid #22c55e;color:#22c55e;padding:.75rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.85rem;">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

@if(session('error'))
<div style="background:#ef444422;border:1px solid #ef4444;color:#ef4444;padding:.75rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.85rem;">
    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
</div>
@endif

@if($errors->any())
<div style="background:#ef444422;border:1px solid #ef4444;border-radius:8px;padding:.75rem 1rem;margin-bottom:1rem;font-size:.82rem;color:#ef4444;">
    @foreach($errors->all() as $error)
        <div><i class="fas fa-exclamation-circle"></i> {{ $error }}</div>
    @endforeach
</div>
@endif

<div style="max-width:640px;">

    {{-- ── Monto exacto + temporizador ─────────────────────────────── --}}
    <div style="background:var(--theme-surface-2);border:1px solid rgba(180,60,120,.25);border-radius:14px;padding:1.35rem;margin-bottom:1.1rem;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;flex-wrap:wrap;">
            <div>
                <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.08em;color:var(--theme-muted);font-weight:700;margin-bottom:.35rem;">
                    Monto exacto a transferir
                </div>
                <div style="font-size:2.1rem;font-weight:900;color:var(--theme-accent);line-height:1;">
                    ${{ number_format($spei['amount'], 2) }}
                    <span style="font-size:.9rem;font-weight:700;color:var(--theme-muted);">MXN</span>
                </div>
                @if($spei['cents'] > 0)
                <div style="font-size:.74rem;color:var(--theme-muted);margin-top:.45rem;">
                    Precio del plan ${{ number_format($spei['base_amount'], 2) }}
                    + <strong style="color:var(--theme-accent);">{{ $spei['cents'] }} centavos</strong>
                    para identificar tu transferencia.
                </div>
                @endif
            </div>

            <div style="text-align:right;min-width:150px;">
                <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.08em;color:var(--theme-muted);font-weight:700;margin-bottom:.35rem;">
                    Tiempo restante
                </div>
                @if($vencida)
                    <div style="font-size:1rem;font-weight:800;color:#ef4444;">
                        <i class="fas fa-times-circle"></i> Vencida
                    </div>
                @else
                    <div id="spei-countdown"
                         data-expires="{{ $payment->expires_at?->toIso8601String() }}"
                         style="font-size:1.15rem;font-weight:800;color:var(--theme-text);font-variant-numeric:tabular-nums;">
                        —
                    </div>
                    <div style="font-size:.7rem;color:var(--theme-muted);margin-top:.25rem;">
                        Ventana de {{ $spei['window_hours'] }} h
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if(! $spei['configured'])
    {{-- Sin CLABE en .env la pantalla NO inventa datos bancarios --}}
    <div style="background:#f59e0b18;border:1px solid #f59e0b;border-radius:12px;padding:1rem 1.15rem;margin-bottom:1.1rem;font-size:.85rem;">
        <div style="font-weight:800;color:#f59e0b;margin-bottom:.35rem;">
            <i class="fas fa-tools"></i> Falta configurar la cuenta receptora
        </div>
        <div style="color:var(--theme-text);">
            El administrador aún no cargó la CLABE. Agrega en el archivo <code>.env</code>:
            <div style="margin-top:.5rem;font-family:monospace;font-size:.78rem;background:rgba(0,0,0,.25);padding:.6rem .7rem;border-radius:6px;line-height:1.6;">
                SPEI_CLABE=012345678901234567<br>
                SPEI_BENEFICIARIO=Club Lobby69<br>
                SPEI_BANCO=BBVA
            </div>
            y luego ejecuta <code>php artisan config:clear</code>.
        </div>
    </div>
    @else

    {{-- ── Datos bancarios ─────────────────────────────────────────── --}}
    <div style="background:var(--theme-surface-2);border:1px solid rgba(180,60,120,.15);border-radius:14px;padding:1.35rem;margin-bottom:1.1rem;">
        <h2 style="font-size:.95rem;font-weight:800;color:var(--theme-text);margin:0 0 1rem;">
            <i class="fas fa-university" style="color:var(--theme-accent);"></i> Datos para tu transferencia
        </h2>

        @php
            $filas = [
                ['CLABE interbancaria', $spei['clabe'], true],
                ['Beneficiario', $spei['beneficiary'], false],
                ['Banco', $spei['bank'] ?: '—', false],
                ['Referencia', $spei['reference'], true],
                ['Concepto', $spei['concept'], true],
            ];
        @endphp

        @foreach($filas as [$etiqueta, $valor, $copiable])
        <div style="display:flex;align-items:center;justify-content:space-between;gap:.75rem;padding:.6rem 0;border-bottom:1px solid rgba(180,60,120,.1);">
            <div style="min-width:0;">
                <div style="font-size:.68rem;text-transform:uppercase;letter-spacing:.06em;color:var(--theme-muted);font-weight:700;">
                    {{ $etiqueta }}
                </div>
                <div style="font-size:.92rem;font-weight:700;color:var(--theme-text);word-break:break-all;">
                    {{ $valor }}
                </div>
            </div>
            @if($copiable && $valor !== '')
            <button type="button"
                    data-copy="{{ $valor }}"
                    class="spei-copy"
                    style="flex-shrink:0;padding:.35rem .7rem;border-radius:8px;cursor:pointer;
                           border:1px solid rgba(180,60,120,.35);background:rgba(180,60,120,.12);
                           color:var(--theme-accent);font-size:.72rem;font-weight:700;">
                <i class="fas fa-copy"></i> Copiar
            </button>
            @endif
        </div>
        @endforeach

        <div style="margin-top:1rem;font-size:.76rem;color:var(--theme-muted);line-height:1.6;">
            <i class="fas fa-info-circle" style="color:var(--theme-accent);"></i>
            Transfiere <strong style="color:var(--theme-text);">exactamente ${{ number_format($spei['amount'], 2) }}</strong>
            — no redondees los centavos. Son los que permiten identificar tu pago sin depender de que
            el banco te deje escribir la referencia. Desde tu banca en línea usa <em>SPEI</em> o
            <em>transferencia interbancaria</em>.
        </div>
    </div>

    @endif

    {{-- ── Ya transferí: subir comprobante ────────────────────────── --}}
    @if(! $vencida)
    <div style="background:var(--theme-surface-2);border:1px solid rgba(180,60,120,.15);border-radius:14px;padding:1.35rem;">
        <h2 style="font-size:.95rem;font-weight:800;color:var(--theme-text);margin:0 0 .35rem;">
            <i class="fas fa-receipt" style="color:var(--theme-accent);"></i> Ya transferí
        </h2>
        <p style="font-size:.78rem;color:var(--theme-muted);margin:0 0 1rem;">
            Sube tu comprobante para que revisemos el pago. Puedes hacerlo ahora o más tarde
            desde <a href="{{ route('membership.status') }}" style="color:var(--theme-accent);">Mis membresías</a>.
        </p>

        <form method="POST"
              action="{{ route('membership.proof', ['code' => $payment->public_code]) }}"
              enctype="multipart/form-data">
            @csrf

            <div style="margin-bottom:1rem;">
                <label style="display:block;font-size:.78rem;color:var(--theme-muted);margin-bottom:.4rem;font-weight:600;">
                    Comprobante — JPG, PNG o PDF · máx. 5 MB
                </label>
                <div style="border:2px dashed rgba(180,60,120,.3);border-radius:8px;padding:1rem;text-align:center;cursor:pointer;"
                     onclick="document.getElementById('spei-receipt').click()">
                    <i class="fas fa-cloud-upload-alt" style="font-size:1.5rem;color:rgba(180,60,120,.5);margin-bottom:.35rem;display:block;"></i>
                    <div style="font-size:.78rem;color:var(--theme-muted);" id="spei-receipt-label">
                        Haz clic para subir
                    </div>
                </div>
                <input type="file"
                       id="spei-receipt"
                       name="receipt"
                       accept="image/jpg,image/jpeg,image/png,application/pdf"
                       style="display:none;"
                       onchange="document.getElementById('spei-receipt-label').textContent = this.files[0]?.name || 'Haz clic para subir'">
            </div>

            <div style="margin-bottom:1rem;">
                <label style="display:block;font-size:.78rem;color:var(--theme-muted);margin-bottom:.4rem;font-weight:600;">
                    Clave de rastreo del banco <span style="font-weight:400;">(opcional, acelera la revisión)</span>
                </label>
                <input type="text"
                       name="bank_tracking_key"
                       value="{{ old('bank_tracking_key') }}"
                       placeholder="Ej: 2026102200123456789"
                       maxlength="64"
                       style="width:100%;padding:.55rem .85rem;border-radius:8px;border:1px solid var(--theme-border);background:var(--theme-bg);color:var(--theme-text);font-size:.88rem;">
            </div>

            <button type="submit"
                    style="width:100%;padding:.65rem 1.25rem;background:var(--theme-accent);color:#fff;border:none;border-radius:8px;cursor:pointer;font-size:.88rem;font-weight:700;">
                <i class="fas fa-paper-plane"></i> Enviar comprobante
            </button>
        </form>
    </div>
    @else
    <div style="background:#ef444418;border:1px solid #ef4444;border-radius:12px;padding:1.15rem;font-size:.85rem;color:var(--theme-text);">
        <strong style="color:#ef4444;"><i class="fas fa-clock"></i> La ventana de pago venció.</strong>
        <div style="margin-top:.4rem;">
            Esta referencia ya no sirve, pero no perdiste nada: crea una orden nueva y obtendrás
            otra referencia con su propio monto.
        </div>
        <a href="{{ route('membership.index') }}"
           style="display:inline-block;margin-top:.75rem;padding:.5rem 1.1rem;background:var(--theme-accent);color:#fff;border-radius:8px;text-decoration:none;font-size:.82rem;font-weight:700;">
            Elegir plan de nuevo
        </a>
    </div>
    @endif

</div>

@endsection

@push('scripts')
<script>
(function () {
    // Copiar al portapapeles
    document.querySelectorAll('.spei-copy').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var texto = btn.getAttribute('data-copy') || '';
            var original = btn.innerHTML;
            var listo = function () {
                btn.innerHTML = '<i class="fas fa-check"></i> Copiado';
                setTimeout(function () { btn.innerHTML = original; }, 1600);
            };
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(texto).then(listo).catch(function () {});
            } else {
                var tmp = document.createElement('textarea');
                tmp.value = texto;
                tmp.setAttribute('readonly', '');
                tmp.style.position = 'absolute';
                tmp.style.left = '-9999px';
                document.body.appendChild(tmp);
                tmp.select();
                try { document.execCommand('copy'); listo(); } catch (e) {}
                document.body.removeChild(tmp);
            }
        });
    });

    // Cuenta regresiva de la ventana de pago
    var reloj = document.getElementById('spei-countdown');
    if (!reloj) return;

    var fin = new Date(reloj.getAttribute('data-expires')).getTime();
    if (isNaN(fin)) { reloj.textContent = '—'; return; }

    function pinta() {
        var resta = fin - Date.now();
        if (resta <= 0) {
            reloj.textContent = 'Vencida';
            reloj.style.color = '#ef4444';
            return;
        }
        var h = Math.floor(resta / 3600000);
        var m = Math.floor((resta % 3600000) / 60000);
        var s = Math.floor((resta % 60000) / 1000);
        reloj.textContent = h + 'h ' + String(m).padStart(2, '0') + 'm ' + String(s).padStart(2, '0') + 's';
        setTimeout(pinta, 1000);
    }
    pinta();
})();
</script>
@endpush
