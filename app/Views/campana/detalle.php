<?php
$title = 'Campaña ' . htmlspecialchars($campana['nombre']);
$cerrada = !$campana['activa'];
$totalKilos     = array_sum(array_column($registros, 'kilos'));
$totalBeneficio = array_sum(array_column($registros, 'beneficio'));
?>
<div class="container">

    <!-- Cabecera -->
    <div class="page-header">
        <div>
            <h2>🫒 Campaña <?= htmlspecialchars($campana['nombre']) ?>
                <?php if ($cerrada): ?>
                    <span style="font-size:.8rem; color:#6b7280; font-weight:400;">✓ Cerrada</span>
                <?php else: ?>
                    <span style="font-size:.8rem; color:#16a34a; font-weight:400;">● Activa</span>
                <?php endif; ?>
            </h2>
            <small style="color:#6b7280;">
                Inicio: <?= htmlspecialchars($campana['fecha_inicio']) ?>
                <?php if ($campana['fecha_fin']): ?> — Fin: <?= htmlspecialchars($campana['fecha_fin']) ?><?php endif; ?>
                <?php if ($campana['precio_venta']): ?> · Precio aceite: <?= number_format($campana['precio_venta'], 4, ',', '.') ?> €/kg<?php endif; ?>
            </small>
        </div>
        <div style="display:flex; gap:.5rem; flex-wrap:wrap;">
            <?php if (!$cerrada): ?>
                <button class="btn btn-primary" onclick="abrirModalRegistro()">+ Añadir registro</button>
                <button class="btn btn-secondary" onclick="abrirModalCerrar()">🔒 Cerrar campaña</button>
            <?php endif; ?>
            <a href="<?= $this->url('/campana') ?>" class="btn btn-secondary">← Volver</a>
        </div>
    </div>

    <!-- Tabla de registros -->
    <div class="card">
        <h3 style="padding:1rem 1.5rem 0; margin:0;">Registros de recolección</h3>
        <table class="styled-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Parcela</th>
                    <th>Kilos</th>
                    <th>Rendimiento %</th>
                    <th>Beneficio</th>
                    <?php if (!$cerrada): ?><th>Acciones</th><?php endif; ?>
                </tr>
            </thead>
            <tbody id="tablaRegistros">
                <?php foreach ($registros as $r): ?>
                <tr id="registro-row-<?= intval($r['id']) ?>">
                    <td><?= htmlspecialchars($r['fecha']) ?></td>
                    <td><?= htmlspecialchars($r['parcela_nombre'] ?? '—') ?></td>
                    <td><?= number_format($r['kilos'], 2, ',', '.') ?> kg</td>
                    <td><?= $r['rendimiento_pct'] !== null ? number_format($r['rendimiento_pct'], 1, ',', '.') . ' %' : '—' ?></td>
                    <td><?= $r['beneficio'] !== null ? number_format($r['beneficio'], 2, ',', '.') . ' €' : '—' ?></td>
                    <?php if (!$cerrada): ?>
                    <td>
                        <button class="btn btn-secondary btn-sm" onclick="editarRegistro(<?= htmlspecialchars(json_encode($r), ENT_QUOTES) ?>)">Editar</button>
                        <button class="btn btn-danger btn-sm" onclick="eliminarRegistro(<?= intval($r['id']) ?>)">Eliminar</button>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($registros)): ?>
                <tr><td colspan="<?= $cerrada ? 5 : 6 ?>" style="text-align:center; color:#6b7280; padding:1.5rem;">Sin registros aún.</td></tr>
                <?php endif; ?>
            </tbody>
            <?php if (!empty($registros)): ?>
            <tfoot>
                <tr style="font-weight:700; background:var(--bg-secondary, #f3f4f6);">
                    <td colspan="2">TOTAL</td>
                    <td><?= number_format($totalKilos, 2, ',', '.') ?> kg</td>
                    <td>—</td>
                    <td><?= $totalBeneficio > 0 ? number_format($totalBeneficio, 2, ',', '.') . ' €' : '—' ?></td>
                    <?php if (!$cerrada): ?><td></td><?php endif; ?>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>

    <!-- Reporte por parcela (solo si hay datos) -->
    <?php if (!empty($reporte)): ?>
    <div class="card" style="margin-top:1.5rem;">
        <h3 style="padding:1rem 1.5rem 0; margin:0;">📊 Reporte por parcela</h3>
        <table class="styled-table">
            <thead>
                <tr>
                    <th>Parcela</th>
                    <th>Kilos recogidos</th>
                    <th>Rend. medio</th>
                    <th>Beneficio aceite</th>
                    <th>Coste producción</th>
                    <th>Margen neto</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reporte as $rep): ?>
                <?php $margenColor = $rep['margen'] >= 0 ? '#16a34a' : '#dc2626'; ?>
                <tr>
                    <td><?= htmlspecialchars($rep['parcela_nombre'] ?? '—') ?></td>
                    <td><?= number_format($rep['total_kilos'], 0, ',', '.') ?> kg</td>
                    <td><?= $rep['avg_rendimiento'] !== null ? number_format($rep['avg_rendimiento'], 1, ',', '.') . ' %' : '—' ?></td>
                    <td><?= $rep['total_beneficio'] !== null ? number_format($rep['total_beneficio'], 2, ',', '.') . ' €' : '—' ?></td>
                    <td><?= number_format($rep['coste_produccion'], 2, ',', '.') ?> €</td>
                    <td style="font-weight:700; color:<?= $margenColor ?>;"><?= number_format($rep['margen'], 2, ',', '.') ?> €</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if (!$campana['precio_venta']): ?>
        <p style="padding:.5rem 1.5rem 1rem; color:#92400e; font-size:.875rem;">⚠️ El beneficio se calcula al cerrar la campaña con el precio de venta.</p>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>

