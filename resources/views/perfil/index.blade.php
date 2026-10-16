@extends('layouts.app')

@section('title', 'Mi Cuenta y Seguridad — La Petdiatría')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-100 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                <span>Usuario</span>
                <i data-lucide="chevron-right" class="size-4 text-slate-400"></i>
                <span class="text-slate-800 font-medium">Mi Cuenta & Seguridad</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Perfil y Seguridad de la Cuenta</h1>
            <p class="text-sm text-slate-500">Gestiona tus datos de acceso, correo enlazado para códigos OTP y cambio de contraseña.</p>
        </div>
    </div>

    <!-- Alertas Flash -->
    @if(session('success_perfil'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50/80 p-4 text-emerald-800 flex items-start gap-3">
            <i data-lucide="check-circle" class="size-5 shrink-0 text-emerald-600 mt-0.5"></i>
            <p class="text-sm font-medium">{{ session('success_perfil') }}</p>
        </div>
    @endif

    @if(session('success_password'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50/80 p-4 text-emerald-800 flex items-start gap-3">
            <i data-lucide="check-circle" class="size-5 shrink-0 text-emerald-600 mt-0.5"></i>
            <p class="text-sm font-medium">{{ session('success_password') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Columna Izquierda: Tarjeta de Identidad y Rol -->
        <div class="space-y-6 lg:col-span-1">
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-xs text-center">
                <div class="mx-auto flex size-20 items-center justify-center rounded-2xl bg-primary-100 text-primary-700 font-bold text-2xl shadow-soft ring-4 ring-primary-50">
                    {{ strtoupper(substr($usuario->nombre_usuario, 0, 2)) }}
                </div>
                <h2 class="mt-4 text-lg font-bold text-slate-900">{{ $persona?->nombre }} {{ $persona?->apellido1 }} {{ $persona?->apellido2 }}</h2>
                <p class="text-xs font-mono text-slate-500">@<span>{{ $usuario->nombre_usuario }}</span></p>

                <div class="mt-3 flex flex-wrap justify-center gap-1.5">
                    @foreach($usuario->roles as $rol)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary-50 text-primary-700 border border-primary-200/50">
                            {{ $rol->nombre }}
                        </span>
                    @endforeach
                </div>

                @if($profesional)
                    <div class="mt-6 pt-5 border-t border-slate-100 text-left space-y-3">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-400">Especialidad:</span>
                            <span class="font-semibold text-slate-800">{{ $profesional->especialidad }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-400">Registro Prof.:</span>
                            <span class="font-mono font-medium text-slate-700">{{ $profesional->registro_profesional ?: 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-400">Estado Profesional:</span>
                            <span class="inline-flex items-center gap-1 text-emerald-600 font-semibold">
                                <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                {{ $profesional->estado }}
                            </span>
                        </div>
                    </div>
                @endif

                <div class="mt-6 pt-5 border-t border-slate-100 text-left space-y-2 text-xs text-slate-500">
                    <p class="flex items-center gap-2">
                        <i data-lucide="shield" class="size-4 text-slate-400"></i>
                        <span>Estado de Cuenta: <strong class="text-slate-800">{{ $usuario->estado }}</strong></span>
                    </p>
                    <p class="flex items-center gap-2">
                        <i data-lucide="clock" class="size-4 text-slate-400"></i>
                        <span>Último Acceso: <strong class="text-slate-800">{{ $usuario->ultimo_acceso ? \Carbon\Carbon::parse($usuario->ultimo_acceso)->format('d/m/Y H:i') : 'Primer ingreso' }}</strong></span>
                    </p>
                </div>
            </div>

            <!-- Caja Informativa de Seguridad OTP -->
            <div class="bg-indigo-50/70 border border-indigo-100 rounded-2xl p-5 text-indigo-950">
                <div class="flex items-start gap-3">
                    <div class="size-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center shrink-0 mt-0.5">
                        <i data-lucide="shield-alert" class="size-4"></i>
                    </div>
                    <div class="text-xs space-y-1">
                        <h3 class="font-bold text-indigo-900">Enlace de Seguridad OTP</h3>
                        <p class="text-indigo-700 leading-relaxed">
                            Mantén siempre actualizado tu correo electrónico personal o profesional. Si alguna vez olvidas tu clave, el código OTP de verificación se enviará a esa dirección.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Formularios de Edición y Cambio de Contraseña -->
        <div class="space-y-6 lg:col-span-2">

            <!-- Formulario 1: Correo Electrónico Enlazado y Datos de Contacto -->
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-xs">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Correo Electrónico y Contacto</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Dirección enlazada para autenticación de seguridad y códigos OTP.</p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <i data-lucide="check-check" class="size-3.5"></i>
                        Enlazado a OTP
                    </span>
                </div>

                <form action="{{ route('perfil.update') }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-field label="Correo Electrónico (Para recuperación OTP)" :error="$errors->first('email')" required hint="A este correo llegarán los códigos OTP">
                            <div class="relative">
                                <input
                                    type="email"
                                    name="email"
                                    value="{{ old('email', $persona?->email) }}"
                                    required
                                    placeholder="profesional@lapetdiatria.com"
                                    class="block w-full rounded-lg border-0 py-2 pl-3.5 pr-10 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus-ring bg-white {{ $errors->has('email') ? 'ring-rose-300 focus:ring-rose-400' : 'ring-slate-200 focus:ring-primary-500' }}"
                                />
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
                                    <i data-lucide="mail" class="size-4"></i>
                                </div>
                            </div>
                        </x-field>

                        <x-field label="Teléfono de Contacto" :error="$errors->first('telefono')">
                            <div class="relative">
                                <input
                                    type="text"
                                    name="telefono"
                                    value="{{ old('telefono', $persona?->telefono) }}"
                                    placeholder="Ej. +595 981 123456"
                                    class="block w-full rounded-lg border-0 py-2 pl-3.5 pr-10 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus-ring bg-white"
                                />
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
                                    <i data-lucide="phone" class="size-4"></i>
                                </div>
                            </div>
                        </x-field>
                    </div>

                    <div class="flex justify-end pt-2">
                        <x-button type="submit" variant="secondary" icon="save">
                            Guardar Datos de Contacto
                        </x-button>
                    </div>
                </form>
            </div>

            <!-- Formulario 2: Cambiar Contraseña -->
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-xs" x-data="{ showCurrent: false, showNew: false, showConfirm: false }">
                <div class="border-b border-slate-100 pb-4 mb-5">
                    <h2 class="text-base font-bold text-slate-900">Cambiar Contraseña</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Actualiza tu contraseña periódicamente para mantener tu cuenta protegida.</p>
                </div>

                <form action="{{ route('perfil.password') }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <!-- Contraseña Actual -->
                    <x-field label="Contraseña Actual" :error="$errors->first('current_password')" required>
                        <div class="relative">
                            <input
                                :type="showCurrent ? 'text' : 'password'"
                                name="current_password"
                                required
                                placeholder="Ingresa tu contraseña actual"
                                class="block w-full rounded-lg border-0 py-2 pl-3.5 pr-10 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus-ring bg-white {{ $errors->has('current_password') ? 'ring-rose-300 focus:ring-rose-400' : 'ring-slate-200 focus:ring-primary-500' }}"
                            />
                            <button
                                type="button"
                                @click="showCurrent = !showCurrent"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer"
                                tabindex="-1"
                            >
                                <i :data-lucide="showCurrent ? 'eye-off' : 'eye'" class="size-4"></i>
                            </button>
                        </div>
                    </x-field>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Nueva Contraseña -->
                        <x-field label="Nueva Contraseña" :error="$errors->first('password')" required hint="Mínimo 8 caracteres">
                            <div class="relative">
                                <input
                                    :type="showNew ? 'text' : 'password'"
                                    name="password"
                                    required
                                    placeholder="••••••••"
                                    class="block w-full rounded-lg border-0 py-2 pl-3.5 pr-10 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus-ring bg-white {{ $errors->has('password') ? 'ring-rose-300 focus:ring-rose-400' : 'ring-slate-200 focus:ring-primary-500' }}"
                                />
                                <button
                                    type="button"
                                    @click="showNew = !showNew"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer"
                                    tabindex="-1"
                                >
                                    <i :data-lucide="showNew ? 'eye-off' : 'eye'" class="size-4"></i>
                                </button>
                            </div>
                        </x-field>

                        <!-- Confirmar Nueva Contraseña -->
                        <x-field label="Confirmar Nueva Contraseña" :error="$errors->first('password_confirmation')" required>
                            <div class="relative">
                                <input
                                    :type="showConfirmPassword ? 'text' : 'password'"
                                    name="password_confirmation"
                                    required
                                    placeholder="••••••••"
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
                    </div>

                    <div class="flex justify-end pt-2">
                        <x-button type="submit" variant="primary" icon="key">
                            Actualizar Contraseña
                        </x-button>
                    </div>
                </form>
            </div>

            <!-- Formulario 3: Autenticación de Dos Factores (2FA TOTP) -->
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-xs" x-data="{ confirmingDisable: false }">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Autenticación de Dos Factores (2FA)</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Compatible con Google Authenticator, Microsoft Authenticator y 1Password.</p>
                    </div>
                    @if($usuario->hasTwoFactorEnabled())
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <i data-lucide="shield-check" class="size-3.5"></i>
                            2FA Activado
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                            <i data-lucide="shield-alert" class="size-3.5"></i>
                            No configurado
                        </span>
                    @endif
                </div>

                @if(session('success_2fa'))
                    <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50/80 p-4 text-emerald-800 flex items-start gap-3">
                        <i data-lucide="check-circle" class="size-5 shrink-0 text-emerald-600 mt-0.5"></i>
                        <div class="text-xs">
                            <p class="font-bold">{{ session('success_2fa') }}</p>
                            @if(session('recovery_codes'))
                                <p class="mt-1 text-slate-700 font-medium">Guarda estos códigos de recuperación en un lugar seguro. Si pierdes tu teléfono, los necesitarás para acceder:</p>
                                <div class="mt-3 grid grid-cols-2 sm:grid-cols-4 gap-2 p-3 bg-white border border-slate-200 rounded-xl font-mono text-xs text-slate-900 text-center font-bold">
                                    @foreach(session('recovery_codes') as $code)
                                        <div class="p-1.5 bg-slate-50 rounded border border-slate-100">{{ $code }}</div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                @error('two_factor')
                    <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50/80 p-3 text-xs text-rose-700 font-medium">
                        {{ $message }}
                    </div>
                @enderror

                @if($usuario->hasTwoFactorEnabled())
                    <div class="space-y-4">
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Tu cuenta se encuentra blindada con autenticación TOTP de dos pasos. Al iniciar sesión se te solicitará un código de 6 dígitos emitido por tu aplicación autenticadora.
                        </p>

                        <div class="pt-2">
                            <button
                                type="button"
                                @click="confirmingDisable = !confirmingDisable"
                                class="text-xs font-semibold text-rose-600 hover:text-rose-700 hover:underline cursor-pointer"
                            >
                                Desactivar autenticación de dos factores
                            </button>

                            <div x-show="confirmingDisable" x-cloak class="mt-4 p-4 rounded-xl border border-rose-200 bg-rose-50/50 space-y-3">
                                <p class="text-xs text-rose-800 font-medium">Ingresa tu contraseña actual para confirmar la desactivación del 2FA:</p>
                                <form action="{{ route('perfil.2fa.disable') }}" method="POST" class="flex flex-col sm:flex-row gap-2">
                                    @csrf
                                    <input
                                        type="password"
                                        name="current_password"
                                        required
                                        placeholder="Tu contraseña actual"
                                        class="rounded-lg border-0 py-1.5 px-3 text-xs text-slate-900 ring-1 ring-inset ring-rose-300 focus-ring bg-white"
                                    />
                                    <x-button type="submit" variant="danger" class="text-xs py-1.5">
                                        Confirmar Desactivación
                                    </x-button>
                                </form>
                                @error('current_password_2fa')
                                    <p class="text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                @else
                    <div class="space-y-4">
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Para activar 2FA, escanea el siguiente código QR con <strong>Google Authenticator</strong> o ingresa la clave manual. Luego escribe el código de 6 dígitos que genera la app para confirmar.
                        </p>

                        <div class="flex flex-col sm:flex-row items-center gap-6 p-4 rounded-xl bg-slate-50 border border-slate-200/80">
                            @if($qrCodeUrl)
                                <img src="{{ $qrCodeUrl }}" alt="Código QR 2FA" class="size-36 rounded-lg border border-slate-200 bg-white p-1 shadow-xs shrink-0">
                            @endif
                            <div class="space-y-2 text-xs text-slate-700">
                                <p class="font-semibold text-slate-900">Paso 1: Escanea el código o escribe la clave secreta</p>
                                <p class="text-slate-500">Clave secreta manual:</p>
                                <div class="font-mono text-sm font-bold tracking-widest text-primary-700 bg-white px-3 py-1.5 rounded-lg border border-slate-200 select-all inline-block">
                                    {{ $pending2FaSecret }}
                                </div>
                            </div>
                        </div>

                        <form action="{{ route('perfil.2fa.confirm') }}" method="POST" class="space-y-3 pt-2">
                            @csrf
                            <p class="text-xs font-semibold text-slate-900">Paso 2: Confirma el código de 6 dígitos</p>
                            <div class="flex items-center gap-3">
                                <input
                                    type="text"
                                    name="code"
                                    maxlength="6"
                                    required
                                    placeholder="123456"
                                    class="w-36 text-center text-lg font-mono tracking-widest rounded-lg border-0 py-2 px-3 text-slate-900 ring-1 ring-inset ring-slate-200 focus-ring bg-white"
                                />
                                <x-button type="submit" variant="primary" icon="shield-check">
                                    Activar 2FA
                                </x-button>
                            </div>
                            @error('code')
                                <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                            @enderror
                        </form>
                    </div>
                @endif
            </div>

        </div>

    </div>
</div>
@endsection
