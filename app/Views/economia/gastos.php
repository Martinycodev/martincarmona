<?php $title = 'Economía — Gastos'; ?>

<style>
/* ── Tabs (compartido) ── */
.eco-tabs { display:flex; gap:0; border-bottom:2px solid #333; margin-bottom:24px; }
.eco-tabs a {
    padding:10px 20px; text-decoration:none; color:#aaa; font-size:.9rem;
    border-bottom:3px solid transparent; margin-bottom:-2px; transition:color .2s;
}
.eco-tabs a.active { color:#fff; border-bottom-color:#4caf50; }
.eco-tabs a:hover  { color:#fff; }

/* ── Toolbar ── */
.eco-toolbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px; }
.eco-toolbar h2 { margin:0; font-size:1.1rem; color:#ccc; font-weight:600; }

/* ── Tabla ── */
.eco-table { width:100%; border-collapse:collapse; font-size:.875rem; }
.eco-table th { background:#222; color:#888; font-weight:500; text-align:left; padding:10px 12px; border-bottom:1px solid #333; font-size:.8rem; text-transform:uppercase; }
.eco-table td { padding:10px 12px; border-bottom:1px solid #2a2a2a; color:#ccc; vertical-align:middle; }
.eco-table tr:last-child td { border-bottom:none; }
.eco-table tr:hover td { background:#1e1e1e; }
.eco-table .actions { display:flex; gap:6px; }

/* ── Badges ── */
.badge-cuenta { display:inline-block; padding:2px 8px; border-radius:12px; font-size:.75rem; font-weight:600; }
.badge-banco    { background:#1565c0; color:#90caf9; }
.badge-efectivo { background:#2e7d32; color:#a5d6a7; }
.badge-cat { display:inline-block; padding:2px 8px; border-radius:12px; font-size:.75rem; background:#333; color:#bbb; }
.importe-gasto { color:#f44336; font-weight:600; }

/* ── Botones ── */
.btn-icon { background:none; border:1px solid #444; border-radius:6px; padding:4px 9px; cursor:pointer; color:#aaa; font-size:.85rem; transition:all .15s; }
.btn-icon:hover { background:#333; color:#fff; border-color:#666; }
.btn-icon.danger:hover { background:#b71c1c; border-color:#f44336; color:#fff; }

/* ── Modal ── */
.eco-modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.7); z-index:900; }
.eco-modal-overlay.open { display:flex; align-items:center; justify-content:center; }
.eco-modal { background:#1e1e1e; border:1px solid #333; border-radius:12px; width:100%; max-width:520px; padding:28px; }
.eco-modal h3 { margin:0 0 20px; font-size:1rem; color:#fff; }
.eco-modal .close-btn { float:right; background:none; border:none; color:#888; font-size:1.2rem; cursor:pointer; margin-top:-4px; }
.eco-modal .close-btn:hover { color:#fff; }
.form-row { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
.form-group { display:flex; flex-direction:column; gap:5px; margin-bottom:14px; }
.form-group label { font-size:.8rem; color:#888; text-transform:uppercase; letter-spacing:.04em; }
.form-group input, .form-group select, .form-group textarea {
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
        <a href="<?= $this->url('/economia/gastos') ?>" class="active">Gastos</a>
        <a href="<?= $this->url('/economia/ingresos') ?>">Ingresos</a>
        <a href="<?= $this->url('/economia/deudas') ?>">Deudas trabajadores</a>
    </nav>

    <div class="eco-toolbar">
        <h2>Gastos — <?= count($gastos) ?> registros</h2>
        <button class="btn btn-primary" onclick="abrirModal()">+ Nuevo gasto</button>
    </div>

    <?php if (empty($gastos)): ?>
        <p class="eco-empty">No hay gastos registrados. Pulsa "Nuevo gasto" para añadir el primero.</p>
    <?php else: ?>
    <table class="eco-table" id="tablaGastos">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Concepto</th>
                <th>Categoría</th>
                <th>Importe</th>
                <th>Cuenta</th>
                <th>Relacionado</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($gastos as $g): ?>
        <tr data-id="<?= $g['id'] ?>">
            <td><?= date('d/m/Y', strtotime($g['fecha'])) ?></td>
            <td><?= htmlspecialchars($g['concepto']) ?></td>
            <td><span class="badge-cat"><?= htmlspecialchars($labels[$g['categoria']] ?? $g['categoria']) ?></span></td>
            <td class="importe-gasto">−<?= number_format($g['importe'], 2, ',', '.') ?> €</td>
            <td><span class="badge-cuenta badge-<?= $g['cuenta'] ?? 'banco' ?>"><?= ucfirst($g['cuenta'] ?? 'banco') ?></span></td>
            <td>
                <?php
                $rel = [];
                if ($g['proveedor_nombre']) $rel[] = $g['proveedor_nombre'];
                if ($g['vehiculo_nombre'])  $rel[] = $g['vehiculo_nombre'];
                if ($g['parcela_nombre'])   $rel[] = $g['parcela_nombre'];
                echo $rel ? htmlspecialchars(implode(', ', $rel)) : '—';
                ?>
            </td>
            <td>
                <div class="actions">
                    <button class="btn-icon" onclick="editarGasto(<?= $g['id'] ?>)" title="Editar">✏️</button>
                    <button class="btn-icon danger" onclick="eliminarGasto(<?= $g['id'] ?>)" title="Eliminar">🗑</button>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

</div>

<!-- Modal crear/editar gasto -->
<div class="eco-modal-overlay" id="modalOverlay">
    <div class="eco-modal">
        <button class="close-btn" onclick="cerrarModal()">✕</button>
        <h3 id="modalTitulo">Nuevo gasto</h3>

        <form id="formGasto">
            <input type="hidden" id="gastoId" name="id">
            <input type="hidden" name="tipo" value="gasto">

            <div class="form-row">
                <div class="form-group">
                    <label>Fecha</label>
                    <input type="date" name="fecha" id="gastoFecha" required>
                </div>
                <div class="form-group">
                    <label>Importe (€)</label>
                    <input type="number" name="importe" id="gastoImporte" step="0.01" min="0" required placeholder="0,00">
                </div>
            </div>

            <div class="form-group">
                <label>Concepto</label>
                <input type="text" name="concepto" id="gastoConcepto" required placeholder="Descripción del gasto">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Categoría</label>
                    <select name="categoria" id="gastoCategoria" required>
                        <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat ?>"><?= htmlspecialchars($labels[$cat]) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Cuenta</label>
                    <select name="cuenta" id="gastoCuenta" required>
                        <option value="banco">Banco</option>
                        <option value="efectivo">Efectivo</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Proveedor (opcional)</label>
                    <select name="proveedor_id" id="gastoProveedor">
                        <option value="">— ninguno —</option>
                        <?php foreach ($proveedores as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Vehículo (opcional)</label>
                    <select name="vehiculo_id" id="gastoVehiculo">
                        <option value="">— ninguno —</option>
                        <?php foreach ($vehiculos as $v): ?>
                        <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['nombre'] . ' (' . $v['matricula'] . ')') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Parcela (opcional)</label>
                <select name="parcela_id" id="gastoParcela">
                    <option value="">— ninguna —</option>
                    <?php foreach ($parcelas as $par): ?>
                    <option value="<?= $par['id'] ?>"><?= htmlspecialchars($par['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
                <button type="submit" class="btn-save" id="btnGuardar">Guardar</button>
            </div>
        </form>
    </div>
</div>

<div id="toast" class="toast"></div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const BASE = window._APP_BASE_PATH ?? '';

function abrirModal(datos = null) {
    document.getElementById('modalTitulo').textContent = datos ? 'Editar gasto' : 'Nuevo gasto';
    document.getElementById('formGasto').reset();
    document.getElementById('gastoId').value    = dados?.id ?? '';
    document.getElementById('gastoFecha').value = dados?.fecha ?? new Date().toISOString().split('T')[0];

    if (datos) {
        document.getElementById('gastoConcepto').value  = dados.concepto  ?? '';
        document.getElementById('gastoCategoria').value = dados.categoria ?? '';
        document.getElementById('gastoImporte').value   = dados.importe   ?? '';
        document.getElementById('gastoCuenta').value    = dados.cuenta    ?? 'banco';
        document.getElementById('gastoProveedor').value = dados.proveedor_id ?? '';
        document.getElementById('gastoVehiculo').value  = dados.vehiculo_id  ?? '';
        document.getElementById('gastoParcela').value   = dados.parcela_id   ?? '';
    }

    document.getElementById('modalOverlay').classList.add('open');
}

// alias para usar desde botón
var dados = null;

function cerrarModal() {
    document.getElementById('modalOverlay').classList.remove('open');
}

function editarGasto(id) {
    fetch(BASE + '/economia/obtener?id=' + id)
        .then(r => r.json())
        .then(res => { if (res.success) abrirModal(res.data); else toast('Error al cargar', 'error'); });
}

function eliminarGasto(id) {
    if (!confirm('¿Eliminar este gasto?')) return;
    postJson(BASE + '/economia/eliminar', { id })
        .then(res => { if (res.success) { toast(res.message); location.reload(); } else toast(res.message, 'error'); });
}

document.getElementById('formGasto').addEventListener('submit', function(e) {
    e.preventDefault();
    const fd   = new FormData(this);
    const esEdicion = fd.get('id') !== '';
    const url  = BASE + (esEdicion ? '/economia/editar' : '/economia/crear');
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
