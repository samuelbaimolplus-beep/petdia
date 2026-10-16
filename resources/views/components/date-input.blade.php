@props([
    'name' => 'fecha',
    'value' => null,
    'placeholder' => 'DD/MM/AAAA',
    'disablePast' => false,
    'invalid' => false,
    'required' => false,
])

@php
    $initialValue = old($name, $value);
    $initialDateStr = $initialValue ? \Carbon\Carbon::parse($initialValue)->format('Y-m-d') : '';
    $initialDisplayStr = $initialValue ? \Carbon\Carbon::parse($initialValue)->format('d/m/Y') : '';
@endphp

<div
    x-data="datePickerComponent({
        initialDate: '{{ $initialDateStr }}',
        initialDisplay: '{{ $initialDisplayStr }}',
        disablePast: {{ $disablePast ? 'true' : 'false' }}
    })"
    class="relative"
    @click.away="open = false"
    @keydown.escape.window="open = false"
>
    <!-- Input oculto para envío estándar del formulario en formato Y-m-d -->
    <input
        type="hidden"
        name="{{ $name }}"
        :value="selectedDate"
        {{ $required ? 'required' : '' }}
    />

    <!-- Input visual con formato DD/MM/AAAA -->
    <div class="relative cursor-pointer" @click="toggleCalendar()">
        <input
            type="text"
            readonly
            :value="displayDate"
            placeholder="{{ $placeholder }}"
            class="block w-full cursor-pointer rounded-xl border-0 py-2.5 pl-3.5 pr-10 text-sm text-slate-900 shadow-xs ring-1 ring-inset bg-white transition-all {{ $invalid ? 'ring-rose-500 focus:ring-rose-500' : 'ring-slate-200 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600' }}"
            {{ $attributes->except(['class', 'name', 'value', 'type']) }}
        />
        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
            <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
    </div>

    <!-- Dropdown Calendario Visual Mes a Mes -->
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95 translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-1"
        class="absolute left-0 z-50 mt-1.5 w-72 rounded-2xl bg-white p-4 shadow-xl border border-slate-100 ring-1 ring-black/5"
    >
        <!-- Navegación de Mes / Año -->
        <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100">
            <button
                type="button"
                @click.stop="prevMonth()"
                class="p-1.5 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition-colors"
                title="Mes anterior"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>

            <span class="text-xs font-bold text-slate-800 tracking-tight" x-text="monthYearTitle"></span>

            <button
                type="button"
                @click.stop="nextMonth()"
                class="p-1.5 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition-colors"
                title="Mes siguiente"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>

        <!-- Días de la semana -->
        <div class="grid grid-cols-7 gap-1 text-center mb-1">
            <span class="text-[10px] font-bold text-slate-400">Lu</span>
            <span class="text-[10px] font-bold text-slate-400">Ma</span>
            <span class="text-[10px] font-bold text-slate-400">Mi</span>
            <span class="text-[10px] font-bold text-slate-400">Ju</span>
            <span class="text-[10px] font-bold text-slate-400">Vi</span>
            <span class="text-[10px] font-bold text-slate-400">Sá</span>
            <span class="text-[10px] font-bold text-rose-400">Do</span>
        </div>

        <!-- Cuadrícula de días -->
        <div class="grid grid-cols-7 gap-1 text-center text-xs">
            <template x-for="(blank, i) in blankDays" :key="'blank-' + i">
                <span class="p-1.5"></span>
            </template>

            <template x-for="dateObj in daysInMonth" :key="dateObj.dateStr">
                <button
                    type="button"
                    :disabled="dateObj.disabled"
                    @click.stop="selectDate(dateObj)"
                    class="p-1.5 rounded-xl text-center text-xs transition-colors font-medium"
                    :class="{
                        'bg-indigo-600 text-white font-bold shadow-2xs': dateObj.isSelected,
                        'text-indigo-600 font-bold bg-indigo-50': dateObj.isToday && !dateObj.isSelected,
                        'text-slate-700 hover:bg-slate-100': !dateObj.isSelected && !dateObj.disabled && !dateObj.isToday,
                        'text-slate-300 cursor-not-allowed': dateObj.disabled
                    }"
                    x-text="dateObj.dayNumber"
                ></button>
            </template>
        </div>

        <!-- Botón Hoy y Limpiar -->
        <div class="flex items-center justify-between mt-3 pt-2 border-t border-slate-100 text-[11px]">
            <button
                type="button"
                @click.stop="setToday()"
                class="font-semibold text-indigo-600 hover:text-indigo-800"
            >
                Hoy
            </button>
            <button
                type="button"
                @click.stop="clearDate()"
                class="font-medium text-slate-400 hover:text-slate-600"
            >
                Limpiar
            </button>
        </div>
    </div>
