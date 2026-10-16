@extends('layouts.app')

@section('title', 'Registrar Cobro — La Petdiatría')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6" x-data="{
        atenciones: {{ Js::from($atencionesPendientes->map(fn($a) => [
            'id_atencion' => $a->id_atencion,
            'paciente_nombre' => $a->paciente?->nombre ?? 'Paciente',
            'propietario_nombre' => $a->paciente?->propietario?->persona ? ($a->paciente->propietario->persona->nombre . ' ' . $a->paciente->propietario->persona->apellido1) : 'Sin Propietario',
            'propietario_id' => $a->paciente?->id_propietario,
            'total' => (float) ($a->total_final ?? 0),
            'servicios' => $a->atencionServicios->map(fn($s) => $s->servicio?->nombre ?? 'Servicio')->implode(', ')
        ])) }},
        idAtencion: @js($atencionSeleccionada?->id_atencion ?? request('id_atencion', '')),
        idPropietario: @js($atencionSeleccionada?->paciente?->id_propietario ?? ''),
        montoBase: {{ (float) ($atencionSeleccionada?->total_final ?? 0) }},
        descuento: 0,
        efectivoRecibido: 0,
        idMetodoPago: @js($metodosPago->first()?->id_metodo_pago ?? 1),
        
        init() {
            if (this.idAtencion) {
                this.seleccionarAtencion(this.idAtencion);
            }
        },

        seleccionarAtencion(id) {
            const atencion = this.atenciones.find(a => a.id_atencion == id);
            if (atencion) {
                this.montoBase = atencion.total;
                this.idPropietario = atencion.propietario_id;
            }
        },

        get totalFinal() {
            return Math.max(0, this.montoBase - (parseFloat(this.descuento) || 0));
        },

        get cambio() {
            const recibido = parseFloat(this.efectivoRecibido) || 0;
            return Math.max(0, recibido - this.totalFinal);
        }
    }">

        <!-- Encabezado con breadcrumb -->
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2 text-xs text-slate-400">
                    <a href="{{ route('caja.index') }}" class="hover:text-primary-600 transition-colors">Caja y Cobros</a>
                    <span>/</span>
                    <span class="text-slate-600 font-medium">Registrar Cobro</span>
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 mt-1">Registrar Cobro en Caja</h1>
            </div>
            <a
                href="{{ route('caja.index') }}"
                class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 transition-colors"
            >
                <i data-lucide="arrow-left" class="size-4 text-slate-400"></i>
                <span>Volver a Caja</span>
            </a>
        </div>

        <form action="{{ route('caja.cobro.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Columna Izquierda / Central: Selección y datos del cobro (2 cols) -->
                <div class="md:col-span-2 space-y-6">

                    <!-- Tarjeta: Origen del Cobro -->
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs">
                        <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                            <div class="flex size-9 items-center justify-center rounded-xl bg-primary-50 text-primary-600">
                                <i data-lucide="stethoscope" class="size-5"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900">Origen de la Liquidación</h3>
                                <p class="text-xs text-slate-500">Selecciona la atención pendiente o el cliente a cobrar</p>
                            </div>
                        </div>

                        <div class="mt-5 space-y-4">
                            <!-- Selector de Atención Pendiente -->
                            <div>
                                <label for="id_atencion" class="block text-xs font-semibold text-slate-700 mb-1">
                                    Atención Pendiente de Cobro (opcional)
                                </label>
                                <select
                                    id="id_atencion"
                                    name="id_atencion"
                                    x-model="idAtencion"
                                    @change="seleccionarAtencion($event.target.value)"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50/50 p-2.5 text-xs text-slate-800 focus-ring"
                                >
                                    <option value="">-- Cobro libre o sin atención vinculada --</option>
                                    @foreach ($atencionesPendientes as $aten)
                                        <option value="{{ $aten->id_atencion }}" {{ ($atencionSeleccionada?->id_atencion == $aten->id_atencion) ? 'selected' : '' }}>
                                            #{{ $aten->id_atencion }} · {{ $aten->paciente?->nombre }} ({{ $aten->paciente?->propietario?->persona?->nombre }}) — Total: ${{ number_format((float)$aten->total_final, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Selector de Propietario / Cliente -->
                            <div>
                                <label for="id_propietario" class="block text-xs font-semibold text-slate-700 mb-1">
                                    Cliente / Propietario <span class="text-slate-400 font-normal">(asociar a cuenta corriente)</span>
                                </label>
                                <select
                                    id="id_propietario"
                                    name="id_propietario"
                                    x-model="idPropietario"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50/50 p-2.5 text-xs text-slate-800 focus-ring"
                                >
                                    <option value="">-- Cliente Mostrador / Sin registrar --</option>
                                    @foreach ($propietarios as $prop)
                                        <option value="{{ $prop->id_persona }}">
                                            {{ $prop->persona?->nombre }} {{ $prop->persona?->apellido1 }} · CI: {{ $prop->persona?->documento ?? 'S/D' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Resumen si hay atención seleccionada -->
                            @if ($atencionSeleccionada)
                                <div class="rounded-xl bg-slate-50 p-4 border border-slate-100 space-y-2">
                                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Detalle de la Atención #{{ $atencionSeleccionada->id_atencion }}</p>
                                    <div class="grid grid-cols-2 gap-2 text-xs">
                                        <div>
                                            <span class="text-slate-400">Paciente:</span>
                                            <span class="font-semibold text-slate-800 ml-1">{{ $atencionSeleccionada->paciente?->nombre }}</span>
                                        </div>
                                        <div>
                                            <span class="text-slate-400">Propietario:</span>
                                            <span class="font-semibold text-slate-800 ml-1">{{ $atencionSeleccionada->paciente?->propietario?->persona?->nombre ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                    <div class="pt-2 border-t border-slate-200/60 text-xs">
                                        <span class="text-slate-400">Servicios realizados:</span>
                                        <div class="mt-1 flex flex-wrap gap-1">
                                            @foreach ($atencionSeleccionada->atencionServicios as $as)
                                                <span class="rounded-md bg-white px-2 py-0.5 border border-slate-200 text-slate-700 text-[11px] font-medium">
                                                    {{ $as->servicio?->nombre }} (${{ number_format((float)$as->precio_aplicado, 2) }})
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Tarjeta: Método de Pago y Referencia -->
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs">
                        <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                            <div class="flex size-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                                <i data-lucide="credit-card" class="size-5"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900">Método de Pago</h3>
                                <p class="text-xs text-slate-500">Selecciona cómo realiza el abono el cliente</p>
                            </div>
                        </div>

                        <div class="mt-5 space-y-4">
                            <!-- Opciones de métodos con botones visuales -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-2">Forma de pago elegida <span class="text-rose-500">*</span></label>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                                    @foreach ($metodosPago as $metodo)
                                        <label
                                            class="flex flex-col items-center justify-center rounded-xl border p-3 cursor-pointer transition-all text-center"
                                            :class="idMetodoPago == '{{ $metodo->id_metodo_pago }}' ? 'border-primary-600 bg-primary-50/40 text-primary-700 ring-2 ring-primary-500/20' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'"
                                        >
                                            <input
                                                type="radio"
                                                name="id_metodo_pago"
                                                value="{{ $metodo->id_metodo_pago }}"
                                                x-model="idMetodoPago"
                                                class="sr-only"
                                            />
                                            <i data-lucide="{{ str_contains($metodo->nombre, 'EFECTIVO') ? 'banknote' : (str_contains($metodo->nombre, 'QR') ? 'qr-code' : 'credit-card') }}" class="size-5 mb-1"></i>
                                            <span class="text-xs font-semibold">{{ $metodo->nombre }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Campo de Efectivo y Cambio (visible si es Efectivo) -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-100">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Efectivo entregado por cliente</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">$</span>
                                        <input
                                            type="number"
                                            step="0.01"
                                            x-model="efectivoRecibido"
                                            placeholder="0.00"
                                            class="w-full rounded-xl border border-slate-200 bg-slate-50/50 pl-7 pr-3 py-2 text-xs font-mono text-slate-800 focus-ring"
                                        />
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Cambio a devolver</label>
                                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-mono font-bold text-emerald-700">
                                        $<span x-text="cambio.toFixed(2)"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Referencia / Voucher -->
                            <div>
                                <label for="referencia" class="block text-xs font-semibold text-slate-700 mb-1">
                                    N° de Referencia / Voucher / Autorización <span class="text-slate-400 font-normal">(opcional)</span>
                                </label>
                                <input
                                    type="text"
                                    id="referencia"
                                    name="referencia"
                                    placeholder="Ej: POS-98231 o Ref Transferencia..."
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 focus-ring"
                                />
                            </div>

                            <!-- Observaciones -->
                            <div>
                                <label for="observaciones" class="block text-xs font-semibold text-slate-700 mb-1">Observaciones o concepto</label>
                                <input
                                    type="text"
                                    id="observaciones"
                                    name="observaciones"
                                    placeholder="Notas del cobro..."
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 focus-ring"
                                />
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Columna Derecha: Tarjeta de Liquidación y Confirmación (1 col) -->
                <div class="space-y-6">
                    <div class="sticky top-20 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs space-y-5">
                        <h3 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Resumen de Liquidación</h3>

                        <!-- Importe Base -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Monto Base ($) <span class="text-rose-500">*</span></label>
                            <input
                                type="number"
                                step="0.01"
                                min="0.01"
                                name="monto"
                                x-model="montoBase"
                                required
                                class="w-full rounded-xl border border-slate-200 bg-white p-2.5 text-sm font-mono font-bold text-slate-900 focus-ring"
                            />
                        </div>

                        <!-- Descuento adicional -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Descuento aplicado ($)</label>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                name="descuento"
                                x-model="descuento"
                                placeholder="0.00"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/50 p-2 text-xs font-mono text-slate-800 focus-ring"
                            />
                        </div>

                        <!-- Desglose Final -->
                        <div class="rounded-xl bg-slate-50 p-4 border border-slate-100 space-y-2 text-xs">
                            <div class="flex justify-between text-slate-500">
                                <span>Subtotal:</span>
                                <span class="font-mono" x-text="'$' + (parseFloat(montoBase) || 0).toFixed(2)"></span>
                            </div>
                            <div class="flex justify-between text-rose-600" x-show="descuento > 0">
                                <span>Descuento:</span>
                                <span class="font-mono" x-text="'-$' + (parseFloat(descuento) || 0).toFixed(2)"></span>
                            </div>
                            <div class="pt-2 border-t border-slate-200 flex justify-between items-center text-sm font-bold text-slate-900">
                                <span>Total a Cobrar:</span>
                                <span class="font-mono text-xl text-primary-600" x-text="'$' + totalFinal.toFixed(2)"></span>
                            </div>
                        </div>

                        <!-- Botón de Confirmación -->
                        <button
                            type="submit"
                            :disabled="totalFinal <= 0"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-primary-600 py-3 text-sm font-semibold text-white shadow-xs hover:bg-primary-700 disabled:opacity-50 transition-colors"
                        >
                            <i data-lucide="check-circle" class="size-4"></i>
                            <span>Procesar y Emitir Recibo</span>
                        </button>

                        <p class="text-center text-[11px] text-slate-400">
                            Se generará el recibo contable y se actualizará el estado de la atención.
                        </p>
                    </div>
                </div>
            </div>

        </form>

    </div>
@endsection
