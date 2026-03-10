/**
 * Componente compartido para renderizar detalles de tareas
 * Se usa en Dashboard (Calendario) y Lista de Tareas
 */

/**
 * Genera el HTML para los detalles de una tarea
 * @param {Object} tarea Objeto con los datos de la tarea
 * @returns {string} HTML renderizado
 */
function renderTaskDetailHtml(tarea) {
    if (!tarea) return '<div class="alert alert-danger">No se encontraron datos de la tarea</div>';

    // Helper para formatear fecha (si ya viene formateada la usa, si no la formatea)
    const formatDate = (dateStr) => {
        if (!dateStr) return 'Fecha no válida';
        // Si ya parece una fecha formateada (dd/mm/yyyy), devolverla tal cual
        if (dateStr.includes('/')) return dateStr;
        const date = new Date(dateStr);
        return date.toLocaleDateString('es-ES', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    };

    // Helper para formatear horas
    const formatHoras = (horas) => {
        return horas ? parseFloat(horas).toFixed(1) + 'h' : '0h';
    };

    let html = `
        <div class="tarea-details-container">
            <!-- Encabezado con Fecha y Horas Totales -->
            <div class="detail-header-section">
                <div class="detail-date">
                    <span class="icon">📅</span>
                    <strong class="editable"
                            data-id="${tarea.id}"
                            data-field="fecha"
                            contenteditable="true"
                            title="Haz clic para editar fecha">${formatDate(tarea.fecha)}</strong>
                </div>
                <div class="detail-hours badge-hours">
                    <span class="icon">⏱️</span>
                    <span class="editable"
                          data-id="${tarea.id}"
                          data-field="horas"
                          contenteditable="true"
                          title="Haz clic para editar horas">${formatHoras(tarea.horas)}</span>
                </div>
            </div>

            <!-- Descripción -->
            <div class="detail-section description-section">
                <h4>📝 Descripción</h4>
                <div class="description-content editable"
                     data-id="${tarea.id}"
                     data-field="descripcion"
                     contenteditable="true"
                     title="Haz clic para editar descripción">
                    ${tarea.descripcion ? tarea.descripcion.replace(/\n/g, '<br>') : '<em class="text-muted">Sin descripción</em>'}
                </div>
            </div>

            <div class="detail-grid-row">
                <!-- Trabajadores -->
                <div class="detail-column">
                    <h4>👷‍♂️ Trabajadores</h4>
                    <div class="list-items-container" id="trabajadores-container-${tarea.id}">
    `;

    // Cards de trabajadores con botón × para quitar
    if (tarea.trabajadores && Array.isArray(tarea.trabajadores) && tarea.trabajadores.length > 0) {
        tarea.trabajadores.forEach(trabajador => {
            html += `
                <div class="list-item-card" id="trabajador-card-${tarea.id}-${trabajador.id}">
                    <span class="item-name">${trabajador.nombre}</span>
                    ${trabajador.horas_asignadas ? `<span class="item-meta badge-small">${formatHoras(trabajador.horas_asignadas)}</span>` : ''}
                    <button class="btn-quitar-item" title="Quitar trabajador"
                            onclick="quitarTrabajador(${tarea.id}, ${trabajador.id})">×</button>
                </div>
            `;
        });
    } else {
        html += `<div class="empty-state" id="empty-trabajadores-${tarea.id}">No asignados</div>`;
    }

    // Selector para añadir trabajador
    html += `
                    </div>
                    <div class="inline-add-row">
                        <div class="combobox-wrap" id="cbWrap-trab-${tarea.id}">
                            <input class="combobox-input inline-select" placeholder="+ Añadir trabajador..." autocomplete="off" spellcheck="false">
                            <input type="hidden" class="combobox-val" value="">
                            <ul class="combobox-list"></ul>
                        </div>
                        <button class="btn-inline-add" onclick="añadirTrabajador(${tarea.id})">✓</button>
                    </div>
                </div>

                <!-- Parcelas -->
                <div class="detail-column">
                    <h4>🌾 Parcelas</h4>
                    <div class="list-items-container" id="parcelas-container-${tarea.id}">
    `;

    // Cards de parcelas con botón × para quitar
    if (tarea.parcelas && Array.isArray(tarea.parcelas) && tarea.parcelas.length > 0) {
        tarea.parcelas.forEach(parcela => {
            html += `
                <div class="list-item-card" id="parcela-card-${tarea.id}-${parcela.id}">
                    <span class="item-name">${parcela.nombre}</span>
                    ${parcela.ubicacion ? `<span class="item-meta location-text">📍 ${parcela.ubicacion}</span>` : ''}
                    <button class="btn-quitar-item" title="Quitar parcela"
                            onclick="quitarParcela(${tarea.id}, ${parcela.id})">×</button>
                </div>
            `;
        });
    } else {
        html += `<div class="empty-state" id="empty-parcelas-${tarea.id}">No asignadas</div>`;
    }

    // Selector para añadir parcela
    html += `
                    </div>
                    <div class="inline-add-row">
                        <div class="combobox-wrap" id="cbWrap-parc-${tarea.id}">
                            <input class="combobox-input inline-select" placeholder="+ Añadir parcela..." autocomplete="off" spellcheck="false">
                            <input type="hidden" class="combobox-val" value="">
                            <ul class="combobox-list"></ul>
                        </div>
                        <button class="btn-inline-add" onclick="añadirParcela(${tarea.id})">✓</button>
                    </div>
                </div>
            </div>

            <div class="detail-grid-row">
                <!-- Trabajos (Tipo de trabajo) -->
                <div class="detail-column">
                    <h4>🔧 Tipo de Trabajo</h4>
                    <div class="list-items-container" id="trabajos-container-${tarea.id}">
    `;

    // Cards de trabajos (solo se cambia con el select, no se quitan individualmente)
    if (tarea.trabajos && Array.isArray(tarea.trabajos) && tarea.trabajos.length > 0) {
        tarea.trabajos.forEach(trabajo => {
            html += `
                <div class="list-item-card">
                    <span class="item-name">${trabajo.nombre}</span>
                    ${trabajo.horas_trabajo ? `<span class="item-meta badge-small">${formatHoras(trabajo.horas_trabajo)}</span>` : ''}
                </div>
            `;
        });
    } else if (tarea.trabajo_nombre) {
        html += `
                <div class="list-item-card">
                    <span class="item-name">${tarea.trabajo_nombre}</span>
                </div>
            `;
    } else {
        html += '<div class="empty-state">No especificado</div>';
    }

    // Selector para cambiar tipo de trabajo + botón crear nuevo
    html += `
                    </div>
                    <div class="inline-add-row">
                        <div class="combobox-wrap" id="cbWrap-work-${tarea.id}">
                            <input class="combobox-input inline-select" placeholder="Cambiar tipo de trabajo..." autocomplete="off" spellcheck="false">
                            <input type="hidden" class="combobox-val" value="">
                            <ul class="combobox-list"></ul>
                        </div>
                        <button class="btn-inline-add" title="Crear nuevo trabajo"
                                onclick="toggleFormNuevoTrabajo(${tarea.id})">+</button>
                    </div>
                    <div class="form-nuevo-trabajo" id="formNuevoTrabajo_${tarea.id}"
                         style="display:none; margin-top:8px; gap:6px; flex-wrap:wrap;">
                        <input type="text" class="inline-select" id="nuevoTrabajoNombre_${tarea.id}"
                               placeholder="Nombre del trabajo" style="flex:1; min-width:120px;">
                        <input type="number" class="inline-select" id="nuevoTrabajoPrecio_${tarea.id}"
                               placeholder="€/h" style="width:66px;" min="0" step="0.01">
                        <button class="btn-inline-add" title="Guardar nuevo trabajo"
                                onclick="crearTrabajoInline(${tarea.id})">✓</button>
                    </div>
                </div>
            </div>

            <!-- Imágenes -->
            <div class="detail-section images-section">
                <h4>📸 Imágenes</h4>
                <div id="detailImagenes" class="detail-images-gallery">
    `;

    // Si ya vienen las imágenes, renderizarlas
    if (tarea.imagenes && Array.isArray(tarea.imagenes) && tarea.imagenes.length > 0) {
        tarea.imagenes.forEach(img => {
            html += `
                <div class="image-item">
                    <a href="${img.file_path}" target="_blank">
                        <img src="${img.file_path}" alt="${img.original_filename || 'Imagen tarea'}" loading="lazy">
                    </a>
                </div>
            `;
        });
    } else if (tarea.imagenes && Array.isArray(tarea.imagenes) && tarea.imagenes.length === 0) {
        html += '<div class="empty-state text-center">No hay imágenes adjuntas</div>';
    } else {
        html += '<!-- Las imágenes se pueden cargar dinámicamente aquí -->';
    }

    html += `
                </div>
            </div>

            <!-- Meta info (Solo si existe) -->
    `;

    if (tarea.created_at || tarea.updated_at) {
        html += `
            <div class="detail-meta-footer">
                ${tarea.created_at ? `<span>Creada: ${formatDate(tarea.created_at)}</span>` : ''}
                ${tarea.updated_at ? `<span>Actualizada: ${formatDate(tarea.updated_at)}</span>` : ''}
            </div>
        `;
    }

    html += `</div>`; // Cierre de tarea-details-container
    return html;
}

// Exportar globalmente
window.renderTaskDetailHtml = renderTaskDetailHtml;


// =====================================================================
// FUNCIONES DE EDICIÓN INLINE: trabajadores, parcelas y trabajos
// Se llaman desde los botones × y selects generados en renderTaskDetailHtml
// =====================================================================

/**
 * Carga las opciones disponibles en los selects cuando se abre el detalle de una tarea.
 * Hace una petición al servidor para obtener todos los trabajadores, parcelas y trabajos.
 * @param {number} tareaId - ID de la tarea abierta
 */
async function cargarOpcionesModal(tareaId) {
    try {
        const res = await fetch(buildUrl('/tareas/opcionesModal'), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        if (!res.ok) return;
        const data = await res.json();

        if (data.trabajadores) initCombobox(`cbWrap-trab-${tareaId}`, data.trabajadores);
        if (data.parcelas)     initCombobox(`cbWrap-parc-${tareaId}`, data.parcelas);
        if (data.trabajos)     initCombobox(`cbWrap-work-${tareaId}`,  data.trabajos,
            (opt) => cambiarTrabajo(tareaId, opt.id, opt.nombre));
    } catch (err) {
        console.error('Error cargando opciones del modal:', err);
    }
}

/**
 * Añade un trabajador a la tarea actual desde el selector
 * @param {number} tareaId
 */
async function añadirTrabajador(tareaId) {
    const { id: trabajadorId, text: trabajadorNombre } = _getComboboxSel(`cbWrap-trab-${tareaId}`);
    if (!trabajadorId) {
        showToast('Selecciona un trabajador primero', 'warning');
        return;
    }

    try {
        const res = await fetch(buildUrl('/tareas/agregarTrabajador'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ tarea_id: tareaId, trabajador_id: trabajadorId })
        });

        const data = await res.json();

        if (data.success) {
            // Quitar el mensaje de "No asignados" si existe
            const emptyState = document.getElementById(`empty-trabajadores-${tareaId}`);
            if (emptyState) emptyState.remove();

            // Añadir la nueva card al contenedor sin recargar
            const container = document.getElementById(`trabajadores-container-${tareaId}`);
            if (container) {
                const card = document.createElement('div');
                card.className = 'list-item-card';
                card.id = `trabajador-card-${tareaId}-${trabajadorId}`;
                card.innerHTML = `
                    <span class="item-name">${trabajadorNombre}</span>
                    <button class="btn-quitar-item" title="Quitar trabajador"
                            onclick="quitarTrabajador(${tareaId}, ${trabajadorId})">×</button>
                `;
                container.appendChild(card);
            }

            // Resetear el combobox
            _resetCombobox(`cbWrap-trab-${tareaId}`);
            window.needsReload = true;
        } else {
            showToast(data.message || 'Error al añadir trabajador', 'error');
        }
    } catch (err) {
        console.error('Error añadiendo trabajador:', err);
        showToast('Error de conexión', 'error');
    }
}

