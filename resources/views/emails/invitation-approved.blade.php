<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
body{font-family:Arial,sans-serif;background:#FAF9F6;margin:0;padding:40px 20px;}
.wrap{max-width:560px;margin:0 auto;background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 10px 40px rgba(0,0,0,.1);}
.hdr{background:linear-gradient(135deg,#FF1493,#9B59B6);padding:40px;text-align:center;}
.hdr h1{color:#fff;margin:0;font-size:2rem;letter-spacing:2px;}
.hdr p{color:rgba(255,255,255,.85);margin:8px 0 0;}
.bod{padding:40px;}
.bod h2{color:#2C3E50;}
.bod p{color:#555;line-height:1.7;}
.creds{background:#FAF9F6;border-radius:12px;padding:24px;margin:24px 0;border-left:4px solid #FF6B6B;}
.creds p{margin:6px 0;font-size:.95rem;}
.creds code{background:#fff;padding:4px 10px;border-radius:6px;font-size:1.1rem;font-weight:700;color:#FF1493;border:1px solid #eee;}
.btn{display:block;width:fit-content;margin:24px auto;background:#FF6B6B;color:#fff;padding:14px 36px;border-radius:50px;text-decoration:none;font-weight:700;}
.warn{background:#FFF3CD;border-radius:10px;padding:16px;margin-top:20px;font-size:.85rem;color:#856404;}
.ftr{background:#2C3E50;padding:24px;text-align:center;color:rgba(255,255,255,.5);font-size:.8rem;}
</style>
</head>
<body>
<div class="wrap">
    <div class="hdr"><h1>LOBBY69</h1><p>Club Privado — Acceso Aprobado</p></div>
    <div class="bod">
        <h2>¡Bienvenido/a, {{ $nombre }}!</h2>
        <p>Tu solicitud de acceso a <strong>CLUB LOBBY69</strong> ha sido aprobada.</p>
        <div class="creds">
            <p><strong>Email:</strong> {{ $email }}</p>
            <p><strong>Contraseña temporal:</strong> <code>{{ $tempPassword }}</code></p>
        </div>
        <a href="{{ url('/login') }}" class="btn">Iniciar Sesión</a>
        <div class="warn">⚠️ Al iniciar sesión deberás completar tu perfil y cambiar tu contraseña. Esta contraseña expira en 48 horas.</div>
    </div>
    <div class="ftr">© 2026 CLUB LOBBY69 — Plataforma privada para adultos +18</div>
</div>
</body>
</html>