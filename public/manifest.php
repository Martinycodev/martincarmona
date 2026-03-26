<?php
/**
 * PWA Web App Manifest — generado dinámicamente.
 *
 * Inyecta APP_BASE_PATH desde .env para que las rutas funcionen
 * tanto en localhost/martincarmona como en producción (dominio raíz).
 */

// Cargar .env para obtener APP_BASE_PATH
require_once dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists(dirname(__DIR__) . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();
}

$base = $_ENV['APP_BASE_PATH'] ?? '';

// Tamaños de iconos PNG disponibles
$sizes = [72, 96, 128, 144, 152, 192, 384, 512];

// Construir array de iconos
$icons = [];

// SVG (tamaño "any" para navegadores modernos)
$icons[] = [
    'src'     => $base . '/public/img/favicon.svg',
    'sizes'   => 'any',
    'type'    => 'image/svg+xml',
    'purpose' => 'any'
];

// PNGs con purpose "any"
foreach ($sizes as $s) {
    $icons[] = [
        'src'     => $base . '/public/img/icons/icon-' . $s . 'x' . $s . '.png',
        'sizes'   => $s . 'x' . $s,
        'type'    => 'image/png',
        'purpose' => 'any'
    ];
}

// PNGs con purpose "maskable" (solo los tamaños grandes)
foreach ([192, 384, 512] as $s) {
    $icons[] = [
        'src'     => $base . '/public/img/icons/icon-' . $s . 'x' . $s . '.png',
        'sizes'   => $s . 'x' . $s,
        'type'    => 'image/png',
        'purpose' => 'maskable'
    ];
}

$manifest = [
    'name'             => 'MiOlivar — Gestión Agrícola',
    'short_name'       => 'MiOlivar',
    'description'      => 'Sistema de gestión integral para explotaciones agrícolas de olivar',
    'start_url'        => $base . '/dashboard',
    'scope'            => $base . '/',
    'display'          => 'standalone',
    'background_color' => '#1a1a1a',
    'theme_color'      => '#4caf50',
    'orientation'      => 'any',
    'lang'             => 'es',
    'icons'            => $icons
];

header('Content-Type: application/manifest+json');
echo json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