/**
 * Quita un trabajador de la tarea y elimina su card del DOM
 * @param {number} tareaId
 * @param {number} trabajadorId
 * @param {HTMLElement} btn - Botón × que se pulsó
 */
async function quitarTrabajador(tareaId, trabajadorId) {
    try {
        const res = await fetch(buildUrl('/tareas/quitarTrabajador'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ tarea_id: tareaId, trabajador_id: trabajadorId })
        });

        const data = await res.json();

        if (data.success) {
            // Eliminar la card del DOM directamente
            const card = document.getElementById(`trabajador-card-${tareaId}-${trabajadorId}`);
            if (card) card.remove();

            // Si ya no hay trabajadores, mostrar empty state
            const container = document.getElementById(`trabajadores-container-${tareaId}`);
            if (container && container.querySelectorAll('.list-item-card').length === 0) {
                container.innerHTML = `<div class="empty-state" id="empty-trabajadores-${tareaId}">No asignados</div>`;
            }

            window.needsReload = true;
        } else {
            showToast(data.message || 'Error al quitar trabajador', 'error');
        }
    } catch (err) {
        console.error('Error quitando trabajador:', err);
        showToast('Error de conexión', 'error');
    }
}

/**
 * Añade una parcela a la tarea desde el selector
 * @param {number} tareaId
 */
async function añadirParcela(tareaId) {
    const { id: parcelaId, text: parcelaNombre } = _getComboboxSel(`cbWrap-parc-${tareaId}`);
    if (!parcelaId) {
        showToast('Selecciona una parcela primero', 'warning');
        return;
    }

    try {
        const res = await fetch(buildUrl('/tareas/agregarParcela'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ tarea_id: tareaId, parcela_id: parcelaId })
        });

        const data = await res.json();

        if (data.success) {
            const emptyState = document.getElementById(`empty-parcelas-${tareaId}`);
            if (emptyState) emptyState.remove();

            const container = document.getElementById(`parcelas-container-${tareaId}`);
            if (container) {
                const card = document.createElement('div');
                card.className = 'list-item-card';
                card.id = `parcela-card-${tareaId}-${parcelaId}`;
                card.innerHTML = `
                    <span class="item-name">${parcelaNombre}</span>
                    <button class="btn-quitar-item" title="Quitar parcela"
                            onclick="quitarParcela(${tareaId}, ${parcelaId})">×</button>
                `;
                container.appendChild(card);
            }

            _resetCombobox(`cbWrap-parc-${tareaId}`);
            window.needsReload = true;
        } else {
            showToast(data.message || 'Error al añadir parcela', 'error');
        }
    } catch (err) {
        console.error('Error añadiendo parcela:', err);
        showToast('Error de conexión', 'error');
    }
}

/**
 * Quita una parcela de la tarea y elimina su card del DOM
 * @param {number} tareaId
 * @param {number} parcelaId
 * @param {HTMLElement} btn
 */
async function quitarParcela(tareaId, parcelaId) {
    try {
        const res = await fetch(buildUrl('/tareas/quitarParcela'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ tarea_id: tareaId, parcela_id: parcelaId })
        });

        const data = await res.json();

        if (data.success) {
            const card = document.getElementById(`parcela-card-${tareaId}-${parcelaId}`);
            if (card) card.remove();

            const container = document.getElementById(`parcelas-container-${tareaId}`);
            if (container && container.querySelectorAll('.list-item-card').length === 0) {
                container.innerHTML = `<div class="empty-state" id="empty-parcelas-${tareaId}">No asignadas</div>`;
            }

            window.needsReload = true;
        } else {
            showToast(data.message || 'Error al quitar parcela', 'error');
        }
    } catch (err) {
        console.error('Error quitando parcela:', err);
        showToast('Error de conexión', 'error');
    }
}

/**
 * Cambia el tipo de trabajo de la tarea al instante
 * @param {number} tareaId
 * @param {number} trabajoId
 * @param {string} trabajoNombre
 */
async function cambiarTrabajo(tareaId, trabajoId, trabajoNombre) {
    if (!trabajoId) return;

    try {
        const res = await fetch(buildUrl('/tareas/cambiarTrabajo'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ tarea_id: tareaId, trabajo_id: trabajoId })
        });

        const data = await res.json();

        if (data.success) {
            const container = document.getElementById(`trabajos-container-${tareaId}`);
            if (container) {
                container.innerHTML = `
                    <div class="list-item-card">
                        <span class="item-name">${trabajoNombre}</span>
                    </div>
                `;
            }
            _resetCombobox(`cbWrap-work-${tareaId}`);
            window.needsReload = true;
        } else {
            showToast(data.message || 'Error al cambiar trabajo', 'error');
            _resetCombobox(`cbWrap-work-${tareaId}`);
        }
    } catch (err) {
        console.error('Error cambiando trabajo:', err);
        showToast('Error de conexión', 'error');
        _resetCombobox(`cbWrap-work-${tareaId}`);
    }
}

