<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Consentimiento Informado Quirúrgico — La Petdiatría</title>
    <style>
        @page { margin: 20mm 18mm 20mm 18mm; }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #0f172a;
            font-size: 11px;
            line-height: 1.45;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #0284c7;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }
        .clinic-name {
            font-size: 20px;
            font-weight: bold;
            color: #0369a1;
            margin: 0 0 3px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .clinic-subtitle {
            font-size: 10px;
            color: #64748b;
            margin: 0;
        }
        .doc-title {
            text-align: center;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 6px;
            margin-bottom: 14px;
            border-radius: 4px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .info-table td {
            vertical-align: top;
            padding: 2px 4px;
        }
        .box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            padding: 8px 12px;
            margin-bottom: 12px;
        }
        .box-title {
            font-size: 9.5px;
            font-weight: bold;
            text-transform: uppercase;
            color: #0284c7;
            margin-bottom: 6px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 2px;
        }
        .label {
            font-weight: bold;
            color: #475569;
            font-size: 10px;
        }
        .val {
            color: #0f172a;
            font-size: 10.5px;
        }
        .clauses {
            font-size: 9.5px;
            color: #334155;
            text-align: justify;
            line-height: 1.4;
            margin-bottom: 16px;
        }
        .clauses ol {
            margin: 4px 0 0 16px;
            padding: 0;
        }
        .clauses li {
            margin-bottom: 4px;
        }
        .signatures {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
        }
        .signatures td {
            width: 50%;
            text-align: center;
            vertical-align: bottom;
            padding: 0 15px;
        }
        .sig-line {
            border-top: 1px solid #64748b;
            padding-top: 6px;
            margin-top: 40px;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-asa {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td style="width: 65%;">
                <div class="clinic-name">La Petdiatría</div>
                <div class="clinic-subtitle">Clínica Veterinaria Especializada y Centro Quirúrgico</div>
                <div style="font-size: 9px; color: #64748b; margin-top: 2px;">
                    Cirugía General · Traumatología · Anestesia Inhalatoria · Cuidados Intensivos
                </div>
            </td>
            <td style="width: 35%; text-align: right; font-size: 9px; color: #64748b; line-height: 1.3;">
                <strong>Fecha de Emisión:</strong> {{ date('d/m/Y H:i') }}<br>
                <strong>Expediente Quirúrgico:</strong> #CQ-{{ str_pad($cirugia->id_procedimiento, 5, '0', STR_PAD_LEFT) }}<br>
                <strong>Teléfono Urgencias:</strong> +591 (4) 445-5667
            </td>
        </tr>
    </table>

    <div class="doc-title">
        Consentimiento Informado para Procedimiento Quirúrgico y Manejo Anestésico
    </div>

    <!-- DATOS DEL PACIENTE Y PROPIETARIO -->
    <table style="width: 100%; margin-bottom: 10px;">
        <tr>
            <td style="width: 50%; padding-right: 6px; vertical-align: top;">
                <div class="box">
                    <div class="box-title">Datos de la Mascota (Paciente)</div>
                    <table class="info-table">
                        <tr>
                            <td class="label" style="width: 35%;">Nombre:</td>
                            <td class="val"><strong>{{ $paciente->nombre }}</strong></td>
                        </tr>
                        <tr>
                            <td class="label">Especie / Raza:</td>
                            <td class="val">{{ $paciente->raza?->especie?->nombre ?? 'Canino' }} · {{ $paciente->raza?->nombre ?? 'Mestizo' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Sexo / Edad:</td>
                            <td class="val">
                                {{ $paciente->sexo }} · 
                                {{ $paciente->fecha_nacimiento ? \Carbon\Carbon::parse($paciente->fecha_nacimiento)->age . ' años' : 'S/D' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="label">Microchip / ID:</td>
                            <td class="val">{{ $paciente->microchip ?: 'No registrado' }}</td>
                        </tr>
                    </table>
                </div>
            </td>
            <td style="width: 50%; padding-left: 6px; vertical-align: top;">
                <div class="box">
                    <div class="box-title">Datos del Tutor / Propietario Responsable</div>
                    <table class="info-table">
                        <tr>
                            <td class="label" style="width: 35%;">Nombre Tutor:</td>
                            <td class="val"><strong>{{ $propietario ? trim("{$propietario->nombre} {$propietario->apellido1} {$propietario->apellido2}") : 'Propietario no registrado' }}</strong></td>
                        </tr>
                        <tr>
                            <td class="label">Documento / CI:</td>
                            <td class="val">{{ $propietario?->documento ?: 'S/D' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Teléfono / Celular:</td>
                            <td class="val">{{ $propietario?->telefono ?: 'S/D' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Dirección:</td>
                            <td class="val">{{ $propietario?->direccion ?: 'Domicilio registrado' }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <!-- DETALLES DE LA INTERVENCIÓN QUIRÚRGICA -->
    <div class="box">
        <div class="box-title">Plan Quirúrgico y Valoración Pre-Anestésica</div>
        <table class="info-table">
            <tr>
                <td class="label" style="width: 25%;">Cirugía Programada:</td>
                <td class="val" style="width: 75%;">
                    <strong style="color: #0369a1; font-size: 11px;">{{ $cirugia->tipo_cirugia }}</strong>
                </td>
            </tr>
            <tr>
                <td class="label">Diagnóstico Pre-quirúrgico:</td>
                <td class="val">{{ $cirugia->diagnostico_preoperatorio ?: 'Intervención quirúrgica electiva / terapéutica.' }}</td>
            </tr>
            <tr>
                <td class="label">Equipo Quirúrgico:</td>
                <td class="val">
                    <strong>Cirujano:</strong> {{ $cirujano?->persona ? trim("{$cirujano->persona->nombre} {$cirujano->persona->apellido1}") : 'Médico Veterinario Especialista' }}
                    @if ($anestesista?->persona)
                        &nbsp;·&nbsp; <strong>Anestesista:</strong> {{ trim("{$anestesista->persona->nombre} {$anestesista->persona->apellido1}") }}
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label">Riesgo ASA:</td>
                <td class="val">
                    <span class="badge badge-asa">{{ $cirugia->riesgo_asa ?: 'ASA II' }}</span>
                    <span style="font-size: 9.5px; color: #64748b; margin-left: 6px;">
                        @switch($cirugia->riesgo_asa)
                            @case('ASA I') Paciente sano sin enfermedad sistémica subyacente. @break
                            @case('ASA II') Enfermedad sistémica leve o localizada. @break
                            @case('ASA III') Enfermedad sistémica grave no incapacitante. @break
                            @case('ASA IV') Enfermedad sistémica grave con amenaza vital constante. @break
                            @case('ASA V') Paciente moribundo con escasa expectativa de supervivencia sin cirugía. @break
                            @default Valoración preanestésica estándar.
                        @endswitch
                    </span>
                </td>
            </tr>
            <tr>
                <td class="label">Técnica Anestésica:</td>
                <td class="val">{{ $cirugia->tipo_anestesia ?: 'Anestesia General Inhalatoria con Intubación Endotraqueal y Monitorización Multiparamétrica' }}</td>
            </tr>
            <tr>
                <td class="label">Ayuno Confirmado:</td>
                <td class="val">
                    Sólidos: <strong>{{ $cirugia->ayuno_solidos_horas ?? 8 }} horas</strong> &nbsp;|&nbsp; 
                    Líquidos: <strong>{{ $cirugia->ayuno_liquidos_horas ?? 2 }} horas</strong>
                </td>
            </tr>
            @if ($cirugia->observaciones_prequirurgicas)
                <tr>
                    <td class="label">Observaciones:</td>
                    <td class="val" style="font-style: italic; color: #475569;">{{ $cirugia->observaciones_prequirurgicas }}</td>
                </tr>
            @endif
        </table>
    </div>

    <!-- CLÁUSULAS MÉDICO-LEGALES -->
    <div class="clauses">
        <strong>DECLARACIONES Y AUTORIZACIÓN EXPRESA:</strong>
        <ol>
            <li><strong>Comprensión del Acto Médico:</strong> He sido informado detalladamente por el equipo veterinario sobre la naturaleza de la intervención quirúrgica, sus objetivos terapéuticos, alternativas existentes y el pronóstico médico esperado.</li>
            <li><strong>Riesgos Anestésicos y Quirúrgicos:</strong> Comprendo que todo procedimiento quirúrgico y la administración de fármacos anestésicos conllevan riesgos inherentes imprevisibles (tales como reacciones anafilácticas adversas, paro cardiorrespiratorio, hemorragias o complicaciones postquirúrgicas), aún mediando la máxima diligencia y protocolos de bioseguridad.</li>
            <li><strong>Autorización de Emergencia y RCP:</strong> En caso de presentarse una complicación imprevista durante el acto anestésico-quirúrgico, autorizo expresamente al equipo médico a implementar todas las medidas terapéuticas, maniobras de resucitación cardiopulmonar (RCP) y medicamentos de urgencia que consideren necesarios para preservar la vida del paciente.</li>
            <li><strong>Cumplimiento de Indicaciones:</strong> Declaro bajo juramento que el paciente ha cumplido rigurosamente el ayuno prequirúrgico indicado y no ha ingerido sustancias ni medicamentos no declarados.</li>
            <li><strong>Cuidados Posteriores:</strong> Me comprometo a cumplir estrictamente el plan terapéutico postoperatorio, curaciones, uso de collar isabelino y acudir a los controles de retiro de puntos programados.</li>
        </ol>
    </div>

    <!-- SECCIÓN DE FIRMAS -->
    <table class="signatures">
        <tr>
            <td>
                <div class="sig-line">
                    <strong>{{ $propietario ? trim("{$propietario->nombre} {$propietario->apellido1} {$propietario->apellido2}") : 'Firma del Propietario / Tutor Legal' }}</strong><br>
                    <span style="font-size: 9.5px; color: #475569;">
                        CI / Documento: {{ $propietario?->documento ?: '..........................................' }}<br>
                        Tutor Legal del Paciente
                    </span>
                </div>
            </td>
            <td>
                <div class="sig-line">
                    <strong>{{ $cirujano?->persona ? trim("{$cirujano->persona->nombre} {$cirujano->persona->apellido1}") : 'Médico Veterinario Cirujano' }}</strong><br>
                    <span style="font-size: 9.5px; color: #475569;">
                        Matrícula Profesional / Registro Médico<br>
                        Centro Quirúrgico Veterinario
                    </span>
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
