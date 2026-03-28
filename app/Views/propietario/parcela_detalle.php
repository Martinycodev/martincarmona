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
        <div class="card-header" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:.5rem;">
            <h3 style="margin:0;"><?= emoji('clipboard') ?> Trabajos realizados</h3>
            <?php if (!empty($anosDisponibles)): ?>
            <div style="display:flex; align-items:center; gap:.5rem;">
                <label for="year-filter" style="font-size:.85rem; color:#6b7280;">Año:</label>
                <select id="year-filter" onchange="window.location.href='<?= $this->url('/propietario/parcela/' . $parcela['id']) ?>?year=' + this.value"
                    style="padding:.35rem .5rem; border-radius:6px; border:1px solid #374151; background:#2a2a2a; color:#ccc; font-size:.85rem;">
                    <?php foreach ($anosDisponibles as $ano): ?>
                    <option value="<?= $ano ?>" <?= $ano == $yearFilter ? 'selected' : '' ?>><?= $ano ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
        </div>

        <?php if (empty($tareas)): ?>
            <p style="padding:1rem;color:#6b7280;text-align:center;">No hay trabajos registrados en <?= $yearFilter ?>.</p>
        <?php else: ?>
        <p style="padding:.75rem 1rem 0; font-size:.8rem; color:#6b7280; margin:0;">
            Mostrando <?= count($tareas) ?> de <?= $totalTareas ?> trabajos en <?= $yearFilter ?>
        </p>
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
                    <td style="white-space:nowrap;"><?= !empty($t['fecha']) ? date('d-m-Y', strtotime($t['fecha'])) : '—' ?></td>
                    <td><strong><?= htmlspecialchars($t['titulo'] ?? '—') ?></strong></td>
                    <td style="font-size:.85rem;color:#6b7280;">
                        <?= htmlspecialchars(mb_substr($t['descripcion'] ?? '', 0, 100)) ?>
                        <?= mb_strlen($t['descripcion'] ?? '') > 100 ? '…' : '' ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Paginación -->
        <?php if ($totalPaginas > 1): ?>
        <div style="display:flex; align-items:center; justify-content:center; gap:.5rem; padding:1rem; flex-wrap:wrap;">
            <?php if ($page > 1): ?>
            <a href="<?= $this->url('/propietario/parcela/' . $parcela['id']) ?>?year=<?= $yearFilter ?>&page=<?= $page - 1 ?>"
               class="btn btn-secondary" style="padding:.4rem .75rem; font-size:.85rem;">← Anterior</a>
            <?php endif; ?>

            <?php
            $inicio = max(1, $page - 2);
            $fin    = min($totalPaginas, $page + 2);
            for ($i = $inicio; $i <= $fin; $i++): ?>
                <?php if ($i == $page): ?>
                <span style="padding:.4rem .65rem; background:#4caf50; color:#fff; border-radius:6px; font-size:.85rem; font-weight:600;"><?= $i ?></span>
                <?php else: ?>
                <a href="<?= $this->url('/propietario/parcela/' . $parcela['id']) ?>?year=<?= $yearFilter ?>&page=<?= $i ?>"
                   style="padding:.4rem .65rem; background:#2a2a2a; color:#ccc; border-radius:6px; font-size:.85rem; text-decoration:none; border:1px solid #374151;"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($page < $totalPaginas): ?>
            <a href="<?= $this->url('/propietario/parcela/' . $parcela['id']) ?>?year=<?= $yearFilter ?>&page=<?= $page + 1 ?>"
               class="btn btn-secondary" style="padding:.4rem .75rem; font-size:.85rem;">Siguiente →</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