/**
 * Muestra/oculta el formulario inline para crear un nuevo trabajo
 * @param {number} tareaId
 */
function toggleFormNuevoTrabajo(tareaId) {
    const form = document.getElementById(`formNuevoTrabajo_${tareaId}`);
    if (!form) return;
    const visible = form.style.display !== 'none';
    form.style.display = visible ? 'none' : 'flex';
    if (!visible) {
        document.getElementById(`nuevoTrabajoNombre_${tareaId}`)?.focus();
    }
}

/**
 * Crea un nuevo trabajo desde el sidebar de tarea y lo asigna a la tarea actual
 * @param {number} tareaId
 */
async function crearTrabajoInline(tareaId) {
    const nombreInput  = document.getElementById(`nuevoTrabajoNombre_${tareaId}`);
    const precioInput  = document.getElementById(`nuevoTrabajoPrecio_${tareaId}`);
    const nombre       = nombreInput?.value.trim();
    const precio_hora  = parseFloat(precioInput?.value) || 0;

    if (!nombre) {
        showToast('El nombre del trabajo es requerido', 'warning');
        nombreInput?.focus();
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    try {
        const res = await fetch(buildUrl('/trabajos/crear'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ nombre, precio_hora })
        });

        const data = await res.json();

        if (data.success) {
            // Asignar el nuevo trabajo a la tarea
            await cambiarTrabajo(tareaId, data.id, nombre);
            // Ocultar formulario y limpiar inputs
            toggleFormNuevoTrabajo(tareaId);
            if (nombreInput) nombreInput.value = '';
            if (precioInput) precioInput.value = '';
            showToast('Trabajo "' + nombre + '" creado y asignado', 'success');
        } else {
            showToast(data.message || 'Error al crear el trabajo', 'error');
        }
    } catch (err) {
        console.error('Error creando trabajo inline:', err);
        showToast('Error de conexión', 'error');
    }
}

/**
 * Inicializa un combobox typeahead sobre un wrapper ya en el DOM.
 * @param {string} wrapperId - ID del div.combobox-wrap
 * @param {Array}  options   - Array de {id, nombre}
 * @param {Function} [onSelect] - Callback opcional cuando se selecciona un item. Si se pasa, se ejecuta automáticamente al seleccionar (sin necesidad de pulsar ✓).
 */
