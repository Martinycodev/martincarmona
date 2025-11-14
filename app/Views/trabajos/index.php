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
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Precio/Hora</th>
                        <th class="actions-column">Acciones</th>
                    </tr>
                </thead>
                <tbody id="trabajosTableBody">
                    <?php foreach ($trabajos as $trabajo): ?>
                    <tr data-id="<?= $trabajo['id'] ?>">
                        <td><?= htmlspecialchars($trabajo['id'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($trabajo['nombre'] ?? '-') ?></td>
                        <td><?= isset($trabajo['precio_hora']) ? '€' . number_format($trabajo['precio_hora'], 2) : '-' ?></td>
                        <td class="actions">
                            <button class="btn-icon btn-edit" onclick="editJob(<?= $trabajo['id'] ?>, this)" title="Editar">
                                ✏️
                            </button>
                            <button class="btn-icon btn-delete" onclick="deleteJob(<?= $trabajo['id'] ?>, '<?= htmlspecialchars($trabajo['nombre']) ?>')" title="Eliminar">
                                🗑️
                            </button>
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
                        <div class="form-group full-width">
                            <label for="editDescripcion">Descripción:</label>
                            <textarea id="editDescripcion" name="descripcion" rows="3"></textarea>
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

    <!-- Script específico para trabajos -->
    <script src="<?= $this->url('/public/js/trabajos.js') ?>"></script>

</body>
</html>
