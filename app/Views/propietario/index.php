<?php $title = 'Mis Parcelas'; ?>
<div class="container">
    <div class="page-header">
        <h2><?= emoji('tree', '1.2rem') ?> Mis parcelas</h2>
        <p style="color:#6b7280; margin-top:.25rem;">
            <?= htmlspecialchars($propietario['nombre']) ?>
            <?= $propietario['apellidos'] ? htmlspecialchars($propietario['apellidos']) : '' ?>
        </p>
        <?php if (!empty($nombreEmpresa)): ?>
        <p style="color:#9ca3af; margin-top:.25rem; font-size:.85rem;">Empresa: <strong style="color:#ccc;"><?= htmlspecialchars($nombreEmpresa) ?></strong></p>
        <?php endif; ?>
    </div>

    <!-- Parcelas -->
    <h3 style="margin-bottom:.75rem; font-size:1rem; color:#374151;">📍 Parcelas</h3>
    <div class="card" style="margin-bottom:1.5rem;">
        <?php if (empty($parcelas)): ?>
            <p style="color:#6b7280; padding:1.5rem; text-align:center;">No tienes parcelas asignadas.</p>
        <?php else: ?>
        <table class="styled-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Ubicación</th>
                    <th>Olivos</th>
                    <th>Tipo</th>
                    <th>Riego</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($parcelas as $p): ?>
                <tr onclick="window.location.href='<?= $this->url('/propietario/parcela/' . $p['id']) ?>'"
                    style="cursor:pointer;">
                    <td><strong><?= htmlspecialchars($p['nombre']) ?></strong></td>
                    <td><?= htmlspecialchars($p['ubicacion'] ?? '—') ?></td>
                    <td><?= $p['olivos'] ? intval($p['olivos']) : '—' ?></td>
                    <td><?= htmlspecialchars($p['tipo_plantacion'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($p['riego_secano'] ?? '—') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <!-- Tareas realizadas -->
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:.75rem; flex-wrap:wrap; gap:.5rem;">
        <h3 style="margin:0; font-size:1rem; color:#374151;"><?= emoji('clipboard') ?> Trabajos realizados</h3>
        <?php if (!empty($anosDisponibles)): ?>
        <div style="display:flex; align-items:center; gap:.5rem;">
            <label for="year-filter" style="font-size:.85rem; color:#6b7280;">Año:</label>
            <select id="year-filter" onchange="window.location.href='<?= $this->url('/propietario') ?>?year=' + this.value"
                style="padding:.35rem .5rem; border-radius:6px; border:1px solid #374151; background:#2a2a2a; color:#ccc; font-size:.85rem;">
                <?php foreach ($anosDisponibles as $ano): ?>
                <option value="<?= $ano ?>" <?= $ano == $yearFilter ? 'selected' : '' ?>><?= $ano ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
    </div>

    <div class="card">
        <?php if (empty($tareas)): ?>
            <p style="color:#6b7280; padding:1.5rem; text-align:center;">Sin trabajos registrados en <?= $yearFilter ?>.</p>
        <?php else: ?>
        <!-- Info de resultados -->
        <p style="padding:.75rem 1rem 0; font-size:.8rem; color:#6b7280; margin:0;">
            Mostrando <?= count($tareas) ?> de <?= $totalTareas ?> trabajos en <?= $yearFilter ?>
        </p>
        <table class="styled-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Tarea</th>
                    <th>Parcela(s)</th>
                    <th>Descripción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tareas as $t): ?>
                <tr>
                    <td style="white-space:nowrap;"><?= !empty($t['fecha']) ? date('d-m-Y', strtotime($t['fecha'])) : '—' ?></td>
                    <td><strong><?= htmlspecialchars($t['titulo']) ?></strong></td>
                    <td style="font-size:.85rem;"><?= htmlspecialchars($t['parcelas_nombres'] ?? '—') ?></td>
                    <td style="font-size:.85rem; color:#6b7280;"><?= htmlspecialchars(mb_substr($t['descripcion'] ?? '', 0, 80)) ?><?= mb_strlen($t['descripcion'] ?? '') > 80 ? '…' : '' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Paginación -->
        <?php if ($totalPaginas > 1): ?>
        <div style="display:flex; align-items:center; justify-content:center; gap:.5rem; padding:1rem; flex-wrap:wrap;">
            <?php if ($page > 1): ?>
            <a href="<?= $this->url('/propietario') ?>?year=<?= $yearFilter ?>&page=<?= $page - 1 ?>"
               class="btn btn-secondary" style="padding:.4rem .75rem; font-size:.85rem;">← Anterior</a>
            <?php endif; ?>

            <?php
            // Mostrar máximo 5 páginas alrededor de la actual
            $inicio = max(1, $page - 2);
            $fin    = min($totalPaginas, $page + 2);
            for ($i = $inicio; $i <= $fin; $i++): ?>
                <?php if ($i == $page): ?>
                <span style="padding:.4rem .65rem; background:#4caf50; color:#fff; border-radius:6px; font-size:.85rem; font-weight:600;"><?= $i ?></span>
                <?php else: ?>
                <a href="<?= $this->url('/propietario') ?>?year=<?= $yearFilter ?>&page=<?= $i ?>"
                   style="padding:.4rem .65rem; background:#2a2a2a; color:#ccc; border-radius:6px; font-size:.85rem; text-decoration:none; border:1px solid #374151;"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($page < $totalPaginas): ?>
            <a href="<?= $this->url('/propietario') ?>?year=<?= $yearFilter ?>&page=<?= $page + 1 ?>"
               class="btn btn-secondary" style="padding:.4rem .75rem; font-size:.85rem;">Siguiente →</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
