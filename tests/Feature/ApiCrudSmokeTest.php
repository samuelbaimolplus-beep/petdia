<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ApiCrudSmokeTest extends TestCase
{
    use RefreshDatabase;

    private const SKIP = [
        'User', 'RolPermiso', 'ProfesionalServicio', 'AtencionTratamiento', 'Configuracion',
        'AuditoriaLog', 'RegistroAcceso', 'ControlCirugia', 'ObservacionRegistro',
    ];

    private const EXISTING = ['pacientes', 'citas', 'servicios', 'productos', 'atenciones'];

    public static function resources(): array
    {
        $cases = [];

        $modelsDir = dirname(__DIR__, 2).'/app/Models';
        foreach (scandir($modelsDir) as $file) {
            if ($file === '.' || $file === '..' || ! str_ends_with($file, '.php')) {
                continue;
            }
            $short = basename($file, '.php');

            if (in_array($short, self::SKIP, true)) {
                continue;
            }

            $class = 'App\\Models\\'.$short;
            $model = new $class;
            $table = $model->getTable();

            if (in_array($table, self::EXISTING, true)) {
                continue;
            }

            $cases[$table] = [$table, $class];
        }

        return $cases;
    }

    private function normalizedPayload(string $class, ?string $pkValue = null): array
    {
        $model = $class::factory()->make();
        $payload = $model->getAttributes();

        if ($model->getIncrementing()) {
            unset($payload[$model->getKeyName()]);
        }

        if ($pkValue !== null) {
            $payload[$model->getKeyName()] = $pkValue;
        }

        return $this->normalizeDates($payload);
    }

    private function normalizeDates(array $payload): array
    {
        foreach ($payload as $key => $value) {
            if ($value instanceof \DateTimeInterface) {
                $payload[$key] = $value->format('Y-m-d H:i:s');
            }
        }

        return $payload;
    }

    #[DataProvider('resources')]
    public function test_index_returns_paginated_records(string $route, string $class): void
    {
        $class::factory()->count(2)->create();

        $this->getJson('/api/v1/'.$route)
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    #[DataProvider('resources')]
    public function test_show_returns_record(string $route, string $class): void
    {
        $model = $class::factory()->create();

        $this->getJson('/api/v1/'.$route.'/'.$model->getKey())
            ->assertOk()
            ->assertJsonPath('data.id', $model->getKey());
    }

    #[DataProvider('resources')]
    public function test_store_creates_record(string $route, string $class): void
    {
        $model = $class::factory()->make();
        $payload = $this->normalizeDates($model->getAttributes());

        if ($model->getIncrementing()) {
            unset($payload[$model->getKeyName()]);
        }

        $response = $this->postJson('/api/v1/'.$route, $payload);

        $response->assertCreated()
            ->assertJsonStructure(['data' => ['id']]);

        $this->assertDatabaseHas($model->getTable(), [$model->getKeyName() => $response->json('data.id')]);
    }

    #[DataProvider('resources')]
    public function test_update_modifies_record(string $route, string $class): void
    {
        $model = $class::factory()->create();
        $payload = $this->normalizeDates($model->getAttributes());

        if ($model->getIncrementing()) {
            unset($payload[$model->getKeyName()]);
        }

        $this->patchJson('/api/v1/'.$route.'/'.$model->getKey(), $payload)
            ->assertOk();

        $this->assertDatabaseHas($model->getTable(), [$model->getKeyName() => $model->getKey()]);
    }

    #[DataProvider('resources')]
    public function test_destroy_deletes_record(string $route, string $class): void
    {
        $model = $class::factory()->create();

        $this->deleteJson('/api/v1/'.$route.'/'.$model->getKey())
            ->assertOk()
            ->assertJsonStructure(['message']);

        $this->assertDatabaseMissing($model->getTable(), [$model->getKeyName() => $model->getKey()]);
    }
}
