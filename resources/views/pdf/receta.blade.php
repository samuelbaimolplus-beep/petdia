<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Receta Médica — La Petdiatría</title>
    <style>
        @page { margin: 25mm 20mm 25mm 20mm; }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 12px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .header {
            border-bottom: 2px solid #0284c7;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .clinic-name {
            font-size: 22px;
            font-weight: bold;
            color: #0369a1;
            margin: 0 0 4px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .clinic-subtitle {
            font-size: 11px;
            color: #64748b;
            margin: 0;
        }
        .clinic-info {
            font-size: 9px;
            color: #64748b;
            text-align: right;
            line-height: 1.3;
        }
        .table-header {
            width: 100%;
            margin-bottom: 20px;
        }
        .box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 20px;
        }
        .box-title {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            color: #0284c7;
            margin-bottom: 6px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 3px;
        }
        .grid-2 {
            width: 100%;
        }
        .grid-2 td {
            vertical-align: top;
            width: 50%;
            font-size: 11px;
            padding: 2px 0;
        }
        .label {
            color: #64748b;
            font-weight: normal;
        }
        .val {
            color: #0f172a;
            font-weight: bold;
        }
        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #0369a1;
            margin-top: 20px;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 4px;
        }
        .prescriptions-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .prescriptions-table th {
            background-color: #f1f5f9;
            color: #475569;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            text-align: left;
            padding: 8px 10px;
            border: 1px solid #cbd5e1;
        }
        .prescriptions-table td {
            padding: 8px 10px;
            font-size: 11px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }
        .prescriptions-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .instructions-box {
            background-color: #fffbeb;
            border-left: 4px solid #f59e0b;
            padding: 10px 14px;
            margin-top: 15px;
            border-radius: 4px;
        }
        .instructions-title {
            font-size: 11px;
            font-weight: bold;
            color: #b45309;
            margin-bottom: 4px;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 80px;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
            text-align: center;
        }
        .signature-table {
            width: 100%;
            margin-top: 40px;
        }
        .signature-line {
            width: 220px;
            border-top: 1px solid #0f172a;
            margin: 0 auto 5px auto;
            text-align: center;
        }
    </style>
</head>
<body>

    <table class="table-header">
        <tr>
            <td style="vertical-align: middle;">
                <h1 class="clinic-name">🐾 La Petdiatría</h1>
                <p class="clinic-subtitle">Centro Veterinario y Especialidades Pediátricas</p>
            </td>
            <td class="clinic-info">
                <strong>RUC:</strong> 80012345-6<br>
                <strong>Tel:</strong> +595 21 600 700 / 0981 123 456<br>
                <strong>Dir:</strong> Av. Santa Teresa c/ Denis Roa, Asunción<br>
                <strong>Email:</strong> contacto@lapetdiatria.com.py
            </td>
        </tr>
    </table>

    <div class="box">
        <div class="box-title">Datos del Paciente y Tutor</div>
        <table class="grid-2">
            <tr>
                <td><span class="label">Paciente:</span> <span class="val">{{ $paciente->nombre ?? 'S/D' }}</span></td>
                <td><span class="label">Tutor / Dueño:</span> <span class="val">{{ $propietario ? trim($propietario->nombre . ' ' . $propietario->apellido1) : 'S/D' }}</span></td>
            </tr>
            <tr>
                <td><span class="label">Especie / Raza:</span> <span class="val">{{ $paciente->raza?->especie?->nombre ?? 'Canino' }} · {{ $paciente->raza?->nombre ?? 'Mestizo' }}</span></td>
                <td><span class="label">C.I. Propietario:</span> <span class="val">{{ $propietario->documento ?? 'S/D' }}</span></td>
            </tr>
            <tr>
                <td><span class="label">Sexo / Edad:</span> <span class="val">{{ $paciente->sexo ?? 'S/D' }} · {{ $paciente->fecha_nacimiento ? \Carbon\Carbon::parse($paciente->fecha_nacimiento)->age . ' años' : 'S/D' }}</span></td>
                <td><span class="label">Fecha de Emisión:</span> <span class="val">{{ \Carbon\Carbon::parse($receta->fecha_hora ?? now())->format('d/m/Y · H:i') }}</span></td>
            </tr>
        </table>
    </div>

    <div class="section-title">Rp. / Prescripción Médica</div>

    <table class="prescriptions-table">
        <thead>
            <tr>
                <th style="width: 30%;">Medicamento</th>
                <th style="width: 15%;">Dosis</th>
                <th style="width: 15%;">Vía</th>
                <th style="width: 20%;">Frecuencia</th>
                <th style="width: 20%;">Duración</th>
            </tr>
        </thead>
        <tbody>
            @forelse($detalles as $det)
                <tr>
                    <td>
                        <strong>{{ $det->medicamento?->producto?->nombre ?? 'Medicamento Prescrito' }}</strong>
                        @if($det->indicaciones)
                            <br><small style="color: #64748b;">{{ $det->indicaciones }}</small>
                        @endif
                    </td>
                    <td>{{ $det->dosis }} {{ $det->unidad_dosis }}</td>
                    <td>{{ $det->via_administracion ?? 'Oral' }}</td>
                    <td>{{ $det->frecuencia ?? 'c/ 12 hs' }}</td>
                    <td>{{ $det->duracion ?? '7 días' }}</td>
                </tr>
            @empty
                @if(isset($atencion) && $atencion->atencionVeterinaria?->tratamientos?->isNotEmpty())
                    @foreach($atencion->atencionVeterinaria->tratamientos as $trat)
                        <tr>
                            <td><strong>{{ $trat->nombre }}</strong></td>
                            <td colspan="4">{{ $trat->indicaciones ?: 'Según pauta médica' }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="5" style="text-align: center; color: #94a3b8; padding: 15px;">
                            Prescripción clínica general indicada en observaciones adjuntas.
                        </td>
                    </tr>
                @endif
            @endforelse
        </tbody>
    </table>

    <div class="instructions-box">
        <div class="instructions-title">Indicaciones Generales y Cuidados</div>
        <p style="margin: 0; color: #334155;">
            {{ $receta->indicaciones_generales ?: 'Administrar la medicación en los horarios fijados. En caso de vómitos, reacciones alérgicas o falta de apetito, suspender y contactar de inmediato con la clínica.' }}
        </p>
    </div>

    <table class="signature-table">
        <tr>
            <td style="width: 50%;">
                <p style="font-size: 10px; color: #64748b; margin-top: 35px;">
                    Validez de la receta: 30 días a partir de su emisión.<br>
                    Documento emitido electrónicamente por el sistema La Petdiatría.
                </p>
            </td>
            <td style="width: 50%; text-align: center; vertical-align: bottom;">
                <div class="signature-line">
                    <strong style="font-size: 11px; color: #0f172a;">
                        Dr(a). {{ $profesional->persona?->nombre ?? $profesional->nombre_completo ?? 'Veterinario/a de Guardia' }} {{ $profesional->persona?->apellido1 ?? '' }}
                    </strong><br>
                    <span style="font-size: 10px; color: #64748b;">Médico Veterinario — Reg. Prof. #{{ $profesional->registro_profesional ?? '1042-PY' }}</span>
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
