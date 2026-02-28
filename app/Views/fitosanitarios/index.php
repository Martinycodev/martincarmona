<?php $title = 'Fitosanitarios'; ?>
<div class="container">
    <div class="page-header">
        <h2>💊 Fitosanitarios</h2>
    </div>

    <!-- Pestañas -->
    <div style="display:flex; gap:.5rem; margin-bottom:1rem; border-bottom:2px solid var(--border-color, #e5e7eb);">
        <button id="tabInventarioBtn" class="tab-btn tab-active" onclick="mostrarTab('inventario')">📦 Inventario</button>
        <button id="tabAplicacionesBtn" class="tab-btn" onclick="mostrarTab('aplicaciones')">📋 Aplicaciones</button>
    </div>

    <!-- TAB: Inventario -->
    <div id="tabInventario">
        <div style="display:flex; justify-content:flex-end; margin-bottom:.75rem;">
            <button class="btn btn-primary" onclick="abrirModalInv()">+ Añadir producto</button>
        </div>
        <div class="card">
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Fecha compra</th>
                        <th>Cantidad</th>
                        <th>Unidad</th>
                        <th>Proveedor</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inventario as $item): ?>
                    <tr id="inv-row-<?= intval($item['id']) ?>">
                        <td><strong><?= htmlspecialchars($item['producto']) ?></strong></td>
                        <td><?= htmlspecialchars($item['fecha_compra'] ?? '—') ?></td>
                        <td><?= $item['cantidad'] !== null ? number_format($item['cantidad'], 2, ',', '.') : '—' ?></td>
                        <td><?= htmlspecialchars($item['unidad'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($item['proveedor_nombre'] ?? '—') ?></td>
                        <td>
                            <button class="btn btn-secondary btn-sm" onclick="editarInv(<?= htmlspecialchars(json_encode($item), ENT_QUOTES) ?>)">Editar</button>
                            <button class="btn btn-danger btn-sm" onclick="eliminarInv(<?= intval($item['id']) ?>)">Eliminar</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($inventario)): ?>
                    <tr><td colspan="6" style="text-align:center; color:#6b7280; padding:2rem;">Sin productos en inventario.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB: Aplicaciones -->
    <div id="tabAplicaciones" style="display:none;">
        <div style="display:flex; justify-content:flex-end; margin-bottom:.75rem;">
            <button class="btn btn-primary" onclick="abrirModalApl()">+ Añadir aplicación</button>
        </div>
        <div class="card">
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Producto</th>
                        <th>Parcela</th>
                        <th>Cantidad</th>
                        <th>Origen</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($aplicaciones as $ap): ?>
                    <tr id="apl-row-<?= intval($ap['id']) ?>">
                        <td><?= htmlspecialchars($ap['fecha']) ?></td>
                        <td><strong><?= htmlspecialchars($ap['producto']) ?></strong></td>
                        <td><?= htmlspecialchars($ap['parcela_nombre'] ?? '—') ?></td>
                        <td><?= $ap['cantidad'] !== null ? number_format($ap['cantidad'], 2, ',', '.') : '—' ?></td>
                        <td>
                            <?php if ($ap['tarea_id']): ?>
                                <span style="color:#6b7280; font-size:.8rem;">🔗 Auto (tarea #<?= intval($ap['tarea_id']) ?>)</span>
                            <?php else: ?>
                                <span style="color:#6b7280; font-size:.8rem;">Manual</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!$ap['tarea_id']): ?>
                            <button class="btn btn-danger btn-sm" onclick="eliminarApl(<?= intval($ap['id']) ?>)">Eliminar</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($aplicaciones)): ?>
                    <tr><td colspan="6" style="text-align:center; color:#6b7280; padding:2rem;">Sin aplicaciones registradas.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal inventario -->
