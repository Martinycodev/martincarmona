<?php

error_reporting(E_ALL);

// Ruta base del proyecto
define('BASE_PATH', __DIR__);

// Composer y variables de entorno
require_once BASE_PATH . '/vendor/autoload.php';
if (file_exists(BASE_PATH . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
    $dotenv->load();
}

// Mostrar errores solo en desarrollo
ini_set('display_errors', ($_ENV['APP_ENV'] ?? 'production') === 'development' ? 1 : 0);

// Handler global de errores y excepciones
require_once BASE_PATH . '/core/ErrorHandler.php';
Core\ErrorHandler::register();

// Configuración de la app
$config = require_once BASE_PATH . '/config/config.php';
define('APP_BASE_PATH', $config['base_path']);

// Sesión segura
require_once BASE_PATH . '/config/session.php';
SessionConfig::configure();

// Base de datos (siempre disponible)
require_once BASE_PATH . '/config/database.php';

// Autoloader PSR-4
require_once BASE_PATH . '/core/Autoloader.php';
$autoloader = new Core\Autoloader();
$autoloader->register();
$autoloader->addNamespace('Core', BASE_PATH . '/core');
$autoloader->addNamespace('App', BASE_PATH . '/app');

// Restaurar sesión desde cookie "Recuérdame" si existe y no hay sesión activa
// (después del autoloader para poder usar \Core\Logger)
if (!isset($_SESSION['user_id']) && isset($_COOKIE['user_id'])) {
    $userId = (int) $_COOKIE['user_id'];
    try {
        $db = \Database::connect();
        $stmt = $db->prepare("SELECT id, name, email, rol, propietario_id, trabajador_id FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $_SESSION['user_id']             = $user['id'];
            $_SESSION['user_name']           = $user['name'];
            $_SESSION['user_email']          = $user['email'];
            $_SESSION['user_rol']            = $user['rol'] ?? 'empresa';
            $_SESSION['user_propietario_id'] = $user['propietario_id'] ?? null;
            $_SESSION['user_trabajador_id']  = $user['trabajador_id'] ?? null;
        }
        $stmt->close();
    } catch (\Throwable $e) {
        \Core\Logger::security()->error("Error restaurando sesión desde cookie: " . $e->getMessage());
    }
}

// Router
$router = new Core\Router();

// Cargar rutas
require BASE_PATH . '/routes/web.php';

// Ejecutar
$router->run();
