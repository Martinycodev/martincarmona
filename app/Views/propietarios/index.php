<?php
$title = 'Gestión de Propietarios';
?>
<div class="container">
    <div class="page-header">
        <h1>Gestión de Propietarios</h1>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="openCreateSection()">Nuevo Propietario</button>
            <a href="<?= $this->url('/datos') ?>" class="btn btn-secondary">Volver</a>
        </div>
    </div>

    <!-- Sección de Crear Nuevo Propietario -->
    <div class="create-section" id="createSection" style="display:none">
        <div class="card">
            <div class="card-header">
                <h3>Crear Nuevo Propietario</h3>
                <button class="close-btn" onclick="closeCreateSection()">×</button>
            </div>
            <form id="createPropietarioForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="nombre">Nombre: <span class="required">*</span></label>
                        <input type="text" id="nombre" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="apellidos">Apellidos:</label>
                        <input type="text" id="apellidos" name="apellidos">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="dni">DNI:</label>
                        <input type="text" id="dni" name="dni" maxlength="20">
                    </div>
                    <div class="form-group">
                        <label for="telefono">Teléfono:</label>
                        <input type="text" id="telefono" name="telefono" maxlength="20">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" maxlength="150">
                    </div>
                </div>

                <div class="form-buttons">
                    <button type="button" class="btn btn-secondary" onclick="closeCreateSection()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear Propietario</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Propietarios -->
    <div class="table-container">
        <table class="styled-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Apellidos</th>
                    <th>DNI</th>
                    <th>Teléfono</th>
                    <th>Email</th>
                    <th class="actions-column">Acciones</th>
                </tr>
            </thead>
            <tbody id="propietariosTableBody">
                <?php foreach ($propietarios as $propietario): ?>
                <tr data-id="<?= $propietario['id'] ?>">
                    <td><?= htmlspecialchars($propietario['nombre'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($propietario['apellidos'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($propietario['dni'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($propietario['telefono'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($propietario['email'] ?? '—') ?></td>
                    <td class="actions">
                        <button class="btn-icon btn-edit"
                                onclick="loadPropietario(<?= $propietario['id'] ?>)"
                                title="Editar">Editar</button>
                        <button class="btn-icon btn-delete"
                                onclick="deletePropietario(<?= $propietario['id'] ?>, '<?= htmlspecialchars($propietario['nombre'], ENT_QUOTES) ?>')"
                                title="Eliminar">Eliminar</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal de Edición -->
    <div id="editModal" class="modal" style="display:none">
        <div class="modal-overlay" onclick="closeEditModal()"></div>
        <div class="modal-card">
            <div class="modal-header">
                <h3>Editar Propietario</h3>
                <span class="close" onclick="closeEditModal()">&times;</span>
            </div>
            <form id="editPropietarioForm">
                <input type="hidden" id="editId" name="id">

                <div class="form-row">
                    <div class="form-group">
                        <label for="editNombre">Nombre: <span class="required">*</span></label>
                        <input type="text" id="editNombre" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="editApellidos">Apellidos:</label>
                        <input type="text" id="editApellidos" name="apellidos">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="editDni">DNI:</label>
                        <input type="text" id="editDni" name="dni" maxlength="20">
                    </div>
                    <div class="form-group">
                        <label for="editTelefono">Teléfono:</label>
                        <input type="text" id="editTelefono" name="telefono" maxlength="20">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="editEmail">Email:</label>
                        <input type="email" id="editEmail" name="email" maxlength="150">
                    </div>
                </div>

                <!-- Imágenes del DNI -->
                <div class="form-section">
                    <h4>Imágenes del DNI</h4>
                    <div class="form-row">
                        <div class="form-group">
                            <label>DNI Anverso:</label>
                            <div class="dni-preview-wrap">
                                <div class="dni-preview" id="previewAnverso">
                                    <span>Sin imagen</span>
                                </div>
                                <label class="btn btn-secondary btn-sm" for="inputAnverso">
                                    Subir anverso
                                    <input type="file" id="inputAnverso" accept="image/*" style="display:none"
                                           onchange="subirDni(this, 'anverso')">
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>DNI Reverso:</label>
                            <div class="dni-preview-wrap">
                                <div class="dni-preview" id="previewReverso">
                                    <span>Sin imagen</span>
                                </div>
                                <label class="btn btn-secondary btn-sm" for="inputReverso">
                                    Subir reverso
                                    <input type="file" id="inputReverso" accept="image/*" style="display:none"
                                           onchange="subirDni(this, 'reverso')">
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-buttons">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast de notificaciones -->
    <div id="toast" class="toast"></div>
</div>

<style>
.required { color: #dc3545; }
.form-section { margin-top: 1.5rem; border-top: 1px solid #dee2e6; padding-top: 1rem; }
.form-section h4 { margin-bottom: 0.75rem; color: #495057; }
.dni-preview-wrap { display: flex; flex-direction: column; gap: 8px; margin-top: 6px; }
.dni-preview {
    width: 100%; max-width: 200px; height: 120px;
    border: 2px dashed #dee2e6; border-radius: 6px;
    display: flex; align-items: center; justify-content: center;
    background: #f8f9fa; color: #adb5bd; font-size: 0.85rem;
    overflow: hidden;
}
.dni-preview img { width: 100%; height: 100%; object-fit: cover; }

/* Modal overlay style */
.modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 1000; }
.modal-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
.modal-card {
    position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
    background: #fff; border-radius: 8px; padding: 2rem;
    width: 90%; max-width: 640px; max-height: 90vh; overflow-y: auto;
    box-shadow: 0 4px 24px rgba(0,0,0,0.2); z-index: 1;
}
.modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.modal-header h3 { margin: 0; }
.modal-header .close { cursor: pointer; font-size: 1.5rem; color: #6c757d; }
.modal-buttons { display: flex; gap: 8px; justify-content: flex-end; margin-top: 1.5rem; }
</style>

<script>
(function () {
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var basePath  = window._APP_BASE_PATH || '';

    function showToast(msg, type) {
        var toast = document.getElementById('toast');
        toast.textContent = msg;
        toast.className = 'toast' + (type === 'error' ? ' toast-error' : ' toast-success');
        toast.style.display = 'block';
        setTimeout(function () { toast.style.display = 'none'; }, 3500);
    }

    window.openCreateSection = function () {
        document.getElementById('createSection').style.display = 'block';
        document.getElementById('createPropietarioForm').reset();
    };

    window.closeCreateSection = function () {
        document.getElementById('createSection').style.display = 'none';
    };

    window.closeEditModal = function () {
        document.getElementById('editModal').style.display = 'none';
    };

    window.loadPropietario = function (id) {
        fetch(basePath + '/propietarios/obtener?id=' + id, {
            headers: { 'X-CSRF-TOKEN': csrfToken }
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (!data.success) {
                showToast(data.message || 'Error al cargar el propietario', 'error');
                return;
            }
            var p = data.propietario;
            document.getElementById('editId').value        = p.id;
            document.getElementById('editNombre').value    = p.nombre || '';
            document.getElementById('editApellidos').value = p.apellidos || '';
            document.getElementById('editDni').value       = p.dni || '';
            document.getElementById('editTelefono').value  = p.telefono || '';
            document.getElementById('editEmail').value     = p.email || '';

            // Mostrar imágenes si existen
            var prevAnverso = document.getElementById('previewAnverso');
            var prevReverso = document.getElementById('previewReverso');

            if (p.imagen_dni_anverso) {
                prevAnverso.innerHTML = '<img src="' + basePath + p.imagen_dni_anverso + '" alt="Anverso">';
            } else {
                prevAnverso.innerHTML = '<span>Sin imagen</span>';
            }
            if (p.imagen_dni_reverso) {
                prevReverso.innerHTML = '<img src="' + basePath + p.imagen_dni_reverso + '" alt="Reverso">';
            } else {
                prevReverso.innerHTML = '<span>Sin imagen</span>';
            }

            document.getElementById('editModal').style.display = 'block';
        })
        .catch(function () {
            showToast('Error de conexión', 'error');
        });
    };

    window.submitCreateForm = function (e) {
        e.preventDefault();
        var form = document.getElementById('createPropietarioForm');
        var data = {
            nombre:    form.nombre.value.trim(),
            apellidos: form.apellidos.value.trim(),
            dni:       form.dni.value.trim(),
            telefono:  form.telefono.value.trim(),
            email:     form.email.value.trim()
        };

        fetch(basePath + '/propietarios/crear', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(data)
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (res.success) {
                showToast(res.message, 'success');
                closeCreateSection();
                setTimeout(function () { location.reload(); }, 1000);
            } else {
                showToast(res.message || 'Error al crear el propietario', 'error');
            }
        })
        .catch(function () {
            showToast('Error de conexión', 'error');
        });
    };

    window.submitEditForm = function (e) {
        e.preventDefault();
        var form = document.getElementById('editPropietarioForm');
        var data = {
            id:        parseInt(document.getElementById('editId').value),
            nombre:    document.getElementById('editNombre').value.trim(),
            apellidos: document.getElementById('editApellidos').value.trim(),
            dni:       document.getElementById('editDni').value.trim(),
            telefono:  document.getElementById('editTelefono').value.trim(),
            email:     document.getElementById('editEmail').value.trim()
        };

        fetch(basePath + '/propietarios/actualizar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(data)
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (res.success) {
                showToast(res.message, 'success');
                closeEditModal();
                setTimeout(function () { location.reload(); }, 1000);
            } else {
                showToast(res.message || 'Error al actualizar el propietario', 'error');
            }
        })
        .catch(function () {
            showToast('Error de conexión', 'error');
        });
    };

    window.deletePropietario = function (id, nombre) {
        if (!confirm('¿Seguro que deseas eliminar al propietario "' + nombre + '"?')) {
            return;
        }

        fetch(basePath + '/propietarios/eliminar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ id: id })
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (res.success) {
                showToast(res.message, 'success');
                var row = document.querySelector('#propietariosTableBody tr[data-id="' + id + '"]');
                if (row) row.remove();
            } else {
                showToast(res.message || 'Error al eliminar el propietario', 'error');
            }
        })
        .catch(function () {
            showToast('Error de conexión', 'error');
        });
    };

    window.subirDni = function (input, lado) {
        var id = parseInt(document.getElementById('editId').value);
        if (!id || id <= 0) {
            showToast('Guarda el propietario antes de subir imágenes', 'error');
            return;
        }

        var file = input.files[0];
        if (!file) return;

        var formData = new FormData();
        formData.append('id', id);
        formData.append('lado', lado);
        formData.append('imagen', file);
        formData.append('csrf_token', csrfToken);

        fetch(basePath + '/propietarios/subirImagenDni', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            body: formData
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (res.success) {
                showToast('Imagen subida correctamente', 'success');
                var previewId = lado === 'anverso' ? 'previewAnverso' : 'previewReverso';
                var preview   = document.getElementById(previewId);
                preview.innerHTML = '<img src="' + basePath + res.imagen + '?t=' + Date.now() + '" alt="' + lado + '">';
            } else {
                showToast(res.message || 'Error al subir la imagen', 'error');
            }
        })
        .catch(function () {
            showToast('Error de conexión', 'error');
        });
    };

    // Vincular eventos de formularios
    document.getElementById('createPropietarioForm').addEventListener('submit', submitCreateForm);
    document.getElementById('editPropietarioForm').addEventListener('submit', submitEditForm);
}());
</script>
