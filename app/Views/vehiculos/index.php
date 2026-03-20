<?php $title = 'Vehículos'; ?>
<div class="container">
    <div class="page-header">
        <h2>Vehículos</h2>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="openCrearModal()">+ Nuevo vehículo</button>
            <a href="<?= $this->url('/datos') ?>" class="btn btn-secondary">← Volver</a>
        </div>
    </div>

    <div class="card">
        <table class="styled-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Matrícula</th>
                    <th>ITV</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($vehiculos as $v): ?>
                <tr onclick="window.location.href='<?= $this->url('/vehiculos/detalle?id=' . intval($v['id'])) ?>'" style="cursor:pointer;">
                    <td><?= htmlspecialchars($v['nombre'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($v['matricula'] ?? '—') ?></td>
                    <td>
                        <?php if (!empty($v['pasa_itv'])): ?>
                            <?php
                            $hoy = new DateTime();
                            $fechaItv = new DateTime($v['pasa_itv']);
                            $diff = $hoy->diff($fechaItv);
                            $dias = $fechaItv > $hoy ? $diff->days : -$diff->days;
                            $color = $dias < 0 ? '#f44336' : ($dias <= 30 ? '#ff9800' : '#4caf50');
                            ?>
                            <span style="color:<?= $color ?>"><?= date('d/m/Y', strtotime($v['pasa_itv'])) ?></span>
                        <?php else: ?>
                            <span style="color:#666;">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?= $this->url('/vehiculos/detalle?id=' . intval($v['id'])) ?>" class="btn btn-secondary btn-sm">Ver →</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($vehiculos)): ?>
                <tr><td colspan="4" style="text-align:center; color:#6b7280; padding:2rem;">No hay vehículos registrados.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal crear vehículo -->
    <div id="crearModal" class="modal" style="display:none">
        <div class="modal-overlay" onclick="closeCrearModal()"></div>
        <div class="modal-card">
            <div class="modal-header">
                <h3>Nuevo Vehículo</h3>
                <span class="close" onclick="closeCrearModal()">&times;</span>
            </div>
            <form id="crearVehiculoForm">
                <div class="form-row">
                    <div class="form-group">
                        <label>Nombre <span class="required">*</span></label>
                        <input type="text" id="crearNombre" required placeholder="Ej: Tractor John Deere">
                    </div>
                    <div class="form-group">
                        <label>Matrícula</label>
                        <input type="text" id="crearMatricula" placeholder="Ej: 1234 ABC">
                    </div>
                </div>
                <div class="modal-buttons">
                    <button type="button" class="btn btn-secondary" onclick="closeCrearModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear vehículo</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast -->
    <div id="toast" class="toast"></div>
</div>

<style>
.required { color: #dc3545; }
.modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 1000; }
.modal-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); }
.modal-card {
    position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
    background: #2a2a2a; border: 1px solid #444; border-radius: 10px; padding: 2rem;
    width: 90%; max-width: 480px; max-height: 90vh; overflow-y: auto;
    box-shadow: 0 4px 24px rgba(0,0,0,0.5); z-index: 1; color: #eee;
}
.modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.modal-header h3 { margin: 0; color: #fff; }
.modal-header .close { cursor: pointer; font-size: 1.5rem; color: #888; }
.modal-header .close:hover { color: #fff; }
.modal-buttons { display: flex; gap: 8px; justify-content: flex-end; margin-top: 1.5rem; }
</style>

<script>
var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
var basePath  = window._APP_BASE_PATH || '';

function openCrearModal() { document.getElementById('crearModal').style.display = 'block'; }
function closeCrearModal() { document.getElementById('crearModal').style.display = 'none'; }

document.getElementById('crearVehiculoForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var data = {
        nombre: document.getElementById('crearNombre').value.trim(),
        matricula: document.getElementById('crearMatricula').value.trim()
    };
    fetch(basePath + '/vehiculos/crear', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify(data)
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.success) {
            window.location.href = basePath + '/vehiculos/detalle?id=' + res.id;
        } else {
            if (typeof showToast === 'function') showToast(res.message || 'Error al crear', 'error');
        }
    })
    .catch(function() { if (typeof showToast === 'function') showToast('Error de conexión', 'error'); });
});
</script>
