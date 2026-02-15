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
                    <div class="list-items-container">
    `;

    if (tarea.trabajadores && Array.isArray(tarea.trabajadores) && tarea.trabajadores.length > 0) {
        tarea.trabajadores.forEach(trabajador => {
            html += `
                <div class="list-item-card">
                    <span class="item-name">${trabajador.nombre}</span>
                    ${trabajador.horas_asignadas ? `<span class="item-meta badge-small">${formatHoras(trabajador.horas_asignadas)}</span>` : ''}
                </div>
            `;
        });
    } else {
        html += '<div class="empty-state">No asignados</div>';
    }

    html += `
                    </div>
                </div>

                <!-- Parcelas -->
                <div class="detail-column">
                    <h4>🌾 Parcelas</h4>
                    <div class="list-items-container">
    `;

    if (tarea.parcelas && Array.isArray(tarea.parcelas) && tarea.parcelas.length > 0) {
        tarea.parcelas.forEach(parcela => {
            html += `
                <div class="list-item-card">
                    <span class="item-name">${parcela.nombre}</span>
                    ${parcela.ubicacion ? `<span class="item-meta location-text">📍 ${parcela.ubicacion}</span>` : ''}
                </div>
            `;
        });
    } else {
        html += '<div class="empty-state">No asignadas</div>';
    }

    html += `
                    </div>
                </div>
            </div>

            <div class="detail-grid-row">
                 <!-- Trabajos (Tipo de trabajo) -->
                <div class="detail-column">
                    <h4>🔧 Tipo de Trabajo</h4>
                    <div class="list-items-container">
    `;

    // Compatibilidad: A veces viene como array 'trabajos', a veces como propiedad simple 'trabajo_nombre'
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

    html += `
                    </div>
                </div>
            </div>

            <!-- Imágenes -->
            <div class="detail-section images-section">
                <h4>📸 Imágenes</h4>
                <div id="detailImagenes" class="detail-images-gallery">
    `;

    // Si ya vienen las imágenes (ej. desde vista detalle de lista), renderizarlas
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
        // Si no está la propiedad imagenes (ej. dashboard antes de cargar), dejar vacío o mostrar loader si se va a cargar externamente
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
