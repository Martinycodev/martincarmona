<?php
$title = 'Ficha de Parcela — ' . htmlspecialchars($parcela['nombre']);
?>
<div class="container">
    <div class="page-header">
        <h2>📍 <?= htmlspecialchars($parcela['nombre']) ?></h2>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="openEditModal()">✏️ Editar</button>
            <button class="btn btn-danger" onclick="deleteParcela()"><?= emoji('trash') ?> Eliminar</button>
            <a href="<?= $this->url('/datos/parcelas') ?>" class="btn btn-secondary">← Volver</a>
        </div>
    </div>

    <!-- Datos generales + Imagen -->
    <div class="card">
        <div class="card-header"><h3>Datos de la Parcela</h3></div>
        <div class="parcela-datos-layout">
            <!-- Columna izquierda: datos -->
            <div class="detail-grid" style="flex:1;">
                <div><strong>Olivos:</strong> <?= intval($parcela['olivos']) ?></div>
                <?php if (($parcela['riego_secano'] ?? '') !== 'secano'): ?>
                <div><strong>Hidrante:</strong> <?= intval($parcela['hidrante'] ?? 0) ?></div>
                <?php endif; ?>
                <?php if (!empty($parcela['referencia_catastral'])): ?>
                <div><strong>Referencia catastral:</strong> <?= htmlspecialchars($parcela['referencia_catastral']) ?></div>
                <?php endif; ?>
                <?php if (!empty($parcela['num_municipio']) || !empty($parcela['num_poligono']) || !empty($parcela['num_parcela'])): ?>
                <div><strong>SIGPAC:</strong> Mun. <?= htmlspecialchars($parcela['num_municipio'] ?? '—') ?> / Pol. <?= htmlspecialchars($parcela['num_poligono'] ?? '—') ?> / Par. <?= htmlspecialchars($parcela['num_parcela'] ?? '—') ?></div>
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
            <!-- Columna derecha: imagen de la parcela -->
            <div class="parcela-imagen-col">
                <div class="parcela-imagen-wrap" id="parcelaImagenWrap">
                    <?php if (!empty($parcela['imagen'])): ?>
                        <img src="<?= $this->url($parcela['imagen']) ?>" alt="Imagen de <?= htmlspecialchars($parcela['nombre']) ?>"
                             class="parcela-imagen" onclick="openLightbox(this.src)"
                             title="Click para ver en grande">
                        <button class="parcela-imagen-delete" onclick="eliminarImagenParcela()" title="Eliminar imagen">✕</button>
                    <?php else: ?>
                        <div class="parcela-imagen-placeholder" onclick="document.getElementById('inputImagenParcela').click()">
                            <span>+</span>
                            <small>Subir imagen</small>
                        </div>
                    <?php endif; ?>
                </div>
                <input type="file" id="inputImagenParcela" accept="image/jpeg,image/png,image/webp" style="display:none"
                       onchange="subirImagenParcela(this)">
                <?php if (!empty($parcela['imagen'])): ?>
                <button class="btn btn-secondary btn-sm" style="margin-top:8px;width:100%;"
                        onclick="document.getElementById('inputImagenParcela').click()">Cambiar imagen</button>
                <?php endif; ?>
            </div>
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
                        <label>Nº Municipio:</label>
                        <input type="text" name="num_municipio" maxlength="10" value="<?= htmlspecialchars($parcela['num_municipio'] ?? '') ?>" placeholder="Ej: 050">
                    </div>
                    <div class="form-group">
                        <label>Nº Polígono:</label>
                        <input type="text" name="num_poligono" maxlength="10" value="<?= htmlspecialchars($parcela['num_poligono'] ?? '') ?>" placeholder="Ej: 012">
                    </div>
                    <div class="form-group">
                        <label>Nº Parcela:</label>
                        <input type="text" name="num_parcela" maxlength="10" value="<?= htmlspecialchars($parcela['num_parcela'] ?? '') ?>" placeholder="Ej: 00045">
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
                    <td><?= !empty($doc['created_at']) ? date('d-m-Y', strtotime($doc['created_at'])) : '—' ?></td>
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
            showToast(json.message || 'Error desconocido', 'error');
        }
    } catch {
        showToast('Error de conexión', 'error');
    }
}

