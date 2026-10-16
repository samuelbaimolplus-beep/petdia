<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verificar Código OTP y Restablecer Contraseña — La Petdiatría</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full bg-slate-50/50 flex min-h-full items-center justify-center p-4 sm:p-6 lg:p-8 font-sans antialiased text-slate-900" x-data="{ showPassword: false, showConfirmPassword: false }">

    <div class="w-full max-w-md space-y-6">

        <!-- Logo e Identidad -->
        <div class="text-center">
            <div class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-primary-600 text-white shadow-soft ring-1 ring-primary-500/30">
                <i data-lucide="paw-print" class="size-6"></i>
            </div>
            <h1 class="mt-4 text-2xl font-bold tracking-tight text-slate-900">La Petdiatría</h1>
            <p class="mt-1 text-sm text-slate-500">Sistema de Gestión Clínica Veterinaria</p>
        </div>

        <!-- Tarjeta de Verificación OTP -->
        <div class="rounded-2xl border border-slate-200/80 bg-white p-6 sm:p-8 shadow-card">
            
            <div class="mb-5">
                <div class="inline-flex size-9 items-center justify-center rounded-xl bg-primary-50 text-primary-600 mb-2.5">
                    <i data-lucide="shield-check" class="size-5"></i>
                </div>
                <h2 class="text-base font-semibold text-slate-900">Código de Verificación</h2>
                <p class="text-xs text-slate-500 mt-1">
                    Ingresa el código OTP de 6 dígitos enviado a <span class="font-semibold text-slate-800">{{ $maskedEmail }}</span> y tu nueva contraseña.
                </p>
            </div>

            <!-- Alerta de Notificación / Flash -->
            @if (session('status'))
                <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50/80 p-3.5 text-emerald-800">
                    <div class="flex items-start gap-2.5">
                        <i data-lucide="mail-check" class="size-4 shrink-0 text-emerald-600 mt-0.5"></i>
                        <p class="text-xs font-medium">{{ session('status') }}</p>
                    </div>
                </div>
            @endif

            @if(app()->isLocal() && session('dev_otp'))
                <div class="mb-5 rounded-xl border border-indigo-200 bg-indigo-50/90 p-4 text-indigo-900">
                    <div class="flex items-start gap-2.5">
                        <i data-lucide="info" class="size-4 shrink-0 text-indigo-600 mt-0.5"></i>
                        <div class="text-xs">
                            <p class="font-bold text-indigo-950">Modo Local (Driver de Correo: LOG)</p>
                            <p class="mt-1 text-slate-600 leading-relaxed">
                                Tu <code>.env</code> tiene <code>MAIL_MAILER=log</code>, por lo que el correo se registra en <code>storage/logs/laravel.log</code>.
                            </p>
                            <p class="mt-2 text-indigo-900">
                                Código OTP de prueba generado:
                                <span class="font-mono text-base font-extrabold bg-white px-2 py-0.5 rounded border border-indigo-300 text-primary-700 tracking-wider inline-block ml-1">
                                    {{ session('dev_otp') }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            @endif

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

            <!-- Formulario de Restablecimiento con OTP -->
            <form action="{{ route('password.update') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="email" value="{{ $email }}">

                <!-- Campo Código OTP -->
                <x-field label="Código OTP (6 dígitos)" :error="$errors->first('otp')" required hint="Revisa tu bandeja de entrada o spam">
                    <div class="relative">
                        <input
                            id="otp"
                            name="otp"
                            type="text"
                            inputmode="numeric"
                            pattern="[0-9]{6}"
                            maxlength="6"
                            placeholder="123456"
                            required
                            autofocus
                            value="{{ old('otp') }}"
                            class="block w-full text-center tracking-[0.4em] font-mono text-xl font-bold rounded-lg border-0 py-2.5 text-primary-700 shadow-sm ring-1 ring-inset ring-slate-200 placeholder:text-slate-300 placeholder:tracking-normal placeholder:font-sans placeholder:text-sm focus-ring bg-slate-50/50 {{ $errors->has('otp') ? 'ring-rose-300 focus:ring-rose-400' : 'ring-slate-200 focus:ring-primary-500' }}"
                        />
                    </div>
                </x-field>

                <!-- Campo Nueva Contraseña -->
                <x-field label="Nueva Contraseña" :error="$errors->first('password')" required hint="Mínimo 8 caracteres">
                    <div class="relative">
                        <input
                            id="password"
                            name="password"
                            :type="showPassword ? 'text' : 'password'"
                            placeholder="••••••••"
                            autocomplete="new-password"
                            required
                            class="block w-full rounded-lg border-0 py-2 pl-3.5 pr-10 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus-ring bg-white {{ $errors->has('password') ? 'ring-rose-300 focus:ring-rose-400' : 'ring-slate-200 focus:ring-primary-500' }}"
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

                <!-- Campo Confirmar Nueva Contraseña -->
                <x-field label="Confirmar Nueva Contraseña" :error="$errors->first('password_confirmation')" required>
                    <div class="relative">
                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            :type="showConfirmPassword ? 'text' : 'password'"
                            placeholder="••••••••"
                            autocomplete="new-password"
                            required
                            class="block w-full rounded-lg border-0 py-2 pl-3.5 pr-10 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus-ring bg-white"
                        />
                        <button
                            type="button"
                            @click="showConfirmPassword = !showConfirmPassword"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer"
                            tabindex="-1"
                        >
                            <i :data-lucide="showConfirmPassword ? 'eye-off' : 'eye'" class="size-4"></i>
                        </button>
                    </div>
                </x-field>

                <div class="pt-2">
                    <x-button type="submit" variant="primary" class="w-full py-2.5 text-sm shadow-sm" icon="key">
                        Restablecer Contraseña
                    </x-button>
                </div>
            </form>

            <!-- Reenviar OTP -->
            <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500">¿No recibiste el código?</span>
                <form action="{{ route('password.otp.resend') }}" method="POST">
                    @csrf
                    <input type="hidden" name="email" value="{{ $email }}">
                    <button type="submit" class="font-semibold text-primary-600 hover:text-primary-700 hover:underline">
                        Reenviar código OTP
                    </button>
                </form>
            </div>

            <div class="mt-4 text-center">
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
