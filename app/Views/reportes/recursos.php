<?php $title = 'Recursos - Reportes'; ?>
<div class="container">
<div class="reports-container">
    <!-- Header -->
    <div class="reports-header">
        <div class="reports-header-left">
            <a href="<?= $this->url('/reportes') ?>" style="color:#4caf50; text-decoration:none; margin-right:12px;">&larr; Reportes</a>
            <div>
                <h1>Gestión de Recursos</h1>
                <p class="reports-subtitle">Vehículos, herramientas y alertas</p>
            </div>
        </div>
    </div>

    <!-- KPIs -->
    <div class="kpis-section">
        <div class="kpis-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
            <div class="kpi-card kpi-primary">
                <div class="kpi-content" style="text-align:center;">
                    <div class="kpi-value"><?= $total_vehiculos ?></div>
                    <div class="kpi-label">Vehículos</div>
                </div>
            </div>
            <div class="kpi-card kpi-info">
                <div class="kpi-content" style="text-align:center;">
                    <div class="kpi-value"><?= $total_herramientas ?></div>
                    <div class="kpi-label">Herramientas</div>
                </div>
            </div>
            <?php if ($alertas_vehiculos > 0): ?>
            <div class="kpi-card kpi-economy" style="border-left: 3px solid #f44336;">
                <div class="kpi-content" style="text-align:center;">
                    <div class="kpi-value" style="color:#f44336;"><?= $alertas_vehiculos ?></div>
                    <div class="kpi-label">Alertas Activas</div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Vehículos -->
    <div class="analytics-section" style="margin-top:20px;">
        <div class="analytics-card" style="max-width:100%;">
            <h3>Vehículos</h3>
            <?php if (empty($vehiculos)): ?>
                <p style="color:#888; text-align:center; padding:20px;">Sin vehículos registrados</p>
            <?php else: ?>
            <div style="overflow-x:auto;">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Matrícula</th>
                            <th>Seguro</th>
                            <th>Precio Seguro</th>
                            <th>Fecha Matriculación</th>
                            <th>ITV</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vehiculos as $v): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($v['nombre']) ?></strong></td>
                            <td><?= htmlspecialchars($v['matricula'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($v['seguro'] ?? '—') ?></td>
                            <td><?= !empty($v['precio_seguro']) ? '&euro;' . number_format($v['precio_seguro'], 2) : '—' ?></td>
                            <td><?= !empty($v['fecha_matriculacion']) ? date('d/m/Y', strtotime($v['fecha_matriculacion'])) : '—' ?></td>
                            <td>
                                <?php if (!empty($v['pasa_itv'])): ?>
                                    <?= date('d/m/Y', strtotime($v['pasa_itv'])) ?>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (empty($v['alertas'])): ?>
                                    <span class="status-badge status-active">OK</span>
                                <?php else: ?>
                                    <?php foreach ($v['alertas'] as $a): ?>
                                    <span class="status-badge status-<?= $a['tipo'] === 'error' ? 'error' : ($a['tipo'] === 'warning' ? 'warning' : 'info') ?>">
                                        <?= htmlspecialchars($a['msg']) ?>
                                    </span>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Herramientas -->
    <div class="analytics-section" style="margin-top:20px;">
        <div class="analytics-card" style="max-width:100%;">
            <h3>Herramientas</h3>
            <?php if (empty($herramientas)): ?>
                <p style="color:#888; text-align:center; padding:20px;">Sin herramientas registradas</p>
            <?php else: ?>
            <div style="overflow-x:auto;">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Cantidad</th>
                            <th>Precio</th>
                            <th>Fecha Compra</th>
                            <th>Descripción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($herramientas as $h): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($h['nombre']) ?></strong></td>
                            <td><?= intval($h['cantidad'] ?? 1) ?></td>
                            <td><?= !empty($h['precio']) ? '&euro;' . number_format($h['precio'], 2) : '—' ?></td>
                            <td><?= !empty($h['fecha_compra']) ? date('d/m/Y', strtotime($h['fecha_compra'])) : '—' ?></td>
                            <td style="max-width:300px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?= htmlspecialchars($h['descripcion'] ?? '') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Resumen de costos de seguros -->
    <?php
    $totalSeguros = 0;
    foreach ($vehiculos as $v) {
        $totalSeguros += floatval($v['precio_seguro'] ?? 0);
    }
    if ($totalSeguros > 0):
    ?>
    <div class="analytics-section" style="margin-top:20px;">
        <div class="analytics-card" style="max-width:400px;">
            <h3>Costo Anual en Seguros</h3>
            <div style="text-align:center; padding:20px;">
                <div style="font-size:32px; font-weight:700; color:#2196f3;">&euro;<?= number_format($totalSeguros, 2) ?></div>
                <div style="color:#888; margin-top:8px;">Total seguros de vehículos</div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.report-table { width:100%; border-collapse:collapse; margin-top:12px; }
.report-table th, .report-table td { padding:10px 14px; text-align:left; border-bottom:1px solid #333; }
.report-table th { background:#1a1a1a; color:#a8d5ab; font-weight:600; font-size:13px; text-transform:uppercase; letter-spacing:.5px; }
.report-table tbody tr:hover { background:rgba(76,175,80,.08); }
.status-badge { padding:3px 10px; border-radius:12px; font-size:12px; font-weight:500; display:inline-block; margin:2px 0; }
.status-active { background:rgba(76,175,80,.2); color:#4caf50; }
.status-error { background:rgba(244,67,54,.2); color:#f44336; }
.status-warning { background:rgba(255,152,0,.2); color:#ff9800; }
.status-info { background:rgba(33,150,243,.2); color:#2196f3; }
</style>

</div>
