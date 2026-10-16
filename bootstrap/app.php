<?php

use App\Http\Middleware\CheckModuleAccess;
use App\Http\Middleware\EnforceSessionSecurity;
use App\Http\Middleware\SecurityHeadersMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // SEC: Solo confiar en proxies conocidos (localhost para Nginx local)
        $middleware->trustProxies(at: ['127.0.0.1', '::1']);

        // SEC: Cabeceras de seguridad HTTP y control de sesiones e inactividad
        $middleware->web(append: [
            SecurityHeadersMiddleware::class,
            EnforceSessionSecurity::class,
        ]);

        // SEC: Middleware de autorización estricta en servidor por módulo
        $middleware->alias([
            'module' => CheckModuleAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
