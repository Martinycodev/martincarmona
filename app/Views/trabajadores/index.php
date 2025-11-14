<?php 
$title = 'Gestión de Trabajadores';
?>
<div class="container">
        <div class="page-header">
            <h1>👷‍♂️ Gestión de Trabajadores</h1>
            <div class="header-actions">
                <button class="btn btn-primary" onclick="openCreateModal()">➕ Nuevo Trabajador</button>
                <button class="btn btn-secondary" onclick="testBuildUrl()">🧪 Test URL</button>
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
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombre">Nombre Completo:</label>
                            <input type="text" id="nombre" name="nombre" required>
                        </div>
                        <div class="form-group">
                            <label for="dni">DNI (8 números + 1 letra mayúscula):</label>
                            <input type="text" id="dni" name="dni" maxlength="9" pattern="[0-9]{8}[A-Z]" placeholder="12345678A" title="Formato: 8 números seguidos de 1 letra mayúscula (ej: 12345678A)" oninput="formatearDNI(this)">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="ss">Número SS:</label>
                            <input type="text" id="ss" name="ss" maxlength="12">
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
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>DNI</th>
                        <th>SS</th>

                        <th class="actions-column">Acciones</th>
                    </tr>
                </thead>
                <tbody id="trabajadoresTableBody">
                    <?php foreach ($trabajadores as $trabajador): ?>
                    <tr data-id="<?= $trabajador['id'] ?>">
                        <td><?= htmlspecialchars($trabajador['id'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($trabajador['nombre'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($trabajador['dni'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($trabajador['ss'] ?? '-') ?></td>

                        <td class="actions">
                            <button class="btn-icon btn-view" onclick="window.location.href='<?= $this->url('/datos/trabajadores?id=' . $trabajador['id']) ?>'" title="Ver detalles">
                                👁️
                            </button>
                            <button class="btn-icon btn-edit" onclick="editWorker(<?= $trabajador['id'] ?>, this)" title="Editar">
                                ✏️
                            </button>
                            <button class="btn-icon btn-delete" onclick="deleteWorker(<?= $trabajador['id'] ?>, '<?= htmlspecialchars($trabajador['nombre']) ?>')" title="Eliminar">
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
                    <h3>✏️ Editar Trabajador</h3>
                    <span class="close" onclick="closeEditModal()">&times;</span>
                </div>
                <form id="editWorkerForm">
                    <input type="hidden" id="editId" name="id">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="editNombre">Nombre Completo:</label>
                            <input type="text" id="editNombre" name="nombre" required>
                        </div>
                        <div class="form-group">
                            <label for="editDni">DNI (8 números + 1 letra mayúscula):</label>
                            <input type="text" id="editDni" name="dni" maxlength="9" pattern="[0-9]{8}[A-Z]" placeholder="12345678A" title="Formato: 8 números seguidos de 1 letra mayúscula (ej: 12345678A)" oninput="formatearDNI(this)">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="editSs">Número SS:</label>
                            <input type="text" id="editSs" name="ss" maxlength="12">
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

    <!-- Script específico para trabajadores -->
    <script src="<?= $this->url('/public/js/trabajadores.js') ?>"></script>

</body>
</html>