// showToast() ya definida globalmente en modal-functions.js

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
            showToast(res.message, 'error');
        }
    })
    .catch(function() {
        showToast('Error de conexión', 'error');
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
            showToast(res.message, 'error');
        }
    });
}

// --- Imagen de parcela ---
async function subirImagenParcela(input) {
    var file = input.files[0];
    if (!file) return;

    // Comprimir imagen antes de subir (fotos de móvil pueden pesar 5-15MB)
    var compressed = await compressImage(file);

    var formData = new FormData();
    formData.append('imagen', compressed);
    formData.append('id', <?= intval($parcela['id']) ?>);
    formData.append('csrf_token', csrfToken);

    fetch(basePath + '/parcelas/subirImagen', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken },
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.success) {
            if (typeof showToast === 'function') showToast('Imagen subida correctamente', 'success');
            setTimeout(function() { location.reload(); }, 600);
        } else {
            if (typeof showToast === 'function') showToast(res.message || 'Error al subir', 'error');
        }
    })
    .catch(function() { if (typeof showToast === 'function') showToast('Error de conexión', 'error'); });
    input.value = '';
}

function eliminarImagenParcela() {
    if (!confirm('¿Eliminar la imagen de esta parcela?')) return;
    fetch(basePath + '/parcelas/eliminarImagen', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ id: <?= intval($parcela['id']) ?> })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.success) {
            location.reload();
        } else {
            if (typeof showToast === 'function') showToast(res.message || 'Error', 'error');
        }
    });
}

// Abrir lightbox (usa el lightbox global del footer)
function openLightbox(src) {
    var lb = document.getElementById('img-lightbox');
    var img = document.getElementById('img-lightbox-img');
    if (lb && img) {
        img.src = src;
        lb.style.display = 'flex';
    }
}
</script>

<style>
/* Layout datos + imagen de parcela */
.parcela-datos-layout {
    display: flex;
    gap: 24px;
    padding: 0;
}
.parcela-imagen-col {
    flex: 0 0 200px;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 16px 16px 16px 0;
}
.parcela-imagen-wrap {
    position: relative;
    width: 200px;
    height: 200px;
    border-radius: 10px;
    overflow: hidden;
    background: #333;
    border: 2px solid #444;
}
.parcela-imagen {
    width: 100%;
    height: 100%;
    object-fit: cover;
    cursor: pointer;
    transition: opacity 0.2s;
}
.parcela-imagen:hover {
    opacity: 0.85;
}
.parcela-imagen-delete {
    position: absolute;
    top: 6px;
    right: 6px;
    background: rgba(0,0,0,0.7);
    color: #f44336;
    border: none;
    border-radius: 50%;
    width: 28px;
    height: 28px;
    cursor: pointer;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.2s;
}
.parcela-imagen-wrap:hover .parcela-imagen-delete {
    opacity: 1;
}
.parcela-imagen-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #666;
    cursor: pointer;
    transition: background 0.2s;
}
.parcela-imagen-placeholder:hover {
    background: #3a3a3a;
    color: #4caf50;
}
.parcela-imagen-placeholder span {
    font-size: 2rem;
    line-height: 1;
}
.parcela-imagen-placeholder small {
    margin-top: 4px;
    font-size: 0.8rem;
}

/* Responsive: en móvil, imagen arriba */
@media (max-width: 640px) {
    .parcela-datos-layout {
        flex-direction: column-reverse;
    }
    .parcela-imagen-col {
        flex: none;
        padding: 16px 16px 0;
        align-items: center;
    }
    .parcela-imagen-wrap {
        width: 160px;
        height: 160px;
    }
}
</style>
