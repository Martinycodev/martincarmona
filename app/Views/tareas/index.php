<?php 
$title = 'Gestión de Tareas';
include BASE_PATH . '/app/Views/layouts/header.php'; 
?>
        <div class="page-header">
            <h1>📝 Gestión de Tareas</h1>
            <div class="header-actions">
                <button class="btn btn-primary" onclick="openCreateModal()">+</button>
                <a href="<?= $this->url('/busqueda') ?>" class="btn btn-info">🔍 Búsqueda Avanzada</a>
                <a href="<?= $this->url('/datos') ?>" class="btn btn-secondary">← Volver</a>
            </div>
        </div>

        <!-- Sección de Crear Nueva Tarea Completa -->
        <div class="create-section" id="createSection">
            <div class="card">
                <div class="card-header">
                    <h3>➕ Crear Nueva Tarea</h3>
                    <button class="close-btn" onclick="closeCreateSection()">×</button>
                </div>
                <form id="createTareaForm">
                    <input type="hidden" id="createUserId" name="user_id" value="<?= $_SESSION['user_id'] ?? 0 ?>">
                    
                    <div class="form-group">
                        <label for="fecha">Fecha:</label>
                        <input type="date" id="fecha" name="fecha" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcion">Descripción:</label>
                        <textarea id="descripcion" name="descripcion" rows="3" required></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="createParcela">Parcelas:</label>
                            <div class="multi-select-wrapper">
                                <!-- Tags de parcelas seleccionadas -->
                                <div class="selected-parcels" id="createSelectedParcels"></div>
                                
                                <!-- Input de búsqueda -->
                                <div class="autocomplete-wrapper">
                                    <input type="text" 
                                           id="createParcela" 
                                           name="parcela_busqueda" 
                                           placeholder="Buscar parcela..." 
                                           autocomplete="off">
                                    <div id="createParcelaResults" class="autocomplete-results" style="display: none;"></div>
                                </div>
                                
                                <!-- Inputs hidden para enviar los IDs -->
                                <div id="createParcelHiddenInputs"></div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="createTrabajador">Trabajadores:</label>
                            <div class="multi-select-wrapper">
                                <!-- Tags de trabajadores seleccionados -->
                                <div class="selected-workers" id="createSelectedWorkers"></div>
                                
                                <!-- Input de búsqueda -->
                                <div class="autocomplete-wrapper">
                                    <input type="text" 
                                           id="createTrabajador" 
                                           name="trabajador_busqueda" 
                                           placeholder="Buscar trabajador..." 
                                           autocomplete="off">
                                    <div id="createTrabajadorResults" class="autocomplete-results" style="display: none;"></div>
                                </div>
                                
                                <!-- Inputs hidden para enviar los IDs -->
                                <div id="createWorkerHiddenInputs"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="createTrabajo">Tipo de Trabajo:</label>
                            <div class="autocomplete-wrapper">
                                <input type="text" id="createTrabajo" name="trabajo_nombre" autocomplete="off">
                                <input type="hidden" id="createTrabajoId" name="trabajo" value="">
                                <div id="createTrabajoResults" class="autocomplete-results" style="display: none;"></div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="horas">Horas:</label>
                            <input type="number" id="horas" name="horas" min="0" step="0.5">
                        </div>
                    </div>
                    
                    <div class="form-buttons">
                        <button type="button" class="btn btn-secondary" onclick="closeCreateSection()">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Crear Tarea</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de Tareas (Vista Simplificada) -->
        <div class="table-container">
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Descripción</th>
                        <th>Horas</th>
                        <th class="actions-column">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tareasTableBody">
                    <?php if (!empty($tareas)): ?>
                        <?php foreach ($tareas as $tarea): ?>
                        <tr data-id="<?= $tarea['id'] ?>">
                            <td><?= htmlspecialchars(date('d/m/Y', strtotime($tarea['fecha']))) ?></td>
                            <td class="description-cell"><?= htmlspecialchars($tarea['descripcion'] ?? 'Sin descripción') ?></td>
                            <td><?= $tarea['horas'] ? number_format($tarea['horas'], 1) . 'h' : '0h' ?></td>
                            <td class="actions">
                                <button class="btn-icon btn-info" onclick="viewTarea(<?= $tarea['id'] ?>, this)" title="Ver más">
                                    👁️
                                </button>
                                <button class="btn-icon btn-edit" onclick="editTarea(<?= $tarea['id'] ?>, this)" title="Editar">
                                    ✏️
                                </button>
                                <button class="btn-icon btn-delete" onclick="deleteTarea(<?= $tarea['id'] ?>, '<?= htmlspecialchars($tarea['descripcion'] ?? 'Sin descripción') ?>')" title="Eliminar">
                                    🗑️
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="no-data">
                                <div class="no-tareas">
                                    <h3>📝 No hay tareas registradas</h3>
                                    <p>Comienza creando tu primera tarea para organizar el trabajo del campo.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <?php if (isset($pagination) && $pagination['totalPages'] > 1): ?>
        <div class="pagination-container">
          
            <div class="pagination-controls">
                <!-- Botón Primera Página -->
                <?php if ($pagination['hasPrev']): ?>
                    <a href="<?= $this->url('/tareas') ?>?page=1" class="btn-pagination btn-first" title="Primera página">
                        ⏮️
                    </a>
                <?php else: ?>
                    <span class="btn-pagination btn-first disabled">⏮️</span>
                <?php endif; ?>
                
                <!-- Botón Anterior -->
                <?php if ($pagination['hasPrev']): ?>
                    <a href="<?= $this->url('/tareas') ?>?page=<?= $pagination['currentPage'] - 1 ?>" class="btn-pagination btn-prev" title="Página anterior">
                        ◀️
                    </a>
                <?php else: ?>
                    <span class="btn-pagination btn-prev disabled">◀️</span>
                <?php endif; ?>
                
                <!-- Páginas numeradas -->
                <div class="pagination-numbers">
                    <?php
                    $start = max(1, $pagination['currentPage'] - 2);
                    $end = min($pagination['totalPages'], $pagination['currentPage'] + 2);
                    
                    // Mostrar siempre al menos 5 páginas si es posible
                    if ($end - $start < 4) {
                        if ($start == 1) {
                            $end = min($pagination['totalPages'], $start + 4);
                        } else {
                            $start = max(1, $end - 4);
                        }
                    }
                    
                    for ($i = $start; $i <= $end; $i++): ?>
                        <?php if ($i == $pagination['currentPage']): ?>
                            <span class="btn-pagination current"><?= $i ?></span>
                        <?php else: ?>
                            <a href="<?= $this->url('/tareas') ?>?page=<?= $i ?>" class="btn-pagination"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
                
                <!-- Botón Siguiente -->
                <?php if ($pagination['hasNext']): ?>
                    <a href="<?= $this->url('/tareas') ?>?page=<?= $pagination['currentPage'] + 1 ?>" class="btn-pagination btn-next" title="Página siguiente">
                        ▶️
                    </a>
                <?php else: ?>
                    <span class="btn-pagination btn-next disabled">▶️</span>
                <?php endif; ?>
                
                <!-- Botón Última Página -->
                <?php if ($pagination['hasNext']): ?>
                    <a href="<?= $this->url('/tareas') ?>?page=<?= $pagination['totalPages'] ?>" class="btn-pagination btn-last" title="Última página">
                        ⏭️
                    </a>
                <?php else: ?>
                    <span class="btn-pagination btn-last disabled">⏭️</span>
                <?php endif; ?>
            </div>

            <div class="pagination-info">
                <span>Mostrando página <?= $pagination['currentPage'] ?> de <?= $pagination['totalPages'] ?></span>
                <span>(<?= $pagination['totalItems'] ?> tareas en total)</span>
            </div>
            
        </div>
        <?php endif; ?>

        <!-- Modal de Ver Detalles -->
        <div id="viewModal" class="modal">
            <div class="modal-content modal-large">
                <div class="modal-header">
                    <h3>👁️ Detalles de la Tarea</h3>
                    <span class="close" onclick="closeViewModal()">&times;</span>
                </div>
                <div class="modal-body">
                    <div id="tareaDetails">
                        <!-- Los detalles se cargarán aquí dinámicamente -->
                    </div>
                </div>
                <div class="modal-buttons">
                    <button type="button" class="btn-modal btn-secondary" onclick="closeViewModal()">Cerrar</button>
                </div>
            </div>
        </div>

        <!-- Modal de Edición Completo -->
        <div id="editModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>✏️ Editar Tarea</h3>
                    <span class="close" onclick="closeEditModal()">&times;</span>
                </div>
                <form id="editTareaForm">
                    <input type="hidden" id="editId" name="id">
                    <input type="hidden" id="editUserId" name="user_id">
                    
                    <div class="form-group">
                        <label for="editFecha">Fecha:</label>
                        <input type="date" id="editFecha" name="fecha" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="editDescripcion">Descripción:</label>
                        <textarea id="editDescripcion" name="descripcion" rows="3" required></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="editParcela">Parcelas:</label>
                            <div class="multi-select-wrapper">
                                <!-- Tags de parcelas seleccionadas -->
                                <div class="selected-parcels" id="editSelectedParcels"></div>
                                
                                <!-- Input de búsqueda -->
                                <div class="autocomplete-wrapper">
                                    <input type="text" 
                                           id="editParcela" 
                                           name="parcela_busqueda" 
                                           placeholder="Buscar parcela..." 
                                           autocomplete="off">
                                    <div id="editParcelaResults" class="autocomplete-results" style="display: none;"></div>
                                </div>
                                
                                <!-- Inputs hidden para enviar los IDs -->
                                <div id="editParcelHiddenInputs"></div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="editTrabajador">Trabajadores:</label>
                            <div class="multi-select-wrapper">
                                <!-- Tags de trabajadores seleccionados -->
                                <div class="selected-workers" id="editSelectedWorkers"></div>
                                
                                <!-- Input de búsqueda -->
                                <div class="autocomplete-wrapper">
                                    <input type="text" 
                                           id="editTrabajador" 
                                           name="trabajador_busqueda" 
                                           placeholder="Buscar trabajador..." 
                                           autocomplete="off">
                                    <div id="editTrabajadorResults" class="autocomplete-results" style="display: none;"></div>
                                </div>
                                
                                <!-- Inputs hidden para enviar los IDs -->
                                <div id="editWorkerHiddenInputs"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="editTrabajo">Tipo de Trabajo:</label>
                            <div class="autocomplete-wrapper">
                                <input type="text" id="editTrabajo" name="trabajo_nombre" autocomplete="off">
                                <input type="hidden" id="editTrabajoId" name="trabajo" value="">
                                <div id="editTrabajoResults" class="autocomplete-results" style="display: none;"></div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="editHoras">Horas:</label>
                            <input type="number" id="editHoras" name="horas" min="0" step="0.5">
                        </div>
                    </div>
                    
                    <div class="modal-buttons">
                        <button type="button" class="btn-modal btn-danger" onclick="deleteTareaFromEdit()">🗑️ Eliminar</button>
                        <button type="button" class="btn-modal btn-secondary" onclick="closeEditModal()">❌ Cancelar</button>
                        <button type="submit" class="btn-modal btn-primary">💾 Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Toast de notificaciones -->
        <div id="toast" class="toast"></div>
    </div>

    <script>
        // Variables globales
        let createSectionVisible = false;

        // Funciones para manejar la sección de crear
        function openCreateModal() {
            document.getElementById('createSection').style.display = 'block';
            createSectionVisible = true;
            document.getElementById('fecha').focus();
        }

        function closeCreateSection() {
            document.getElementById('createSection').style.display = 'none';
            createSectionVisible = false;
            document.getElementById('createTareaForm').reset();
            
            // Limpiar campos relacionados
            document.getElementById('createTrabajoId').value = '';
            createWorkerSelector.clearAll();
            createParcelaSelector.clearAll();
        }

        // Funciones para el modal de ver detalles
        function openViewModal(buttonElement = null) {
            const modal = document.getElementById('viewModal');
            modal.style.display = 'block';
            
            // Posicionar modal en el viewport actual
            positionModalInViewport(modal);
        }

        function closeViewModal() {
            const modal = document.getElementById('viewModal');
            const modalContent = modal.querySelector('.modal-content');
            
            modal.style.display = 'none';
            
            // Limpiar posicionamiento dinámico
            if (modalContent) {
                modalContent.classList.remove('dynamic-position');
                modalContent.style.top = '';
            }
        }

        // Funciones para el modal de edición
        function openEditModal(buttonElement = null) {
            const modal = document.getElementById('editModal');
            modal.style.display = 'block';
            
            // Posicionar modal en el viewport actual
            positionModalInViewport(modal);
        }

        function closeEditModal() {
            const modal = document.getElementById('editModal');
            const modalContent = modal.querySelector('.modal-content');
            
            modal.style.display = 'none';
            document.getElementById('editTareaForm').reset();
            
            // Limpiar posicionamiento dinámico
            if (modalContent) {
                modalContent.classList.remove('dynamic-position');
                modalContent.style.top = '';
            }
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

        // Función para ver detalles de tarea
        async function viewTarea(id, buttonElement = null) {
            try {
                showToast('Cargando detalles de la tarea...', 'info');
                
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
                    const tarea = data.tarea;
                    
                    // Construir HTML de detalles
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
                    
                    // Fechas de creación/actualización
                    detailsHtml += `
                        <div class="detail-section">
                            <h4>📊 Información de Sistema</h4>
                            <div class="detail-grid">
                                <div class="detail-item">
                                    <strong>Creada:</strong> ${formatDate(tarea.created_at)}
                                </div>
                                <div class="detail-item">
                                    <strong>Actualizada:</strong> ${formatDate(tarea.updated_at)}
                                </div>
                            </div>
                        </div>
                    `;
                    
                    detailsHtml += `</div>`;
                    
                    document.getElementById('tareaDetails').innerHTML = detailsHtml;
                    openViewModal(buttonElement);
                    showToast('Detalles cargados correctamente', 'success');
                } else {
                    showToast('Error: ' + (data.message || 'No se pudieron cargar los detalles'), 'error');
                }
            } catch (error) {
                console.error('Error completo:', error);
                showToast('Error de conexión: ' + error.message, 'error');
            }
        }

        // Función para editar tarea (con datos completos)
        async function editTarea(id, buttonElement = null) {
            try {
                showToast('Cargando datos de la tarea...', 'info');
                
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
                    const tarea = data.tarea;
                    
                    // Llenar campos básicos
                    document.getElementById('editId').value = tarea.id;
                    document.getElementById('editUserId').value = <?= $_SESSION['user_id'] ?? 0 ?>;
                    document.getElementById('editFecha').value = tarea.fecha;
                    document.getElementById('editDescripcion').value = tarea.descripcion || '';
                    document.getElementById('editHoras').value = tarea.horas || '';
                    
                    // Limpiar campos relacionados
                    document.getElementById('editTrabajo').value = '';
                    document.getElementById('editTrabajoId').value = '';
                    
                    // Cargar parcelas múltiples
                    if (tarea.parcelas && tarea.parcelas.length > 0) {
                        editParcelaSelector.preloadParcels(tarea.parcelas);
                    } else {
                        editParcelaSelector.clearAll();
                    }
                    
                    // Cargar trabajo si existe
                    if (tarea.trabajos && tarea.trabajos.length > 0) {
                        const trabajo = tarea.trabajos[0]; // Primer trabajo
                        document.getElementById('editTrabajo').value = trabajo.nombre;
                        document.getElementById('editTrabajoId').value = trabajo.id;
                    }
                    
                    // Cargar trabajadores con multi-select
                    if (tarea.trabajadores && Array.isArray(tarea.trabajadores)) {
                        editWorkerSelector.preloadWorkers(tarea.trabajadores);
                    }
                    
                    openEditModal(buttonElement);
                    showToast('Datos cargados correctamente', 'success');
                } else {
                    showToast('Error: ' + (data.message || 'No se pudieron cargar los datos'), 'error');
                }
            } catch (error) {
                console.error('Error completo:', error);
                showToast('Error de conexión: ' + error.message, 'error');
            }
        }

        // Función para eliminar tarea
        async function deleteTarea(id, descripcion) {
            if (confirm(`¿Estás seguro de que quieres eliminar la tarea "${descripcion}"?`)) {
                try {
                    const response = await fetch(`<?= $this->url("/tareas/eliminar") ?>`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ id: id })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        document.querySelector(`tr[data-id="${id}"]`).remove();
                        showToast('Tarea eliminada correctamente', 'success');
                    } else {
                        showToast('Error al eliminar la tarea: ' + data.message, 'error');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showToast('Error al eliminar la tarea', 'error');
                }
            }
        }

        // Función para mostrar notificaciones
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

        // Función helper para formatear fechas
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('es-ES');
        }

        // Manejo del formulario de crear (con datos completos)
        document.getElementById('createTareaForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            // Construir objeto con todos los datos necesarios
            const tareaData = {
                user_id: parseInt(formData.get('user_id')),
                fecha: formData.get('fecha'),
                descripcion: formData.get('descripcion'),
                parcelas: createParcelaSelector.getSelectedParcelaIds(),
                trabajadores: createWorkerSelector.getSelectedWorkerIds(),
                trabajo: parseInt(document.getElementById('createTrabajoId').value) || 0,
                horas: parseFloat(formData.get('horas')) || 0
            };
            
            console.log('Datos a enviar (crear):', tareaData);
            
            try {
                showToast('Creando tarea...', 'info');
                
                const response = await fetch('<?= $this->url("/tareas/crear") ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(tareaData)
                });
                
                console.log('Response status (crear):', response.status);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                console.log('Response data (crear):', data);
                
                if (data.success) {
                    closeCreateSection();
                    location.reload(); // Recargar para mostrar la nueva tarea
                    showToast('Tarea creada correctamente', 'success');
                } else {
                    showToast('Error al crear: ' + (data.message || 'Error desconocido'), 'error');
                }
            } catch (error) {
                console.error('Error completo (crear):', error);
                showToast('Error de conexión: ' + error.message, 'error');
            }
        });

        // Función para eliminar desde el modal de edición
        function deleteTareaFromEdit() {
            const id = document.getElementById('editId').value;
            const descripcion = document.getElementById('editDescripcion').value || 'Sin descripción';
            
            if (confirm(`¿Estás seguro de que quieres eliminar la tarea "${descripcion}"?`)) {
                deleteTarea(id, descripcion).then(() => {
                    closeEditModal();
                });
            }
        }

        // Manejo del formulario de editar (con datos completos)
        document.getElementById('editTareaForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            // Construir objeto con todos los datos necesarios
            const tareaData = {
                id: parseInt(formData.get('id')),
                user_id: parseInt(formData.get('user_id')),
                fecha: formData.get('fecha'),
                descripcion: formData.get('descripcion'),
                parcelas: editParcelaSelector.getSelectedParcelaIds(),
                trabajadores: editWorkerSelector.getSelectedWorkerIds(),
                trabajo: parseInt(document.getElementById('editTrabajoId').value) || 0,
                horas: parseFloat(formData.get('horas')) || 0
            };
            
            console.log('Datos a enviar (editar):', tareaData);
            
            try {
                showToast('Actualizando tarea...', 'info');
                
                const response = await fetch('<?= $this->url("/tareas/actualizar") ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(tareaData)
                });
                
                console.log('Response status (actualizar):', response.status);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                console.log('Response data (actualizar):', data);
                
                if (data.success) {
                    closeEditModal();
                    location.reload(); // Recargar para mostrar los cambios
                    showToast('Tarea actualizada correctamente', 'success');
                } else {
                    showToast('Error al actualizar: ' + (data.message || 'Error desconocido'), 'error');
                }
            } catch (error) {
                console.error('Error completo (actualizar):', error);
                showToast('Error de conexión: ' + error.message, 'error');
            }
        });

        // Cerrar modales al hacer clic fuera
        window.addEventListener('click', function(e) {
            const viewModal = document.getElementById('viewModal');
            const editModal = document.getElementById('editModal');
            
            if (e.target === viewModal) {
                closeViewModal();
            }
            if (e.target === editModal) {
                closeEditModal();
            }
            
            // Cerrar resultados de parcelas múltiples
            const createParcelaWrapper = document.querySelector('#createParcela').closest('.multi-select-wrapper');
            const editParcelaWrapper = document.querySelector('#editParcela').closest('.multi-select-wrapper');
            
            if (createParcelaWrapper && !createParcelaWrapper.contains(e.target)) {
                createParcelaSelector.hideResults();
            }
            if (editParcelaWrapper && !editParcelaWrapper.contains(e.target)) {
                editParcelaSelector.hideResults();
            }
        });

        // ====== SISTEMA DE SELECCIÓN MÚTLTIPLE DE TRABAJADORES ======
        class MultiWorkerSelector {
            constructor(inputId, resultsId, selectedWorkersId, hiddenInputsId) {
                this.input = document.getElementById(inputId);
                this.results = document.getElementById(resultsId);
                this.selectedWorkersContainer = document.getElementById(selectedWorkersId);
                this.hiddenInputsContainer = document.getElementById(hiddenInputsId);
                this.selectedWorkers = new Map(); // Map para mantener orden y evitar duplicados
                this.debounceTimer = null;
                
                this.initializeEvents();
            }
            
            initializeEvents() {
                // Evento de búsqueda con debounce
                this.input.addEventListener('input', () => {
                    clearTimeout(this.debounceTimer);
                    this.debounceTimer = setTimeout(() => {
                        this.search();
                    }, 300);
                });
                
                // Ocultar resultados al perder foco
                this.input.addEventListener('blur', () => {
                    setTimeout(() => this.hideResults(), 200);
                });
                
                // Mostrar resultados al enfocar (si hay texto)
                this.input.addEventListener('focus', () => {
                    if (this.input.value.trim().length >= 2) {
                        this.search();
                    }
                });
            }
            
            async search() {
                const query = this.input.value.trim();
                if (query.length < 2) {
                    this.hideResults();
                    return;
                }
                
                try {
                    const response = await fetch(`<?= $this->url("/trabajadores/buscar") ?>?q=${encodeURIComponent(query)}`);
                    const workers = await response.json();
                    this.showResults(workers);
                } catch (error) {
                    console.error('Error buscando trabajadores:', error);
                }
            }
            
            showResults(workers) {
                if (!workers || workers.length === 0) {
                    this.hideResults();
                    return;
                }
                
                // Filtrar trabajadores ya seleccionados
                const availableWorkers = workers.filter(worker => 
                    !this.selectedWorkers.has(worker.id.toString())
                );
                
                if (availableWorkers.length === 0) {
                    this.results.innerHTML = '<div class="no-results">👥 Todos los trabajadores ya están seleccionados</div>';
                } else {
                    const selectorName = this.getSelectorName();
                    this.results.innerHTML = availableWorkers.map(worker => 
                        `<div class="autocomplete-item" onclick="${selectorName}.selectWorker(${worker.id}, '${worker.nombre.replace(/'/g, "\\'")}')">\n                            <strong>${worker.nombre}</strong>\n                            <small>${worker.dni || 'Sin DNI'}</small>\n                        </div>`
                    ).join('');
                }
                
                this.results.style.display = 'block';
            }
            
            selectWorker(id, nombre) {
                // Añadir a seleccionados
                this.selectedWorkers.set(id.toString(), { id, nombre });
                
                // Limpiar input
                this.input.value = '';
                this.hideResults();
                
                // Actualizar UI
                this.updateSelectedWorkersUI();
                this.updateHiddenInputs();
            }
            
            removeWorker(id) {
                this.selectedWorkers.delete(id.toString());
                this.updateSelectedWorkersUI();
                this.updateHiddenInputs();
            }
            
            updateSelectedWorkersUI() {
                if (this.selectedWorkers.size === 0) {
                    this.selectedWorkersContainer.innerHTML = '<div class="placeholder">👥 Selecciona trabajadores...</div>';
                    return;
                }
                
                const selectorName = this.getSelectorName();
                const workersHTML = Array.from(this.selectedWorkers.values()).map(worker => 
                    `<div class="worker-tag">\n                        <span>👷‍♂️ ${worker.nombre}</span>\n                        <button type="button" class="remove-worker" onclick="${selectorName}.removeWorker('${worker.id}')">&times;</button>\n                    </div>`
                ).join('');
                
                this.selectedWorkersContainer.innerHTML = workersHTML;
            }
            
            updateHiddenInputs() {
                this.hiddenInputsContainer.innerHTML = '';
                Array.from(this.selectedWorkers.keys()).forEach((workerId, index) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = `trabajadores[${index}]`;
                    input.value = workerId;
                    this.hiddenInputsContainer.appendChild(input);
                });
            }
            
            hideResults() {
                this.results.style.display = 'none';
            }
            
            clearAll() {
                this.selectedWorkers.clear();
                this.input.value = '';
                this.hideResults();
                this.updateSelectedWorkersUI();
                this.updateHiddenInputs();
            }
            
            preloadWorkers(workers) {
                this.clearAll();
                workers.forEach(worker => {
                    this.selectedWorkers.set(worker.id.toString(), {
                        id: worker.id,
                        nombre: worker.nombre
                    });
                });
                this.updateSelectedWorkersUI();
                this.updateHiddenInputs();
            }
            
            getSelectedWorkerIds() {
                return Array.from(this.selectedWorkers.keys()).map(id => parseInt(id));
            }
            
            getSelectorName() {
                // Determinar qué instancia es basado en el ID del input
                if (this.input.id === 'createTrabajador') {
                    return 'createWorkerSelector';
                } else if (this.input.id === 'editTrabajador') {
                    return 'editWorkerSelector';
                }
                return 'workerSelector';
            }
        }

        // Instanciar selectores de trabajadores
        const createWorkerSelector = new MultiWorkerSelector(
            'createTrabajador',
            'createTrabajadorResults',
            'createSelectedWorkers',
            'createWorkerHiddenInputs'
        );
        
        const editWorkerSelector = new MultiWorkerSelector(
            'editTrabajador',
            'editTrabajadorResults',
            'editSelectedWorkers', 
            'editWorkerHiddenInputs'
        );

        // ====== SISTEMA DE SELECCIÓN MÚLTIPLE DE PARCELAS ======
        class MultiParcelaSelector {
            constructor(inputId, resultsId, selectedParcelsId, hiddenInputsId) {
                this.input = document.getElementById(inputId);
                this.results = document.getElementById(resultsId);
                this.selectedParcelsContainer = document.getElementById(selectedParcelsId);
                this.hiddenInputsContainer = document.getElementById(hiddenInputsId);
                this.selectedParcels = new Map();
                this.selectedIndex = -1;
                
                this.init();
            }
            
            init() {
                this.setupEventListeners();
            }
            
            setupEventListeners() {
                this.input.addEventListener('input', (e) => {
                    this.handleSearch(e.target.value.trim());
                });
                
                this.input.addEventListener('keydown', (e) => {
                    this.handleKeydown(e);
                });
                
                this.input.addEventListener('focus', () => {
                    this.input.value = '';
                });
            }
            
            async handleSearch(query) {
                if (query.length >= 3) {
                    try {
                        const response = await fetch(`<?= $this->url("/parcelas/buscar") ?>?q=${encodeURIComponent(query)}`, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        const parcels = await response.json();
                        this.displayResults(parcels);
                    } catch (error) {
                        console.error('Error en búsqueda de parcelas:', error);
                    }
                } else {
                    this.hideResults();
                }
            }
            
            displayResults(parcels) {
                if (parcels.length === 0) {
                    this.results.innerHTML = '<div class="autocomplete-item no-results">No hay coincidencias</div>';
                } else {
                    this.results.innerHTML = parcels
                        .map((parcela, index) => {
                            const isSelected = this.selectedParcels.has(parcela.id);
                            const extraClass = isSelected ? 'selected-parcel' : '';
                            
                            return `
                                <div class="autocomplete-item ${extraClass}" 
                                     data-id="${parcela.id}" 
                                     data-name="${parcela.nombre}"
                                     data-olivos="${parcela.olivos || 0}"
                                     data-index="${index}"
                                     onclick="${isSelected ? '' : `this.multiSelector.selectParcela(${parcela.id}, '${parcela.nombre.replace(/'/g, "\\'")}', ${parcela.olivos || 0})`}">
                                    <strong>${parcela.nombre}</strong>
                                    <br><small>${parcela.olivos || 0} olivos</small>
                                </div>
                            `;
                        }).join('');
                        
                    this.results.querySelectorAll('.autocomplete-item').forEach(item => {
                        item.multiSelector = this;
                    });
                }
                this.showResults();
                this.selectedIndex = -1;
            }
            
            selectParcela(id, name, olivos) {
                if (this.selectedParcels.has(id)) return;
                
                this.selectedParcels.set(id, { id, name, olivos });
                this.createParcelaTag(id, name, olivos);
                this.createHiddenInput(id);
                this.input.value = '';
                this.hideResults();
                this.updatePlaceholder();
                
                console.log('Parcela seleccionada:', { id, name, olivos });
            }
            
            createParcelaTag(id, name, olivos) {
                const tag = document.createElement('div');
                tag.className = 'parcel-tag';
                tag.dataset.parcelId = id;
                tag.innerHTML = `
                    <span>${name} (${olivos} olivos)</span>
                    <button type="button" class="remove-parcel" onclick="this.closest('.parcel-tag').multiSelector.removeParcela(${id})">×</button>
                `;
                
                tag.multiSelector = this;
                this.selectedParcelsContainer.appendChild(tag);
            }
            
            createHiddenInput(id) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'parcelas[]';
                input.value = id;
                input.dataset.parcelId = id;
                
                this.hiddenInputsContainer.appendChild(input);
            }
            
            removeParcela(id) {
                this.selectedParcels.delete(id);
                
                const tag = this.selectedParcelsContainer.querySelector(`[data-parcel-id="${id}"]`);
                if (tag) {
                    tag.style.animation = 'slideOut 0.3s ease-in forwards';
                    setTimeout(() => tag.remove(), 300);
                }
                
                const hiddenInput = this.hiddenInputsContainer.querySelector(`[data-parcel-id="${id}"]`);
                if (hiddenInput) hiddenInput.remove();
                
                this.updatePlaceholder();
            }
            
            updatePlaceholder() {
                const count = this.selectedParcels.size;
                if (count === 0) {
                    this.input.placeholder = 'Buscar parcela...';
                } else {
                    this.input.placeholder = `${count} parcela${count > 1 ? 's' : ''} seleccionada${count > 1 ? 's' : ''}. Buscar más...`;
                }
            }
            
            handleKeydown(e) {
                const items = this.results.querySelectorAll('.autocomplete-item:not(.selected-parcel)');
                if (items.length === 0) return;
                
                switch (e.key) {
                    case 'ArrowDown':
                        e.preventDefault();
                        this.selectedIndex = (this.selectedIndex + 1) % items.length;
                        this.highlightItem(items);
                        break;
                    case 'ArrowUp':
                        e.preventDefault();
                        this.selectedIndex = this.selectedIndex <= 0 ? items.length - 1 : this.selectedIndex - 1;
                        this.highlightItem(items);
                        break;
                    case 'Enter':
                        e.preventDefault();
                        if (this.selectedIndex >= 0 && items[this.selectedIndex]) {
                            const item = items[this.selectedIndex];
                            const id = item.dataset.id;
                            const name = item.dataset.name;
                            const olivos = item.dataset.olivos;
                            this.selectParcela(id, name, olivos);
                        }
                        break;
                    case 'Escape':
                        this.hideResults();
                        this.input.blur();
                        break;
                }
            }
            
            highlightItem(items) {
                items.forEach(item => item.classList.remove('selected'));
                if (items[this.selectedIndex]) {
                    items[this.selectedIndex].classList.add('selected');
                }
            }
            
            showResults() {
                this.results.style.display = 'block';
            }
            
            hideResults() {
                this.results.style.display = 'none';
                this.selectedIndex = -1;
            }
            
            clearAll() {
                this.selectedParcels.clear();
                this.selectedParcelsContainer.innerHTML = '';
                this.hiddenInputsContainer.innerHTML = '';
                this.updatePlaceholder();
            }
            
            preloadParcels(parcels) {
                this.clearAll();
                parcels.forEach(parcela => {
                    this.selectParcela(parcela.id, parcela.nombre, parcela.olivos || 0);
                });
            }
            
            getSelectedParcelaIds() {
                return Array.from(this.selectedParcels.keys());
            }
        }

        // Instanciar selectores de parcelas
        const createParcelaSelector = new MultiParcelaSelector(
            'createParcela',
            'createParcelaResults',
            'createSelectedParcels',
            'createParcelHiddenInputs'
        );

        const editParcelaSelector = new MultiParcelaSelector(
            'editParcela',
            'editParcelaResults',
            'editSelectedParcels',
            'editParcelHiddenInputs'
        );


        // ====== AUTOCOMPLETADO PARA TRABAJOS ======
        function setupTrabajoAutocomplete(inputId, hiddenInputId, resultsId) {
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
                        return;
                    }
                    
                    try {
                        const response = await fetch(`<?= $this->url("/trabajos/buscar") ?>?q=${encodeURIComponent(query)}`);
                        const trabajos = await response.json();
                        
                        if (trabajos.length > 0) {
                            results.innerHTML = trabajos.map(trabajo => 
                                `<div class="autocomplete-item" onclick="selectTrabajo('${inputId}', '${hiddenInputId}', '${resultsId}', ${trabajo.id}, '${trabajo.nombre.replace(/'/g, "\\'")}')">\n                                    <strong>${trabajo.nombre}</strong>\n                                </div>`
                            ).join('');
                            results.style.display = 'block';
                        } else {
                            results.style.display = 'none';
                        }
                    } catch (error) {
                        console.error('Error buscando trabajos:', error);
                    }
                }, 300);
            });
            
            input.addEventListener('blur', function() {
                setTimeout(() => {
                    results.style.display = 'none';
                }, 200);
            });
        }
        
        function selectTrabajo(inputId, hiddenInputId, resultsId, id, nombre) {
            document.getElementById(inputId).value = nombre;
            document.getElementById(hiddenInputId).value = id;
            document.getElementById(resultsId).style.display = 'none';
        }

        // Establecer fecha de hoy por defecto e inicializar autocompletados
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('fecha').value = today;
            
            // Inicializar autocompletados para trabajos
            setupTrabajoAutocomplete('createTrabajo', 'createTrabajoId', 'createTrabajoResults');
            setupTrabajoAutocomplete('editTrabajo', 'editTrabajoId', 'editTrabajoResults');
        });
    </script>

</body>
</html>