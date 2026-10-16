<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MejorasGraficasTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $user;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRol = Rol::create(['nombre' => 'ADMIN', 'descripcion' => 'Admin']);

        $this->user = Usuario::factory()->create([
            'nombre_usuario' => 'admin_graficas',
            'password_hash' => bcrypt('12345678'),
        ]);
        $this->user->roles()->attach($adminRol->id_rol);
    }

    public function test_3_1_dashboard_renders_interactive_charts(): void
    {
        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('chartData');
        $response->assertSee('dashboardCitasChart');
        $response->assertSee('dashboardIngresosChart');
        $response->assertSee('Citas por Estado');
        $response->assertSee('Ingresos Diarios de Caja');

        $chartData = $response->viewData('chartData');
        $this->assertArrayHasKey('citas', $chartData);
        $this->assertArrayHasKey('ingresos', $chartData);
        $this->assertCount(7, $chartData['citas']['labels']);
        $this->assertCount(30, $chartData['ingresos']['labels']);
    }

    public function test_3_2_reportes_renders_interactive_charts(): void
    {
        $response = $this->actingAs($this->user)->get(route('reportes.index'));

        $response->assertStatus(200);
        $response->assertViewHas('charts');
        $response->assertSee('reporteIngresosChart');
        $response->assertSee('reporteMetodosChart');
        $response->assertSee('reporteAtencionesChart');
        $response->assertSee('reporteServiciosChart');

        $charts = $response->viewData('charts');
        $this->assertArrayHasKey('ingresos', $charts);
        $this->assertArrayHasKey('metodos', $charts);
        $this->assertArrayHasKey('servicios', $charts);
        $this->assertArrayHasKey('atenciones', $charts);
    }
}
