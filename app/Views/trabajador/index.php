<?php $title = 'Mi cuenta'; ?>
<div class="container">
    <div class="page-header">
        <h2>👷 Mi cuenta</h2>
        <p style="color:#6b7280; margin-top:.25rem;"><?= htmlspecialchars($trabajador['nombre']) ?></p>
        <?php if (!empty($nombreEmpresa)): ?>
        <p style="color:#9ca3af; margin-top:.25rem; font-size:.85rem;">Empresa: <strong style="color:#ccc;"><?= htmlspecialchars($nombreEmpresa) ?></strong></p>
        <?php endif; ?>
    </div>

    <!-- Deuda estimada -->
    <div class="card" style="margin-bottom:1.5rem; padding:1.25rem 1.5rem;">
        <div style="display:flex; align-items:center; gap:1rem; flex-wrap:wrap;">
            <div>
                <p style="font-size:.8rem; color:#6b7280; margin:0;">Saldo a tu favor</p>
                <p style="font-size:1.8rem; font-weight:700; margin:.25rem 0 0;
                    color:<?= $deuda > 0 ? '#16a34a' : '#6b7280' ?>;">
                    <?= number_format($deuda, 2, ',', '.') ?> €
                </p>
                <p style="font-size:.75rem; color:#9ca3af; margin:.25rem 0 0;">Calculado a partir de horas trabajadas</p>
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

    <!-- Alta / Baja en Seguridad Social -->
    <div class="card" style="margin-bottom:1.5rem; padding:1.25rem 1.5rem;">
        <h3 style="margin:0 0 .75rem; font-size:.95rem; color:#374151;">🏥 Seguridad Social</h3>
        <div style="display:flex; gap:1.5rem; flex-wrap:wrap;">
            <div>
                <p style="font-size:.8rem; color:#6b7280; margin:0;">Fecha de alta</p>
                <p style="font-size:1.1rem; font-weight:600; margin:.25rem 0 0; color:<?= !empty($trabajador['alta_ss']) ? '#16a34a' : '#6b7280' ?>;">
                    <?= !empty($trabajador['alta_ss']) ? date('d-m-Y', strtotime($trabajador['alta_ss'])) : 'Sin registrar' ?>
                </p>
            </div>
            <div>
                <p style="font-size:.8rem; color:#6b7280; margin:0;">Fecha de baja</p>
                <p style="font-size:1.1rem; font-weight:600; margin:.25rem 0 0; color:<?= !empty($trabajador['baja_ss']) ? '#dc2626' : '#16a34a' ?>;">
                    <?= !empty($trabajador['baja_ss']) ? date('d-m-Y', strtotime($trabajador['baja_ss'])) : 'Activo (sin baja)' ?>
                </p>
            </div>
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
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:.75rem; flex-wrap:wrap; gap:.5rem;">
        <h3 style="margin:0; font-size:1rem; color:#374151;">📋 Trabajos realizados</h3>
        <?php if (!empty($anosDisponibles)): ?>
        <div style="display:flex; align-items:center; gap:.5rem;">
            <label for="year-filter" style="font-size:.85rem; color:#6b7280;">Año:</label>
            <select id="year-filter" onchange="window.location.href='<?= $this->url('/trabajador') ?>?year=' + this.value"
                style="padding:.35rem .5rem; border-radius:6px; border:1px solid #374151; background:#2a2a2a; color:#ccc; font-size:.85rem;">
                <?php foreach ($anosDisponibles as $ano): ?>
                <option value="<?= $ano ?>" <?= $ano == $yearFilter ? 'selected' : '' ?>><?= $ano ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
    </div>

    <div class="card">
        <?php if (empty($historial)): ?>
            <p style="color:#6b7280; padding:1.5rem; text-align:center;">Sin trabajos registrados en <?= $yearFilter ?>.</p>
        <?php else: ?>
        <p style="padding:.75rem 1rem 0; font-size:.8rem; color:#6b7280; margin:0;">
            Mostrando <?= count($historial) ?> de <?= $totalTareas ?> trabajos en <?= $yearFilter ?>
        </p>
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

        <!-- Paginación -->
        <?php if ($totalPaginas > 1): ?>
        <div style="display:flex; align-items:center; justify-content:center; gap:.5rem; padding:1rem; flex-wrap:wrap;">
            <?php if ($page > 1): ?>
            <a href="<?= $this->url('/trabajador') ?>?year=<?= $yearFilter ?>&page=<?= $page - 1 ?>"
               class="btn btn-secondary" style="padding:.4rem .75rem; font-size:.85rem;">← Anterior</a>
            <?php endif; ?>

            <?php
            $inicio = max(1, $page - 2);
            $fin    = min($totalPaginas, $page + 2);
            for ($i = $inicio; $i <= $fin; $i++): ?>
                <?php if ($i == $page): ?>
                <span style="padding:.4rem .65rem; background:#4caf50; color:#fff; border-radius:6px; font-size:.85rem; font-weight:600;"><?= $i ?></span>
                <?php else: ?>
                <a href="<?= $this->url('/trabajador') ?>?year=<?= $yearFilter ?>&page=<?= $i ?>"
                   style="padding:.4rem .65rem; background:#2a2a2a; color:#ccc; border-radius:6px; font-size:.85rem; text-decoration:none; border:1px solid #374151;"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($page < $totalPaginas): ?>
            <a href="<?= $this->url('/trabajador') ?>?year=<?= $yearFilter ?>&page=<?= $page + 1 ?>"
               class="btn btn-secondary" style="padding:.4rem .75rem; font-size:.85rem;">Siguiente →</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
