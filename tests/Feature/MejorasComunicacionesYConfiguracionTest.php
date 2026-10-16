<?php

namespace Tests\Feature;

use App\Mail\RecordatorioMailable;
use App\Models\Configuracion;
use App\Models\Especie;
use App\Models\Paciente;
use App\Models\Persona;
use App\Models\Profesional;
use App\Models\Propietario;
use App\Models\Raza;
use App\Models\Recordatorio;
use App\Models\Rol;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MejorasComunicacionesYConfiguracionTest extends TestCase
{
    private function createAuthUser(string $rolName = 'SUPER_ADMIN'): Usuario
    {
        $persona = Persona::create([
            'documento' => 'DOC-'.uniqid(),
            'nombre' => 'Test',
            'apellido1' => 'User',
            'email' => 'user_'.uniqid().'@example.com',
        ]);

        $usuario = Usuario::create([
            'id_persona' => $persona->id_persona,
            'nombre_usuario' => 'user_'.uniqid(),
            'password_hash' => bcrypt('password'),
            'estado' => 'ACTIVO',
        ]);

        $rol = Rol::firstOrCreate(['nombre' => $rolName]);
        $usuario->roles()->attach($rol->id_rol);

        return $usuario;
    }

    private function createPacienteConPropietario(string $email = 'dueno@example.com'): Paciente
    {
        $especie = Especie::firstOrCreate(['nombre' => 'Canino']);
        $raza = Raza::firstOrCreate(['id_especie' => $especie->id_especie, 'nombre' => 'Mestizo']);

        $persona = Persona::create([
            'documento' => 'PROP-'.uniqid(),
            'nombre' => 'Juan',
            'apellido1' => 'Pérez',
            'email' => $email,
            'telefono' => '0981123456',
        ]);

        $propietario = Propietario::create([
            'id_persona' => $persona->id_persona,
        ]);

        return Paciente::create([
            'id_propietario' => $propietario->id_persona,
            'id_raza' => $raza->id_raza,
            'nombre' => 'Bobby',
            'sexo' => 'MACHO',
            'estado' => 'VIVO',
        ]);
    }

    public function test_4_1_enviar_recordatorio_despacha_mailable(): void
    {
        Mail::fake();

        $admin = $this->createAuthUser('SUPER_ADMIN');
        $paciente = $this->createPacienteConPropietario('dueno_bobby@test.com');

        $personaVeterinario = Persona::create([
            'documento' => 'VET-'.uniqid(),
            'nombre' => 'Carlos',
            'apellido1' => 'Villagra',
            'email' => 'carlosvillagraa00l@gmail.com',
            'telefono' => '0981999888',
        ]);
        $profesional = Profesional::create([
            'id_persona' => $personaVeterinario->id_persona,
            'especialidad' => 'Cirugía Veterinaria',
            'registro_profesional' => 'REG-'.uniqid(),
            'estado' => 'ACTIVO',
        ]);

        $recordatorio = Recordatorio::create([
            'id_paciente' => $paciente->id_paciente,
            'id_profesional' => $profesional->id_persona,
            'tipo' => 'VACUNA',
            'fecha_hora_programada' => Carbon::now()->addDays(2),
            'medio_envio' => 'EMAIL',
            'mensaje' => 'Alistar vacuna y jeringa para Bobby',
            'estado' => 'PENDIENTE',
        ]);

        $response = $this->actingAs($admin)->patch(route('recordatorios.marcar-enviado', $recordatorio));

        $response->assertRedirect(route('recordatorios.index'));
        $recordatorio->refresh();

        $this->assertEquals('ENVIADO', $recordatorio->estado);
        $this->assertNotNull($recordatorio->fecha_hora_envio);

        Mail::assertSent(RecordatorioMailable::class, function (RecordatorioMailable $mail) use ($recordatorio) {
            return $mail->hasTo('carlosvillagraa00l@gmail.com') &&
                   $mail->nombreMascota === 'Bobby' &&
                   $mail->recordatorio->id_recordatorio === $recordatorio->id_recordatorio;
        });
    }

    public function test_4_2_plantillas_personalizables(): void
    {
        $admin = $this->createAuthUser('ADMIN');

        $responseGet = $this->actingAs($admin)->get(route('configuracion.plantillas'));
        $responseGet->assertStatus(200);
        $responseGet->assertSee('Plantillas de Mensajes');

        $responsePost = $this->actingAs($admin)->post(route('configuracion.plantillas.update'), [
            'asunto' => 'Aviso Especial para {{nombre_mascota}}',
            'cuerpo' => 'Hola {{nombre_propietario}}, {{nombre_mascota}} tiene cita el {{fecha_cita}}.',
        ]);

        $responsePost->assertRedirect(route('configuracion.plantillas'));
        $this->assertEquals('Aviso Especial para {{nombre_mascota}}', Configuracion::get('plantilla_recordatorio_asunto'));
        $this->assertEquals('Hola {{nombre_propietario}}, {{nombre_mascota}} tiene cita el {{fecha_cita}}.', Configuracion::get('plantilla_recordatorio_cuerpo'));
    }

    public function test_7_1_configuracion_clinica_super_admin(): void
    {
        $superAdmin = $this->createAuthUser('SUPER_ADMIN');

        $responseGet = $this->actingAs($superAdmin)->get(route('configuracion.index'));
        $responseGet->assertStatus(200);
        $responseGet->assertSee('Configuración General de la Clínica');

        $responsePost = $this->actingAs($superAdmin)->post(route('configuracion.update'), [
            'nombre' => 'Veterinaria Central La Petdiatría',
            'direccion' => 'Av. España 456, Asunción',
            'telefono' => '021-999888',
            'ruc' => '80055443-2',
            'email' => 'admin@lapetdiatria.com.py',
            'horario' => '24 horas',
            'moneda' => 'Gs.',
            'separador_miles' => '.',
            'separador_decimales' => ',',
        ]);

        $responsePost->assertRedirect(route('configuracion.index'));
        $this->assertEquals('Veterinaria Central La Petdiatría', Configuracion::get('clinica_nombre'));
        $this->assertEquals('Av. España 456, Asunción', Configuracion::get('clinica_direccion'));
        $this->assertEquals('021-999888', Configuracion::get('clinica_telefono'));
    }
}
