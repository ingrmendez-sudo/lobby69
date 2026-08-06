<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Bienvenido a LOBBY69</title>
</head>
<body style="margin:0;padding:0;background:#0d0d0d;font-family:'Helvetica Neue',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#0d0d0d;padding:40px 20px;">
  <tr>
    <td align="center">
      <table width="560" cellpadding="0" cellspacing="0"
             style="background:#1a1a2e;border-radius:16px;overflow:hidden;border:1px solid #2d2d4e;">

        {{-- Header --}}
        <tr>
          <td style="background:linear-gradient(135deg,#e91e8c,#9c27b0);padding:36px 40px;text-align:center;">
            <div style="font-size:28px;font-weight:900;color:#fff;letter-spacing:2px;">LOBBY69</div>
            <div style="font-size:13px;color:rgba(255,255,255,.8);margin-top:4px;letter-spacing:1px;">
              LA COMUNIDAD SWINGER MÁS DISCRETA DE MÉXICO
            </div>
          </td>
        </tr>

        {{-- Body --}}
        <tr>
          <td style="padding:36px 40px;">

            <p style="margin:0 0 20px;font-size:22px;font-weight:700;color:#fff;">
              ¡Bienvenido, {{ $nombre }}! 🎉
            </p>

            <p style="margin:0 0 24px;font-size:15px;color:#b0b0c8;line-height:1.7;">
              Tu solicitud de acceso a <strong style="color:#e91e8c;">LOBBY69</strong>
              ha sido <strong style="color:#22c55e;">aprobada</strong>.
              Ya puedes acceder a la plataforma con las siguientes credenciales temporales:
            </p>

            {{-- Credenciales --}}
            <table width="100%" cellpadding="0" cellspacing="0"
                   style="background:#0d0d1a;border:1px solid #2d2d4e;border-radius:12px;margin-bottom:28px;">
              <tr>
                <td style="padding:20px 24px;">
                  <div style="margin-bottom:14px;">
                    <div style="font-size:11px;color:#6b6b8a;text-transform:uppercase;letter-spacing:.08em;margin-bottom:4px;">
                      Usuario
                    </div>
                    <div style="font-size:18px;font-weight:700;color:#e91e8c;font-family:monospace;">
                      {{ $username }}
                    </div>
                  </div>
                  <div>
                    <div style="font-size:11px;color:#6b6b8a;text-transform:uppercase;letter-spacing:.08em;margin-bottom:4px;">
                      Contraseña temporal
                    </div>
                    <div style="font-size:18px;font-weight:700;color:#f59e0b;font-family:monospace;letter-spacing:2px;">
                      {{ $tempPassword }}
                    </div>
                  </div>
                </td>
              </tr>
            </table>

            <p style="margin:0 0 28px;font-size:14px;color:#b0b0c8;line-height:1.7;
                      background:#22c55e15;border:1px solid #22c55e33;border-radius:8px;padding:14px 18px;">
              ⚠️ <strong style="color:#f59e0b;">Importante:</strong>
              Al ingresar por primera vez se te pedirá completar tu perfil
              y cambiar tu contraseña por una personal y segura.
            </p>

            {{-- CTA --}}
            <table width="100%" cellpadding="0" cellspacing="0">
              <tr>
                <td align="center">
                  <a href="{{ $loginUrl }}"
                     style="display:inline-block;padding:14px 40px;
                            background:linear-gradient(135deg,#e91e8c,#9c27b0);
                            color:#fff;font-size:16px;font-weight:700;
                            border-radius:50px;text-decoration:none;
                            letter-spacing:.5px;">
                    Ingresar a LOBBY69 →
                  </a>
                </td>
              </tr>
            </table>

          </td>
        </tr>

        {{-- Footer --}}
        <tr>
          <td style="padding:20px 40px;border-top:1px solid #2d2d4e;text-align:center;">
            <p style="margin:0;font-size:12px;color:#4a4a6a;line-height:1.6;">
              Este correo es confidencial. No lo compartas con nadie.<br>
              © {{ date('Y') }} LOBBY69 — Todos los derechos reservados.
            </p>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>
</body>
</html>