<div id="modalInv" class="modal" style="display:none;">
    <div class="modal-content" style="max-width:480px;">
        <div class="modal-header">
            <h3 id="modalInvTitle">Añadir producto</h3>
            <button class="modal-close" onclick="cerrarModalInv()">&times;</button>
        </div>
        <form id="formInv" style="padding:1rem 1.5rem 1.5rem;">
            <input type="hidden" id="inv_id" value="0">
            <div class="form-group">
                <label>Producto <span class="required">*</span></label>
                <input type="text" id="inv_producto" placeholder="Ej. Sulfato de cobre" required>
            </div>
            <div class="form-group">
                <label>Fecha de compra</label>
                <input type="date" id="inv_fecha_compra">
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div class="form-group">
                    <label>Cantidad</label>
                    <input type="number" id="inv_cantidad" step="0.01" min="0" placeholder="0.00">
                </div>
                <div class="form-group">
                    <label>Unidad</label>
                    <select id="inv_unidad">
                        <option value="">—</option>
                        <option value="litros">Litros</option>
                        <option value="kg">Kilogramos</option>
                        <option value="g">Gramos</option>
                        <option value="unidades">Unidades</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Proveedor</label>
                <select id="inv_proveedor_id">
                    <option value="">— Sin proveedor —</option>
                    <?php foreach ($proveedores as $p): ?>
                    <option value="<?= intval($p['id']) ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="cerrarModalInv()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal aplicación manual -->
<div id="modalApl" class="modal" style="display:none;">
    <div class="modal-content" style="max-width:460px;">
        <div class="modal-header">
            <h3>Registrar aplicación</h3>
            <button class="modal-close" onclick="cerrarModalApl()">&times;</button>
        </div>
        <form id="formApl" style="padding:1rem 1.5rem 1.5rem;">
            <div class="form-group">
                <label>Producto <span class="required">*</span></label>
                <input type="text" id="apl_producto" placeholder="Ej. Herbicida Roundup" required list="productosDatalist">
                <datalist id="productosDatalist">
                    <?php foreach ($productos as $p): ?>
                    <option value="<?= htmlspecialchars($p) ?>">
                    <?php endforeach; ?>
                </datalist>
            </div>
            <div class="form-group">
                <label>Fecha <span class="required">*</span></label>
                <input type="date" id="apl_fecha" required>
            </div>
            <div class="form-group">
                <label>Parcela</label>
                <select id="apl_parcela_id">
                    <option value="">— Sin parcela —</option>
                    <?php foreach ($parcelas as $p): ?>
                    <option value="<?= intval($p['id']) ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Cantidad aplicada</label>
                <input type="number" id="apl_cantidad" step="0.01" min="0" placeholder="0.00">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="cerrarModalApl()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

