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
                        <select class="inline-select" id="selectTrabajador_${tarea.id}">
                            <option value="">+ Añadir trabajador...</option>
                        </select>
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
                        <select class="inline-select" id="selectParcela_${tarea.id}">
                            <option value="">+ Añadir parcela...</option>
                        </select>
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

    // Selector para cambiar tipo de trabajo (actualiza al instante al cambiar)
    html += `
                    </div>
                    <div class="inline-add-row">
                        <select class="inline-select" id="selectTrabajo_${tarea.id}"
                                onchange="cambiarTrabajo(${tarea.id}, this)">
                            <option value="">Cambiar tipo de trabajo...</option>
                        </select>
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
        // Un único endpoint devuelve trabajadores, parcelas y trabajos de una vez
        const res = await fetch(buildUrl('/tareas/opcionesModal'), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        if (!res.ok) return;

        const data = await res.json();

        // Rellenar select de trabajadores
        const selectTrabajador = document.getElementById(`selectTrabajador_${tareaId}`);
        if (selectTrabajador && data.trabajadores) {
            data.trabajadores.forEach(t => {
                const opt = document.createElement('option');
                opt.value = t.id;
                opt.textContent = t.nombre;
                selectTrabajador.appendChild(opt);
            });
        }

        // Rellenar select de parcelas
        const selectParcela = document.getElementById(`selectParcela_${tareaId}`);
        if (selectParcela && data.parcelas) {
            data.parcelas.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = p.nombre;
                selectParcela.appendChild(opt);
            });
        }

        // Rellenar select de trabajos
        const selectTrabajo = document.getElementById(`selectTrabajo_${tareaId}`);
        if (selectTrabajo && data.trabajos) {
            data.trabajos.forEach(t => {
                const opt = document.createElement('option');
                opt.value = t.id;
                opt.textContent = t.nombre;
                selectTrabajo.appendChild(opt);
            });
        }

    } catch (err) {
        console.error('Error cargando opciones del modal:', err);
    }
}

/**
 * Añade un trabajador a la tarea actual desde el selector
 * @param {number} tareaId
 */
async function añadirTrabajador(tareaId) {
    const select = document.getElementById(`selectTrabajador_${tareaId}`);
    const trabajadorId = parseInt(select.value);
    const trabajadorNombre = select.options[select.selectedIndex]?.text;

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

            // Resetear el select
            select.value = '';
            showToast('Trabajador añadido', 'success');
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

            showToast('Trabajador quitado', 'success');
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
    const select = document.getElementById(`selectParcela_${tareaId}`);
    const parcelaId = parseInt(select.value);
    const parcelaNombre = select.options[select.selectedIndex]?.text;

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

            select.value = '';
            showToast('Parcela añadida', 'success');
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

            showToast('Parcela quitada', 'success');
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
 * Cambia el tipo de trabajo de la tarea al instante (onchange del select)
 * @param {number} tareaId
 * @param {HTMLSelectElement} select
 */
async function cambiarTrabajo(tareaId, select) {
    const trabajoId = parseInt(select.value);
    const trabajoNombre = select.options[select.selectedIndex]?.text;

    if (!trabajoId) return; // Sin selección, no hacer nada

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
            // Reemplazar el contenido del contenedor con la nueva card
            const container = document.getElementById(`trabajos-container-${tareaId}`);
            if (container) {
                container.innerHTML = `
                    <div class="list-item-card">
                        <span class="item-name">${trabajoNombre}</span>
                    </div>
                `;
            }

            // Resetear el select a la opción por defecto
            select.value = '';
            showToast('Tipo de trabajo actualizado', 'success');
            window.needsReload = true;
        } else {
            showToast(data.message || 'Error al cambiar trabajo', 'error');
            select.value = '';
        }
    } catch (err) {
        console.error('Error cambiando trabajo:', err);
        showToast('Error de conexión', 'error');
        select.value = '';
    }
}

// Exportar funciones de edición inline globalmente
window.cargarOpcionesModal = cargarOpcionesModal;
window.añadirTrabajador    = añadirTrabajador;
window.quitarTrabajador    = quitarTrabajador;
window.añadirParcela       = añadirParcela;
window.quitarParcela       = quitarParcela;
window.cambiarTrabajo      = cambiarTrabajo;
