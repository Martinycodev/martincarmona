<?php $title = 'Gestión de Usuarios'; ?>
<?php
$rolColors = [
    'empresa'     => '#16a34a',
    'admin'       => '#7c3aed',
    'propietario' => '#2563eb',
    'trabajador'  => '#d97706',
];
$rolLabels = [
    'empresa'     => 'Empresa',
    'admin'       => 'Admin',
    'propietario' => 'Propietario',
    'trabajador'  => 'Trabajador',
];
?>
<div class="container">
    <div class="page-header">
        <h2>👥 Gestión de Usuarios</h2>
    </div>

    <div style="display:flex; justify-content:flex-end; margin-bottom:.75rem;">
        <button class="btn btn-primary" onclick="abrirModal()">+ Nuevo usuario</button>
    </div>

    <div class="card">
        <table class="styled-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Vinculado con</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $u): ?>
                <tr id="user-row-<?= intval($u['id']) ?>">
                    <td><strong><?= htmlspecialchars($u['name']) ?></strong></td>
                    <td style="font-size:.9rem;"><?= htmlspecialchars($u['email']) ?></td>
                    <td>
                        <span style="background:<?= $rolColors[$u['rol']] ?? '#6b7280' ?>22; color:<?= $rolColors[$u['rol']] ?? '#6b7280' ?>; padding:.2rem .6rem; border-radius:999px; font-size:.8rem; font-weight:600;">
                            <?= $rolLabels[$u['rol']] ?? htmlspecialchars($u['rol']) ?>
                        </span>
                    </td>
                    <td style="font-size:.85rem;">
                        <?php if ($u['propietario_nombre']): ?>
                            👤 <?= htmlspecialchars($u['propietario_nombre']) ?>
                        <?php elseif ($u['trabajador_nombre']): ?>
                            👷 <?= htmlspecialchars($u['trabajador_nombre']) ?>
                        <?php else: ?>
                            <span style="color:#9ca3af;">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button class="btn btn-secondary btn-sm" onclick="editarUsuario(<?= htmlspecialchars(json_encode($u), ENT_QUOTES) ?>)">Editar</button>
                        <?php if ($u['id'] !== $currentUserId): ?>
                        <button class="btn btn-danger btn-sm" onclick="eliminarUsuario(<?= intval($u['id']) ?>, <?= htmlspecialchars(json_encode($u['name']), ENT_QUOTES) ?>)">Eliminar</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($usuarios)): ?>
                <tr><td colspan="5" style="text-align:center; color:#6b7280; padding:2rem;">Sin usuarios registrados.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal usuario -->
