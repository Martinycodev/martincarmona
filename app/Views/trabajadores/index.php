<?php
$title = 'Gestión de Trabajadores';
?>
<div class="container">
    <div class="page-header">
        <h1>👷‍♂️ Gestión de Trabajadores</h1>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="openCreateModal()">➕ Nuevo Trabajador</button>
            <a href="<?= $this->url('/datos') ?>" class="btn btn-secondary">← Volver</a>
        </div>
    </div>

    <!-- Sección de Crear Nuevo Trabajador -->
    <div class="create-section" id="createSection">
        <div class="card">
            <div class="card-header">
                <h3>➕ Crear Nuevo Trabajador</h3>
                <button class="close-btn" onclick="closeCreateSection()">×</button>
            </div>
            <form id="createWorkerForm">
                <!-- Foto de perfil -->
                <div class="form-group foto-group">
                    <label>Foto de perfil:</label>
                    <div class="foto-preview-wrap">
                        <div class="foto-preview foto-preview--empty" id="createFotoPreview">
                            <span>👤</span>
                        </div>
                        <label class="btn btn-secondary btn-sm foto-btn" for="createFotoInput">
                            Seleccionar imagen
                            <input type="file" id="createFotoInput" accept="image/*" style="display:none"
                                   onchange="previewFoto(this, 'createFotoPreview')">
                        </label>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="nombre">Nombre Completo: <span class="required">*</span></label>
                        <input type="text" id="nombre" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="dni">DNI (8 números + 1 letra mayúscula):</label>
                        <input type="text" id="dni" name="dni" maxlength="9" pattern="[0-9]{8}[A-Z]"
                               placeholder="12345678A" title="Formato: 8 números seguidos de 1 letra mayúscula"
                               oninput="formatearDNI(this)">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="ss">Número SS:</label>
                        <input type="text" id="ss" name="ss" maxlength="12">
                    </div>
                    <div class="form-group">
                        <label for="alta_ss">Alta en Seguridad Social:</label>
                        <input type="date" id="alta_ss" name="alta_ss">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="baja_ss">Baja en Seguridad Social:</label>
                        <input type="date" id="baja_ss" name="baja_ss">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group form-group--checkbox">
                        <label class="checkbox-label">
                            <input type="checkbox" id="cuadrilla" name="cuadrilla" value="1">
                            <span>👷 Parte de la cuadrilla</span>
                        </label>
                        <small class="form-hint">Los trabajadores de la cuadrilla se pueden asignar a tareas en bloque.</small>
                    </div>
                </div>

                <div class="form-buttons">
                    <button type="button" class="btn btn-secondary" onclick="closeCreateSection()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear Trabajador</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Trabajadores -->
    <div class="table-container">
        <table class="styled-table">
            <thead>
                <tr>
                    <th style="width:50px">Foto</th>
                    <th>Nombre</th>
                    <th>DNI</th>
                    <th>Alta SS</th>
                    <th style="width:80px;text-align:center">Cuadrilla</th>
                    <th class="actions-column">Acciones</th>
                </tr>
            </thead>
            <tbody id="trabajadoresTableBody">
                <?php foreach ($trabajadores as $trabajador): ?>
                <?php
                    $fotoSrc = !empty($trabajador['foto'])
                        ? $this->url($trabajador['foto'])
                        : null;
                    $esCuadrilla = !empty($trabajador['cuadrilla']);
                    $altaSs = !empty($trabajador['alta_ss'])
                        ? date('d/m/Y', strtotime($trabajador['alta_ss']))
                        : '—';
                ?>
                <tr data-id="<?= $trabajador['id'] ?>">
                    <td>
                        <?php if ($fotoSrc): ?>
                            <img src="<?= htmlspecialchars($fotoSrc) ?>" alt="Foto" class="worker-avatar">
                        <?php else: ?>
                            <div class="worker-avatar worker-avatar--empty">👤</div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($trabajador['nombre'] ?? '-') ?>
                        <?php if ($esCuadrilla): ?>
                            <span class="badge-cuadrilla" title="Cuadrilla">👷</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($trabajador['dni'] ?? '—') ?></td>
                    <td><?= $altaSs ?></td>
                    <td style="text-align:center">
                        <?= $esCuadrilla ? '<span class="cuadrilla-check">✓</span>' : '<span class="cuadrilla-no">—</span>' ?>
                    </td>
                    <td class="actions">
                        <a href="<?= $this->url('/trabajadores/detalle?id=' . $trabajador['id']) ?>" class="btn btn-info btn-sm" title="Ficha individual">📋 Ficha</a>
                        <button class="btn-icon btn-view"
                                onclick="window.location.href='<?= $this->url('/datos/trabajadores?id=' . $trabajador['id']) ?>'"
                                title="Ver detalles">👁️</button>
                        <button class="btn-icon btn-edit"
                                onclick="editWorker(<?= $trabajador['id'] ?>, this)"
                                title="Editar">✏️</button>
                        <button class="btn-icon btn-delete"
                                onclick="deleteWorker(<?= $trabajador['id'] ?>, '<?= htmlspecialchars($trabajador['nombre']) ?>')"
                                title="Eliminar">🗑️</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal de Edición -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>✏️ Editar Trabajador</h3>
                <span class="close" onclick="closeEditModal()">&times;</span>
            </div>
            <form id="editWorkerForm">
                <input type="hidden" id="editId" name="id">

                <!-- Foto de perfil -->
                <div class="form-group foto-group">
                    <label>Foto de perfil:</label>
                    <div class="foto-preview-wrap">
                        <div class="foto-preview" id="editFotoPreview">
                            <span>👤</span>
                        </div>
                        <label class="btn btn-secondary btn-sm foto-btn" for="editFotoInput">
                            Cambiar foto
                            <input type="file" id="editFotoInput" accept="image/*" style="display:none"
                                   onchange="previewFoto(this, 'editFotoPreview')">
                        </label>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="editNombre">Nombre Completo: <span class="required">*</span></label>
                        <input type="text" id="editNombre" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="editDni">DNI (8 números + 1 letra mayúscula):</label>
                        <input type="text" id="editDni" name="dni" maxlength="9" pattern="[0-9]{8}[A-Z]"
                               placeholder="12345678A" oninput="formatearDNI(this)">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="editSs">Número SS:</label>
                        <input type="text" id="editSs" name="ss" maxlength="12">
                    </div>
                    <div class="form-group">
                        <label for="editAltaSs">Alta en Seguridad Social:</label>
                        <input type="date" id="editAltaSs" name="alta_ss">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="editBajaSs">Baja en Seguridad Social:</label>
                        <input type="date" id="editBajaSs" name="baja_ss">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group form-group--checkbox">
                        <label class="checkbox-label">
                            <input type="checkbox" id="editCuadrilla" name="cuadrilla" value="1">
                            <span>👷 Parte de la cuadrilla</span>
                        </label>
                    </div>
                </div>

                <div class="modal-buttons">
                    <button type="button" class="btn-modal btn-secondary" onclick="closeEditModal()">Cancelar</button>
                    <button type="submit" class="btn-modal btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast de notificaciones -->
    <div id="toast" class="toast"></div>
