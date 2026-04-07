<?php

namespace App\Modules\Planner\Models;

/**
 * ScheduleBlock — un bloque del horario diario generado por la IA.
 *
 * Tabla: planner_schedule_blocks
 *
 * Es la "verdad operativa" del día: el endpoint /planner/api/now
 * (Paso 4 del roadmap) lee de aquí qué tarea está en curso.
 *
 * Estados:
 *   pending → in_progress → done | postponed | skipped
 */
class ScheduleBlock extends PlannerModel
{
    protected static string $table = 'planner_schedule_blocks';

    /**
     * Todos los bloques de un día concreto, ordenados por hora de inicio.
     *
     * @param string $date  Formato YYYY-MM-DD.
     */
    public static function forDate(string $date): array
    {
        $sql = 'SELECT * FROM `planner_schedule_blocks`
                WHERE `scheduled_date` = :date
                ORDER BY `start_at` ASC';

        $stmt = static::db()->prepare($sql);
        $stmt->execute(['date' => $date]);
        return $stmt->fetchAll();
    }

    /**
     * Bloque "actual": el que está en curso ahora mismo, o el primero
     * pendiente cuyo start_at ya haya pasado. Devuelve null si no hay
     * nada que mostrar (todo done/skipped o el día aún no ha empezado).
     *
     * Esta es la query crítica del endpoint /planner/api/now.
     */
    public static function current(): ?array
    {
        $sql = "SELECT * FROM `planner_schedule_blocks`
                WHERE `status` IN ('in_progress','pending')
                  AND `start_at` <= NOW()
                  AND `end_at`   >= NOW()
                ORDER BY
                    FIELD(`status`, 'in_progress', 'pending'),
                    `start_at` ASC
                LIMIT 1";

        $stmt = static::db()->query($sql);
        $row  = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /**
     * Devuelve el bloque inmediatamente posterior al dado, dentro del
     * mismo día. Lo usa /planner/api/now para mostrar el "teaser" de la
     * siguiente tarea (regla del minimalismo: una tarea visible + 1 hint).
     */
    public static function nextAfter(array $currentBlock): ?array
    {
        $sql = 'SELECT * FROM `planner_schedule_blocks`
                WHERE `scheduled_date` = :date
                  AND `start_at` > :start
                  AND `status` = "pending"
                ORDER BY `start_at` ASC
                LIMIT 1';

        $stmt = static::db()->prepare($sql);
        $stmt->execute([
            'date'  => $currentBlock['scheduled_date'],
            'start' => $currentBlock['start_at'],
        ]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /**
     * Cambia el estado de un bloque. Centralizado aquí para que los
     * controllers no tengan que conocer los nombres de los estados.
     */
    public static function setStatus(int $id, string $status): bool
    {
        $allowed = ['pending', 'in_progress', 'done', 'postponed', 'skipped'];
        if (!in_array($status, $allowed, true)) {
            throw new \InvalidArgumentException("Estado inválido: {$status}");
        }
        return static::update($id, ['status' => $status]);
    }
}
