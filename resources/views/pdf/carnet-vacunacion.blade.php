<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carnet de Vacunación — {{ $paciente->nombre }} — La Petdiatría</title>
    <style>
        @page {
            margin: 10mm 12mm 10mm 12mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 10px;
            margin: 0;
            padding: 0;
        }
        .header {
            width: 100%;
            border-bottom: 2px solid #0d9488;
            padding-bottom: 6px;
            margin-bottom: 10px;
        }
        .clinic-brand {
            font-size: 16px;
            font-weight: bold;
            color: #0f766e;
            text-transform: uppercase;
        }
        .doc-title {
            font-size: 12px;
            font-weight: bold;
            color: #115e59;
            text-align: right;
            text-transform: uppercase;
        }
        .patient-card {
            width: 100%;
            background-color: #f0fdfa;
            border: 1px solid #ccfbf1;
            border-radius: 6px;
            padding: 6px 10px;
            margin-bottom: 12px;
        }
        .patient-card td {
            vertical-align: top;
            font-size: 10px;
            padding: 2px 4px;
        }
        .label {
            color: #0d9488;
            font-weight: bold;
        }
        .val {
            color: #0f172a;
            font-weight: bold;
        }
        .grid-tables {
            width: 100%;
        }
        .grid-tables td {
            vertical-align: top;
            width: 50%;
            padding: 0 4px;
        }
        .table-title {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            color: #0f766e;
            border-bottom: 1.5px solid #0d9488;
            padding-bottom: 3px;
            margin-bottom: 6px;
        }
        .card-table {
            width: 100%;
            border-collapse: collapse;
        }
        .card-table th {
            background-color: #e6fffa;
            color: #115e59;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            border: 1px solid #99f6e4;
            padding: 4px 6px;
            text-align: left;
        }
        .card-table td {
            font-size: 9px;
            border: 1px solid #ccfbf1;
            padding: 4px 6px;
        }
        .card-table tr:nth-child(even) td {
            background-color: #fcfdfd;
        }
        .footer {
            margin-top: 10px;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
            font-size: 8px;
            color: #64748b;
            text-align: center;
        }
    </style>
</head>
<body>

    <table class="header">
        <tr>
            <td>
                <span class="clinic-brand">🐾 LA PETDIATRÍA</span>
                <span style="font-size: 9px; color: #64748b; margin-left: 8px;">Centro de Especialidades Pediátricas Veterinarias</span>
            </td>
            <td class="doc-title">
                CARNET OFICIAL DE SALUD & VACUNACIÓN
            </td>
        </tr>
    </table>

    <table class="patient-card">
        <tr>
            <td style="width: 25%;">
                <span class="label">Mascota:</span> <span class="val">{{ $paciente->nombre }}</span><br>
                <span class="label">Especie:</span> {{ $paciente->raza?->especie?->nombre ?? 'Canino' }}
            </td>
            <td style="width: 25%;">
                <span class="label">Raza:</span> {{ $paciente->raza?->nombre ?? 'Mestizo' }}<br>
                <span class="label">Sexo:</span> {{ $paciente->sexo ?? 'MACHO' }}
            </td>
            <td style="width: 25%;">
                <span class="label">Nacimiento:</span> {{ $paciente->fecha_nacimiento ? \Carbon\Carbon::parse($paciente->fecha_nacimiento)->format('d/m/Y') : 'S/D' }}<br>
                <span class="label">Microchip:</span> {{ $paciente->microchip ?: 'Sin microchip' }}
            </td>
            <td style="width: 25%;">
                <span class="label">Tutor:</span> <span class="val">{{ $propietario ? trim($propietario->nombre . ' ' . $propietario->apellido1) : 'S/D' }}</span><br>
                <span class="label">Teléfono:</span> {{ $propietario?->telefono ?: 'S/D' }}
            </td>
        </tr>
    </table>

    <table class="grid-tables">
        <tr>
            <!-- Columna Vacunas -->
            <td>
                <div class="table-title">💉 Vacunas Aplicadas</div>
                <table class="card-table">
                    <thead>
                        <tr>
                            <th style="width: 22%;">Fecha</th>
                            <th style="width: 38%;">Biológico / Vacuna</th>
                            <th style="width: 22%;">Próx. Dosis</th>
                            <th style="width: 18%;">Profesional</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vacunas as $vac)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($vac['fecha'])->format('d/m/Y') }}</td>
                                <td><strong>{{ $vac['vacuna'] }}</strong></td>
                                <td style="color: #0d9488; font-weight: bold;">
                                    {{ $vac['proxima'] ? \Carbon\Carbon::parse($vac['proxima'])->format('d/m/Y') : 'Anual' }}
                                </td>
                                <td>{{ $vac['profesional'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align: center; color: #94a3b8; padding: 10px;">
                                    No hay registros de vacunas aplicadas en el historial.
                                </td>
                            </tr>
                        @endforelse
                        {{-- Espacio para firmas futuras si hay pocos registros --}}
                        @for($i = count($vacunas); $i < 3; $i++)
                            <tr>
                                <td style="color: #cbd5e1;">___/___/______</td>
                                <td style="color: #cbd5e1;">Refuerzo programado</td>
                                <td style="color: #cbd5e1;">___/___/______</td>
                                <td style="color: #cbd5e1;">Sello / Firma</td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </td>

            <!-- Columna Desparasitaciones -->
            <td>
                <div class="table-title">🛡️ Control Antiparasitario</div>
                <table class="card-table">
                    <thead>
                        <tr>
                            <th style="width: 22%;">Fecha</th>
                            <th style="width: 38%;">Producto / Tipo</th>
                            <th style="width: 22%;">Próx. Dosis</th>
                            <th style="width: 18%;">Profesional</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($desparasitaciones as $desp)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($desp['fecha'])->format('d/m/Y') }}</td>
                                <td><strong>{{ $desp['producto'] }}</strong><br><small style="color: #64748b;">{{ $desp['tipo'] }}</small></td>
                                <td style="color: #b45309; font-weight: bold;">
                                    {{ $desp['proxima'] ? \Carbon\Carbon::parse($desp['proxima'])->format('d/m/Y') : 'Trimestral' }}
                                </td>
                                <td>{{ $desp['profesional'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align: center; color: #94a3b8; padding: 10px;">
                                    Sin desparasitaciones registradas.
                                </td>
                            </tr>
                        @endforelse
                        @for($i = count($desparasitaciones); $i < 3; $i++)
                            <tr>
                                <td style="color: #cbd5e1;">___/___/______</td>
                                <td style="color: #cbd5e1;">Control antiparasitario</td>
                                <td style="color: #cbd5e1;">___/___/______</td>
                                <td style="color: #cbd5e1;">Sello / Firma</td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    <div class="footer">
        La Petdiatría · Urgencias 24h: 0981 123 456 · Av. Santa Teresa c/ Denis Roa, Asunción · www.lapetdiatria.com.py<br>
        Este carnet es un resumen médico oficial generado desde el expediente clínico electrónico.
    </div>

</body>
</html>
