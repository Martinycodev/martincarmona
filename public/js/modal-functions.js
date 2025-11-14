/**
 * Funciones globales para manejo de modales
 * Compatible con navegación AJAX
 */

// Función para obtener la ruta base de la aplicación
function getBaseUrl() {
    console.log('getBaseUrl called, current pathname:', window.location.pathname);
    
    // Obtener la ruta base desde el elemento base o calcularla
    const baseElement = document.querySelector('base');
    if (baseElement && baseElement.href) {
        const basePath = new URL(baseElement.href).pathname;
        console.log('Base element found:', basePath);
        return basePath;
    }
    
    // Si no hay elemento base, calcular desde la URL actual
    const path = window.location.pathname;
    const pathParts = path.split('/');
    console.log('Path parts:', pathParts);
    
    // Buscar 'martincarmona' en la ruta
    const martincarmonaIndex = pathParts.indexOf('martincarmona');
    console.log('martincarmona index:', martincarmonaIndex);
    
    if (martincarmonaIndex !== -1) {
        const basePath = '/' + pathParts.slice(1, martincarmonaIndex + 1).join('/');
        console.log('Calculated base path:', basePath);
        return basePath;
    }
    
    // Fallback: usar la ruta actual
    console.log('Using fallback path:', path);
    return path;
}

// Variable global para la ruta base
window.APP_BASE_URL = getBaseUrl();
console.log('Base URL detected:', window.APP_BASE_URL);

// Función helper para construir URLs
function buildUrl(path) {
    // Asegurar que path empiece con /
    if (!path.startsWith('/')) {
        path = '/' + path;
    }
    
    // Combinar base URL con path
    const fullUrl = window.APP_BASE_URL + path;
    console.log('buildUrl called with path:', path, 'result:', fullUrl);
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
    
    // Obtener dimensiones del viewport
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    const windowHeight = window.innerHeight;
    
    // Calcular posición Y para centrar en el viewport actual
    // Dejar espacio para que el modal sea completamente visible
    const modalHeight = Math.min(windowHeight * 0.8, 600); // Máximo 80% del viewport o 600px
    const targetY = scrollTop + (windowHeight - modalHeight) / 2;
    
    // Asegurar que el modal esté completamente visible
    const minY = scrollTop + 20; // 20px desde el top del viewport
    const maxY = scrollTop + windowHeight - modalHeight - 20; // 20px desde el bottom
    
    const finalY = Math.max(minY, Math.min(targetY, maxY));
    
    // Aplicar posicionamiento dinámico
    modalContent.classList.add('dynamic-position');
    modalContent.style.top = finalY + 'px';
}

// Función genérica para abrir modales
function openModal(modalId, buttonElement = null) {
    console.log('Opening modal:', modalId);
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
        positionModalInViewport(modal);
        
        // Enfocar el primer input si existe
        const firstInput = modal.querySelector('input, textarea, select');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 100);
        }
        console.log('Modal opened successfully:', modalId);
    } else {
        console.error('Modal not found:', modalId);
    }
}

// Función genérica para cerrar modales
function closeModal(modalId) {
    console.log('Closing modal:', modalId);
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
        console.log('Modal closed successfully:', modalId);
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
function handleApiResponse(response, context = 'operación') {
    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
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

// Inicializar modales cuando se carga el DOM
document.addEventListener('DOMContentLoaded', function() {
    console.log('Modal functions loaded and initializing...');
    initializeGlobalModals();
});

// Función para inicializar modales globales
function initializeGlobalModals() {
    console.log('Initializing global modals...');
    
    // Event listeners para cerrar modales al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal')) {
            const modalId = e.target.id;
            console.log('Clicked on modal:', modalId);
            if (modalId) {
                closeModal(modalId);
            }
        }
    });
    
    // Event listeners para botones de cerrar
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('close') || e.target.classList.contains('close-btn')) {
            const modal = e.target.closest('.modal');
            console.log('Clicked on close button, modal:', modal);
            if (modal) {
                closeModal(modal.id);
            }
        }
    });
    
    console.log('Global modals initialized');
}

// Función para reinicializar modales después de carga AJAX
function reinitializeModals() {
    initializeGlobalModals();
}

// Exportar funciones para uso global
window.showToast = showToast;
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

// Verificar que las funciones se exportaron correctamente
console.log('Functions exported to window:', {
    showToast: typeof window.showToast,
    openModal: typeof window.openModal,
    closeModal: typeof window.closeModal,
    reinitializeModals: typeof window.reinitializeModals
});
