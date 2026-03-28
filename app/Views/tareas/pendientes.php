<?php $title = 'Tareas Pendientes'; ?>

<div class="container">
    <div class="page-header">
        <h2><?= emoji('clipboard', '1.2rem') ?> Tareas Pendientes</h2>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="abrirFormNueva()">+ Nueva tarea pendiente</button>
            <a href="<?= $this->url('/tareas') ?>" class="btn btn-secondary">📅 Ver calendario</a>
        </div>
    </div>

    <!-- Formulario nueva tarea pendiente -->
    <div id="formNueva" class="card" style="display:none;">
        <div class="card-header">
            <h3>Nueva tarea pendiente</h3>
            <button class="close-btn" onclick="cerrarFormNueva()">×</button>
        </div>
        <form id="formCrearPendiente">
            <div class="form-group">
                <label for="nuevo_titulo">Título: <span class="required">*</span></label>
                <input type="text" id="nuevo_titulo" name="titulo" required placeholder="Título de la tarea">
            </div>
            <div class="form-group">
                <label for="nuevo_descripcion">Descripción:</label>
                <textarea id="nuevo_descripcion" name="descripcion" rows="2" placeholder="Descripción opcional"></textarea>
            </div>
            <div class="form-buttons">
                <button type="button" class="btn btn-secondary" onclick="cerrarFormNueva()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>

    <!-- Lista de tareas pendientes -->
    <div id="listaPendientes">
        <?php if (empty($tareas)): ?>
        <div class="card" style="text-align:center; padding:2rem; color:#6b7280;">
            <p style="font-size:2rem; margin-bottom:0.5rem;">✅</p>
            <p>No hay tareas pendientes. ¡Todo al día!</p>
        </div>
        <?php else: ?>
        <div class="card">
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Descripción</th>
                        <th>Trabajadores</th>
                        <th>Parcelas</th>
                        <th>Creada</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaPendientesBody">
                    <?php foreach ($tareas as $t): ?>
                    <tr id="pendiente-row-<?= intval($t['id']) ?>">
                        <td><strong><?= htmlspecialchars($t['titulo'] ?: '—') ?></strong></td>
                        <td><?= htmlspecialchars($t['descripcion'] ?: '—') ?></td>
                        <td><?= htmlspecialchars($t['trabajadores_nombres'] ?: '—') ?></td>
                        <td><?= htmlspecialchars($t['parcelas_nombres'] ?: '—') ?></td>
                        <td><?= !empty($t['created_at']) ? date('d-m-Y', strtotime($t['created_at'])) : '—' ?></td>
                        <td>
                            <button class="btn btn-primary btn-sm" onclick="abrirModalFechar(<?= intval($t['id']) ?>, <?= htmlspecialchars(json_encode($t['titulo'] ?: ''), ENT_QUOTES) ?>)">
                                📅 Fechar
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="eliminarPendiente(<?= intval($t['id']) ?>)">
                                Eliminar
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal fechar tarea -->
<div id="modalFechar" class="modal" style="display:none;">
    <div class="modal-content" style="max-width:400px;">
        <div class="modal-header">
            <h3>📅 Fechar tarea</h3>
            <button class="modal-close" onclick="cerrarModalFechar()">&times;</button>
        </div>
        <p id="modalFecharTitulo" style="padding:0 1.5rem; color:#374151; font-weight:500;"></p>
        <form id="formFechar" style="padding:1rem 1.5rem;">
            <input type="hidden" id="fechar_id">
            <div class="form-group">
                <label for="fechar_fecha">Fecha de realización: <span class="required">*</span></label>
                <input type="date" id="fechar_fecha" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="cerrarModalFechar()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Confirmar</button>
            </div>
        </form>
    </div>
</div>

<script>
var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
var basePath  = window._APP_BASE_PATH || '';

function abrirFormNueva() {
    document.getElementById('formNueva').style.display = 'block';
    document.getElementById('nuevo_titulo').focus();
}
function cerrarFormNueva() {
    document.getElementById('formNueva').style.display = 'none';
    document.getElementById('formCrearPendiente').reset();
}

function abrirModalFechar(id, titulo) {
    document.getElementById('fechar_id').value = id;
    document.getElementById('modalFecharTitulo').textContent = titulo;
    document.getElementById('fechar_fecha').value = new Date().toISOString().split('T')[0];
    document.getElementById('modalFechar').style.display = 'flex';
    document.getElementById('fechar_fecha').focus();
}
function cerrarModalFechar() {
    document.getElementById('modalFechar').style.display = 'none';
}

function eliminarPendiente(id) {
    if (!confirm('¿Eliminar esta tarea pendiente?')) return;
    fetch(basePath + '/tareas/eliminar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ id: id })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.success) {
            var row = document.getElementById('pendiente-row-' + id);
            if (row) row.remove();
        } else {
            showToast(res.message, 'error');
        }
    })
    .catch(function() { showToast('Error de conexión', 'error'); });
}

document.getElementById('formCrearPendiente').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = this.querySelector('[type=submit]');
    btn.disabled = true;

    fetch(basePath + '/tareas/crearPendiente', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({
            titulo:      document.getElementById('nuevo_titulo').value,
            descripcion: document.getElementById('nuevo_descripcion').value
        })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        btn.disabled = false;
        if (res.success) {
            cerrarFormNueva();
            location.reload();
        } else {
            showToast(res.message, 'error');
        }
    })
    .catch(function() { btn.disabled = false; showToast('Error de conexión', 'error'); });
});

document.getElementById('formFechar').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = this.querySelector('[type=submit]');
    btn.disabled = true;

    var id    = document.getElementById('fechar_id').value;
    var fecha = document.getElementById('fechar_fecha').value;

    fetch(basePath + '/tareas/fechar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ id: parseInt(id), fecha: fecha })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        btn.disabled = false;
        if (res.success) {
            cerrarModalFechar();
            var row = document.getElementById('pendiente-row-' + id);
            if (row) row.remove();
        } else {
            showToast(res.message, 'error');
        }
    })
    .catch(function() { btn.disabled = false; showToast('Error de conexión', 'error'); });
});

document.getElementById('modalFechar').addEventListener('click', function(e) {
    if (e.target === this) cerrarModalFechar();
});
</script>
