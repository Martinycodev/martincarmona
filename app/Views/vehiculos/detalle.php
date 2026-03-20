<?php
$title = 'Ficha de Vehículo — ' . htmlspecialchars($vehiculo['nombre'] ?? '');
// Formatear fechas para mostrar
$fechaItv = !empty($vehiculo['pasa_itv']) ? date('d/m/Y', strtotime($vehiculo['pasa_itv'])) : null;
$fechaMatriculacion = !empty($vehiculo['fecha_matriculacion']) ? date('d/m/Y', strtotime($vehiculo['fecha_matriculacion'])) : null;

// Calcular estado de la ITV
$itvEstado = null;
$itvClase = '';
if (!empty($vehiculo['pasa_itv'])) {
    $hoy = new DateTime();
    $fechaItvObj = new DateTime($vehiculo['pasa_itv']);
    $diff = $hoy->diff($fechaItvObj);
    $diasRestantes = $fechaItvObj > $hoy ? $diff->days : -$diff->days;

    if ($diasRestantes < 0) {
        $itvEstado = 'CADUCADA';
        $itvClase = 'itv-caducada';
    } elseif ($diasRestantes <= 30) {
        $itvEstado = "Vence en {$diasRestantes} días";
        $itvClase = 'itv-proxima';
    } else {
        $itvEstado = 'Vigente';
        $itvClase = 'itv-vigente';
    }
}
?>
<div class="container">
    <div class="page-header">
        <h2><?= htmlspecialchars($vehiculo['nombre'] ?? 'Vehículo') ?></h2>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="openEditModal()">Editar</button>
            <a href="<?= $this->url('/datos/vehiculos') ?>" class="btn btn-secondary">← Volver</a>
        </div>
    </div>

    <!-- Datos principales -->
    <div class="card">
        <div class="card-header"><h3>Datos del Vehículo</h3></div>
        <div class="detail-grid">
            <div class="detail-item">
                <strong>Nombre</strong>
                <span><?= htmlspecialchars($vehiculo['nombre'] ?? '—') ?></span>
            </div>
            <div class="detail-item">
                <strong>Matrícula</strong>
                <span><?= htmlspecialchars($vehiculo['matricula'] ?? '—') ?></span>
            </div>
            <div class="detail-item">
                <strong>Fecha matriculación</strong>
                <span><?= $fechaMatriculacion ?? '—' ?></span>
            </div>
        </div>
    </div>

    <!-- ITV -->
    <div class="card">
        <div class="card-header">
            <h3>ITV</h3>
            <?php if ($itvEstado): ?>
                <span class="itv-badge <?= $itvClase ?>"><?= $itvEstado ?></span>
            <?php endif; ?>
        </div>
        <div class="detail-grid">
            <div class="detail-item">
                <strong>Fecha próxima ITV</strong>
                <span><?= $fechaItv ?? 'Sin registrar' ?></span>
            </div>
            <?php if (!empty($vehiculo['pasa_itv']) && $itvEstado): ?>
            <div class="detail-item">
                <strong>Estado</strong>
                <span class="<?= $itvClase ?>"><?= $itvEstado ?></span>
            </div>
            <?php endif; ?>
        </div>
        <p style="color:#888;font-size:0.85rem;margin:12px 16px 8px;">
            Al actualizar la fecha de ITV, se genera automáticamente un recordatorio 15 días antes del vencimiento.
        </p>
    </div>

    <!-- Seguro -->
    <div class="card">
        <div class="card-header"><h3>Seguro</h3></div>
        <div class="detail-grid">
            <div class="detail-item">
                <strong>Aseguradora</strong>
                <span><?= htmlspecialchars($vehiculo['seguro'] ?? '—') ?></span>
            </div>
            <div class="detail-item">
                <strong>Precio del seguro</strong>
                <span><?= !empty($vehiculo['precio_seguro']) ? number_format($vehiculo['precio_seguro'], 2, ',', '.') . ' €' : '—' ?></span>
            </div>
            <div class="detail-item">
                <strong>Teléfono aseguradora</strong>
                <span><?= htmlspecialchars($vehiculo['telefono_aseguradora'] ?? '—') ?></span>
            </div>
        </div>
    </div>

    <!-- Documentos -->
    <div class="card">
        <div class="card-header"><h3>Documentos</h3></div>
        <div class="detail-grid">
            <div class="detail-item">
                <strong>Ficha Técnica</strong>
                <?php if (!empty($vehiculo['ficha_tecnica'])): ?>
                    <a href="<?= $this->url($vehiculo['ficha_tecnica']) ?>" target="_blank" class="btn btn-secondary btn-sm" style="margin-top:4px;">Ver documento</a>
                <?php else: ?>
                    <span style="color:#888;">Sin adjuntar</span>
                <?php endif; ?>
                <button class="btn btn-secondary btn-sm" onclick="subirDoc('ficha_tecnica')" style="margin-top:4px;">
                    <?= empty($vehiculo['ficha_tecnica']) ? 'Subir' : 'Reemplazar' ?>
                </button>
            </div>
            <div class="detail-item">
                <strong>Póliza de Seguro</strong>
                <?php if (!empty($vehiculo['poliza_seguro'])): ?>
                    <a href="<?= $this->url($vehiculo['poliza_seguro']) ?>" target="_blank" class="btn btn-secondary btn-sm" style="margin-top:4px;">Ver documento</a>
                <?php else: ?>
                    <span style="color:#888;">Sin adjuntar</span>
                <?php endif; ?>
                <button class="btn btn-secondary btn-sm" onclick="subirDoc('poliza_seguro')" style="margin-top:4px;">
                    <?= empty($vehiculo['poliza_seguro']) ? 'Subir' : 'Reemplazar' ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Zona peligrosa -->
    <div class="card" style="border:1px solid #f4433633; margin-top:2rem;">
        <div class="card-header" style="border-bottom:1px solid #f4433633;">
            <h3 style="color:#f44336;">Zona peligrosa</h3>
        </div>
        <div style="padding:16px; display:flex; justify-content:space-between; align-items:center;">
            <div>
                <strong style="color:#eee;">Eliminar vehículo</strong>
                <p style="color:#888;margin:4px 0 0;font-size:0.85rem;">Esta acción no se puede deshacer.</p>
            </div>
            <button class="btn btn-danger" onclick="deleteVehiculo()">Eliminar</button>
        </div>
    </div>

    <!-- Modal de Edición -->
    <div id="editModal" class="modal" style="display:none">
        <div class="modal-overlay" onclick="closeEditModal()"></div>
        <div class="modal-card">
            <div class="modal-header">
                <h3>Editar Vehículo</h3>
                <span class="close" onclick="closeEditModal()">&times;</span>
            </div>
            <form id="editVehiculoForm">
                <div class="form-row">
                    <div class="form-group">
                        <label>Nombre <span class="required">*</span></label>
                        <input type="text" id="editNombre" value="<?= htmlspecialchars($vehiculo['nombre'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Matrícula</label>
                        <input type="text" id="editMatricula" value="<?= htmlspecialchars($vehiculo['matricula'] ?? '') ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Fecha matriculación</label>
                        <input type="date" id="editFechaMatriculacion" value="<?= htmlspecialchars($vehiculo['fecha_matriculacion'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Próxima ITV</label>
                        <input type="date" id="editPasaItv" value="<?= htmlspecialchars($vehiculo['pasa_itv'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-section">
                    <h4>Datos del Seguro</h4>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Aseguradora</label>
                            <input type="text" id="editSeguro" value="<?= htmlspecialchars($vehiculo['seguro'] ?? '') ?>" placeholder="Ej: Mapfre, Allianz...">
                        </div>
                        <div class="form-group">
                            <label>Precio seguro (€)</label>
                            <input type="number" id="editPrecioSeguro" step="0.01" min="0" value="<?= htmlspecialchars($vehiculo['precio_seguro'] ?? '') ?>" placeholder="0,00">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Teléfono aseguradora</label>
                            <input type="text" id="editTelAseguradora" value="<?= htmlspecialchars($vehiculo['telefono_aseguradora'] ?? '') ?>" placeholder="Ej: 900 123 456">
                        </div>
                    </div>
                </div>

                <div class="modal-buttons">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Input oculto para subir documentos -->
    <input type="file" id="fileInputVehiculo" accept=".jpg,.jpeg,.png,.webp,.pdf" style="display:none;">

    <!-- Toast -->
    <div id="toast" class="toast"></div>
