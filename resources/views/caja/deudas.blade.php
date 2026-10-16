@extends('layouts.app')

@section('title', 'Cuentas por Cobrar — La Petdiatría')

@section('content')
    <div class="space-y-6">

        <!-- Encabezado con breadcrumb -->
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2 text-xs text-slate-400">
                    <a href="{{ route('caja.index') }}" class="hover:text-primary-600 transition-colors">Caja y Cobros</a>
                    <span>/</span>
                    <span class="text-slate-600 font-medium">Cuentas por Cobrar</span>
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 mt-1">Cuentas y Saldos de Clientes</h1>
                <p class="text-sm text-slate-500">Gestión de créditos, deudas pendientes y cuentas corrientes de propietarios.</p>
            </div>
            <a
                href="{{ route('caja.index') }}"
                class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 transition-colors"
            >
                <i data-lucide="arrow-left" class="size-4 text-slate-400"></i>
                <span>Volver a Caja</span>
            </a>
        </div>

        <!-- Tabla de Cuentas -->
        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="border-b border-slate-100 bg-slate-50/75 text-xs font-bold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-5 py-3.5">N° Cuenta</th>
                            <th class="px-4 py-3.5">Propietario</th>
                            <th class="px-4 py-3.5">Documento / Teléfono</th>
                            <th class="px-4 py-3.5">Fecha Apertura</th>
                            <th class="px-4 py-3.5 text-right">Cargos Totales</th>
                            <th class="px-4 py-3.5 text-right">Pagos Realizados</th>
                            <th class="px-4 py-3.5 text-center">Estado</th>
                            <th class="px-5 py-3.5 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($cuentas as $cuenta)
                            @php
                                $persona = $cuenta->propietario?->persona;
                                $totalCargos = (float) $cuenta->cargos->sum('monto');
                                $totalPagos = (float) $cuenta->pagos->flatMap->detallePagos->sum('monto');
                                $saldoPendiente = max(0, $totalCargos - $totalPagos);
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <!-- N° Cuenta -->
                                <td class="px-5 py-3.5 font-mono text-xs font-bold text-slate-900">
                                    #CTA-{{ str_pad($cuenta->id_cuenta, 4, '0', STR_PAD_LEFT) }}
                                </td>

                                <!-- Propietario -->
                                <td class="px-4 py-3.5">
                                    <div class="font-medium text-slate-900 text-xs">
                                        {{ $persona ? $persona->nombre . ' ' . $persona->apellido1 : 'Propietario #' . $cuenta->id_propietario }}
                                    </div>
                                </td>

                                <!-- Documento / Contacto -->
                                <td class="px-4 py-3.5 text-xs text-slate-500">
                                    <div>CI: {{ $persona?->documento ?? 'S/D' }}</div>
                                    <div class="text-[11px] text-slate-400">Tel: {{ $persona?->telefono ?? '—' }}</div>
                                </td>

                                <!-- Fecha Apertura -->
                                <td class="px-4 py-3.5 whitespace-nowrap text-xs text-slate-600">
                                    {{ $cuenta->fecha_apertura ? \Carbon\Carbon::parse($cuenta->fecha_apertura)->format('d/m/Y') : 'N/A' }}
                                </td>

                                <!-- Cargos Totales -->
                                <td class="px-4 py-3.5 text-right font-mono text-xs font-semibold text-slate-800">
                                    ${{ number_format($totalCargos, 2) }}
                                </td>

                                <!-- Pagos Realizados -->
                                <td class="px-4 py-3.5 text-right font-mono text-xs font-semibold text-emerald-700">
                                    ${{ number_format($totalPagos, 2) }}
                                </td>

                                <!-- Estado -->
                                <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                    @if ($saldoPendiente > 0.01)
                                        <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700">
                                            Deuda: ${{ number_format($saldoPendiente, 2) }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                                            Al día
                                        </span>
                                    @endif
                                </td>

                                <!-- Acciones -->
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    <a
                                        href="{{ route('caja.cobro', ['id_propietario' => $cuenta->id_propietario]) }}"
                                        class="inline-flex items-center gap-1 rounded-lg bg-primary-50 px-2.5 py-1.5 text-xs font-semibold text-primary-700 hover:bg-primary-100 transition-colors"
                                    >
                                        <i data-lucide="plus" class="size-3.5"></i>
                                        <span>Abonar / Cobrar</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-12 text-center">
                                    <x-empty-state
                                        title="No hay cuentas registradas"
                                        description="Las cuentas de clientes se crean automáticamente al registrar atenciones o ventas a crédito."
                                    >
                                        <x-slot:icon>
                                            <i data-lucide="book-open" class="size-8 text-slate-300"></i>
                                        </x-slot:icon>
                                    </x-empty-state>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            @if ($cuentas->hasPages())
                <div class="border-t border-slate-100 px-5 py-3">
                    {{ $cuentas->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection
