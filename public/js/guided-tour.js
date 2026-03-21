/**
 * GuidedTour — Sistema de visita guiada vanilla JS
 * Resalta elementos del DOM uno a uno con un tooltip explicativo.
 * Compatible con navegacion AJAX y tema oscuro.
 */
class GuidedTour {
    constructor() {
        this.tours = {};          // Configuraciones registradas por ID
        this.currentTourId = null;
        this.currentStep = 0;
        this.isActive = false;
        this.overlay = null;
        this.tooltip = null;
        this.highlightedEl = null;
        this._originalStyles = {};
        this._onResize = this._handleResize.bind(this);
        this._onKeydown = this._handleKeydown.bind(this);
        this._createDOM();
    }

    /**
     * Crea los elementos del overlay y tooltip en el DOM (una sola vez)
     */
    _createDOM() {
        // Overlay: captura clicks fuera del elemento destacado
        this.overlay = document.createElement('div');
        this.overlay.className = 'tour-overlay';
        this.overlay.addEventListener('click', () => this.skip());

        // Tooltip
        this.tooltip = document.createElement('div');
        this.tooltip.className = 'tour-tooltip';
        this.tooltip.innerHTML =
            '<div class="tour-tooltip-header">' +
                '<span class="tour-tooltip-title"></span>' +
                '<button class="tour-tooltip-close" title="Cerrar">&times;</button>' +
            '</div>' +
            '<div class="tour-tooltip-body"></div>' +
            '<div class="tour-tooltip-footer">' +
                '<span class="tour-tooltip-counter"></span>' +
                '<div class="tour-tooltip-nav">' +
                    '<button class="tour-btn-skip">Saltar tour</button>' +
                    '<button class="tour-btn-prev">Anterior</button>' +
                    '<button class="tour-btn-next">Siguiente</button>' +
                '</div>' +
            '</div>';

        // Event listeners de los botones
        this.tooltip.querySelector('.tour-tooltip-close').addEventListener('click', () => this.skip());
        this.tooltip.querySelector('.tour-btn-next').addEventListener('click', () => this.next());
        this.tooltip.querySelector('.tour-btn-prev').addEventListener('click', () => this.prev());
        this.tooltip.querySelector('.tour-btn-skip').addEventListener('click', () => this.skip());
    }

    /**
     * Registra una configuracion de tour (idempotente)
     * @param {Object} config - { id: string, steps: Array }
     */
    register(config) {
        this.tours[config.id] = config;
    }

    /**
     * Inicia un tour por su ID
     * @param {string} tourId
     * @param {boolean} force - true para ignorar localStorage
     */
    start(tourId, force) {
        var tour = this.tours[tourId];
        if (!tour || !tour.steps || tour.steps.length === 0) return;

        // No repetir si ya fue completado (salvo force)
        if (!force && this.isCompleted(tourId)) return;

        // No iniciar si ya hay un tour activo
        if (this.isActive) return;

        this.currentTourId = tourId;
        this.currentStep = 0;
        this.isActive = true;

        // Insertar overlay y tooltip en el body
        document.body.appendChild(this.overlay);
        document.body.appendChild(this.tooltip);
        document.body.classList.add('tour-active');

        // Listeners globales
        window.addEventListener('resize', this._onResize);
        window.addEventListener('keydown', this._onKeydown, true);

        this._showStep(0);
    }

    /**
     * Muestra el paso N del tour activo
     */
    _showStep(index) {
        var tour = this.tours[this.currentTourId];
        if (!tour) return;

        // Limpiar highlight anterior
        this._removeHighlight();

        // Saltar pasos cuyo elemento no existe
        var step = tour.steps[index];
        var el = document.querySelector(step.selector);

        if (!el) {
            // Intentar saltar al siguiente paso
            if (index < tour.steps.length - 1) {
                this.currentStep = index + 1;
                this._showStep(this.currentStep);
            } else {
                this._complete();
            }
            return;
        }

        this.currentStep = index;
        this.highlightedEl = el;

        // Scroll suave al elemento
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });

        // Aplicar highlight con un pequeno delay para que el scroll termine
        var self = this;
        setTimeout(function() {
            // Guardar estilos originales
            self._originalStyles = {
                position: el.style.position,
                zIndex: el.style.zIndex,
                boxShadow: el.style.boxShadow,
                borderRadius: el.style.borderRadius
            };

            // Aplicar spotlight
            el.classList.add('tour-highlight');

            // Actualizar contenido del tooltip
            self.tooltip.querySelector('.tour-tooltip-title').textContent = step.title;
            self.tooltip.querySelector('.tour-tooltip-body').innerHTML = step.description;
            self.tooltip.querySelector('.tour-tooltip-counter').textContent =
                (index + 1) + ' / ' + tour.steps.length;

            // Botones: mostrar/ocultar segun paso
            var btnPrev = self.tooltip.querySelector('.tour-btn-prev');
            var btnNext = self.tooltip.querySelector('.tour-btn-next');
            var btnSkip = self.tooltip.querySelector('.tour-btn-skip');

            btnPrev.style.display = index === 0 ? 'none' : '';
            btnSkip.style.display = index === tour.steps.length - 1 ? 'none' : '';
            btnNext.textContent = index === tour.steps.length - 1 ? 'Finalizar' : 'Siguiente';

            // Posicionar tooltip
            self._positionTooltip(el, step.position || 'bottom');

            // Mostrar tooltip con animacion
            self.tooltip.classList.add('tour-tooltip-visible');
        }, 400);
    }

    /**
     * Posiciona el tooltip relativo al elemento destacado
     */
    _positionTooltip(el, preferredPos) {
        var rect = el.getBoundingClientRect();
        var tooltip = this.tooltip;

        // Resetear posicion para medir
        tooltip.style.top = '';
        tooltip.style.left = '';
        tooltip.style.right = '';
        tooltip.removeAttribute('data-position');

        // Medir tooltip
        var tooltipRect = tooltip.getBoundingClientRect();
        var gap = 16; // Espacio entre elemento y tooltip
        var viewW = window.innerWidth;
        var viewH = window.innerHeight;

        // En movil, forzar bottom
        if (viewW <= 768) preferredPos = 'bottom';

        // Calcular posicion
        var pos = preferredPos;
        var top, left;

        if (pos === 'bottom') {
            top = rect.bottom + gap + window.scrollY;
            left = rect.left + (rect.width / 2) - (tooltipRect.width / 2);
        } else if (pos === 'top') {
            top = rect.top - tooltipRect.height - gap + window.scrollY;
            left = rect.left + (rect.width / 2) - (tooltipRect.width / 2);
        } else if (pos === 'right') {
            top = rect.top + (rect.height / 2) - (tooltipRect.height / 2) + window.scrollY;
            left = rect.right + gap;
        } else if (pos === 'left') {
            top = rect.top + (rect.height / 2) - (tooltipRect.height / 2) + window.scrollY;
            left = rect.left - tooltipRect.width - gap;
        }

        // Verificar overflow y ajustar si es necesario
        if (pos === 'bottom' && (rect.bottom + gap + tooltipRect.height > viewH)) {
            pos = 'top';
            top = rect.top - tooltipRect.height - gap + window.scrollY;
            left = rect.left + (rect.width / 2) - (tooltipRect.width / 2);
        } else if (pos === 'top' && rect.top - gap - tooltipRect.height < 0) {
            pos = 'bottom';
            top = rect.bottom + gap + window.scrollY;
            left = rect.left + (rect.width / 2) - (tooltipRect.width / 2);
        }

        // Ajustar horizontalmente para no salir de la pantalla
        if (left < 10) left = 10;
        if (left + tooltipRect.width > viewW - 10) left = viewW - tooltipRect.width - 10;

        tooltip.style.top = top + 'px';
        tooltip.style.left = left + 'px';
        tooltip.setAttribute('data-position', pos);
    }

    /**
     * Siguiente paso
     */
    next() {
        var tour = this.tours[this.currentTourId];
        if (!tour) return;

        if (this.currentStep < tour.steps.length - 1) {
            this.tooltip.classList.remove('tour-tooltip-visible');
            this._showStep(this.currentStep + 1);
        } else {
            this._complete();
        }
    }

    /**
     * Paso anterior
     */
    prev() {
        if (this.currentStep > 0) {
            this.tooltip.classList.remove('tour-tooltip-visible');
            this._showStep(this.currentStep - 1);
        }
    }

    /**
     * Saltar/cerrar el tour
     */
    skip() {
        this._complete();
    }

    /**
     * Completa el tour y guarda en localStorage
     */
    _complete() {
        if (this.currentTourId) {
            localStorage.setItem('guidedTour_' + this.currentTourId + '_completed', '1');
        }
        this._cleanup();
    }

    /**
     * Limpia el DOM y restaura el estado original
     */
    _cleanup() {
        this._removeHighlight();
        this.tooltip.classList.remove('tour-tooltip-visible');

        if (this.overlay.parentNode) this.overlay.parentNode.removeChild(this.overlay);
        if (this.tooltip.parentNode) this.tooltip.parentNode.removeChild(this.tooltip);

        document.body.classList.remove('tour-active');
        this.isActive = false;
        this.currentTourId = null;
        this.currentStep = 0;

        window.removeEventListener('resize', this._onResize);
        window.removeEventListener('keydown', this._onKeydown, true);
    }

    /**
     * Quita el highlight del elemento actual
     */
    _removeHighlight() {
        if (this.highlightedEl) {
            this.highlightedEl.classList.remove('tour-highlight');
            this.highlightedEl = null;
        }
    }

    /**
     * Comprueba si un tour fue completado
     */
    isCompleted(tourId) {
        return localStorage.getItem('guidedTour_' + tourId + '_completed') === '1';
    }

    /**
     * Resetea el estado de completado (para debug o relanzar)
     */
    reset(tourId) {
        localStorage.removeItem('guidedTour_' + tourId + '_completed');
    }

    /**
     * Handler de resize: reposiciona el tooltip
     */
    _handleResize() {
        if (!this.isActive || !this.highlightedEl) return;
        var tour = this.tours[this.currentTourId];
        if (!tour) return;
        var step = tour.steps[this.currentStep];
        this._positionTooltip(this.highlightedEl, step.position || 'bottom');
    }

    /**
     * Handler de teclado: Escape cierra el tour
     */
    _handleKeydown(e) {
        if (!this.isActive) return;
        if (e.key === 'Escape') {
            e.stopPropagation();
            e.preventDefault();
            this.skip();
        }
    }
}

// Crear instancia global (singleton)
(function() {
    if (!window.guidedTour) {
        window.guidedTour = new GuidedTour();
    }
})();
