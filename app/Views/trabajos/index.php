<?php 
$title = 'Gestión de Trabajos';
?>
<div class="container">
        <div class="page-header">
            <h1>🛠️ Gestión de Trabajos</h1>
            <div class="header-actions">
                <button class="btn btn-primary" onclick="openCreateModal()">➕ Nuevo Trabajo</button>
                <a href="<?= $this->url('/datos') ?>" class="btn btn-secondary">← Volver</a>
            </div>
        </div>

        <!-- Sección de Crear Nuevo Trabajo -->
        <div class="create-section" id="createSection">
            <div class="card">
                <div class="card-header">
                    <h3>➕ Crear Nuevo Trabajo</h3>
                    <button class="close-btn" onclick="closeCreateSection()">×</button>
                </div>
                <form id="createJobForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombre">Nombre del Trabajo:</label>
                            <input type="text" id="nombre" name="nombre" required>
                        </div>
                        <div class="form-group">
                            <label for="precio_hora">Precio por Hora (€):</label>
                            <input type="number" id="precio_hora" name="precio_hora" step="0.01" min="0" placeholder="0.00" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="categoria">Categoría:</label>
                            <select id="categoria" name="categoria">
                                <option value="campo">🟢 Campo (laboreo, desbrozar)</option>
                                <option value="tratamiento">🔵 Tratamiento (herbicida, sulfato)</option>
                                <option value="recoleccion">🟠 Recolección (aceituna)</option>
                                <option value="riego">🔷 Riego</option>
                                <option value="poda">🟣 Poda</option>
                                <option value="mantenimiento">🟡 Mantenimiento</option>
                                <option value="otro" selected>⚪ Otro</option>
                            </select>
                        </div>
                        <div class="form-group full-width">
                            <label for="descripcion">Descripción:</label>
                            <textarea id="descripcion" name="descripcion" rows="3" placeholder="Descripción opcional del trabajo"></textarea>
                        </div>
                    </div>

                    <div class="form-buttons">
                        <button type="button" class="btn btn-secondary" onclick="closeCreateSection()">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Crear Trabajo</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de Trabajos -->
        <div class="table-container">
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Precio/Hora</th>
                        <th>Descripción</th>
                        <th>Documento</th>
                    </tr>
                </thead>
                <tbody id="trabajosTableBody">
                    <?php foreach ($trabajos as $trabajo): ?>
                    <tr data-id="<?= $trabajo['id'] ?>"
                        class="clickable-row"
                        onclick="editJob(<?= $trabajo['id'] ?>, this)">
                        <td><?= htmlspecialchars($trabajo['nombre'] ?? '-') ?></td>
                        <td><span class="cat-badge cat-<?= htmlspecialchars($trabajo['categoria'] ?? 'otro') ?>"><?= htmlspecialchars(ucfirst($trabajo['categoria'] ?? 'otro')) ?></span></td>
                        <td>€<?= number_format($trabajo['precio_hora'] ?? 0, 2) ?></td>
                        <td><?= htmlspecialchars($trabajo['descripcion'] ?? '') ?></td>
                        <td>
                            <?php if (!empty($trabajo['documento'])): ?>
                                <a href="<?= $this->url($trabajo['documento']) ?>" target="_blank" class="btn-link" onclick="event.stopPropagation()">📄 Ver</a>
                            <?php else: ?>
                                <span style="color: #666;">—</span>
                            <?php endif; ?>
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
                    <h3>✏️ Editar Trabajo</h3>
                    <span class="close" onclick="closeEditModal()">&times;</span>
                </div>
                <form id="editJobForm">
                    <input type="hidden" id="editId" name="id">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="editNombre">Nombre del Trabajo:</label>
                            <input type="text" id="editNombre" name="nombre" required>
                        </div>
                        <div class="form-group">
                            <label for="editPrecioHora">Precio por Hora (€):</label>
                            <input type="number" id="editPrecioHora" name="precio_hora" step="0.01" min="0" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="editCategoria">Categoría:</label>
                            <select id="editCategoria" name="categoria">
                                <option value="campo">🟢 Campo (laboreo, desbrozar)</option>
                                <option value="tratamiento">🔵 Tratamiento (herbicida, sulfato)</option>
                                <option value="recoleccion">🟠 Recolección (aceituna)</option>
                                <option value="riego">🔷 Riego</option>
                                <option value="poda">🟣 Poda</option>
                                <option value="mantenimiento">🟡 Mantenimiento</option>
                                <option value="otro">⚪ Otro</option>
                            </select>
                        </div>
                        <div class="form-group full-width">
                            <label for="editDescripcion">Descripción:</label>
                            <textarea id="editDescripcion" name="descripcion" rows="3"></textarea>
                        </div>
                    </div>

                    <!-- Sección de documento con método de trabajo -->
                    <div class="form-row">
                        <div class="form-group full-width">
                            <label>📄 Documento de método de trabajo:</label>
                            <div id="documentoPreview" style="margin-bottom: 10px; display: none; align-items: center; gap: 10px;">
                                <a id="documentoLink" href="#" target="_blank" class="btn-link" style="margin-right: 10px;">📄 Ver documento actual</a>
                                <button type="button" class="btn btn-sm btn-danger-outline" onclick="eliminarDocumento()">Eliminar</button>
                            </div>
                            <div id="documentoUpload">
                                <input type="file" id="editDocumento" accept=".jpg,.jpeg,.png,.webp,.pdf" style="display: none;" onchange="subirDocumento()">
                                <button type="button" class="btn btn-sm btn-secondary" onclick="document.getElementById('editDocumento').click()">
                                    📎 Subir documento
                                </button>
                                <span style="color: #888; font-size: 0.85em; margin-left: 8px;">JPG, PNG, WebP o PDF (máx. 10MB)</span>
                            </div>
                            <div id="documentoProgress" style="display: none; margin-top: 8px;">
                                <span style="color: #4caf50;">⏳ Subiendo documento...</span>
                            </div>
                        </div>
                    </div>

                    <div class="modal-buttons">
                        <button type="button" class="btn-modal btn-danger-outline"
                                onclick="deleteJob(document.getElementById('editId').value, document.getElementById('editNombre').value)">
                            Eliminar
                        </button>
                        <button type="button" class="btn-modal btn-secondary" onclick="closeEditModal()">Cancelar</button>
                        <button type="submit" class="btn-modal btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Toast de notificaciones -->
        <div id="toast" class="toast"></div>

        <!-- Script específico para trabajos -->
        <script src="<?= $this->url('/public/js/trabajos.js') ?>"></script>
    </div>