<div id="modalUsuario" class="modal" style="display:none;">
    <div class="modal-content" style="max-width:500px;">
        <div class="modal-header">
            <h3 id="modalUsuarioTitle">Nuevo usuario</h3>
            <button class="modal-close" onclick="cerrarModal()">&times;</button>
        </div>
        <form id="formUsuario" style="padding:1rem 1.5rem 1.5rem;">
            <input type="hidden" id="u_id" value="0">
            <div class="form-group">
                <label>Nombre <span class="required">*</span></label>
                <input type="text" id="u_name" required>
            </div>
            <div class="form-group">
                <label>Email <span class="required">*</span></label>
                <input type="email" id="u_email" required>
            </div>
            <div class="form-group">
                <label id="u_password_label">Contraseña <span class="required">*</span></label>
                <input type="password" id="u_password" autocomplete="new-password" placeholder="Mínimo 6 caracteres">
            </div>
            <div class="form-group">
                <label>Rol <span class="required">*</span></label>
                <select id="u_rol" onchange="onRolChange()">
                    <option value="empresa">Empresa</option>
                    <option value="admin">Admin</option>
                    <option value="propietario">Propietario</option>
                    <option value="trabajador">Trabajador</option>
                </select>
            </div>
            <div class="form-group" id="propietarioGroup" style="display:none;">
                <label>Propietario vinculado</label>
                <select id="u_propietario_id">
                    <option value="">— Sin vincular —</option>
                    <?php foreach ($propietarios as $p): ?>
                    <option value="<?= intval($p['id']) ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" id="trabajadorGroup" style="display:none;">
                <label>Trabajador vinculado</label>
                <select id="u_trabajador_id">
                    <option value="">— Sin vincular —</option>
                    <?php foreach ($trabajadores as $t): ?>
                    <option value="<?= intval($t['id']) ?>"><?= htmlspecialchars($t['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="cerrarModal()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
var csrfToken    = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
var basePath     = window._APP_BASE_PATH || '';
var currentUserId = <?= intval($currentUserId) ?>;

function abrirModal(u) {
    var isEdit = !!u;
    document.getElementById('modalUsuarioTitle').textContent = isEdit ? 'Editar usuario' : 'Nuevo usuario';
    document.getElementById('u_id').value    = u ? u.id    : 0;
    document.getElementById('u_name').value  = u ? u.name  : '';
    document.getElementById('u_email').value = u ? u.email : '';
    document.getElementById('u_password').value    = '';
    document.getElementById('u_password').required = !isEdit;
    document.getElementById('u_password_label').innerHTML = isEdit
        ? 'Contraseña <small style="color:#6b7280">(dejar vacío para no cambiar)</small>'
        : 'Contraseña <span class="required">*</span>';
    document.getElementById('u_rol').value = u ? u.rol : 'empresa';
    document.getElementById('u_propietario_id').value = (u && u.propietario_id) ? u.propietario_id : '';
    document.getElementById('u_trabajador_id').value  = (u && u.trabajador_id)  ? u.trabajador_id  : '';
    onRolChange();
    document.getElementById('modalUsuario').style.display = 'flex';
    document.getElementById('u_name').focus();
}

function cerrarModal() {
    document.getElementById('modalUsuario').style.display = 'none';
    document.getElementById('formUsuario').reset();
    document.getElementById('u_id').value = 0;
}

function editarUsuario(u) { abrirModal(u); }

function onRolChange() {
    var rol = document.getElementById('u_rol').value;
    document.getElementById('propietarioGroup').style.display = rol === 'propietario' ? '' : 'none';
    document.getElementById('trabajadorGroup').style.display  = rol === 'trabajador'  ? '' : 'none';
}

document.getElementById('formUsuario').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = this.querySelector('[type=submit]');
    btn.disabled = true;
    var id  = parseInt(document.getElementById('u_id').value);
    var url = id > 0 ? '/admin/actualizarUsuario' : '/admin/crearUsuario';

    var payload = {
        name:           document.getElementById('u_name').value,
        email:          document.getElementById('u_email').value,
        rol:            document.getElementById('u_rol').value,
        propietario_id: document.getElementById('u_propietario_id').value || null,
        trabajador_id:  document.getElementById('u_trabajador_id').value  || null,
    };
    if (id > 0) payload.id = id;
    var pw = document.getElementById('u_password').value;
    if (pw) payload.password = pw;

    fetch(basePath + url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify(payload)
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        btn.disabled = false;
        if (res.success) { cerrarModal(); location.reload(); }
        else alert('Error: ' + (res.message || 'Error desconocido'));
    })
    .catch(function() { btn.disabled = false; alert('Error de conexión'); });
});

function eliminarUsuario(id, name) {
    if (!confirm('¿Eliminar el usuario "' + name + '"? Esta acción no se puede deshacer.')) return;
    fetch(basePath + '/admin/eliminarUsuario', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ id: id })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.success) {
            var row = document.getElementById('user-row-' + id);
            if (row) row.remove();
        } else alert('Error: ' + res.message);
    })
    .catch(function() { alert('Error de conexión'); });
}

document.getElementById('modalUsuario').addEventListener('click', function(e) {
    if (e.target === this) cerrarModal();
});
</script>
