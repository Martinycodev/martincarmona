<?php
$title = 'Gestión de Tareas';
?>
<div class="container">
    <div class="page-header">
        <h1>📝 Gestión de Tareas</h1>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="window.taskSidebar && window.taskSidebar.open()">✏️ Nueva Tarea</button>
            <a href="<?= $this->url('/busqueda') ?>" class="btn btn-info">🔍 Búsqueda Avanzada</a>
            <a href="<?= $this->url('/datos') ?>" class="btn btn-secondary">← Volver</a>
        </div>
    </div>

    <!-- Tabla de Tareas -->
    <div class="table-container">
        <table class="styled-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Título</th>
                    <th>Trabajo</th>
                    <th>Horas</th>
                </tr>
            </thead>
            <tbody id="tareasTableBody">
                <?php if (!empty($tareas)): ?>
                    <?php foreach ($tareas as $tarea): ?>
                        <tr data-id="<?= $tarea['id'] ?>"
                            style="cursor:pointer;"
                            onclick="window.taskSidebar && window.taskSidebar.open(<?= $tarea['id'] ?>)">
                            <td><?= htmlspecialchars(date('d-m-Y', strtotime($tarea['fecha']))) ?></td>
                            <td class="description-cell">
                                <?= htmlspecialchars($tarea['titulo'] ?: ($tarea['descripcion'] ?? 'Sin título')) ?>
                            </td>
                            <td>
                                <?php if (!empty($tarea['trabajos'])): ?>
                                    <?= htmlspecialchars(implode(', ', array_column($tarea['trabajos'], 'nombre'))) ?>
                                <?php else: ?>
                                    <span style="color:#666;">—</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $tarea['horas'] ? number_format($tarea['horas'], 1) . 'h' : '0h' ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="no-data">
                            <div class="no-tareas">
                                <h3>📝 No hay tareas registradas</h3>
                                <p>Comienza creando tu primera tarea para organizar el trabajo del campo.</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <?php if (isset($pagination) && $pagination['totalPages'] > 1): ?>
        <div class="pagination-container">
            <div class="pagination-controls">
                <?php if ($pagination['hasPrev']): ?>
                    <a href="<?= $this->url('/tareas') ?>?page=1" class="btn-pagination btn-first" title="Primera página">⏮️</a>
                <?php else: ?>
                    <span class="btn-pagination btn-first disabled">⏮️</span>
                <?php endif; ?>

                <?php if ($pagination['hasPrev']): ?>
                    <a href="<?= $this->url('/tareas') ?>?page=<?= $pagination['currentPage'] - 1 ?>" class="btn-pagination btn-prev" title="Página anterior">◀️</a>
                <?php else: ?>
                    <span class="btn-pagination btn-prev disabled">◀️</span>
                <?php endif; ?>

                <div class="pagination-numbers">
                    <?php
                    $start = max(1, $pagination['currentPage'] - 2);
                    $end   = min($pagination['totalPages'], $pagination['currentPage'] + 2);
                    if ($end - $start < 4) {
                        if ($start == 1) {
                            $end = min($pagination['totalPages'], $start + 4);
                        } else {
                            $start = max(1, $end - 4);
                        }
                    }
                    for ($i = $start; $i <= $end; $i++): ?>
                        <?php if ($i == $pagination['currentPage']): ?>
                            <span class="btn-pagination current"><?= $i ?></span>
                        <?php else: ?>
                            <a href="<?= $this->url('/tareas') ?>?page=<?= $i ?>" class="btn-pagination"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>

                <?php if ($pagination['hasNext']): ?>
                    <a href="<?= $this->url('/tareas') ?>?page=<?= $pagination['currentPage'] + 1 ?>" class="btn-pagination btn-next" title="Página siguiente">▶️</a>
                <?php else: ?>
                    <span class="btn-pagination btn-next disabled">▶️</span>
                <?php endif; ?>

                <?php if ($pagination['hasNext']): ?>
                    <a href="<?= $this->url('/tareas') ?>?page=<?= $pagination['totalPages'] ?>" class="btn-pagination btn-last" title="Última página">⏭️</a>
                <?php else: ?>
                    <span class="btn-pagination btn-last disabled">⏭️</span>
                <?php endif; ?>
            </div>

            <div class="pagination-info">
                <span>Mostrando página <?= $pagination['currentPage'] ?> de <?= $pagination['totalPages'] ?></span>
                <span>(<?= $pagination['totalItems'] ?> tareas en total)</span>
            </div>
        </div>
    <?php endif; ?>

</div>

