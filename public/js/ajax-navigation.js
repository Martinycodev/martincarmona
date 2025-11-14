/**
 * Sistema de navegación AJAX para MartinCarmona.com
 * Evita recargas de página manteniendo el Dashboard y cambiando solo el contenido
 */

class AjaxNavigation {
    constructor() {
        this.currentUrl = window.location.pathname;
        this.contentContainer = null;
        this.loadingIndicator = null;
        this.init();
    }

    init() {
        // Crear contenedor para el contenido dinámico
        this.createContentContainer();
        
        // Crear indicador de carga
        this.createLoadingIndicator();
        
        // Interceptar clics en enlaces de navegación
        this.interceptNavigationLinks();
        
        // Manejar navegación del navegador (botones atrás/adelante)
        this.handleBrowserNavigation();
        
        // Cargar contenido inicial
        this.loadInitialContent();
    }

    createContentContainer() {
        // Buscar el contenedor principal existente
        const existingContainer = document.querySelector('.container');
        if (existingContainer) {
            this.contentContainer = existingContainer;
        } else {
            // Crear nuevo contenedor si no existe
            this.contentContainer = document.createElement('div');
            this.contentContainer.className = 'container';
            document.body.appendChild(this.contentContainer);
        }
    }

    createLoadingIndicator() {
        this.loadingIndicator = document.createElement('div');
        this.loadingIndicator.className = 'ajax-loading';
        this.loadingIndicator.innerHTML = `
            <div class="loading-content">
                <div class="loading-spinner"></div>
                <p>Cargando...</p>
            </div>
        `;
        this.loadingIndicator.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(42, 42, 42, 0.95);
            padding: 20px;
            border-radius: 12px;
            z-index: 10000;
            display: none;
            box-shadow: 0 8px 32px rgba(0,0,0,0.5);
            border: 1px solid #444;
        `;
        document.body.appendChild(this.loadingIndicator);
    }

    interceptNavigationLinks() {
        // Interceptar clics en enlaces del menú de navegación
        const navLinks = document.querySelectorAll('.nav-menu a[href]');
        navLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const url = link.getAttribute('href');
                this.navigateTo(url);
            });
        });

        // Interceptar clics en enlaces de acción
        const actionLinks = document.querySelectorAll('.action-card[href]');
        actionLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const url = link.getAttribute('href');
                this.navigateTo(url);
            });
        });

        // Interceptar clics en enlaces de navegación rápida
        const quickNavLinks = document.querySelectorAll('.nav-card[href]');
        quickNavLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const url = link.getAttribute('href');
                this.navigateTo(url);
            });
        });
    }

    handleBrowserNavigation() {
        // Manejar navegación del navegador (botones atrás/adelante)
        window.addEventListener('popstate', (e) => {
            if (e.state && e.state.url) {
                this.loadContent(e.state.url, false);
            } else {
                // Si no hay estado, cargar la URL actual
                this.loadContent(window.location.pathname, false);
            }
        });
    }

    loadInitialContent() {
        // Solo cargar contenido inicial si no hay contenido ya presente
        const hasContent = this.contentContainer && this.contentContainer.children.length > 0;
        if (!hasContent) {
            this.loadContent(this.currentUrl, false);
        }
    }

    async navigateTo(url) {
        // Actualizar la URL en el navegador sin recargar la página
        history.pushState({ url: url }, '', url);
        
        // Cargar el nuevo contenido
        await this.loadContent(url, true);
        
        // Actualizar la URL actual
        this.currentUrl = url;
    }

    async loadContent(url, showLoading = true) {
        try {
            if (showLoading) {
                this.showLoading();
            }

            // Realizar petición AJAX
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            });

            if (!response.ok) {
                throw new Error(`Error ${response.status}: ${response.statusText}`);
            }

            const html = await response.text();
            
            // Extraer solo el contenido del contenedor principal
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            // Buscar el contenido principal en la respuesta
            const newContent = doc.querySelector('.container') || doc.querySelector('main') || doc.body;
            
            if (newContent) {
                // Actualizar el contenido con animación suave
                await this.updateContentWithAnimation(newContent.innerHTML);
                
                // Re-interceptar enlaces en el nuevo contenido
                this.interceptNavigationLinks();
                
                // Ejecutar scripts del nuevo contenido
                this.executeScripts(newContent);
            }

        } catch (error) {
            console.error('Error cargando contenido:', error);
            this.showError('Error al cargar la página. Por favor, inténtalo de nuevo.');
        } finally {
            if (showLoading) {
                this.hideLoading();
            }
        }
    }

    async updateContentWithAnimation(newContent) {
        return new Promise((resolve) => {
            // Crear elemento temporal para la animación
            const tempContainer = document.createElement('div');
            tempContainer.innerHTML = newContent;
            tempContainer.style.cssText = `
                opacity: 0;
                transform: translateY(20px);
                transition: all 0.3s ease-out;
            `;

            // Reemplazar contenido
            this.contentContainer.innerHTML = '';
            this.contentContainer.appendChild(tempContainer);

            // Animar entrada
            requestAnimationFrame(() => {
                tempContainer.style.opacity = '1';
                tempContainer.style.transform = 'translateY(0)';
                
                setTimeout(() => {
                    // Limpiar estilos de animación
                    tempContainer.style.cssText = '';
                    resolve();
                }, 300);
            });
        });
    }

    executeScripts(container) {
        // Ejecutar scripts que puedan estar en el nuevo contenido
        const scripts = container.querySelectorAll('script');
        scripts.forEach(script => {
            if (script.src) {
                // Cargar script externo
                this.loadExternalScript(script.src);
            } else {
                // Ejecutar script inline
                const newScript = document.createElement('script');
                newScript.textContent = script.textContent;
                document.head.appendChild(newScript);
                document.head.removeChild(newScript);
            }
        });
        
        // Inicializar modales y funcionalidades específicas
        this.initializeModals(container);
        
        // Llamar a la función global de reinicialización de modales
        if (typeof window.reinitializeModals === 'function') {
            console.log('Reinitializing modals after AJAX load...');
            window.reinitializeModals();
        } else {
            console.warn('reinitializeModals function not found');
        }
    }
    
    loadExternalScript(src) {
        // Verificar si el script ya está cargado
        const existingScript = document.querySelector(`script[src="${src}"]`);
        if (existingScript) {
            console.log('Script already loaded:', src);
            return;
        }
        
        console.log('Loading external script:', src);
        const script = document.createElement('script');
        script.src = src;
        script.onload = () => {
            console.log('Script loaded successfully:', src);
        };
        script.onerror = () => {
            console.error('Error loading script:', src);
        };
        document.head.appendChild(script);
    }
    
    initializeModals(container) {
        // Reinicializar event listeners para modales
        this.initializeModalEventListeners(container);
        
        // Reinicializar formularios
        this.initializeForms(container);
        
        // Reinicializar botones de acción
        this.initializeActionButtons(container);
    }
    
    initializeModalEventListeners(container) {
        // Event listeners para cerrar modales al hacer clic fuera
        const modals = container.querySelectorAll('.modal');
        modals.forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    // Buscar función de cerrar modal específica
                    const closeFunction = this.getAttribute('data-close-function') || 'closeModal';
                    if (typeof window[closeFunction] === 'function') {
                        window[closeFunction]();
                    } else {
                        this.style.display = 'none';
                    }
                }
            });
        });
        
        // Event listeners para botones de cerrar
        const closeButtons = container.querySelectorAll('.close, .close-btn');
        closeButtons.forEach(button => {
            button.addEventListener('click', function() {
                const modal = this.closest('.modal');
                if (modal) {
                    const closeFunction = modal.getAttribute('data-close-function') || 'closeModal';
                    if (typeof window[closeFunction] === 'function') {
                        window[closeFunction]();
                    } else {
                        modal.style.display = 'none';
                    }
                }
            });
        });
    }
    
    initializeForms(container) {
        // Reinicializar formularios de crear
        const createForms = container.querySelectorAll('form[id$="Form"]');
        createForms.forEach(form => {
            // Remover listeners existentes para evitar duplicados
            const newForm = form.cloneNode(true);
            form.parentNode.replaceChild(newForm, form);
        });
    }
    
    initializeActionButtons(container) {
        // Reinicializar botones de acción (editar, eliminar, etc.)
        const actionButtons = container.querySelectorAll('button[onclick], a[onclick]');
        actionButtons.forEach(button => {
            // Los botones con onclick ya funcionan, pero podemos agregar validaciones adicionales
            button.addEventListener('click', function(e) {
                // Asegurar que las funciones globales estén disponibles
                const onclickAttr = this.getAttribute('onclick');
                if (onclickAttr && onclickAttr.includes('(')) {
                    // Evaluar la función onclick para asegurar que funcione
                    try {
                        // No ejecutar aquí, solo validar que la función existe
                        const functionName = onclickAttr.split('(')[0];
                        if (typeof window[functionName] !== 'function') {
                            console.warn(`Función ${functionName} no encontrada para botón:`, this);
                        }
                    } catch (error) {
                        console.warn('Error validando onclick:', error);
                    }
                }
            });
        });
    }

    showLoading() {
        this.loadingIndicator.style.display = 'block';
    }

    hideLoading() {
        this.loadingIndicator.style.display = 'none';
    }

    showError(message) {
        // Crear notificación de error
        const errorToast = document.createElement('div');
        errorToast.className = 'toast toast-error';
        errorToast.textContent = message;
        errorToast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 20px;
            background: #f44336;
            color: white;
            border-radius: 6px;
            z-index: 10001;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            animation: slideInRight 0.3s ease-out;
        `;
        
        document.body.appendChild(errorToast);
        
        // Remover después de 5 segundos
        setTimeout(() => {
            errorToast.style.animation = 'slideOutRight 0.3s ease-out';
            setTimeout(() => {
                if (errorToast.parentNode) {
                    errorToast.parentNode.removeChild(errorToast);
                }
            }, 300);
        }, 5000);
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    new AjaxNavigation();
});

// Agregar estilos CSS para las animaciones
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .ajax-loading .loading-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
    }
    
    .ajax-loading .loading-spinner {
        width: 32px;
        height: 32px;
        border: 3px solid #444;
        border-top: 3px solid #4CAF50;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .ajax-loading p {
        margin: 0;
        color: #e0e0e0;
        font-size: 14px;
    }
`;
document.head.appendChild(style);
