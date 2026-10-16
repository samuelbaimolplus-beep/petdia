<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante de Pago #{{ $pago->id_pago }} — La Petdiatría</title>
    <style>
        @page {
            margin: 5mm 5mm 5mm 5mm;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            color: #000;
            font-size: 10px;
            line-height: 1.25;
            margin: 0;
            padding: 0;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .divider {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }
        .title {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .subtitle {
            font-size: 9px;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 2px 0;
        }
        .item-table th {
            border-bottom: 1px dashed #000;
            font-size: 9px;
            text-align: left;
        }
        .item-table td {
            font-size: 9px;
        }
        .total-row {
            font-size: 11px;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="text-center">
        <div class="title">🐾 LA PETDIATRÍA</div>
        <div class="subtitle">Clínica Veterinaria & Pet Shop</div>
        <div>RUC: 80012345-6 · Tel: 021-600700</div>
        <div>Av. Santa Teresa c/ Denis Roa, Asunción</div>
    </div>

    <div class="divider"></div>

    <div class="text-center bold">
        TICKET DE CAJA / COMPROBANTE
    </div>
    <div class="text-center">
        NRO: #{{ str_pad($pago->id_pago, 6, '0', STR_PAD_LEFT) }}
    </div>

    <div class="divider"></div>

    <table>
        <tr>
            <td class="bold">Fecha/Hora:</td>
            <td class="text-right">{{ \Carbon\Carbon::parse($pago->fecha_hora ?? now())->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td class="bold">Cliente:</td>
            <td class="text-right">{{ $propietario ? trim($propietario->nombre . ' ' . $propietario->apellido1) : 'Cliente Ocasional' }}</td>
        </tr>
        <tr>
            <td class="bold">Documento / CI:</td>
            <td class="text-right">{{ $propietario?->documento ?? 'Sin RUC / CI' }}</td>
        </tr>
        <tr>
            <td class="bold">Estado:</td>
            <td class="text-right">{{ $pago->estado ?? 'CONFIRMADO' }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <div class="bold" style="margin-bottom: 4px;">DETALLE DE COBROS</div>
    <table class="item-table">
        <thead>
            <tr>
                <th>Concepto / Método</th>
                <th class="text-right">Monto (Gs.)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($detalles as $det)
                <tr>
                    <td>{{ $det->metodoPago?->nombre ?? 'Pago' }} {{ $det->referencia ? "({$det->referencia})" : '' }}</td>
                    <td class="text-right">{{ number_format($det->monto, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td>Cobro en Caja</td>
                    <td class="text-right">{{ number_format($pago->monto_total, 0, ',', '.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="divider"></div>

    <table>
        <tr class="total-row">
            <td>TOTAL PAGADO:</td>
            <td class="text-right">Gs. {{ number_format($pago->monto_total, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <div style="font-size: 8px;">
        <div><strong>Métodos de Pago:</strong> {{ $pago->metodos_texto ?: 'Efectivo' }}</div>
        @if($pago->observaciones)
            <div><strong>Obs:</strong> {{ $pago->observaciones }}</div>
        @endif
    </div>

    <div class="divider"></div>

    <div class="text-center" style="margin-top: 8px;">
        <div class="bold">¡GRACIAS POR VISITAR LA PETDIATRÍA!</div>
        <div style="font-size: 8px; margin-top: 3px;">Cuidando a los más consentidos del hogar.</div>
        <div style="font-size: 8px; color: #555; margin-top: 4px;">Comprobante de control interno sin validez de crédito fiscal.</div>
    </div>

</body>
</html>
