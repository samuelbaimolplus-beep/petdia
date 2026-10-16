<?php

namespace Tests\Feature;

use App\Models\AdministracionMedicamento;
use App\Models\Atencion;
use App\Models\AtencionVeterinaria;
use App\Models\Especie;
use App\Models\Paciente;
use App\Models\Persona;
use App\Models\Procedimiento;
use App\Models\Producto;
use App\Models\Profesional;
use App\Models\Propietario;
use App\Models\Raza;
use App\Models\Receta;
use App\Models\Rol;
use App\Models\Servicio;
use App\Models\UsoProductoAtencion;
use App\Models\Usuario;
use App\Models\Vacunacion;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalWebTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $propietarioUser;

    private Persona $propietarioPersona;

    private Paciente $paciente;

    private Servicio $servicio;

    private Profesional $profesional;

    private Usuario $veterinarioUser;

    private Rol $rolCliente;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rolCliente = Rol::firstOrCreate(['nombre' => 'CLIENTE', 'descripcion' => 'Cliente Propietario']);

        // 1. Propietario / Cliente
        $this->propietarioPersona = Persona::create([
            'nombre' => 'María',
            'apellido1' => 'Gómez',
            'documento' => 'CI-55443322',
            'telefono' => '71122334',
            'email' => 'maria@gmail.com',
        ]);

        Propietario::create([
            'id_persona' => $this->propietarioPersona->id_persona,
            'direccion' => 'Av. América 456',
        ]);

        $this->propietarioUser = Usuario::factory()->create([
            'id_persona' => $this->propietarioPersona->id_persona,
            'nombre_usuario' => 'maria_gomez',
            'estado' => 'ACTIVO',
        ]);
        $this->propietarioUser->roles()->attach($this->rolCliente->id_rol);

        // 2. Veterinario
        $personaVet = Persona::create([
            'nombre' => 'Dr. Fernando',
            'apellido1' => 'Suárez',
            'documento' => 'VET-771122',
            'telefono' => '72233445',
            'email' => 'suarez@lapetdiatria.com',
        ]);

        $this->profesional = Profesional::create([
            'id_persona' => $personaVet->id_persona,
            'especialidad' => 'Medicina General',
            'registro_profesional' => 'VET-BOL-9900',
        ]);

        $rolVet = Rol::create(['nombre' => 'VETERINARIO', 'descripcion' => 'Veterinario']);
        $this->veterinarioUser = Usuario::factory()->create([
            'id_persona' => $personaVet->id_persona,
            'nombre_usuario' => 'dr_suarez',
            'estado' => 'ACTIVO',
        ]);
        $this->veterinarioUser->roles()->attach($rolVet->id_rol);

        // 3. Mascota
        $especie = Especie::create(['nombre' => 'Canino']);
        $raza = Raza::create(['id_especie' => $especie->id_especie, 'nombre' => 'Golden Retriever']);

        $this->paciente = Paciente::create([
            'id_propietario' => $this->propietarioPersona->id_persona,
            'id_raza' => $raza->id_raza,
            'nombre' => 'Chester',
            'sexo' => 'M',
            'fecha_nacimiento' => Carbon::now()->subYears(3),
            'color' => 'Dorado',
            'peso_actual' => 28.5,
            'estado' => 'ACTIVO',
        ]);

        // 4. Servicio
        $this->servicio = Servicio::create([
            'nombre' => 'Consulta Médica General',
            'descripcion' => 'Evaluación preventiva',
            'precio_base' => 120.00,
            'estado' => 'ACTIVO',
        ]);
    }

    public function test_invitado_es_redirigido_al_login(): void
    {
        $response = $this->get(route('portal.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_propietario_puede_ver_portal_con_sus_mascotas(): void
    {
        $response = $this->actingAs($this->propietarioUser)->get(route('portal.index'));

        $response->assertStatus(200);
        $response->assertSee('¡Hola, María Gómez!');
        $response->assertSee('Chester');
        $response->assertSee('Golden Retriever');
        $response->assertSee('Ficha de Salud');
    }

    public function test_propietario_puede_ver_ficha_de_salud_de_su_mascota(): void
    {
        $atencion = Atencion::create([
            'id_paciente' => $this->paciente->id_paciente,
            'id_profesional' => $this->profesional->id_persona,
            'fecha_hora_inicio' => Carbon::now()->subMonths(2),
            'fecha_hora_fin' => Carbon::now()->subMonths(2)->addMinutes(30),
            'estado' => 'FINALIZADA',
        ]);

        $vet = AtencionVeterinaria::create([
            'id_atencion' => $atencion->id_atencion,
            'motivo_consulta' => 'Control de vacunas',
        ]);

        $proc = Procedimiento::create([
            'id_atencion' => $vet->id_atencion,
            'descripcion' => 'Vacuna Sextuple Canina',
            'fecha_hora' => Carbon::now()->subMonths(2),
        ]);

        $producto = Producto::create([
            'nombre' => 'Vacuna Sextuple Canina',
            'tipo' => 'MEDICAMENTO',
            'precio_venta' => 80.00,
        ]);

        $uso = UsoProductoAtencion::create([
            'id_atencion' => $atencion->id_atencion,
            'id_producto' => $producto->id_producto,
            'cantidad' => 1,
        ]);

        AdministracionMedicamento::create([
            'id_uso' => $uso->id_uso,
            'dosis' => 1,
            'via_administracion' => 'Subcutánea',
        ]);

        Vacunacion::create([
            'id_procedimiento' => $proc->id_procedimiento,
            'id_uso' => $uso->id_uso,
            'proxima_dosis' => Carbon::now()->addMonths(10)->toDateString(),
            'sitio_aplicacion' => 'Subcutáneo interescapular',
        ]);

        $response = $this->actingAs($this->propietarioUser)
            ->get(route('portal.mascota', $this->paciente->id_paciente));

        $response->assertStatus(200);
        $response->assertSee('Chester');
        $response->assertSee('Vacuna Sextuple Canina');
        $response->assertSee('Descargar Carnet Vacunación PDF');
    }

    public function test_propietario_no_puede_ver_mascota_ajena(): void
    {
        $otroPersona = Persona::create([
            'nombre' => 'Juan',
            'apellido1' => 'Pérez',
            'documento' => 'CI-99881122',
        ]);
        Propietario::create([
            'id_persona' => $otroPersona->id_persona,
        ]);
        $otroUser = Usuario::factory()->create(['id_persona' => $otroPersona->id_persona]);
        $otroUser->roles()->attach($this->rolCliente->id_rol);

        $otraMascota = Paciente::create([
            'id_propietario' => $otroPersona->id_persona,
            'id_raza' => $this->paciente->id_raza,
            'nombre' => 'Firulais',
            'sexo' => 'M',
            'estado' => 'ACTIVO',
        ]);

        $response = $this->actingAs($this->propietarioUser)
            ->get(route('portal.mascota', $otraMascota->id_paciente));

        $response->assertStatus(403);
    }

    public function test_staff_puede_inspeccionar_ficha_de_salud_de_cualquier_mascota(): void
    {
        $response = $this->actingAs($this->veterinarioUser)
            ->get(route('portal.mascota', $this->paciente->id_paciente));

        $response->assertStatus(200);
        $response->assertSee('Chester');
    }

    public function test_propietario_puede_solicitar_cita_online(): void
    {
        $fechaCita = Carbon::tomorrow()->setHour(10)->setMinute(0);

        $response = $this->actingAs($this->propietarioUser)->post(route('portal.solicitar-cita'), [
            'id_paciente' => $this->paciente->id_paciente,
            'id_servicio' => $this->servicio->id_servicio,
            'fecha_hora_inicio' => $fechaCita->format('Y-m-d\TH:i'),
            'motivo' => 'Control de peso y vacunación anual',
        ]);

        $response->assertRedirect(route('portal.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('citas', [
            'id_paciente' => $this->paciente->id_paciente,
            'estado' => 'PENDIENTE',
            'origen' => 'ONLINE_PORTAL',
        ]);

        $this->assertDatabaseHas('auditoria_logs', [
            'accion' => 'SOLICITAR_CITA_PORTAL',
            'modulo' => 'CITAS',
        ]);
    }

    public function test_propietario_puede_descargar_receta_de_su_mascota(): void
    {
        $atencion = Atencion::create([
            'id_paciente' => $this->paciente->id_paciente,
            'id_profesional' => $this->profesional->id_persona,
            'fecha_hora_inicio' => Carbon::now()->subDay(),
            'fecha_hora_fin' => Carbon::now()->subDay()->addMinutes(30),
            'estado' => 'FINALIZADA',
        ]);

        $vet = AtencionVeterinaria::create([
            'id_atencion' => $atencion->id_atencion,
            'motivo_consulta' => 'Dermatitis',
        ]);

        $receta = Receta::create([
            'id_atencion' => $vet->id_atencion,
            'fecha_hora' => Carbon::now()->subDay(),
        ]);

        $response = $this->actingAs($this->propietarioUser)
            ->get(route('historial.receta.pdf', $receta->id_receta));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));
    }
}
