@props([
    'value' => null,
    'tone' => null,
])

@php
    // Mapa central de estados -> tone (mismos colores en toda la app)
    $map = [
        // Estados generales
        'ACTIVO' => 'success',
        'INACTIVO' => 'neutral',
        'CANCELADO' => 'danger',
        'ANULADO' => 'danger',
        'BLOQUEADO' => 'danger',
        'SUSPENDIDO' => 'danger',
        'PENDIENTE' => 'warning',
        'EN_PROCESO' => 'primary',
        'EN_ESPERA' => 'warning',
        'COMPLETADO' => 'success',
        'COMPLETADA' => 'success',
        'CONFIRMADA' => 'success',
        'RECIBIDA' => 'success',
        'PAGADO' => 'success',
        // Pagos
        'EFECTIVO' => 'success',
        'TARJETA_CREDITO' => 'primary',
        'TARJETA_DEBITO' => 'violet',
        'QR' => 'primary',
        // Movimientos / sexo / condicion
        'ENTRADA' => 'success',
        'SALIDA' => 'warning',
        'AJUSTE' => 'violet',
        'MACHO' => 'primary',
        'HEMBRA' => 'violet',
        'CASTRADO' => 'neutral',
        'INTACTO' => 'warning',
    ];
    $resolved = $tone ?? ($map[strtoupper((string) $value)] ?? 'neutral');
@endphp

<x-badge :tone="$resolved" {{ $attributes }}>
    {{ $value ?? $slot }}
</x-badge>