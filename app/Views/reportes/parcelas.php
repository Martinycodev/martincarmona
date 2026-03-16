<?php $title = 'Parcelas - Reportes'; ?>
<div class="container">
<div class="reports-container">
    <!-- Header -->
    <div class="reports-header">
        <div class="reports-header-left">
            <a href="<?= $this->url('/reportes') ?>" class="btn-back" style="color:#4caf50; text-decoration:none; margin-right:12px;">&larr; Reportes</a>
            <div>
                <h1>Análisis de Parcelas</h1>
                <p class="reports-subtitle">Productividad y riego — <?= $anio ?></p>
            </div>
        </div>
        <div class="reports-header-right">
            <form class="periodo-selector" method="get" action="<?= $this->url('/reportes/parcelas') ?>">
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
        <div class="kpis-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
            <div class="kpi-card kpi-primary">
                <div class="kpi-content" style="text-align:center;">
                    <div class="kpi-value"><?= $total_parcelas ?></div>
                    <div class="kpi-label">Total Parcelas</div>
                </div>
            </div>
            <div class="kpi-card kpi-success">
                <div class="kpi-content" style="text-align:center;">
                    <div class="kpi-value"><?= $parcelas_activas ?></div>
                    <div class="kpi-label">Con Actividad (<?= $anio ?>)</div>
                </div>
            </div>
            <div class="kpi-card kpi-info">
                <div class="kpi-content" style="text-align:center;">
                    <div class="kpi-value"><?= number_format($total_m3, 1) ?> m&sup3;</div>
                    <div class="kpi-label">Agua Consumida (<?= $anio ?>)</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de actividad por parcela/mes -->
    <?php if (!empty($parcelas_mes)): ?>
    <div class="charts-section">
        <div class="chart-card" style="max-width:100%;">
            <h3>Actividad Mensual por Parcela — <?= $anio ?></h3>
            <canvas id="chart-parcelas-mes" height="100"></canvas>
        </div>
    </div>
    <?php endif; ?>

    <!-- Tabla de parcelas -->
    <div class="analytics-section" style="margin-top:20px;">
        <div class="analytics-card" style="max-width:100%;">
            <h3>Detalle por Parcela</h3>
            <div style="overflow-x:auto;">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Olivos</th>
                            <th>Ubicación</th>
                            <th>Tareas (<?= $anio ?>)</th>
                            <th>Horas (<?= $anio ?>)</th>
                            <th>Riegos</th>
                            <th>m&sup3; Agua</th>
                            <th>Última Actividad</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($parcelas)): ?>
                        <tr><td colspan="9" style="text-align:center; color:#888;">Sin parcelas registradas</td></tr>
                        <?php else: ?>
                        <?php foreach ($parcelas as $i => $p): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><strong><?= htmlspecialchars($p['nombre']) ?></strong></td>
                            <td><?= intval($p['olivos']) ?></td>
                            <td><?= htmlspecialchars($p['ubicacion'] ?? '') ?></td>
                            <td><?= intval($p['tareas_anio']) ?></td>
                            <td><?= number_format(floatval($p['horas_anio']), 1) ?>h</td>
                            <td><?= $p['num_riegos'] ?></td>
                            <td><?= $p['m3_riego'] > 0 ? number_format($p['m3_riego'], 1) : '—' ?></td>
                            <td><?= $p['ultima_actividad'] ? date('d/m/Y', strtotime($p['ultima_actividad'])) : '—' ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.report-table { width:100%; border-collapse:collapse; margin-top:12px; }
.report-table th, .report-table td { padding:10px 14px; text-align:left; border-bottom:1px solid #333; }
.report-table th { background:#1a1a1a; color:#a8d5ab; font-weight:600; font-size:13px; text-transform:uppercase; letter-spacing:.5px; }
.report-table tbody tr:hover { background:rgba(76,175,80,.08); }
.periodo-selector select { background:#2a2a2a; color:#fff; border:1px solid #444; border-radius:8px; padding:8px 12px; font-size:14px; cursor:pointer; }
.periodo-selector select:focus { border-color:#4caf50; outline:none; }
</style>

<?php if (!empty($parcelas_mes)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function initParcelasChart() {
    var ctx = document.getElementById('chart-parcelas-mes');
    if (!ctx) return;

    var colores = ['#4caf50','#2196f3','#ff9800','#e91e63','#9c27b0','#00bcd4'];
    var datasets = [];
    var nombres = <?= json_encode(array_keys($parcelas_mes)) ?>;
    var datos = <?= json_encode(array_values(array_map(fn($m) => array_values($m), $parcelas_mes))) ?>;

    nombres.forEach(function(nombre, i) {
        datasets.push({
            label: nombre,
            data: datos[i],
            borderColor: colores[i % colores.length],
            backgroundColor: colores[i % colores.length] + '22',
            fill: false,
            tension: 0.3,
            borderWidth: 2
        });
    });

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($chart_labels) ?>,
            datasets: datasets
        },
        options: {
            responsive: true,
            plugins: { legend: { labels: { color: '#ccc' } } },
            scales: {
                x: { ticks: { color: '#888' }, grid: { color: '#333' } },
                y: { ticks: { color: '#888' }, grid: { color: '#333' }, title: { display: true, text: 'Tareas', color: '#ccc' } }
            }
        }
    });
}
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initParcelasChart);
} else {
    initParcelasChart();
}
</script>
<?php endif; ?>

</div>
