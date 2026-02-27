<?php
$title = 'Ficha de Trabajador — ' . htmlspecialchars($trabajador['nombre']);
?>
<div class="container">
    <div class="page-header">
        <div class="header-left" style="display:flex;align-items:center;gap:16px;">
            <?php if (!empty($trabajador['foto'])): ?>
                <img src="<?= $this->url($trabajador['foto']) ?>" alt="Foto" class="worker-avatar-lg">
            <?php else: ?>
                <div class="worker-avatar-lg worker-avatar-lg--empty">👤</div>
            <?php endif; ?>
            <h2>👷 <?= htmlspecialchars($trabajador['nombre']) ?></h2>
        </div>
        <div class="header-actions">
            <a href="<?= $this->url('/datos/trabajadores') ?>" class="btn btn-secondary">← Volver</a>
        </div>
    </div>

    <!-- Datos personales -->
    <div class="card">
        <div class="card-header"><h3>Datos Personales</h3></div>
        <div class="detail-grid">
            <div><strong>Nombre:</strong> <?= htmlspecialchars($trabajador['nombre'] ?? '—') ?></div>
            <div><strong>DNI:</strong> <?= htmlspecialchars($trabajador['dni'] ?? '—') ?></div>
            <div><strong>Nº Seguridad Social:</strong> <?= htmlspecialchars($trabajador['ss'] ?? '—') ?></div>
            <div><strong>Alta SS:</strong>
                <?= !empty($trabajador['alta_ss']) ? date('d/m/Y', strtotime($trabajador['alta_ss'])) : '—' ?>
            </div>
            <div><strong>Baja SS:</strong>
                <?= !empty($trabajador['baja_ss']) ? date('d/m/Y', strtotime($trabajador['baja_ss'])) : '—' ?>
            </div>
            <div><strong>Cuadrilla:</strong>
                <?= !empty($trabajador['cuadrilla']) ? '<span style="color:#28a745;font-weight:bold;">Sí</span>' : 'No' ?>
            </div>
        </div>
    </div>

    <!-- Documentos -->
    <div class="card">
        <div class="card-header"><h3>Documentos</h3></div>
        <div class="detail-grid docs-grid">

            <!-- DNI Anverso -->
            <div class="doc-section">
                <h4>DNI Anverso</h4>
                <?php if (!empty($trabajador['imagen_dni_anverso'])): ?>
                    <?php $ext = strtolower(pathinfo($trabajador['imagen_dni_anverso'], PATHINFO_EXTENSION)); ?>
                    <?php if ($ext === 'pdf'): ?>
                        <a href="<?= $this->url($trabajador['imagen_dni_anverso']) ?>" target="_blank" class="btn btn-secondary btn-sm">Ver PDF</a>
                    <?php else: ?>
                        <img src="<?= $this->url($trabajador['imagen_dni_anverso']) ?>" alt="DNI Anverso" class="doc-preview">
                    <?php endif; ?>
                <?php else: ?>
                    <p class="doc-empty">No subido</p>
                <?php endif; ?>
                <div class="doc-upload">
                    <label class="btn btn-secondary btn-sm">
                        Subir archivo
                        <input type="file" accept="image/jpeg,image/png,image/webp,application/pdf" style="display:none"
                               onchange="subirDocumento(this, 'dni_anverso')">
                    </label>
                </div>
            </div>

            <!-- DNI Reverso -->
            <div class="doc-section">
                <h4>DNI Reverso</h4>
                <?php if (!empty($trabajador['imagen_dni_reverso'])): ?>
                    <?php $ext = strtolower(pathinfo($trabajador['imagen_dni_reverso'], PATHINFO_EXTENSION)); ?>
                    <?php if ($ext === 'pdf'): ?>
                        <a href="<?= $this->url($trabajador['imagen_dni_reverso']) ?>" target="_blank" class="btn btn-secondary btn-sm">Ver PDF</a>
                    <?php else: ?>
                        <img src="<?= $this->url($trabajador['imagen_dni_reverso']) ?>" alt="DNI Reverso" class="doc-preview">
                    <?php endif; ?>
                <?php else: ?>
                    <p class="doc-empty">No subido</p>
                <?php endif; ?>
                <div class="doc-upload">
                    <label class="btn btn-secondary btn-sm">
                        Subir archivo
                        <input type="file" accept="image/jpeg,image/png,image/webp,application/pdf" style="display:none"
                               onchange="subirDocumento(this, 'dni_reverso')">
                    </label>
                </div>
            </div>

            <!-- Tarjeta Seguridad Social -->
            <div class="doc-section">
                <h4>Tarjeta Seguridad Social</h4>
                <?php if (!empty($trabajador['imagen_ss'])): ?>
                    <?php $ext = strtolower(pathinfo($trabajador['imagen_ss'], PATHINFO_EXTENSION)); ?>
                    <?php if ($ext === 'pdf'): ?>
                        <a href="<?= $this->url($trabajador['imagen_ss']) ?>" target="_blank" class="btn btn-secondary btn-sm">Ver PDF</a>
                    <?php else: ?>
                        <img src="<?= $this->url($trabajador['imagen_ss']) ?>" alt="Seguridad Social" class="doc-preview">
                    <?php endif; ?>
                <?php else: ?>
                    <p class="doc-empty">No subido</p>
                <?php endif; ?>
                <div class="doc-upload">
                    <label class="btn btn-secondary btn-sm">
                        Subir archivo
                        <input type="file" accept="image/jpeg,image/png,image/webp,application/pdf" style="display:none"
                               onchange="subirDocumento(this, 'ss')">
                    </label>
                </div>
            </div>

        </div>
    </div>

    <!-- Deuda pendiente -->
    <div class="card">
        <div class="card-header"><h3>Deuda Pendiente</h3></div>
        <div class="detail-grid">
            <div>
                <strong>Total pendiente de pago:</strong>
                <span class="<?= $deuda_pendiente > 0 ? 'text-danger' : 'text-success' ?>" style="font-size:1.2em;font-weight:bold;">
                    <?= number_format($deuda_pendiente, 2) ?> €
                </span>
            </div>
        </div>
    </div>

    <!-- Historial de tareas -->
    <div class="card">
        <div class="card-header"><h3>Historial de Tareas (últimas 50)</h3></div>
        <?php if (empty($historial)): ?>
            <p style="padding:1rem;color:#6c757d;">No hay tareas registradas para este trabajador.</p>
        <?php else: ?>
        <div class="table-container">
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tarea</th>
                        <th style="text-align:right">Horas</th>
                        <th style="text-align:right">€/hora</th>
                        <th style="text-align:right">Coste</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($historial as $fila): ?>
                    <tr>
                        <td><?= !empty($fila['fecha']) ? date('d/m/Y', strtotime($fila['fecha'])) : '—' ?></td>
                        <td><?= htmlspecialchars($fila['titulo'] ?? '—') ?></td>
                        <td style="text-align:right"><?= number_format($fila['horas_asignadas'] ?? 0, 2) ?></td>
                        <td style="text-align:right"><?= number_format($fila['precio_hora'] ?? 0, 2) ?> €</td>
                        <td style="text-align:right"><?= number_format($fila['coste'] ?? 0, 2) ?> €</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" style="text-align:right">Total coste:</th>
                        <th style="text-align:right">
                            <?= number_format(array_sum(array_column($historial, 'coste')), 2) ?> €
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.worker-avatar-lg {
    width: 80px; height: 80px; border-radius: 50%;
    object-fit: cover; border: 3px solid #dee2e6;
    flex-shrink: 0;
}
.worker-avatar-lg--empty {
    width: 80px; height: 80px; border-radius: 50%;
    background: #f0f0f0; display: flex; align-items: center;
    justify-content: center; font-size: 2.2rem; border: 3px solid #dee2e6;
    flex-shrink: 0;
}
.docs-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    padding: 1rem;
}
.doc-section {
    display: flex; flex-direction: column; align-items: center;
    gap: 0.75rem; text-align: center;
}
.doc-section h4 { margin: 0; font-size: 0.95rem; color: #495057; }
.doc-preview {
    max-width: 160px; max-height: 110px;
    border: 1px solid #dee2e6; border-radius: 4px;
    object-fit: cover;
}
.doc-empty { color: #adb5bd; font-style: italic; margin: 0; }
.doc-upload { margin-top: 0.25rem; }
.text-danger { color: #dc3545; }
.text-success { color: #28a745; }
.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    padding: 1rem;
}
</style>

<script>
function subirDocumento(input, tipo) {
    if (!input.files || !input.files[0]) return;

    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var basePath = window._APP_BASE_PATH || '';
    var trabajadorId = <?= intval($trabajador['id']) ?>;

    var formData = new FormData();
    formData.append('id', trabajadorId);
    formData.append('tipo', tipo);
    formData.append('documento', input.files[0]);
    formData.append('csrf_token', csrfToken);

    fetch(basePath + '/trabajadores/subirDocumento', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken },
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.success) {
            location.reload();
        } else {
            alert('Error: ' + res.message);
        }
    })
    .catch(function(err) {
        alert('Error de conexión: ' + err.message);
    });
}
</script>
