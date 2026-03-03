<?php

namespace Tests\Unit;

use Tests\DatabaseTestCase;
use App\Models\Trabajador;

/**
 * Tests para el modelo Trabajador.
 *
 * Estrategia de aislamiento: begin_transaction en setUp + rollback en tearDown.
 * Todos los INSERTs/UPDATEs/DELETEs quedan dentro de la transacción y nunca
 * se persisten en la BD real.
 */
class TrabajadorModelTest extends DatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->db->begin_transaction();
    }

    protected function tearDown(): void
    {
        $this->db->rollback();
        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    // Datos de prueba
    // -------------------------------------------------------------------------

    private function sampleData(): array
    {
        return [
            'nombre'             => '__Test__',
            'apellidos'          => 'Trabajador',
            'dni'                => '99999999T',
            'telefono'           => '600000000',
            'email'              => 'test_trabajador@test.local',
            'direccion'          => 'Calle Test 1',
            'especialidad'       => 'Poda',
            'fecha_contratacion' => '2026-01-01',
            'estado'             => 'activo',
            'foto'               => '',
        ];
    }

    // -------------------------------------------------------------------------
    // create()
    // -------------------------------------------------------------------------

    public function test_create_returns_int_id(): void
    {
        $id = (new Trabajador())->create($this->sampleData(), self::$testUserId);
        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);
    }

    // -------------------------------------------------------------------------
    // getById()
    // -------------------------------------------------------------------------

    public function test_getById_returns_inserted_record(): void
    {
        $model = new Trabajador();
        $id    = $model->create($this->sampleData(), self::$testUserId);

        $row = $model->getById($id, self::$testUserId);

        $this->assertIsArray($row);
        $this->assertSame('__Test__', $row['nombre']);
        $this->assertSame('Trabajador', $row['apellidos']);
        $this->assertSame('Poda', $row['especialidad']);
    }

    public function test_getById_wrong_user_returns_null(): void
    {
        $model = new Trabajador();
        $id    = $model->create($this->sampleData(), self::$testUserId);

        $row = $model->getById($id, 999999);
        $this->assertNull($row);
    }

    // -------------------------------------------------------------------------
    // getAll()
    // -------------------------------------------------------------------------

    public function test_getAll_includes_new_record(): void
    {
        $model = new Trabajador();
        $id    = $model->create($this->sampleData(), self::$testUserId);

        $all = $model->getAll(self::$testUserId);
        $ids = array_map('intval', array_column($all, 'id'));

        $this->assertContains($id, $ids);
    }

    // -------------------------------------------------------------------------
    // update()
    // -------------------------------------------------------------------------

    public function test_update_modifies_nombre(): void
    {
        $model   = new Trabajador();
        $id      = $model->create($this->sampleData(), self::$testUserId);
        $updated = array_merge($this->sampleData(), ['id' => $id, 'nombre' => 'Actualizado']);

        $result = $model->update($updated, self::$testUserId);
        $this->assertTrue($result);

        $row = $model->getById($id, self::$testUserId);
        $this->assertSame('Actualizado', $row['nombre']);
    }

    // -------------------------------------------------------------------------
    // delete()
    // -------------------------------------------------------------------------

    public function test_delete_removes_record(): void
    {
        $model = new Trabajador();
        $id    = $model->create($this->sampleData(), self::$testUserId);

        $result = $model->delete($id, self::$testUserId);
        $this->assertTrue($result);

        $row = $model->getById($id, self::$testUserId);
        $this->assertNull($row);
    }

    public function test_delete_wrong_user_leaves_record(): void
    {
        $model = new Trabajador();
        $id    = $model->create($this->sampleData(), self::$testUserId);

        // Intentar borrar con otro userId → no debe afectar al registro
        $model->delete($id, 999999);

        $row = $model->getById($id, self::$testUserId);
        $this->assertIsArray($row);
    }
}
