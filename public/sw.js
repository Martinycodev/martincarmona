/**
 * Service Worker — MartinCarmona PWA
 * Cachea el shell de la app (CSS, JS, fuentes) para carga rápida.
 * Muestra pantalla offline si no hay conexión.
 */

const CACHE_NAME = 'martincarmona-v2';
const BASE = '/martincarmona';

// Archivos del shell de la aplicación que se cachean al instalar
const SHELL_FILES = [
    BASE + '/public/css/styles.css',
    BASE + '/public/css/autocomplete.css',
    BASE + '/public/js/modal-functions.js',
    BASE + '/public/js/offline-queue.js',
    BASE + '/public/js/ajax-navigation.js',
    BASE + '/public/js/task-sidebar.js',
    BASE + '/public/img/favicon.svg',
    BASE + '/public/offline.html'
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
