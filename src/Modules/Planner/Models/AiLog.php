<?php

namespace App\Modules\Planner\Models;

/**
 * AiLog — auditoría de cada llamada a la API de Anthropic.
 *
 * Tabla: planner_ai_logs
 *
 * Se escribe SIEMPRE, incluso cuando la respuesta falla la validación
 * de esquema o devuelve un error 5xx. Es la herramienta principal
 * para depurar prompts y vigilar el coste de tokens.
 *
 * El campo prompt_hash (sha256) permite detectar peticiones idénticas
 * y, en el futuro, cachearlas si fuese necesario.
 */
class AiLog extends PlannerModel
{
    protected static string $table = 'planner_ai_logs';

    /**
     * Las últimas N entradas (por defecto 20). Útil para la pantalla
     * de auditoría que se construirá en pasos posteriores.
     */
    public static function recent(int $limit = 20): array
    {
        // LIMIT no admite parámetros bind en algunas versiones de PDO/MySQL,
        // por eso lo casteamos a int e interpolamos directamente. Es seguro
        // porque pasa por (int).
        $limit = max(1, $limit);
        $sql   = "SELECT * FROM `planner_ai_logs`
                  ORDER BY `created_at` DESC
                  LIMIT {$limit}";
        $stmt  = static::db()->query($sql);
        return $stmt->fetchAll();
    }
}
