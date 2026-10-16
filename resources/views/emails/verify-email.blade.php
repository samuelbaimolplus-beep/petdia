<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Correo — {{ $clinicaNombre }}</title>
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
            background: linear-gradient(135deg, #0284c7, #0369a1);
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
        .btn-container {
            text-align: center;
            margin: 28px 0;
        }
        .btn {
            background-color: #0284c7;
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 15px;
            display: inline-block;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.3);
        }
        .warning-box {
            background-color: #eff6ff;
            border-left: 4px solid #0284c7;
            padding: 12px 16px;
            border-radius: 0 8px 8px 0;
            font-size: 13px;
            color: #1e40af;
            margin-top: 20px;
        }
        .link-text {
            word-break: break-all;
            color: #0284c7;
            font-size: 12px;
        }
        .footer {
            border-top: 1px solid #f1f5f9;
            padding: 20px 28px;
            background-color: #f8fafc;
            font-size: 12px;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h1>{{ $clinicaNombre }}</h1>
            <p>Activación de Cuenta de Usuario</p>
        </div>

        <div class="content">
            <p>Hola, <strong>{{ $nombrePersona }}</strong> (@<strong>{{ $nombreUsuario }}</strong>):</p>
            <p>Se ha creado una cuenta para ti en el sistema de la clínica. Para activar tu acceso y comenzar a operar de forma segura, utiliza el siguiente código de activación (OTP):</p>

            <div style="text-align: center; margin: 32px 0;">
                <div style="display: inline-block; background-color: #f1f5f9; border: 2px dashed #0284c7; border-radius: 12px; padding: 18px 36px;">
                    <span style="font-family: 'Courier New', Courier, monospace; font-size: 36px; font-weight: 800; letter-spacing: 8px; color: #0284c7;">{{ $otpCode }}</span>
                </div>
                <p style="font-size: 13px; color: #64748b; margin-top: 10px;">Código de Verificación de 6 dígitos</p>
            </div>

            <div class="warning-box">
                <strong>¿Cómo activar tu cuenta?</strong><br>
                1. Ve al sistema e intenta iniciar sesión con tu usuario <strong>{{ $nombreUsuario }}</strong>.<br>
                2. El sistema detectará que tu cuenta está pendiente y te solicitará este código OTP.<br>
                3. Ingresa tu usuario y este código de 6 dígitos para validar tu cuenta y configurar tu contraseña personal.
            </div>

            <p style="font-size: 13px; color: #64748b; margin-top: 24px;">
                Este código expira en <strong>{{ $horasExpiracion }} horas</strong> y solo puede ser utilizado una vez.
            </p>
            <p style="font-size: 13px; color: #94a3b8; margin-top: 16px;">
                Si tú no perteneces al personal de la clínica o no solicitaste este acceso, puedes ignorar este correo.
            </p>
        </div>

        <div class="footer">
            <p>© 2026 {{ $clinicaNombre }}. Todos los derechos reservados.</p>
            <p>Este es un correo automático generado por el sistema de seguridad. Por favor no respondas a esta dirección.</p>
        </div>
    </div>
</body>
</html>
