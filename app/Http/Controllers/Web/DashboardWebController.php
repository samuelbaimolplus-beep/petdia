<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Atencion;
use App\Models\Cargo;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\DetallePago;
use App\Models\Especie;
use App\Models\Paciente;
use App\Models\Profesional;
use App\Models\RegistroAcceso;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardWebController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Usuario $user */
        $user = Auth::user();
        $rol = strtoupper(str_replace(['-', ' '], '_', $user->rol_principal));

        if ($rol === 'ESTETICO') {
            $rol = 'SPA';
        }

        $hoy = Carbon::today();
        $mesInicio = Carbon::now()->startOfMonth();

        // Métricas base de Ciclo 1: Administración de Usuarios y CU Base
        $totalClientes = Schema::hasTable('clientes') ? Cliente::count() : 0;
        $totalMascotas = Schema::hasTable('pacientes') ? Paciente::count() : 0;
        $totalProfesionales = Schema::hasTable('profesionales') ? Profesional::where('estado', 'ACTIVO')->count() : 0;
        $totalUsuarios = Schema::hasTable('usuarios') ? Usuario::count() : 0;
        $usuariosActivos = Schema::hasTable('usuarios') ? Usuario::where('estado', 'ACTIVO')->count() : 0;
        $totalEspecies = Schema::hasTable('especies') ? Especie::count() : 0;

        $accesosHoy = Schema::hasTable('registro_accesos')
            ? RegistroAcceso::whereDate('fecha_ingreso', $hoy)->count()
            : 0;

        $ultimosAccesos = Schema::hasTable('registro_accesos')
            ? RegistroAcceso::orderBy('id_acceso', 'desc')->take(6)->get()
            : collect();

        $ultimosClientes = Schema::hasTable('clientes')
            ? Cliente::with('persona')->orderBy('id_cliente', 'desc')->take(5)->get()
            : collect();

        $ultimasMascotas = Schema::hasTable('pacientes')
            ? Paciente::with(['propietario.persona', 'raza.especie'])->orderBy('id_paciente', 'desc')->take(5)->get()
            : collect();

        // 1. Citas del día (si existen datos de prueba)
        $citasHoy = collect();
        $totalCitasHoy = 0;
        try {
            if (Schema::hasTable('citas')) {
                $citasHoy = Cita::with(['paciente.propietario.persona', 'detalles.servicio', 'profesional.persona'])
                    ->whereDate('fecha_hora_inicio', $hoy)
                    ->orderBy('fecha_hora_inicio')
                    ->get();
                $totalCitasHoy = $citasHoy->count();
            }
        } catch (\Throwable $e) {
            // Silencioso en caso de tablas en migración
        }

        // 2. Gráfica de carga por hora
        $horas = collect(range(8, 19))->map(function (int $hora) use ($citasHoy) {
            $completadas = $citasHoy->filter(function ($c) use ($hora) {
                return Carbon::parse($c->fecha_hora_inicio)->hour === $hora
                    && in_array(strtoupper($c->estado ?? ''), ['COMPLETADA', 'COMPLETADO']);
            })->count();

            $restantes = $citasHoy->filter(function ($c) use ($hora) {
                return Carbon::parse($c->fecha_hora_inicio)->hour === $hora
                    && ! in_array(strtoupper($c->estado ?? ''), ['COMPLETADA', 'COMPLETADO']);
            })->count();

            return [
                'hora' => $hora,
                'label' => sprintf('%02d:00', $hora),
                'completadas' => $completadas,
                'restantes' => $restantes,
                'total' => $completadas + $restantes,
            ];
        });

        $totalCompletadas = $horas->sum('completadas');
        $totalRestantes = $horas->sum('restantes');
        $maxHora = $horas->sortByDesc('total')->first();
        $horaPico = ($maxHora && $maxHora['total'] > 0) ? $maxHora : null;

        // 3. Sala de espera
        $salaEspera = collect();
        $salaEsperaCount = 0;
        try {
            if (Schema::hasTable('atenciones')) {
                $salaEspera = Atencion::with(['paciente.propietario.persona', 'profesionales.persona', 'cita.profesional.persona'])
                    ->whereIn('estado', ['PENDIENTE', 'EN_PROCESO', 'EN_ESPERA'])
                    ->orderBy('fecha_hora_inicio', 'desc')
                    ->limit(5)
                    ->get();
                $salaEsperaCount = Atencion::whereIn('estado', ['PENDIENTE', 'EN_PROCESO', 'EN_ESPERA'])->count();
            }
        } catch (\Throwable $e) {
        }

        // 4. Ingresos del mes
        $ingresosMes = 0.0;
        try {
            if (Schema::hasTable('detalle_pagos')) {
                $ingresosMes = (float) DetallePago::whereHas('pago', function ($q) use ($mesInicio) {
                    $q->where('fecha_hora', '>=', $mesInicio)->where('estado', '!=', 'ANULADO');
                })->sum('monto');
            }
        } catch (\Throwable $e) {
        }

        // 5. Deudas
        $deudasVencidasCount = 0;
        $montoDeudasVencidas = 0.0;
        try {
            if (Schema::hasTable('cargos')) {
                $deudasVencidasCount = Cargo::whereIn('estado', ['PENDIENTE', 'VENCIDO'])->count();
                $montoDeudasVencidas = (float) Cargo::whereIn('estado', ['PENDIENTE', 'VENCIDO'])->sum('monto');
            }
        } catch (\Throwable $e) {
        }

        // 6. Alertas de inventario
        $productosStockBajo = collect();
        $stockBajoCount = 0;
        $lotesPorVencer = collect();
        $vencimientosCount = 0;

        // 7. Recordatorios
        $recordatorios = collect();

        // 8. Métricas configuradas según el rol del usuario (Adaptadas a Ciclo 1)
        $kpis = $this->buildKpisForRole(
            $rol,
            $totalClientes,
            $totalMascotas,
            $totalProfesionales,
            $totalUsuarios,
            $usuariosActivos,
            $accesosHoy,
            $totalEspecies
        );

        // 9. Datos para Gráficas
        $ultimos7Dias = collect(range(6, 0))->map(fn ($d) => Carbon::today()->subDays($d));
        $labelsCitas = $ultimos7Dias->map(fn ($d) => $d->format('d/m'))->values();

        $citasAgendadas = $ultimos7Dias->map(fn () => 0)->values();
        $citasAtendidas = $ultimos7Dias->map(fn () => 0)->values();
        $citasCanceladas = $ultimos7Dias->map(fn () => 0)->values();

        $ultimos30Dias = collect(range(29, 0))->map(fn ($d) => Carbon::today()->subDays($d));
        $labelsIngresos = $ultimos30Dias->map(fn ($d) => $d->format('d/m'))->values();
        $valoresIngresos = $ultimos30Dias->map(fn () => 0.0)->values();

        $chartData = [
            'citas' => [
                'labels' => $labelsCitas,
                'agendadas' => $citasAgendadas,
                'atendidas' => $citasAtendidas,
                'canceladas' => $citasCanceladas,
            ],
            'ingresos' => [
                'labels' => $labelsIngresos,
                'valores' => $valoresIngresos,
            ],
        ];

        return view('dashboard.index', [
            'user' => $user,
            'rol' => $rol,
            'kpis' => $kpis,
            'citasHoy' => $citasHoy,
            'totalCitasHoy' => $totalCitasHoy,
            'totalCompletadas' => $totalCompletadas,
            'totalRestantes' => $totalRestantes,
            'horas' => $horas,
            'horaPico' => $horaPico,
            'salaEspera' => $salaEspera,
            'salaEsperaCount' => $salaEsperaCount,
            'ingresosMes' => $ingresosMes,
            'deudasVencidasCount' => $deudasVencidasCount,
            'montoDeudasVencidas' => $montoDeudasVencidas,
            'productosStockBajo' => $productosStockBajo,
            'stockBajoCount' => $stockBajoCount,
            'lotesPorVencer' => $lotesPorVencer,
            'vencimientosCount' => $vencimientosCount,
            'recordatorios' => $recordatorios,
            'chartData' => $chartData,
            'totalClientes' => $totalClientes,
            'totalMascotas' => $totalMascotas,
            'totalProfesionales' => $totalProfesionales,
            'totalUsuarios' => $totalUsuarios,
            'usuariosActivos' => $usuariosActivos,
            'accesosHoy' => $accesosHoy,
            'ultimosAccesos' => $ultimosAccesos,
            'ultimosClientes' => $ultimosClientes,
            'ultimasMascotas' => $ultimasMascotas,
        ]);
    }

    /**
     * Construye las 4 tarjetas KPI adaptadas al rol del usuario para Ciclo 1.
     */
    private function buildKpisForRole(
        string $rol,
        int $totalClientes,
        int $totalMascotas,
        int $totalProfesionales,
        int $totalUsuarios,
        int $usuariosActivos,
        int $accesosHoy,
        int $totalEspecies
    ): array {
        switch ($rol) {
            case 'RECEPCION':
                return [
                    [
                        'label' => 'CLIENTES REGISTRADOS',
                        'value' => (string) $totalClientes,
                        'description' => 'Propietarios en sistema (CU9)',
                        'icon' => 'users',
                        'tone' => 'primary',
                    ],
                    [
                        'label' => 'MASCOTAS ACTIVAS',
                        'value' => (string) $totalMascotas,
                        'description' => 'Pacientes asociados (CU10/11)',
                        'icon' => 'paw-print',
                        'tone' => 'warning',
                    ],
                    [
                        'label' => 'BÚSQUEDA RÁPIDA',
                        'value' => 'CU12',
                        'description' => 'Filtro de clientes y mascotas',
                        'icon' => 'search',
                        'tone' => 'success',
                    ],
                    [
                        'label' => 'HISTORIAL SERVICIOS',
                        'value' => 'CU13',
                        'description' => 'Consulta disponible por paciente',
                        'icon' => 'history',
                        'tone' => 'violet',
                    ],
                ];

            case 'VETERINARIO':
                return [
                    [
                        'label' => 'PACIENTES / MASCOTAS',
                        'value' => (string) $totalMascotas,
                        'description' => 'Animales en clínica (CU10)',
                        'icon' => 'paw-print',
                        'tone' => 'primary',
                    ],
                    [
                        'label' => 'PROPIETARIOS',
                        'value' => (string) $totalClientes,
                        'description' => 'Clientes registrados (CU9)',
                        'icon' => 'users',
                        'tone' => 'warning',
                    ],
                    [
                        'label' => 'HISTORIAL CLÍNICO',
                        'value' => 'CU20',
                        'description' => 'Acceso médico autorizado',
                        'icon' => 'clipboard-check',
                        'tone' => 'success',
                    ],
                    [
                        'label' => 'ESPECIES ATENDIDAS',
                        'value' => (string) $totalEspecies,
                        'description' => 'Especies en catálogo activo',
                        'icon' => 'dna',
                        'tone' => 'violet',
                    ],
                ];

            case 'SPA':
                return [
                    [
                        'label' => 'MASCOTAS REGISTRADAS',
                        'value' => (string) $totalMascotas,
                        'description' => 'Pacientes en sistema (CU10)',
                        'icon' => 'sparkles',
                        'tone' => 'violet',
                    ],
                    [
                        'label' => 'PROPIETARIOS',
                        'value' => (string) $totalClientes,
                        'description' => 'Clientes registrados (CU9)',
                        'icon' => 'users',
                        'tone' => 'primary',
                    ],
                    [
                        'label' => 'HISTORIAL SERVICIOS',
                        'value' => 'CU13',
                        'description' => 'Historial de estética y baños',
                        'icon' => 'history',
                        'tone' => 'warning',
                    ],
                    [
                        'label' => 'ESTADO SISTEMA',
                        'value' => 'ACTIVO',
                        'description' => 'Ciclo 1: Gestión Base Operativa',
                        'icon' => 'check-circle-2',
                        'tone' => 'success',
                    ],
                ];

            case 'TRANSPORTE':
                return [
                    [
                        'label' => 'MASCOTAS REGISTRADAS',
                        'value' => (string) $totalMascotas,
                        'description' => 'Pacientes en sistema (CU10)',
                        'icon' => 'truck',
                        'tone' => 'primary',
                    ],
                    [
                        'label' => 'PROPIETARIOS',
                        'value' => (string) $totalClientes,
                        'description' => 'Clientes con dirección (CU9)',
                        'icon' => 'users',
                        'tone' => 'warning',
                    ],
                    [
                        'label' => 'HISTORIAL SERVICIOS',
                        'value' => 'CU13',
                        'description' => 'Servicios a domicilio consultables',
                        'icon' => 'history',
                        'tone' => 'violet',
                    ],
                    [
                        'label' => 'ESTADO SISTEMA',
                        'value' => 'ACTIVO',
                        'description' => 'Ciclo 1: Gestión Base Operativa',
                        'icon' => 'check-circle-2',
                        'tone' => 'success',
                    ],
                ];

            case 'ADMIN':
                return [
                    [
                        'label' => 'CLIENTES REGISTRADOS',
                        'value' => (string) $totalClientes,
                        'description' => 'Gestión de propietarios (CU9/14)',
                        'icon' => 'users',
                        'tone' => 'primary',
                    ],
                    [
                        'label' => 'MASCOTAS REGISTRADAS',
                        'value' => (string) $totalMascotas,
                        'description' => 'Pacientes y vínculos (CU10/11)',
                        'icon' => 'paw-print',
                        'tone' => 'warning',
                    ],
                    [
                        'label' => 'PROFESIONALES',
                        'value' => (string) $totalProfesionales,
                        'description' => 'Equipo profesional activo (CU53)',
                        'icon' => 'user-check',
                        'tone' => 'success',
                    ],
                    [
                        'label' => 'ESPECIES Y RAZAS',
                        'value' => (string) $totalEspecies,
                        'description' => 'Catálogo taxonómico configurado',
                        'icon' => 'dna',
                        'tone' => 'violet',
                    ],
                ];

            case 'SUPER_ADMIN':
            default:
                return [
                    [
                        'label' => 'USUARIOS DEL SISTEMA',
                        'value' => (string) $totalUsuarios,
                        'description' => "{$usuariosActivos} activos / control de acceso (CU3-CU6)",
                        'icon' => 'shield-check',
                        'tone' => 'primary',
                    ],
                    [
                        'label' => 'CLIENTES & PROPIETARIOS',
                        'value' => (string) $totalClientes,
                        'description' => 'Clientes registrados (CU9)',
                        'icon' => 'users',
                        'tone' => 'warning',
                    ],
                    [
                        'label' => 'MASCOTAS REGISTRADAS',
                        'value' => (string) $totalMascotas,
                        'description' => 'Pacientes en plataforma (CU10/11)',
                        'icon' => 'paw-print',
                        'tone' => 'success',
                    ],
                    [
                        'label' => 'ACCESOS HOY',
                        'value' => (string) $accesosHoy,
                        'description' => 'Inicios de sesión registrados hoy',
                        'icon' => 'clock',
                        'tone' => 'violet',
                    ],
                ];
        }
    }
}