</div>

<style>
.required { color: #dc3545; }
.form-section { margin-top: 1.5rem; border-top: 1px solid #444; padding-top: 1rem; }
.form-section h4 { margin-bottom: 0.75rem; color: #ccc; }
.itv-badge {
    display: inline-block; padding: 4px 10px; border-radius: 6px;
    font-size: 0.8rem; font-weight: 600;
}
.itv-vigente { background: #4caf5022; color: #4caf50; }
.itv-proxima { background: #ff980022; color: #ff9800; }
.itv-caducada { background: #f4433622; color: #f44336; }
.modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 1000; }
.modal-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); }
.modal-card {
    position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
    background: #2a2a2a; border: 1px solid #444; border-radius: 10px; padding: 2rem;
    width: 90%; max-width: 640px; max-height: 90vh; overflow-y: auto;
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
var vehiculoId = <?= intval($vehiculo['id']) ?>;

function openEditModal() {
    document.getElementById('editModal').style.display = 'block';
}
function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

// Guardar cambios del vehículo
document.getElementById('editVehiculoForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var data = {
        id: vehiculoId,
        nombre: document.getElementById('editNombre').value.trim(),
        matricula: document.getElementById('editMatricula').value.trim(),
        fecha_matriculacion: document.getElementById('editFechaMatriculacion').value,
        pasa_itv: document.getElementById('editPasaItv').value,
        seguro: document.getElementById('editSeguro').value.trim(),
        precio_seguro: document.getElementById('editPrecioSeguro').value,
        telefono_aseguradora: document.getElementById('editTelAseguradora').value.trim()
    };

    fetch(basePath + '/vehiculos/actualizar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify(data)
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.success) {
            if (typeof showToast === 'function') showToast('Vehículo actualizado', 'success');
            closeEditModal();
            setTimeout(function() { location.reload(); }, 800);
        } else {
            if (typeof showToast === 'function') showToast(res.message || 'Error al guardar', 'error');
        }
    })
    .catch(function() { if (typeof showToast === 'function') showToast('Error de conexión', 'error'); });
});

