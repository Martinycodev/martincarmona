<?php 
$title = 'Gestión de Trabajos';
?>
<div class="container">
        <div class="page-header">
            <h1>🛠️ Gestión de Trabajos</h1>
            <div class="header-actions">
                <button class="btn btn-primary" onclick="openCreateModal()">➕ Nuevo Trabajo</button>
                <a href="<?= $this->url('/datos') ?>" class="btn btn-secondary">← Volver</a>
            </div>
        </div>

        <!-- Sección de Crear Nuevo Trabajo -->
        <div class="create-section" id="createSection">
            <div class="card">
                <div class="card-header">
                    <h3>➕ Crear Nuevo Trabajo</h3>
                    <button class="close-btn" onclick="closeCreateSection()">×</button>
                </div>
                <form id="createJobForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombre">Nombre del Trabajo:</label>
                            <input type="text" id="nombre" name="nombre" required>
                        </div>
                        <div class="form-group">
                            <label for="precio_hora">Precio por Hora (€):</label>
                            <input type="number" id="precio_hora" name="precio_hora" step="0.01" min="0" placeholder="0.00" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="descripcion">Descripción:</label>
                            <textarea id="descripcion" name="descripcion" rows="3" placeholder="Descripción opcional del trabajo"></textarea>
                        </div>
                    </div>

                    <div class="form-buttons">
                        <button type="button" class="btn btn-secondary" onclick="closeCreateSection()">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Crear Trabajo</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de Trabajos -->
        <div class="table-container">
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Precio/Hora</th>
                        <th class="actions-column">Acciones</th>
                    </tr>
                </thead>
                <tbody id="trabajosTableBody">
                    <?php foreach ($trabajos as $trabajo): ?>
                    <tr data-id="<?= $trabajo['id'] ?>">
                        <td><?= htmlspecialchars($trabajo['id'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($trabajo['nombre'] ?? '-') ?></td>
                        <td><?= isset($trabajo['precio_hora']) ? '€' . number_format($trabajo['precio_hora'], 2) : '-' ?></td>
                        <td class="actions">
                            <button class="btn-icon btn-edit" onclick="editJob(<?= $trabajo['id'] ?>, this)" title="Editar">
                                ✏️
                            </button>
                            <button class="btn-icon btn-delete" onclick="deleteJob(<?= $trabajo['id'] ?>, '<?= htmlspecialchars($trabajo['nombre']) ?>')" title="Eliminar">
                                🗑️
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Modal de Edición -->
        <div id="editModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>✏️ Editar Trabajo</h3>
                    <span class="close" onclick="closeEditModal()">&times;</span>
                </div>
                <form id="editJobForm">
                    <input type="hidden" id="editId" name="id">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="editNombre">Nombre del Trabajo:</label>
                            <input type="text" id="editNombre" name="nombre" required>
                        </div>
                        <div class="form-group">
                            <label for="editPrecioHora">Precio por Hora (€):</label>
                            <input type="number" id="editPrecioHora" name="precio_hora" step="0.01" min="0" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="editDescripcion">Descripción:</label>
                            <textarea id="editDescripcion" name="descripcion" rows="3"></textarea>
                        </div>
                    </div>

                    <div class="modal-buttons">
                        <button type="button" class="btn-modal btn-secondary" onclick="closeEditModal()">Cancelar</button>
                        <button type="submit" class="btn-modal btn-primary">Guardar Cambios</button>
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
            document.getElementById('nombre').focus();
        }

        function closeCreateSection() {
            document.getElementById('createSection').style.display = 'none';
            createSectionVisible = false;
            document.getElementById('createJobForm').reset();
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
            document.getElementById('editJobForm').reset();
            
            // Limpiar posicionamiento dinámico
            if (modalContent) {
                modalContent.classList.remove('dynamic-position');
                modalContent.style.top = '';
            }
        }

        // Función para editar trabajo
        async function editJob(id, buttonElement = null) {
            try {
                // Mostrar indicador de carga
                showToast('Cargando datos del trabajo...', 'info');
                
                const response = await fetch(`<?= $this->url("/trabajos/obtener") ?>?id=${id}`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                console.log('Response status:', response.status);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                console.log('Data received:', data);
                
                if (data.success && data.trabajo) {
                    const job = data.trabajo;
                    document.getElementById('editId').value = job.id;
                    document.getElementById('editNombre').value = job.nombre || '';
                    document.getElementById('editPrecioHora').value = job.precio_hora || '';
                    document.getElementById('editDescripcion').value = job.descripcion || '';

                    openEditModal(buttonElement);
                    showToast('Datos cargados correctamente', 'success');
                } else {
                    console.error('Error en respuesta:', data);
                    showToast('Error: ' + (data.message || 'No se pudieron cargar los datos'), 'error');
                }
            } catch (error) {
                console.error('Error completo:', error);
                showToast('Error de conexión: ' + error.message, 'error');
            }
        }

        // Función para eliminar trabajo
        async function deleteJob(id, nombre) {
            if (confirm(`¿Estás seguro de que quieres eliminar el trabajo "${nombre}"?`)) {
                try {
                    const response = await fetch(`<?= $this->url("/trabajos/eliminar") ?>`, {
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
                        showToast('Trabajo eliminado correctamente', 'success');
                    } else {
                        showToast('Error al eliminar el trabajo: ' + data.message, 'error');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showToast('Error al eliminar el trabajo', 'error');
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

        // Manejo del formulario de crear
        document.getElementById('createJobForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const jobData = Object.fromEntries(formData);
            
            try {
                const response = await fetch('<?= $this->url("/trabajos/crear") ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(jobData)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    closeCreateSection();
                    location.reload(); // Recargar para mostrar el nuevo trabajo
                    showToast('Trabajo creado correctamente', 'success');
                } else {
                    showToast('Error al crear el trabajo: ' + data.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Error al crear el trabajo', 'error');
            }
        });

        // Manejo del formulario de editar
        document.getElementById('editJobForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const jobData = Object.fromEntries(formData);
            
            try {
                const response = await fetch('<?= $this->url("/trabajos/actualizar") ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(jobData)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    closeEditModal();
                    location.reload(); // Recargar para mostrar los cambios
                    showToast('Trabajo actualizado correctamente', 'success');
                } else {
                    showToast('Error al actualizar el trabajo: ' + data.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Error al actualizar el trabajo', 'error');
            }
        });

        // Cerrar modales al hacer clic fuera
        window.addEventListener('click', function(e) {
            const editModal = document.getElementById('editModal');
            if (e.target === editModal) {
                closeEditModal();
            }
        });
    </script>

</body>
</html>
