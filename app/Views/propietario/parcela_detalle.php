<?php $title = 'Parcela — ' . htmlspecialchars($parcela['nombre']); ?>
<div class="container">
    <div class="page-header">
        <h2>📍 <?= htmlspecialchars($parcela['nombre']) ?></h2>
        <div class="header-actions">
            <a href="<?= $this->url('/propietario') ?>" class="btn btn-secondary">← Volver</a>
        </div>
    </div>

    <!-- Datos de la parcela -->
    <div class="card">
        <div class="card-header"><h3>Datos de la Parcela</h3></div>
        <div class="detail-grid">
            <?php if (!empty($parcela['ubicacion'])): ?>
            <div><strong>Ubicación:</strong> <?= htmlspecialchars($parcela['ubicacion']) ?></div>
            <?php endif; ?>
            <div><strong>Olivos:</strong> <?= intval($parcela['olivos']) ?></div>
            <?php if (!empty($parcela['tipo_plantacion'])): ?>
            <div><strong>Tipo de plantación:</strong> <?= htmlspecialchars($parcela['tipo_plantacion']) ?></div>
            <?php endif; ?>
            <?php if (!empty($parcela['riego_secano'])): ?>
            <div><strong>Riego / Secano:</strong> <?= htmlspecialchars($parcela['riego_secano']) ?></div>
            <?php endif; ?>
            <?php if ($parcela['hidrante']): ?>
            <div><strong>Hidrante:</strong> <?= intval($parcela['hidrante']) ?></div>
            <?php endif; ?>
            <?php if (!empty($parcela['descripcion'])): ?>
            <div style="grid-column: 1 / -1;"><strong>Descripción:</strong> <?= htmlspecialchars($parcela['descripcion']) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Trabajos realizados en esta parcela -->
    <div class="card">
        <div class="card-header"><h3>📋 Trabajos realizados</h3></div>
        <?php if (empty($tareas)): ?>
            <p style="padding:1rem;color:#6b7280;text-align:center;">No hay trabajos registrados en esta parcela.</p>
        <?php else: ?>
        <table class="styled-table">
            <thead>
                <tr>
                    <th style="white-space:nowrap;">Fecha</th>
                    <th>Tarea</th>
                    <th>Descripción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tareas as $t): ?>
                <tr>
                    <td style="white-space:nowrap;"><?= htmlspecialchars($t['fecha'] ?? '—') ?></td>
                    <td><strong><?= htmlspecialchars($t['titulo'] ?? '—') ?></strong></td>
                    <td style="font-size:.85rem;color:#6b7280;">
                        <?= htmlspecialchars(mb_substr($t['descripcion'] ?? '', 0, 100)) ?>
                        <?= mb_strlen($t['descripcion'] ?? '') > 100 ? '…' : '' ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
