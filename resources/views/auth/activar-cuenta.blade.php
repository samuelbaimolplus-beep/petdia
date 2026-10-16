<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Activar Cuenta — La Petdiatría</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full bg-slate-50/50 flex min-h-full items-center justify-center p-4 sm:p-6 lg:p-8 font-sans antialiased text-slate-900" x-data="{ resending: false }">

    <div class="w-full max-w-md space-y-6">

        <!-- Logo e Identidad -->
        <div class="text-center">
            <div class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-primary-600 text-white shadow-soft ring-1 ring-primary-500/30">
                <i data-lucide="shield-check" class="size-6"></i>
            </div>
            <h1 class="mt-4 text-2xl font-bold tracking-tight text-slate-900">La Petdiatría</h1>
            <p class="mt-1 text-sm text-slate-500">Activación de Cuenta de Usuario</p>
        </div>

        <!-- Tarjeta de Activación OTP -->
        <div class="rounded-2xl border border-slate-200/80 bg-white p-6 sm:p-8 shadow-card">

            <div class="mb-6">
                <div class="inline-flex size-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600 mb-2.5">
                    <i data-lucide="key" class="size-5"></i>
                </div>
                <h2 class="text-lg font-bold text-slate-900 tracking-tight">Ingresa tu Código de Activación</h2>
                <p class="mt-1 text-sm text-slate-500 leading-relaxed">
                    Tu cuenta está pendiente de activación. Ingresa tu usuario y el código de 6 dígitos (OTP) que se envió a tu correo electrónico.
                </p>
            </div>

            <!-- Alertas informativas / de advertencia -->
            @if (session('warning'))
                <div class="mb-5 rounded-xl border border-amber-200 bg-amber-50/80 p-3.5 text-xs text-amber-900">
                    <div class="flex items-start gap-2.5">
                        <i data-lucide="info" class="size-4 shrink-0 text-amber-600 mt-0.5"></i>
                        <p class="font-medium">{{ session('warning') }}</p>
                    </div>
                </div>
            @endif

            @if (session('status'))
                <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50/80 p-3.5 text-xs text-emerald-900">
                    <div class="flex items-start gap-2.5">
                        <i data-lucide="check-circle" class="size-4 shrink-0 text-emerald-600 mt-0.5"></i>
                        <p class="font-medium">{{ session('status') }}</p>
                    </div>
                </div>
            @endif

            <!-- Errores de Validación -->
            @if ($errors->any())
                <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50/80 p-3.5 text-xs text-rose-800">
                    <div class="flex items-start gap-2.5">
                        <i data-lucide="alert-triangle" class="size-4 shrink-0 text-rose-600 mt-0.5"></i>
                        <div>
                            @foreach ($errors->all() as $error)
                                <p class="font-semibold">{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Formulario de Activación -->
            <form action="{{ route('activacion.verify') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Campo de Nombre de Usuario -->
                <div>
                    <label for="nombre_usuario" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Nombre de Usuario
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                            <i data-lucide="user" class="size-4 text-slate-400"></i>
                        </div>
                        <input
                            type="text"
                            id="nombre_usuario"
                            name="nombre_usuario"
                            value="{{ old('nombre_usuario', session('nombre_usuario')) }}"
                            placeholder="Tu nombre de usuario"
                            required
                            class="block w-full rounded-xl border-0 py-2.5 pl-10 pr-3.5 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-primary-500 @error('nombre_usuario') ring-rose-300 @enderror"
                        />
                    </div>
                </div>

                <!-- Campo de Código OTP -->
                <div>
                    <label for="codigo_otp" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Código de Activación OTP (6 dígitos)
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                            <i data-lucide="hash" class="size-4 text-slate-400"></i>
                        </div>
                        <input
                            type="text"
                            id="codigo_otp"
                            name="codigo_otp"
                            maxlength="6"
                            placeholder="000000"
                            required
                            autofocus
                            autocomplete="one-time-code"
                            inputmode="numeric"
                            pattern="[0-9]{6}"
                            class="block w-full text-center tracking-[0.4em] font-mono font-bold text-lg rounded-xl border-0 py-2.5 pl-10 pr-3.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-200 placeholder:text-slate-300 placeholder:tracking-normal focus:ring-2 focus:ring-inset focus:ring-primary-500 @error('codigo_otp') ring-rose-300 @enderror"
                        />
                    </div>
                    <p class="mt-1 text-xs text-slate-400">Revisa la bandeja de entrada o spam de tu correo electrónico registrado.</p>
                </div>

                <!-- Botón de Envío -->
                <button
                    type="submit"
                    class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 transition-colors mt-2"
                >
                    <i data-lucide="check" class="size-4"></i>
                    <span>Verificar y Activar Cuenta</span>
                </button>
            </form>

            <div class="mt-6 pt-5 border-t border-slate-100 flex flex-col gap-3 text-center">
                <!-- Reenviar Código OTP -->
                <form action="{{ route('activacion.resend') }}" method="POST" @submit="resending = true">
                    @csrf
                    <input type="hidden" name="nombre_usuario" :value="document.getElementById('nombre_usuario')?.value || '{{ old('nombre_usuario', session('nombre_usuario')) }}'">
                    <button
                        type="submit"
                        :disabled="resending"
                        class="text-xs font-semibold text-primary-600 hover:text-primary-700 hover:underline inline-flex items-center gap-1.5 transition-colors disabled:opacity-50"
                    >
                        <i data-lucide="mail-redo" class="size-3.5"></i>
                        <span x-text="resending ? 'Enviando nuevo código...' : '¿No recibiste el código o expiró? Reenviar OTP'"></span>
                    </button>
                </form>

                <!-- Volver a Login -->
                <a
                    href="{{ route('login') }}"
                    class="text-xs font-semibold text-slate-500 hover:text-slate-700 transition-colors inline-flex items-center justify-center gap-1"
                >
                    <i data-lucide="arrow-left" class="size-3.5"></i>
                    <span>Volver al inicio de sesión</span>
                </a>
            </div>

        </div>

        <p class="text-center text-xs text-slate-400">
            © {{ date('Y') }} La Petdiatría. Gestión Médica Veterinaria Segura.
        </p>

    </div>

</body>
</html>
