/**
 * Funciones globales para manejo de modales
 * Compatible con navegación AJAX
 */

// =====================================================================
// INTERCEPTOR CSRF
// Sobrescribe fetch() globalmente para añadir el token CSRF
// automáticamente en todas las peticiones POST/PUT/DELETE/PATCH.
// Así no hay que modificar cada llamada fetch individualmente.
// =====================================================================
(function () {
    const _fetch = window.fetch;
    // Guardar referencia al fetch original para que offline-queue.js
    // pueda reenviar sin pasar por el interceptor
    window._originalFetch = _fetch;

    window.fetch = function (url, options = {}) {
        const method = (options.method || 'GET').toUpperCase();

        // Solo añadir el token en peticiones que modifican datos
        if (['POST', 'PUT', 'DELETE', 'PATCH'].includes(method)) {
            // Obtener el token del meta tag añadido por CsrfMiddleware::getMetaTag()
            const meta = document.querySelector('meta[name="csrf-token"]');
            const token = meta ? meta.getAttribute('content') : null;

            if (token) {
                options.headers = options.headers || {};

                // Si headers es un objeto plano, añadir directamente
                if (typeof options.headers === 'object' && !(options.headers instanceof Headers)) {
                    options.headers['X-CSRF-TOKEN'] = token;
                } else if (options.headers instanceof Headers) {
                    options.headers.set('X-CSRF-TOKEN', token);
                }
            }
        }

        return _fetch.call(this, url, options).catch(function(error) {
            // Si es una petición de escritura y falló por red → encolar offline
            if (['POST', 'PUT', 'DELETE', 'PATCH'].includes(method)
                && typeof OfflineQueue !== 'undefined') {
                return OfflineQueue.enqueue(url, options).then(function() {
                    if (typeof showToast === 'function') {
                        showToast('Sin conexión — guardado para enviar después', 'warning');
                    }
                    // Devolver una Response simulada para que el código llamante
                    // no lance error no controlado
                    return new Response(JSON.stringify({
                        success: true,
                        offline: true,
                        message: 'Guardado offline, se enviará al reconectar'
                    }), {
                        status: 200,
                        headers: { 'Content-Type': 'application/json' }
                    });
                });
            }
            // Si es GET o no hay OfflineQueue, propagar el error
            throw error;
        });
    };
})();

// Función para obtener la ruta base de la aplicación
function getBaseUrl() {
    // Variable inyectada desde PHP en el layout (la más fiable)
    if (typeof window._APP_BASE_PATH !== 'undefined') {
        return window._APP_BASE_PATH;
    }

    // Fallback: elemento <base> en el HTML
    const baseElement = document.querySelector('base');
    if (baseElement && baseElement.href) {
        return new URL(baseElement.href).pathname;
    }

    // Fallback: buscar 'martincarmona' en la ruta (dev local con XAMPP)
    const pathParts = window.location.pathname.split('/');
    const idx = pathParts.indexOf('martincarmona');
    if (idx !== -1) {
        return '/' + pathParts.slice(1, idx + 1).join('/');
    }

    // Último recurso: dominio raíz
    return '';
}

// Variable global para la ruta base
window.APP_BASE_URL = getBaseUrl();

// Función helper para construir URLs
function buildUrl(path) {
    // Asegurar que path empiece con /
    if (!path.startsWith('/')) {
        path = '/' + path;
    }

    // Combinar base URL con path
    const fullUrl = window.APP_BASE_URL + path;
    return fullUrl;
}

// Función para mostrar notificaciones toast
function showToast(message, type = 'info') {
    // Buscar toast existente o crear uno nuevo
    let toast = document.getElementById('toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'toast';
        toast.className = 'toast';
        document.body.appendChild(toast);
    }

    toast.textContent = message;

    // Limpiar clases previas
    toast.className = `toast toast-${type}`;

    // Forzar reflow para reiniciar animación
    toast.offsetHeight;

    // Mostrar con animación de entrada
    toast.classList.add('show');

    // Ocultar con animación de salida después de 3 segundos
    setTimeout(() => {
        toast.classList.remove('show');
        toast.classList.add('hide');

        // Limpiar clase hide después de la animación
        setTimeout(() => {
            toast.classList.remove('hide');
        }, 400); // Duración de la transición
    }, 3000);
}

