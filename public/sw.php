<?php
/**
 * Service Worker — MartinCarmona PWA (generado dinámicamente).
 *
 * Inyecta APP_BASE_PATH y genera un hash de versión automático
 * basado en la fecha de modificación de los archivos del shell.
 */

// Cargar .env para obtener APP_BASE_PATH
require_once dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists(dirname(__DIR__) . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();
}

$base = $_ENV['APP_BASE_PATH'] ?? '';

// Archivos del shell que se cachean al instalar
$shellFiles = [
    '/public/css/styles.css',
    '/public/css/autocomplete.css',
    '/public/js/modal-functions.js',
    '/public/js/offline-queue.js',
    '/public/js/ajax-navigation.js',
    '/public/js/task-sidebar.js',
    '/public/img/favicon.svg',
    '/public/offline.html',
];

// Generar versión del cache basada en la última modificación de los archivos
$maxMtime = 0;
foreach ($shellFiles as $file) {
    $path = dirname(__DIR__) . $file;
    if (file_exists($path)) {
        $mtime = filemtime($path);
        if ($mtime > $maxMtime) $maxMtime = $mtime;
    }
}
$cacheVersion = 'martincarmona-v' . $maxMtime;

// Servir como JavaScript
header('Content-Type: application/javascript');
header('Service-Worker-Allowed: ' . $base . '/');
?>
/**
 * Service Worker — MartinCarmona PWA
 * Cachea el shell de la app (CSS, JS, fuentes) para carga rápida.
 * Muestra pantalla offline si no hay conexión.
 *
 * Versión del cache generada automáticamente: <?= $cacheVersion ?>

 */

var CACHE_NAME = '<?= $cacheVersion ?>';
var BASE = '<?= $base ?>';

// Archivos del shell de la aplicación que se cachean al instalar
var SHELL_FILES = [
<?php foreach ($shellFiles as $file): ?>
    BASE + '<?= $file ?>',
<?php endforeach; ?>
];

// Instalar: cachear shell
self.addEventListener('install', function(event) {
    event.waitUntil(
        caches.open(CACHE_NAME).then(function(cache) {
            return cache.addAll(SHELL_FILES);
        })
    );
    // Activar inmediatamente sin esperar a que se cierren las pestañas
    self.skipWaiting();
});

// Activar: limpiar caches antiguas
self.addEventListener('activate', function(event) {
    event.waitUntil(
        caches.keys().then(function(names) {
            return Promise.all(
                names.filter(function(name) { return name !== CACHE_NAME; })
                     .map(function(name) { return caches.delete(name); })
            );
        })
    );
    self.clients.claim();
});

// Fetch: Network-first para HTML, Cache-first para assets estáticos
self.addEventListener('fetch', function(event) {
    var url = new URL(event.request.url);

    // Solo interceptar peticiones del mismo origen
    if (url.origin !== location.origin) return;

    // Assets estáticos (CSS, JS, imágenes): cache-first
    if (url.pathname.match(/\.(css|js|svg|png|jpg|jpeg|gif|ico|woff2?)$/)) {
        event.respondWith(
            caches.match(event.request).then(function(cached) {
                return cached || fetch(event.request).then(function(response) {
                    // Cachear la respuesta para la próxima vez
                    var clone = response.clone();
                    caches.open(CACHE_NAME).then(function(cache) {
                        cache.put(event.request, clone);
                    });
                    return response;
                });
            })
        );
        return;
    }

    // Peticiones HTML/API: network-first, fallback a offline
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request).catch(function() {
                return caches.match(BASE + '/public/offline.html');
            })
        );
    }
});
