<?php

namespace Tests\Unit;

use Tests\DatabaseTestCase;
use App\Models\Tarea;

/**
 * Tests para el modelo Tarea.
 *
 * Estrategia de aislamiento: rastreo explícito de IDs creados + DELETE en tearDown.
 *
 * No se usa begin_transaction/rollback porque Tarea::create(), update() y delete()
 * gestionan sus propias transacciones internas. Un BEGIN externo causaría un commit
 * implícito de la transacción de prueba antes de poder hacer rollback.
 */
class TareaModelTest extends DatabaseTestCase
{
    /** IDs de tareas creadas en cada test, para limpiar en tearDown */
    private array $createdIds = [];

    protected function tearDown(): void
    {
        if (!empty($this->createdIds)) {
            $model = new Tarea();
            foreach ($this->createdIds as $id) {
                $model->delete($id, self::$testUserId);
            }
            $this->createdIds = [];
        }
        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    // Datos de prueba — mínimos para no tocar tablas de relaciones
    // -------------------------------------------------------------------------

    private function sampleData(): array
    {
        return [
            'fecha'       => '2026-01-15',
            'titulo'      => '__TestTarea__',
            'descripcion' => 'Test descripcion',
            'horas'       => 4.0,
            // Sin trabajadores/parcelas/trabajo → solo inserta en `tareas`
        ];
    }

    // -------------------------------------------------------------------------
    // create()
    // -------------------------------------------------------------------------

    public function test_create_returns_int_id(): void
    {
        $id = (new Tarea())->create($this->sampleData(), self::$testUserId);

        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);

        $this->createdIds[] = $id;
    }

    public function test_create_stores_titulo_and_fecha(): void
    {
        $model = new Tarea();
        $id    = $model->create($this->sampleData(), self::$testUserId);
        $this->createdIds[] = $id;

        // Verificar con una consulta directa que los datos se guardaron
        $stmt = $this->db->prepare("SELECT titulo, fecha, horas FROM tareas WHERE id = ? AND id_user = ?");
        $stmt->bind_param("ii", $id, self::$testUserId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $this->assertNotNull($row);
        $this->assertSame('__TestTarea__', $row['titulo']);
        $this->assertSame('2026-01-15', $row['fecha']);
    }

    // -------------------------------------------------------------------------
    // getAll()
    // -------------------------------------------------------------------------

    public function test_getAll_includes_new_record(): void
    {
        $model = new Tarea();
        $id    = $model->create($this->sampleData(), self::$testUserId);
        $this->createdIds[] = $id;

        $all = $model->getAll(self::$testUserId);
        $ids = array_map('intval', array_column($all, 'id'));

        $this->assertContains($id, $ids);
    }

    // -------------------------------------------------------------------------
    // update()
    // -------------------------------------------------------------------------

    public function test_update_modifies_titulo(): void
    {
        $model = new Tarea();
        $id    = $model->create($this->sampleData(), self::$testUserId);
        $this->createdIds[] = $id;

        $data   = array_merge($this->sampleData(), ['id' => $id, 'titulo' => '__TareaActualizada__']);
        $result = $model->update($data, self::$testUserId);

        $this->assertTrue($result);

        // Verificar con consulta directa
        $stmt = $this->db->prepare("SELECT titulo FROM tareas WHERE id = ? AND id_user = ?");
        $stmt->bind_param("ii", $id, self::$testUserId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $this->assertSame('__TareaActualizada__', $row['titulo']);
    }

    // -------------------------------------------------------------------------
    // delete()
    // -------------------------------------------------------------------------

    public function test_delete_removes_record(): void
    {
        $model = new Tarea();
        $id    = $model->create($this->sampleData(), self::$testUserId);
        // No añadir a createdIds: vamos a borrarlo en el test

        $result = $model->delete($id, self::$testUserId);
        $this->assertTrue($result);

        // Verificar que ya no existe
        $stmt = $this->db->prepare("SELECT id FROM tareas WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $this->assertNull($row);
    }

    public function test_delete_wrong_user_returns_false(): void
    {
        $model = new Tarea();
        $id    = $model->create($this->sampleData(), self::$testUserId);
        $this->createdIds[] = $id;

        $result = $model->delete($id, 999999);
        $this->assertFalse($result);
    }
}
