<?php

namespace App\Modules\Planner\Controllers;

use App\Core\Controller;
use App\Modules\Planner\Support\Csrf;

/**
 * AuthController
 * --------------
 * Login y logout del módulo Planner.
 *
 * Importante: NO extiende de PlannerController. Si lo hiciera, el
 * middleware de autenticación se ejecutaría en el constructor y nadie
 * podría llegar nunca al formulario de login (paradoja del huevo y la
 * gallina). En su lugar extiende del Controller base del core.
 *
 * Las rutas de auth están exentas del middleware en PlannerAuthMiddleware.
 */
class AuthController extends Controller
{
    /**
     * Carpeta de vistas propia del módulo (aislada del sitio público).
     * Se usa en renderPlannerView() abajo.
     */
    private const VIEWS_DIR = __DIR__ . '/../Views/';

    /** Máximo de intentos fallidos antes de bloquear temporalmente. */
    private const MAX_ATTEMPTS = 5;
    /** Ventana de bloqueo en segundos (15 minutos). */
    private const LOCKOUT_SECONDS = 900;

    /**
     * GET /planner/login
     * Muestra el formulario. Si ya hay sesión, redirige a /planner/health.
     */
    public function showLogin(): string
    {
        if (!empty($_SESSION['planner_authed'])) {
            header('Location: ' . $this->basePath() . '/planner/health');
            exit;
        }

        return $this->renderPlannerView('login', [
            'error' => $_SESSION['planner_login_error'] ?? null,
            'csrf'  => Csrf::field(),
        ]);
        // Nota: el error se consume justo después en clearFlash() vía
        // el unset al final del render. Se hace en login() para que
        // sobreviva al redirect.
    }

    /**
     * POST /planner/login
     * Verifica credenciales contra .env y crea la sesión.
     */
    public function login(): string
    {
        // Limpio cualquier error flash anterior antes de procesar.
        unset($_SESSION['planner_login_error']);

        // 1) CSRF
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            return $this->failLogin('Token CSRF inválido. Recarga la página.');
        }

        // 2) Rate limiting por sesión
        if ($this->isLockedOut()) {
            return $this->failLogin('Demasiados intentos. Espera 15 minutos.');
        }

        // 3) Lectura cruda de POST: NO usamos $request->get('password')
        //    porque Request::getBody() aplica FILTER_SANITIZE_SPECIAL_CHARS
        //    que rompería contraseñas con caracteres como < > " ' &.
        $username = trim((string)($_POST['username'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        $expectedUser = $_ENV['PLANNER_USERNAME']      ?? '';
        $expectedHash = $_ENV['PLANNER_PASSWORD_HASH'] ?? '';

        // 4) Si .env no está configurado, fallo explícito (en lugar de
        //    permitir login con strings vacíos).
        if ($expectedUser === '' || $expectedHash === '') {
            return $this->failLogin('El módulo Planner no tiene credenciales configuradas en .env');
        }

        // 5) Verificación timing-safe de usuario + contraseña.
        //    Comparamos siempre ambos campos aunque el usuario no coincida,
        //    para no filtrar por timing si el username existe o no.
        $userOk = hash_equals($expectedUser, $username);
        $passOk = password_verify($password, $expectedHash);

        if (!$userOk || !$passOk) {
            $this->registerFailedAttempt();
            return $this->failLogin('Credenciales incorrectas.');
        }

        // 6) Login válido: regenerar ID de sesión (anti session-fixation)
        //    y marcar como autenticado.
        session_regenerate_id(true);
        $_SESSION['planner_authed']    = true;
        $_SESSION['planner_login_at']  = time();
        unset($_SESSION['planner_login_attempts']);

        header('Location: ' . $this->basePath() . '/planner/health');
        exit;
    }

    /**
     * GET /planner/logout
     * Destruye únicamente las claves del planner. No matamos la sesión
     * entera por si en el futuro la web pública usara la misma sesión
     * para flash messages, etc.
     */
    public function logout(): string
    {
        unset(
            $_SESSION['planner_authed'],
            $_SESSION['planner_login_at'],
            $_SESSION['planner_csrf'],
            $_SESSION['planner_login_attempts']
        );
        session_regenerate_id(true);

        header('Location: ' . $this->basePath() . '/planner/login');
        exit;
    }

    // ─────────────────────────────────────────────────────────────
    //  Helpers privados
    // ─────────────────────────────────────────────────────────────

    /**
     * Guarda un mensaje de error en sesión y redirige al formulario.
     * El "flash" sobrevive un único request.
     */
    private function failLogin(string $message): string
    {
        $_SESSION['planner_login_error'] = $message;
        header('Location: ' . $this->basePath() . '/planner/login');
        exit;
    }

    /**
     * Comprueba si la sesión ha superado el límite de intentos fallidos.
     */
    private function isLockedOut(): bool
    {
        $attempts = $_SESSION['planner_login_attempts'] ?? null;
        if (!$attempts) {
            return false;
        }
        if ($attempts['count'] < self::MAX_ATTEMPTS) {
            return false;
        }
        // Si ha pasado la ventana, resetear contador.
        if (time() - $attempts['first_at'] > self::LOCKOUT_SECONDS) {
            unset($_SESSION['planner_login_attempts']);
            return false;
        }
        return true;
    }

    /**
     * Incrementa el contador de intentos fallidos.
     */
    private function registerFailedAttempt(): void
    {
        $attempts = $_SESSION['planner_login_attempts'] ?? ['count' => 0, 'first_at' => time()];
        $attempts['count']++;
        $_SESSION['planner_login_attempts'] = $attempts;
    }

    /**
     * Devuelve el subdirectorio base de la app (vacío en producción,
     * "/martincarmona/public" en local). Necesario para que los redirects
     * funcionen igual en ambos entornos. Equivalente a la lógica inversa
     * de Request::getPath().
     */
    private function basePath(): string
    {
        $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        return $scriptDir;
    }

    /**
     * Renderiza una vista del módulo Planner sin pasar por el layout
     * del sitio público. Las vistas viven en src/Modules/Planner/Views/.
     */
    private function renderPlannerView(string $view, array $params = []): string
    {
        $viewPath = self::VIEWS_DIR . $view . '.php';
        if (!file_exists($viewPath)) {
            throw new \Exception("Planner view [{$view}] not found", 500);
        }
        extract($params);
        ob_start();
        include $viewPath;
        // Limpieza del flash de error: se muestra una sola vez.
        unset($_SESSION['planner_login_error']);
        return ob_get_clean();
    }
}