function initCombobox(wrapperId, options, onSelect = null) {
    const wrap = document.getElementById(wrapperId);
    if (!wrap) return;

    const input     = wrap.querySelector('.combobox-input');
    const hiddenVal = wrap.querySelector('.combobox-val');
    const list      = wrap.querySelector('.combobox-list');
    if (!input || !hiddenVal || !list) return;

    list.style.display = 'none';

    function renderList(filter) {
        const q = (filter || '').toLowerCase().trim();
        list.innerHTML = '';
        const filtered = q
            ? options.filter(o => o.nombre.toLowerCase().includes(q))
            : options;

        if (!filtered.length) {
            const li = document.createElement('li');
            li.className = 'combobox-no-results';
            li.textContent = 'Sin resultados';
            list.appendChild(li);
            return;
        }

        filtered.forEach(opt => {
            const li = document.createElement('li');
            li.className = 'combobox-item';
            li.dataset.id = opt.id;
            li.dataset.nombre = opt.nombre;

            if (q) {
                const idx = opt.nombre.toLowerCase().indexOf(q);
                li.innerHTML = idx >= 0
                    ? opt.nombre.substring(0, idx)
                      + '<mark>' + opt.nombre.substring(idx, idx + q.length) + '</mark>'
                      + opt.nombre.substring(idx + q.length)
                    : opt.nombre;
            } else {
                li.textContent = opt.nombre;
            }

            li.addEventListener('mousedown', (e) => {
                e.preventDefault();
                selectItem(opt);
            });
            list.appendChild(li);
        });
    }

    function selectItem(opt) {
        input.value     = opt.nombre;
        hiddenVal.value = opt.id;
        closeList();
        if (onSelect) onSelect(opt);
    }

    function positionList() {
        const rect = input.getBoundingClientRect();
        list.style.top   = (rect.bottom + window.scrollY) + 'px';
        list.style.left  = (rect.left + window.scrollX) + 'px';
        list.style.width = rect.width + 'px';
    }

    function openList() {
        renderList(input.value);
        positionList();
        list.style.display = '';
    }

    function closeList() {
        list.style.display = 'none';
    }

    // Remove old listeners by cloning (safe since initCombobox is called once per open)
    input.addEventListener('focus', openList);
    input.addEventListener('input', () => {
        hiddenVal.value = '';
        renderList(input.value);
        positionList();
        list.style.display = '';
    });
    input.addEventListener('blur', () => setTimeout(closeList, 150));

    input.addEventListener('keydown', (e) => {
        const items = list.querySelectorAll('.combobox-item');
        const active = list.querySelector('.combobox-item.cb-active');

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (!active) {
                items[0]?.classList.add('cb-active');
            } else {
                const next = active.nextElementSibling;
                if (next?.classList.contains('combobox-item')) {
                    active.classList.remove('cb-active');
                    next.classList.add('cb-active');
                }
            }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (active) {
                const prev = active.previousElementSibling;
                active.classList.remove('cb-active');
                if (prev?.classList.contains('combobox-item')) prev.classList.add('cb-active');
            }
        } else if (e.key === 'Enter') {
            const act = list.querySelector('.combobox-item.cb-active');
            if (act) {
                e.preventDefault();
                selectItem({ id: act.dataset.id, nombre: act.dataset.nombre });
            }
        } else if (e.key === 'Escape') {
            closeList();
        }
    });
}

/** Devuelve {id, text} del combobox indicado */
function _getComboboxSel(wrapperId) {
    const wrap = document.getElementById(wrapperId);
    if (!wrap) return { id: '', text: '' };
    return {
        id:   parseInt(wrap.querySelector('.combobox-val')?.value || '0') || 0,
        text: wrap.querySelector('.combobox-input')?.value ?? ''
    };
}

