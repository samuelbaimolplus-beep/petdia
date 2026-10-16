<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\Lote;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CompraWebController extends Controller
{
    /**
     * Historial de compras a proveedores con KPIs y filtros.
     */
    public function index(Request $request): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('compras')) {
            abort(403, 'No tienes permisos para acceder al módulo de compras.');
        }

        $query = Compra::with(['proveedor', 'detalleCompras.producto'])->orderByDesc('id_compra');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('id_compra', 'ilike', "%{$search}%")
                    ->orWhere('observaciones', 'ilike', "%{$search}%")
                    ->orWhereHas('proveedor', function ($qp) use ($search) {
                        $qp->where('nombre', 'ilike', "%{$search}%")
                            ->orWhere('nit', 'ilike', "%{$search}%");
                    });
            });
        }

        if ($idProveedor = $request->input('id_proveedor')) {
            $query->where('id_proveedor', $idProveedor);
        }

        if ($fecha = $request->input('fecha')) {
            $query->whereDate('fecha_hora', $fecha);
        }

        $compras = $query->paginate(15)->withQueryString();
        $proveedores = Proveedor::where('estado', 'ACTIVO')->orWhereNull('estado')->orderBy('nombre')->get();

        // KPIs
        $totalComprasMes = (float) Compra::whereMonth('fecha_hora', Carbon::now()->month)
            ->whereYear('fecha_hora', Carbon::now()->year)
            ->sum('total');
        $cantidadComprasMes = Compra::whereMonth('fecha_hora', Carbon::now()->month)->count();
        $proveedoresActivos = Proveedor::where('estado', 'ACTIVO')->orWhereNull('estado')->count();
        $ultimaCompra = Compra::latest('id_compra')->first();

        return view('compras.index', compact(
            'compras',
            'proveedores',
            'totalComprasMes',
            'cantidadComprasMes',
            'proveedoresActivos',
            'ultimaCompra'
        ));
    }

    /**
     * Formulario interactivo para registrar una compra a proveedor.
     */
    public function create(): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('compras')) {
            abort(403, 'No tienes permisos para acceder al módulo de compras.');
        }

        $proveedores = Proveedor::where('estado', 'ACTIVO')->orWhereNull('estado')->orderBy('nombre')->get();
        $productos = Producto::withSum('movimientoInventarios as stock_actual', 'cantidad')
            ->where('estado', 'ACTIVO')
            ->orderBy('nombre')
            ->get();

        return view('compras.create', compact('proveedores', 'productos'));
    }

    /**
     * Registra la compra e impacta de inmediato el inventario mediante movimientos de ENTRADA.
     */
    public function store(Request $request): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('compras')) {
            abort(403, 'No tienes permisos para acceder al módulo de compras.');
        }

        $validated = $request->validate([
            'id_proveedor' => ['required', 'integer', 'exists:proveedores,id_proveedor'],
            'fecha' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id_producto' => ['required', 'integer', 'exists:productos,id_producto'],
            'items.*.cantidad' => ['required', 'numeric', 'min:1'],
            'items.*.precio_unitario' => ['required', 'numeric', 'min:0'],
            'items.*.lote' => ['nullable', 'string', 'max:50'],
            'items.*.fecha_vencimiento' => ['nullable', 'date'],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ]);

        return DB::transaction(function () use ($validated, $currentUser) {
            $totalCompra = 0;
            foreach ($validated['items'] as $item) {
                $totalCompra += ((float) $item['cantidad'] * (float) $item['precio_unitario']);
            }

            // 1. Crear Compra
            $compra = Compra::create([
                'id_proveedor' => $validated['id_proveedor'],
                'fecha_hora' => Carbon::parse($validated['fecha'])->setTimeFrom(Carbon::now()),
                'estado' => 'COMPLETADA',
                'total' => $totalCompra,
                'observaciones' => $validated['observaciones'] ?? 'Compra registrada en sistema',
            ]);

            // 2. Registrar Detalle e Impactar Stock
            foreach ($validated['items'] as $item) {
                $detalle = DetalleCompra::create([
                    'id_compra' => $compra->id_compra,
                    'id_producto' => $item['id_producto'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                ]);

                $idLote = null;
                if (! empty($item['lote'])) {
                    $lote = Lote::create([
                        'id_producto' => $item['id_producto'],
                        'id_detalle_compra' => $detalle->id_detalle_compra,
                        'numero_lote' => $item['lote'],
                        'fecha_vencimiento' => $item['fecha_vencimiento'] ?? null,
                        'estado' => 'ACTIVO',
                    ]);
                    $idLote = $lote->id_lote;
                }

                // Impacto en Inventario: Movimiento de ENTRADA
                MovimientoInventario::create([
                    'id_producto' => $item['id_producto'],
                    'id_lote' => $idLote,
                    'id_usuario' => $currentUser->id_usuario,
                    'id_detalle_compra' => $detalle->id_detalle_compra,
                    'fecha_hora' => Carbon::now(),
                    'tipo_movimiento' => 'ENTRADA',
                    'cantidad' => (float) $item['cantidad'],
                    'motivo' => 'COMPRA_PROVEEDOR',
                ]);
            }

            return redirect()->route('compras.index')->with('success', "Compra #{$compra->id_compra} registrada exitosamente. Se incrementó el stock en inventario.");
        });
    }

    /**
     * Muestra el detalle de una compra con su impacto en inventario.
     */
    public function show(Compra $compra): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('compras')) {
            abort(403, 'No tienes permisos para acceder al módulo de compras.');
        }

        $compra->load(['proveedor', 'detalleCompras.producto.movimientoInventarios', 'detalleCompras.lotes']);

        return view('compras.show', compact('compra'));
    }

    /**
     * Directorio de Proveedores.
     */
    public function proveedores(Request $request): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('compras')) {
            abort(403, 'No tienes permisos para acceder a proveedores.');
        }

        $query = Proveedor::withCount('compras')->orderBy('nombre');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'ilike', "%{$search}%")
                    ->orWhere('nit', 'ilike', "%{$search}%")
                    ->orWhere('telefono', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        $proveedores = $query->paginate(15)->withQueryString();

        return view('compras.proveedores', compact('proveedores'));
    }

    /**
     * Guarda un nuevo proveedor.
     */
    public function storeProveedor(Request $request): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('compras')) {
            abort(403, 'No tienes permisos para gestionar proveedores.');
        }

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'nit' => ['nullable', 'string', 'max:50'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:150'],
            'direccion' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['estado'] = 'ACTIVO';
        Proveedor::create($validated);

        return redirect()->route('compras.proveedores')->with('success', 'Proveedor registrado correctamente.');
    }

    /**
     * Actualiza los datos de un proveedor.
     */
    public function updateProveedor(Request $request, Proveedor $proveedor): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('compras')) {
            abort(403, 'No tienes permisos para gestionar proveedores.');
        }

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'nit' => ['nullable', 'string', 'max:50'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:150'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'estado' => ['required', 'string', 'in:ACTIVO,INACTIVO'],
        ]);

        $proveedor->update($validated);

        return redirect()->route('compras.proveedores')->with('success', 'Proveedor actualizado con éxito.');
    }
}
