<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Cirugia;
use App\Models\Paciente;
use App\Models\Pago;
use App\Models\Receta;
use App\Models\Usuario;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class DocumentoPdfController extends Controller
{
    /**
     * 2.1 — Genera el PDF de la Receta Médica.
     */
    public function recetaPdf(int $id): Response
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();

        $receta = Receta::with([
            'atencionVeterinaria.atencion.paciente.propietario.persona',
            'atencionVeterinaria.atencion.profesionales.persona',
            'detalleRecetas.medicamento',
        ])->findOrFail($id);

        $atencion = $receta->atencionVeterinaria?->atencion;
        $paciente = $atencion?->paciente;
        $propietario = $paciente?->propietario?->persona;
        $profesional = $atencion?->profesional ?? $currentUser;
        $detalles = $receta->detalleRecetas ?? collect();

        $isStaff = $currentUser->hasRole(['SUPER_ADMIN', 'ADMIN', 'VETERINARIO']);
        $isOwner = $paciente && $paciente->id_propietario === $currentUser->id_persona;

        if (! $isStaff && ! $isOwner) {
            abort(403, 'Acceso denegado: solo personal médico o el propietario de la mascota pueden consultar esta receta.');
        }

        $pdf = Pdf::loadView('pdf.receta', [
            'receta' => $receta,
            'atencion' => $atencion,
            'paciente' => $paciente,
            'propietario' => $propietario,
            'profesional' => $profesional,
            'detalles' => $detalles,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream("receta-{$receta->id_receta}.pdf");
    }

    /**
     * 2.2 — Genera el Comprobante de Pago / Ticket de Caja.
     */
    public function comprobantePagoPdf(int $id): Response
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();

        if (! $currentUser->canAccessModule('caja')) {
            abort(403, 'Acceso denegado al módulo de caja.');
        }

        $pago = Pago::with([
            'cuenta.propietario.persona',
            'detallePagos.metodoPago',
            'pagoAtencions.atencion.paciente',
            'pagoVentas.venta.detalles.producto',
        ])->findOrFail($id);

        $pdf = Pdf::loadView('pdf.comprobante-pago', [
            'pago' => $pago,
            'cuenta' => $pago->cuenta,
            'propietario' => $pago->cuenta?->propietario?->persona,
            'detalles' => $pago->detallePagos,
        ])->setPaper([0, 0, 226.77, 500], 'portrait'); // 80mm format

        return $pdf->stream("ticket-pago-{$pago->id_pago}.pdf");
    }

    /**
     * 2.3 — Genera el Carnet de Vacunación A5 horizontal.
     */
    public function carnetVacunacionPdf(int $id): Response
    {
        $paciente = Paciente::with([
            'raza.especie',
            'propietario.persona',
            'atenciones.atencionVeterinaria.procedimientos.vacunacion.administracionMedicamento',
            'atenciones.atencionVeterinaria.procedimientos.desparasitacion',
            'atenciones.participaciones.profesional.persona',
        ])->findOrFail($id);

        // Extraer vacunaciones
        $vacunas = collect();
        $desparasitaciones = collect();

        foreach ($paciente->atenciones as $atencion) {
            $prof = $atencion->participaciones->first()?->profesional?->persona?->nombre ?? 'Dr(a). Veterinario';

            if ($vet = $atencion->atencionVeterinaria) {
                foreach ($vet->procedimientos as $proc) {
                    if ($proc->vacunacion) {
                        $vacunas->push([
                            'fecha' => $proc->fecha_hora,
                            'vacuna' => $proc->descripcion ?: 'Vacuna Polivalente',
                            'proxima' => $proc->vacunacion->proxima_dosis,
                            'sitio' => $proc->vacunacion->sitio_aplicacion,
                            'profesional' => $prof,
                        ]);
                    }
                    if ($proc->desparasitacion) {
                        $desparasitaciones->push([
                            'fecha' => $proc->fecha_hora,
                            'producto' => $proc->descripcion ?: 'Antiparasitario amplio espectro',
                            'tipo' => $proc->desparasitacion->tipo ?? 'Interno / Externo',
                            'proxima' => $proc->desparasitacion->proxima_dosis,
                            'profesional' => $prof,
                        ]);
                    }
                }
            }
        }

        $pdf = Pdf::loadView('pdf.carnet-vacunacion', [
            'paciente' => $paciente,
            'propietario' => $paciente->propietario?->persona,
            'vacunas' => $vacunas,
            'desparasitaciones' => $desparasitaciones,
        ])->setPaper('a5', 'landscape');

        return $pdf->stream("carnet-{$paciente->nombre}-{$paciente->id_paciente}.pdf");
    }

    /**
     * 2.4 — Genera el Consentimiento Informado Quirúrgico y Anestésico en PDF.
     */
    public function consentimientoCirugiaPdf(int $id): Response
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();

        if (! $currentUser->canAccessModule('cirugias') && ! $currentUser->hasRole(['VETERINARIO', 'SUPER_ADMIN'])) {
            abort(403, 'Acceso denegado: solo personal médico o administración puede emitir consentimientos informados.');
        }

        $cirugia = Cirugia::with([
            'procedimiento.atencionVeterinaria.atencion.paciente.propietario.persona',
            'procedimiento.atencionVeterinaria.atencion.paciente.raza.especie',
            'cirujano.persona',
            'anestesista.persona',
        ])->findOrFail($id);

        $procedimiento = $cirugia->procedimiento;
        $atencion = $procedimiento?->atencionVeterinaria?->atencion;
        $paciente = $atencion?->paciente;
        $propietario = $paciente?->propietario?->persona;
        $cirujano = $cirugia->cirujano;
        $anestesista = $cirugia->anestesista;

        $pdf = Pdf::loadView('pdf.consentimiento-cirugia', [
            'cirugia' => $cirugia,
            'procedimiento' => $procedimiento,
            'atencion' => $atencion,
            'paciente' => $paciente,
            'propietario' => $propietario,
            'cirujano' => $cirujano,
            'anestesista' => $anestesista,
        ])->setPaper('a4', 'portrait');

        $slugNombre = $paciente ? strtolower(str_replace(' ', '_', $paciente->nombre)) : 'paciente';

        return $pdf->stream("consentimiento-quirurgico-{$slugNombre}-{$cirugia->id_procedimiento}.pdf");
    }
}
