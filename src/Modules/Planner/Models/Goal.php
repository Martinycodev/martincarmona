<?php

namespace App\Modules\Planner\Models;

/**
 * Goal — propósito a largo plazo del usuario.
 *
 * Tabla: planner_goals
 *
 * Los goals son el "input" de alto nivel que recibe la IA cuando
 * genera el horario diario: PlannerAIService::buildUserMessage()
 * solo manda los goals con status='active'.
 */
class Goal extends PlannerModel
{
    protected static string $table = 'planner_goals';

    /**
     * Devuelve los objetivos activos ordenados por prioridad
     * (1 = máxima, 5 = mínima). Es la query que usará el servicio de
     * IA cada vez que pida una replanificación.
     */
    public static function active(): array
    {
        $sql = 'SELECT * FROM `planner_goals`
                WHERE `status` = :status
                ORDER BY `priority` ASC, `id` ASC';

        $stmt = static::db()->prepare($sql);
        $stmt->execute(['status' => 'active']);
        return $stmt->fetchAll();
    }
}
