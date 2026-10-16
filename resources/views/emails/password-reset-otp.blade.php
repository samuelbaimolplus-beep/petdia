<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperación de Contraseña — {{ $clinicaNombre }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f8fafc;
            color: #334155;
            margin: 0;
            padding: 24px;
        }
        .card {
            max-width: 540px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .header {
            background: linear-gradient(135deg, #4f46e5, #4338ca);
            color: #ffffff;
            padding: 32px 24px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        .header p {
            margin: 6px 0 0 0;
            font-size: 13px;
            opacity: 0.9;
        }
        .content {
            padding: 32px 28px;
            line-height: 1.6;
            font-size: 15px;
        }
        .otp-container {
            background: #f1f5f9;
            border: 2px dashed #cbd5e1;
            border-radius: 14px;
            padding: 20px;
            text-align: center;
            margin: 24px 0;
        }
        .otp-code {
            font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
            font-size: 34px;
            font-weight: 800;
            letter-spacing: 8px;
            color: #4338ca;
            display: inline-block;
        }
        .otp-note {
            margin-top: 8px;
            font-size: 12px;
            color: #64748b;
        }
        .alert-box {
            background-color: #fffbeb;
            border-left: 4px solid #f59e0b;
            padding: 12px 16px;
            border-radius: 6px;
            margin-top: 20px;
            font-size: 13px;
            color: #92400e;
        }
        .footer {
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 20px 24px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h1>🐾 {{ $clinicaNombre }}</h1>
            <p>Sistema Clínico Veterinario Integrado</p>
        </div>

        <div class="content">
            <p>Hola <strong>{{ $nombrePersona }}</strong> (usuario: <code>{{ $nombreUsuario }}</code>),</p>
            
            <p>Recibimos una solicitud para restablecer la contraseña de tu cuenta profesional en <strong>{{ $clinicaNombre }}</strong>.</p>
            
            <p>Usa el siguiente código de verificación de un solo uso (OTP) para completar el proceso:</p>

            <div class="otp-container">
                <div class="otp-code">{{ $otp }}</div>
                <div class="otp-note">Válido por los próximos <strong>{{ $minutosExpiracion }} minutos</strong></div>
            </div>

            <p style="font-size: 14px; color: #475569;">
                Ingresa este código en la pantalla de recuperación de contraseña de la plataforma para elegir una nueva contraseña segura.
            </p>

            <div class="alert-box">
                <strong>⚠️ Advertencia de seguridad:</strong> Si no solicitaste este cambio, puedes ignorar este correo de forma segura. Tu contraseña actual no será modificada sin este código.
            </div>
        </div>

        <div class="footer">
            <p>© {{ date('Y') }} {{ $clinicaNombre }}. Este es un mensaje automático de seguridad, por favor no respondas a este correo.</p>
        </div>
    </div>
</body>
</html>