/** Limpia el input y el valor oculto del combobox */
function _resetCombobox(wrapperId) {
    const wrap = document.getElementById(wrapperId);
    if (!wrap) return;
    const inp = wrap.querySelector('.combobox-input');
    const val = wrap.querySelector('.combobox-val');
    if (inp) inp.value = '';
    if (val) val.value = '';
}

// Exportar funciones de edición inline globalmente
window.cargarOpcionesModal     = cargarOpcionesModal;
window.añadirTrabajador        = añadirTrabajador;
window.quitarTrabajador        = quitarTrabajador;
window.añadirParcela           = añadirParcela;
window.quitarParcela           = quitarParcela;
window.cambiarTrabajo          = cambiarTrabajo;
window.toggleFormNuevoTrabajo  = toggleFormNuevoTrabajo;
window.crearTrabajoInline      = crearTrabajoInline;
window.initCombobox            = initCombobox;
window._getComboboxSel         = _getComboboxSel;
window._resetCombobox          = _resetCombobox;


// =====================================================================
// GESTIÓN DE IMÁGENES (compartida con task-sidebar.js)
// =====================================================================

async function loadTaskImages(taskId, containerId, allowDelete = false) {
    const container = document.getElementById(containerId);
    if (!container) return;

    container.innerHTML = '<div style="text-align:center; padding:10px; color:#999;">Cargando imágenes...</div>';

    try {
        const response = await fetch(buildUrl('/tareas/obtener') + '?id=' + taskId, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await response.json();

        if (data.success && data.tarea.imagenes && data.tarea.imagenes.length > 0) {
            container.innerHTML = '';
            data.tarea.imagenes.forEach(img => {
                const div = document.createElement('div');
                div.className = 'image-item';

                const imgUrl = buildUrl(img.file_path);
                div.innerHTML = `<img src="${imgUrl}" alt="${img.original_filename}"
                    title="${img.original_filename}" style="cursor:zoom-in;">`;

                div.addEventListener('click', () =>
                    openLightbox(img.id, imgUrl, img.original_filename, taskId, containerId, allowDelete)
                );
                container.appendChild(div);
            });
        } else {
            container.innerHTML = '<div style="grid-column: 1/-1; text-align:center; padding:10px; color:#999;">No hay imágenes adjuntas</div>';
        }
    } catch (error) {
        console.error('Error cargando imágenes:', error);
        container.innerHTML = '<div style="color: #dc3545; padding:10px;">Error al cargar imágenes</div>';
    }
}

async function deleteImage(imageId, taskId, containerId) {
    if (!confirm('¿Estás seguro de eliminar esta imagen?')) return;

    try {
        const response = await fetch(buildUrl('/tareas/eliminarImagen'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ id: imageId })
        });
        const data = await response.json();

        if (data.success) {
            loadTaskImages(taskId, containerId, true);
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error eliminando imagen:', error);
        alert('Error al eliminar la imagen');
    }
}

window.loadTaskImages = loadTaskImages;
window.deleteImage    = deleteImage;


// =====================================================================
// LIGHTBOX
// =====================================================================

function openLightbox(imageId, imgUrl, altText, taskId, containerId, allowDelete) {
    const lb        = document.getElementById('img-lightbox');
    const img       = document.getElementById('img-lightbox-img');
    const deleteBtn = document.getElementById('img-lightbox-delete');

    img.src = imgUrl;
    img.alt = altText;

    if (allowDelete) {
        deleteBtn.style.display = '';
        deleteBtn.onclick = async (e) => {
            e.stopPropagation();
            await deleteImage(imageId, taskId, containerId);
            closeLightbox();
        };
    } else {
        deleteBtn.style.display = 'none';
    }

    lb.classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    const lb = document.getElementById('img-lightbox');
    lb.classList.remove('open');
    document.getElementById('img-lightbox-img').src = '';
    document.body.style.overflow = '';
}

// Cerrar con Escape
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && document.getElementById('img-lightbox')?.classList.contains('open')) {
        closeLightbox();
    }
});

window.openLightbox  = openLightbox;
window.closeLightbox = closeLightbox;
