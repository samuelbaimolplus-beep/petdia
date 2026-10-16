<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recuperar Contraseña — La Petdiatría</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full bg-slate-50/50 flex min-h-full items-center justify-center p-4 sm:p-6 lg:p-8 font-sans antialiased text-slate-900">

    <div class="w-full max-w-md space-y-6">

        <!-- Logo e Identidad -->
        <div class="text-center">
            <div class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-primary-600 text-white shadow-soft ring-1 ring-primary-500/30">
                <i data-lucide="paw-print" class="size-6"></i>
            </div>
            <h1 class="mt-4 text-2xl font-bold tracking-tight text-slate-900">La Petdiatría</h1>
            <p class="mt-1 text-sm text-slate-500">Sistema de Gestión Clínica Veterinaria</p>
        </div>

        <!-- Tarjeta de Recuperación -->
        <div class="rounded-2xl border border-slate-200/80 bg-white p-6 sm:p-8 shadow-card">
            
            <div class="mb-6">
                <h2 class="text-base font-semibold text-slate-900">¿Olvidaste tu contraseña?</h2>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                    Ingresa tu <strong>nombre de usuario</strong> o tu <strong>correo electrónico</strong> vinculado. Te enviaremos un código de seguridad OTP para verificar tu identidad y restablecerla.
                </p>
            </div>

            <!-- Alerta de Errores -->
            @if ($errors->any())
                <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50/70 p-3.5 text-rose-700">
                    <div class="flex items-start gap-2.5">
                        <i data-lucide="circle-alert" class="size-4 shrink-0 text-rose-500 mt-0.5"></i>
                        <div class="text-xs font-medium">
                            {{ $errors->first() }}
                        </div>
                    </div>
                </div>
            @endif

            <!-- Formulario para solicitar código OTP -->
            <form action="{{ route('password.email') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Campo Identificador -->
                <x-field label="Usuario o Correo Electrónico" :error="$errors->first('identificador')" required>
                    <div class="relative">
                        <input
                            id="identificador"
                            name="identificador"
                            type="text"
                            value="{{ old('identificador') }}"
                            placeholder="Ej. jperez o juan@email.com"
                            required
                            autofocus
                            class="block w-full rounded-lg border-0 py-2 pl-3.5 pr-10 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus-ring bg-white {{ $errors->has('identificador') ? 'ring-rose-300 focus:ring-rose-400' : 'ring-slate-200 focus:ring-primary-500' }}"
                        />
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
                            <i data-lucide="mail" class="size-4"></i>
                        </div>
                    </div>
                </x-field>

                <div class="pt-2">
                    <x-button type="submit" variant="primary" class="w-full py-2.5 text-sm shadow-sm" icon="send">
                        Enviar Código de Seguridad
                    </x-button>
                </div>
            </form>

            <div class="mt-6 pt-5 border-t border-slate-100 text-center">
                <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-primary-600 transition-colors">
                    <i data-lucide="arrow-left" class="size-3.5"></i>
                    <span>Volver a Iniciar Sesión</span>
                </a>
            </div>

        </div>

        <p class="text-center text-xs text-slate-400">
            © 2026 La Petdiatría · Sistema Clínico Veterinario Integrado
        </p>

    </div>

</body>
</html>
