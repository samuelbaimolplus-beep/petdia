<?php

namespace App\Http\Middleware;

use App\Models\Usuario;
use App\Services\AuditService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModuleAccess
{
    /**
     * Handle an incoming request.
     *
     * Enforces STRICT SERVER-SIDE authorization:
     * Eliminates any client-side "isAdmin" bypass.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $module): Response
    {
        /** @var Usuario|null $user */
        $user = $request->user();

        if (! $user) {
            abort(401, 'No autenticado.');
        }

        // Si el usuario está inactivo en el sistema
        if ($user->estado !== 'ACTIVO') {
            abort(403, 'Tu cuenta de usuario se encuentra inactiva. Comunícate con un administrador.');
        }

        // Verificación estricta en servidor del módulo solicitado
        if (! $user->canAccessModule($module)) {
            AuditService::log(
                'ACCESO_DENEGADO',
                strtoupper($module),
                [
                    'ruta' => $request->path(),
                    'metodo' => $request->method(),
                    'modulo' => $module,
                ],
                null,
                $user->id_usuario,
                'RECHAZADO',
                "Intento no autorizado de acceso al módulo [{$module}] por el usuario '{$user->nombre_usuario}'"
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Acceso denegado',
                    'message' => "El rol actual no posee permisos en el servidor para acceder al recurso [{$module}].",
                ], 403);
            }

            abort(403, "Acceso restringido: Se requieren permisos específicos de servidor para acceder al módulo '{$module}'.");
        }

        return $next($request);
    }
}
