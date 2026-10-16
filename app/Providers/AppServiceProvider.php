<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Límite por defecto de la API: 60 peticiones/minuto por IP o usuario (sin límite en tests automáticos).
        RateLimiter::for('api', function (Request $request) {
            return app()->runningUnitTests()
                ? Limit::none()
                : Limit::perMinute(60)->by($request->user()?->id_usuario ?: $request->ip());
        });

        // Política de contraseñas de alta seguridad (8+ chars, mayúscula, minúscula, número, símbolo y verificación HaveIBeenPwned k-anonymity)
        Password::defaults(function () {
            $rule = Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols();

            // Verificación contra filtraciones públicas (HaveIBeenPwned API k-anonymity)
            return app()->runningUnitTests() ? $rule : $rule->uncompromised();
        });
    }
}
