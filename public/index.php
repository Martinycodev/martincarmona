<?php

define('ROOT_PATH', dirname(__DIR__));

require_once ROOT_PATH . '/vendor/autoload.php';

// Cargar variables de entorno si existe .env
if (file_exists(ROOT_PATH . '/.env')) {
    $lines = file(ROOT_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (!str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

// Iniciar sesión para CSRF y flash messages
session_start();

// Inicializar ViteHelper
$isDev = ($_ENV['APP_ENV'] ?? 'production') === 'local';
\App\Core\ViteHelper::init($isDev, ROOT_PATH);

// Arrancar aplicación
$app = new \App\Core\Application(ROOT_PATH);

// Cargar rutas
$router = $app->router;
require_once ROOT_PATH . '/src/routes.php';

$app->run();
