<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar Sesión — {{ \App\Models\Configuracion::get('clinica_nombre', 'La Petdiatría') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full bg-slate-50/50 flex min-h-full items-center justify-center p-4 sm:p-6 lg:p-8 font-sans antialiased text-slate-900" x-data="{ username: @js(old('nombre_usuario', '')), password: '', showPassword: false }">

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
                    <i data-lucide="paw-print" class="size-6"></i>
                </div>
            @endif
            <h1 class="mt-4 text-2xl font-bold tracking-tight text-slate-900">{{ $clinicaNombre }}</h1>
            <p class="mt-1 text-sm text-slate-500">Sistema de Gestión Clínica Veterinaria</p>
        </div>

        <!-- Tarjeta de Login -->
        <div class="rounded-2xl border border-slate-200/80 bg-white p-6 sm:p-8 shadow-card">
            
            <div class="mb-6">
                <h2 class="text-base font-semibold text-slate-900">Acceder al sistema</h2>
                <p class="text-xs text-slate-500 mt-0.5">Ingresa tus credenciales profesionales para continuar.</p>
            </div>

            <!-- Mensaje de Estado / Éxito (ej. Contraseña restablecida) -->
            @if(session('status') || session('success'))
                <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50/80 p-4 text-emerald-800">
                    <div class="flex items-start gap-3">
                        <i data-lucide="check-circle" class="size-5 shrink-0 text-emerald-600 mt-0.5"></i>
                        <div class="text-xs">
                            <p class="font-semibold">{{ session('status') ?? session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Alerta de Bloqueo por 5 Intentos Fallidos -->
            @error('lockout')
                <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50/80 p-4 text-rose-800">
                    <div class="flex items-start gap-3">
                        <i data-lucide="shield-alert" class="size-5 shrink-0 text-rose-600 mt-0.5"></i>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-rose-700">Acceso bloqueado</p>
                            <p class="mt-0.5 text-xs text-rose-600 leading-relaxed">{{ $message }}</p>
                        </div>
                    </div>
                </div>
            @enderror

            <!-- Alerta de Credenciales Inválidas -->
            @error('credenciales')
                <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50/70 p-3.5 text-rose-700">
                    <div class="flex items-start gap-2.5">
                        <i data-lucide="circle-alert" class="size-4 shrink-0 text-rose-500 mt-0.5"></i>
                        <div class="text-xs">
                            <p class="font-semibold">{{ $message }}</p>
                            @error('attempts')
                                <p class="mt-0.5 text-rose-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            @enderror

            <!-- Reenvío y Activación de Cuenta si está pendiente -->
            @if($errors->has('unverified'))
                <div class="mb-5 rounded-xl border border-amber-200 bg-amber-50/80 p-3.5 text-amber-800">
                    <p class="text-xs font-semibold mb-2">Tu cuenta aún no está activada.</p>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('activacion.form', ['nombre_usuario' => old('unverified_username', old('nombre_usuario'))]) }}" class="text-xs font-bold text-white bg-primary-600 hover:bg-primary-700 px-3 py-1.5 rounded-lg transition-colors inline-flex items-center gap-1">
                            <i data-lucide="key" class="size-3.5"></i>
                            Ingresar Código OTP
                        </a>
                        <form action="{{ route('activacion.resend') }}" method="POST" class="inline">
                            @csrf
                            <input type="hidden" name="nombre_usuario" value="{{ old('unverified_username', old('nombre_usuario')) }}">
                            <button type="submit" class="text-xs font-bold text-amber-900 bg-amber-200 hover:bg-amber-300 px-3 py-1.5 rounded-lg transition-colors cursor-pointer">
                                Reenviar OTP
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Formulario de Login -->
            <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Desafío Anti-Fuerza Bruta tras 3 intentos -->
                @if($errors->has('show_captcha') || $errors->has('captcha_answer'))
                    <div class="rounded-xl border border-amber-200 bg-amber-50/60 p-3.5 space-y-2">
                        <div class="flex items-center gap-2 text-xs font-semibold text-amber-900">
                            <i data-lucide="shield-question" class="size-4 text-amber-600"></i>
                            <span>Desafío Anti-Fuerza Bruta</span>
                        </div>
                        <p class="text-[11px] text-amber-700">Por seguridad tras varios intentos consecutivos, resuelve la siguiente suma:</p>
                        <div class="flex items-center gap-3">
                            <span class="px-3 py-1.5 rounded-lg bg-white border border-amber-300 text-sm font-bold text-slate-800 select-none">
                                {{ $captchaQuestion ?? '3 + 4' }} = ?
                            </span>
                            <input
                                type="number"
                                name="captcha_answer"
                                required
                                placeholder="Resultado"
                                class="w-28 rounded-lg border-0 py-1.5 px-3 text-sm text-slate-900 ring-1 ring-inset ring-amber-300 focus-ring bg-white"
                            />
                        </div>
                        @error('captcha_answer')
                            <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <!-- Campo Usuario -->
                <x-field label="Nombre de usuario" :error="$errors->first('nombre_usuario')" required>
                    <div class="relative">
                        <input
                            id="nombre_usuario"
                            name="nombre_usuario"
                            type="text"
                            x-model="username"
                            placeholder="Ej. jperez, mlopez"
                            autocomplete="username"
                            required
                            autofocus
                            class="block w-full rounded-lg border-0 py-2 pl-3.5 pr-10 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus-ring bg-white {{ ($errors->has('nombre_usuario') || $errors->has('credenciales')) ? 'ring-rose-300 focus:ring-rose-400' : 'ring-slate-200 focus:ring-primary-500' }}"
                        />
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
                            <i data-lucide="user" class="size-4"></i>
                        </div>
                    </div>
                </x-field>

                <!-- Campo Contraseña -->
                <x-field label="Contraseña" :error="$errors->first('password')" required>
                    <div class="relative">
                        <input
                            id="password"
                            name="password"
                            :type="showPassword ? 'text' : 'password'"
                            x-model="password"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            required
                            class="block w-full rounded-lg border-0 py-2 pl-3.5 pr-10 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus-ring bg-white"
                        />
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer"
                            tabindex="-1"
                        >
                            <i :data-lucide="showPassword ? 'eye-off' : 'eye'" class="size-4"></i>
                        </button>
                    </div>
                </x-field>

                <!-- Recordarme y Olvidé mi contraseña -->
                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-2 cursor-pointer text-slate-600 select-none">
                        <input
                            type="checkbox"
                            name="remember"
                            class="rounded border-slate-300 text-primary-600 focus:ring-primary-500 size-4"
                        />
                        <span>Recordar sesión</span>
                    </label>
                    <a href="{{ route('password.request') }}" class="font-medium text-primary-600 hover:text-primary-700 hover:underline transition-colors">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>

                <!-- Botón de Envío -->
                <div class="pt-2">
                    <x-button type="submit" variant="primary" class="w-full py-2.5 text-sm shadow-sm" icon="log-in">
                        Iniciar Sesión
                    </x-button>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 text-center">
                    <a href="{{ route('activacion.form') }}" class="text-xs font-semibold text-primary-600 hover:text-primary-700 hover:underline transition-colors inline-flex items-center gap-1">
                        <i data-lucide="key" class="size-3.5"></i>
                        <span>¿Tienes un código de activación? Activar cuenta</span>
                    </a>
                </div>
            </form>

        </div>

        <p class="text-center text-xs text-slate-400">
            © 2026 La Petdiatría · Sistema Clínico Veterinario Integrado
        </p>

    </div>

</body>
</html>
