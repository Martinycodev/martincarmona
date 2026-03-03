<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

/**
 * Clase base para tests que necesitan conexión a base de datos.
 *
 * Resuelve un usuario real de la tabla `usuarios` para usar como propietario
 * de los datos de prueba. Las subclases eligen su propia estrategia de
 * aislamiento (rollback o borrado explícito).
 */
abstract class DatabaseTestCase extends TestCase
{
    /** ID de un usuario existente en la BD, usado como propietario de los datos de test */
    protected static int $testUserId;

    /** Conexión mysqli compartida (Singleton de \Database) */
    protected \mysqli $db;

    // -------------------------------------------------------------------------
    // Bootstrap de clase: obtener un userId válido
    // -------------------------------------------------------------------------

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $db = \Database::connect();

        // Buscar primero un usuario de tipo 'empresa' (propietario natural de tareas/parcelas)
        $result = $db->query("SELECT id FROM usuarios WHERE rol = 'empresa' LIMIT 1");

        if (!$result || $result->num_rows === 0) {
            // Fallback: cualquier usuario
            $result = $db->query("SELECT id FROM usuarios LIMIT 1");
        }

        if (!$result || $result->num_rows === 0) {
            static::markTestSkipped(
                'No hay usuarios en la base de datos. Ejecuta las migraciones primero.'
            );
        }

        static::$testUserId = (int) $result->fetch_assoc()['id'];
    }

    // -------------------------------------------------------------------------
    // setUp: guardar referencia a la conexión
    // -------------------------------------------------------------------------

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = \Database::connect();
    }
}