<!-- Modal añadir/editar registro -->
<div id="modalRegistro" class="modal" style="display:none;">
    <div class="modal-content" style="max-width:480px;">
        <div class="modal-header">
            <h3 id="modalRegistroTitle">Añadir registro</h3>
            <button class="modal-close" onclick="cerrarModalRegistro()">&times;</button>
        </div>
        <form id="formRegistro" style="padding:1rem 1.5rem 1.5rem;">
            <input type="hidden" id="reg_id" value="0">
            <div class="form-group">
                <label>Fecha <span class="required">*</span></label>
                <input type="date" id="reg_fecha" required>
            </div>
            <div class="form-group">
                <label>Parcela</label>
                <select id="reg_parcela_id">
                    <option value="">— Sin parcela —</option>
                    <?php foreach ($parcelas as $p): ?>
                    <option value="<?= intval($p['id']) ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Kilos recolectados <span class="required">*</span></label>
                <input type="number" id="reg_kilos" step="0.01" min="0" placeholder="0.00" required>
            </div>
            <div class="form-group">
                <label>Rendimiento % <small style="color:#6b7280;">(aceite/oliva, se puede completar después)</small></label>
                <input type="number" id="reg_rendimiento" step="0.01" min="0" max="100" placeholder="Ej. 20.5">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="cerrarModalRegistro()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal cerrar campaña -->
<div id="modalCerrar" class="modal" style="display:none;">
    <div class="modal-content" style="max-width:420px;">
        <div class="modal-header">
            <h3>🔒 Cerrar campaña</h3>
            <button class="modal-close" onclick="cerrarModalCerrar()">&times;</button>
        </div>
        <form id="formCerrar" style="padding:1rem 1.5rem 1.5rem;">
            <p style="color:#374151; margin-bottom:1rem;">Al cerrar la campaña se calculará el beneficio de cada registro según el precio de venta indicado.</p>
            <div class="form-group">
                <label>Fecha de cierre <span class="required">*</span></label>
                <input type="date" id="cerrar_fecha_fin" required>
            </div>
            <div class="form-group">
                <label>Precio de venta aceite (€/kg) <span class="required">*</span></label>
                <input type="number" id="cerrar_precio" step="0.0001" min="0" placeholder="Ej. 3.5000" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="cerrarModalCerrar()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Confirmar cierre</button>
            </div>
        </form>
    </div>
