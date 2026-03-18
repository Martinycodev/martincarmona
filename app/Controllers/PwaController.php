<?php

namespace App\Controllers;

/**
 * PwaController — Sirve los archivos PWA (manifest y service worker)
 * a través del router para evitar el bloqueo de .htaccess sobre archivos .php directos.
 */
class PwaController extends BaseController
{
    /**
     * Sirve el Web App Manifest con rutas dinámicas basadas en APP_BASE_PATH.
     */
    public function manifest()
    {
        $base = APP_BASE_PATH;

        // Tamaños de iconos PNG disponibles
        $sizes = [72, 96, 128, 144, 152, 192, 384, 512];

        // Construir array de iconos (solo PNGs — el SVG usa <text> y Chrome no lo carga como icono)
        $icons = [];

        // PNGs con purpose "any"
        foreach ($sizes as $s) {
            $icons[] = [
                'src'     => $base . '/public/img/icons/icon-' . $s . 'x' . $s . '.png',
                'sizes'   => $s . 'x' . $s,
                'type'    => 'image/png',
                'purpose' => 'any'
            ];
        }

        // PNGs con purpose "maskable" (solo tamaños grandes)
        foreach ([192, 384, 512] as $s) {
            $icons[] = [
                'src'     => $base . '/public/img/icons/icon-' . $s . 'x' . $s . '.png',
                'sizes'   => $s . 'x' . $s,
                'type'    => 'image/png',
                'purpose' => 'maskable'
            ];
        }

        // Screenshots para el diálogo de instalación enriquecido
        $screenshots = [
            [
                'src'         => $base . '/public/img/icons/screenshot-desktop.png',
                'sizes'       => '1280x720',
                'type'        => 'image/png',
                'form_factor' => 'wide',
                'label'       => 'Dashboard de gestión agrícola'
            ],
            [
                'src'         => $base . '/public/img/icons/screenshot-mobile.png',
                'sizes'       => '750x1334',
                'type'        => 'image/png',
                'form_factor' => 'narrow',
                'label'       => 'Vista móvil del dashboard'
            ]
        ];

        $manifest = [
            'name'             => 'MartinCarmona — Gestión Agrícola',
            'short_name'       => 'MartinCarmona',
            'description'      => 'Sistema de gestión integral para explotaciones agrícolas de olivar',
            'start_url'        => $base . '/dashboard',
            'scope'            => $base . '/',
            'display'          => 'standalone',
            'background_color' => '#1a1a1a',
            'theme_color'      => '#4caf50',
            'orientation'      => 'any',
            'lang'             => 'es',
            'icons'            => $icons,
            'screenshots'      => $screenshots
        ];

        header('Content-Type: application/manifest+json');
        echo json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Sirve el Service Worker con base path dinámico y versionado automático.
     */
    public function sw()
    {
        $base = APP_BASE_PATH;

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

        // Generar versión del cache basada en la última modificación
        $maxMtime = 0;
        foreach ($shellFiles as $file) {
            $path = BASE_PATH . $file;
            if (file_exists($path)) {
                $mtime = filemtime($path);
                if ($mtime > $maxMtime) $maxMtime = $mtime;
            }
        }
        $cacheVersion = 'martincarmona-v' . $maxMtime;

        // Construir lista JS de archivos
        $shellFilesJs = '';
        foreach ($shellFiles as $file) {
            $shellFilesJs .= "    BASE + '" . $file . "',\n";
        }

        header('Content-Type: application/javascript');
        header('Service-Worker-Allowed: ' . $base . '/');

        echo <<<JS
/**
 * Service Worker — MartinCarmona PWA
 * Cachea el shell de la app (CSS, JS, fuentes) para carga rápida.
 * Muestra pantalla offline si no hay conexión.
 * Versión: {$cacheVersion}
 */

var CACHE_NAME = '{$cacheVersion}';
var BASE = '{$base}';

var SHELL_FILES = [
{$shellFilesJs}];

// Instalar: cachear shell
self.addEventListener('install', function(event) {
    event.waitUntil(
        caches.open(CACHE_NAME).then(function(cache) {
            return cache.addAll(SHELL_FILES);
        })
    );
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

    if (url.origin !== location.origin) return;

    // Assets estáticos: cache-first
    if (url.pathname.match(/\\.(css|js|svg|png|jpg|jpeg|gif|ico|woff2?)$/)) {
        event.respondWith(
            caches.match(event.request).then(function(cached) {
                return cached || fetch(event.request).then(function(response) {
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

    // HTML: network-first, fallback a offline
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request).catch(function() {
                return caches.match(BASE + '/public/offline.html');
            })
        );
    }
});
JS;
        exit;
    }
}
