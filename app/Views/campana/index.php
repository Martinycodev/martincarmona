<?php $title = 'Campañas de Recolección'; ?>
<div class="container">
    <div class="page-header">
        <h2>🫒 Campañas</h2>
        <button class="btn btn-primary" onclick="abrirModalNueva()">+ Nueva campaña</button>
    </div>

    <div class="card">
        <table class="styled-table">
            <thead>
                <tr>
                    <th>Campaña</th>
                    <th>Inicio</th>
                    <th>Fin</th>
                    <th>Estado</th>
                    <th>Registros</th>
                    <th>Total kg</th>
                    <th>Beneficio</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($campanas as $c): ?>
                <tr class="clickable-row" onclick="window.location.href='<?= $this->url('/campana/detalle?id=' . intval($c['id'])) ?>'">
                    <td><strong><?= htmlspecialchars($c['nombre']) ?></strong></td>
                    <td><?= htmlspecialchars($c['fecha_inicio']) ?></td>
                    <td><?= $c['fecha_fin'] ? htmlspecialchars($c['fecha_fin']) : '—' ?></td>
                    <td>
                        <?php if ($c['activa']): ?>
                            <span style="color:#16a34a; font-weight:600;">● Activa</span>
                        <?php else: ?>
                            <span style="color:#6b7280;">✓ Cerrada</span>
                        <?php endif; ?>
                    </td>
                    <td><?= intval($c['num_registros']) ?></td>
                    <td><?= number_format($c['total_kilos'], 0, ',', '.') ?> kg</td>
                    <td>
                        <?php if ($c['total_beneficio'] > 0): ?>
                            <strong style="color:#16a34a;"><?= number_format($c['total_beneficio'], 2, ',', '.') ?> €</strong>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($campanas)): ?>
                <tr><td colspan="7" style="text-align:center; color:#6b7280; padding:2rem;">No hay campañas. Crea la primera para empezar.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

<!-- Modal nueva campaña -->
<div id="modalNueva" class="modal" style="display:none;">
    <div class="modal-content" style="max-width:420px;">
        <div class="modal-header">
            <h3>🫒 Nueva campaña</h3>
            <button class="modal-close" onclick="cerrarModalNueva()">&times;</button>
        </div>
        <form id="formNuevaCampana" style="padding:1rem 1.5rem 1.5rem;">
            <div class="form-group">
                <label>Nombre <span class="required">*</span></label>
                <input type="text" id="nueva_nombre" placeholder="Ej. 25/26" required>
            </div>
            <div class="form-group">
                <label>Fecha de inicio <span class="required">*</span></label>
                <input type="date" id="nueva_fecha_inicio" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="cerrarModalNueva()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
var basePath  = window._APP_BASE_PATH ?? '';

function abrirModalNueva() {
    document.getElementById('nueva_fecha_inicio').value = new Date().toISOString().split('T')[0];
    document.getElementById('modalNueva').style.display = 'flex';
    document.getElementById('nueva_nombre').focus();
}
function cerrarModalNueva() {
    document.getElementById('modalNueva').style.display = 'none';
    document.getElementById('formNuevaCampana').reset();
}

document.getElementById('formNuevaCampana').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = this.querySelector('[type=submit]');
    btn.disabled = true;

    fetch(basePath + '/campana/crear', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({
            nombre:       document.getElementById('nueva_nombre').value,
            fecha_inicio: document.getElementById('nueva_fecha_inicio').value
        })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        btn.disabled = false;
        if (res.success) {
            window.location = basePath + '/campana/detalle?id=' + res.id;
        } else {
            showToast(res.message, 'error');
        }
    })
    .catch(function() { btn.disabled = false; showToast('Error de conexión', 'error'); });
});

document.getElementById('modalNueva').addEventListener('click', function(e) {
    if (e.target === this) cerrarModalNueva();
});
</script>

</div>
