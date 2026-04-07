<?php

namespace App\Modules\Planner\Controllers;

/**
 * HealthController
 * ----------------
 * Endpoint mínimo para verificar que el wiring del módulo está vivo:
 *   - autoload PSR-4 resuelve App\Modules\Planner\*
 *   - el middleware se ejecuta correctamente
 *   - las rutas /planner/* están registradas en el Router
 *
 * No tiene lógica de negocio. Se puede borrar cuando los endpoints
 * reales del Paso 4 estén operativos.
 */
class HealthController extends PlannerController
{
    public function index(): string
    {
        return $this->json([
            'module' => 'planner',
            'status' => 'ok',
            'time'   => date('c'),
        ]);
    }
}