// Función para posicionar modal en el viewport actual
function positionModalInViewport(modal) {
    const modalContent = modal.querySelector('.modal-content');
    if (!modalContent) return;

    const windowHeight = window.innerHeight;

    // Calcular posición Y para centrar en el viewport
    // El padre .modal es position:fixed, así que no necesitamos sumar scrollTop
    const modalHeight = Math.min(windowHeight * 0.8, 600);
    const targetY = (windowHeight - modalHeight) / 2;

    // Asegurar que el modal no se salga del viewport
    const minY = 20;
    const maxY = windowHeight - modalHeight - 20;

    const finalY = Math.max(minY, Math.min(targetY, maxY));

    // Aplicar posicionamiento dinámico
    modalContent.classList.add('dynamic-position');
    modalContent.style.top = finalY + 'px';
}

// Función genérica para abrir modales
function openModal(modalId, buttonElement = null) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
        positionModalInViewport(modal);

        // Enfocar el primer input si existe
        const firstInput = modal.querySelector('input, textarea, select');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 100);
        }
    } else {
        console.error('Modal not found:', modalId);
    }
}

// Función genérica para cerrar modales
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        const modalContent = modal.querySelector('.modal-content');

        modal.style.display = 'none';

        // Limpiar posicionamiento dinámico
        if (modalContent) {
            modalContent.classList.remove('dynamic-position');
            modalContent.style.top = '';
        }

        // Limpiar formularios
        const forms = modal.querySelectorAll('form');
        forms.forEach(form => form.reset());
    } else {
        console.error('Modal not found for closing:', modalId);
    }
}

// Función para abrir sección de crear
function openCreateSection() {
    const createSection = document.getElementById('createSection');
    if (createSection) {
        createSection.style.display = 'block';

        // Enfocar el primer input
        const firstInput = createSection.querySelector('input, textarea, select');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 100);
        }
    }
}

// Función para cerrar sección de crear
function closeCreateSection() {
    const createSection = document.getElementById('createSection');
    if (createSection) {
        createSection.style.display = 'none';

        // Limpiar formularios
        const forms = createSection.querySelectorAll('form');
        forms.forEach(form => form.reset());
    }
}

// Función para validar DNI español
function validarDNI(dni) {
    // Expresión regular: 8 dígitos + 1 letra mayúscula
    const regexDNI = /^[0-9]{8}[A-Z]$/;

    if (!regexDNI.test(dni)) {
        return false;
    }

    // Validar la letra del DNI (opcional pero recomendado)
    const letras = 'TRWAGMYFPDXBNJZSQVHLCKE';
    const numero = parseInt(dni.substring(0, 8));
    const letra = dni.charAt(8);
    const letraCorrecta = letras.charAt(numero % 23);

    return letra === letraCorrecta;
}

// Convertir DNI a mayúsculas automáticamente
function formatearDNI(input) {
    input.value = input.value.toUpperCase();
}

// Función para manejar errores de fetch
function handleFetchError(error, context = 'operación') {
    console.error(`Error en ${context}:`, error);
    showToast(`Error de conexión en ${context}: ${error.message}`, 'error');
}

// Función para manejar respuestas de API
async function handleApiResponse(response, context = 'operación') {
    if (!response.ok) {
        // Intentar leer el mensaje de error del servidor
        try {
            const errorData = await response.json();
            throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
        } catch (parseError) {
            // Si no es JSON, lanzar con el código de estado
            if (parseError.message && !parseError.message.includes('HTTP error')) {
                throw parseError;
            }
            throw new Error(`HTTP error! status: ${response.status}`);
        }
    }
    return response.json();
}

// Función para actualizar tabla después de operaciones CRUD
function updateTableAfterOperation(operation, data) {
    if (data.success) {
        showToast(`${operation} realizado correctamente`, 'success');

        // Si hay una función de recarga específica, usarla
        if (typeof window.reloadTable === 'function') {
            window.reloadTable();
        } else {
            // Fallback: recargar la página
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        }
    } else {
        showToast(`Error al ${operation}: ${data.message || 'Error desconocido'}`, 'error');
    }
}

