<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request and attach essential security headers.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        // SEC: Prevenir Clickjacking (CWE-1021)
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // SEC: Prevenir MIME Sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // SEC: Control de cabecera Referer
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // SEC: Restricción de permisos y APIs del navegador
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // SEC: Content Security Policy (CWE-79 / XSS mitigation)
        $response->headers->set('Content-Security-Policy', "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline'; font-src 'self' data:; img-src 'self' data: blob:; connect-src 'self' ws:;");

        return $response;
    }
}
