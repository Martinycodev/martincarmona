<?php
$title = 'Gestión de Proveedores';
?>
<div class="container">
    <div class="page-header">
        <h1>🚚 Gestión de Proveedores</h1>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="openCreateSection()">➕ Nuevo Proveedor</button>
            <a href="<?= $this->url('/datos') ?>" class="btn btn-secondary">← Volver</a>
        </div>
    </div>

    <!-- Sección de Crear Nuevo Proveedor -->
    <div class="create-section" id="createSection" style="display:none">
        <div class="card">
            <div class="card-header">
                <h3>➕ Crear Nuevo Proveedor</h3>
                <button class="close-btn" onclick="closeCreateSection()">×</button>
            </div>
            <form id="createProveedorForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="nombre">Nombre: <span class="required">*</span></label>
                        <input type="text" id="nombre" name="nombre" required placeholder="Nombre comercial">
                    </div>
                    <div class="form-group">
                        <label for="cif">CIF / NIF:</label>
                        <input type="text" id="cif" name="cif" maxlength="20" placeholder="B12345678">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="telefono">Teléfono:</label>
                        <input type="text" id="telefono" name="telefono" maxlength="20">
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" maxlength="150">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="sector">Sector:</label>
                        <select id="sector" name="sector">
                            <option value="">— Sin especificar —</option>
                            <option value="agricultura">Agricultura</option>
                            <option value="maquinaria">Maquinaria</option>
                            <option value="quimicos">Químicos / Fitosanitarios</option>
                            <option value="transporte">Transporte</option>
                            <option value="servicios">Servicios</option>
                            <option value="otros">Otros</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="contacto_principal">Persona de contacto:</label>
                        <input type="text" id="contacto_principal" name="contacto_principal" maxlength="150">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="notas">Notas:</label>
                        <textarea id="notas" name="notas" rows="2" placeholder="Observaciones sobre el proveedor"></textarea>
                    </div>
                </div>

                <div class="form-buttons">
                    <button type="button" class="btn btn-secondary" onclick="closeCreateSection()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear Proveedor</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Proveedores -->
    <div class="table-container">
        <table class="styled-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>CIF</th>
                    <th>Teléfono</th>
                    <th>Email</th>
                    <th>Sector</th>
                </tr>
            </thead>
            <tbody id="proveedoresTableBody">
                <?php foreach ($proveedores as $p): ?>
                <tr data-id="<?= intval($p['id']) ?>"
                    class="clickable-row"
                    onclick="editarProveedor(<?= intval($p['id']) ?>)">
                    <td><?= htmlspecialchars($p['nombre'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($p['cif'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($p['telefono'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($p['email'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($p['sector'] ?? '—') ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($proveedores)): ?>
                <tr><td colspan="5" style="text-align:center; color:#6b7280; padding:2rem;">No hay proveedores registrados.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal de Edición -->
    <div id="editModal" class="modal" style="display:none">
        <div class="modal-content">
            <div class="modal-header">
                <h3>✏️ Editar Proveedor</h3>
                <span class="close" onclick="cerrarEditModal()">&times;</span>
            </div>
            <form id="editProveedorForm">
                <input type="hidden" id="editId" name="id">

                <div class="form-row">
                    <div class="form-group">
                        <label for="editNombre">Nombre: <span class="required">*</span></label>
                        <input type="text" id="editNombre" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="editCif">CIF / NIF:</label>
                        <input type="text" id="editCif" name="cif" maxlength="20">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="editTelefono">Teléfono:</label>
                        <input type="text" id="editTelefono" name="telefono" maxlength="20">
                    </div>
                    <div class="form-group">
                        <label for="editEmail">Email:</label>
                        <input type="email" id="editEmail" name="email" maxlength="150">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="editSector">Sector:</label>
                        <select id="editSector" name="sector">
                            <option value="">— Sin especificar —</option>
                            <option value="agricultura">Agricultura</option>
                            <option value="maquinaria">Maquinaria</option>
                            <option value="quimicos">Químicos / Fitosanitarios</option>
                            <option value="transporte">Transporte</option>
                            <option value="servicios">Servicios</option>
                            <option value="otros">Otros</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="editContacto">Persona de contacto:</label>
                        <input type="text" id="editContacto" name="contacto_principal" maxlength="150">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="editNotas">Notas:</label>
                        <textarea id="editNotas" name="notas" rows="2"></textarea>
                    </div>
                </div>

                <div class="modal-buttons">
                    <button type="button" class="btn-modal btn-danger-outline"
                            onclick="eliminarProveedor(document.getElementById('editId').value)">
                        Eliminar
                    </button>
                    <button type="button" class="btn-modal btn-secondary" onclick="cerrarEditModal()">Cancelar</button>
                    <button type="submit" class="btn-modal btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast de notificaciones -->
    <div id="toast" class="toast"></div>

<script>
(function () {
    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
    var basePath  = window._APP_BASE_PATH || '';

    /* ── Crear ─────────────────────────────────────────────────── */
    window.openCreateSection = function () {
        document.getElementById('createSection').style.display = 'block';
        document.getElementById('createProveedorForm').reset();
    };

    window.closeCreateSection = function () {
        document.getElementById('createSection').style.display = 'none';
    };

    document.getElementById('createProveedorForm').addEventListener('submit', function (e) {
        e.preventDefault();
        var form = this;
        var data = {
            nombre:             form.nombre.value.trim(),
            cif:                form.cif.value.trim(),
            telefono:           form.telefono.value.trim(),
            email:              form.email.value.trim(),
            sector:             form.sector.value,
            contacto_principal: form.contacto_principal.value.trim(),
            notas:              form.notas.value.trim()
        };

        fetch(basePath + '/proveedores/crear', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify(data)
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (res.success) {
                showToast(res.message, 'success');
                closeCreateSection();
                setTimeout(function () { location.reload(); }, 800);
            } else {
                showToast(res.message || 'Error al crear', 'error');
            }
        })
        .catch(function () { showToast('Error de conexión', 'error'); });
    });

    /* ── Editar ────────────────────────────────────────────────── */
    window.cerrarEditModal = function () {
        document.getElementById('editModal').style.display = 'none';
    };

    window.editarProveedor = function (id) {
        fetch(basePath + '/proveedores/obtener?id=' + id, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (!res.success) {
                showToast(res.message || 'Error al cargar', 'error');
                return;
            }
            var p = res.proveedor;
            document.getElementById('editId').value       = p.id;
            document.getElementById('editNombre').value   = p.nombre || '';
            document.getElementById('editCif').value      = p.cif || '';
            document.getElementById('editTelefono').value = p.telefono || '';
            document.getElementById('editEmail').value    = p.email || '';
            document.getElementById('editSector').value   = p.sector || '';
            document.getElementById('editContacto').value = p.contacto_principal || '';
            document.getElementById('editNotas').value    = p.notas || '';
            document.getElementById('editModal').style.display = 'block';
        })
        .catch(function () { showToast('Error de conexión', 'error'); });
    };

    document.getElementById('editProveedorForm').addEventListener('submit', function (e) {
        e.preventDefault();
        var data = {
            id:                 parseInt(document.getElementById('editId').value),
            nombre:             document.getElementById('editNombre').value.trim(),
            cif:                document.getElementById('editCif').value.trim(),
            telefono:           document.getElementById('editTelefono').value.trim(),
            email:              document.getElementById('editEmail').value.trim(),
            sector:             document.getElementById('editSector').value,
            contacto_principal: document.getElementById('editContacto').value.trim(),
            notas:              document.getElementById('editNotas').value.trim()
        };

        fetch(basePath + '/proveedores/actualizar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify(data)
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (res.success) {
                showToast(res.message, 'success');
                cerrarEditModal();
                setTimeout(function () { location.reload(); }, 800);
            } else {
                showToast(res.message || 'Error al actualizar', 'error');
            }
        })
        .catch(function () { showToast('Error de conexión', 'error'); });
    });

    /* ── Eliminar ──────────────────────────────────────────────── */
    window.eliminarProveedor = function (id) {
        if (!confirm('¿Seguro que deseas eliminar este proveedor?')) return;

        fetch(basePath + '/proveedores/eliminar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ id: parseInt(id) })
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (res.success) {
                showToast(res.message, 'success');
                cerrarEditModal();
                var row = document.querySelector('#proveedoresTableBody tr[data-id="' + id + '"]');
                if (row) row.remove();
            } else {
                showToast(res.message || 'Error al eliminar', 'error');
            }
        })
        .catch(function () { showToast('Error de conexión', 'error'); });
    };
}());
</script>
</div>