// Función para confirmar eliminación
function confirmDelete(itemName, itemType = 'elemento') {
    return confirm(`¿Estás seguro de que quieres eliminar ${itemType} "${itemName}"?`);
}

// ── Cerrar modales con Escape ────────────────────────────────────────────
document.addEventListener('keydown', function(e) {
    if (e.key !== 'Escape') return;

    // Cerrar toast de confirmación si existe
    var confirmEl = document.getElementById('toast-confirm');
    if (confirmEl) { confirmEl.remove(); return; }

    // Cerrar modal de día móvil
    if (typeof cerrarModalDia === 'function') {
        var mobileDayModal = document.getElementById('mobileDayModal');
        if (mobileDayModal && mobileDayModal.classList.contains('open')) {
            cerrarModalDia();
            return;
        }
    }

    // Cerrar el sidebar de tarea si está abierto
    var sidebar = document.getElementById('task-sidebar');
    if (sidebar && sidebar.classList.contains('open')) {
        if (window.taskSidebar) window.taskSidebar.close();
        return;
    }

    // Cerrar el modal visible más cercano
    var modals = document.querySelectorAll('.modal');
    for (var i = modals.length - 1; i >= 0; i--) {
        if (modals[i].style.display === 'flex' || modals[i].style.display === 'block') {
            modals[i].style.display = 'none';
            return;
        }
    }

    // Cerrar lightbox
    var lightbox = document.getElementById('img-lightbox');
    if (lightbox && lightbox.classList.contains('open')) {
        if (typeof closeLightbox === 'function') closeLightbox();
    }
});

// Inicializar modales cuando se carga el DOM
document.addEventListener('DOMContentLoaded', function () {
    initializeGlobalModals();
});

// Función para inicializar modales globales
function initializeGlobalModals() {

    // Event listeners para cerrar modales al hacer clic fuera
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('modal')) {
            const modalId = e.target.id;
            if (modalId) {
                closeModal(modalId);
            }
        }
    });

    // Event listeners para botones de cerrar
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('close') || e.target.classList.contains('close-btn')) {
            const modal = e.target.closest('.modal');
            if (modal) {
                closeModal(modal.id);
            }
        }
    });

}

// Función para reinicializar modales después de carga AJAX
function reinitializeModals() {
    initializeGlobalModals();
}

// ── Toast de confirmación (reemplazo de confirm() nativo) ────────────────
// Devuelve una Promise que resuelve true/false según el botón pulsado.
// Uso: showConfirm('¿Eliminar?').then(ok => { if (ok) ... })
function showConfirm(message, okText, cancelText) {
    okText     = okText     || 'Eliminar';
    cancelText = cancelText || 'Cancelar';

    return new Promise(function(resolve) {
        // Eliminar confirmación previa si existe
        var prev = document.getElementById('toast-confirm');
        if (prev) prev.remove();

        var el = document.createElement('div');
        el.id = 'toast-confirm';
        el.className = 'toast toast-confirm show';
        el.innerHTML = '<div class="toast-confirm-msg">' + message + '</div>'
            + '<div class="toast-confirm-btns">'
            +   '<button class="toast-confirm-cancel">' + cancelText + '</button>'
            +   '<button class="toast-confirm-ok">' + okText + '</button>'
            + '</div>';
        document.body.appendChild(el);

        el.querySelector('.toast-confirm-ok').addEventListener('click', function() {
            el.remove(); resolve(true);
        });
        el.querySelector('.toast-confirm-cancel').addEventListener('click', function() {
            el.remove(); resolve(false);
        });
    });
}

// ── Botón con estado loading ─────────────────────────────────────────────
// Uso: setButtonLoading(btn, true) al enviar, setButtonLoading(btn, false) al terminar
function setButtonLoading(btn, loading) {
    if (loading) {
        btn.classList.add('btn-loading');
        btn.disabled = true;
    } else {
        btn.classList.remove('btn-loading');
        btn.disabled = false;
    }
}

