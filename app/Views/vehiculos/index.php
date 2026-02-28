<?php $title = 'Vehículos'; ?>
<div class="container">
    <div class="page-header">
        <h2>🚗 Vehículos</h2>
        <a href="<?= $this->url('/datos') ?>" class="btn btn-secondary">← Volver</a>
    </div>

    <div class="card">
        <table class="styled-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Matrícula</th>
                    <th>ITV</th>
                    <th>Ficha Técnica</th>
                    <th>Póliza Seguro</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($vehiculos as $v): ?>
                <tr>
                    <td><?= htmlspecialchars($v['nombre'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($v['matricula'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($v['pasa_itv'] ?? '—') ?></td>
                    <td>
                        <?php if (!empty($v['ficha_tecnica'])): ?>
                            <a href="<?= $this->url($v['ficha_tecnica']) ?>" target="_blank" class="btn btn-secondary btn-sm">📄 Ver</a>
                        <?php endif; ?>
                        <button class="btn btn-sm" onclick="subirDoc(<?= intval($v['id']) ?>, 'ficha_tecnica')">
                            📤 <?= empty($v['ficha_tecnica']) ? 'Subir' : 'Reemplazar' ?>
                        </button>
                    </td>
                    <td>
                        <?php if (!empty($v['poliza_seguro'])): ?>
                            <a href="<?= $this->url($v['poliza_seguro']) ?>" target="_blank" class="btn btn-secondary btn-sm">📄 Ver</a>
                        <?php endif; ?>
                        <button class="btn btn-sm" onclick="subirDoc(<?= intval($v['id']) ?>, 'poliza_seguro')">
                            📤 <?= empty($v['poliza_seguro']) ? 'Subir' : 'Reemplazar' ?>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($vehiculos)): ?>
                <tr><td colspan="5" style="text-align:center; color:#6b7280; padding:2rem;">No hay vehículos registrados.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Input oculto compartido para subida de ficheros -->
<input type="file" id="fileInputVehiculo" accept=".jpg,.jpeg,.png,.webp,.pdf" style="display:none;">

<script>
var csrfToken   = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
var basePath    = window._APP_BASE_PATH || '';
var _uploadId   = null;
var _uploadTipo = null;

function subirDoc(id, tipo) {
    _uploadId   = id;
    _uploadTipo = tipo;
    document.getElementById('fileInputVehiculo').click();
}

document.getElementById('fileInputVehiculo').addEventListener('change', function () {
    var file = this.files[0];
    if (!file) return;

    var formData = new FormData();
    formData.append('archivo',     file);
    formData.append('id',          _uploadId);
    formData.append('tipo',        _uploadTipo);
    formData.append('csrf_token',  csrfToken);

    fetch(basePath + '/vehiculos/subirDocumento', {
        method: 'POST',
        body:   formData
    })
    .then(function (r) { return r.json(); })
    .then(function (res) {
        if (res.success) {
            location.reload();
        } else {
            alert('Error: ' + res.message);
        }
    })
    .catch(function () { alert('Error de conexión'); });

    this.value = '';
});
</script>
