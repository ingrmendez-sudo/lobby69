@extends('layouts.minimal')

@section('title', 'Solicitud Enviada')

@section('content')
<section style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem 1rem;background:linear-gradient(135deg,#f5f7fa,#c3cfe2);">
    <div style="background:#fff;border-radius:20px;padding:3rem 2.5rem;max-width:520px;width:100%;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,.12);">

        <div style="width:72px;height:72px;background:linear-gradient(135deg,#f97068,#e84393);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;font-size:2rem;color:#fff;">
            ✓
        </div>

        <h1 style="font-size:1.75rem;font-weight:700;color:#1a1a2e;margin-bottom:.6rem;">¡Solicitud Enviada!</h1>

        @if(session('ref_code'))
            <div style="background:#f0fdf4;border:2px solid #86efac;border-radius:10px;padding:.75rem 1rem;margin-bottom:1.2rem;font-size:.875rem;color:#166534;font-weight:600;">
                🎟️ Código de referido aplicado: <strong style="font-family:monospace;">{{ session('ref_code') }}</strong>
            </div>
        @endif

        <p style="color:#555;font-size:.95rem;line-height:1.75;margin-bottom:1.75rem;">
            Hemos recibido tu solicitud correctamente. Nuestro equipo la revisará
            y recibirás una respuesta en tu correo en un plazo de
            <strong>24 a 72 horas</strong>.
        </p>

        <div style="background:#f8f9ff;border-radius:12px;padding:1.25rem 1.5rem;margin-bottom:2rem;text-align:left;">
            <p style="font-weight:700;color:#333;margin-bottom:.85rem;font-size:.875rem;">📋 ¿Qué sigue?</p>

            <div style="display:flex;gap:.75rem;margin-bottom:.6rem;align-items:flex-start;">
                <span style="min-width:22px;height:22px;background:#f97068;color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:700;">1</span>
                <p style="margin:0;color:#555;font-size:.85rem;">El equipo revisará tu perfil y el motivo de tu solicitud.</p>
            </div>
            <div style="display:flex;gap:.75rem;margin-bottom:.6rem;align-items:flex-start;">
                <span style="min-width:22px;height:22px;background:#f97068;color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:700;">2</span>
                <p style="margin:0;color:#555;font-size:.85rem;">Recibirás un correo de aprobación o de solicitud de información adicional.</p>
            </div>
            <div style="display:flex;gap:.75rem;align-items:flex-start;">
                <span style="min-width:22px;height:22px;background:#f97068;color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:700;">3</span>
                <p style="margin:0;color:#555;font-size:.85rem;">Al ser aprobado, recibirás tus credenciales de acceso.</p>
            </div>
        </div>

        <a href="{{ url('/') }}"
           style="display:inline-block;background:linear-gradient(135deg,#f97068,#e84393);color:#fff;padding:.85rem 2.5rem;border-radius:50px;text-decoration:none;font-weight:600;font-size:.95rem;"
           onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
            Volver al inicio
        </a>
    </div>
</section>
@endsection
