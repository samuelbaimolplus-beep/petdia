<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Cuenta;
use App\Models\Especie;
use App\Models\FichaMedica;
use App\Models\Paciente;
use App\Models\Persona;
use App\Models\Propietario;
use App\Models\Raza;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClienteWebController extends Controller
{
    /**
     * Muestra el listado de propietarios con buscador y métricas.
     */
    public function index(Request $request): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('clientes')) {
            abort(403, 'Acceso denegado a este módulo.');
        }

        $query = Propietario::with(['persona', 'pacientes.raza.especie'])
            ->orderBy('id_persona', 'desc');

        // Buscador por nombre, documento (CI), teléfono de la persona o nombre de la mascota
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('persona', function ($qp) use ($search) {
                    $qp->where('nombre', 'ilike', "%{$search}%")
                        ->orWhere('apellido1', 'ilike', "%{$search}%")
                        ->orWhere('apellido2', 'ilike', "%{$search}%")
                        ->orWhere('documento', 'ilike', "%{$search}%")
                        ->orWhere('telefono', 'ilike', "%{$search}%");
                })->orWhereHas('pacientes', function ($qpac) use ($search) {
                    $qpac->where('nombre', 'ilike', "%{$search}%");
                });
            });
        }

        // Filtro por posesión de mascotas
        if ($filtroMascotas = $request->input('con_mascotas')) {
            if ($filtroMascotas === 'si') {
                $query->has('pacientes');
            } elseif ($filtroMascotas === 'no') {
                $query->doesntHave('pacientes');
            }
        }

        $propietarios = $query->paginate(10)->withQueryString();
        $totalPropietarios = Propietario::count();
        $totalMascotas = Paciente::count();
        $mascotasActivas = Paciente::where('estado', 'ACTIVO')->count();
        $totalEspecies = Especie::count();

        $especies = Especie::with('razas')->orderBy('nombre')->get();
        $canWrite = $currentUser->hasRole(['SUPER_ADMIN', 'ADMIN', 'RECEPCION']);

        return view('clientes.index', [
            'propietarios' => $propietarios,
            'totalPropietarios' => $totalPropietarios,
            'totalMascotas' => $totalMascotas,
            'mascotasActivas' => $mascotasActivas,
            'totalEspecies' => $totalEspecies,
            'especies' => $especies,
            'canWrite' => $canWrite,
            'filters' => [
                'search' => $request->input('search'),
                'con_mascotas' => $request->input('con_mascotas'),
            ],
        ]);
    }

    /**
     * Registra un nuevo propietario.
     */
    public function store(Request $request): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->hasRole(['SUPER_ADMIN', 'ADMIN', 'RECEPCION'])) {
            abort(403, 'No tienes permisos de escritura en este módulo.');
        }

        $validated = $request->validate([
            'documento' => ['required', 'string', 'max:20'],
            'nombre' => ['required', 'string', 'max:50'],
            'apellido1' => ['required', 'string', 'max:50'],
            'apellido2' => ['nullable', 'string', 'max:50'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'direccion' => ['nullable', 'string', 'max:200'],
        ], [
            'documento.required' => 'El documento de identidad (CI) es obligatorio.',
            'nombre.required' => 'El nombre es obligatorio.',
            'apellido1.required' => 'El primer apellido es obligatorio.',
        ]);

        $propietario = DB::transaction(function () use ($validated) {
            $persona = Persona::where('documento', $validated['documento'])->first();
            if ($persona) {
                $persona->update($validated);
            } else {
                $persona = Persona::create($validated);
            }

            $prop = Propietario::firstOrCreate(['id_persona' => $persona->id_persona]);

            // Crear cuenta para transacciones si no existe
            Cuenta::firstOrCreate([
                'id_propietario' => $prop->id_persona,
            ], [
                'saldo_pendiente' => 0.00,
                'limite_credito' => 0.00,
                'estado' => 'ACTIVA',
            ]);

            return $prop;
        });

        return redirect()->route('clientes.show', $propietario->id_persona)
            ->with('success', 'Cliente registrado exitosamente. Ahora puedes asociar sus mascotas.');
    }

    /**
     * Muestra la ficha de un propietario con sus mascotas asociadas.
     */
    public function show(int $id): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('clientes')) {
            abort(403, 'Acceso denegado.');
        }

        $propietario = Propietario::with([
            'persona',
            'pacientes.raza.especie',
            'pacientes.citas' => fn ($q) => $q->latest('fecha_hora_inicio')->limit(3),
            'cuenta',
        ])->findOrFail($id);

        $especies = Especie::with('razas')->orderBy('nombre')->get();
        $canWrite = $currentUser->hasRole(['SUPER_ADMIN', 'ADMIN', 'RECEPCION']);

        return view('clientes.show', [
            'propietario' => $propietario,
            'especies' => $especies,
            'canWrite' => $canWrite,
        ]);
    }

    /**
     * Actualiza los datos personales del propietario.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->hasRole(['SUPER_ADMIN', 'ADMIN', 'RECEPCION'])) {
            abort(403, 'No tienes permisos de edición.');
        }

        $propietario = Propietario::with('persona')->findOrFail($id);
        $persona = $propietario->persona;

        $validated = $request->validate([
            'documento' => ['required', 'string', 'max:20', Rule::unique('personas', 'documento')->ignore($persona->id_persona, 'id_persona')],
            'nombre' => ['required', 'string', 'max:50'],
            'apellido1' => ['required', 'string', 'max:50'],
            'apellido2' => ['nullable', 'string', 'max:50'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'direccion' => ['nullable', 'string', 'max:200'],
        ]);

        $persona->update($validated);

        return back()->with('success', 'Datos del propietario actualizados correctamente.');
    }

    /**
     * Registra una nueva mascota para el propietario.
     */
    public function storeMascota(Request $request, int $id): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->hasRole(['SUPER_ADMIN', 'ADMIN', 'RECEPCION'])) {
            abort(403, 'No tienes permisos para registrar mascotas.');
        }

        $propietario = Propietario::findOrFail($id);

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:50'],
            'id_raza' => ['required', 'integer', 'exists:razas,id_raza'],
            'sexo' => ['required', 'string', 'in:MACHO,HEMBRA'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'tamanio' => ['nullable', 'string', 'in:PEQUEÑO,MEDIANO,GRANDE,GIGANTE'],
            'condicion_reproductiva' => ['nullable', 'string', 'in:INTACTO,CASTRADO'],
            'color' => ['nullable', 'string', 'max:50'],
            'microchip' => ['nullable', 'string', 'max:50'],
            'fecha_implantacion' => ['nullable', 'date'],
            'tipo_pelaje' => ['nullable', 'string', 'max:50'],
            'caract_particulares' => ['nullable', 'string'],
            'comportamiento_normal' => ['nullable', 'string'],
            'recomendaciones' => ['nullable', 'string'],
            'autorizacion_redes' => ['nullable', 'boolean'],
            'estado' => ['required', 'string', 'in:ACTIVO,INACTIVO,FALLECIDO'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $validated['id_propietario'] = $propietario->id_persona;
        $validated['autorizacion_redes'] = $request->boolean('autorizacion_redes');

        DB::transaction(function () use ($validated, $request) {
            // Subir foto si fue proporcionada
            if ($request->hasFile('foto')) {
                $validated['foto'] = $request->file('foto')->store('mascotas', 'public');
            }

            $paciente = Paciente::create($validated);

            // Crear ficha médica base inicial
            FichaMedica::firstOrCreate(
                ['id_paciente' => $paciente->id_paciente],
                [
                    'fecha_apertura' => now()->toDateString(),
                    'antecedentes' => null,
                    'alergias' => null,
                    'enfermedades_previas' => null,
                    'cirugias_previas' => null,
                    'medicamentos_habituales' => null,
                    'observaciones' => 'Ficha clínica abierta al registrar el paciente.',
                ]
            );
        });

        return back()->with('success', "Mascota '{$validated['nombre']}' agregada correctamente.");
    }

    /**
     * Muestra la ficha detallada de una mascota.
     */
    public function showMascota(int $id): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('clientes')) {
            abort(403, 'Acceso denegado.');
        }

        $paciente = Paciente::with([
            'raza.especie',
            'propietario.persona',
            'citas.detalles.servicio',
            'citas.servicio',
            'atenciones.atencionVeterinaria',
            'recordatorios',
            'fichaMedica',
        ])->findOrFail($id);

        $especies = Especie::with('razas')->orderBy('nombre')->get();
        $propietarios = Propietario::with('persona')->get();
        $canWrite = $currentUser->hasRole(['SUPER_ADMIN', 'ADMIN', 'RECEPCION']);
        $canAccessClinico = $currentUser->canAccessModule('historial_clinico');

        return view('clientes.mascota', [
            'paciente' => $paciente,
            'especies' => $especies,
            'propietarios' => $propietarios,
            'canWrite' => $canWrite,
            'canAccessClinico' => $canAccessClinico,
        ]);
    }

    /**
     * Actualiza los datos de una mascota.
     */
    public function updateMascota(Request $request, int $id): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->hasRole(['SUPER_ADMIN', 'ADMIN', 'RECEPCION'])) {
            abort(403, 'No tienes permisos de edición.');
        }

        $paciente = Paciente::findOrFail($id);

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:50'],
            'id_propietario' => ['nullable', 'integer', 'exists:propietarios,id_persona'],
            'id_raza' => ['required', 'integer', 'exists:razas,id_raza'],
            'sexo' => ['required', 'string', 'in:MACHO,HEMBRA'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'tamanio' => ['nullable', 'string', 'in:PEQUEÑO,MEDIANO,GRANDE,GIGANTE'],
            'condicion_reproductiva' => ['nullable', 'string', 'in:INTACTO,CASTRADO'],
            'color' => ['nullable', 'string', 'max:50'],
            'microchip' => ['nullable', 'string', 'max:50'],
            'fecha_implantacion' => ['nullable', 'date'],
            'tipo_pelaje' => ['nullable', 'string', 'max:50'],
            'caract_particulares' => ['nullable', 'string'],
            'comportamiento_normal' => ['nullable', 'string'],
            'recomendaciones' => ['nullable', 'string'],
            'autorizacion_redes' => ['nullable', 'boolean'],
            'estado' => ['required', 'string', 'in:ACTIVO,INACTIVO,FALLECIDO'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $validated['autorizacion_redes'] = $request->boolean('autorizacion_redes');

        // Subir foto si fue proporcionada
        if ($request->hasFile('foto')) {
            // Borrar foto anterior si existe
            if ($paciente->foto) {
                Storage::disk('public')->delete($paciente->foto);
            }
            $validated['foto'] = $request->file('foto')->store('mascotas', 'public');
        }

        $paciente->update($validated);

        return back()->with('success', "Datos de la mascota '{$paciente->nombre}' actualizados correctamente.");
    }

    /**
     * Gestión del catálogo de especies y razas.
     */
    public function catalogoEspeciesRazas(Request $request): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('clientes')) {
            abort(403, 'Acceso denegado.');
        }

        $especies = Especie::with(['razas' => fn ($q) => $q->withCount('pacientes')])
            ->orderBy('nombre')
            ->get();

        $canWrite = $currentUser->hasRole(['SUPER_ADMIN', 'ADMIN', 'RECEPCION']);

        return view('clientes.catalogo', [
            'especies' => $especies,
            'canWrite' => $canWrite,
        ]);
    }

    /**
     * Registra una nueva especie.
     */
    public function storeEspecie(Request $request): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->hasRole(['SUPER_ADMIN', 'ADMIN', 'RECEPCION'])) {
            abort(403, 'No autorizado.');
        }

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:50', 'unique:especies,nombre'],
        ], [
            'nombre.required' => 'El nombre de la especie es obligatorio.',
            'nombre.unique' => 'Esta especie ya se encuentra registrada.',
        ]);

        Especie::create([
            'nombre' => ucfirst(trim($validated['nombre'])),
        ]);

        return redirect()->route('catalogo.especies-razas')
            ->with('success', 'Especie registrada correctamente.');
    }

    /**
     * Actualiza una especie existente.
     */
    public function updateEspecie(Request $request, int $id): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->hasRole(['SUPER_ADMIN', 'ADMIN', 'RECEPCION'])) {
            abort(403, 'No autorizado.');
        }

        $especie = Especie::findOrFail($id);

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:50', Rule::unique('especies', 'nombre')->ignore($especie->id_especie, 'id_especie')],
        ]);

        $especie->update([
            'nombre' => ucfirst(trim($validated['nombre'])),
        ]);

        return redirect()->route('catalogo.especies-razas')
            ->with('success', 'Especie actualizada exitosamente.');
    }

    /**
     * Registra una nueva raza asociada a una especie.
     */
    public function storeRaza(Request $request): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->hasRole(['SUPER_ADMIN', 'ADMIN', 'RECEPCION'])) {
            abort(403, 'No autorizado.');
        }

        $validated = $request->validate([
            'id_especie' => ['required', 'integer', 'exists:especies,id_especie'],
            'nombre' => ['required', 'string', 'max:50'],
        ], [
            'id_especie.required' => 'Debe seleccionar una especie.',
            'nombre.required' => 'El nombre de la raza es obligatorio.',
        ]);

        Raza::create([
            'id_especie' => $validated['id_especie'],
            'nombre' => ucfirst(trim($validated['nombre'])),
        ]);

        return redirect()->route('catalogo.especies-razas')
            ->with('success', 'Raza registrada correctamente.');
    }

    /**
     * Actualiza una raza.
     */
    public function updateRaza(Request $request, int $id): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->hasRole(['SUPER_ADMIN', 'ADMIN', 'RECEPCION'])) {
            abort(403, 'No autorizado.');
        }

        $raza = Raza::findOrFail($id);

        $validated = $request->validate([
            'id_especie' => ['required', 'integer', 'exists:especies,id_especie'],
            'nombre' => ['required', 'string', 'max:50'],
        ]);

        $raza->update([
            'id_especie' => $validated['id_especie'],
            'nombre' => ucfirst(trim($validated['nombre'])),
        ]);

        return redirect()->route('catalogo.especies-razas')
            ->with('success', 'Raza actualizada correctamente.');
    }

    /**
     * Retorna las razas de una especie en formato JSON para selects dependientes.
     */
    public function getRazasByEspecie(int $idEspecie): JsonResponse
    {
        $razas = Raza::where('id_especie', $idEspecie)
            ->orderBy('nombre')
            ->get(['id_raza', 'nombre']);

        return response()->json($razas);
    }
}
