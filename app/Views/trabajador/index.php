<?php $title = 'Mi cuenta'; ?>
<div class="container">
    <div class="page-header">
        <h2>👷 Mi cuenta</h2>
        <p style="color:#6b7280; margin-top:.25rem;"><?= htmlspecialchars($trabajador['nombre']) ?></p>
    </div>

    <!-- Deuda estimada -->
    <div class="card" style="margin-bottom:1.5rem; padding:1.25rem 1.5rem;">
        <div style="display:flex; align-items:center; gap:1rem; flex-wrap:wrap;">
            <div>
                <p style="font-size:.8rem; color:#6b7280; margin:0;">Deuda estimada</p>
                <p style="font-size:1.8rem; font-weight:700; margin:.25rem 0 0;
                    color:<?= $deuda > 0 ? '#dc2626' : '#16a34a' ?>;">
                    <?= number_format($deuda, 2, ',', '.') ?> €
                </p>
                <p style="font-size:.75rem; color:#9ca3af; margin:.25rem 0 0;">Calculada a partir de horas trabajadas</p>
            </div>
            <?php if ($mesesPendientes > 0): ?>
            <div style="background:#fef2f2; border:1px solid #fecaca; border-radius:.5rem; padding:.5rem .75rem;">
                <p style="margin:0; font-size:.85rem; color:#dc2626;">
                    <?= $mesesPendientes ?> mes<?= $mesesPendientes > 1 ? 'es' : '' ?> sin liquidar
                </p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tareas pendientes -->
    <?php if (!empty($tareasPendientes)): ?>
    <h3 style="margin-bottom:.75rem; font-size:1rem; color:#374151;">⏳ Tareas pendientes asignadas</h3>
    <div class="card" style="margin-bottom:1.5rem;">
        <table class="styled-table">
            <thead>
                <tr>
                    <th>Tarea</th>
                    <th>Parcela(s)</th>
                    <th>Descripción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tareasPendientes as $t): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($t['titulo']) ?></strong></td>
                    <td style="font-size:.85rem;"><?= htmlspecialchars($t['parcelas'] ?? '—') ?></td>
                    <td style="font-size:.85rem; color:#6b7280;"><?= htmlspecialchars(mb_substr($t['descripcion'] ?? '', 0, 80)) ?><?= mb_strlen($t['descripcion'] ?? '') > 80 ? '…' : '' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- Historial -->
    <h3 style="margin-bottom:.75rem; font-size:1rem; color:#374151;">📋 Últimos trabajos realizados</h3>
    <div class="card">
        <?php if (empty($historial)): ?>
            <p style="color:#6b7280; padding:1.5rem; text-align:center;">Sin trabajos registrados aún.</p>
        <?php else: ?>
        <table class="styled-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Tarea</th>
                    <th>Parcela(s)</th>
                    <th>Horas</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($historial as $h): ?>
                <tr>
                    <td style="white-space:nowrap;"><?= !empty($h['fecha']) ? date('d-m-Y', strtotime($h['fecha'])) : '—' ?></td>
                    <td><strong><?= htmlspecialchars($h['titulo']) ?></strong></td>
                    <td style="font-size:.85rem;"><?= htmlspecialchars($h['parcelas'] ?? '—') ?></td>
                    <td><?= $h['horas_asignadas'] !== null ? number_format($h['horas_asignadas'], 1, ',', '.') . ' h' : '—' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
