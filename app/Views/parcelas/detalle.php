<?php
$title = 'Ficha de Parcela — ' . htmlspecialchars($parcela['nombre']);
?>
<div class="container">
    <div class="page-header">
        <h2>📍 <?= htmlspecialchars($parcela['nombre']) ?></h2>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="openEditModal()">✏️ Editar</button>
            <button class="btn btn-danger" onclick="deleteParcela()">🗑️ Eliminar</button>
            <a href="<?= $this->url('/datos/parcelas') ?>" class="btn btn-secondary">← Volver</a>
        </div>
    </div>

    <!-- Datos generales -->
    <div class="card">
        <div class="card-header"><h3>Datos de la Parcela</h3></div>
        <div class="detail-grid">
            <div><strong>Olivos:</strong> <?= intval($parcela['olivos']) ?></div>
            <div><strong>Ubicación:</strong> <?= htmlspecialchars($parcela['ubicacion'] ?? '—') ?></div>
            <div><strong>Hidrante:</strong> <?= intval($parcela['hidrante'] ?? 0) ?></div>
            <?php if (!empty($parcela['referencia_catastral'])): ?>
            <div><strong>Referencia catastral:</strong> <?= htmlspecialchars($parcela['referencia_catastral']) ?></div>
            <?php endif; ?>
            <?php if (!empty($parcela['tipo_olivos'])): ?>
            <div><strong>Tipo de olivos:</strong> <?= htmlspecialchars($parcela['tipo_olivos']) ?></div>
            <?php endif; ?>
            <?php if (!empty($parcela['año_plantacion'])): ?>
            <div><strong>Año de plantación:</strong> <?= intval($parcela['año_plantacion']) ?></div>
            <?php endif; ?>
            <?php if (!empty($parcela['tipo_plantacion'])): ?>
            <div><strong>Tipo de plantación:</strong> <?= htmlspecialchars($parcela['tipo_plantacion']) ?></div>
            <?php endif; ?>
            <?php if (!empty($parcela['riego_secano'])): ?>
            <div><strong>Riego / Secano:</strong> <?= htmlspecialchars($parcela['riego_secano']) ?></div>
            <?php endif; ?>
            <?php if (!empty($parcela['corta'])): ?>
            <div><strong>Corta:</strong> <?= htmlspecialchars($parcela['corta']) ?></div>
            <?php endif; ?>
            <?php if (!empty($parcela['descripcion'])): ?>
            <div style="grid-column: 1 / -1;"><strong>Descripción:</strong> <?= htmlspecialchars($parcela['descripcion']) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Propietario -->
    <?php if (!empty($parcela['propietario_nombre'])): ?>
    <div class="card">
        <div class="card-header"><h3>Propietario</h3></div>
        <div class="detail-grid">
            <div><strong>Nombre:</strong> <?= htmlspecialchars($parcela['propietario_nombre'] . ' ' . ($parcela['propietario_apellidos'] ?? '')) ?></div>
            <?php if (!empty($parcela['propietario_telefono'])): ?>
            <div><strong>Teléfono:</strong> <?= htmlspecialchars($parcela['propietario_telefono']) ?></div>
            <?php endif; ?>
            <?php if (!empty($parcela['propietario_email'])): ?>
            <div><strong>Email:</strong> <?= htmlspecialchars($parcela['propietario_email']) ?></div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Resumen económico -->
    <div class="card">
        <div class="card-header"><h3>Resumen Económico</h3></div>
        <div class="detail-grid">
            <div><strong>Coste acumulado:</strong> <?= number_format($coste_acumulado, 2) ?> €</div>
            <?php if (intval($parcela['olivos']) > 0): ?>
            <div><strong>Coste por olivo:</strong> <?= number_format($coste_acumulado / intval($parcela['olivos']), 2) ?> €</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Historial de Riego -->
    <?php if (!empty($riegos_por_anio) || !empty($riegos_recientes)): ?>
    <div class="card">
        <div class="card-header"><h3>💧 Historial de Riego</h3></div>

        <?php if (!empty($riegos_por_anio)): ?>
        <div class="detail-grid" style="margin-bottom: 1rem;">
            <?php foreach ($riegos_por_anio as $ra): ?>
            <div>
                <strong><?= intval($ra['anio']) ?>:</strong>
                <?= number_format($ra['total_m3_anio'], 1) ?> m³
                (<?= intval($ra['num_riegos']) ?> riego<?= $ra['num_riegos'] != 1 ? 's' : '' ?>)
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($riegos_recientes)): ?>
        <table class="styled-table">
            <thead>
                <tr>
                    <th>Fecha ini</th>
                    <th>Fecha fin</th>
                    <th>Días</th>
                    <th>Hidrante</th>
                    <th>Contador ini</th>
                    <th>Contador fin</th>
                    <th>Total m³</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($riegos_recientes as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($r['fecha_ini'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($r['fecha_fin'] ?? '—') ?></td>
                    <td><?= $r['dias'] !== null ? intval($r['dias']) : '—' ?></td>
                    <td><?= htmlspecialchars($r['hidrante'] ?? '—') ?></td>
                    <td><?= $r['cantidad_ini'] !== null ? number_format($r['cantidad_ini'], 1) : '—' ?></td>
                    <td><?= $r['cantidad_fin'] !== null ? number_format($r['cantidad_fin'], 1) : '—' ?></td>
                    <td><strong><?= $r['total_m3'] !== null ? number_format($r['total_m3'], 1) . ' m³' : '—' ?></strong></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <div style="padding: 0.5rem 0;">
            <a href="<?= $this->url('/datos/riego') ?>" class="btn btn-secondary btn-sm">Ver todos los riegos →</a>
        </div>
    </div>
    <?php endif; ?>

    <!-- Modal de Edición -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>✏️ Editar Parcela</h3>
                <span class="close" onclick="closeEditModal()">&times;</span>
            </div>
            <form id="editParcelaForm">
                <input type="hidden" name="id" value="<?= intval($parcela['id']) ?>">
                <div class="form-row">
                    <div class="form-group">
                        <label>Nombre:</label>
                        <input type="text" name="nombre" value="<?= htmlspecialchars($parcela['nombre'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Ubicación:</label>
                        <input type="text" name="ubicacion" value="<?= htmlspecialchars($parcela['ubicacion'] ?? '') ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Número de Olivos:</label>
                        <input type="number" name="olivos" min="0" value="<?= intval($parcela['olivos'] ?? 0) ?>">
                    </div>
                    <div class="form-group">
                        <label>Hidrante:</label>
                        <input type="number" name="hidrante" min="0" value="<?= intval($parcela['hidrante'] ?? 0) ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Referencia Catastral:</label>
                        <input type="text" name="referencia_catastral" maxlength="50" value="<?= htmlspecialchars($parcela['referencia_catastral'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Tipo de Olivos:</label>
                        <input type="text" name="tipo_olivos" maxlength="100" value="<?= htmlspecialchars($parcela['tipo_olivos'] ?? '') ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Año de Plantación:</label>
                        <input type="number" name="año_plantacion" min="1900" max="2100" value="<?= intval($parcela['año_plantacion'] ?? 0) ?: '' ?>">
                    </div>
                    <div class="form-group">
                        <label>Tipo de Plantación:</label>
                        <select name="tipo_plantacion">
                            <option value="">-- Seleccionar --</option>
                            <?php foreach (['tradicional','intensivo','superintensivo'] as $opt): ?>
                            <option value="<?= $opt ?>" <?= ($parcela['tipo_plantacion'] ?? '') === $opt ? 'selected' : '' ?>><?= ucfirst($opt) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Riego / Secano:</label>
                        <select name="riego_secano">
                            <option value="">-- Seleccionar --</option>
                            <?php foreach (['riego','secano'] as $opt): ?>
                            <option value="<?= $opt ?>" <?= ($parcela['riego_secano'] ?? '') === $opt ? 'selected' : '' ?>><?= ucfirst($opt) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Corta:</label>
                        <select name="corta">
                            <option value="">-- Seleccionar --</option>
                            <?php foreach (['par','impar','siempre'] as $opt): ?>
                            <option value="<?= $opt ?>" <?= ($parcela['corta'] ?? '') === $opt ? 'selected' : '' ?>><?= ucfirst($opt) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Descripción:</label>
                        <textarea name="descripcion" rows="3"><?= htmlspecialchars($parcela['descripcion'] ?? '') ?></textarea>
                    </div>
                </div>
                <input type="hidden" name="propietario_id" value="<?= intval($parcela['propietario_id'] ?? 0) ?>">
                <input type="hidden" name="propietario" value="<?= htmlspecialchars($parcela['propietario'] ?? '') ?>">
                <div class="modal-buttons">
                    <button type="button" class="btn-modal btn-secondary" onclick="closeEditModal()">Cancelar</button>
                    <button type="submit" class="btn-modal btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast de notificaciones -->
    <div id="toast" class="toast"></div>

    <!-- Documentos -->
    <div class="card">
        <div class="card-header">
            <h3>Documentos</h3>
        </div>

        <!-- Formulario subida -->
        <form id="uploadDocForm" class="upload-form" style="padding: 1rem 0;">
            <input type="hidden" name="parcela_id" value="<?= intval($parcela['id']) ?>">
            <div class="form-row">
                <div class="form-group">
                    <label>Nombre del documento: <span class="required">*</span></label>
                    <input type="text" name="nombre" required placeholder="Ej: Escritura 2020">
                </div>
                <div class="form-group">
                    <label>Tipo:</label>
                    <select name="tipo">
                        <option value="escritura">Escritura</option>
                        <option value="permiso_riego">Permiso de riego</option>
                        <option value="otro" selected>Otro</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Archivo (PDF o imagen, máx. 10MB): <span class="required">*</span></label>
                <input type="file" name="archivo" accept=".pdf,.jpg,.jpeg,.png,.webp" required>
            </div>
            <button type="submit" class="btn btn-primary">📎 Subir Documento</button>
        </form>

        <!-- Tabla de documentos -->
        <table class="styled-table" id="docsTable">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Tipo</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($documentos as $doc): ?>
                <tr id="doc-row-<?= intval($doc['id']) ?>">
                    <td><?= htmlspecialchars($doc['nombre']) ?></td>
                    <td><?= htmlspecialchars($doc['tipo']) ?></td>
                    <td><?= htmlspecialchars($doc['created_at']) ?></td>
                    <td>
                        <a href="<?= $this->url($doc['archivo']) ?>" class="btn btn-secondary btn-sm" target="_blank">Descargar</a>
                        <button class="btn btn-danger btn-sm" onclick="eliminarDocumento(<?= intval($doc['id']) ?>)">Eliminar</button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($documentos)): ?>
                <tr><td colspan="4" class="text-center">No hay documentos adjuntos</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
var basePath = window._APP_BASE_PATH || '';

// --- Edit modal ---
function openEditModal() {
    document.getElementById('editModal').style.display = 'block';
}
function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}
window.addEventListener('click', function(e) {
    if (e.target === document.getElementById('editModal')) closeEditModal();
});

document.getElementById('editParcelaForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(this));
    try {
        showToast('Actualizando...', 'info');
        const res = await fetch(basePath + '/parcelas/actualizar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify(data)
        });
        const json = await res.json();
        if (json.success) {
            closeEditModal();
            location.reload();
        } else {
            showToast('Error: ' + (json.message || 'No se pudo guardar'), 'error');
        }
    } catch {
        showToast('Error de conexión', 'error');
    }
});

