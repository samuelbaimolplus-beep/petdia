<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Cuenta;
use App\Models\DetallePago;
use App\Models\DetalleVenta;
use App\Models\MetodoPago;
use App\Models\MovimientoInventario;
use App\Models\Pago;
use App\Models\PagoVenta;
use App\Models\Producto;
use App\Models\Propietario;
use App\Models\Usuario;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PetShopWebController extends Controller
{
    /**
     * Catálogo del Pet Shop con cuadrícula/tabla, buscador, filtros, KPIs y carrito interactivo.
     */
    public function index(Request $request): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('petshop')) {
            abort(403, 'No tienes permisos para acceder al módulo de Pet Shop.');
        }

        $query = Producto::withSum('movimientoInventarios as stock_calculado', 'cantidad')
            ->orderBy('nombre');

        // Búsqueda por nombre, marca o código de barras
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'ilike', "%{$search}%")
                    ->orWhere('marca', 'ilike', "%{$search}%")
                    ->orWhere('descripcion', 'ilike', "%{$search}%")
                    ->orWhere('codigo_barra', 'ilike', "%{$search}%");
            });
        }

        // Filtro por Categoría
        if ($categoria = $request->input('categoria')) {
            $query->where('categoria', $categoria);
        }

        // Filtro por Estado
        if ($estado = $request->input('estado')) {
            $query->where('estado', $estado);
        } else {
            $query->where('estado', 'ACTIVO');
        }

        // Solo productos con stock
        if ($request->boolean('solo_con_stock')) {
            $query->has('movimientoInventarios');
        }

        $productos = $query->paginate(24)->withQueryString();

        // Categorías existentes para filtro
        $categorias = Producto::whereNotNull('categoria')
            ->distinct()
            ->pluck('categoria')
            ->sort()
            ->values();

        // Métodos de pago para venta rápida
        $metodosPago = MetodoPago::where('estado', 'ACTIVO')->get();

        // Clientes / Propietarios para asignar la venta
        $propietarios = Propietario::with('persona')
            ->limit(50)
            ->get();

        // KPIs del Pet Shop
        $totalProductos = Producto::where('estado', 'ACTIVO')->count();
        $ventasHoy = Venta::whereDate('fecha_hora', Carbon::today())->count();
        $ingresosVentasHoy = (float) Venta::whereDate('fecha_hora', Carbon::today())
            ->where('estado', '!=', 'ANULADA')
            ->sum('total');

        // Alertas de stock bajo
        $productosAlerta = Producto::withSum('movimientoInventarios as stock_calculado', 'cantidad')
            ->where('estado', 'ACTIVO')
            ->get()
            ->filter(fn ($p) => ($p->stock_calculado ?? 0) <= ($p->stock_minimo ?? 5))
            ->count();

        return view('petshop.index', compact(
            'productos',
            'categorias',
            'metodosPago',
            'propietarios',
            'totalProductos',
            'ventasHoy',
            'ingresosVentasHoy',
            'productosAlerta'
        ));
    }

    /**
     * Formulario de creación de producto de Pet Shop.
     */
    public function create(): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('petshop')) {
            abort(403, 'No tienes permisos para acceder al módulo de Pet Shop.');
        }

        $categorias = Producto::whereNotNull('categoria')->distinct()->pluck('categoria')->sort()->values();

        return view('petshop.create', compact('categorias'));
    }

    /**
     * Almacena un nuevo producto en catálogo.
     */
    public function store(Request $request): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('petshop')) {
            abort(403, 'No tienes permisos para acceder al módulo de Pet Shop.');
        }

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'tipo' => ['nullable', 'string', 'max:50'],
            'categoria' => ['required', 'string', 'max:50'],
            'marca' => ['nullable', 'string', 'max:50'],
            'unidad_medida' => ['nullable', 'string', 'max:20'],
            'precio_venta' => ['required', 'numeric', 'min:0'],
            'stock_minimo' => ['nullable', 'integer', 'min:0'],
            'stock_inicial' => ['nullable', 'integer', 'min:0'],
            'codigo_barra' => ['nullable', 'string', 'max:100', 'unique:productos,codigo_barra'],
            'descripcion' => ['nullable', 'string', 'max:500'],
        ]);

        return DB::transaction(function () use ($validated, $currentUser) {
            $producto = Producto::create([
                'nombre' => $validated['nombre'],
                'tipo' => $validated['tipo'] ?? 'PETSHOP',
                'categoria' => $validated['categoria'],
                'marca' => $validated['marca'] ?? null,
                'unidad_medida' => $validated['unidad_medida'] ?? 'UNIDAD',
                'precio_venta' => $validated['precio_venta'],
                'stock_minimo' => $validated['stock_minimo'] ?? 5,
                'codigo_barra' => ! empty($validated['codigo_barra']) ? trim($validated['codigo_barra']) : null,
                'descripcion' => $validated['descripcion'] ?? null,
                'estado' => 'ACTIVO',
                'control_lotes' => false,
            ]);

            // Si se especificó un stock inicial de apertura
            if (! empty($validated['stock_inicial']) && $validated['stock_inicial'] > 0) {
                MovimientoInventario::create([
                    'id_producto' => $producto->id_producto,
                    'id_usuario' => $currentUser->id_usuario,
                    'fecha_hora' => Carbon::now(),
                    'tipo_movimiento' => 'ENTRADA',
                    'cantidad' => $validated['stock_inicial'],
                    'motivo' => 'INVENTARIO_INICIAL',
                ]);
            }

            return redirect()->route('petshop.index')->with('success', "Producto '{$producto->nombre}' registrado correctamente.");
        });
    }

    /**
     * Formulario de edición de producto.
     */
    public function edit(Producto $producto): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('petshop')) {
            abort(403, 'No tienes permisos para acceder al módulo de Pet Shop.');
        }

        $categorias = Producto::whereNotNull('categoria')->distinct()->pluck('categoria')->sort()->values();

        return view('petshop.edit', compact('producto', 'categorias'));
    }

    /**
     * Actualiza los datos del producto.
     */
    public function update(Request $request, Producto $producto): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('petshop')) {
            abort(403, 'No tienes permisos para acceder al módulo de Pet Shop.');
        }

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'categoria' => ['required', 'string', 'max:50'],
            'marca' => ['nullable', 'string', 'max:50'],
            'unidad_medida' => ['nullable', 'string', 'max:20'],
            'precio_venta' => ['required', 'numeric', 'min:0'],
            'stock_minimo' => ['nullable', 'integer', 'min:0'],
            'codigo_barra' => ['nullable', 'string', 'max:100', 'unique:productos,codigo_barra,'.$producto->id_producto.',id_producto'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'estado' => ['required', 'string', 'in:ACTIVO,INACTIVO'],
        ]);

        $validated['codigo_barra'] = ! empty($validated['codigo_barra']) ? trim($validated['codigo_barra']) : null;
        $producto->update($validated);

        return redirect()->route('petshop.index')->with('success', "Producto '{$producto->nombre}' actualizado con éxito.");
    }

    /**
     * Procesa una venta rápida desde el carrito del Pet Shop y emite el cobro contable.
     */
    public function procesarVenta(Request $request): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('petshop')) {
            abort(403, 'No tienes permisos para acceder al módulo de Pet Shop.');
        }

        $validated = $request->validate([
            'id_propietario' => ['nullable', 'integer', 'exists:propietarios,id_persona'],
            'id_metodo_pago' => ['required', 'integer', 'exists:metodos_pago,id_metodo_pago'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id_producto' => ['required', 'integer', 'exists:productos,id_producto'],
            'items.*.cantidad' => ['required', 'numeric', 'min:1'],
            // SEC: precio_unitario se obtiene de la BD, no del formulario
            'items.*.descuento' => ['nullable', 'numeric', 'min:0'],
            'descuento_general' => ['nullable', 'numeric', 'min:0'],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ]);

        return DB::transaction(function () use ($validated, $currentUser) {
            $totalCalculado = 0;

            foreach ($validated['items'] as $item) {
                // SEC: obtener precio real de la base de datos, no del formulario
                $producto = Producto::findOrFail($item['id_producto']);
                $precioUnitario = (float) $producto->precio_venta;

                $sub = ($item['cantidad'] * $precioUnitario) - ($item['descuento'] ?? 0);
                $totalCalculado += max(0, $sub);
            }

            if (! empty($validated['descuento_general'])) {
                $totalCalculado = max(0, $totalCalculado - (float) $validated['descuento_general']);
            }

            // 1. Crear Venta
            $venta = Venta::create([
                'id_usuario' => $currentUser->id_usuario,
                'id_propietario' => $validated['id_propietario'] ?? null,
                'fecha_hora' => Carbon::now(),
                'estado' => 'COMPLETADA',
                'total' => $totalCalculado,
                'observaciones' => $validated['observaciones'] ?? 'Venta rápida Pet Shop',
            ]);

            // 2. Registrar Detalle de Venta y Movimientos de Inventario (Salida)
            foreach ($validated['items'] as $item) {
                // SEC: precio real desde la BD
                $producto = Producto::findOrFail($item['id_producto']);

                $detalle = DetalleVenta::create([
                    'id_venta' => $venta->id_venta,
                    'id_producto' => $item['id_producto'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $producto->precio_venta,
                    'descuento' => $item['descuento'] ?? 0,
                ]);

                // Descuento en inventario
                MovimientoInventario::create([
                    'id_producto' => $item['id_producto'],
                    'id_usuario' => $currentUser->id_usuario,
                    'id_detalle_venta' => $detalle->id_detalle_venta,
                    'fecha_hora' => Carbon::now(),
                    'tipo_movimiento' => 'SALIDA',
                    'cantidad' => -abs((float) $item['cantidad']),
                    'motivo' => 'VENTA_PETSHOP',
                ]);
            }

            // 3. Crear Cuenta para el cliente si no tiene y registrar cobro en Caja
            $cuenta = null;
            if (! empty($validated['id_propietario'])) {
                $cuenta = Cuenta::firstOrCreate(
                    ['id_propietario' => $validated['id_propietario']],
                    ['fecha_apertura' => Carbon::now()->toDateString(), 'estado' => 'ACTIVA']
                );
            }

            // 4. Registrar Pago en Caja
            $pago = Pago::create([
                'id_cuenta' => $cuenta?->id_cuenta,
                'fecha_hora' => Carbon::now(),
                'estado' => 'COMPLETADO',
                'observaciones' => "Venta Pet Shop #{$venta->id_venta}",
            ]);

            DetallePago::create([
                'id_pago' => $pago->id_pago,
                'id_metodo_pago' => $validated['id_metodo_pago'],
                'monto' => $totalCalculado,
            ]);

            PagoVenta::create([
                'id_pago' => $pago->id_pago,
                'id_venta' => $venta->id_venta,
                'monto_aplicado' => $totalCalculado,
            ]);

            return redirect()->route('petshop.index')->with('success', "Venta #{$venta->id_venta} procesada exitosamente. Recibo de caja generado por \${$totalCalculado}.");
        });
    }

    /**
     * Historial de ventas de Pet Shop con tickets y detalle.
     */
    public function historialVentas(Request $request): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('petshop')) {
            abort(403, 'No tienes permisos para acceder al módulo de Pet Shop.');
        }

        $query = Venta::with(['propietario.persona', 'usuario.persona', 'detalleVentas.producto', 'pagoVentas.pago.detallePagos.metodoPago'])
            ->orderByDesc('id_venta');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('id_venta', 'ilike', "%{$search}%")
                    ->orWhereHas('propietario.persona', function ($qp) use ($search) {
                        $qp->where('nombre', 'ilike', "%{$search}%")
                            ->orWhere('apellido1', 'ilike', "%{$search}%");
                    });
            });
        }

        if ($fecha = $request->input('fecha')) {
            $query->whereDate('fecha_hora', $fecha);
        }

        $ventas = $query->paginate(15)->withQueryString();

        return view('petshop.ventas', compact('ventas'));
    }

    /**
     * Búsqueda rápida por código de barras para pistola lectora o entrada rápida.
     */
    public function buscarPorCodigo(Request $request): JsonResponse
    {
        $codigo = trim($request->input('codigo', ''));
        if (empty($codigo)) {
            return response()->json(['success' => false, 'message' => 'Código no proporcionado.'], 400);
        }

        $producto = Producto::withSum('movimientoInventarios as stock_calculado', 'cantidad')
            ->where(function ($q) use ($codigo) {
                $q->where('codigo_barra', $codigo);
                if (is_numeric($codigo)) {
                    $q->orWhere('id_producto', (int) $codigo);
                }
            })
            ->where('estado', 'ACTIVO')
            ->first();

        if (! $producto) {
            return response()->json([
                'success' => false,
                'message' => "Producto con código '{$codigo}' no encontrado.",
            ], 404);
        }

        return response()->json([
            'success' => true,
            'producto' => [
                'id_producto' => $producto->id_producto,
                'nombre' => $producto->nombre,
                'precio_venta' => (float) $producto->precio_venta,
                'stock' => (int) ($producto->stock_calculado ?? 0),
                'codigo_barra' => $producto->codigo_barra,
            ],
        ]);
    }
}
