<?php $title = 'Economía - Reportes'; ?>
<div class="container">
<div class="reports-container">
    <!-- Header -->
    <div class="reports-header">
        <div class="reports-header-left">
            <a href="<?= $this->url('/reportes') ?>" style="color:#4caf50; text-decoration:none; margin-right:12px;">&larr; Reportes</a>
            <div>
                <h1>Análisis Económico</h1>
                <p class="reports-subtitle">Ingresos, gastos y evolución financiera — <?= $anio ?></p>
            </div>
        </div>
        <div class="reports-header-right">
            <form class="periodo-selector" method="get" action="<?= $this->url('/reportes/economia') ?>">
                <select name="anio" onchange="this.form.submit()">
                    <?php foreach ($anios_disponibles as $a): ?>
                    <option value="<?= $a ?>" <?= $a == $anio ? 'selected' : '' ?>><?= $a ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    </div>

    <!-- KPIs -->
    <div class="kpis-section">
        <div class="kpis-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
            <div class="kpi-card kpi-success">
                <div class="kpi-content" style="text-align:center;">
                    <div class="kpi-value" style="color:#4caf50;">&euro;<?= number_format($total_ingresos, 2) ?></div>
                    <div class="kpi-label">Ingresos <?= $anio ?></div>
                </div>
            </div>
            <div class="kpi-card kpi-economy" style="border-left: 3px solid #f44336;">
                <div class="kpi-content" style="text-align:center;">
                    <div class="kpi-value" style="color:#f44336;">&euro;<?= number_format($total_gastos, 2) ?></div>
                    <div class="kpi-label">Gastos <?= $anio ?></div>
                </div>
            </div>
            <div class="kpi-card <?= $balance_anual >= 0 ? 'kpi-primary' : 'kpi-economy' ?>" style="border-left: 3px solid <?= $balance_anual >= 0 ? '#4caf50' : '#f44336' ?>;">
                <div class="kpi-content" style="text-align:center;">
                    <div class="kpi-value" style="color:<?= $balance_anual >= 0 ? '#4caf50' : '#f44336' ?>;">
                        <?= $balance_anual >= 0 ? '+' : '' ?>&euro;<?= number_format($balance_anual, 2) ?>
                    </div>
                    <div class="kpi-label">Balance <?= $anio ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico evolución mensual -->
    <div class="charts-section">
        <div class="chart-card" style="max-width:100%;">
            <h3>Evolución Mensual — <?= $anio ?></h3>
            <canvas id="chart-economia" height="100"></canvas>
        </div>
    </div>

    <!-- Gráfico balance acumulado -->
    <div class="charts-section" style="margin-top:20px;">
        <div class="chart-card" style="max-width:100%;">
            <h3>Balance Acumulado — <?= $anio ?></h3>
            <canvas id="chart-balance" height="80"></canvas>
        </div>
    </div>

    <!-- Desglose por categoría -->
    <div class="analytics-section" style="margin-top:20px;">
        <div class="analytics-row">
            <!-- Gastos por categoría -->
            <div class="analytics-card">
                <h3 style="color:#f44336;">Gastos por Categoría</h3>
                <?php if (empty($categorias_gasto)): ?>
                    <p style="color:#888; text-align:center; padding:20px;">Sin gastos registrados</p>
                <?php else: ?>
                <div class="trabajos-list">
                    <?php
                    $totalCatGasto = array_sum(array_column($categorias_gasto, 'total')) ?: 1;
                    foreach ($categorias_gasto as $cat):
                        $pct = round($cat['total'] / $totalCatGasto * 100, 1);
                    ?>
                    <div class="trabajo-item">
                        <div class="trabajo-info">
                            <h4><?= htmlspecialchars($cat['categoria']) ?></h4>
                            <div style="background:#333; border-radius:4px; height:6px; margin-top:6px;">
                                <div style="background:#f44336; height:6px; border-radius:4px; width:<?= $pct ?>%;"></div>
                            </div>
                        </div>
                        <div class="trabajo-average">
                            <span class="average-value" style="color:#f44336;">&euro;<?= number_format($cat['total'], 2) ?></span>
                            <span class="average-label"><?= $pct ?>%</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Ingresos por categoría -->
            <div class="analytics-card">
                <h3 style="color:#4caf50;">Ingresos por Categoría</h3>
                <?php if (empty($categorias_ingreso)): ?>
                    <p style="color:#888; text-align:center; padding:20px;">Sin ingresos registrados</p>
                <?php else: ?>
                <div class="trabajos-list">
                    <?php
                    $totalCatIng = array_sum(array_column($categorias_ingreso, 'total')) ?: 1;
                    foreach ($categorias_ingreso as $cat):
                        $pct = round($cat['total'] / $totalCatIng * 100, 1);
                    ?>
                    <div class="trabajo-item">
                        <div class="trabajo-info">
                            <h4><?= htmlspecialchars($cat['categoria']) ?></h4>
                            <div style="background:#333; border-radius:4px; height:6px; margin-top:6px;">
                                <div style="background:#4caf50; height:6px; border-radius:4px; width:<?= $pct ?>%;"></div>
                            </div>
                        </div>
                        <div class="trabajo-average">
                            <span class="average-value" style="color:#4caf50;">&euro;<?= number_format($cat['total'], 2) ?></span>
                            <span class="average-label"><?= $pct ?>%</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Top movimientos -->
    <?php if (!empty($top_movimientos)): ?>
    <div class="analytics-section" style="margin-top:20px;">
        <div class="analytics-card" style="max-width:100%;">
            <h3>Movimientos Más Importantes — <?= $anio ?></h3>
            <div style="overflow-x:auto;">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Concepto</th>
                            <th>Tipo</th>
                            <th>Categoría</th>
                            <th>Importe</th>
                            <th>Cuenta</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($top_movimientos as $mov): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($mov['fecha'])) ?></td>
                            <td><?= htmlspecialchars($mov['concepto']) ?></td>
                            <td>
                                <span style="color:<?= $mov['tipo'] === 'ingreso' ? '#4caf50' : '#f44336' ?>; font-weight:600;">
                                    <?= ucfirst($mov['tipo']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($labels_categoria[$mov['categoria']] ?? ucfirst($mov['categoria'])) ?></td>
                            <td style="font-weight:600; color:<?= $mov['tipo'] === 'ingreso' ? '#4caf50' : '#f44336' ?>;">
                                <?= $mov['tipo'] === 'ingreso' ? '+' : '-' ?>&euro;<?= number_format($mov['importe'], 2) ?>
                            </td>
                            <td><?= ucfirst($mov['cuenta'] ?? '') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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
.periodo-selector select { background:#2a2a2a; color:#fff; border:1px solid #444; border-radius:8px; padding:8px 12px; font-size:14px; cursor:pointer; }
.periodo-selector select:focus { border-color:#4caf50; outline:none; }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function initEconomiaCharts() {
    // Gráfico de evolución mensual (barras ingresos/gastos)
    var ctx1 = document.getElementById('chart-economia');
    if (ctx1) {
        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: <?= json_encode($chart_labels) ?>,
                datasets: [
                    {
                        label: 'Ingresos',
                        data: <?= json_encode($chart_ingresos) ?>,
                        backgroundColor: 'rgba(76, 175, 80, 0.7)',
                        borderRadius: 4
                    },
                    {
                        label: 'Gastos',
                        data: <?= json_encode($chart_gastos) ?>,
                        backgroundColor: 'rgba(244, 67, 54, 0.7)',
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: { legend: { labels: { color: '#ccc' } } },
                scales: {
                    x: { ticks: { color: '#888' }, grid: { color: '#333' } },
                    y: { ticks: { color: '#888', callback: function(v) { return '\u20AC' + v; } }, grid: { color: '#333' } }
                }
            }
        });
    }

    // Gráfico de balance acumulado (línea)
    var ctx2 = document.getElementById('chart-balance');
    if (ctx2) {
        var balanceData = <?= json_encode($chart_balance) ?>;
        var bgColors = balanceData.map(function(v) { return v >= 0 ? 'rgba(76,175,80,0.15)' : 'rgba(244,67,54,0.15)'; });

        new Chart(ctx2, {
            type: 'line',
            data: {
                labels: <?= json_encode($chart_labels) ?>,
                datasets: [{
                    label: 'Balance Acumulado',
                    data: balanceData,
                    borderColor: function(ctx) {
                        var v = ctx.raw;
                        return v >= 0 ? '#4caf50' : '#f44336';
                    },
                    segment: {
                        borderColor: function(ctx) {
                            return ctx.p1.raw >= 0 ? '#4caf50' : '#f44336';
                        }
                    },
                    backgroundColor: 'rgba(76,175,80,0.1)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 5,
                    pointBackgroundColor: function(ctx) {
                        return (ctx.raw >= 0) ? '#4caf50' : '#f44336';
                    }
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { labels: { color: '#ccc' } } },
                scales: {
                    x: { ticks: { color: '#888' }, grid: { color: '#333' } },
                    y: {
                        ticks: { color: '#888', callback: function(v) { return '\u20AC' + v; } },
                        grid: { color: '#333' }
                    }
                }
            }
        });
    }
}
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initEconomiaCharts);
} else {
    initEconomiaCharts();
}
</script>

</div>
