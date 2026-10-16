<x-layout title="Gestión de Usuarios y Roles — La Petdiatría">
    @php
        /** @var \App\Models\Usuario $currentUser */
        $currentUser = Auth::user();
        $totalUsuarios = \App\Models\Usuario::count();
        $activosCount = \App\Models\Usuario::where('estado', 'ACTIVO')->count();
        $inactivosCount = \App\Models\Usuario::where('estado', 'INACTIVO')->count();
        $rolesCount = $roles->count();
    @endphp

    <div
        x-data="{
            userModalOpen: {{ $errors->any() ? 'true' : 'false' }},
            isEditing: {{ old('_is_editing') ? 'true' : 'false' }},
            modalTitle: 'Nuevo Usuario del Sistema',
            formAction: @js(old('_form_action', route('usuarios.store'))),
            formMethod: @js(old('_form_method', 'POST')),
            formData: {
                id_usuario: @js(old('id_usuario', '')),
                documento: @js(old('documento', '')),
                nombre: @js(old('nombre', '')),
                apellido1: @js(old('apellido1', '')),
                apellido2: @js(old('apellido2', '')),
                telefono: @js(old('telefono', '')),
                email: @js(old('email', '')),
                direccion: @js(old('direccion', '')),
                nombre_usuario: @js(old('nombre_usuario', '')),
                id_rol: @js(old('id_rol', $roles->first()?->id_rol)),
                estado: @js(old('estado', 'ACTIVO')),
            },
            openCreate() {
                this.isEditing = false;
                this.modalTitle = 'Nuevo Usuario del Sistema';
                this.formAction = '{{ route('usuarios.store') }}';
                this.formMethod = 'POST';
                this.formData = {
                    id_usuario: '',
                    documento: '',
                    nombre: '',
                    apellido1: '',
                    apellido2: '',
                    telefono: '',
                    email: '',
                    direccion: '',
                    nombre_usuario: '',
                    id_rol: '{{ $roles->first()?->id_rol }}',
                    estado: 'ACTIVO'
                };
                this.userModalOpen = true;
            },
            openEdit(user) {
                this.isEditing = true;
                this.modalTitle = 'Editar Usuario: @' + user.nombre_usuario;
                this.formAction = '/usuarios/' + user.id_usuario;
                this.formMethod = 'PUT';
                this.formData = {
                    id_usuario: user.id_usuario,
                    documento: user.persona ? user.persona.documento : '',
                    nombre: user.persona ? user.persona.nombre : '',
                    apellido1: user.persona ? user.persona.apellido1 : '',
                    apellido2: user.persona ? (user.persona.apellido2 || '') : '',
                    telefono: user.persona ? (user.persona.telefono || '') : '',
                    email: user.persona ? (user.persona.email || '') : '',
                    direccion: user.persona ? (user.persona.direccion || '') : '',
                    nombre_usuario: user.nombre_usuario,
                    id_rol: user.roles && user.roles.length > 0 ? user.roles[0].id_rol : '{{ $roles->first()?->id_rol }}',
                    estado: user.estado
                };
                this.userModalOpen = true;
            },
            closeModal() {
                this.userModalOpen = false;
            },
            roleModalOpen: false,
            isEditingRole: false,
            roleModalTitle: 'Nuevo Rol Institucional',
            roleFormAction: '{{ route('roles.store') }}',
            roleFormData: {
                id_rol: '',
                nombre: '',
                descripcion: ''
            },
            openCreateRole() {
                this.isEditingRole = false;
                this.roleModalTitle = 'Nuevo Rol Institucional';
                this.roleFormAction = '{{ route('roles.store') }}';
                this.roleFormData = { id_rol: '', nombre: '', descripcion: '' };
                this.roleModalOpen = true;
            },
            openEditRole(role) {
                this.isEditingRole = true;
                this.roleModalTitle = 'Editar Rol: ' + role.nombre;
                this.roleFormAction = '/roles/' + role.id_rol;
                this.roleFormData = {
                    id_rol: role.id_rol,
                    nombre: role.nombre,
                    descripcion: role.descripcion || ''
                };
                this.roleModalOpen = true;
            },
            closeRoleModal() {
                this.roleModalOpen = false;
            },
        }"
        @keydown.escape.window="if(userModalOpen) closeModal(); if(roleModalOpen) closeRoleModal();"
        class="space-y-6"
    >


        <!-- Encabezado con información del módulo y botón de acción principal -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-primary-600">
                    <i data-lucide="shield-check" class="size-4"></i>
                    <span>Administración · Seguridad del Sistema</span>
                </div>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                    Usuarios, Roles y Permisos
                </h1>
                <p class="mt-1 text-sm text-slate-500 max-w-2xl">
                    Gestiona los accesos del personal de la clínica veterinaria, asignación de roles y la matriz de privilegios RBAC.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <x-button variant="primary" @click="openCreate()">
                    <i data-lucide="user-plus" class="size-4 mr-2"></i>
                    <span>Nuevo Usuario</span>
                </x-button>
            </div>
        </div>

        <!-- Fila de métricas rápidas del módulo -->
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-card">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Usuarios</span>
                    <div class="flex size-7 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                        <i data-lucide="users" class="size-4"></i>
                    </div>
                </div>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ $totalUsuarios }}</p>
                <p class="text-xs text-slate-500 mt-0.5">Cuentas creadas</p>
            </div>

            <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-card">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Usuarios Activos</span>
                    <div class="flex size-7 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                        <i data-lucide="user-check" class="size-4"></i>
                    </div>
                </div>
                <p class="mt-2 text-2xl font-bold text-emerald-600">{{ $activosCount }}</p>
                <p class="text-xs text-slate-500 mt-0.5">Con acceso habilitado</p>
            </div>

            <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-card">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Baja Lógica</span>
                    <div class="flex size-7 items-center justify-center rounded-lg bg-rose-50 text-rose-600">
                        <i data-lucide="user-x" class="size-4"></i>
                    </div>
                </div>
                <p class="mt-2 text-2xl font-bold text-slate-700">{{ $inactivosCount }}</p>
                <p class="text-xs text-slate-500 mt-0.5">Desactivados temporalmente</p>
            </div>

            <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-card">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Roles Activos</span>
                    <div class="flex size-7 items-center justify-center rounded-lg bg-violet-50 text-violet-600">
                        <i data-lucide="shield" class="size-4"></i>
                    </div>
                </div>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ $rolesCount }}</p>
                <p class="text-xs text-slate-500 mt-0.5">Perfiles configurados</p>
            </div>
        </div>

        <!-- Contenedor con Tabs: Usuarios vs Roles y Matriz -->
        <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-card" x-data="{ currentTab: @js($activeTab ?? 'usuarios') }">
            <div class="border-b border-slate-200 pb-3">
                <nav class="flex space-x-4 sm:space-x-8" aria-label="Pestañas de usuarios y roles">
                    <button
                        type="button"
                        @click="currentTab = 'usuarios'"
                        class="inline-flex items-center gap-2 border-b-2 py-2 px-1 text-sm font-semibold transition-colors whitespace-nowrap"
                        :class="currentTab === 'usuarios'
                            ? 'border-primary-600 text-primary-600'
                            : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700'"
                    >
                        <i data-lucide="users" class="size-4"></i>
                        <span>Listado de Usuarios</span>
                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">
                            {{ $usuarios->total() }}
                        </span>
                    </button>

                    <button
                        type="button"
                        @click="currentTab = 'roles'"
                        class="inline-flex items-center gap-2 border-b-2 py-2 px-1 text-sm font-semibold transition-colors whitespace-nowrap"
                        :class="currentTab === 'roles'
                            ? 'border-primary-600 text-primary-600'
                            : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700'"
                    >
                        <i data-lucide="shield-check" class="size-4"></i>
                        <span>Roles y Matriz de Permisos</span>
                        <span class="rounded-full bg-primary-50 px-2 py-0.5 text-xs font-semibold text-primary-700">
                            {{ $rolesConPermisos->count() }} roles
                        </span>
                    </button>
                </nav>
            </div>

            <!-- PESTAÑA 1: LISTADO DE USUARIOS -->
            <div x-show="currentTab === 'usuarios'" class="mt-6 space-y-5">
                <!-- Barra de Búsqueda y Filtros reactivos -->
                <form action="{{ route('usuarios.index') }}" method="GET" class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <input type="hidden" name="tab" value="usuarios">

                    <div class="flex flex-1 flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <!-- Buscador -->
                        <div class="relative flex-1">
                            <i data-lucide="search" class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 size-4 text-slate-400"></i>
                            <input
                                type="text"
                                name="search"
                                value="{{ $filters['search'] ?? '' }}"
                                placeholder="Buscar por usuario, nombre, apellido o CI..."
                                class="block w-full rounded-xl border-0 py-2 pl-10 pr-4 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus-ring"
                            />
                        </div>

                        <!-- Filtro por Rol -->
                        <div class="sm:w-48">
                            <select
                                name="rol"
                                onchange="this.form.submit()"
                                class="block w-full rounded-xl border-0 bg-white py-2 pl-3.5 pr-8 text-sm text-slate-700 shadow-sm ring-1 ring-inset ring-slate-200 focus-ring"
                            >
                                <option value="">Todos los roles</option>
                                @foreach ($roles as $rol)
                                    <option value="{{ $rol->id_rol }}" {{ ($filters['rol'] ?? '') == $rol->id_rol ? 'selected' : '' }}>
                                        {{ $rol->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Filtro por Estado -->
                        <div class="sm:w-40">
                            <select
                                name="estado"
                                onchange="this.form.submit()"
                                class="block w-full rounded-xl border-0 bg-white py-2 pl-3.5 pr-8 text-sm text-slate-700 shadow-sm ring-1 ring-inset ring-slate-200 focus-ring"
                            >
                                <option value="">Todos los estados</option>
                                <option value="ACTIVO" {{ ($filters['estado'] ?? '') === 'ACTIVO' ? 'selected' : '' }}>Solo Activos</option>
                                <option value="INACTIVO" {{ ($filters['estado'] ?? '') === 'INACTIVO' ? 'selected' : '' }}>Solo Inactivos</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <x-button variant="secondary" type="submit">
                            <i data-lucide="filter" class="size-4 mr-1.5"></i>
                            <span>Filtrar</span>
                        </x-button>

                        @if (!empty($filters['search']) || !empty($filters['rol']) || !empty($filters['estado']))
                            <a
                                href="{{ route('usuarios.index') }}"
                                class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-500 hover:bg-slate-50 hover:text-slate-700 transition-colors"
                                title="Limpiar filtros"
                            >
                                <i data-lucide="rotate-ccw" class="size-4"></i>
                            </a>
                        @endif
                    </div>
                </form>

                <!-- Tabla de Usuarios -->
                <div class="overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-card">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200/80 text-left text-sm">
                            <thead class="bg-slate-50/75 text-xs font-semibold uppercase tracking-wider text-slate-500">
                                <tr>
                                    <th scope="col" class="px-5 py-3.5 whitespace-nowrap">Usuario</th>
                                    <th scope="col" class="px-5 py-3.5 whitespace-nowrap">Nombre y Apellidos</th>
                                    <th scope="col" class="px-5 py-3.5 whitespace-nowrap">Documento / Contacto</th>
                                    <th scope="col" class="px-5 py-3.5 whitespace-nowrap">Rol Asignado</th>
                                    <th scope="col" class="px-5 py-3.5 whitespace-nowrap">Estado / Activación</th>
                                    <th scope="col" class="px-5 py-3.5 whitespace-nowrap text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white text-slate-700">
                                @forelse ($usuarios as $usuario)
                                    @php
                                        $primaryRole = $usuario->roles->first()?->nombre ?? 'SIN_ROL';
                                        $isSelf = $usuario->id_usuario === $currentUser->id_usuario;
                                        $badgeTone = match($primaryRole) {
                                            'SUPER_ADMIN' => 'primary',
                                            'ADMIN' => 'info',
                                            'VETERINARIO' => 'success',
                                            'RECEPCION' => 'warning',
                                            default => 'neutral',
                                        };
                                    @endphp
                                    <tr class="hover:bg-slate-50/70 transition-colors {{ $usuario->estado === 'INACTIVO' ? 'opacity-60 bg-slate-50/30' : '' }}">
                                        <!-- Usuario -->
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-primary-100 text-primary-700 font-bold text-xs ring-2 ring-primary-500/20">
                                                    {{ strtoupper(substr($usuario->nombre_usuario, 0, 2)) }}
                                                </div>
                                                <div>
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-bold text-slate-900">
                                                             {{ $usuario->nombre_usuario }}
                                                        </span>
                                                        @if ($isSelf)
                                                            <span class="inline-flex items-center rounded-md bg-indigo-50 px-1.5 py-0.5 text-[10px] font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-700/10">
                                                                Tú
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <span class="text-xs text-slate-400">ID: #{{ $usuario->id_usuario }}</span>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Nombre y Apellidos -->
                                        <td class="px-5 py-4">
                                            <p class="font-semibold text-slate-900">
                                                {{ $usuario->persona ? ($usuario->persona->nombre . ' ' . $usuario->persona->apellido1 . ($usuario->persona->apellido2 ? ' ' . $usuario->persona->apellido2 : '')) : 'Sin persona vinculada' }}
                                            </p>
                                            <p class="text-xs text-slate-500">
                                                {{ $usuario->persona?->email ?: 'Sin correo registrado' }}
                                            </p>
                                        </td>

                                        <!-- Documento / Contacto -->
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <div class="space-y-0.5">
                                                <div class="flex items-center gap-1.5 text-xs text-slate-700">
                                                    <i data-lucide="id-card" class="size-3.5 text-slate-400"></i>
                                                    <span class="font-medium">CI: {{ $usuario->persona?->documento ?: 'S/D' }}</span>
                                                </div>
                                                @if ($usuario->persona?->telefono)
                                                    <div class="flex items-center gap-1.5 text-xs text-slate-500">
                                                        <i data-lucide="phone" class="size-3.5 text-slate-400"></i>
                                                        <span>{{ $usuario->persona->telefono }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>

                                        <!-- Rol Asignado -->
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <x-badge :tone="$badgeTone">
                                                {{ $primaryRole }}
                                            </x-badge>
                                        </td>

                                        <!-- Estado y Activación -->
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <div class="space-y-1.5">
                                                <x-state-badge :state="$usuario->estado" />
                                                <div>
                                                    @if ($usuario->hasVerifiedEmail())
                                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20" title="Verificado">
                                                            <i data-lucide="check-check" class="size-3 text-emerald-600"></i>
                                                            <span>Verificado</span>
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-700 ring-1 ring-inset ring-amber-600/20" title="Enlace de 24h enviado a {{ $usuario->persona?->email }}">
                                                            <i data-lucide="clock" class="size-3 text-amber-600"></i>
                                                            <span>Pendiente Activación</span>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Acciones -->
                                        <td class="px-5 py-4 whitespace-nowrap text-right">
                                            <div class="flex items-center justify-end gap-1.5">


                                                <!-- Botón Editar -->
                                                <button
                                                    type="button"
                                                    @click="openEdit(@js($usuario))"
                                                    class="inline-flex size-8 items-center justify-center rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors"
                                                    title="Editar usuario"
                                                >
                                                    <i data-lucide="pencil" class="size-4"></i>
                                                </button>

                                                <!-- Botón Baja Lógica (Toggle Estado) -->
                                                @if (! $isSelf)
                                                    @if ($usuario->estado === 'ACTIVO')
                                                        <button
                                                            type="button"
                                                            @click="$dispatch('open-confirm-modal', {
                                                                url: '{{ route('usuarios.toggle-estado', $usuario->id_usuario) }}',
                                                                name: '{{ $usuario->nombre_usuario }} ({{ $usuario->persona?->nombre }})',
                                                                details: 'Baja lógica: El usuario no podrá iniciar sesión pero todos sus datos e historiales clínicos se preservarán intactos.',
                                                                title: '¿Desactivar usuario del sistema?',
                                                                description: 'Esta acción aplica una baja lógica para deshabilitar el acceso.',
                                                                confirmText: 'Desactivar Usuario',
                                                                variant: 'danger',
                                                                method: 'POST'
                                                            })"
                                                            class="inline-flex size-8 items-center justify-center rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50 transition-colors"
                                                            title="Dar de baja lógica"
                                                        >
                                                            <i data-lucide="user-x" class="size-4"></i>
                                                        </button>
                                                    @else
                                                        <button
                                                            type="button"
                                                            @click="$dispatch('open-confirm-modal', {
                                                                url: '{{ route('usuarios.toggle-estado', $usuario->id_usuario) }}',
                                                                name: '{{ $usuario->nombre_usuario }} ({{ $usuario->persona?->nombre }})',
                                                                details: 'Reactivación: El usuario recuperará de inmediato el acceso con su contraseña y rol vigentes.',
                                                                title: '¿Reactivar usuario en el sistema?',
                                                                description: 'Se restablecerán las credenciales de acceso para esta cuenta.',
                                                                confirmText: 'Reactivar Usuario',
                                                                variant: 'primary',
                                                                method: 'POST'
                                                            })"
                                                            class="inline-flex size-8 items-center justify-center rounded-lg border border-emerald-200 text-emerald-600 hover:bg-emerald-50 transition-colors"
                                                            title="Reactivar usuario"
                                                        >
                                                            <i data-lucide="user-check" class="size-4"></i>
                                                        </button>
                                                    @endif
                                                @else
                                                    <span class="inline-block size-8 text-center text-xs text-slate-300 py-1.5" title="No puedes desactivar tu propia cuenta">
                                                        —
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="p-8">
                                            <x-empty-state
                                                icon="users"
                                                title="No se encontraron usuarios"
                                                description="No hay usuarios registrados con los criterios de búsqueda aplicados."
                                            />
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($usuarios->hasPages())
                        <div class="border-t border-slate-200/80 px-5 py-3.5 bg-slate-50/40">
                            {{ $usuarios->links() }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- PESTAÑA 2: GESTIÓN DE ROLES Y MATRIZ DE PERMISOS -->
            <div x-show="currentTab === 'roles'" class="mt-6 space-y-6">
                <!-- Tarjeta de Información y Explicación de Seguridad con botón Nuevo Rol -->
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between rounded-xl border border-indigo-100 bg-indigo-50/50 p-4">
                    <div class="flex items-start gap-4">
                        <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-primary-100 text-primary-700">
                            <i data-lucide="shield-alert" class="size-5"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Matriz de Control de Acceso (RBAC)</h3>
                            <p class="mt-0.5 text-xs text-slate-600 leading-relaxed">
                                Administra los roles institucionales y sus privilegios operativos. Marca o desmarca permisos y presiona <strong>"Guardar Matriz de Permisos"</strong>.
                            </p>
                        </div>
                    </div>
                    <div class="shrink-0">
                        <x-button variant="primary" type="button" @click="openCreateRole()">
                            <i data-lucide="shield-plus" class="size-4 mr-1.5"></i>
                            <span>Nuevo Rol</span>
                        </x-button>
                    </div>
                </div>

                <!-- Formulario de la Matriz de Permisos -->
                <form action="{{ route('roles.update-permisos') }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-card">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200/80 text-left text-sm">
                                <thead class="bg-slate-50/75 text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    <tr>
                                        <th scope="col" class="px-5 py-4 w-72 whitespace-nowrap">Rol Institucional</th>
                                        @foreach ($permisos as $permiso)
                                            <th scope="col" class="px-4 py-4 text-center whitespace-nowrap min-w-32">
                                                <div class="font-bold text-slate-800">{{ $permiso->nombre }}</div>
                                                <div class="text-[10px] text-slate-400 font-normal lowercase tracking-normal max-w-xs mx-auto truncate" title="{{ $permiso->descripcion }}">
                                                    {{ $permiso->descripcion }}
                                                </div>
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white text-slate-700">
                                    @foreach ($rolesConPermisos as $rol)
                                        @php
                                            $isSuperAdminRole = $rol->nombre === 'SUPER_ADMIN';
                                            $badgeTone = match($rol->nombre) {
                                                'SUPER_ADMIN' => 'primary',
                                                'ADMIN' => 'info',
                                                'VETERINARIO' => 'success',
                                                'RECEPCION' => 'warning',
                                                default => 'neutral',
                                            };
                                        @endphp
                                        <tr class="hover:bg-slate-50/70 transition-colors">
                                            <!-- Rol y Descripción -->
                                            <td class="px-5 py-4">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <x-badge :tone="$badgeTone">
                                                        {{ $rol->nombre }}
                                                    </x-badge>
                                                    @if (!$isSuperAdminRole)
                                                        <form action="{{ route('roles.toggle-estado', $rol->id_rol) }}" method="POST" class="inline">
                                                            @csrf
                                                            <button
                                                                type="submit"
                                                                class="text-[10px] font-bold px-2 py-0.5 rounded-full border transition-colors {{ ($rol->estado ?? 'ACTIVO') === 'ACTIVO' ? 'border-emerald-200 text-emerald-700 bg-emerald-50 hover:bg-emerald-100' : 'border-slate-300 text-slate-500 bg-slate-100 hover:bg-slate-200' }}"
                                                                title="Clic para cambiar estado activo/inactivo del grupo"
                                                            >
                                                                {{ $rol->estado ?? 'ACTIVO' }}
                                                            </button>
                                                        </form>
                                                    @else
                                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border border-indigo-200 text-indigo-700 bg-indigo-50">
                                                            ACTIVO (Protegido)
                                                        </span>
                                                    @endif
                                                    <button
                                                        type="button"
                                                        @click="openEditRole(@js($rol))"
                                                        class="inline-flex size-6 items-center justify-center rounded-md border border-slate-200 text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors"
                                                        title="Editar nombre y descripción de {{ $rol->nombre }}"
                                                    >
                                                        <i data-lucide="pencil" class="size-3"></i>
                                                    </button>
                                                </div>
                                                <p class="mt-1 text-xs text-slate-500">
                                                    {{ $rol->descripcion ?: 'Sin descripción detallada' }}
                                                </p>
                                            </td>

                                            <!-- Checkbox por Permiso -->
                                            @foreach ($permisos as $permiso)
                                                @php
                                                    $tienePermiso = $rol->hasPermiso($permiso->id_permiso);
                                                @endphp
                                                <td class="px-4 py-4 text-center">
                                                    <label class="inline-flex items-center justify-center p-2 rounded-lg hover:bg-slate-100 cursor-pointer transition-colors">
                                                        <input
                                                            type="checkbox"
                                                            name="permisos[{{ $rol->id_rol }}][]"
                                                            value="{{ $permiso->id_permiso }}"
                                                            {{ $tienePermiso ? 'checked' : '' }}
                                                            class="size-4.5 rounded border-slate-300 text-primary-600 focus:ring-primary-500 cursor-pointer"
                                                        />
                                                    </label>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="flex items-center justify-between border-t border-slate-200/80 bg-slate-50/50 px-6 py-4">
                            <span class="text-xs text-slate-500">
                                <i data-lucide="info" class="inline size-3.5 mr-1 text-slate-400"></i>
                                Los cambios en los permisos se registran en la tabla pivote de seguridad inmediatamente.
                            </span>

                            <x-button variant="primary" type="submit">
                                <i data-lucide="save" class="size-4 mr-2"></i>
                                <span>Guardar Matriz de Permisos</span>
                            </x-button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL DE ALTA / EDICIÓN DE USUARIO -->
        <div
            x-show="userModalOpen"
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            x-transition.opacity
        >
            <div class="flex min-h-full items-center justify-center p-4">
                <!-- Fondo oscuro backdrop -->
                <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="closeModal()"></div>

                <!-- Contenedor del Modal -->
                <div
                    class="relative w-full max-w-2xl rounded-2xl bg-white shadow-pop overflow-hidden"
                    x-show="userModalOpen"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                >
                    <!-- Cabecera del Modal -->
                    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 bg-slate-50/50">
                        <div class="flex items-center gap-3">
                            <div class="flex size-9 items-center justify-center rounded-xl bg-primary-50 text-primary-600">
                                <i data-lucide="user-cog" class="size-5"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900" x-text="modalTitle"></h3>
                                <p class="text-xs text-slate-500">Datos personales y credenciales de acceso al sistema</p>
                            </div>
                        </div>

                        <button type="button" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600" @click="closeModal()">
                            <i data-lucide="x" class="size-5"></i>
                        </button>
                    </div>

                    <!-- Formulario de Guardado -->
                    <form :action="formAction" method="POST">
                        @csrf
                        <template x-if="isEditing">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <div class="max-h-[75vh] overflow-y-auto p-6 space-y-6">
                            <!-- Sección 1: Datos Personales (Persona) -->
                            <div>
                                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 flex items-center gap-2">
                                    <i data-lucide="id-card" class="size-3.5"></i>
                                    <span>Datos Personales (Persona)</span>
                                </h4>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <x-field label="Documento de Identidad (CI)" :error="$errors->first('documento')" required>
                                        <x-input
                                            type="text"
                                            name="documento"
                                            x-model="formData.documento"
                                            placeholder="Ej. 12345678"
                                            required
                                            :invalid="$errors->has('documento')"
                                        />
                                    </x-field>

                                    <x-field label="Nombres" :error="$errors->first('nombre')" required>
                                        <x-input
                                            type="text"
                                            name="nombre"
                                            x-model="formData.nombre"
                                            placeholder="Ej. Ana María"
                                            required
                                            :invalid="$errors->has('nombre')"
                                        />
                                    </x-field>

                                    <x-field label="Primer Apellido" :error="$errors->first('apellido1')" required>
                                        <x-input
                                            type="text"
                                            name="apellido1"
                                            x-model="formData.apellido1"
                                            placeholder="Ej. Morales"
                                            required
                                            :invalid="$errors->has('apellido1')"
                                        />
                                    </x-field>

                                    <x-field label="Segundo Apellido" :error="$errors->first('apellido2')">
                                        <x-input
                                            type="text"
                                            name="apellido2"
                                            x-model="formData.apellido2"
                                            placeholder="Ej. Gutiérrez"
                                            :invalid="$errors->has('apellido2')"
                                        />
                                    </x-field>

                                    <x-field label="Teléfono / Celular" :error="$errors->first('telefono')">
                                        <x-input
                                            type="text"
                                            name="telefono"
                                            x-model="formData.telefono"
                                            placeholder="Ej. 71234567"
                                            :invalid="$errors->has('telefono')"
                                        />
                                    </x-field>

                                    <x-field label="Correo Electrónico" :error="$errors->first('email')" required hint="Enlazado para recuperación OTP">
                                        <x-input
                                            type="email"
                                            name="email"
                                            x-model="formData.email"
                                            placeholder="ejemplo@lapetdiatria.com"
                                            required
                                            :invalid="$errors->has('email')"
                                        />
                                    </x-field>

                                    <div class="sm:col-span-2">
                                        <x-field label="Dirección de Domicilio" :error="$errors->first('direccion')">
                                            <x-input
                                                type="text"
                                                name="direccion"
                                                x-model="formData.direccion"
                                                placeholder="Ej. Av. Principal #123, Zona Central"
                                                :invalid="$errors->has('direccion')"
                                            />
                                        </x-field>
                                    </div>
                                </div>
                            </div>

                            <!-- Separador -->
                            <div class="border-t border-slate-100"></div>

                            <!-- Sección 2: Credenciales y Rol de Acceso (Usuario) -->
                            <div>
                                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 flex items-center gap-2">
                                    <i data-lucide="key-round" class="size-3.5"></i>
                                    <span>Credenciales y Rol de Acceso (Usuario)</span>
                                </h4>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <x-field label="Nombre de Usuario" :error="$errors->first('nombre_usuario')" required hint="Único en todo el sistema">
                                        <x-input
                                            type="text"
                                            name="nombre_usuario"
                                            x-model="formData.nombre_usuario"
                                            placeholder="Ej. amorales"
                                            required
                                            :invalid="$errors->has('nombre_usuario')"
                                        />
                                    </x-field>

                                    <x-field label="Rol Asignado" :error="$errors->first('id_rol')" required>
                                        <x-select name="id_rol" x-model="formData.id_rol" required :invalid="$errors->has('id_rol')">
                                            @foreach ($roles as $rol)
                                                <option value="{{ $rol->id_rol }}">{{ $rol->nombre }}</option>
                                            @endforeach
                                        </x-select>
                                    </x-field>

                                    <x-field label="Estado del Usuario" :error="$errors->first('estado')" required>
                                        <x-select name="estado" x-model="formData.estado" required :invalid="$errors->has('estado')">
                                            <option value="ACTIVO">ACTIVO (Permitir acceso)</option>
                                            <option value="INACTIVO">INACTIVO (Bloquear acceso)</option>
                                        </x-select>
                                    </x-field>

                                    <div class="sm:col-span-2">
                                        <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-3 mb-3 text-xs text-slate-500" x-show="isEditing">
                                            <i data-lucide="info" class="inline size-3.5 text-primary-600 mr-1"></i>
                                            Deja los campos de contraseña vacíos si deseas mantener la contraseña actual del usuario.
                                        </div>

                                        <div class="rounded-xl border border-amber-200 bg-amber-50/70 p-3.5 mb-3 text-xs text-amber-800" x-show="!isEditing">
                                            <div class="flex items-start gap-2.5">
                                                <i data-lucide="shield-alert" class="size-4 shrink-0 text-amber-600 mt-0.5"></i>
                                                <div>
                                                    <p class="font-bold text-amber-900">Seguridad: Verificación Obligatoria de Correo (24h)</p>
                                                    <p class="mt-0.5 leading-relaxed text-amber-800">
                                                        Al registrar al usuario, su cuenta quedará <strong>Pendiente de Activación</strong>. Se generará un enlace expirable de 24 horas y <strong>el usuario NO podrá ingresar en /login</strong> hasta abrir dicho enlace para verificar su cuenta.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <x-field
                                        label="Contraseña"
                                        :error="$errors->first('password')"
                                        ::required="!isEditing"
                                        hint="Mínimo 6 caracteres"
                                    >
                                        <x-input
                                            type="password"
                                            name="password"
                                            placeholder="••••••••"
                                            ::required="!isEditing"
                                            :invalid="$errors->has('password')"
                                        />
                                    </x-field>

                                    <x-field
                                        label="Confirmar Contraseña"
                                        :error="$errors->first('password_confirmation')"
                                        ::required="!isEditing"
                                    >
                                        <x-input
                                            type="password"
                                            name="password_confirmation"
                                            placeholder="••••••••"
                                            ::required="!isEditing"
                                            :invalid="$errors->has('password_confirmation')"
                                        />
                                    </x-field>
                                </div>
                            </div>
                        </div>

                        <!-- Pie del Modal con Botones -->
                        <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4 bg-slate-50/50">
                            <x-button variant="secondary" type="button" @click="closeModal()">
                                Cancelar
                            </x-button>

                            <x-button variant="primary" type="submit">
                                <span x-text="isEditing ? 'Guardar Cambios' : 'Crear Usuario'"></span>
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- MODAL DE ALTA / EDICIÓN DE ROL -->
        <div
            x-show="roleModalOpen"
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            x-transition.opacity
        >
            <div class="flex min-h-full items-center justify-center p-4">
                <!-- Fondo oscuro backdrop -->
                <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="closeRoleModal()"></div>

                <!-- Contenedor del Modal de Rol -->
                <div
                    class="relative w-full max-w-lg rounded-2xl bg-white shadow-pop overflow-hidden"
                    x-show="roleModalOpen"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                >
                    <!-- Cabecera del Modal -->
                    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 bg-slate-50/50">
                        <div class="flex items-center gap-3">
                            <div class="flex size-9 items-center justify-center rounded-xl bg-primary-50 text-primary-600">
                                <i data-lucide="shield" class="size-5"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900" x-text="roleModalTitle"></h3>
                                <p class="text-xs text-slate-500">Configuración de perfiles y accesos del sistema</p>
                            </div>
                        </div>

                        <button type="button" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600" @click="closeRoleModal()">
                            <i data-lucide="x" class="size-5"></i>
                        </button>
                    </div>

                    <!-- Formulario de Rol -->
                    <form :action="roleFormAction" method="POST">
                        @csrf
                        <template x-if="isEditingRole">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <div class="p-6 space-y-4">
                            <x-field label="Nombre del Rol (Identificador)" :error="$errors->first('nombre')" required hint="Ej. ASISTENTE_VETERINARIO, CAJERO">
                                <x-input
                                    type="text"
                                    name="nombre"
                                    x-model="roleFormData.nombre"
                                    placeholder="Ej. ASISTENTE"
                                    required
                                    :invalid="$errors->has('nombre')"
                                />
                            </x-field>

                            <x-field label="Descripción del Rol" :error="$errors->first('descripcion')" hint="Breve resumen de las responsabilidades">
                                <x-input
                                    type="text"
                                    name="descripcion"
                                    x-model="roleFormData.descripcion"
                                    placeholder="Ej. Personal de apoyo en consultas clínicas y laboratorio"
                                    :invalid="$errors->has('descripcion')"
                                />
                            </x-field>
                        </div>

                        <!-- Pie del Modal -->
                        <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4 bg-slate-50/50">
                            <x-button variant="secondary" type="button" @click="closeRoleModal()">
                                Cancelar
                            </x-button>

                            <x-button variant="primary" type="submit">
                                <span x-text="isEditingRole ? 'Guardar Cambios' : 'Crear Rol'"></span>
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>



    </div>
</x-layout>

