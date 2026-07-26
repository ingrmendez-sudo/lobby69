@extends('layouts.app')

@section('title', 'Solicitar ' . $plan->name)

@push('sidebar-left')
    @include('layouts.sidebar-left')
@endpush

@section('content')

{{-- Breadcrumb --}}
<div style="margin-bottom:1.25rem;font-size:.8rem;color:var(--theme-muted);">
    <a href="{{ route('membership.index') }}" style="color:var(--theme-accent);text-decoration:none;">
        <i class="fas fa-crown"></i> Membresías
    </a>
    <span style="margin:0 .4rem;">›</span>
    <span>Solicitar {{ $plan->name }}</span>
</div>

<div style="max-width:560px;">

    {{-- Resumen del plan --}}
    <div style="background:var(--theme-surface-2);border:1px solid rgba(180,60,120,.2);border-radius:14px;padding:1.25rem;margin-bottom:1.25rem;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.75rem;">
            <div>
                <div style="font-size:1.1rem;font-weight:800;color:var(--theme-text);">{{ $plan->name }}</div>
                @if($plan->description)
                <div style="font-size:.8rem;color:var(--theme-muted);margin-top:.2rem;">{{ $plan->description }}</div>
                @endif
            </div>
            <div style="text-align:right;">
                <div style="font-size:1.4rem;font-weight:800;color:var(--theme-accent);">
                    ${{ number_format($plan->active_price, 0) }} MXN
                </div>
                <div style="font-size:.72rem;color:var(--theme-muted);">
                    {{ $plan->slug === 'vitalicio' ? 'único pago' : 'por mes' }}
                </div>
            </div>
        </div>
        <div style="font-size:.75rem;color:var(--theme-muted);padding-top:.75rem;border-top:1px solid rgba(180,60,120,.12);">
            <i class="fas fa-info-circle" style="color:var(--theme-accent);"></i>
            Tu membresía se activa en menos de 24 horas tras confirmar tu pago.
        </div>
    </div>

    {{-- Formulario --}}
    <div style="background:var(--theme-surface-2);border:1px solid rgba(180,60,120,.15);border-radius:14px;padding:1.5rem;">
        <h2 style="font-size:1rem;font-weight:700;color:var(--theme-text);margin:0 0 1.25rem;">
            <i class="fas fa-paper-plane" style="color:var(--theme-accent);"></i> Enviar comprobante de pago
        </h2>

        @if($errors->any())
        <div style="background:#ef444422;border:1px solid #ef4444;border-radius:8px;padding:.75rem 1rem;margin-bottom:1rem;font-size:.82rem;color:#ef4444;">
            <i class="fas fa-exclamation-circle"></i>
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
        @endif

        <form method="POST"
              action="{{ route('membership.submit') }}"
              enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="plan_slug" value="{{ $plan->slug }}">

            {{-- Método de pago --}}
            <div style="margin-bottom:1rem;">
                <label style="display:block;font-size:.8rem;color:var(--theme-muted);margin-bottom:.4rem;font-weight:600;">
                    Método de pago <span style="color:#ef4444;">*</span>
                </label>
                <select name="payment_method" required
                        style="width:100%;padding:.55rem .85rem;border-radius:8px;border:1px solid var(--theme-border);background:var(--theme-bg);color:var(--theme-text);font-size:.88rem;">
                    <option value="">— Selecciona cómo pagaste —</option>
                    <option value="transferencia" {{ old('payment_method') === 'transferencia' ? 'selected' : '' }}>Transferencia bancaria / SPEI</option>
                    <option value="oxxo"          {{ old('payment_method') === 'oxxo'          ? 'selected' : '' }}>OXXO</option>
                    <option value="tarjeta"        {{ old('payment_method') === 'tarjeta'       ? 'selected' : '' }}>Tarjeta de crédito / débito</option>
                    <option value="paypal"         {{ old('payment_method') === 'paypal'        ? 'selected' : '' }}>PayPal</option>
                    <option value="crypto"         {{ old('payment_method') === 'crypto'        ? 'selected' : '' }}>Criptomoneda</option>
                    <option value="efectivo"       {{ old('payment_method') === 'efectivo'      ? 'selected' : '' }}>Efectivo (en mano)</option>
                    <option value="otro"           {{ old('payment_method') === 'otro'          ? 'selected' : '' }}>Otro</option>
                </select>
            </div>

            {{-- Referencia --}}
            <div style="margin-bottom:1rem;">
                <label style="display:block;font-size:.8rem;color:var(--theme-muted);margin-bottom:.4rem;font-weight:600;">
                    Número de referencia / folio
                </label>
                <input type="text"
                       name="payment_reference"
                       value="{{ old('payment_reference') }}"
                       placeholder="Ej: TX12345678 o número de confirmación"
                       style="width:100%;padding:.55rem .85rem;border-radius:8px;border:1px solid var(--theme-border);background:var(--theme-bg);color:var(--theme-text);font-size:.88rem;">
            </div>

            {{-- Comprobante --}}
            <div style="margin-bottom:1.5rem;">
                <label style="display:block;font-size:.8rem;color:var(--theme-muted);margin-bottom:.4rem;font-weight:600;">
                    Comprobante de pago
                    <span style="font-weight:400;"> — JPG, PNG o PDF · máx. 5 MB</span>
                </label>
                <div style="border:2px dashed rgba(180,60,120,.3);border-radius:8px;padding:1rem;text-align:center;cursor:pointer;"
                     onclick="document.getElementById('receipt-input').click()">
                    <i class="fas fa-cloud-upload-alt" style="font-size:1.5rem;color:rgba(180,60,120,.5);margin-bottom:.4rem;display:block;"></i>
                    <div style="font-size:.8rem;color:var(--theme-muted);" id="receipt-label">
                        Haz clic para subir o arrastra aquí
                    </div>
                </div>
                <input type="file"
                       id="receipt-input"
                       name="receipt"
                       accept="image/jpg,image/jpeg,image/png,application/pdf"
                       style="display:none;"
                       onchange="document.getElementById('receipt-label').textContent = this.files[0]?.name || 'Haz clic para subir'">
            </div>

            {{-- Botones --}}
            <div style="display:flex;gap:.75rem;">
                <a href="{{ route('membership.index') }}"
                   style="flex:1;text-align:center;padding:.6rem;border:1px solid var(--theme-border);color:var(--theme-muted);border-radius:8px;text-decoration:none;font-size:.85rem;">
                    Cancelar
                </a>
                <button type="submit"
                        style="flex:2;padding:.6rem 1.25rem;background:var(--theme-accent);color:#fff;border:none;border-radius:8px;cursor:pointer;font-size:.88rem;font-weight:700;">
                    <i class="fas fa-paper-plane"></i> Enviar solicitud
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
