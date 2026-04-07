<?php

namespace App\Modules\Planner\Controllers;

use App\Core\Controller;
use App\Modules\Planner\Middleware\PlannerAuthMiddleware;

/**
 * PlannerController (base)
 * ------------------------
 * Clase base de la que heredan TODOS los controladores del módulo Planner.
 *
 * Su única responsabilidad en esta fase es ejecutar el middleware de
 * autenticación en el constructor. De este modo, cualquier subclase
 * queda automáticamente protegida sin tocar el Router del core.
 *
 * Decisión deliberada: no usamos el layout del sitio público porque el
 * planner tendrá su propio frontend minimalista (Paso 7 del roadmap).
 */
abstract class PlannerController extends Controller
{
    public function __construct()
    {
        // Bloquea o permite la petición antes de ejecutar cualquier acción.
        PlannerAuthMiddleware::handle();
    }

    /**
     * Helper para responder JSON desde cualquier controller del módulo.
     * Centralizado aquí para no repetir cabeceras en cada acción.
     */
    protected function json(array $payload, int $status = 200): string
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        return json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
}
