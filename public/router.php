<?php
/**
 * Router para `php -S` (servidor embebido de PHP)
 * -----------------------------------------------
 * El servidor built-in no procesa .htaccess. Este script replica
 * la regla del htaccess de public/:
 *
 *   - Si la URL apunta a un archivo o directorio real → servirlo
 *   - Cualquier otra ruta → redirigir a index.php (front controller)
 *
 * Uso desde la raíz del proyecto:
 *   php -S localhost:8000 -t public public/router.php
 */

$requested = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$filePath  = __DIR__ . $requested;

// Servir archivos estáticos reales tal cual (CSS, imágenes, fuentes…)
// `return false` indica al servidor built-in: "encárgate tú".
if ($requested !== '/' && file_exists($filePath) && !is_dir($filePath)) {
    return false;
}

// Resto: front controller
require __DIR__ . '/index.php';