// Eliminar vehículo
function deleteVehiculo() {
    if (!confirm('¿Eliminar este vehículo? Esta acción no se puede deshacer.')) return;
    fetch(basePath + '/vehiculos/eliminar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ id: vehiculoId })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.success) {
            window.location.href = basePath + '/datos/vehiculos';
        } else {
            if (typeof showToast === 'function') showToast(res.message || 'Error al eliminar', 'error');
        }
    })
    .catch(function() { if (typeof showToast === 'function') showToast('Error de conexión', 'error'); });
}

// Subir documentos (ficha técnica / póliza)
var _uploadTipo = null;
function subirDoc(tipo) {
    _uploadTipo = tipo;
    document.getElementById('fileInputVehiculo').click();
}

document.getElementById('fileInputVehiculo').addEventListener('change', function() {
    var file = this.files[0];
    if (!file) return;

    var formData = new FormData();
    formData.append('archivo', file);
    formData.append('id', vehiculoId);
    formData.append('tipo', _uploadTipo);
    formData.append('csrf_token', csrfToken);

    fetch(basePath + '/vehiculos/subirDocumento', {
        method: 'POST',
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.success) {
            if (typeof showToast === 'function') showToast('Documento subido correctamente', 'success');
            setTimeout(function() { location.reload(); }, 800);
        } else {
            if (typeof showToast === 'function') showToast(res.message || 'Error al subir', 'error');
        }
    })
    .catch(function() { if (typeof showToast === 'function') showToast('Error de conexión', 'error'); });

    this.value = '';
});
</script>
