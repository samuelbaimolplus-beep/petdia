<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AtencionResource;
use App\Models\Atencion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AtencionController extends Controller
{
    /**
     * Lista de atenciones (filtrable por paciente).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $atenciones = Atencion::with('paciente', 'atencionVeterinaria', 'atencionEstetica')
            ->when(
                $request->filled('paciente_id'),
                fn ($query) => $query->where('id_paciente', $request->integer('paciente_id'))
            )
            ->orderByDesc('fecha_hora_inicio')
            ->paginate(15);

        return AtencionResource::collection($atenciones);
    }

    /**
     * Historial clínico completo de una atención.
     */
    public function show(Atencion $atencion): AtencionResource
    {
        $atencion->load([
            'paciente.raza',
            'atencionVeterinaria.diagnosticos',
            'atencionVeterinaria.recetas.detalleRecetas.medicamento.producto',
            'atencionVeterinaria.procedimientos.cirugia',
            'atencionEstetica',
            'atencionServicios.servicio',
            'participaciones.profesional.persona',
        ]);

        return new AtencionResource($atencion);
    }
}