<style>
.tab-btn { background:none; border:none; padding:.5rem 1rem; cursor:pointer; color:#6b7280; font-size:.95rem; border-bottom:3px solid transparent; margin-bottom:-2px; }
.tab-active { color:var(--primary-color, #4ade80); border-bottom-color:var(--primary-color, #4ade80); font-weight:600; }
</style>

<script>
var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
var basePath  = window._APP_BASE_PATH || '';

function mostrarTab(tab) {
    document.getElementById('tabInventario').style.display   = tab === 'inventario'   ? '' : 'none';
    document.getElementById('tabAplicaciones').style.display = tab === 'aplicaciones' ? '' : 'none';
    document.getElementById('tabInventarioBtn').classList.toggle('tab-active',   tab === 'inventario');
    document.getElementById('tabAplicacionesBtn').classList.toggle('tab-active', tab === 'aplicaciones');
}

// ── Inventario ────────────────────────────────────────────────────────────
function abrirModalInv(item) {
    document.getElementById('modalInvTitle').textContent = item ? 'Editar producto' : 'Añadir producto';
    document.getElementById('inv_id').value          = item ? item.id : 0;
    document.getElementById('inv_producto').value    = item ? item.producto : '';
    document.getElementById('inv_fecha_compra').value = item ? (item.fecha_compra || '') : '';
    document.getElementById('inv_cantidad').value    = item ? (item.cantidad || '') : '';
    document.getElementById('inv_unidad').value      = item ? (item.unidad || '') : '';
    document.getElementById('inv_proveedor_id').value = item ? (item.proveedor_id || '') : '';
    document.getElementById('modalInv').style.display = 'flex';
    document.getElementById('inv_producto').focus();
}
function cerrarModalInv() {
    document.getElementById('modalInv').style.display = 'none';
    document.getElementById('formInv').reset();
    document.getElementById('inv_id').value = 0;
}
function editarInv(item) { abrirModalInv(item); }

document.getElementById('formInv').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = this.querySelector('[type=submit]');
    btn.disabled = true;
    var id = parseInt(document.getElementById('inv_id').value);
    var url = id > 0 ? '/fitosanitarios/actualizarInventario' : '/fitosanitarios/crearInventario';

    var payload = {
        producto:     document.getElementById('inv_producto').value,
        fecha_compra: document.getElementById('inv_fecha_compra').value || null,
        cantidad:     document.getElementById('inv_cantidad').value !== '' ? parseFloat(document.getElementById('inv_cantidad').value) : null,
        unidad:       document.getElementById('inv_unidad').value || null,
        proveedor_id: document.getElementById('inv_proveedor_id').value || null
    };
    if (id > 0) payload.id = id;

    fetch(basePath + url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify(payload)
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        btn.disabled = false;
        if (res.success) { cerrarModalInv(); location.reload(); }
        else alert('Error: ' + (res.message || 'Error desconocido'));
    })
    .catch(function() { btn.disabled = false; alert('Error de conexión'); });
});

function eliminarInv(id) {
    if (!confirm('¿Eliminar este producto del inventario?')) return;
    fetch(basePath + '/fitosanitarios/eliminarInventario', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ id: id })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.success) { var row = document.getElementById('inv-row-' + id); if (row) row.remove(); }
        else alert('Error: ' + res.message);
    })
    .catch(function() { alert('Error de conexión'); });
}

// ── Aplicaciones ──────────────────────────────────────────────────────────
function abrirModalApl() {
    document.getElementById('apl_fecha').value = new Date().toISOString().split('T')[0];
    document.getElementById('modalApl').style.display = 'flex';
    document.getElementById('apl_producto').focus();
}
function cerrarModalApl() {
    document.getElementById('modalApl').style.display = 'none';
    document.getElementById('formApl').reset();
}

document.getElementById('formApl').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = this.querySelector('[type=submit]');
    btn.disabled = true;

    fetch(basePath + '/fitosanitarios/crearAplicacion', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({
            producto:   document.getElementById('apl_producto').value,
            fecha:      document.getElementById('apl_fecha').value,
            parcela_id: document.getElementById('apl_parcela_id').value || null,
            cantidad:   document.getElementById('apl_cantidad').value !== '' ? parseFloat(document.getElementById('apl_cantidad').value) : null
        })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        btn.disabled = false;
        if (res.success) { cerrarModalApl(); location.reload(); }
        else alert('Error: ' + (res.message || 'Error desconocido'));
    })
    .catch(function() { btn.disabled = false; alert('Error de conexión'); });
});

function eliminarApl(id) {
    if (!confirm('¿Eliminar esta aplicación?')) return;
    fetch(basePath + '/fitosanitarios/eliminarAplicacion', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ id: id })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.success) { var row = document.getElementById('apl-row-' + id); if (row) row.remove(); }
        else alert('Error: ' + res.message);
    })
    .catch(function() { alert('Error de conexión'); });
}

document.getElementById('modalInv').addEventListener('click', function(e) { if (e.target === this) cerrarModalInv(); });
document.getElementById('modalApl').addEventListener('click', function(e) { if (e.target === this) cerrarModalApl(); });
</script>
