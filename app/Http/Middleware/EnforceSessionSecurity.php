<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnforceSessionSecurity
{
    /**
     * Handle an incoming request.
     *
     * Enforces:
     * 1. Inactivity timeout (30 minutes max idle).
     * 2. Automatic session ID rotation (token rotation) every 15 minutes.
     * 3. Protection against session fixation and stale session hijacking.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $now = time();
            $maxIdleSeconds = 30 * 60; // 30 minutos de inactividad
            $rotationSeconds = 15 * 60; // Rotación automática de ID de sesión cada 15 min

            $lastActivity = $request->session()->get('auth_last_activity');
            $sessionCreated = $request->session()->get('auth_session_created_at');

            // 1. Verificar expiración por inactividad
            if ($lastActivity && ($now - $lastActivity > $maxIdleSeconds)) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'lockout' => 'Tu sesión ha expirado por inactividad (30 minutos) por motivos de seguridad. Por favor inicia sesión nuevamente.',
                ]);
            }

            // 2. Rotación periódica de ID de sesión (Refresh Token / Session Rotation)
            if ($sessionCreated && ($now - $sessionCreated > $rotationSeconds)) {
                $request->session()->migrate(true);
                $request->session()->put('auth_session_created_at', $now);
            } elseif (! $sessionCreated) {
                $request->session()->put('auth_session_created_at', $now);
            }

            // Actualizar marca de última actividad
            $request->session()->put('auth_last_activity', $now);
        }

        return $next($request);
    }
}