</div>

<script>
var csrfToken  = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
var basePath   = window._APP_BASE_PATH || '';
var campanaId  = <?= intval($campana['id']) ?>;

// ── Modal registro ────────────────────────────────────────────────────────
function abrirModalRegistro(registro) {
    document.getElementById('modalRegistroTitle').textContent = registro ? 'Editar registro' : 'Añadir registro';
    document.getElementById('reg_id').value          = registro ? registro.id : 0;
    document.getElementById('reg_fecha').value       = registro ? registro.fecha : new Date().toISOString().split('T')[0];
    document.getElementById('reg_parcela_id').value  = registro ? (registro.parcela_id || '') : '';
    document.getElementById('reg_kilos').value       = registro ? registro.kilos : '';
    document.getElementById('reg_rendimiento').value = registro ? (registro.rendimiento_pct || '') : '';
    document.getElementById('modalRegistro').style.display = 'flex';
    document.getElementById('reg_fecha').focus();
}
function cerrarModalRegistro() {
    document.getElementById('modalRegistro').style.display = 'none';
    document.getElementById('formRegistro').reset();
    document.getElementById('reg_id').value = 0;
}
function editarRegistro(registro) { abrirModalRegistro(registro); }

document.getElementById('formRegistro').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = this.querySelector('[type=submit]');
    btn.disabled = true;
    var id = parseInt(document.getElementById('reg_id').value);
    var url = id > 0 ? '/campana/actualizarRegistro' : '/campana/crearRegistro';

    var payload = {
        campana_id:      campanaId,
        parcela_id:      document.getElementById('reg_parcela_id').value || null,
        fecha:           document.getElementById('reg_fecha').value,
        kilos:           parseFloat(document.getElementById('reg_kilos').value),
        rendimiento_pct: document.getElementById('reg_rendimiento').value !== '' ? parseFloat(document.getElementById('reg_rendimiento').value) : null
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
        if (res.success) {
            cerrarModalRegistro();
            location.reload();
        } else {
            showToast(res.message || 'Error desconocido', 'error');
        }
    })
    .catch(function() { btn.disabled = false; showToast('Error de conexión', 'error'); });
});

function eliminarRegistro(id) {
    if (!confirm('¿Eliminar este registro?')) return;
    fetch(basePath + '/campana/eliminarRegistro', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ id: id })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.success) {
            var row = document.getElementById('registro-row-' + id);
            if (row) row.remove();
        } else {
            showToast(res.message, 'error');
        }
    })
    .catch(function() { showToast('Error de conexión', 'error'); });
}

// ── Modal cerrar campaña ──────────────────────────────────────────────────
function abrirModalCerrar() {
    document.getElementById('cerrar_fecha_fin').value = new Date().toISOString().split('T')[0];
    document.getElementById('modalCerrar').style.display = 'flex';
    document.getElementById('cerrar_precio').focus();
}
function cerrarModalCerrar() {
    document.getElementById('modalCerrar').style.display = 'none';
    document.getElementById('formCerrar').reset();
}

document.getElementById('formCerrar').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = this.querySelector('[type=submit]');
    btn.disabled = true;

    fetch(basePath + '/campana/cerrar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({
            id:           campanaId,
            precio_venta: parseFloat(document.getElementById('cerrar_precio').value),
            fecha_fin:    document.getElementById('cerrar_fecha_fin').value
        })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        btn.disabled = false;
        if (res.success) location.reload();
        else showToast(res.message, 'error');
    })
    .catch(function() { btn.disabled = false; showToast('Error de conexión', 'error'); });
});

document.getElementById('modalRegistro').addEventListener('click', function(e) { if (e.target === this) cerrarModalRegistro(); });
document.getElementById('modalCerrar').addEventListener('click', function(e)   { if (e.target === this) cerrarModalCerrar(); });
</script>
