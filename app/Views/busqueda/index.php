<?php 
$title = 'Búsqueda Avanzada de Tareas';
?>
<div class="container">
        <div class="search-page">
            <!-- Header con navegación -->
            <div class="search-header">
                <div class="search-header-left">
                    <h1>🔍 Búsqueda Avanzada de Tareas</h1>
                    <p class="search-subtitle">Centro de control para analizar y filtrar todas tus tareas</p>
                </div>
                <div class="search-header-right">
                    <a href="<?= $this->url('/tareas') ?>" class="btn btn-secondary">📝 Vista Normal</a>
                    <a href="<?= $this->url('/dashboard') ?>" class="btn btn-primary">📊 Dashboard</a>
                </div>
            </div>

            <!-- Panel de filtros -->
            <div class="search-filters-panel">
                <div class="filters-header">
                    <h3>🎛️ Filtros de Búsqueda</h3>
                    <div class="filters-actions">
                        <button class="btn-filter btn-clear" onclick="limpiarFiltros()">🗑️ Limpiar</button>
                        <button class="btn-filter btn-toggle" onclick="toggleFiltros()" id="toggleBtn">▼ Ocultar</button>
                    </div>
                </div>
                
                <div class="filters-content" id="filtersContent">
                    <form id="searchForm">
                        <!-- Fila 1: Búsqueda de texto y fechas -->
                        <div class="filter-row">
                            <div class="filter-group">
                                <label for="texto">🔍 Buscar en descripción:</label>
                                <input type="text" id="texto" name="texto" placeholder="Escribe palabras clave...">
                            </div>
                            <div class="filter-group">
                                <label for="fecha_desde">📅 Desde:</label>
                                <input type="date" id="fecha_desde" name="fecha_desde">
                            </div>
                            <div class="filter-group">
                                <label for="fecha_hasta">📅 Hasta:</label>
                                <input type="date" id="fecha_hasta" name="fecha_hasta">
                            </div>
                        </div>

                        <!-- Fila 2: Trabajadores, Parcelas, Trabajos -->
                        <div class="filter-row">
                            <div class="filter-group">
                                <label for="trabajador">👷‍♂️ Trabajador:</label>
                                <div class="autocomplete-wrapper">
                                    <input type="text" id="trabajador" name="trabajador_nombre" placeholder="Buscar trabajador..." autocomplete="off">
                                    <input type="hidden" id="trabajador_id" name="trabajador_id">
                                    <div id="trabajadorResults" class="autocomplete-results" style="display: none;"></div>
                                </div>
                            </div>
                            <div class="filter-group">
                                <label for="parcela">🌾 Parcela:</label>
                                <div class="autocomplete-wrapper">
                                    <input type="text" id="parcela" name="parcela_nombre" placeholder="Buscar parcela..." autocomplete="off">
                                    <input type="hidden" id="parcela_id" name="parcela_id">
                                    <div id="parcelaResults" class="autocomplete-results" style="display: none;"></div>
                                </div>
                            </div>
                            <div class="filter-group">
                                <label for="trabajo">🔧 Tipo de trabajo:</label>
                                <div class="autocomplete-wrapper">
                                    <input type="text" id="trabajo" name="trabajo_nombre" placeholder="Buscar trabajo..." autocomplete="off">
                                    <input type="hidden" id="trabajo_id" name="trabajo_id">
                                    <div id="trabajoResults" class="autocomplete-results" style="display: none;"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Fila 3: Horas y ordenamiento -->
                        <div class="filter-row">
                            <div class="filter-group">
                                <label for="horas_min">⏱️ Horas mínimas:</label>
                                <input type="number" id="horas_min" name="horas_min" step="0.5" min="0" placeholder="0">
                            </div>
                            <div class="filter-group">
                                <label for="horas_max">⏱️ Horas máximas:</label>
                                <input type="number" id="horas_max" name="horas_max" step="0.5" min="0" placeholder="∞">
                            </div>
                            <div class="filter-group">
                                <label for="orden">📊 Ordenar por:</label>
                                <select id="orden" name="orden">
                                    <option value="fecha_desc">📅 Fecha (más reciente)</option>
                                    <option value="fecha_asc">📅 Fecha (más antigua)</option>
                                    <option value="horas_desc">⏱️ Horas (mayor a menor)</option>
                                    <option value="horas_asc">⏱️ Horas (menor a mayor)</option>
                                    <option value="descripcion_asc">📝 Descripción (A-Z)</option>
                                    <option value="descripcion_desc">📝 Descripción (Z-A)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Fila 4: Límite y botón buscar -->
                        <div class="filter-row">
                            <div class="filter-group">
                                <label for="limite">📊 Límite de resultados:</label>
                                <select id="limite" name="limite">
                                    <option value="25">25 resultados</option>
                                    <option value="50" selected>50 resultados</option>
                                    <option value="100">100 resultados</option>
                                    <option value="200">200 resultados</option>
                                </select>
                            </div>
                            <div class="filter-group">
                                <button type="submit" class="btn-search">🔍 Buscar Tareas</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Panel de estadísticas -->
            <div class="stats-panel" id="statsPanel" style="display: none;">
                <div class="stats-header">
                    <h3>📊 Estadísticas de Resultados</h3>
                </div>
                <div class="stats-content" id="statsContent">
                    <!-- Las estadísticas se cargarán aquí -->
                </div>
            </div>

            <!-- Loading state -->
            <div class="search-loading" id="searchLoading" style="display: none;">
                <div class="loading-content">
                    <div class="loading-spinner"></div>
                    <p>🔍 Buscando tareas...</p>
                </div>
            </div>

            <!-- Resultados -->
            <div class="results-panel" id="resultsPanel" style="display: none;">
                <div class="results-header">
                    <h3 id="resultsTitle">📋 Resultados</h3>
                    <div class="results-actions">
                        <button class="btn-export" onclick="exportarResultados()">📄 Exportar</button>
                        <button class="btn-clear-results" onclick="limpiarResultados()">❌ Limpiar</button>
                    </div>
                </div>
                <div class="results-content">
                    <div class="results-table-container">
                        <table class="results-table" id="resultsTable">
                            <thead>
                                <tr>
                                    <th>📅 Fecha</th>
                                    <th>📝 Descripción</th>
                                    <th>👷‍♂️ Trabajadores</th>
                                    <th>🌾 Parcelas</th>
                                    <th>🔧 Trabajos</th>
                                    <th>⏱️ Horas</th>
                                    <th>🔧 Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="resultsTableBody">
                                <!-- Los resultados se cargarán aquí -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Estado vacío -->
            <div class="empty-state" id="emptyState">
                <div class="empty-content">
                    <div class="empty-icon">🔍</div>
                    <h3>Búsqueda Avanzada de Tareas</h3>
                    <p>Utiliza los filtros de arriba para encontrar tareas específicas.</p>
                    <p>Puedes buscar por texto, fechas, trabajadores, parcelas y más.</p>
                    <button class="btn-primary" onclick="document.getElementById('texto').focus()">🚀 Empezar a buscar</button>
                </div>
            </div>

            <!-- Modal de detalles de tarea -->
            <div id="tareaDetailsModal" class="modal">
                <div class="modal-content modal-large">
                    <div class="modal-header">
                        <h3>👁️ Detalles de la Tarea</h3>
                        <span class="close" onclick="closeTareaDetailsModal()">&times;</span>
                    </div>
                    <div class="modal-body">
                        <div id="tareaDetailsContent">
                            <!-- Los detalles se cargarán aquí -->
                        </div>
                    </div>
                    <div class="modal-buttons">
                        <button type="button" class="btn-modal btn-secondary" onclick="closeTareaDetailsModal()">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toast de notificaciones -->
        <div id="toast" class="toast"></div>
    </div>

    <script>
        // Variables globales
        let currentResults = [];
        let currentStats = {};
        let filtrosVisibles = true;

        // Funciones de UI
        function toggleFiltros() {
            const content = document.getElementById('filtersContent');
            const btn = document.getElementById('toggleBtn');
            
            if (filtrosVisibles) {
                content.style.display = 'none';
                btn.textContent = '▶ Mostrar';
                filtrosVisibles = false;
            } else {
                content.style.display = 'block';
                btn.textContent = '▼ Ocultar';
                filtrosVisibles = true;
            }
        }

        function limpiarFiltros() {
            document.getElementById('searchForm').reset();
            document.getElementById('trabajador_id').value = '';
            document.getElementById('parcela_id').value = '';
            document.getElementById('trabajo_id').value = '';
            limpiarResultados();
        }

        function limpiarResultados() {
            document.getElementById('resultsPanel').style.display = 'none';
            document.getElementById('statsPanel').style.display = 'none';
            document.getElementById('emptyState').style.display = 'block';
            currentResults = [];
            currentStats = {};
        }

        function showLoading() {
            document.getElementById('searchLoading').style.display = 'block';
            document.getElementById('resultsPanel').style.display = 'none';
            document.getElementById('statsPanel').style.display = 'none';
            document.getElementById('emptyState').style.display = 'none';
        }

        function hideLoading() {
            document.getElementById('searchLoading').style.display = 'none';
        }

        function showToast(message, type = 'info') {
            const toast = document.getElementById('toast');
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

        // Función principal de búsqueda
        async function buscarTareas(formData) {
            const params = new URLSearchParams();
            
            // Agregar todos los filtros como parámetros GET
            for (const [key, value] of formData.entries()) {
                if (value && value.trim() !== '') {
                    params.append(key, value);
                }
            }
            
            try {
                showLoading();
                
                console.log('Parámetros de búsqueda enviados:', params.toString());
                
                const response = await fetch(`<?= $this->url("/busqueda/buscar") ?>?${params.toString()}`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success) {
                    currentResults = data.tareas;
                    currentStats = data.estadisticas;
                    mostrarResultados(data.tareas, data.estadisticas, data.total);
                    showToast(`Se encontraron ${data.total} tareas`, 'success');
                } else {
                    showToast('Error en la búsqueda: ' + data.message, 'error');
                }
                
            } catch (error) {
                console.error('Error en búsqueda:', error);
                showToast('Error de conexión: ' + error.message, 'error');
            } finally {
                hideLoading();
            }
        }

        // Mostrar resultados en la tabla
        function mostrarResultados(tareas, estadisticas, total) {
            // Ocultar estado vacío
            document.getElementById('emptyState').style.display = 'none';
            
            // Mostrar estadísticas
            mostrarEstadisticas(estadisticas);
            
            // Actualizar título de resultados
            document.getElementById('resultsTitle').textContent = `📋 ${total} Resultado${total !== 1 ? 's' : ''}`;
            
            // Llenar tabla
            const tbody = document.getElementById('resultsTableBody');
            tbody.innerHTML = '';
            
            tareas.forEach(tarea => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${formatDate(tarea.fecha)}</td>
                    <td class="description-cell">${tarea.descripcion || 'Sin descripción'}</td>
                    <td class="workers-cell">
                        ${tarea.trabajadores.map(t => `<span class="worker-tag-small">👷‍♂️ ${t.nombre}</span>`).join('') || '-'}
                    </td>
                    <td class="parcelas-cell">
                        ${tarea.parcelas.map(p => `<span class="parcela-tag-small">🌾 ${p.nombre}</span>`).join('') || '-'}
                    </td>
                    <td class="trabajos-cell">
                        ${tarea.trabajos.map(tr => `<span class="trabajo-tag-small">🔧 ${tr.nombre}</span>`).join('') || '-'}
                    </td>
                    <td class="hours-cell">${tarea.horas ? parseFloat(tarea.horas).toFixed(1) + 'h' : '0h'}</td>
                    <td class="actions-cell">
                        <button class="btn-icon btn-info" onclick="verDetallesTarea(${tarea.id}, this)" title="Ver detalles">👁️</button>
                        <button class="btn-icon btn-edit" onclick="editarTarea(${tarea.id}, this)" title="Editar">✏️</button>
                    </td>
                `;
                tbody.appendChild(row);
            });
            
            // Mostrar panel de resultados
            document.getElementById('resultsPanel').style.display = 'block';
        }

        // Mostrar estadísticas
        function mostrarEstadisticas(stats) {
            const content = document.getElementById('statsContent');
            content.innerHTML = `
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">📋</div>
                        <div class="stat-info">
                            <div class="stat-value">${stats.total_tareas}</div>
                            <div class="stat-label">Tareas encontradas</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">⏱️</div>
                        <div class="stat-info">
                            <div class="stat-value">${stats.total_horas}h</div>
                            <div class="stat-label">Horas totales</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">📊</div>
                        <div class="stat-info">
                            <div class="stat-value">${stats.promedio_horas}h</div>
                            <div class="stat-label">Promedio por tarea</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">📅</div>
                        <div class="stat-info">
                            <div class="stat-value">${stats.fecha_mas_reciente ? formatDate(stats.fecha_mas_reciente) : '-'}</div>
                            <div class="stat-label">Fecha más reciente</div>
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('statsPanel').style.display = 'block';
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

        // Ver detalles de tarea (reutilizar función de vista tareas)
        async function verDetallesTarea(id, buttonElement = null) {
            try {
                showToast('Cargando detalles...', 'info');
                
                const response = await fetch(`<?= $this->url("/tareas/obtener") ?>?id=${id}`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success && data.tarea) {
                    mostrarDetallesTarea(data.tarea);
                } else {
                    showToast('Error: ' + (data.message || 'No se pudieron cargar los detalles'), 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Error de conexión: ' + error.message, 'error');
            }
        }

        function mostrarDetallesTarea(tarea) {
            // Crear HTML de detalles (similar al de vista tareas)
            let detailsHtml = `
                <div class="tarea-details">
                    <div class="detail-section">
                        <h4>📅 Información General</h4>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <strong>Fecha:</strong> ${formatDate(tarea.fecha)}
                            </div>
                            <div class="detail-item">
                                <strong>Horas:</strong> ${tarea.horas ? parseFloat(tarea.horas).toFixed(1) + 'h' : '0h'}
                            </div>
                            <div class="detail-item full-width">
                                <strong>Descripción:</strong><br>
                                ${tarea.descripcion || 'Sin descripción'}
                            </div>
                        </div>
                    </div>
            `;
            
            // Trabajadores
            if (tarea.trabajadores && tarea.trabajadores.length > 0) {
                detailsHtml += `
                    <div class="detail-section">
                        <h4>👷‍♂️ Trabajadores Asignados</h4>
                        <div class="list-items">
                `;
                tarea.trabajadores.forEach(trabajador => {
                    detailsHtml += `
                        <div class="list-item">
                            <span class="item-name">${trabajador.nombre}</span>
                            <span class="item-hours">${trabajador.horas_asignadas ? parseFloat(trabajador.horas_asignadas).toFixed(1) + 'h' : '0h'}</span>
                        </div>
                    `;
                });
                detailsHtml += `</div></div>`;
            }
            
            // Parcelas
            if (tarea.parcelas && tarea.parcelas.length > 0) {
                detailsHtml += `
                    <div class="detail-section">
                        <h4>🌾 Parcelas Trabajadas</h4>
                        <div class="list-items">
                `;
                tarea.parcelas.forEach(parcela => {
                    detailsHtml += `
                        <div class="list-item">
                            <span class="item-name">${parcela.nombre}</span>
                            <span class="item-location">${parcela.ubicacion || ''}</span>
                        </div>
                    `;
                });
                detailsHtml += `</div></div>`;
            }
            
            // Trabajos
            if (tarea.trabajos && tarea.trabajos.length > 0) {
                detailsHtml += `
                    <div class="detail-section">
                        <h4>🔧 Tipos de Trabajo</h4>
                        <div class="list-items">
                `;
                tarea.trabajos.forEach(trabajo => {
                    detailsHtml += `
                        <div class="list-item">
                            <span class="item-name">${trabajo.nombre}</span>
                            <span class="item-hours">${trabajo.horas_trabajo ? parseFloat(trabajo.horas_trabajo).toFixed(1) + 'h' : '0h'}</span>
                        </div>
                    `;
                });
                detailsHtml += `</div></div>`;
            }
            
            detailsHtml += `</div>`;
            
            document.getElementById('tareaDetailsContent').innerHTML = detailsHtml;
            
            const modal = document.getElementById('tareaDetailsModal');
            modal.style.display = 'block';
            
            // Posicionar modal en el viewport actual
            positionModalInViewport(modal);
        }

        function closeTareaDetailsModal() {
            const modal = document.getElementById('tareaDetailsModal');
            const modalContent = modal.querySelector('.modal-content');
            
            modal.style.display = 'none';
            
            // Limpiar posicionamiento dinámico
            if (modalContent) {
                modalContent.classList.remove('dynamic-position');
                modalContent.style.top = '';
            }
        }

        function editarTarea(id) {
            // Redirigir a la vista de tareas con el ID para editar
            window.location.href = `<?= $this->url("/tareas") ?>?edit=${id}`;
        }

        function exportarResultados() {
            if (currentResults.length === 0) {
                showToast('No hay resultados para exportar', 'warning');
                return;
            }
            
            // Crear CSV simple
            let csv = 'Fecha,Descripción,Trabajadores,Parcelas,Trabajos,Horas\n';
            
            currentResults.forEach(tarea => {
                const trabajadores = tarea.trabajadores.map(t => t.nombre).join('; ');
                const parcelas = tarea.parcelas.map(p => p.nombre).join('; ');
                const trabajos = tarea.trabajos.map(tr => tr.nombre).join('; ');
                
                csv += `"${tarea.fecha}","${tarea.descripcion || ''}","${trabajadores}","${parcelas}","${trabajos}","${tarea.horas || 0}"\n`;
            });
            
            // Descargar archivo
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `tareas_${new Date().toISOString().split('T')[0]}.csv`;
            a.click();
            window.URL.revokeObjectURL(url);
            
            showToast('Resultados exportados correctamente', 'success');
        }

        // Funciones de autocompletado
        function setupAutocomplete(inputId, hiddenInputId, resultsId, endpoint) {
            const input = document.getElementById(inputId);
            const hiddenInput = document.getElementById(hiddenInputId);
            const results = document.getElementById(resultsId);
            
            let debounceTimer;
            
            input.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(async () => {
                    const query = this.value.trim();
                    if (query.length < 2) {
                        results.style.display = 'none';
                        hiddenInput.value = '';
                        return;
                    }
                    
                    try {
                        const response = await fetch(endpoint + '?q=' + encodeURIComponent(query));
                        const items = await response.json();
                        
                        if (items.length > 0) {
                            results.innerHTML = items.map(item => 
                                `<div class="autocomplete-item" onclick="selectItem('${inputId}', '${hiddenInputId}', '${resultsId}', ${item.id}, '${item.nombre.replace(/'/g, "\\\\'")}')">
                                    <strong>${item.nombre}</strong>
                                    ${item.dni ? `<small>${item.dni}</small>` : ''}
                                    ${item.olivos ? `<small>${item.olivos} olivos</small>` : ''}
                                </div>`
                            ).join('');
                            results.style.display = 'block';
                        } else {
                            results.style.display = 'none';
                        }
                    } catch (error) {
                        console.error('Error en autocompletado:', error);
                    }
                }, 300);
            });
            
            input.addEventListener('blur', function() {
                setTimeout(() => {
                    results.style.display = 'none';
                }, 200);
            });
        }

        function selectItem(inputId, hiddenInputId, resultsId, id, nombre) {
            document.getElementById(inputId).value = nombre;
            document.getElementById(hiddenInputId).value = id;
            document.getElementById(resultsId).style.display = 'none';
        }

        // Función helper para formatear fechas
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('es-ES');
        }

        // Manejo del formulario de búsqueda
        document.getElementById('searchForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Crear FormData manualmente, priorizando los IDs sobre los nombres
            const formData = new FormData();
            
            // Campos de texto simples
            const texto = document.getElementById('texto').value;
            if (texto) formData.append('texto', texto);
            
            const fecha_desde = document.getElementById('fecha_desde').value;
            if (fecha_desde) formData.append('fecha_desde', fecha_desde);
            
            const fecha_hasta = document.getElementById('fecha_hasta').value;
            if (fecha_hasta) formData.append('fecha_hasta', fecha_hasta);
            
            const horas_min = document.getElementById('horas_min').value;
            if (horas_min) formData.append('horas_min', horas_min);
            
            const horas_max = document.getElementById('horas_max').value;
            if (horas_max) formData.append('horas_max', horas_max);
            
            const orden = document.getElementById('orden').value;
            if (orden) formData.append('orden', orden);
            
            const limite = document.getElementById('limite').value;
            if (limite) formData.append('limite', limite);
            
            // Campos de autocompletado - solo usar IDs si están definidos
            const trabajador_id = document.getElementById('trabajador_id').value;
            if (trabajador_id) formData.append('trabajador_id', trabajador_id);
            
            const parcela_id = document.getElementById('parcela_id').value;
            if (parcela_id) formData.append('parcela_id', parcela_id);
            
            const trabajo_id = document.getElementById('trabajo_id').value;
            if (trabajo_id) formData.append('trabajo_id', trabajo_id);
            
            buscarTareas(formData);
        });

        // Inicialización
        document.addEventListener('DOMContentLoaded', function() {
            // Configurar autocompletados
            setupAutocomplete('trabajador', 'trabajador_id', 'trabajadorResults', '<?= $this->url("/trabajadores/buscar") ?>');
            setupAutocomplete('parcela', 'parcela_id', 'parcelaResults', '<?= $this->url("/parcelas/buscar") ?>');
            setupAutocomplete('trabajo', 'trabajo_id', 'trabajoResults', '<?= $this->url("/trabajos/buscar") ?>');
            
            // Cerrar modales al hacer clic fuera
            window.addEventListener('click', function(e) {
                const modal = document.getElementById('tareaDetailsModal');
                if (e.target === modal) {
                    closeTareaDetailsModal();
                }
            });
        });
    </script>

</body>
</html>