// ── Compresión de imágenes client-side (Canvas API) ──────────────────────
// Redimensiona y comprime imágenes antes de subirlas al servidor.
// Ideal para fotos de móvil que suelen pesar 5-15MB.
// Devuelve un File comprimido (JPEG) o el archivo original si no es imagen.
//
// Uso:
//   const comprimido = await compressImage(file);
//   formData.append('imagen', comprimido);
//
// Opciones por defecto:
//   maxWidth: 1920px, quality: 0.85 (JPEG)
function compressImage(file, options = {}) {
    const maxWidth = options.maxWidth || 1920;
    const quality  = options.quality  || 0.85;

    // Solo comprimir imágenes (no PDFs ni otros archivos)
    if (!file.type.startsWith('image/')) {
        return Promise.resolve(file);
    }

    return new Promise(function(resolve, reject) {
        var reader = new FileReader();

        reader.onload = function(e) {
            var img = new Image();

            img.onload = function() {
                // Calcular nuevas dimensiones manteniendo proporción
                var width  = img.width;
                var height = img.height;

                if (width > maxWidth) {
                    height = Math.round(height * (maxWidth / width));
                    width  = maxWidth;
                }

                // Dibujar en canvas y exportar como JPEG comprimido
                var canvas = document.createElement('canvas');
                canvas.width  = width;
                canvas.height = height;

                var ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                canvas.toBlob(function(blob) {
                    if (!blob) {
                        // Fallback: devolver archivo original si canvas falla
                        resolve(file);
                        return;
                    }

                    // Crear un File a partir del Blob para mantener compatibilidad
                    var compressedName = file.name.replace(/\.[^.]+$/, '.jpg');
                    var compressed = new File([blob], compressedName, {
                        type: 'image/jpeg',
                        lastModified: Date.now()
                    });

                    resolve(compressed);
                }, 'image/jpeg', quality);
            };

            img.onerror = function() {
                // Si no se puede cargar la imagen, devolver original
                resolve(file);
            };

            img.src = e.target.result;
        };

        reader.onerror = function() {
            resolve(file);
        };

        reader.readAsDataURL(file);
    });
}

// Comprimir múltiples archivos en paralelo
// Uso: const comprimidos = await compressImages(fileList);
function compressImages(files, options) {
    var promises = [];
    for (var i = 0; i < files.length; i++) {
        promises.push(compressImage(files[i], options));
    }
    return Promise.all(promises);
}

// Exportar funciones para uso global
window.compressImage  = compressImage;
window.compressImages = compressImages;
window.showToast = showToast;
window.showConfirm = showConfirm;
window.setButtonLoading = setButtonLoading;
window.positionModalInViewport = positionModalInViewport;
window.openModal = openModal;
window.closeModal = closeModal;
window.openCreateSection = openCreateSection;
window.closeCreateSection = closeCreateSection;
window.validarDNI = validarDNI;
window.formatearDNI = formatearDNI;
window.handleFetchError = handleFetchError;
window.handleApiResponse = handleApiResponse;
window.updateTableAfterOperation = updateTableAfterOperation;
window.confirmDelete = confirmDelete;
window.reinitializeModals = reinitializeModals;
window.buildUrl = buildUrl;


// ====== SISTEMA DE EDICIÓN DIRECTA (EDIT-IN-PLACE) ======
document.addEventListener('blur', async function (e) {
    if (e.target.classList.contains('editable')) {
        const element = e.target;
        const id = element.dataset.id;
        const campo = element.dataset.field;
        const valor = element.innerText.trim();

        // Evitar guardar si no hay cambios (opcional, requeriría guardar valor inicial)

        try {
            const response = await fetch(buildUrl('/tareas/actualizarCampo'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ id, campo, valor })
            });

            const data = await response.json();

            if (data.success) {
                element.classList.add('save-success');
                setTimeout(() => element.classList.remove('save-success'), 1000);
                // Si el cambio afecta a datos que se muestran en la tabla, recargar al cerrar modal
                window.needsReload = true;
            } else {
                showToast('Error al guardar: ' + data.message, 'error');
            }
        } catch (error) {
            console.error('Error auto-guardado:', error);
            showToast('Error de conexión', 'error');
        }
    }
}, true); // Usar captura para interceptar blur en contenteditable

// Manejar tecla Enter para perder el foco y disparar el blur
document.addEventListener('keydown', function (e) {
    if (e.target.classList.contains('editable') && e.key === 'Enter') {
        if (e.target.dataset.field !== 'descripcion') { // No evitar Enter en descripción si es textarea-like
            e.preventDefault();
            e.target.blur();
        }
    }
});
