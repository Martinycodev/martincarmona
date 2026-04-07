<?php

namespace App\Modules\Planner\Models;

use App\Core\Database;
use PDO;

/**
 * PlannerModel
 * ------------
 * Clase base de los modelos del módulo Planner.
 *
 * Filosofía: NO es un ORM. Es un wrapper minimalista alrededor de PDO
 * para no repetir 50 líneas de prepare/execute/fetch en cada modelo.
 * Ofrece CRUD genérico (all/find/create/update/delete) y los modelos
 * concretos añaden encima los métodos de query específicos del dominio.
 *
 * Cada modelo hijo debe declarar:
 *   protected static string $table;
 *
 * Decisión deliberada: los métodos devuelven arrays asociativos, no
 * objetos. Es lo más natural para PDO::FETCH_ASSOC (configurado en
 * App\Core\Database) y evita el overhead de hidratar entidades para
 * un proyecto single-user. Si en el futuro hace falta un objeto rico,
 * se hace en el modelo concreto.
 */
abstract class PlannerModel
{
    /** Nombre de la tabla MySQL. Cada subclase lo sobreescribe. */
    protected static string $table = '';

    /** Acceso al singleton PDO compartido con el resto del proyecto. */
    protected static function db(): PDO
    {
        return Database::getInstance();
    }

    /**
     * Devuelve todas las filas de la tabla, ordenadas por id descendente.
     * Útil para listados administrativos. Para consultas filtradas usa
     * los métodos específicos de cada modelo concreto.
     */
    public static function all(): array
    {
        $sql  = sprintf('SELECT * FROM `%s` ORDER BY `id` DESC', static::$table);
        $stmt = static::db()->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Busca un registro por id. Devuelve null si no existe.
     */
    public static function find(int $id): ?array
    {
        $sql  = sprintf('SELECT * FROM `%s` WHERE `id` = :id LIMIT 1', static::$table);
        $stmt = static::db()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /**
     * Inserta un registro y devuelve el id generado.
     *
     * Construye el INSERT a partir de las claves del array $data, todas
     * pasadas como parámetros nombrados (anti SQL injection).
     */
    public static function create(array $data): int
    {
        if ($data === []) {
            throw new \InvalidArgumentException('create() requiere al menos una columna.');
        }

        $cols         = array_keys($data);
        $placeholders = array_map(fn(string $c): string => ':' . $c, $cols);

        $sql = sprintf(
            'INSERT INTO `%s` (`%s`) VALUES (%s)',
            static::$table,
            implode('`, `', $cols),
            implode(', ', $placeholders)
        );

        $stmt = static::db()->prepare($sql);
        $stmt->execute($data);

        return (int) static::db()->lastInsertId();
    }

    /**
     * Actualiza un registro por id. Devuelve true si la query se ejecutó.
     * No comprueba si afectó a alguna fila (rowCount) porque MySQL puede
     * devolver 0 si los valores nuevos son idénticos a los actuales.
     */
    public static function update(int $id, array $data): bool
    {
        if ($data === []) {
            throw new \InvalidArgumentException('update() requiere al menos una columna.');
        }

        $sets = implode(
            ', ',
            array_map(fn(string $c): string => "`$c` = :$c", array_keys($data))
        );

        $sql  = sprintf('UPDATE `%s` SET %s WHERE `id` = :id', static::$table, $sets);
        $stmt = static::db()->prepare($sql);

        $data['id'] = $id;
        return $stmt->execute($data);
    }

    /**
     * Elimina un registro por id. Las FKs con ON DELETE CASCADE
     * (p.ej. planner_postpone_log → planner_schedule_blocks) se
     * encargan del resto.
     */
    public static function delete(int $id): bool
    {
        $sql  = sprintf('DELETE FROM `%s` WHERE `id` = :id', static::$table);
        $stmt = static::db()->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
}
