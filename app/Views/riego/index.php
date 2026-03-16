<?php $title = 'Gestión de Riego'; ?>

<div class="container">
    <div class="page-header">
        <h2>💧 Gestión de Riego</h2>
        <button class="btn btn-primary" onclick="abrirModalNuevo()">+ Nuevo Riego</button>
    </div>

    <!-- Panel resumen -->
    <div class="resumen-riego" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:1rem; margin-bottom:1.5rem;">
        <div class="card" style="padding:1.2rem; text-align:center;">
            <div style="font-size:2rem; font-weight:700; color:#2196f3;">
                <?= number_format($resumen['total_m3'] ?? 0, 1) ?> m³
            </div>
            <div style="color:#ccc; font-size:0.9rem;">Total consumido</div>
        </div>
        <div class="card" style="padding:1.2rem; text-align:center;">
            <div style="font-size:2rem; font-weight:700; color:#4caf50;">
                <?= intval($resumen['total_riegos'] ?? 0) ?>
            </div>
            <div style="color:#ccc; font-size:0.9rem;">Riegos registrados</div>
        </div>
        <div class="card" style="padding:1.2rem; text-align:center;">
            <div style="font-size:2rem; font-weight:700; color:#ff9800;">
                <?= intval($resumen['total_dias'] ?? 0) ?>
            </div>
            <div style="color:#ccc; font-size:0.9rem;">Días de riego</div>
        </div>
    </div>

    <!-- Selector de año -->
    <div style="margin-bottom:1rem; display:flex; align-items:center; gap:0.75rem;">
        <label for="filtroAnio" style="color:#ccc; font-weight:600;">Filtrar por año:</label>
        <select id="filtroAnio" onchange="filtrarPorAnio(this.value)" style="padding:0.4rem 0.8rem; border-radius:8px; background:#2a2a2a; color:#fff; border:1px solid #404040;">
            <option value="">Todos los años</option>
            <?php foreach ($anios as $a): ?>
            <option value="<?= intval($a) ?>" <?= ($anioActual !== null && intval($a) === $anioActual) ? 'selected' : '' ?>>
                <?= intval($a) ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Tabla de riegos -->
    <div class="card">
        <table class="styled-table" id="riegosTable">
            <thead>
                <tr>
                    <th>Fecha ini</th>
                    <th>Fecha fin</th>
                    <th>Días</th>
                    <th>Parcela</th>
                    <th>Hidrante</th>
                    <th>Contador ini</th>
                    <th>Contador fin</th>
                    <th>Total m³</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($riegos as $r): ?>
                <tr id="riego-row-<?= intval($r['id']) ?>"
                    class="clickable-row"
                    onclick="editarRiego(<?= intval($r['id']) ?>)">
                    <td><?= htmlspecialchars($r['fecha_ini'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($r['fecha_fin'] ?? '—') ?></td>
                    <td><?= $r['dias'] !== null ? intval($r['dias']) : '—' ?></td>
                    <td><?= htmlspecialchars($r['parcela_nombre'] ?? $r['propiedad'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($r['hidrante'] ?? '—') ?></td>
                    <td><?= $r['cantidad_ini'] !== null ? number_format($r['cantidad_ini'], 1) : '—' ?></td>
                    <td><?= $r['cantidad_fin'] !== null ? number_format($r['cantidad_fin'], 1) : '—' ?></td>
                    <td><strong><?= $r['total_m3'] !== null ? number_format($r['total_m3'], 1) . ' m³' : '—' ?></strong></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($riegos)): ?>
                <tr><td colspan="8" class="text-center">No hay registros de riego<?= $anioActual ? ' para ' . $anioActual : '' ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal nuevo/editar riego -->
<div id="modalRiego" class="modal" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalRiegoTitle">Nuevo Riego</h3>
            <button class="modal-close" onclick="cerrarModal()">&times;</button>
        </div>
        <form id="formRiego">
            <input type="hidden" id="riego_id" name="id" value="">

            <div class="form-row">
                <div class="form-group">
                    <label for="fecha_ini">Fecha inicio: <span class="required">*</span></label>
                    <input type="date" id="fecha_ini" name="fecha_ini" required onchange="calcularDias()">
                </div>
                <div class="form-group">
                    <label for="fecha_fin">Fecha fin:</label>
                    <input type="date" id="fecha_fin" name="fecha_fin" onchange="calcularDias()">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="dias">Días:</label>
                    <input type="number" id="dias" name="dias" min="0" placeholder="Se calcula automáticamente">
                </div>
                <div class="form-group">
                    <label for="hidrante">Hidrante:</label>
                    <input type="number" id="hidrante" name="hidrante" placeholder="Nº de hidrante" min="0" oninput="autoRellenarParcela()">
                </div>
            </div>

            <div class="form-group">
                <label for="parcela_id">Parcela:</label>
                <select id="parcela_id" name="parcela_id" onchange="autoRellenarHidrante()">
                    <option value="" data-hidrante="">— Seleccionar parcela —</option>
                    <?php foreach ($parcelas as $p): ?>
                    <option value="<?= intval($p['id']) ?>" data-hidrante="<?= intval($p['hidrante']) ?>"><?= htmlspecialchars($p['nombre']) ?> (H<?= intval($p['hidrante']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="cantidad_ini">Contador inicio (m³):</label>
                    <input type="number" id="cantidad_ini" name="cantidad_ini" step="0.1" min="0" onchange="calcularM3()">
                </div>
                <div class="form-group">
                    <label for="cantidad_fin">Contador fin (m³):</label>
                    <input type="number" id="cantidad_fin" name="cantidad_fin" step="0.1" min="0" onchange="calcularM3()">
                </div>
            </div>

            <div class="form-group">
                <label>Total m³ (calculado):</label>
                <div id="totalM3Display" style="font-size: 1.4rem; font-weight: bold; color: #2563eb; padding: 0.4rem 0;">—</div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-danger-outline" id="btnEliminarRiego"
                        style="display:none; margin-right:auto;"
                        onclick="eliminarRiego(document.getElementById('riego_id').value)">
                    Eliminar
                </button>
                <button type="button" class="btn btn-secondary" onclick="cerrarModal()">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="btnGuardarRiego">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
var basePath  = window._APP_BASE_PATH || '';
var modoEdicion = false;

/* ── Auto-relleno parcela ↔ hidrante ──────────────────────────────────
   Al seleccionar una parcela → se rellena el campo hidrante.
   Al escribir un nº de hidrante → se selecciona la parcela que lo tiene. */

function autoRellenarHidrante() {
    var sel = document.getElementById('parcela_id');
    var opt = sel.options[sel.selectedIndex];
    var h   = opt ? opt.getAttribute('data-hidrante') : '';
    if (h) {
        document.getElementById('hidrante').value = h;
    }
}

function autoRellenarParcela() {
    var valor = document.getElementById('hidrante').value.trim();
    if (!valor) return;
    var sel = document.getElementById('parcela_id');
    // Buscar la opción cuyo data-hidrante coincide
    for (var i = 0; i < sel.options.length; i++) {
        if (sel.options[i].getAttribute('data-hidrante') === valor) {
            sel.selectedIndex = i;
            return;
        }
    }
    // Si no hay coincidencia, dejar sin seleccionar
    sel.selectedIndex = 0;
}

/* Filtrar por año — recarga la página con el parámetro de año */
function filtrarPorAnio(anio) {
    var url = basePath + '/datos/riego';
    if (anio) url += '?anio=' + anio;
    // location.replace evita añadir al historial cada filtro
    // y garantiza recarga aunque la URL base sea la misma
    window.location.replace(url);
}

function abrirModalNuevo() {
    modoEdicion = false;
    document.getElementById('modalRiegoTitle').textContent = 'Nuevo Riego';
    document.getElementById('formRiego').reset();
    document.getElementById('riego_id').value = '';
    document.getElementById('totalM3Display').textContent = '—';
    document.getElementById('btnEliminarRiego').style.display = 'none';
    document.getElementById('modalRiego').style.display = 'flex';
}

function cerrarModal() {
    document.getElementById('modalRiego').style.display = 'none';
}

function calcularDias() {
    var ini = document.getElementById('fecha_ini').value;
    var fin = document.getElementById('fecha_fin').value;
    if (ini && fin) {
        var d1 = new Date(ini);
        var d2 = new Date(fin);
        var diff = Math.round((d2 - d1) / (1000 * 60 * 60 * 24));
        if (diff >= 0) {
            document.getElementById('dias').value = diff;
        }
    }
}

function calcularM3() {
    var ini = parseFloat(document.getElementById('cantidad_ini').value);
    var fin = parseFloat(document.getElementById('cantidad_fin').value);
    if (!isNaN(ini) && !isNaN(fin) && fin >= ini) {
        var total = (fin - ini).toFixed(1);
        document.getElementById('totalM3Display').textContent = total + ' m³';
    } else {
        document.getElementById('totalM3Display').textContent = '—';
    }
}

function editarRiego(id) {
    fetch(basePath + '/riego/obtener?id=' + id)
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (!res.success) { showToast(res.message, 'error'); return; }
        var r = res.riego;
        modoEdicion = true;
        document.getElementById('modalRiegoTitle').textContent = 'Editar Riego';
        document.getElementById('riego_id').value   = r.id;
        document.getElementById('fecha_ini').value  = r.fecha_ini || '';
        document.getElementById('fecha_fin').value  = r.fecha_fin || '';
        document.getElementById('dias').value        = r.dias || '';
        document.getElementById('hidrante').value    = r.hidrante || '';
        document.getElementById('parcela_id').value  = r.parcela_id || '';
        document.getElementById('cantidad_ini').value = r.cantidad_ini || '';
        document.getElementById('cantidad_fin').value = r.cantidad_fin || '';
        calcularM3();
        document.getElementById('btnEliminarRiego').style.display = 'inline-block';
        document.getElementById('modalRiego').style.display = 'flex';
    })
    .catch(function() { showToast('Error de conexión', 'error'); });
}

function eliminarRiego(id) {
    if (!confirm('¿Eliminar este registro de riego?')) return;
    fetch(basePath + '/riego/eliminar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ id: id })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.success) {
            showToast('Riego eliminado', 'success');
            var row = document.getElementById('riego-row-' + id);
            if (row) row.remove();
        } else {
            showToast(res.message, 'error');
        }
    })
    .catch(function() { showToast('Error de conexión', 'error'); });
}

document.getElementById('formRiego').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = document.getElementById('btnGuardarRiego');
    btn.disabled = true;

    var id = document.getElementById('riego_id').value;
    var url = id ? basePath + '/riego/actualizar' : basePath + '/riego/crear';

    var data = {
        id:           id ? parseInt(id) : undefined,
        parcela_id:   document.getElementById('parcela_id').value || null,
        hidrante:     document.getElementById('hidrante').value,
        fecha_ini:    document.getElementById('fecha_ini').value,
        fecha_fin:    document.getElementById('fecha_fin').value || null,
        cantidad_ini: document.getElementById('cantidad_ini').value !== '' ? parseFloat(document.getElementById('cantidad_ini').value) : null,
        cantidad_fin: document.getElementById('cantidad_fin').value !== '' ? parseFloat(document.getElementById('cantidad_fin').value) : null,
        dias:         document.getElementById('dias').value !== '' ? parseInt(document.getElementById('dias').value) : null
    };

    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify(data)
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        btn.disabled = false;
        if (res.success) {
            cerrarModal();
            location.reload();
        } else {
            showToast(res.message, 'error');
        }
    })
    .catch(function() {
        btn.disabled = false;
        showToast('Error de conexión', 'error');
    });
});

// Cerrar modal al hacer clic fuera
document.getElementById('modalRiego').addEventListener('click', function(e) {
    if (e.target === this) cerrarModal();
});
</script>
