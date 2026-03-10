<?php
$title = 'Ficha de Propietario — ' . htmlspecialchars($propietario['nombre'] . ' ' . ($propietario['apellidos'] ?? ''));
?>
<div class="container">
    <div class="page-header">
        <h2>👤 <?= htmlspecialchars($propietario['nombre'] . ' ' . ($propietario['apellidos'] ?? '')) ?></h2>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="openEditModal()">✏️ Editar</button>
            <button class="btn btn-danger" onclick="deletePropietario()">🗑️ Eliminar</button>
            <a href="<?= $this->url('/datos/propietarios') ?>" class="btn btn-secondary">← Volver</a>
        </div>
    </div>

    <!-- Datos de contacto -->
    <div class="card">
        <div class="card-header"><h3>Datos de Contacto</h3></div>
        <div class="detail-grid">
            <?php if (!empty($propietario['dni'])): ?>
            <div><strong>DNI:</strong> <?= htmlspecialchars($propietario['dni']) ?></div>
            <?php endif; ?>
            <?php if (!empty($propietario['telefono'])): ?>
            <div><strong>Teléfono:</strong> <?= htmlspecialchars($propietario['telefono']) ?></div>
            <?php endif; ?>
            <?php if (!empty($propietario['email'])): ?>
            <div><strong>Email:</strong> <?= htmlspecialchars($propietario['email']) ?></div>
            <?php endif; ?>
            <?php if (empty($propietario['dni']) && empty($propietario['telefono']) && empty($propietario['email'])): ?>
            <div style="grid-column:1/-1;color:#666;">Sin datos de contacto registrados.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Imágenes del DNI -->
    <?php if (!empty($propietario['imagen_dni_anverso']) || !empty($propietario['imagen_dni_reverso'])): ?>
    <div class="card">
        <div class="card-header"><h3>Imágenes del DNI</h3></div>
        <div class="detail-grid">
            <?php if (!empty($propietario['imagen_dni_anverso'])): ?>
            <div>
                <strong>Anverso:</strong><br>
                <img src="<?= $this->url($propietario['imagen_dni_anverso']) ?>" alt="DNI Anverso"
                     style="max-width:220px;border-radius:6px;margin-top:8px;border:1px solid #444;">
            </div>
            <?php endif; ?>
            <?php if (!empty($propietario['imagen_dni_reverso'])): ?>
            <div>
                <strong>Reverso:</strong><br>
                <img src="<?= $this->url($propietario['imagen_dni_reverso']) ?>" alt="DNI Reverso"
                     style="max-width:220px;border-radius:6px;margin-top:8px;border:1px solid #444;">
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Parcelas asignadas -->
    <div class="card">
        <div class="card-header"><h3>Parcelas asignadas (<?= count($parcelas) ?>)</h3></div>
        <?php if (empty($parcelas)): ?>
            <div class="detail-grid"><div style="grid-column:1/-1;color:#666;">Este propietario no tiene parcelas asignadas.</div></div>
        <?php else: ?>
        <table class="styled-table" style="margin:0;">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Ubicación</th>
                    <th>Olivos</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($parcelas as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['nombre']) ?></td>
                    <td><?= htmlspecialchars($p['ubicacion'] ?? '—') ?></td>
                    <td><?= intval($p['olivos']) ?></td>
                    <td>
                        <a href="<?= $this->url('/parcelas/detalle?id=' . $p['id']) ?>"
                           class="btn btn-secondary btn-sm">Ver →</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
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
                <input type="hidden" id="editId" name="id" value="<?= intval($propietario['id']) ?>">
                <div class="form-row">
                    <div class="form-group">
                        <label>Nombre: <span class="required">*</span></label>
                        <input type="text" id="editNombre" name="nombre" value="<?= htmlspecialchars($propietario['nombre'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Apellidos:</label>
                        <input type="text" id="editApellidos" name="apellidos" value="<?= htmlspecialchars($propietario['apellidos'] ?? '') ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>DNI:</label>
                        <input type="text" id="editDni" name="dni" maxlength="20" value="<?= htmlspecialchars($propietario['dni'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Teléfono:</label>
                        <input type="text" id="editTelefono" name="telefono" maxlength="20" value="<?= htmlspecialchars($propietario['telefono'] ?? '') ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Email:</label>
                        <input type="email" id="editEmail" name="email" maxlength="150" value="<?= htmlspecialchars($propietario['email'] ?? '') ?>">
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
                                    <?php if (!empty($propietario['imagen_dni_anverso'])): ?>
                                        <img src="<?= $this->url($propietario['imagen_dni_anverso']) ?>" alt="DNI Anverso">
                                    <?php else: ?>
                                        <span>Sin imagen</span>
                                    <?php endif; ?>
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
                                    <?php if (!empty($propietario['imagen_dni_reverso'])): ?>
                                        <img src="<?= $this->url($propietario['imagen_dni_reverso']) ?>" alt="DNI Reverso">
                                    <?php else: ?>
                                        <span>Sin imagen</span>
                                    <?php endif; ?>
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
.form-section { margin-top: 1.5rem; border-top: 1px solid #444; padding-top: 1rem; }
.form-section h4 { margin-bottom: 0.75rem; color: #ccc; }
.dni-preview-wrap { display: flex; flex-direction: column; gap: 8px; margin-top: 6px; }
.dni-preview {
    width: 100%; max-width: 200px; height: 120px;
    border: 2px dashed #555; border-radius: 6px;
    display: flex; align-items: center; justify-content: center;
    background: #333; color: #888; font-size: 0.85rem;
    overflow: hidden;
}
.dni-preview img { width: 100%; height: 100%; object-fit: cover; }
.modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 1000; }
.modal-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); }
.modal-card {
    position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
    background: #2a2a2a; border: 1px solid #444; border-radius: 10px; padding: 2rem;
    width: 90%; max-width: 640px; max-height: 90vh; overflow-y: auto;
    box-shadow: 0 4px 24px rgba(0,0,0,0.5); z-index: 1; color: #eee;
}
.modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.modal-header h3 { margin: 0; color: #fff; }
.modal-header .close { cursor: pointer; font-size: 1.5rem; color: #888; }
.modal-header .close:hover { color: #fff; }
.modal-buttons { display: flex; gap: 8px; justify-content: flex-end; margin-top: 1.5rem; }
</style>

<script>
var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
var basePath  = window._APP_BASE_PATH || '';

function openEditModal() {
    document.getElementById('editModal').style.display = 'block';
}
function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

function showToast(msg, type) {
    var toast = document.getElementById('toast');
    toast.textContent = msg;
    toast.className = 'toast' + (type === 'error' ? ' toast-error' : ' toast-success');
    toast.style.display = 'block';
    setTimeout(function () { toast.style.display = 'none'; }, 3500);
}

document.getElementById('editPropietarioForm').addEventListener('submit', function(e) {
    e.preventDefault();
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
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify(data)
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.success) {
            showToast(res.message, 'success');
            closeEditModal();
            setTimeout(function() { location.reload(); }, 1000);
        } else {
            showToast(res.message || 'Error al actualizar', 'error');
        }
    })
    .catch(function() { showToast('Error de conexión', 'error'); });
});

function deletePropietario() {
    if (!confirm('¿Eliminar este propietario? Esta acción no se puede deshacer.')) return;
    fetch(basePath + '/propietarios/eliminar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ id: <?= intval($propietario['id']) ?> })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.success) {
            window.location.href = basePath + '/datos/propietarios';
        } else {
            alert('Error al eliminar: ' + (res.message || 'Error desconocido'));
        }
    })
    .catch(function() { alert('Error de conexión'); });
}

function subirDni(input, lado) {
    var id = <?= intval($propietario['id']) ?>;
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
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.success) {
            showToast('Imagen subida correctamente', 'success');
            var previewId = lado === 'anverso' ? 'previewAnverso' : 'previewReverso';
            var preview   = document.getElementById(previewId);
            var img       = document.createElement('img');
            img.src = basePath + res.imagen + '?t=' + Date.now();
            img.alt = lado;
            preview.innerHTML = '';
            preview.appendChild(img);
        } else {
            showToast(res.message || 'Error al subir la imagen', 'error');
        }
    })
    .catch(function() { showToast('Error de conexión', 'error'); });
}
</script>
