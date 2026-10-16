<?php

namespace Tests\Feature;

use App\Models\Atencion;
use App\Models\AtencionVeterinaria;
use App\Models\Cuenta;
use App\Models\Especie;
use App\Models\Paciente;
use App\Models\Pago;
use App\Models\Persona;
use App\Models\Propietario;
use App\Models\Raza;
use App\Models\Receta;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MejorasPdfTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $adminUser;

    private Usuario $recepcionUser;

    private Paciente $paciente;

    private Atencion $atencion;

    private Pago $pago;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRol = Rol::create(['nombre' => 'ADMIN', 'descripcion' => 'Admin']);
        $recepcionRol = Rol::create(['nombre' => 'RECEPCION', 'descripcion' => 'Recepción']);

        $this->adminUser = Usuario::factory()->create([
            'nombre_usuario' => 'admin_pdf',
            'password_hash' => bcrypt('12345678'),
        ]);
        $this->adminUser->roles()->attach($adminRol->id_rol);

        $this->recepcionUser = Usuario::factory()->create([
            'nombre_usuario' => 'recepcion_pdf',
            'password_hash' => bcrypt('12345678'),
        ]);
        $this->recepcionUser->roles()->attach($recepcionRol->id_rol);

        $persona = Persona::create([
            'documento' => '4445556',
            'nombre' => 'Carlos',
            'apellido1' => 'Benítez',
            'telefono' => '0971000111',
            'email' => 'carlos@example.com',
            'direccion' => 'San Lorenzo',
            'tipo_persona' => 'FISICA',
        ]);

        $propietario = Propietario::create([
            'id_persona' => $persona->id_persona,
            'estado' => 'ACTIVO',
        ]);

        $especie = Especie::create(['nombre' => 'Felino', 'estado' => 'ACTIVO']);
        $raza = Raza::create(['id_especie' => $especie->id_especie, 'nombre' => 'Siamés', 'estado' => 'ACTIVO']);

        $this->paciente = Paciente::create([
            'id_propietario' => $propietario->id_persona,
            'id_raza' => $raza->id_raza,
            'nombre' => 'Mimi',
            'sexo' => 'HEMBRA',
            'fecha_nacimiento' => now()->subYears(3)->toDateString(),
            'color' => 'Crema / Café',
            'estado' => 'ACTIVO',
        ]);

        $this->atencion = Atencion::create([
            'id_paciente' => $this->paciente->id_paciente,
            'fecha_hora_inicio' => now(),
            'estado' => 'FINALIZADO',
            'total_final' => 150000,
        ]);

        $vet = AtencionVeterinaria::create([
            'id_atencion' => $this->atencion->id_atencion,
            'motivo_consulta' => 'Chequeo anual y vacunación',
            'tipo' => 'CONSULTA',
            'anamnesis' => 'Paciente en buen estado general',
            'conclusion' => 'Continuar con vitaminas y antiparasitarios',
        ]);

        Receta::create([
            'id_atencion' => $this->atencion->id_atencion,
            'fecha_hora' => now(),
            'indicaciones_generales' => '1 comprimido de amoxicilina cada 12 horas por 7 días.',
        ]);

        $cuenta = Cuenta::create([
            'id_propietario' => $propietario->id_persona,
            'estado' => 'ACTIVO',
        ]);

        $this->pago = Pago::create([
            'id_cuenta' => $cuenta->id_cuenta,
            'fecha_hora' => now(),
            'estado' => 'CONFIRMADO',
            'observaciones' => 'Cobro consulta #1',
        ]);
    }

    public function test_2_1_receta_medica_pdf_can_be_generated_by_admin(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('historial.receta.pdf', $this->atencion->id_atencion));

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
    }

    public function test_2_1_receta_medica_pdf_forbidden_for_unauthorized_roles(): void
    {
        $response = $this->actingAs($this->recepcionUser)
            ->get(route('historial.receta.pdf', $this->atencion->id_atencion));

        $response->assertStatus(403);
    }

    public function test_2_2_comprobante_pago_pdf_can_be_generated(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('caja.comprobante', $this->pago->id_pago));

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
    }

    public function test_2_3_carnet_vacunacion_pdf_can_be_generated(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('mascotas.carnet.pdf', $this->paciente->id_paciente));

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
    }
}