</div>

<style>
/* Foto de perfil */
.foto-group { margin-bottom: 1rem; }
.foto-preview-wrap { display: flex; align-items: center; gap: 12px; margin-top: 6px; }
.foto-preview {
    width: 64px; height: 64px; border-radius: 50%;
    overflow: hidden; background: #e9ecef;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.8rem; border: 2px solid #dee2e6;
    flex-shrink: 0;
}
.foto-preview img { width: 100%; height: 100%; object-fit: cover; }
.foto-preview--empty { background: #f8f9fa; }
.foto-btn { cursor: pointer; }

/* Avatar en tabla */
.worker-avatar {
    width: 36px; height: 36px; border-radius: 50%;
    object-fit: cover; border: 2px solid #dee2e6; display: block;
}
.worker-avatar--empty {
    width: 36px; height: 36px; border-radius: 50%;
    background: #f0f0f0; display: flex; align-items: center;
    justify-content: center; font-size: 1.1rem; border: 2px solid #dee2e6;
}

/* Cuadrilla */
.badge-cuadrilla { margin-left: 4px; font-size: 0.9em; }
.cuadrilla-check { color: #28a745; font-weight: bold; }
.cuadrilla-no { color: #adb5bd; }

/* Checkbox */
.form-group--checkbox { display: flex; flex-direction: column; justify-content: center; }
.checkbox-label { display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 500; }
.checkbox-label input[type="checkbox"] { width: 18px; height: 18px; cursor: pointer; }
.form-hint { color: #6c757d; font-size: 0.8rem; margin-top: 4px; }
.required { color: #dc3545; }
</style>

<!-- Script específico para trabajadores -->
<script src="<?= $this->url('/public/js/trabajadores.js') ?>"></script>
</body>
</html>
