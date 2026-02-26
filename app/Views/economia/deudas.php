<?php $title = 'Economía — Deudas trabajadores'; ?>

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

.deuda-section { margin-bottom:36px; }
.deuda-section-title {
    font-size:.9rem; font-weight:600; color:#888; text-transform:uppercase;
    letter-spacing:.06em; margin-bottom:12px; border-bottom:1px solid #333; padding-bottom:8px;
    display:flex; justify-content:space-between; align-items:center;
}

.eco-table { width:100%; border-collapse:collapse; font-size:.875rem; }
.eco-table th { background:#222; color:#888; font-weight:500; text-align:left; padding:10px 12px; border-bottom:1px solid #333; font-size:.8rem; text-transform:uppercase; }
.eco-table td { padding:10px 12px; border-bottom:1px solid #2a2a2a; color:#ccc; vertical-align:middle; }
.eco-table tr:last-child td { border-bottom:none; }
.eco-table tr:hover td { background:#1e1e1e; }

.importe-deuda   { color:#f44336; font-weight:700; }
.badge-pagado    { display:inline-block; padding:2px 8px; border-radius:12px; font-size:.75rem; font-weight:600; background:#2e7d32; color:#a5d6a7; }
.badge-pendiente { display:inline-block; padding:2px 8px; border-radius:12px; font-size:.75rem; font-weight:600; background:#b71c1c; color:#ef9a9a; }

.btn-pagar { background:#1565c0; color:#90caf9; border:none; border-radius:6px; padding:4px 12px; cursor:pointer; font-size:.8rem; font-weight:600; }
.btn-pagar:hover { background:#1976d2; }

.btn-cerrar-mes { background:#f57c00; color:#fff; border:none; border-radius:6px; padding:8px 18px; cursor:pointer; font-size:.875rem; font-weight:600; }
.btn-cerrar-mes:hover { background:#ef6c00; }

.eco-empty { color:#666; font-size:.875rem; padding:16px 0; text-align:center; }

/* Modal pago */
.eco-modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.7); z-index:900; }
.eco-modal-overlay.open { display:flex; align-items:center; justify-content:center; }
.eco-modal { background:#1e1e1e; border:1px solid #333; border-radius:12px; width:100%; max-width:380px; padding:28px; }
.eco-modal h3 { margin:0 0 20px; font-size:1rem; color:#fff; }
.eco-modal .close-btn { float:right; background:none; border:none; color:#888; font-size:1.2rem; cursor:pointer; margin-top:-4px; }
.eco-modal .close-btn:hover { color:#fff; }
.form-group { display:flex; flex-direction:column; gap:5px; margin-bottom:14px; }
.form-group label { font-size:.8rem; color:#888; text-transform:uppercase; letter-spacing:.04em; }
.form-group input { background:#2a2a2a; border:1px solid #444; border-radius:6px; color:#eee; padding:8px 10px; font-size:.875rem; width:100%; box-sizing:border-box; }
.form-group input:focus { outline:none; border-color:#4caf50; }
.modal-actions { display:flex; justify-content:flex-end; gap:10px; margin-top:8px; }
.btn-cancel { background:#333; color:#ccc; border:1px solid #555; border-radius:6px; padding:8px 16px; cursor:pointer; font-size:.875rem; }
.btn-cancel:hover { background:#444; }
.btn-save { background:#1565c0; color:#fff; border:none; border-radius:6px; padding:8px 20px; cursor:pointer; font-size:.875rem; font-weight:600; }
.btn-save:hover { background:#1976d2; }

.mes-selector { display:flex; gap:8px; align-items:center; }
.mes-selector select, .mes-selector input { background:#2a2a2a; border:1px solid #444; border-radius:6px; color:#eee; padding:6px 10px; font-size:.875rem; }
</style>

<div class="container">

    <nav class="eco-tabs">
        <a href="<?= $this->url('/economia') ?>">Dashboard</a>
        <a href="<?= $this->url('/economia/gastos') ?>">Gastos</a>
        <a href="<?= $this->url('/economia/ingresos') ?>">Ingresos</a>
        <a href="<?= $this->url('/economia/deudas') ?>" class="active">Deudas trabajadores</a>
    </nav>

    <!-- Deuda mes actual -->
    <div class="deuda-section">
        <div class="deuda-section-title">
            <span>Deuda calculada — <?= strftime('%B %Y') ?? date('F Y') ?></span>
            <div class="mes-selector">
                <label style="color:#888;font-size:.8rem;">Cerrar mes:</label>
                <select id="selMes">
                    <?php
                    $meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
                    for ($m = 1; $m <= 12; $m++):
                    ?>
                    <option value="<?= $m ?>" <?= $m === $mesActual ? 'selected' : '' ?>>
                        <?= $meses[$m-1] ?>
                    </option>
                    <?php endfor; ?>
                </select>
                <input type="number" id="selAnio" value="<?= $anioActual ?>" min="2020" max="2099" style="width:80px;">
                <button class="btn-cerrar-mes" onclick="cerrarMes()">Cerrar mes</button>
            </div>
        </div>

        <?php if (empty($deudaMesActual)): ?>
            <p class="eco-empty">No hay tareas con coste calculado para el mes actual.</p>
        <?php else: ?>
        <table class="eco-table">
            <thead><tr><th>Trabajador</th><th>Deuda calculada</th><th>Estado</th></tr></thead>
            <tbody>
            <?php foreach ($deudaMesActual as $d): ?>
            <tr>
                <td><?= htmlspecialchars($d['trabajador_nombre'] . ' ' . ($d['trabajador_apellidos'] ?? '')) ?></td>
                <td class="importe-deuda"><?= number_format($d['deuda_calculada'], 2, ',', '.') ?> €</td>
                <td><span class="badge-pendiente">Pendiente de cierre</span></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <!-- Pagos mensuales pendientes -->
    <div class="deuda-section">
        <div class="deuda-section-title">Pagos pendientes (<?= count($pagosPendientes) ?>)</div>

        <?php if (empty($pagosPendientes)): ?>
            <p class="eco-empty">Sin pagos pendientes.</p>
        <?php else: ?>
        <table class="eco-table">
            <thead><tr><th>Trabajador</th><th>Mes / Año</th><th>Importe</th><th>Pagar</th></tr></thead>
            <tbody>
            <?php
            $meses = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
            foreach ($pagosPendientes as $p):
            ?>
            <tr>
                <td><?= htmlspecialchars($p['trabajador_nombre'] . ' ' . ($p['trabajador_apellidos'] ?? '')) ?></td>
                <td><?= $meses[$p['month']] ?> <?= $p['year'] ?></td>
                <td class="importe-deuda"><?= number_format($p['importe_total'], 2, ',', '.') ?> €</td>
                <td>
                    <button class="btn-pagar" onclick="abrirModalPago(<?= $p['id'] ?>, '<?= htmlspecialchars($p['trabajador_nombre']) ?>', '<?= $meses[$p['month']] . ' ' . $p['year'] ?>')">
                        Registrar pago
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <!-- Historial completo de pagos -->
    <div class="deuda-section">
        <div class="deuda-section-title">Historial de pagos (<?= count($todosLosPagos) ?>)</div>

        <?php if (empty($todosLosPagos)): ?>
            <p class="eco-empty">Sin historial de pagos.</p>
        <?php else: ?>
        <table class="eco-table">
            <thead><tr><th>Trabajador</th><th>Mes / Año</th><th>Importe</th><th>Estado</th><th>Fecha pago</th></tr></thead>
            <tbody>
            <?php foreach ($todosLosPagos as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['trabajador_nombre'] . ' ' . ($p['trabajador_apellidos'] ?? '')) ?></td>
                <td><?= $meses[$p['month']] ?> <?= $p['year'] ?></td>
                <td class="importe-deuda"><?= number_format($p['importe_total'], 2, ',', '.') ?> €</td>
                <td>
                    <?php if ($p['pagado']): ?>
                        <span class="badge-pagado">Pagado</span>
                    <?php else: ?>
                        <span class="badge-pendiente">Pendiente</span>
                    <?php endif; ?>
                </td>
                <td><?= $p['fecha_pago'] ? date('d/m/Y', strtotime($p['fecha_pago'])) : '—' ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

</div>

<!-- Modal registrar pago -->
<div class="eco-modal-overlay" id="modalPagoOverlay">
    <div class="eco-modal">
        <button class="close-btn" onclick="cerrarModalPago()">✕</button>
        <h3 id="modalPagoTitulo">Registrar pago</h3>

        <form id="formPago">
            <input type="hidden" id="pagoId" name="id">
            <div class="form-group">
                <label>Fecha de pago</label>
                <input type="date" name="fecha_pago" id="pagoFecha" required>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="cerrarModalPago()">Cancelar</button>
                <button type="submit" class="btn-save">Confirmar pago</button>
            </div>
        </form>
    </div>
</div>

<div id="toast" class="toast"></div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const BASE = window._APP_BASE_PATH ?? '';

function cerrarMes() {
    const month = document.getElementById('selMes').value;
    const year  = document.getElementById('selAnio').value;
    if (!confirm('¿Cerrar ' + document.getElementById('selMes').options[document.getElementById('selMes').selectedIndex].text + ' ' + year + '?\nSe generarán los registros de deuda por trabajador.')) return;

    postJson(BASE + '/economia/cerrarMes', { month, year })
        .then(res => {
            if (res.success) { toast(res.message); setTimeout(() => location.reload(), 1200); }
            else toast(res.message, 'error');
        })
        .catch(() => toast('Error de red', 'error'));
}

function abrirModalPago(id, nombre, periodo) {
    document.getElementById('modalPagoTitulo').textContent = 'Pagar a ' + nombre + ' — ' + periodo;
    document.getElementById('pagoId').value    = id;
    document.getElementById('pagoFecha').value = new Date().toISOString().split('T')[0];
    document.getElementById('modalPagoOverlay').classList.add('open');
}

function cerrarModalPago() {
    document.getElementById('modalPagoOverlay').classList.remove('open');
}

document.getElementById('formPago').addEventListener('submit', function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    fd.append('csrf_token', CSRF);
    fetch(BASE + '/economia/registrarPago', { method:'POST', body:fd })
        .then(r => r.json())
        .then(res => {
            if (res.success) { toast(res.message); cerrarModalPago(); setTimeout(() => location.reload(), 1200); }
            else toast(res.message, 'error');
        })
        .catch(() => toast('Error de red', 'error'));
});

document.getElementById('modalPagoOverlay').addEventListener('click', function(e) {
    if (e.target === this) cerrarModalPago();
});

function postJson(url, data) {
    const fd = new FormData();
    for (const k in data) fd.append(k, data[k]);
    fd.append('csrf_token', CSRF);
    return fetch(url, { method:'POST', body:fd }).then(r => r.json());
}

function toast(msg, type = 'success') {
    const el = document.getElementById('toast');
    el.textContent = msg;
    el.className = 'toast toast-' + type + ' show';
    setTimeout(() => el.classList.remove('show'), 3500);
}
</script>
