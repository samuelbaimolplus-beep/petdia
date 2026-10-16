<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditoriaLog;
use App\Models\Usuario;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuditoriaWebController extends Controller
{
    /**
     * Muestra la bitácora de auditoría inmutable para SUPER_ADMIN.
     */
    public function index(Request $request): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();

        // Control estricto: Solo SUPER_ADMIN
        if (! $currentUser->hasRole(['SUPER_ADMIN', 'SUPER-ADMIN'])) {
            abort(403, 'Acceso restringido: La bitácora de auditoría es exclusiva del Super Administrador.');
        }

        // Registrar acceso del Super-Administrador a la bitácora (Requisito Sección 3)
        AuditService::log(
            'CONSULTA_BITACORA',
            'AUDITORIA',
            ['ip' => $request->ip()],
            null,
            $currentUser->id_usuario,
            'EXITO',
            'Super-Administrador consultó la bitácora del sistema'
        );

        $query = AuditoriaLog::with('usuario.persona')->orderBy('created_at', 'desc');

        // Filtro por módulo
        if ($modulo = $request->input('modulo')) {
            $query->where('modulo', strtoupper($modulo));
        }

        // Filtro por acción
        if ($accion = $request->input('accion')) {
            $query->where('accion', strtoupper($accion));
        }

        // Filtro por resultado (EXITO, RECHAZADO, FALLIDO)
        if ($resultado = $request->input('resultado')) {
            $query->where('resultado', strtoupper($resultado));
        }

        // Filtro por usuario
        if ($idUsuario = $request->input('id_usuario')) {
            $query->where('id_usuario', $idUsuario);
        }

        // Filtro por rango de fechas o fecha puntual
        if ($fechaDesde = $request->input('fecha_desde')) {
            $query->whereDate('created_at', '>=', $fechaDesde);
        }
        if ($fechaHasta = $request->input('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $fechaHasta);
        }
        if ($fecha = $request->input('fecha')) {
            $query->whereDate('created_at', $fecha);
        }

        // Búsqueda general
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('accion', 'ilike', "%{$search}%")
                    ->orWhere('modulo', 'ilike', "%{$search}%")
                    ->orWhere('descripcion', 'ilike', "%{$search}%")
                    ->orWhere('resultado', 'ilike', "%{$search}%")
                    ->orWhere('ip_address', 'ilike', "%{$search}%")
                    ->orWhere('entidad_id', 'ilike', "%{$search}%")
                    ->orWhereHas('usuario', function ($qu) use ($search) {
                        $qu->where('nombre_usuario', 'ilike', "%{$search}%")
                            ->orWhereHas('persona', function ($qp) use ($search) {
                                $qp->where('nombre', 'ilike', "%{$search}%")
                                    ->orWhere('apellido1', 'ilike', "%{$search}%");
                            });
                    });
            });
        }

        $logs = $query->paginate(20)->withQueryString();

        // Métricas de auditoría
        $totalLogs = AuditoriaLog::count();
        $logsHoy = AuditoriaLog::whereDate('created_at', today())->count();
        $totalRechazos = AuditoriaLog::where('resultado', 'RECHAZADO')->count();
        $usuariosAuditados = AuditoriaLog::distinct('id_usuario')->count('id_usuario');

        // Catálogos para filtros
        $usuarios = Usuario::with('persona')->orderBy('nombre_usuario')->get();
        $modulos = AuditoriaLog::distinct()->pluck('modulo')->filter()->sort()->values();
        $acciones = AuditoriaLog::distinct()->pluck('accion')->filter()->sort()->values();
        $resultados = AuditoriaLog::distinct()->pluck('resultado')->filter()->sort()->values();

        return view('usuarios.auditoria', [
            'logs' => $logs,
            'totalLogs' => $totalLogs,
            'logsHoy' => $logsHoy,
            'totalAnulaciones' => $totalRechazos,
            'usuariosAuditados' => $usuariosAuditados,
            'usuarios' => $usuarios,
            'modulos' => $modulos,
            'acciones' => $acciones,
            'resultados' => $resultados,
            'filters' => [
                'modulo' => $modulo,
                'accion' => $accion,
                'resultado' => $resultado,
                'id_usuario' => $idUsuario,
                'fecha' => $fecha,
                'fecha_desde' => $fechaDesde ?? null,
                'fecha_hasta' => $fechaHasta ?? null,
                'search' => $search,
            ],
        ]);
    }
}
