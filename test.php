<?php

// Configuración de errores para desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Definir la ruta base del proyecto
define('BASE_PATH', __DIR__);

// Cargar configuración
$config = require_once BASE_PATH . '/config/config.php';

echo "<h2>Información del Sistema</h2>";
echo "BASE_PATH: " . BASE_PATH . "<br>";
echo "APP_BASE_PATH: " . $config['base_path'] . "<br>";
echo "Ruta de vistas: " . BASE_PATH . "/app/Views/tareas/index.php<br>";
echo "¿Existe la vista?: " . (file_exists(BASE_PATH . "/app/Views/tareas/index.php") ? "SÍ" : "NO") . "<br>";

echo "<h3>URLs de prueba:</h3>";
echo "Página principal: <a href='" . $config['base_path'] . "'>" . $config['base_path'] . "</a><br>";
echo "Tareas: <a href='" . $config['base_path'] . "/tareas'>" . $config['base_path'] . "/tareas</a><br>";
echo "Crear tarea: <a href='" . $config['base_path'] . "/tareas/crear'>" . $config['base_path'] . "/tareas/crear</a><br>";

// Verificar estructura de directorios
echo "<h3>Estructura del proyecto:</h3>";
echo "<pre>";
print_r(scandir(BASE_PATH));
echo "</pre>";

echo "<h3>Contenido de app/Views:</h3>";
echo "<pre>";
print_r(scandir(BASE_PATH . "/app/Views"));
echo "</pre>";

echo "<h3>Contenido de app/Views/tareas:</h3>";
echo "<pre>";
print_r(scandir(BASE_PATH . "/app/Views/tareas"));
echo "</pre>";
