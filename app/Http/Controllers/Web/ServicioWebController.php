<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Especie;
use App\Models\Raza;
use App\Models\Servicio;
use App\Models\TiempoEstimado;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ServicioWebController extends Controller
{
    /**
     * Catálogo general de servicios con KPIs, filtros y estados.
     */
    public function index(Request $request): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('servicios')) {
            abort(403, 'No tienes permisos para acceder al catálogo de servicios.');
        }

        $query = Servicio::with(['tiemposEstimados.especie'])->orderBy('nombre');

        // Búsqueda por nombre o descripción
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'ilike', "%{$search}%")
                    ->orWhere('descripcion', 'ilike', "%{$search}%")
                    ->orWhere('observaciones', 'ilike', "%{$search}%");
            });
        }

        // Filtro por Categoría
        if ($categoria = $request->input('categoria')) {
            $query->where('categoria', $categoria);
        }

        // Filtro por Estado
        if ($estado = $request->input('estado')) {
            $query->where('estado', $estado);
        }

        // Filtro por Disponibilidad a Domicilio
        if ($request->has('domicilio') && $request->input('domicilio') !== '') {
            $query->where('disponible_domicilio', (bool) $request->input('domicilio'));
        }

        $servicios = $query->paginate(15)->withQueryString();

        // Categorías existentes para filtro
        $categorias = Servicio::whereNotNull('categoria')
            ->distinct()
            ->pluck('categoria')
            ->sort()
            ->values();

        // KPIs
        $totalServicios = Servicio::count();
        $serviciosVeterinarios = Servicio::where('categoria', 'VETERINARIA')->count();
        $serviciosEstetica = Servicio::where('categoria', 'ESTETICA')->count();
        $disponiblesDomicilio = Servicio::where('disponible_domicilio', true)->count();

        return view('servicios.index', compact(
            'servicios',
            'categorias',
            'totalServicios',
            'serviciosVeterinarios',
            'serviciosEstetica',
            'disponiblesDomicilio'
        ));
    }

    /**
     * Formulario de creación de servicio.
     */
    public function create(): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('servicios')) {
            abort(403, 'No tienes permisos para acceder al catálogo de servicios.');
        }

        $especies = Especie::orderBy('nombre')->get();

        return view('servicios.create', compact('especies'));
    }

    /**
     * Almacena un nuevo servicio en el catálogo.
     */
    public function store(Request $request): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('servicios')) {
            abort(403, 'No tienes permisos para acceder al catálogo de servicios.');
        }

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'categoria' => ['required', 'string', 'in:VETERINARIA,ESTETICA,CIRUGIA,LABORATORIO,OTRO'],
            'precio_base' => ['required', 'numeric', 'min:0'],
            'disponible_domicilio' => ['nullable', 'boolean'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'observaciones' => ['nullable', 'string', 'max:500'],
            'duracion_minutos_defecto' => ['nullable', 'integer', 'min:5', 'max:480'],
            'id_especie_defecto' => ['nullable', 'integer', 'exists:especies,id_especie'],
        ]);

        return DB::transaction(function () use ($validated) {
            $servicio = Servicio::create([
                'nombre' => $validated['nombre'],
                'categoria' => $validated['categoria'],
                'precio_base' => $validated['precio_base'],
                'disponible_domicilio' => ! empty($validated['disponible_domicilio']),
                'descripcion' => $validated['descripcion'] ?? null,
                'observaciones' => $validated['observaciones'] ?? null,
                'estado' => 'ACTIVO',
            ]);

            // Si se definió una duración estimada inicial
            if (! empty($validated['duracion_minutos_defecto']) && ! empty($validated['id_especie_defecto'])) {
                TiempoEstimado::create([
                    'id_servicio' => $servicio->id_servicio,
                    'id_especie' => $validated['id_especie_defecto'],
                    'duracion_minutos' => $validated['duracion_minutos_defecto'],
                ]);
            }

            return redirect()->route('servicios.index')->with('success', "Servicio '{$servicio->nombre}' registrado exitosamente.");
        });
    }

    /**
     * Formulario de edición de servicio y configuración de tiempos estimados por especie/peso/raza.
     */
    public function edit(Servicio $servicio): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('servicios')) {
            abort(403, 'No tienes permisos para acceder al catálogo de servicios.');
        }

        $servicio->load(['tiemposEstimados.especie', 'tiemposEstimados.ajustesTiempoRaza.raza']);
        $especies = Especie::with('razas')->get();

        return view('servicios.edit', compact('servicio', 'especies'));
    }

    /**
     * Actualiza los datos generales de un servicio.
     */
    public function update(Request $request, Servicio $servicio): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('servicios')) {
            abort(403, 'No tienes permisos para acceder al catálogo de servicios.');
        }

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'categoria' => ['required', 'string', 'in:VETERINARIA,ESTETICA,CIRUGIA,LABORATORIO,OTRO'],
            'precio_base' => ['required', 'numeric', 'min:0'],
            'disponible_domicilio' => ['nullable', 'boolean'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'observaciones' => ['nullable', 'string', 'max:500'],
            'estado' => ['required', 'string', 'in:ACTIVO,INACTIVO'],
        ]);

        $servicio->update([
            'nombre' => $validated['nombre'],
            'categoria' => $validated['categoria'],
            'precio_base' => $validated['precio_base'],
            'disponible_domicilio' => ! empty($validated['disponible_domicilio']),
            'descripcion' => $validated['descripcion'] ?? null,
            'observaciones' => $validated['observaciones'] ?? null,
            'estado' => $validated['estado'],
        ]);

        return redirect()->route('servicios.index')->with('success', "Servicio '{$servicio->nombre}' actualizado con éxito.");
    }

    /**
     * Switch rápido de activación / desactivación.
     */
    public function toggleEstado(Servicio $servicio): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('servicios')) {
            abort(403, 'No tienes permisos para modificar el catálogo de servicios.');
        }

        $nuevoEstado = ($servicio->estado === 'ACTIVO') ? 'INACTIVO' : 'ACTIVO';
        $servicio->update(['estado' => $nuevoEstado]);

        return redirect()->back()->with('success', "El servicio '{$servicio->nombre}' ahora está {$nuevoEstado}.");
    }

    /**
     * Agrega una regla de duración estimada para el servicio según especie y rango de peso.
     */
    public function storeTiempoEstimado(Request $request, Servicio $servicio): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('servicios')) {
            abort(403, 'No tienes permisos para configurar tiempos estimados.');
        }

        $validated = $request->validate([
            'id_especie' => ['required', 'integer', 'exists:especies,id_especie'],
            'peso_min' => ['nullable', 'numeric', 'min:0'],
            'peso_max' => ['nullable', 'numeric', 'min:0'],
            'duracion_minutos' => ['required', 'integer', 'min:5', 'max:480'],
        ]);

        TiempoEstimado::create([
            'id_servicio' => $servicio->id_servicio,
            'id_especie' => $validated['id_especie'],
            'peso_min' => $validated['peso_min'] ?? null,
            'peso_max' => $validated['peso_max'] ?? null,
            'duracion_minutos' => $validated['duracion_minutos'],
        ]);

        return redirect()->route('servicios.edit', $servicio->id_servicio)->with('success', 'Regla de tiempo estimado agregada correctamente.');
    }

    /**
     * Elimina una regla de tiempo estimado.
     */
    public function deleteTiempoEstimado(TiempoEstimado $tiempo): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('servicios')) {
            abort(403, 'No tienes permisos para configurar tiempos estimados.');
        }

        $idServicio = $tiempo->id_servicio;
        $tiempo->delete();

        return redirect()->route('servicios.edit', $idServicio)->with('success', 'Regla de tiempo eliminada.');
    }
}