// --- Delete ---
async function deleteParcela() {
    if (!confirm('¿Eliminar esta parcela? Esta acción no se puede deshacer.')) return;
    try {
        const res = await fetch(basePath + '/parcelas/eliminar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ id: <?= intval($parcela['id']) ?> })
        });
        const json = await res.json();
        if (json.success) {
            window.location.href = basePath + '/datos/parcelas';
        } else {
            alert('Error al eliminar: ' + (json.message || 'Error desconocido'));
        }
    } catch {
        alert('Error de conexión');
    }
}

// --- Toast ---
function showToast(message, type) {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.className = 'toast toast-' + (type || 'info');
    toast.offsetHeight;
    toast.classList.add('show');
    setTimeout(function() {
        toast.classList.remove('show');
        toast.classList.add('hide');
        setTimeout(function() { toast.classList.remove('hide'); }, 400);
    }, 3000);
}

document.getElementById('uploadDocForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    formData.append('csrf_token', csrfToken);

    fetch(basePath + '/parcelas/subirDocumento', {
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
    .catch(function() {
        alert('Error de conexión');
    });
});

function eliminarDocumento(id) {
    if (!confirm('¿Eliminar este documento?')) return;
    fetch(basePath + '/parcelas/eliminarDocumento', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ id: id })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.success) {
            var row = document.getElementById('doc-row-' + id);
            if (row) row.remove();
        } else {
            alert('Error: ' + res.message);
        }
    });
}
</script>
