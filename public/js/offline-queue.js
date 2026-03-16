/**
 * Offline Queue — Cola de formularios pendientes con IndexedDB
 *
 * Cuando el usuario envía un formulario sin conexión, se guarda aquí.
 * Al recuperar la conexión, se reenvían automáticamente en orden.
 *
 * API pública:
 *   OfflineQueue.enqueue(url, options)  — guarda una petición fallida
 *   OfflineQueue.flush()                — reenvía todas las pendientes
 *   OfflineQueue.count()                — nº de peticiones en cola
 */
var OfflineQueue = (function() {
    var DB_NAME    = 'martincarmona_offline';
    var DB_VERSION = 1;
    var STORE_NAME = 'pending_requests';
    var db = null;

    // ── Abrir/crear la base de datos IndexedDB ──────────────────────────
    function openDB() {
        return new Promise(function(resolve, reject) {
            if (db) { resolve(db); return; }

            var request = indexedDB.open(DB_NAME, DB_VERSION);

            request.onupgradeneeded = function(e) {
                var database = e.target.result;
                if (!database.objectStoreNames.contains(STORE_NAME)) {
                    // autoIncrement para tener un ID único por petición
                    database.createObjectStore(STORE_NAME, { keyPath: 'id', autoIncrement: true });
                }
            };

            request.onsuccess = function(e) {
                db = e.target.result;
                resolve(db);
            };

            request.onerror = function() {
                reject('Error abriendo IndexedDB');
            };
        });
    }

    // ── Guardar una petición en la cola ──────────────────────────────────
    // Recibe la URL y las opciones del fetch original (method, headers, body)
    function enqueue(url, options) {
        return openDB().then(function(database) {
            return new Promise(function(resolve, reject) {
                var tx    = database.transaction(STORE_NAME, 'readwrite');
                var store = tx.objectStore(STORE_NAME);

                // Serializar la petición para poder reproducirla después
                var record = {
                    url:       url,
                    method:    options.method || 'POST',
                    headers:   {},
                    body:      options.body || null,
                    timestamp: Date.now()
                };

                // Copiar headers (puede ser objeto plano o Headers)
                if (options.headers) {
                    if (options.headers instanceof Headers) {
                        options.headers.forEach(function(val, key) {
                            record.headers[key] = val;
                        });
                    } else {
                        record.headers = Object.assign({}, options.headers);
                    }
                }

                var req = store.add(record);
                req.onsuccess = function() {
                    updateBadge();
                    resolve();
                };
                req.onerror = function() { reject('Error guardando en cola offline'); };
            });
        });
    }

    // ── Obtener todas las peticiones pendientes ─────────────────────────
    function getAll() {
        return openDB().then(function(database) {
            return new Promise(function(resolve, reject) {
                var tx    = database.transaction(STORE_NAME, 'readonly');
                var store = tx.objectStore(STORE_NAME);
                var req   = store.getAll();

                req.onsuccess = function() { resolve(req.result || []); };
                req.onerror   = function() { reject('Error leyendo cola offline'); };
            });
        });
    }

    // ── Eliminar una petición de la cola ─────────────────────────────────
    function remove(id) {
        return openDB().then(function(database) {
            return new Promise(function(resolve, reject) {
                var tx    = database.transaction(STORE_NAME, 'readwrite');
                var store = tx.objectStore(STORE_NAME);
                var req   = store.delete(id);

                req.onsuccess = function() { resolve(); };
                req.onerror   = function() { reject('Error eliminando de cola'); };
            });
        });
    }

    // ── Contar peticiones pendientes ─────────────────────────────────────
    function count() {
        return openDB().then(function(database) {
            return new Promise(function(resolve, reject) {
                var tx    = database.transaction(STORE_NAME, 'readonly');
                var store = tx.objectStore(STORE_NAME);
                var req   = store.count();

                req.onsuccess = function() { resolve(req.result); };
                req.onerror   = function() { resolve(0); };
            });
        });
    }

    // ── Flush: reenviar todas las peticiones pendientes ──────────────────
    // Se ejecuta automáticamente al recuperar conexión.
    // Las reenvía en orden (FIFO) y elimina las que tengan éxito.
    function flush() {
        return getAll().then(function(items) {
            if (items.length === 0) return Promise.resolve(0);

            var synced = 0;
            // Cadena secuencial para respetar el orden
            var chain = Promise.resolve();

            items.forEach(function(item) {
                chain = chain.then(function() {
                    // Obtener CSRF token fresco (puede haber cambiado)
                    var meta  = document.querySelector('meta[name="csrf-token"]');
                    var token = meta ? meta.getAttribute('content') : null;
                    if (token && item.headers) {
                        item.headers['X-CSRF-TOKEN'] = token;
                    }

                    // Usar _fetch directamente para evitar que el interceptor
                    // vuelva a encolar si falla
                    var fetchFn = window._originalFetch || window.fetch;
                    return fetchFn(item.url, {
                        method:  item.method,
                        headers: item.headers,
                        body:    item.body
                    }).then(function(response) {
                        if (response.ok) {
                            synced++;
                            return remove(item.id);
                        }
                        // Si el servidor rechaza (4xx/5xx), también la quitamos
                        // para no reintentar infinitamente
                        if (response.status >= 400) {
                            console.warn('Offline sync: servidor rechazó petición', item.url, response.status);
                            return remove(item.id);
                        }
                    }).catch(function() {
                        // Sigue sin conexión, dejar en la cola
                    });
                });
            });

            return chain.then(function() {
                updateBadge();
                if (synced > 0 && typeof showToast === 'function') {
                    showToast(synced + ' formulario' + (synced > 1 ? 's' : '') + ' sincronizado' + (synced > 1 ? 's' : ''), 'success');
                }
                return synced;
            });
        });
    }

    // ── Badge visual: muestra cuántos formularios hay pendientes ─────────
    function updateBadge() {
        count().then(function(n) {
            var badge = document.getElementById('offline-queue-badge');
            if (!badge) return;

            if (n > 0) {
                badge.textContent = n;
                badge.style.display = 'inline-flex';
            } else {
                badge.style.display = 'none';
            }
        });
    }

    // ── Banner de estado de conexión ─────────────────────────────────────
    function showOfflineBanner() {
        var banner = document.getElementById('offline-banner');
        if (banner) { banner.classList.add('visible'); return; }

        banner = document.createElement('div');
        banner.id = 'offline-banner';
        banner.innerHTML = '⚠ Sin conexión — los formularios se guardarán y enviarán al reconectar';
        document.body.appendChild(banner);
        // Forzar reflow para que la transición funcione
        banner.offsetHeight;
        banner.classList.add('visible');
    }

    function hideOfflineBanner() {
        var banner = document.getElementById('offline-banner');
        if (banner) banner.classList.remove('visible');
    }

    // ── Listeners de conexión ────────────────────────────────────────────
    function init() {
        // Estado inicial
        if (!navigator.onLine) showOfflineBanner();

        window.addEventListener('online', function() {
            hideOfflineBanner();
            // Esperar un momento para que la red se estabilice
            setTimeout(function() { flush(); }, 1500);
        });

        window.addEventListener('offline', function() {
            showOfflineBanner();
        });

        // Actualizar badge al cargar
        updateBadge();
    }

    // Inicializar al cargar
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // API pública
    return {
        enqueue:     enqueue,
        flush:       flush,
        count:       count,
        updateBadge: updateBadge
    };
})();
