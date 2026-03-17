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
                        <label for="propietario_id">Propietario:</label>
                        <select id="propietario_id" name="propietario_id">
                            <option value="">-- Sin propietario --</option>
                        </select>
                        <input type="hidden" id="propietario" name="propietario" value="">
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
                        <textarea id="descripcion" name="descripcion" rows="3"
                            placeholder="Descripción opcional"></textarea>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="referencia_catastral">Referencia Catastral:</label>
                        <input type="text" id="referencia_catastral" name="referencia_catastral" maxlength="50">
                    </div>
                    <div class="form-group">
                        <label for="tipo_olivos">Tipo de Olivos:</label>
                        <input type="text" id="tipo_olivos" name="tipo_olivos" maxlength="100">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="num_municipio">Nº Municipio:</label>
                        <input type="text" id="num_municipio" name="num_municipio" maxlength="10" placeholder="Ej: 050">
                    </div>
                    <div class="form-group">
                        <label for="num_poligono">Nº Polígono:</label>
                        <input type="text" id="num_poligono" name="num_poligono" maxlength="10" placeholder="Ej: 012">
                    </div>
                    <div class="form-group">
                        <label for="num_parcela">Nº Parcela:</label>
                        <input type="text" id="num_parcela" name="num_parcela" maxlength="10" placeholder="Ej: 00045">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="año_plantacion">Año de Plantación:</label>
                        <input type="number" id="año_plantacion" name="año_plantacion" min="1900" max="2100">
                    </div>
                    <div class="form-group">
                        <label for="tipo_plantacion">Tipo de Plantación:</label>
                        <select id="tipo_plantacion" name="tipo_plantacion">
                            <option value="">-- Seleccionar --</option>
                            <option value="tradicional">Tradicional</option>
                            <option value="intensivo">Intensivo</option>
                            <option value="superintensivo">Superintensivo</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="riego_secano">Riego / Secano:</label>
                        <select id="riego_secano" name="riego_secano">
                            <option value="">-- Seleccionar --</option>
                            <option value="riego">Riego</option>
                            <option value="secano">Secano</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="corta">Corta:</label>
                        <select id="corta" name="corta">
                            <option value="">-- Seleccionar --</option>
                            <option value="par">Par</option>
                            <option value="impar">Impar</option>
                            <option value="siempre">Siempre</option>
                        </select>
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
                    <th>Nombre</th>
                    <th>Ubicación</th>
                    <th>Propietario</th>
                    <th>Olivos</th>
                    <th>Hidrante</th>
                </tr>
            </thead>
            <tbody id="parcelasTableBody">
                <?php if (empty($parcelas)): ?>
                    <tr>
                        <td colspan="5" class="no-data">
                            <div class="no-tareas">
                                <h3>🌾 No hay parcelas registradas</h3>
                                <p>Empieza añadiendo tu primera parcela para organizar el campo.</p>
                                <button class="btn btn-primary" onclick="openCreateModal()">➕ Nueva Parcela</button>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($parcelas as $parcela): ?>
                        <tr data-id="<?= $parcela['id'] ?>"
                            onclick="window.location.href='<?= $this->url('/parcelas/detalle?id=' . $parcela['id']) ?>'"
                            class="clickable-row">
                            <td><?= htmlspecialchars($parcela['nombre'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($parcela['ubicacion'] ?? '—') ?></td>
                            <td><?= htmlspecialchars(
                                !empty($parcela['propietario_nombre'])
                                ? $parcela['propietario_nombre'] . ' ' . $parcela['propietario_apellidos']
                                : ($parcela['propietario'] ?? '—')
                            ) ?></td>
                            <td><?= htmlspecialchars($parcela['olivos'] ?? '0') ?></td>
                            <td><?= htmlspecialchars($parcela['hidrante'] ?? '0') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
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
                        <label for="edit_propietario_id">Propietario:</label>
                        <select id="edit_propietario_id" name="propietario_id">
                            <option value="">-- Sin propietario --</option>
                        </select>
                        <input type="hidden" id="editPropietario" name="propietario" value="">
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
                        <textarea id="editDescripcion" name="descripcion" rows="3"
                            placeholder="Descripción opcional"></textarea>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="editReferenciaCatastral">Referencia Catastral:</label>
                        <input type="text" id="editReferenciaCatastral" name="referencia_catastral" maxlength="50">
                    </div>
                    <div class="form-group">
                        <label for="editTipoOlivos">Tipo de Olivos:</label>
                        <input type="text" id="editTipoOlivos" name="tipo_olivos" maxlength="100">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="editNumMunicipio">Nº Municipio:</label>
                        <input type="text" id="editNumMunicipio" name="num_municipio" maxlength="10" placeholder="Ej: 050">
                    </div>
                    <div class="form-group">
                        <label for="editNumPoligono">Nº Polígono:</label>
                        <input type="text" id="editNumPoligono" name="num_poligono" maxlength="10" placeholder="Ej: 012">
                    </div>
                    <div class="form-group">
                        <label for="editNumParcela">Nº Parcela:</label>
                        <input type="text" id="editNumParcela" name="num_parcela" maxlength="10" placeholder="Ej: 00045">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="editAñoPlantacion">Año de Plantación:</label>
                        <input type="number" id="editAñoPlantacion" name="año_plantacion" min="1900" max="2100">
                    </div>
                    <div class="form-group">
                        <label for="editTipoPlantacion">Tipo de Plantación:</label>
                        <select id="editTipoPlantacion" name="tipo_plantacion">
                            <option value="">-- Seleccionar --</option>
                            <option value="tradicional">Tradicional</option>
                            <option value="intensivo">Intensivo</option>
                            <option value="superintensivo">Superintensivo</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="editRiegoSecano">Riego / Secano:</label>
                        <select id="editRiegoSecano" name="riego_secano">
                            <option value="">-- Seleccionar --</option>
                            <option value="riego">Riego</option>
                            <option value="secano">Secano</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="editCorta">Corta:</label>
                        <select id="editCorta" name="corta">
                            <option value="">-- Seleccionar --</option>
                            <option value="par">Par</option>
                            <option value="impar">Impar</option>
                            <option value="siempre">Siempre</option>
                        </select>
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

    // Cargar propietarios en un select dinámicamente
    function cargarSelectPropietarios(selectId, selectedId) {
        fetch(window._APP_BASE_PATH + '/parcelas/propietarios')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!data.success) return;
                var sel = document.getElementById(selectId);
                sel.innerHTML = '<option value="">-- Sin propietario --</option>';
                data.propietarios.forEach(function(p) {
                    var opt = document.createElement('option');
                    opt.value = p.id;
                    opt.textContent = p.apellidos ? p.apellidos + ', ' + p.nombre : p.nombre;
                    if (selectedId && parseInt(selectedId) === p.id) opt.selected = true;
                    sel.appendChild(opt);
                });
            });
    }

    // Funciones para manejar la sección de crear
    function openCreateModal() {
        document.getElementById('createSection').style.display = 'block';
        createSectionVisible = true;
        cargarSelectPropietarios('propietario_id', null);
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

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            if (data.success && data.parcela) {
                const parcela = data.parcela;
                document.getElementById('editId').value = parcela.id;
                document.getElementById('editNombre').value = parcela.nombre || '';
                document.getElementById('editUbicacion').value = parcela.ubicacion || '';
                document.getElementById('editPropietario').value = parcela.propietario || '';
                document.getElementById('editOlivos').value = parcela.olivos || '0';
                document.getElementById('editHidrante').value = parcela.hidrante || '0';
                document.getElementById('editDescripcion').value = parcela.descripcion || '';
                document.getElementById('editReferenciaCatastral').value = parcela.referencia_catastral || '';
                document.getElementById('editNumMunicipio').value = parcela.num_municipio || '';
                document.getElementById('editNumPoligono').value = parcela.num_poligono || '';
                document.getElementById('editNumParcela').value = parcela.num_parcela || '';
                document.getElementById('editTipoOlivos').value = parcela.tipo_olivos || '';
                document.getElementById('editAñoPlantacion').value = parcela.año_plantacion || '';
                document.getElementById('editTipoPlantacion').value = parcela.tipo_plantacion || '';
                document.getElementById('editRiegoSecano').value = parcela.riego_secano || '';
                document.getElementById('editCorta').value = parcela.corta || '';
                cargarSelectPropietarios('edit_propietario_id', parcela.propietario_id);
                openEditModal(buttonElement);
                showToast('Datos cargados correctamente', 'success');
            } else {
                showToast('Error: ' + (data.message || 'No se pudieron cargar los datos'), 'error');
            }
        } catch (error) {
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

    // showToast() ya definida globalmente en modal-functions.js

    // Manejo del formulario de crear
    document.getElementById('createParcelaForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        const parcelaData = Object.fromEntries(formData);

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

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            if (data.success) {
                closeCreateSection();
                location.reload(); // Recargar para mostrar la nueva parcela
                showToast('Parcela creada correctamente', 'success');
            } else {
                showToast('Error al crear: ' + (data.message || 'Error desconocido'), 'error');
            }
        } catch (error) {
            showToast('Error de conexión: ' + error.message, 'error');
        }
    });

    // Manejo del formulario de editar
    document.getElementById('editParcelaForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        const parcelaData = Object.fromEntries(formData);

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

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            if (data.success) {
                closeEditModal();
                location.reload(); // Recargar para mostrar los cambios
                showToast('Parcela actualizada correctamente', 'success');
            } else {
                showToast('Error al actualizar: ' + (data.message || 'Error desconocido'), 'error');
            }
        } catch (error) {
            showToast('Error de conexión: ' + error.message, 'error');
        }
    });

    // Cerrar modales al hacer clic fuera
    window.addEventListener('click', function (e) {
        const editModal = document.getElementById('editModal');
        if (e.target === editModal) {
            closeEditModal();
        }
    });
</script>

</body>

</html>