<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Configurar Contraseña — La Petdiatría</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full bg-slate-50/50 flex min-h-full items-center justify-center p-4 sm:p-6 lg:p-8 font-sans antialiased text-slate-900" x-data="{ showPassword: false, showConfirmPassword: false }">

    <div class="w-full max-w-md space-y-6">

        <!-- Logo e Identidad -->
        <div class="text-center">
            <div class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-emerald-600 text-white shadow-soft ring-1 ring-emerald-500/30">
                <i data-lucide="shield-check" class="size-6"></i>
            </div>
            <h1 class="mt-4 text-2xl font-bold tracking-tight text-slate-900">La Petdiatría</h1>
            <p class="mt-1 text-sm text-slate-500">Sistema de Gestión Clínica Veterinaria</p>
        </div>

        <!-- Tarjeta de Configuración de Contraseña -->
        <div class="rounded-2xl border border-slate-200/80 bg-white p-6 sm:p-8 shadow-card">

            <!-- Mensaje de éxito de verificación -->
            <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50/80 p-4">
                <div class="flex items-start gap-3">
                    <div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700">
                        <i data-lucide="check-circle" class="size-5"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-emerald-900">¡Correo verificado exitosamente!</h3>
                        <p class="mt-0.5 text-xs text-emerald-800 leading-relaxed">
                            Tu cuenta <strong>@{{ $username }}</strong> ha sido activada. Ahora configura tu contraseña personal para iniciar sesión.
                        </p>
                    </div>
                </div>
            </div>

            <div class="mb-5">
                <div class="inline-flex size-9 items-center justify-center rounded-xl bg-primary-50 text-primary-600 mb-2.5">
                    <i data-lucide="key-round" class="size-5"></i>
                </div>
                <h2 class="text-lg font-bold text-slate-900 tracking-tight">Configura tu Contraseña</h2>
                <p class="mt-1 text-sm text-slate-500 leading-relaxed">
                    Elige una contraseña segura que usarás para acceder al sistema. La contraseña temporal asignada por el administrador será reemplazada.
                </p>
            </div>

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

            <form action="{{ route('verification.setup-password') }}" method="POST" class="space-y-5">
                @csrf
                <input type="hidden" name="setup_token" value="{{ $setupToken }}">

                <!-- Nueva Contraseña -->
                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Nueva Contraseña
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                            <i data-lucide="lock" class="size-4 text-slate-400"></i>
                        </div>
                        <input
                            :type="showPassword ? 'text' : 'password'"
                            id="password"
                            name="password"
                            placeholder="Mínimo 6 caracteres"
                            required
                            autofocus
                            class="block w-full rounded-xl border-0 py-2.5 pl-10 pr-12 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-primary-500 @error('password') ring-rose-300 @enderror"
                        />
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-600 transition-colors"
                        >
                            <i :data-lucide="showPassword ? 'eye-off' : 'eye'" class="size-4"></i>
                        </button>
                    </div>
                </div>

                <!-- Confirmar Contraseña -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Confirmar Contraseña
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                            <i data-lucide="lock-keyhole" class="size-4 text-slate-400"></i>
                        </div>
                        <input
                            :type="showConfirmPassword ? 'text' : 'password'"
                            id="password_confirmation"
                            name="password_confirmation"
                            placeholder="Repite la nueva contraseña"
                            required
                            class="block w-full rounded-xl border-0 py-2.5 pl-10 pr-12 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-primary-500"
                        />
                        <button
                            type="button"
                            @click="showConfirmPassword = !showConfirmPassword"
                            class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-600 transition-colors"
                        >
                            <i :data-lucide="showConfirmPassword ? 'eye-off' : 'eye'" class="size-4"></i>
                        </button>
                    </div>
                </div>

                <!-- Botón de Envío -->
                <button
                    type="submit"
                    class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 transition-colors mt-2"
                >
                    <i data-lucide="check" class="size-4"></i>
                    <span>Guardar Contraseña e Iniciar Sesión</span>
                </button>
            </form>

            <div class="mt-6 pt-5 border-t border-slate-100 text-center">
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
            © {{ date('Y') }} La Petdiatría · Gestión Médica Veterinaria Segura.
        </p>

    </div>

</body>
</html>
