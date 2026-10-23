@php
    $acento = '#b43c78';
    $texto  = '#1f2430';
    $tenue  = '#6b7280';
    $borde  = '#e5e7eb';

    $tieneComprobante = ! empty($datos['receipt_path']);
    $clave            = $datos['bank_tracking_key'] ?? null;
    $referencia       = $datos['payment_reference'] ?? $payment->payment_reference;
    $vence            = $payment->expires_at
        ? \Carbon\Carbon::parse($payment->expires_at)->locale('es')->isoFormat('dddd D [de] MMMM, HH:mm')
        : null;
@endphp
<!DOCTYPE html><html lang="es"><head><meta charset="utf-8"><title>Comprobante por revisar</title></head>
<body style="margin:0;background:#f3f4f6;font-family:Arial,Helvetica,sans-serif;color:{{ $texto }};">
<div style="max-width:600px;margin:0 auto;padding:24px 12px;">

<div style="background:{{ $acento }};padding:20px 26px;border-radius:12px 12px 0 0;">
  <div style="color:#fff;font-size:18px;font-weight:bold;">CLUB LOBBY69</div>
  <div style="color:#f7d9e8;font-size:13px;margin-top:3px;">Comprobante pendiente de revisión</div>
</div>

<div style="background:#fff;padding:26px;border-radius:0 0 12px 12px;">
  <p style="margin:0 0 16px;font-size:15px;line-height:1.6;">
    <strong>{{ $datos['username'] ?? 'Un socio' }}</strong> subió su comprobante para el plan
    <strong>{{ $payment->requested_membership }}</strong>. La orden está en revisión.
  </p>

  <div style="background:#faf7fb;border:1px solid {{ $borde }};border-radius:10px;padding:16px 18px;text-align:center;">
    <div style="font-size:11px;letter-spacing:1px;text-transform:uppercase;color:{{ $tenue }};font-weight:bold;">Monto declarado</div>
    <div style="font-size:28px;font-weight:bold;color:{{ $acento }};margin-top:4px;">${{ number_format((float) $payment->amount, 2) }} <span style="font-size:13px;color:{{ $tenue }};font-weight:normal;">{{ $payment->currency }}</span></div>
  </div>

  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:20px;border:1px solid {{ $borde }};border-radius:8px;">
    <tr><td style="padding:10px 15px;border-bottom:1px solid {{ $borde }};">
      <div style="font-size:11px;text-transform:uppercase;color:{{ $tenue }};">Socio</div>
      <div style="font-size:15px;font-weight:bold;margin-top:2px;">{{ $datos['username'] ?? '—' }} <span style="font-weight:normal;color:{{ $tenue }};">{{ $datos['email'] ?? '' }}</span></div>
    </td></tr>
    <tr><td style="padding:10px 15px;border-bottom:1px solid {{ $borde }};">
      <div style="font-size:11px;text-transform:uppercase;color:{{ $tenue }};">Orden SPEI</div>
      <div style="font-size:15px;font-weight:bold;font-family:Consolas,Monaco,monospace;margin-top:2px;color:{{ $acento }};">{{ $payment->public_code ?? '—' }}</div>
    </td></tr>
    <tr><td style="padding:10px 15px;border-bottom:1px solid {{ $borde }};">
      <div style="font-size:11px;text-transform:uppercase;color:{{ $tenue }};">Método</div>
      <div style="font-size:15px;font-weight:bold;margin-top:2px;">{{ $payment->payment_method ?? '—' }}</div>
    </td></tr>
    <tr><td style="padding:10px 15px;border-bottom:1px solid {{ $borde }};">
      <div style="font-size:11px;text-transform:uppercase;color:{{ $tenue }};">Referencia del socio</div>
      <div style="font-size:15px;font-weight:bold;margin-top:2px;">{{ $referencia ?: '—' }}</div>
    </td></tr>
    <tr><td style="padding:10px 15px;{{ $vence ? 'border-bottom:1px solid '.$borde.';' : '' }}">
      <div style="font-size:11px;text-transform:uppercase;color:{{ $tenue }};">Clave de rastreo</div>
      <div style="font-size:15px;font-weight:bold;font-family:Consolas,Monaco,monospace;margin-top:2px;{{ $clave ? '' : 'color:#b45309;' }}">
        {{ $clave ?: 'el socio no la capturó' }}
      </div>
    </td></tr>
    @if($vence)
    <tr><td style="padding:10px 15px;">
      <div style="font-size:11px;text-transform:uppercase;color:{{ $tenue }};">Ventana de pago</div>
      <div style="font-size:15px;font-weight:bold;margin-top:2px;">{{ $vence }}</div>
    </td></tr>
    @endif
  </table>

  <p style="margin:18px 0 0;padding:12px 14px;background:{{ $tieneComprobante ? '#eff6ff' : '#fffbeb' }};border-left:3px solid {{ $tieneComprobante ? '#3b82f6' : '#f59e0b' }};font-size:13px;line-height:1.6;">
    @if($tieneComprobante)
      El socio adjuntó un comprobante. Ábrelo y confirma que el monto coincida
      <strong>exactamente</strong> con los ${{ number_format((float) $payment->amount, 2) }} — incluidos los centavos.
    @else
      El socio <strong>no adjuntó archivo</strong>: solo registró datos de su transferencia.
      Verifica el pago en el estado de cuenta antes de aprobar.
    @endif
  </p>

  <div style="text-align:center;margin-top:24px;">
    <a href="{{ url('/admin/membresias?status=pending') }}" style="display:inline-block;padding:14px 26px;background:{{ $acento }};color:#fff;font-size:14px;font-weight:bold;text-decoration:none;border-radius:8px;">Revisar en el panel</a>
  </div>

  <p style="margin:24px 0 0;font-size:12px;line-height:1.6;color:{{ $tenue }};border-top:1px solid {{ $borde }};padding-top:14px;">
    Aviso automático de Club Lobby69. Para desactivarlo, deja <code>SPEI_ADMIN_EMAIL</code> vacío en el <code>.env</code>.
  </p>
</div>
</div>
</body></html>
