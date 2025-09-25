<?php 
$title = 'Gestión de Parcelas';
?>
<div class="container">
        <div class="page-header">
            <h1>🌾 Gestión de Parcelas</h1>
            <div class="header-actions">
                <button class="btn btn-primary" onclick="openCreateModal()">➕ Nueva Parcela</button>
                <a href="<?= $this->url('/datos') ?>" class="btn btn-secondary">← Volver</a>
            </div>
        </div>

        <!-- Sección de Crear Nueva Parcela -->
        <div class="create-section" id="createSection">
            <div class="card">
                <div class="card-header">
                    <h3>➕ Crear Nueva Parcela</h3>
                    <button class="close-btn" onclick="closeCreateSection()">×</button>
                </div>
                <form id="createParcelaForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombre">Nombre:</label>
                            <input type="text" id="nombre" name="nombre" required>
                        </div>
                        <div class="form-group">
                            <label for="ubicacion">Ubicación:</label>
                            <input type="text" id="ubicacion" name="ubicacion">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="empresa">Empresa:</label>
                            <input type="text" id="empresa" name="empresa">
                        </div>
                        <div class="form-group">
                            <label for="dueño">Dueño:</label>
                            <input type="text" id="dueño" name="dueño">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="olivos">Número de Olivos:</label>
                            <input type="number" id="olivos" name="olivos" min="0">
                        </div>
                        <div class="form-group">
                            <label for="hidrante">Hidrante:</label>
                            <input type="number" id="hidrante" name="hidrante" min="0">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="descripcion">Descripción:</label>
                            <textarea id="descripcion" name="descripcion" rows="3" placeholder="Descripción opcional"></textarea>
                        </div>
                    </div>
                    <div class="form-buttons">
                        <button type="button" class="btn btn-secondary" onclick="closeCreateSection()">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Crear Parcela</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de Parcelas -->
        <div class="table-container">
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Ubicación</th>
                        <th>Empresa</th>
                        <th>Dueño</th>
                        <th>Olivos</th>
                        <th>Hidrante</th>
                        <th class="actions-column">Acciones</th>
                    </tr>
                </thead>
                <tbody id="parcelasTableBody">
                    <?php foreach ($parcelas as $parcela): ?>
                    <tr data-id="<?= $parcela['id'] ?>">
                        <td><?= htmlspecialchars($parcela['id'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($parcela['nombre'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($parcela['ubicacion'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($parcela['empresa'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($parcela['dueño'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($parcela['olivos'] ?? '0') ?></td>
                        <td><?= htmlspecialchars($parcela['hidrante'] ?? '0') ?></td>
                        <td class="actions">
                            <button class="btn-icon btn-edit" onclick="editParcela(<?= $parcela['id'] ?>, this)" title="Editar">
                                ✏️
                            </button>
                            <button class="btn-icon btn-delete" onclick="deleteParcela(<?= $parcela['id'] ?>, '<?= htmlspecialchars($parcela['nombre']) ?>')" title="Eliminar">
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
                    <h3>✏️ Editar Parcela</h3>
                    <span class="close" onclick="closeEditModal()">&times;</span>
                </div>
                <form id="editParcelaForm">
                    <input type="hidden" id="editId" name="id">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="editNombre">Nombre:</label>
                            <input type="text" id="editNombre" name="nombre" required>
                        </div>
                        <div class="form-group">
                            <label for="editUbicacion">Ubicación:</label>
                            <input type="text" id="editUbicacion" name="ubicacion">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="editEmpresa">Empresa:</label>
                            <input type="text" id="editEmpresa" name="empresa">
                        </div>
                        <div class="form-group">
                            <label for="editDueño">Dueño:</label>
                            <input type="text" id="editDueño" name="dueño">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="editOlivos">Número de Olivos:</label>
                            <input type="number" id="editOlivos" name="olivos" min="0">
                        </div>
                        <div class="form-group">
                            <label for="editHidrante">Hidrante:</label>
                            <input type="number" id="editHidrante" name="hidrante" min="0">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="editDescripcion">Descripción:</label>
                            <textarea id="editDescripcion" name="descripcion" rows="3" placeholder="Descripción opcional"></textarea>
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
            document.getElementById('createParcelaForm').reset();
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
            document.getElementById('editParcelaForm').reset();
            
            // Limpiar posicionamiento dinámico
            if (modalContent) {
                modalContent.classList.remove('dynamic-position');
                modalContent.style.top = '';
            }
        }

        // Función para editar parcela
        async function editParcela(id, buttonElement = null) {
            try {
                // Mostrar indicador de carga
                showToast('Cargando datos de la parcela...', 'info');
                
                const response = await fetch(`<?= $this->url("/parcelas/obtener") ?>?id=${id}`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                console.log('Response status:', response.status);
                console.log('Response URL:', response.url);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                console.log('Data received:', data);
                
                if (data.success && data.parcela) {
                    const parcela = data.parcela;
                    document.getElementById('editId').value = parcela.id;
                    document.getElementById('editNombre').value = parcela.nombre || '';
                    document.getElementById('editUbicacion').value = parcela.ubicacion || '';
                    document.getElementById('editEmpresa').value = parcela.empresa || '';
                    document.getElementById('editDueño').value = parcela.dueño || '';
                    document.getElementById('editOlivos').value = parcela.olivos || '0';
                    document.getElementById('editHidrante').value = parcela.hidrante || '0';
                    document.getElementById('editDescripcion').value = parcela.descripcion || '';
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

        // Función para eliminar parcela
        async function deleteParcela(id, nombre) {
            if (confirm(`¿Estás seguro de que quieres eliminar la parcela "${nombre}"?`)) {
                try {
                    const response = await fetch(`<?= $this->url("/parcelas/eliminar") ?>`, {
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
                        showToast('Parcela eliminada correctamente', 'success');
                    } else {
                        showToast('Error al eliminar la parcela: ' + data.message, 'error');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showToast('Error al eliminar la parcela', 'error');
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
        document.getElementById('createParcelaForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const parcelaData = Object.fromEntries(formData);
            
            console.log('Datos a enviar (crear):', parcelaData);
            
            try {
                showToast('Creando parcela...', 'info');
                
                const response = await fetch('<?= $this->url("/parcelas/crear") ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(parcelaData)
                });
                
                console.log('Response status (crear):', response.status);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                console.log('Response data (crear):', data);
                
                if (data.success) {
                    closeCreateSection();
                    location.reload(); // Recargar para mostrar la nueva parcela
                    showToast('Parcela creada correctamente', 'success');
                } else {
                    showToast('Error al crear: ' + (data.message || 'Error desconocido'), 'error');
                }
            } catch (error) {
                console.error('Error completo (crear):', error);
                showToast('Error de conexión: ' + error.message, 'error');
            }
        });

        // Manejo del formulario de editar
        document.getElementById('editParcelaForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const parcelaData = Object.fromEntries(formData);
            
            console.log('Datos a enviar (editar):', parcelaData);
            
            try {
                showToast('Actualizando parcela...', 'info');
                
                const response = await fetch('<?= $this->url("/parcelas/actualizar") ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(parcelaData)
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
                    showToast('Parcela actualizada correctamente', 'success');
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
            const editModal = document.getElementById('editModal');
            if (e.target === editModal) {
                closeEditModal();
            }
        });
    </script>

</body>
</html>