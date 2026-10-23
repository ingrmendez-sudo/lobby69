@php
    $acento = '#b43c78'; $texto = '#1f2430'; $tenue = '#6b7280'; $borde = '#e5e7eb';
    $vence  = ! empty($spei['expires_at'])
        ? \Carbon\Carbon::parse($spei['expires_at'])->locale('es')->isoFormat('dddd D [de] MMMM, HH:mm')
        : null;
@endphp
<!DOCTYPE html><html lang="es"><head><meta charset="utf-8"><title>Instrucciones de transferencia</title></head>
<body style="margin:0;background:#f3f4f6;font-family:Arial,Helvetica,sans-serif;color:{{ $texto }};">
<div style="max-width:600px;margin:0 auto;padding:24px 12px;">

<div style="background:{{ $acento }};padding:20px 26px;border-radius:12px 12px 0 0;">
  <div style="color:#fff;font-size:18px;font-weight:bold;">CLUB LOBBY69</div>
  <div style="color:#f7d9e8;font-size:13px;margin-top:3px;">Instrucciones para tu transferencia</div>
</div>

<div style="background:#fff;padding:26px;border-radius:0 0 12px 12px;">
  <p style="margin:0 0 14px;font-size:15px;">Hola{{ $userName ? ' ' . $userName : '' }},</p>
  <p style="margin:0 0 20px;font-size:14px;line-height:1.6;">
    Registramos tu solicitud de la membresía
    <strong>{{ $planName ?? $payment->requested_membership }}</strong>.
    Transfiere con estos datos exactos para activarla.
  </p>

  <div style="background:#faf7fb;border:1px solid {{ $borde }};border-radius:10px;padding:18px;text-align:center;">
    <div style="font-size:11px;letter-spacing:1px;text-transform:uppercase;color:{{ $tenue }};font-weight:bold;">Monto exacto a transferir</div>
    <div style="font-size:32px;font-weight:bold;color:{{ $acento }};margin-top:6px;">${{ number_format((float) $spei['amount'], 2) }} <span style="font-size:14px;color:{{ $tenue }};font-weight:normal;">MXN</span></div>
    @if(! empty($spei['cents']))
    <div style="font-size:12px;color:{{ $tenue }};margin-top:6px;">Precio del plan ${{ number_format((float) $spei['base_amount'], 2) }} + {{ (int) $spei['cents'] }} centavos que identifican tu pago.</div>
    @endif
  </div>

  @if(! empty($spei['cents']))
  <p style="margin:16px 0 0;padding:12px 14px;background:#fffbeb;border-left:3px solid #f59e0b;font-size:13px;line-height:1.6;color:#78350f;">
    <strong>No redondees el monto.</strong> Esos {{ (int) $spei['cents'] }} centavos son los que permiten
    identificar tu transferencia; con otra cifra la conciliación se vuelve manual y tarda más.
  </p>
  @endif

  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:22px;border:1px solid {{ $borde }};border-radius:8px;">
    <tr><td style="padding:11px 15px;border-bottom:1px solid {{ $borde }};">
      <div style="font-size:11px;text-transform:uppercase;color:{{ $tenue }};">CLABE interbancaria</div>
      <div style="font-size:16px;font-weight:bold;font-family:Consolas,Monaco,monospace;margin-top:3px;">{{ $spei['clabe'] }}</div>
    </td></tr>
    <tr><td style="padding:11px 15px;border-bottom:1px solid {{ $borde }};">
      <div style="font-size:11px;text-transform:uppercase;color:{{ $tenue }};">Beneficiario</div>
      <div style="font-size:15px;font-weight:bold;margin-top:3px;">{{ $spei['beneficiary'] }}</div>
    </td></tr>
    @if(! empty($spei['bank']))
    <tr><td style="padding:11px 15px;border-bottom:1px solid {{ $borde }};">
      <div style="font-size:11px;text-transform:uppercase;color:{{ $tenue }};">Banco</div>
      <div style="font-size:15px;font-weight:bold;margin-top:3px;">{{ $spei['bank'] }}</div>
    </td></tr>
    @endif
    <tr><td style="padding:11px 15px;border-bottom:1px solid {{ $borde }};">
      <div style="font-size:11px;text-transform:uppercase;color:{{ $tenue }};">Referencia</div>
      <div style="font-size:16px;font-weight:bold;color:{{ $acento }};font-family:Consolas,Monaco,monospace;margin-top:3px;">{{ $spei['reference'] }}</div>
    </td></tr>
    <tr><td style="padding:11px 15px;">
      <div style="font-size:11px;text-transform:uppercase;color:{{ $tenue }};">Concepto</div>
      <div style="font-size:15px;font-weight:bold;font-family:Consolas,Monaco,monospace;margin-top:3px;">{{ $spei['concept'] }}</div>
    </td></tr>
  </table>

  @if($vence)
  <p style="margin:16px 0 0;font-size:13px;">Válida hasta el <strong>{{ $vence }}</strong> ({{ (int) ($spei['window_hours'] ?? 24) }} horas).</p>
  @endif

  <div style="text-align:center;margin-top:24px;">
    <a href="{{ route('membership.pay', ['code' => $spei['reference']]) }}" style="display:inline-block;padding:14px 26px;background:{{ $acento }};color:#fff;font-size:14px;font-weight:bold;text-decoration:none;border-radius:8px;">Ver instrucciones y subir comprobante</a>
  </div>

  <p style="margin:24px 0 0;font-size:12px;line-height:1.6;color:{{ $tenue }};border-top:1px solid {{ $borde }};padding-top:14px;">
    Si ya no deseas este plan, ignora este correo: la orden vence sola y no se genera ningún cargo.
    Estado en <a href="{{ route('membership.status') }}" style="color:{{ $acento }};">Mis membresías</a>.<br><br>
    Club Lobby69 &middot; correo automático, no respondas a este mensaje.
  </p>
</div>
</div>
</body></html>
