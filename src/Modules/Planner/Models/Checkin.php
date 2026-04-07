<?php

namespace App\Modules\Planner\Models;

/**
 * Checkin — retrospectiva diaria.
 *
 * Tabla: planner_checkins
 *
 * Una entrada por día (UNIQUE en checkin_date). Lo crea el job
 * DailyCheckinJob a las 22:00 con el resumen y el load_factor que
 * la IA infiere de las respuestas y los aplazamientos.
 */
class Checkin extends PlannerModel
{
    protected static string $table = 'planner_checkins';

    /**
     * Recupera el check-in de una fecha concreta, si existe.
     */
    public static function forDate(string $date): ?array
    {
        $sql  = 'SELECT * FROM `planner_checkins` WHERE `checkin_date` = :date LIMIT 1';
        $stmt = static::db()->prepare($sql);
        $stmt->execute(['date' => $date]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /**
     * Último check-in disponible. Lo usa el servicio de IA al construir
     * el user_message del día siguiente: necesita saber el último
     * load_factor y el último resumen anímico para no programar de más.
     */
    public static function latest(): ?array
    {
        $sql  = 'SELECT * FROM `planner_checkins` ORDER BY `checkin_date` DESC LIMIT 1';
        $stmt = static::db()->query($sql);
        $row  = $stmt->fetch();
        return $row === false ? null : $row;
    }
}