</div>

<script>
if (typeof datePickerComponent === 'undefined') {
    function datePickerComponent(config) {
        return {
            open: false,
            selectedDate: config.initialDate || '',
            displayDate: config.initialDisplay || '',
            disablePast: config.disablePast || false,
            viewYear: new Date().getFullYear(),
            viewMonth: new Date().getMonth(),
            monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
            init() {
                if (this.selectedDate) {
                    const parts = this.selectedDate.split('-');
                    if (parts.length === 3) {
                        this.viewYear = parseInt(parts[0], 10);
                        this.viewMonth = parseInt(parts[1], 10) - 1;
                    }
                }
            },
            toggleCalendar() {
                this.open = !this.open;
            },
            get monthYearTitle() {
                return this.monthNames[this.viewMonth] + ' ' + this.viewYear;
            },
            get blankDays() {
                const firstDay = new Date(this.viewYear, this.viewMonth, 1).getDay();
                // Domingo = 0 -> 6 blanks (lunes inicio), Lunes = 1 -> 0 blanks
                const blanks = (firstDay === 0) ? 6 : firstDay - 1;
                return Array.from({ length: blanks });
            },
            get daysInMonth() {
                const totalDays = new Date(this.viewYear, this.viewMonth + 1, 0).getDate();
                const days = [];
                const today = new Date();
                today.setHours(0,0,0,0);

                for (let d = 1; d <= totalDays; d++) {
                    const date = new Date(this.viewYear, this.viewMonth, d);
                    date.setHours(0,0,0,0);

                    const m = String(this.viewMonth + 1).padStart(2, '0');
                    const day = String(d).padStart(2, '0');
                    const dateStr = `${this.viewYear}-${m}-${day}`;
                    const displayStr = `${day}/${m}/${this.viewYear}`;

                    const isToday = date.getTime() === today.getTime();
                    const isPast = date.getTime() < today.getTime();
                    const disabled = this.disablePast && isPast;
                    const isSelected = this.selectedDate === dateStr;

                    days.push({
                        dayNumber: d,
                        dateStr,
                        displayStr,
                        isToday,
                        disabled,
                        isSelected
                    });
                }
                return days;
            },
            prevMonth() {
                if (this.viewMonth === 0) {
                    this.viewMonth = 11;
                    this.viewYear--;
                } else {
                    this.viewMonth--;
                }
            },
            nextMonth() {
                if (this.viewMonth === 11) {
                    this.viewMonth = 0;
                    this.viewYear++;
                } else {
                    this.viewMonth++;
                }
            },
            selectDate(dateObj) {
                if (dateObj.disabled) return;
                this.selectedDate = dateObj.dateStr;
                this.displayDate = dateObj.displayStr;
                this.open = false;
            },
            setToday() {
                const today = new Date();
                const y = today.getFullYear();
                const m = String(today.getMonth() + 1).padStart(2, '0');
                const d = String(today.getDate()).padStart(2, '0');
                this.viewYear = y;
                this.viewMonth = today.getMonth();
                this.selectedDate = `${y}-${m}-${d}`;
                this.displayDate = `${d}/${m}/${y}`;
                this.open = false;
            },
            clearDate() {
                this.selectedDate = '';
                this.displayDate = '';
                this.open = false;
            }
        };
    }
}
</script>
