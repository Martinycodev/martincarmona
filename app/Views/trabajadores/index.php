<?php 
$title = 'Gestión de Trabajadores';
?>
<div class="container">
        <div class="page-header">
            <h1>👷‍♂️ Gestión de Trabajadores</h1>
            <div class="header-actions">
                <button class="btn btn-primary" onclick="openCreateModal()">➕ Nuevo Trabajador</button>
                <a href="<?= $this->url('/datos') ?>" class="btn btn-secondary">← Volver</a>
            </div>
        </div>

        <!-- Sección de Crear Nuevo Trabajador -->
        <div class="create-section" id="createSection">
            <div class="card">
                <div class="card-header">
                    <h3>➕ Crear Nuevo Trabajador</h3>
                    <button class="close-btn" onclick="closeCreateSection()">×</button>
                </div>
                <form id="createWorkerForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombre">Nombre Completo:</label>
                            <input type="text" id="nombre" name="nombre" required>
                        </div>
                        <div class="form-group">
                            <label for="dni">DNI (8 números + 1 letra mayúscula):</label>
                            <input type="text" id="dni" name="dni" maxlength="9" pattern="[0-9]{8}[A-Z]" placeholder="12345678A" title="Formato: 8 números seguidos de 1 letra mayúscula (ej: 12345678A)" oninput="formatearDNI(this)">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="ss">Número SS:</label>
                            <input type="text" id="ss" name="ss" maxlength="12">
                        </div>

                    </div>
                    <div class="form-buttons">
                        <button type="button" class="btn btn-secondary" onclick="closeCreateSection()">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Crear Trabajador</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de Trabajadores -->
        <div class="table-container">
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>DNI</th>
                        <th>SS</th>

                        <th class="actions-column">Acciones</th>
                    </tr>
                </thead>
                <tbody id="trabajadoresTableBody">
                    <?php foreach ($trabajadores as $trabajador): ?>
                    <tr data-id="<?= $trabajador['id'] ?>">
                        <td><?= htmlspecialchars($trabajador['id'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($trabajador['nombre'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($trabajador['dni'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($trabajador['ss'] ?? '-') ?></td>

                        <td class="actions">
                            <a href="<?= $this->url('/datos/trabajadores?id=' . $trabajador['id']) ?>" class="btn-icon btn-view" title="Ver detalles">
                                👁️
                            </a>
                            <button class="btn-icon btn-edit" onclick="editWorker(<?= $trabajador['id'] ?>, this)" title="Editar">
                                ✏️
                            </button>
                            <button class="btn-icon btn-delete" onclick="deleteWorker(<?= $trabajador['id'] ?>, '<?= htmlspecialchars($trabajador['nombre']) ?>')" title="Eliminar">
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
                    <h3>✏️ Editar Trabajador</h3>
                    <span class="close" onclick="closeEditModal()">&times;</span>
                </div>
                <form id="editWorkerForm">
                    <input type="hidden" id="editId" name="id">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="editNombre">Nombre Completo:</label>
                            <input type="text" id="editNombre" name="nombre" required>
                        </div>
                        <div class="form-group">
                            <label for="editDni">DNI (8 números + 1 letra mayúscula):</label>
                            <input type="text" id="editDni" name="dni" maxlength="9" pattern="[0-9]{8}[A-Z]" placeholder="12345678A" title="Formato: 8 números seguidos de 1 letra mayúscula (ej: 12345678A)" oninput="formatearDNI(this)">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="editSs">Número SS:</label>
                            <input type="text" id="editSs" name="ss" maxlength="12">
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
            document.getElementById('createWorkerForm').reset();
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
            document.getElementById('editWorkerForm').reset();
            
            // Limpiar posicionamiento dinámico
            if (modalContent) {
                modalContent.classList.remove('dynamic-position');
                modalContent.style.top = '';
            }
        }

        // Función para editar trabajador
        async function editWorker(id, buttonElement = null) {
            try {
                // Mostrar indicador de carga
                showToast('Cargando datos del trabajador...', 'info');
                
                const response = await fetch(`<?= $this->url("/trabajadores/obtener") ?>?id=${id}`, {
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
                
                if (data.success && data.trabajador) {
                    const worker = data.trabajador;
                    document.getElementById('editId').value = worker.id;
                    document.getElementById('editNombre').value = worker.nombre || '';
                    document.getElementById('editDni').value = worker.dni || '';
                    document.getElementById('editSs').value = worker.ss || '';

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

        // Función para eliminar trabajador
        async function deleteWorker(id, nombre) {
            if (confirm(`¿Estás seguro de que quieres eliminar al trabajador "${nombre}"?`)) {
                try {
                    const response = await fetch(`<?= $this->url("/trabajadores/eliminar") ?>`, {
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
                        showToast('Trabajador eliminado correctamente', 'success');
                    } else {
                        showToast('Error al eliminar el trabajador: ' + data.message, 'error');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showToast('Error al eliminar el trabajador', 'error');
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

        // Manejo del formulario de crear
        document.getElementById('createWorkerForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Validar DNI antes de enviar
            const dni = document.getElementById('dni').value.trim();
            if (dni && !validarDNI(dni)) {
                showToast('El DNI debe tener el formato: 8 números + 1 letra mayúscula (ej: 12345678A)', 'error');
                return;
            }
            
            const formData = new FormData(this);
            const workerData = Object.fromEntries(formData);
            
            console.log('Datos a enviar (crear):', workerData);
            
            try {
                showToast('Creando trabajador...', 'info');
                
                const response = await fetch('<?= $this->url("/trabajadores/crear") ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(workerData)
                });
                
                console.log('Response status (crear):', response.status);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                console.log('Response data (crear):', data);
                
                if (data.success) {
                    closeCreateSection();
                    location.reload(); // Recargar para mostrar el nuevo trabajador
                    showToast('Trabajador creado correctamente', 'success');
                } else {
                    showToast('Error al crear: ' + (data.message || 'Error desconocido'), 'error');
                }
            } catch (error) {
                console.error('Error completo (crear):', error);
                showToast('Error de conexión: ' + error.message, 'error');
            }
        });

        // Manejo del formulario de editar
        document.getElementById('editWorkerForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Validar DNI antes de enviar
            const dni = document.getElementById('editDni').value.trim();
            if (dni && !validarDNI(dni)) {
                showToast('El DNI debe tener el formato: 8 números + 1 letra mayúscula (ej: 12345678A)', 'error');
                return;
            }
            
            const formData = new FormData(this);
            const workerData = Object.fromEntries(formData);
            
            console.log('Datos a enviar (editar):', workerData);
            
            try {
                showToast('Actualizando trabajador...', 'info');
                
                const response = await fetch('<?= $this->url("/trabajadores/actualizar") ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(workerData)
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
                    showToast('Trabajador actualizado correctamente', 'success');
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
