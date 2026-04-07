<?php

namespace App\Modules\Planner\Models;

/**
 * PostponeLog — bitácora de aplazamientos.
 *
 * Tabla: planner_postpone_log
 *
 * Cada vez que el usuario pulsa "Posponer" en una notificación de
 * Telegram (Paso 5) o en la API (Paso 4), se registra una entrada
 * aquí. El check-in diario (Paso 6) lee estas entradas para detectar
 * patrones de procrastinación y ajustar el load_factor del día siguiente.
 */
class PostponeLog extends PlannerModel
{
    protected static string $table = 'planner_postpone_log';

    /**
     * Aplazamientos registrados durante una fecha concreta.
     * Hace JOIN con schedule_blocks para devolver también el título
     * del bloque, que es lo que necesita la IA del check-in para
     * razonar sobre qué se está postergando.
     *
     * @param string $date  Formato YYYY-MM-DD.
     */
    public static function forDate(string $date): array
    {
        $sql = 'SELECT pl.*, sb.title AS block_title, sb.block_type
                FROM `planner_postpone_log` pl
                INNER JOIN `planner_schedule_blocks` sb ON sb.id = pl.block_id
                WHERE DATE(pl.postponed_at) = :date
                ORDER BY pl.postponed_at ASC';

        $stmt = static::db()->prepare($sql);
        $stmt->execute(['date' => $date]);
        return $stmt->fetchAll();
    }
}
