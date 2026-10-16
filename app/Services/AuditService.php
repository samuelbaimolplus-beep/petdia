<?php

namespace App\Services;

use App\Models\AuditoriaLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuditService
{
    /**
     * Registra un evento en la bitácora inmutable de auditoría.
     *
     * @param  string  $accion  Ej: LOGIN, ANULAR_COBRO, CAMBIO_PRECIO, CANCELAR_CITA, CAMBIO_ROL
     * @param  string  $modulo  Ej: CAJA, CITAS, HISTORIAL, USUARIOS, INVENTARIO, CIRUGIAS
     * @param  array<string, mixed>  $detalles  Datos adicionales relevantes
     * @param  Model|null  $entidad  Modelo afectado
     * @param  int|null  $idUsuario  ID del usuario (opcional, defaults a Auth::id())
     */
    public static function log(
        string $accion,
        string $modulo,
        array $detalles = [],
        Model|string|int|null $entidad = null,
        ?int $idUsuario = null,
        string $resultado = 'EXITO',
        ?string $descripcion = null
    ): ?AuditoriaLog {
        try {
            $user = Auth::user();
            $userId = $idUsuario ?? $user?->getAuthIdentifier();

            $desc = $descripcion ?? ($detalles['descripcion'] ?? "Operación {$accion} ejecutada en el módulo {$modulo}");

            $entidadTipo = ($entidad instanceof Model) ? get_class($entidad) : null;
            $entidadId = ($entidad instanceof Model)
                ? (string) $entidad->getKey()
                : ($entidad !== null ? (string) $entidad : ($detalles['id_registro'] ?? null));

            return AuditoriaLog::create([
                'id_usuario' => $userId,
                'accion' => strtoupper($accion),
                'modulo' => strtoupper($modulo),
                'entidad_tipo' => $entidadTipo,
                'entidad_id' => $entidadId,
                'resultado' => strtoupper($resultado),
                'descripcion' => substr((string) $desc, 0, 500),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => substr((string) request()->userAgent(), 0, 500),
                'detalles' => $detalles,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Un fallo en auditoría nunca debe romper la transacción principal
            Log::error('Error registrando auditoría: '.$e->getMessage(), [
                'accion' => $accion,
                'modulo' => $modulo,
            ]);

            return null;
        }
    }
}
