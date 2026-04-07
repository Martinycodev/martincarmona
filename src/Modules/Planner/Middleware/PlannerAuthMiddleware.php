<?php

namespace App\Modules\Planner\Middleware;

/**
 * PlannerAuthMiddleware
 * ---------------------
 * Middleware de autenticación para todo el módulo Planner.
 *
 * Reglas de acceso (uso personal de Martín, single-user):
 *
 *   1. Rutas exentas (login form, login submit, logout): pasan siempre.
 *   2. Token de cron válido (?token=... o cabecera X-Planner-Token): pasa.
 *   3. Sesión PHP con $_SESSION['planner_authed'] = true: pasa.
 *   4. Cualquier otro caso:
 *        - Si la URL es /planner/api/* → 401 JSON.
 *        - En otro caso → redirección a /planner/login.
 *
 * IMPORTANTE: ya NO hay bypass por APP_ENV=local. La autenticación se
 * exige también en desarrollo, porque ese era el sentido de añadir login.
 *
 * Este middleware se invoca desde el constructor de PlannerController.
 * El AuthController NO lo extiende, por eso sus rutas no entran aquí
 * (además están en la lista de exentas como doble seguro).
 */
class PlannerAuthMiddleware
{
    /** Rutas que NUNCA requieren sesión (relativas al raíz de la app). */
    private const EXEMPT_PATHS = [
        '/planner/login',
        '/planner/logout',
    ];

    public static function handle(): void
    {
        $path = self::currentPath();

        // 1) Rutas exentas (formulario de login, etc).
        if (in_array($path, self::EXEMPT_PATHS, true)) {
            return;
        }

        // 2) Token de cron interno.
        if (self::isValidTickToken()) {
            return;
        }

        // 3) Sesión válida.
        if (!empty($_SESSION['planner_authed'])) {
            return;
        }

        // 4) No autorizado: respuesta según tipo de petición.
        if (str_starts_with($path, '/planner/api/')) {
            self::respondJsonUnauthorized();
        } else {
            self::redirectToLogin();
        }
    }

    /**
     * Calcula el path "limpio" (sin subdirectorio base ni query string),
     * replicando la lógica de Request::getPath() para no acoplarlo.
     */
    private static function currentPath(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $pos = strpos($uri, '?');
        if ($pos !== false) {
            $uri = substr($uri, 0, $pos);
        }
        $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        if ($scriptDir !== '' && str_starts_with($uri, $scriptDir)) {
            $uri = substr($uri, strlen($scriptDir));
        }
        return $uri === '' ? '/' : $uri;
    }

    /**
     * Valida el token compartido del cron contra PLANNER_TICK_TOKEN.
     * hash_equals previene timing attacks.
     */
    private static function isValidTickToken(): bool
    {
        $expected = $_ENV['PLANNER_TICK_TOKEN'] ?? '';
        if ($expected === '') {
            return false;
        }
        $provided = $_GET['token']
            ?? $_SERVER['HTTP_X_PLANNER_TOKEN']
            ?? '';
        return is_string($provided) && hash_equals($expected, $provided);
    }

    /**
     * Devuelve 401 JSON y termina la ejecución. Para llamadas a /planner/api/*.
     */
    private static function respondJsonUnauthorized(): void
    {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'unauthorized'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Redirige al formulario de login conservando el subdirectorio base.
     */
    private static function redirectToLogin(): void
    {
        $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        header('Location: ' . $scriptDir . '/planner/login');
        exit;
    }
}
