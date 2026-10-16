<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Configuracion;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ConfiguracionWebController extends Controller
{
    /**
     * Muestra la pantalla de configuración general de la clínica (Solo Super-Admin).
     */
    public function index(): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('configuracion')) {
            abort(403, 'No tienes permisos de Super-Administrador para acceder a la configuración general.');
        }

        $config = [
            'nombre' => Configuracion::get('clinica_nombre', 'Veterinaria La Petdiatría'),
            'direccion' => Configuracion::get('clinica_direccion', 'Avda. Santísimo Sacramento 1234, Asunción'),
            'telefono' => Configuracion::get('clinica_telefono', '+595 21 600 700'),
            'ruc' => Configuracion::get('clinica_ruc', '80012345-6'),
            'email' => Configuracion::get('clinica_email', 'contacto@lapetdiatria.com.py'),
            'horario' => Configuracion::get('clinica_horario', 'Lunes a Sábado: 07:30 a 19:30 | Urgencias 24hs'),
            'moneda' => Configuracion::get('clinica_moneda', 'Gs.'),
            'separador_miles' => Configuracion::get('clinica_separador_miles', '.'),
            'separador_decimales' => Configuracion::get('clinica_separador_decimales', ','),
            'logo' => Configuracion::get('clinica_logo'),
        ];

        return view('configuracion.index', compact('config'));
    }

    /**
     * Guarda la configuración general de la clínica.
     */
    public function update(Request $request): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('configuracion')) {
            abort(403, 'No tienes permisos de Super-Administrador para modificar la configuración.');
        }

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'direccion' => ['required', 'string', 'max:255'],
            'telefono' => ['required', 'string', 'max:50'],
            'ruc' => ['nullable', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:100'],
            'horario' => ['nullable', 'string', 'max:255'],
            'moneda' => ['required', 'string', 'max:10'],
            'separador_miles' => ['nullable', 'string', 'max:5'],
            'separador_decimales' => ['nullable', 'string', 'max:5'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'], // SEC: svg eliminado — riesgo de XSS
        ]);

        Configuracion::set('clinica_nombre', $validated['nombre']);
        Configuracion::set('clinica_direccion', $validated['direccion']);
        Configuracion::set('clinica_telefono', $validated['telefono']);
        Configuracion::set('clinica_ruc', $validated['ruc'] ?? '');
        Configuracion::set('clinica_email', $validated['email']);
        Configuracion::set('clinica_horario', $validated['horario'] ?? '');
        Configuracion::set('clinica_moneda', $validated['moneda']);
        Configuracion::set('clinica_separador_miles', $validated['separador_miles'] ?? '.');
        Configuracion::set('clinica_separador_decimales', $validated['separador_decimales'] ?? ',');

        if ($request->hasFile('logo')) {
            $oldLogo = Configuracion::get('clinica_logo');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }

            $path = $request->file('logo')->store('logos', 'public');
            Configuracion::set('clinica_logo', $path);
        }

        return redirect()->route('configuracion.index')->with('success', 'Configuración general guardada exitosamente.');
    }

    /**
     * Muestra la pantalla de personalización de plantillas de correo (Admin / Super-Admin).
     */
    public function plantillas(): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('plantillas')) {
            abort(403, 'No tienes permisos para configurar las plantillas de correo.');
        }

        $plantillaAsunto = Configuracion::get(
            'plantilla_recordatorio_asunto',
            'Recordatorio de Atención Médica - {{clinica_nombre}}'
        );

        $plantillaCuerpo = Configuracion::get(
            'plantilla_recordatorio_cuerpo',
            "Estimado/a {{nombre_propietario}},\n\nLe recordamos cordialmente desde {{clinica_nombre}} que su mascota {{nombre_mascota}} tiene programado su servicio de {{tipo_recordatorio}} para el día {{fecha_cita}}.\n\nPor favor confirme su asistencia o comuníquese con nosotros si necesita reprogramar.\n\n¡Cuidamos a los que más quieres!"
        );

        return view('configuracion.plantillas', compact('plantillaAsunto', 'plantillaCuerpo'));
    }

    /**
     * Guarda las plantillas de correo personalizadas.
     */
    public function updatePlantillas(Request $request): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('plantillas')) {
            abort(403, 'No tienes permisos para configurar las plantillas de correo.');
        }

        $validated = $request->validate([
            'asunto' => ['required', 'string', 'max:200'],
            'cuerpo' => ['required', 'string', 'max:3000'],
        ]);

        Configuracion::set('plantilla_recordatorio_asunto', $validated['asunto']);
        Configuracion::set('plantilla_recordatorio_cuerpo', $validated['cuerpo']);

        return redirect()->route('configuracion.plantillas')->with('success', 'Plantillas de correo guardadas exitosamente.');
    }
}
