<?php $title = 'Herramientas'; ?>
<div class="container">
    <div class="page-header">
        <h2>🔧 Herramientas</h2>
        <a href="<?= $this->url('/datos') ?>" class="btn btn-secondary">← Volver</a>
    </div>

    <div class="card">
        <table class="styled-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Cantidad</th>
                    <th>Precio</th>
                    <th>Instrucciones PDF</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($herramientas as $h): ?>
                <tr>
                    <td><?= htmlspecialchars($h['nombre'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($h['cantidad'] ?? '—') ?></td>
                    <td><?= isset($h['precio']) ? number_format($h['precio'], 2) . ' €' : '—' ?></td>
                    <td>
                        <?php if (!empty($h['instrucciones_pdf'])): ?>
                            <a href="<?= $this->url($h['instrucciones_pdf']) ?>" target="_blank" class="btn btn-secondary btn-sm">📄 Ver PDF</a>
                        <?php endif; ?>
                        <button class="btn btn-sm" onclick="subirInstrucciones(<?= intval($h['id']) ?>)">
                            📤 <?= empty($h['instrucciones_pdf']) ? 'Subir' : 'Reemplazar' ?>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($herramientas)): ?>
                <tr><td colspan="4" style="text-align:center; color:#6b7280; padding:2rem;">No hay herramientas registradas.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Input oculto para subida de PDF -->
<input type="file" id="fileInputHerramienta" accept=".pdf" style="display:none;">

<script>
var csrfToken  = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
var basePath   = window._APP_BASE_PATH || '';
var _uploadIdH = null;

function subirInstrucciones(id) {
    _uploadIdH = id;
    document.getElementById('fileInputHerramienta').click();
}

document.getElementById('fileInputHerramienta').addEventListener('change', function () {
    var file = this.files[0];
    if (!file) return;

    var formData = new FormData();
    formData.append('archivo',    file);
    formData.append('id',         _uploadIdH);
    formData.append('csrf_token', csrfToken);

    fetch(basePath + '/herramientas/subirInstrucciones', {
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
