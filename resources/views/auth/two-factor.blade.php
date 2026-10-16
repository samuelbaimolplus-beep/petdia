<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verificación de Dos Factores (2FA) — {{ \App\Models\Configuracion::get('clinica_nombre', 'La Petdiatría') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full bg-slate-50/50 flex min-h-full items-center justify-center p-4 sm:p-6 lg:p-8 font-sans antialiased text-slate-900" x-data="{ useRecovery: false }">

    @php
        $clinicaNombre = \App\Models\Configuracion::get('clinica_nombre', 'La Petdiatría');
        $clinicaLogo = \App\Models\Configuracion::get('clinica_logo');
    @endphp

    <div class="w-full max-w-md space-y-6">

        <!-- Logo e Identidad -->
        <div class="text-center">
            @if($clinicaLogo && file_exists(public_path('storage/' . $clinicaLogo)))
                <img src="{{ asset('storage/' . $clinicaLogo) }}" alt="{{ $clinicaNombre }}" class="mx-auto h-14 w-auto object-contain">
            @else
                <div class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-primary-600 text-white shadow-soft ring-1 ring-primary-500/30">
                    <i data-lucide="shield-check" class="size-6"></i>
                </div>
            @endif
            <h1 class="mt-4 text-2xl font-bold tracking-tight text-slate-900">Autenticación en Dos Pasos</h1>
            <p class="mt-1 text-sm text-slate-500">Protección reforzada de cuenta administrativa</p>
        </div>

        <!-- Tarjeta de Verificación -->
        <div class="rounded-2xl border border-slate-200/80 bg-white p-6 sm:p-8 shadow-card">
            
            <div class="mb-6">
                <h2 class="text-base font-semibold text-slate-900" x-text="useRecovery ? 'Ingresar Código de Recuperación' : 'Código de Autenticación (TOTP)'"></h2>
                <p class="text-xs text-slate-500 mt-0.5" x-text="useRecovery ? 'Ingresa uno de tus códigos de emergencia guardados al configurar el 2FA.' : 'Abre Google Authenticator o tu app 2FA e ingresa el código de 6 dígitos.'"></p>
            </div>

            <!-- Error de Código Inválido -->
            @error('code')
                <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50/80 p-3.5 text-rose-700">
                    <div class="flex items-start gap-2.5">
                        <i data-lucide="circle-alert" class="size-4 shrink-0 text-rose-500 mt-0.5"></i>
                        <p class="text-xs font-semibold">{{ $message }}</p>
                    </div>
                </div>
            @enderror

            <!-- Formulario de 2FA -->
            <form action="{{ route('login.2fa.post') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="code" class="block text-xs font-semibold text-slate-700 mb-1" x-text="useRecovery ? 'Código de Recuperación' : 'Código de 6 dígitos'"></label>
                    <div class="relative">
                        <input
                            id="code"
                            name="code"
                            type="text"
                            :placeholder="useRecovery ? 'XXXX-XXXX' : '000000'"
                            :maxlength="useRecovery ? 15 : 6"
                            autocomplete="one-time-code"
                            inputmode="numeric"
                            required
                            autofocus
                            class="block w-full text-center text-2xl font-mono tracking-widest rounded-lg border-0 py-2.5 px-3 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-200 placeholder:text-slate-300 focus-ring bg-white"
                        />
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <button
                        type="button"
                        @click="useRecovery = !useRecovery"
                        class="text-primary-600 hover:text-primary-700 font-medium hover:underline cursor-pointer"
                        x-text="useRecovery ? '← Usar código de app autenticadora' : '¿No tienes acceso a tu app? Usar código de recuperación'"
                    ></button>
                </div>

                <div class="pt-2 flex flex-col gap-2">
                    <x-button type="submit" variant="primary" class="w-full py-2.5 text-sm shadow-sm" icon="check">
                        Verificar y Continuar
                    </x-button>

                    <a href="{{ route('login') }}" class="block text-center text-xs text-slate-400 hover:text-slate-600 py-1 transition-colors">
                        Cancelar y volver al inicio
                    </a>
                </div>
            </form>

        </div>

    </div>

</body>
</html>
