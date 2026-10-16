<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Paciente;
use App\Models\Propietario;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BuscadorWebController extends Controller
{
    /**
     * Búsqueda global en clientes, mascotas y citas.
     */
    public function buscar(Request $request): JsonResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        $termino = trim($request->input('q', ''));

        if (strlen($termino) < 2) {
            return response()->json(['clientes' => [], 'mascotas' => [], 'citas' => []]);
        }

        $resultados = [];

        // SEC: Buscar Clientes solo si el usuario tiene permiso al módulo
        if ($currentUser->canAccessModule('clientes')) {
            $clientes = Propietario::with('persona')
                ->whereHas('persona', function ($q) use ($termino) {
                    $q->where('nombre', 'ilike', "%{$termino}%")
                        ->orWhere('apellido1', 'ilike', "%{$termino}%")
                        ->orWhere('apellido2', 'ilike', "%{$termino}%")
                        ->orWhere('documento', 'ilike', "%{$termino}%");
                })
                ->limit(5)
                ->get()
                ->map(function ($prop) {
                    $p = $prop->persona;

                    return [
                        'id' => $prop->id_persona,
                        'nombre' => trim("{$p->nombre} {$p->apellido1} {$p->apellido2}"),
                        'documento' => $p->documento ? substr($p->documento, 0, 3).'****' : null,
                        'telefono' => $p->telefono ? substr($p->telefono, 0, 3).'****' : null,
                        'url' => route('clientes.show', $prop->id_persona),
                    ];
                });
        } else {
            $clientes = collect();
        }
        $resultados['clientes'] = $clientes;

        // Buscar Mascotas / Pacientes
        $mascotas = Paciente::with(['raza.especie', 'propietario.persona'])
            ->where(function ($q) use ($termino) {
                $q->where('nombre', 'ilike', "%{$termino}%")
                    ->orWhere('microchip', 'ilike', "%{$termino}%")
                    ->orWhereHas('raza', function ($qr) use ($termino) {
                        $qr->where('nombre', 'ilike', "%{$termino}%");
                    });
            })
            ->limit(5)
            ->get()
            ->map(function ($pac) {
                $persona = $pac->propietario?->persona;

                return [
                    'id' => $pac->id_paciente,
                    'nombre' => $pac->nombre,
                    'especie' => $pac->raza?->especie?->nombre ?? 'S/D',
                    'raza' => $pac->raza?->nombre ?? 'Mestizo',
                    'propietario' => $persona ? trim("{$persona->nombre} {$persona->apellido1}") : 'Sin propietario',
                    'estado' => $pac->estado,
                    'foto' => $pac->foto ? asset('storage/'.$pac->foto) : null,
                    'url' => route('mascotas.show', $pac->id_paciente),
                ];
            });
        $resultados['mascotas'] = $mascotas;

        // Buscar Citas (por fecha o nombre de paciente)
        if ($currentUser->canAccessModule('citas')) {
            $citas = Cita::with(['paciente.propietario.persona', 'profesional.persona'])
                ->where(function ($q) use ($termino) {
                    $q->whereHas('paciente', function ($qp) use ($termino) {
                        $qp->where('nombre', 'ilike', "%{$termino}%");
                    })
                        ->orWhereHas('paciente.propietario.persona', function ($qpr) use ($termino) {
                            $qpr->where('nombre', 'ilike', "%{$termino}%")
                                ->orWhere('apellido1', 'ilike', "%{$termino}%");
                        });
                })
                ->orderBy('fecha_hora_inicio', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($cita) {
                    return [
                        'id' => $cita->id_cita,
                        'paciente' => $cita->paciente?->nombre ?? 'N/A',
                        'fecha' => $cita->fecha_hora_inicio?->format('d/m/Y H:i'),
                        'estado' => $cita->estado,
                        'profesional' => $cita->profesional?->persona
                            ? trim("{$cita->profesional->persona->nombre} {$cita->profesional->persona->apellido1}")
                            : 'Sin asignar',
                        'url' => route('citas.index'),
                    ];
                });
            $resultados['citas'] = $citas;
        } else {
            $resultados['citas'] = [];
        }

        return response()->json([
            'query' => $termino,
            'total' => count($clientes) + count($mascotas) + count($citas),
            'clientes' => $clientes,
            'mascotas' => $mascotas,
            'citas' => $citas,
        ]);
    }
}
