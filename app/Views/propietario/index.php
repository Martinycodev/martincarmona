<?php $title = 'Mis Parcelas'; ?>
<div class="container">
    <div class="page-header">
        <h2>🌳 Mis parcelas</h2>
        <p style="color:#6b7280; margin-top:.25rem;">
            <?= htmlspecialchars($propietario['nombre']) ?>
            <?= $propietario['apellidos'] ? htmlspecialchars($propietario['apellidos']) : '' ?>
        </p>
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
                <tr>
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
    <h3 style="margin-bottom:.75rem; font-size:1rem; color:#374151;">📋 Trabajos realizados</h3>
    <div class="card">
        <?php if (empty($tareas)): ?>
            <p style="color:#6b7280; padding:1.5rem; text-align:center;">Sin trabajos registrados en tus parcelas.</p>
        <?php else: ?>
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
                    <td style="white-space:nowrap;"><?= htmlspecialchars($t['fecha'] ?? '—') ?></td>
                    <td><strong><?= htmlspecialchars($t['titulo']) ?></strong></td>
                    <td style="font-size:.85rem;"><?= htmlspecialchars($t['parcelas_nombres'] ?? '—') ?></td>
                    <td style="font-size:.85rem; color:#6b7280;"><?= htmlspecialchars(mb_substr($t['descripcion'] ?? '', 0, 80)) ?><?= mb_strlen($t['descripcion'] ?? '') > 80 ? '…' : '' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
