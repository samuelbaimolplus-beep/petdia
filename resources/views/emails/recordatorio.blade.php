<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tarea Clínica - {{ $clinicaNombre }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f8fafc;
            color: #334155;
            margin: 0;
            padding: 24px;
        }
        .card {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .header {
            background: linear-gradient(135deg, #0d9488, #0f766e);
            color: #ffffff;
            padding: 28px 24px;
            text-align: center;
        }
        .header .badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            padding: 4px 10px;
            border-radius: 9999px;
            margin-bottom: 8px;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 700;
        }
        .header p {
            margin: 4px 0 0 0;
            font-size: 13px;
            opacity: 0.9;
        }
        .content {
            padding: 28px 24px;
            line-height: 1.6;
            font-size: 14px;
        }
        .greeting {
            font-size: 16px;
            color: #0f172a;
            margin-top: 0;
            margin-bottom: 12px;
        }
        .task-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
            margin: 18px 0;
        }
        .task-card table {
            width: 100%;
            border-collapse: collapse;
        }
        .task-card td {
            padding: 6px 4px;
            font-size: 13px;
            vertical-align: top;
        }
        .task-card td.label {
            color: #64748b;
            font-weight: 600;
            width: 38%;
        }
        .task-card td.value {
            color: #0f172a;
            font-weight: 600;
        }
        .notes-box {
            background-color: #f0fdfa;
            border-left: 4px solid #0d9488;
            padding: 12px 16px;
            border-radius: 6px;
            margin: 16px 0;
            font-size: 13px;
            color: #134e4a;
        }
        .btn-whatsapp {
            display: inline-block;
            background-color: #10b981;
            color: #ffffff !important;
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
            padding: 10px 18px;
            border-radius: 8px;
            margin-top: 8px;
        }
        .footer {
            background-color: #f8fafc;
            padding: 18px 24px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            border-top: 1px solid #f1f5f9;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <span class="badge">Aviso Interno de Trabajo</span>
            <h1>🐾 {{ $clinicaNombre }}</h1>
            <p>Alerta de Recordatorio / Tarea Clínica Programada</p>
        </div>
        <div class="content">
            <p class="greeting">Estimado/a <strong>{{ $nombreProfesional }}</strong>,</p>
            
            <p>Se te ha asignado o generado una tarea clínica preventiva en el sistema para dar seguimiento a un paciente:</p>

            <div class="task-card">
                <table>
                    <tr>
                        <td class="label">🐾 Paciente:</td>
                        <td class="value">{{ $nombreMascota }} <span style="font-weight: normal; color: #64748b;">({{ $especieRaza }})</span></td>
                    </tr>
                    <tr>
                        <td class="label">🩺 Tipo de Atención:</td>
                        <td class="value">
                            {{ $tipoRecordatorio }}
                            @if(!empty($servicioNombre))
                                <br><small style="color: #0d9488; font-weight: normal;">Servicio: {{ $servicioNombre }}</small>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="label">📅 Fecha y Hora:</td>
                        <td class="value">{{ $fechaCita }}</td>
                    </tr>
                    <tr>
                        <td class="label">👤 Propietario / Tutor:</td>
                        <td class="value">{{ $nombrePropietario }}</td>
                    </tr>
                    <tr>
                        <td class="label">📞 Teléfono del Tutor:</td>
                        <td class="value">
                            {{ $telefonoPropietario }}
                            @if(!empty($enlaceWhatsapp))
                                <div>
                                    <a href="{{ $enlaceWhatsapp }}" target="_blank" class="btn-whatsapp">
                                        💬 Escribir al Tutor por WhatsApp
                                    </a>
                                </div>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>

            @if(!empty($cuerpoMensaje))
                <div class="notes-box">
                    <strong>📝 Instrucciones / Notas Clínicas:</strong><br>
                    {{ $cuerpoMensaje }}
                </div>
            @endif

            <p style="font-size: 13px; color: #64748b; margin-top: 20px;">
                💡 <em>Por favor verifica el historial médico del paciente en el sistema y alista los biológicos o insumos requeridos con anticipación.</em>
            </p>
        </div>
        <div class="footer">
            <p style="margin: 0;">Sistema de Gestión Veterinaria — <strong>{{ $clinicaNombre }}</strong></p>
            <p style="margin: 4px 0 0 0;">Esta es una notificación interna automatizada para el personal clínico.</p>
        </div>
    </div>
</body>
</html>
