<?php

/**
 * Rutas del módulo Planner
 * ------------------------
 * Todas las rutas viven bajo el prefijo /planner/*.
 * Se incluye desde src/routes.php con un único require, manteniendo
 * el archivo principal limpio y el módulo aislado.
 *
 * El Router del core no soporta grupos ni middlewares, por lo que la
 * autenticación se aplica vía la clase base PlannerController, que
 * invoca PlannerAuthMiddleware en su constructor.
 *
 * @var \App\Core\Router $router
 */

use App\Modules\Planner\Controllers\AuthController;
use App\Modules\Planner\Controllers\DevController;
use App\Modules\Planner\Controllers\HealthController;

// ─── Auth (rutas exentas del middleware) ─────────────────
$router->get ('/planner/login',  [AuthController::class, 'showLogin']);
$router->post('/planner/login',  [AuthController::class, 'login']);
$router->get ('/planner/logout', [AuthController::class, 'logout']);

// ─── Endpoints protegidos por PlannerAuthMiddleware ──────
// Health: verificación de wiring. Borrar cuando exista /planner/api/now.
$router->get('/planner/health', [HealthController::class, 'index']);

// ─── Dev/test temporales (solo APP_ENV=local) ────────────
// Estas rutas exponen información interna (estado de tablas, llamadas
// a la IA) y por tanto NUNCA deben estar disponibles en producción.
// Se registran solo si el entorno es local; en producción simplemente
// no existen en la tabla de rutas y devuelven 404.
if (($_ENV['APP_ENV'] ?? 'production') === 'local') {
    $router->get('/planner/dev/test-models', [DevController::class, 'testModels']);
    $router->get('/planner/dev/test-ai',     [DevController::class, 'testAi']);
}
