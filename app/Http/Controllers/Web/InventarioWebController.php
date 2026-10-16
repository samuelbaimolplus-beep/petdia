<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Lote;
use App\Models\Medicamento;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InventarioWebController extends Controller
{
    private function checkAccess(): void
    {
        $user = Auth::user();
        if (! $user || ! $user->canAccessModule('inventario')) {
            abort(403, 'No tienes permisos para acceder al módulo de Inventario.');
        }
    }

    private function canEdit(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        $activeRole = ((app()->isLocal() || app()->runningUnitTests()) && session()->has('demo_role'))
            ? strtoupper(str_replace(['-', ' '], '_', session('demo_role')))
            : strtoupper(str_replace(['-', ' '], '_', $user->rol_principal));

        return in_array($activeRole, ['SUPER_ADMIN', 'SUPER-ADMIN', 'ADMIN'], true);
    }

    public function index(Request $request): View
    {
        $this->checkAccess();

        $query = Producto::with(['medicamento', 'lotes'])
            ->withSum('movimientoInventarios as stock_calculado', 'cantidad');

        if ($request->filled('buscar')) {
            $search = trim($request->input('buscar'));
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('marca', 'like', "%{$search}%")
                    ->orWhereHas('medicamento', function ($mq) use ($search) {
                        $mq->where('principio_activo', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->input('tipo'));
        }

        if ($request->filled('categoria')) {
            $query->where('categoria', $request->input('categoria'));
        }

        $productos = $query->orderBy('nombre')->paginate(15)->withQueryString();

        // Calculate statuses & nearest expiration for each
        $now = Carbon::now();
        $in30Days = Carbon::now()->addDays(30);

        foreach ($productos as $producto) {
            $producto->stock_actual = (float) ($producto->stock_calculado ?? 0);
            $producto->es_stock_bajo = $producto->stock_actual <= (float) $producto->stock_minimo;

            $activeLotes = $producto->lotes->where('estado', '!=', 'INACTIVO');
            $nearestExp = $activeLotes->whereNotNull('fecha_vencimiento')->sortBy('fecha_vencimiento')->first();

            $producto->lote_proximo = $nearestExp;
            $producto->es_por_vencer = false;
            $producto->es_vencido = false;

            if ($nearestExp && $nearestExp->fecha_vencimiento) {
                $expDate = Carbon::parse($nearestExp->fecha_vencimiento);
                if ($expDate->isPast()) {
                    $producto->es_vencido = true;
                } elseif ($expDate->lte($in30Days)) {
                    $producto->es_por_vencer = true;
                }
            }
        }

        // Global metrics
        $allProducts = Producto::with('lotes')
            ->withSum('movimientoInventarios as stock_calculado', 'cantidad')
            ->get();

        $totalProductos = $allProducts->count();
        $stockBajoCount = $allProducts->filter(function ($p) {
            return ((float) ($p->stock_calculado ?? 0)) <= ((float) $p->stock_minimo);
        })->count();

        $porVencerCount = Lote::whereNotNull('fecha_vencimiento')
            ->whereBetween('fecha_vencimiento', [$now->toDateString(), $in30Days->toDateString()])
            ->where('estado', '!=', 'INACTIVO')
            ->count();

        $movimientosMesCount = MovimientoInventario::whereMonth('fecha_hora', $now->month)
            ->whereYear('fecha_hora', $now->year)
            ->count();

        $canEdit = $this->canEdit();

        return view('inventario.index', compact(
            'productos',
            'totalProductos',
            'stockBajoCount',
            'porVencerCount',
            'movimientosMesCount',
            'canEdit'
        ));
    }

    public function storeProducto(Request $request): RedirectResponse
    {
        $this->checkAccess();
        if (! $this->canEdit()) {
            abort(403, 'No tienes permisos para crear productos.');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'tipo' => 'required|string|in:MEDICAMENTO,INSUMO,ACCESORIO,HERRAMIENTA',
            'categoria' => 'required|string|in:VETERINARIA,ESTETICA',
            'marca' => 'nullable|string|max:100',
            'unidad_medida' => 'required|string|max:50',
            'descripcion' => 'nullable|string',
            'precio_venta' => 'required|numeric|min:0',
            'stock_minimo' => 'required|numeric|min:0',
            'control_lotes' => 'nullable|boolean',
            // Medicamento fields
            'principio_activo' => 'nullable|string|max:150',
            'presentacion' => 'nullable|string|max:100',
            'concentracion' => 'nullable|numeric|min:0',
            'unidad_concentracion' => 'nullable|string|max:50',
            // Initial batch / stock optional
            'lote_numero' => 'nullable|string|max:50',
            'lote_vencimiento' => 'nullable|date',
            'stock_inicial' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $producto = Producto::create([
                'nombre' => $validated['nombre'],
                'tipo' => $validated['tipo'],
                'categoria' => $validated['categoria'],
                'marca' => $validated['marca'] ?? null,
                'unidad_medida' => $validated['unidad_medida'],
                'descripcion' => $validated['descripcion'] ?? null,
                'precio_venta' => $validated['precio_venta'],
                'stock_minimo' => $validated['stock_minimo'],
                'control_lotes' => $request->boolean('control_lotes'),
                'estado' => 'ACTIVO',
            ]);

            if ($validated['tipo'] === 'MEDICAMENTO' || ! empty($validated['principio_activo'])) {
                Medicamento::create([
                    'id_producto' => $producto->id_producto,
                    'principio_activo' => $validated['principio_activo'] ?? $producto->nombre,
                    'presentacion' => $validated['presentacion'] ?? 'Unidad',
                    'concentracion' => $validated['concentracion'] ?? null,
                    'unidad_concentracion' => $validated['unidad_concentracion'] ?? null,
                ]);
            }

            // Initial stock if provided
            if (! empty($validated['stock_inicial']) && $validated['stock_inicial'] > 0) {
                $idLote = null;
                if (! empty($validated['lote_numero'])) {
                    $lote = Lote::create([
                        'id_producto' => $producto->id_producto,
                        'numero_lote' => $validated['lote_numero'],
                        'fecha_vencimiento' => $validated['lote_vencimiento'] ?? null,
                        'estado' => 'ACTIVO',
                    ]);
                    $idLote = $lote->id_lote;
                }

                MovimientoInventario::create([
                    'id_producto' => $producto->id_producto,
                    'id_lote' => $idLote,
                    'id_usuario' => Auth::user()->id_usuario,
                    'fecha_hora' => Carbon::now(),
                    'tipo_movimiento' => 'ENTRADA',
                    'cantidad' => $validated['stock_inicial'],
                    'motivo' => 'INICIAL',
                ]);
            }

            DB::commit();

            return redirect()->route('inventario.index')
                ->with('success', "Producto '{$producto->nombre}' registrado exitosamente.");
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->with('error', 'Error al guardar el producto: '.$e->getMessage());
        }
    }

    public function updateProducto(Request $request, int $id): RedirectResponse
    {
        $this->checkAccess();
        if (! $this->canEdit()) {
            abort(403, 'No tienes permisos para modificar productos.');
        }

        $producto = Producto::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'tipo' => 'required|string|in:MEDICAMENTO,INSUMO,ACCESORIO,HERRAMIENTA',
            'categoria' => 'required|string|in:VETERINARIA,ESTETICA',
            'marca' => 'nullable|string|max:100',
            'unidad_medida' => 'required|string|max:50',
            'descripcion' => 'nullable|string',
            'precio_venta' => 'required|numeric|min:0',
            'stock_minimo' => 'required|numeric|min:0',
            'control_lotes' => 'nullable|boolean',
            'estado' => 'required|string|in:ACTIVO,INACTIVO',
            // Medicamento fields
            'principio_activo' => 'nullable|string|max:150',
            'presentacion' => 'nullable|string|max:100',
            'concentracion' => 'nullable|numeric|min:0',
            'unidad_concentracion' => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();
        try {
            $producto->update([
                'nombre' => $validated['nombre'],
                'tipo' => $validated['tipo'],
                'categoria' => $validated['categoria'],
                'marca' => $validated['marca'] ?? null,
                'unidad_medida' => $validated['unidad_medida'],
                'descripcion' => $validated['descripcion'] ?? null,
                'precio_venta' => $validated['precio_venta'],
                'stock_minimo' => $validated['stock_minimo'],
                'control_lotes' => $request->boolean('control_lotes'),
                'estado' => $validated['estado'],
            ]);

            if ($validated['tipo'] === 'MEDICAMENTO' || ! empty($validated['principio_activo'])) {
                Medicamento::updateOrCreate(
                    ['id_producto' => $producto->id_producto],
                    [
                        'principio_activo' => $validated['principio_activo'] ?? $producto->nombre,
                        'presentacion' => $validated['presentacion'] ?? 'Unidad',
                        'concentracion' => $validated['concentracion'] ?? null,
                        'unidad_concentracion' => $validated['unidad_concentracion'] ?? null,
                    ]
                );
            }

            DB::commit();

            return redirect()->route('inventario.index')
                ->with('success', "Producto '{$producto->nombre}' actualizado exitosamente.");
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->with('error', 'Error al actualizar el producto: '.$e->getMessage());
        }
    }

    public function movimientos(Request $request): View
    {
        $this->checkAccess();

        $query = MovimientoInventario::with(['producto', 'lote', 'usuario.persona'])
            ->orderBy('fecha_hora', 'desc');

        if ($request->filled('tipo_movimiento')) {
            $query->where('tipo_movimiento', $request->input('tipo_movimiento'));
        }

        if ($request->filled('motivo')) {
            $query->where('motivo', $request->input('motivo'));
        }

        if ($request->filled('id_producto')) {
            $query->where('id_producto', $request->input('id_producto'));
        }

        $movimientos = $query->paginate(20)->withQueryString();
        $productos = Producto::where('estado', 'ACTIVO')->orderBy('nombre')->get();
        $canEdit = $this->canEdit();

        return view('inventario.movimientos', compact('movimientos', 'productos', 'canEdit'));
    }

    public function storeMovimiento(Request $request): RedirectResponse
    {
        $this->checkAccess();
        if (! $this->canEdit()) {
            abort(403, 'No tienes permisos para registrar movimientos de inventario.');
        }

        $validated = $request->validate([
            'id_producto' => 'required|exists:productos,id_producto',
            'id_lote' => 'nullable|exists:lotes,id_lote',
            'tipo_movimiento' => 'required|in:ENTRADA,SALIDA',
            'cantidad' => 'required|numeric|gt:0',
            'motivo' => 'required|string|in:COMPRA,USO_ATENCION,VENTA,AJUSTE,BAJA_VENCIMIENTO,BAJA_DANO,INICIAL',
            'numero_lote' => 'nullable|string|max:50',
            'fecha_vencimiento' => 'nullable|date',
        ]);

        $idLote = $validated['id_lote'] ?? null;

        // If new batch specified on ENTRADA
        if ($validated['tipo_movimiento'] === 'ENTRADA' && ! empty($validated['numero_lote']) && empty($idLote)) {
            $lote = Lote::create([
                'id_producto' => $validated['id_producto'],
                'numero_lote' => $validated['numero_lote'],
                'fecha_vencimiento' => $validated['fecha_vencimiento'] ?? null,
                'estado' => 'ACTIVO',
            ]);
            $idLote = $lote->id_lote;
        }

        // In database convention, ENTRADA is positive, SALIDA is stored as negative
        $cantidadFinal = $validated['tipo_movimiento'] === 'ENTRADA'
            ? abs($validated['cantidad'])
            : -abs($validated['cantidad']);

        MovimientoInventario::create([
            'id_producto' => $validated['id_producto'],
            'id_lote' => $idLote,
            'id_usuario' => Auth::user()->id_usuario,
            'fecha_hora' => Carbon::now(),
            'tipo_movimiento' => $validated['tipo_movimiento'],
            'cantidad' => $cantidadFinal,
            'motivo' => $validated['motivo'],
        ]);

        return back()->with('success', 'Movimiento de inventario registrado correctamente.');
    }

    public function alertas(): View
    {
        $this->checkAccess();

        $now = Carbon::now();
        $in30Days = Carbon::now()->addDays(30);

        // Low stock products
        $productosStockBajo = Producto::with('lotes')
            ->withSum('movimientoInventarios as stock_calculado', 'cantidad')
            ->get()
            ->filter(function ($p) {
                return ((float) ($p->stock_calculado ?? 0)) <= ((float) $p->stock_minimo);
            })
            ->values();

        // Expiring or expired lots
        $lotesAlertas = Lote::with('producto')
            ->whereNotNull('fecha_vencimiento')
            ->where('fecha_vencimiento', '<=', $in30Days->toDateString())
            ->where('estado', '!=', 'INACTIVO')
            ->orderBy('fecha_vencimiento')
            ->get();

        $canEdit = $this->canEdit();

        return view('inventario.alertas', compact('productosStockBajo', 'lotesAlertas', 'canEdit'));
    }
}
