<?php

namespace App\Modules\Planner\Models;

/**
 * Constraint — ventana de tiempo recurrente que la IA debe respetar.
 *
 * Tabla: planner_constraints
 *
 * Tipos:
 *   - sleep         : horas de sueño
 *   - meal          : comidas
 *   - fixed_event   : citas inamovibles
 *   - focus_window  : franjas preferentes para deep work
 *   - no_work       : franjas en las que no se programa nada
 *
 * weekday_mask es un bitmask: L=1, M=2, X=4, J=8, V=16, S=32, D=64.
 * 127 = todos los días, 31 = lunes a viernes, 96 = sábado y domingo.
 */
class Constraint extends PlannerModel
{
    protected static string $table = 'planner_constraints';

    /**
     * Devuelve las constraints activas que aplican a un día concreto.
     *
     * @param int $weekday  0 = lunes ... 6 = domingo (estándar de DateTime)
     */
    public static function activeForWeekday(int $weekday): array
    {
        // Convertir el índice del día a su bit correspondiente.
        // L=bit0(1), M=bit1(2), ..., D=bit6(64).
        $bit = 1 << $weekday;

        $sql = 'SELECT * FROM `planner_constraints`
                WHERE `is_active` = 1
                  AND (`weekday_mask` & :bit) = :bit
                ORDER BY `start_time` ASC';

        $stmt = static::db()->prepare($sql);
        $stmt->execute(['bit' => $bit]);
        return $stmt->fetchAll();
    }
}
