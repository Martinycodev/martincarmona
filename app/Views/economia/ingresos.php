<?php $title = 'Economía — Ingresos'; ?>

<style>
.eco-tabs { display:flex; gap:0; border-bottom:2px solid #333; margin-bottom:24px; }
.eco-tabs a {
    padding:10px 20px; text-decoration:none; color:#aaa; font-size:.9rem;
    border-bottom:3px solid transparent; margin-bottom:-2px; transition:color .2s;
}
.eco-tabs a.active { color:#fff; border-bottom-color:#4caf50; }
.eco-tabs a:hover  { color:#fff; }

.eco-toolbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px; }
.eco-toolbar h2 { margin:0; font-size:1.1rem; color:#ccc; font-weight:600; }

.eco-table { width:100%; border-collapse:collapse; font-size:.875rem; }
.eco-table th { background:#222; color:#888; font-weight:500; text-align:left; padding:10px 12px; border-bottom:1px solid #333; font-size:.8rem; text-transform:uppercase; }
.eco-table td { padding:10px 12px; border-bottom:1px solid #2a2a2a; color:#ccc; vertical-align:middle; }
.eco-table tr:last-child td { border-bottom:none; }
.eco-table tr:hover td { background:#1e1e1e; }
.eco-table .actions { display:flex; gap:6px; }

.badge-cuenta { display:inline-block; padding:2px 8px; border-radius:12px; font-size:.75rem; font-weight:600; }
.badge-banco    { background:#1565c0; color:#90caf9; }
.badge-efectivo { background:#2e7d32; color:#a5d6a7; }
.badge-cat { display:inline-block; padding:2px 8px; border-radius:12px; font-size:.75rem; background:#333; color:#bbb; }
.importe-ingreso { color:#4caf50; font-weight:600; }

.btn-icon { background:none; border:1px solid #444; border-radius:6px; padding:4px 9px; cursor:pointer; color:#aaa; font-size:.85rem; transition:all .15s; }
.btn-icon:hover { background:#333; color:#fff; border-color:#666; }
.btn-icon.danger:hover { background:#b71c1c; border-color:#f44336; color:#fff; }

.eco-modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.7); z-index:900; }
.eco-modal-overlay.open { display:flex; align-items:center; justify-content:center; }
.eco-modal { background:#1e1e1e; border:1px solid #333; border-radius:12px; width:100%; max-width:480px; padding:28px; }
.eco-modal h3 { margin:0 0 20px; font-size:1rem; color:#fff; }
.eco-modal .close-btn { float:right; background:none; border:none; color:#888; font-size:1.2rem; cursor:pointer; margin-top:-4px; }
.eco-modal .close-btn:hover { color:#fff; }
.form-row { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
.form-group { display:flex; flex-direction:column; gap:5px; margin-bottom:14px; }
.form-group label { font-size:.8rem; color:#888; text-transform:uppercase; letter-spacing:.04em; }
.form-group input, .form-group select {
    background:#2a2a2a; border:1px solid #444; border-radius:6px;
    color:#eee; padding:8px 10px; font-size:.875rem; width:100%; box-sizing:border-box;
}
.form-group input:focus, .form-group select:focus { outline:none; border-color:#4caf50; }
.modal-actions { display:flex; justify-content:flex-end; gap:10px; margin-top:8px; }
.btn-cancel { background:#333; color:#ccc; border:1px solid #555; border-radius:6px; padding:8px 16px; cursor:pointer; font-size:.875rem; }
.btn-cancel:hover { background:#444; }
.btn-save { background:#4caf50; color:#fff; border:none; border-radius:6px; padding:8px 20px; cursor:pointer; font-size:.875rem; font-weight:600; }
.btn-save:hover { background:#43a047; }
.eco-empty { color:#666; font-size:.875rem; padding:24px 0; text-align:center; }
</style>

<div class="container">

    <nav class="eco-tabs">
        <a href="<?= $this->url('/economia') ?>">Dashboard</a>
        <a href="<?= $this->url('/economia/gastos') ?>">Gastos</a>
        <a href="<?= $this->url('/economia/ingresos') ?>" class="active">Ingresos</a>
        <a href="<?= $this->url('/economia/deudas') ?>">Deudas trabajadores</a>
    </nav>

    <div class="eco-toolbar">
        <h2>Ingresos — <?= count($ingresos) ?> registros</h2>
        <button class="btn btn-primary" onclick="abrirModal()">+ Nuevo ingreso</button>
    </div>

    <?php if (empty($ingresos)): ?>
        <p class="eco-empty">No hay ingresos registrados. Pulsa "Nuevo ingreso" para añadir el primero.</p>
    <?php else: ?>
    <table class="eco-table">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Concepto</th>
                <th>Categoría</th>
                <th>Importe</th>
                <th>Cuenta</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($ingresos as $i): ?>
        <tr data-id="<?= $i['id'] ?>">
            <td><?= date('d/m/Y', strtotime($i['fecha'])) ?></td>
            <td><?= htmlspecialchars($i['concepto']) ?></td>
            <td><span class="badge-cat"><?= htmlspecialchars($labels[$i['categoria']] ?? $i['categoria']) ?></span></td>
            <td class="importe-ingreso">+<?= number_format($i['importe'], 2, ',', '.') ?> €</td>
            <td><span class="badge-cuenta badge-<?= $i['cuenta'] ?? 'banco' ?>"><?= ucfirst($i['cuenta'] ?? 'banco') ?></span></td>
            <td>
                <div class="actions">
                    <button class="btn-icon" onclick="editarIngreso(<?= $i['id'] ?>)" title="Editar">✏️</button>
                    <button class="btn-icon danger" onclick="eliminarIngreso(<?= $i['id'] ?>)" title="Eliminar">🗑</button>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

</div>

<!-- Modal crear/editar ingreso -->
<div class="eco-modal-overlay" id="modalOverlay">
    <div class="eco-modal">
        <button class="close-btn" onclick="cerrarModal()">✕</button>
        <h3 id="modalTitulo">Nuevo ingreso</h3>

        <form id="formIngreso">
            <input type="hidden" id="ingresoId" name="id">
            <input type="hidden" name="tipo" value="ingreso">

            <div class="form-row">
                <div class="form-group">
                    <label>Fecha</label>
                    <input type="date" name="fecha" id="ingresoFecha" required>
                </div>
                <div class="form-group">
                    <label>Importe (€)</label>
                    <input type="number" name="importe" id="ingresoImporte" step="0.01" min="0" required placeholder="0,00">
                </div>
            </div>

            <div class="form-group">
                <label>Concepto</label>
                <input type="text" name="concepto" id="ingresoConcepto" required placeholder="Descripción del ingreso">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Categoría</label>
                    <select name="categoria" id="ingresoCategoria" required>
                        <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat ?>"><?= htmlspecialchars($labels[$cat]) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Cuenta</label>
                    <select name="cuenta" id="ingresoCuenta" required>
                        <option value="banco">Banco</option>
                        <option value="efectivo">Efectivo</option>
                    </select>
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
                <button type="submit" class="btn-save">Guardar</button>
            </div>
        </form>
    </div>
</div>

<div id="toast" class="toast"></div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const BASE = window._APP_BASE_PATH ?? '';

function abrirModal(datos = null) {
    document.getElementById('modalTitulo').textContent = datos ? 'Editar ingreso' : 'Nuevo ingreso';
    document.getElementById('formIngreso').reset();
    document.getElementById('ingresoId').value    = datos?.id ?? '';
    document.getElementById('ingresoFecha').value = datos?.fecha ?? new Date().toISOString().split('T')[0];

    if (datos) {
        document.getElementById('ingresoConcepto').value  = datos.concepto  ?? '';
        document.getElementById('ingresoCategoria').value = datos.categoria ?? '';
        document.getElementById('ingresoImporte').value   = datos.importe   ?? '';
        document.getElementById('ingresoCuenta').value    = datos.cuenta    ?? 'banco';
    }

    document.getElementById('modalOverlay').classList.add('open');
}

function cerrarModal() {
    document.getElementById('modalOverlay').classList.remove('open');
}

function editarIngreso(id) {
    fetch(BASE + '/economia/obtener?id=' + id)
        .then(r => r.json())
        .then(res => { if (res.success) abrirModal(res.data); else toast('Error al cargar', 'error'); });
}

function eliminarIngreso(id) {
    if (!confirm('¿Eliminar este ingreso?')) return;
    postJson(BASE + '/economia/eliminar', { id })
        .then(res => { if (res.success) { toast(res.message); location.reload(); } else toast(res.message, 'error'); });
}

document.getElementById('formIngreso').addEventListener('submit', function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    const esEdicion = fd.get('id') !== '';
    const url = BASE + (esEdicion ? '/economia/editar' : '/economia/crear');
    fd.append('csrf_token', CSRF);

    fetch(url, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if (res.success) { toast(res.message); cerrarModal(); location.reload(); }
            else toast(res.message, 'error');
        })
        .catch(() => toast('Error de red', 'error'));
});

document.getElementById('modalOverlay').addEventListener('click', function(e) {
    if (e.target === this) cerrarModal();
});

function postJson(url, data) {
    const fd = new FormData();
    for (const k in data) fd.append(k, data[k]);
    fd.append('csrf_token', CSRF);
    return fetch(url, { method: 'POST', body: fd }).then(r => r.json());
}

function toast(msg, type = 'success') {
    const el = document.getElementById('toast');
    el.textContent = msg;
    el.className = 'toast toast-' + type + ' show';
    setTimeout(() => el.classList.remove('show'), 3000);
}
</script>
