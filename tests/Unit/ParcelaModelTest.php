<?php

namespace Tests\Unit;

use Tests\DatabaseTestCase;
use App\Models\Parcela;

/**
 * Tests para el modelo Parcela.
 *
 * Estrategia de aislamiento: begin_transaction en setUp + rollback en tearDown.
 */
class ParcelaModelTest extends DatabaseTestCase
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
            'nombre'      => '__TestParcela__',
            'olivos'      => 120,
            'ubicacion'   => 'Zona Norte',
            'propietario' => 'Propietario Test',
            'hidrante'    => 0,
            'descripcion' => 'Descripción de prueba',
        ];
    }

    // -------------------------------------------------------------------------
    // create()
    // -------------------------------------------------------------------------

    public function test_create_returns_int_id(): void
    {
        $id = (new Parcela())->create($this->sampleData(), self::$testUserId);
        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);
    }

    // -------------------------------------------------------------------------
    // getById()
    // -------------------------------------------------------------------------

    public function test_getById_returns_inserted_record(): void
    {
        $model = new Parcela();
        $id    = $model->create($this->sampleData(), self::$testUserId);

        $row = $model->getById($id, self::$testUserId);

        $this->assertIsArray($row);
        $this->assertSame('__TestParcela__', $row['nombre']);
        $this->assertSame('120', (string) $row['olivos']);
    }

    public function test_getById_wrong_user_returns_null(): void
    {
        $model = new Parcela();
        $id    = $model->create($this->sampleData(), self::$testUserId);

        $row = $model->getById($id, 999999);
        $this->assertNull($row);
    }

    // -------------------------------------------------------------------------
    // getAll()
    // -------------------------------------------------------------------------

    public function test_getAll_includes_new_record(): void
    {
        $model = new Parcela();
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
        $model   = new Parcela();
        $id      = $model->create($this->sampleData(), self::$testUserId);
        $updated = array_merge($this->sampleData(), ['id' => $id, 'nombre' => 'ParcelaActualizada']);

        $result = $model->update($updated, self::$testUserId);
        $this->assertTrue($result);

        $row = $model->getById($id, self::$testUserId);
        $this->assertSame('ParcelaActualizada', $row['nombre']);
    }

    // -------------------------------------------------------------------------
    // delete()
    // -------------------------------------------------------------------------

    public function test_delete_removes_record(): void
    {
        $model = new Parcela();
        $id    = $model->create($this->sampleData(), self::$testUserId);

        $result = $model->delete($id, self::$testUserId);
        $this->assertTrue($result);

        $row = $model->getById($id, self::$testUserId);
        $this->assertNull($row);
    }

    public function test_delete_wrong_user_leaves_record(): void
    {
        $model = new Parcela();
        $id    = $model->create($this->sampleData(), self::$testUserId);

        $model->delete($id, 999999);

        $row = $model->getById($id, self::$testUserId);
        $this->assertIsArray($row);
    }

    // -------------------------------------------------------------------------
    // getCostoAcumuladoPorParcela()
    // -------------------------------------------------------------------------

    public function test_getCostoAcumulado_returns_float(): void
    {
        $model = new Parcela();
        $id    = $model->create($this->sampleData(), self::$testUserId);

        $coste = $model->getCostoAcumuladoPorParcela($id, self::$testUserId);

        $this->assertIsFloat($coste);
        // Parcela nueva sin tareas → coste debe ser 0
        $this->assertSame(0.0, $coste);
    }
}
