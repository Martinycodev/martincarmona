<?php $title = 'Personal - Reportes'; ?>
<div class="container">
<div class="reports-container">
    <!-- Header -->
    <div class="reports-header">
        <div class="reports-header-left">
            <a href="<?= $this->url('/reportes') ?>" class="btn-back" style="color:#4caf50; text-decoration:none; margin-right:12px;">&larr; Reportes</a>
            <div>
                <h1>Gestión de Personal</h1>
                <p class="reports-subtitle">Rendimiento de trabajadores — <?= $anio ?></p>
            </div>
        </div>
        <div class="reports-header-right">
            <form class="periodo-selector" method="get" action="<?= $this->url('/reportes/personal') ?>">
                <select name="anio" onchange="this.form.submit()">
                    <?php foreach ($anios_disponibles as $a): ?>
                    <option value="<?= $a ?>" <?= $a == $anio ? 'selected' : '' ?>><?= $a ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    </div>

    <!-- KPIs resumen -->
    <div class="kpis-section">
        <div class="kpis-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
            <div class="kpi-card kpi-primary">
                <div class="kpi-content" style="text-align:center;">
                    <div class="kpi-value"><?= $activos ?></div>
                    <div class="kpi-label">Trabajadores Activos</div>
                </div>
            </div>
            <div class="kpi-card kpi-success">
                <div class="kpi-content" style="text-align:center;">
                    <div class="kpi-value"><?= $total_tareas ?></div>
                    <div class="kpi-label">Tareas Totales</div>
                </div>
            </div>
            <div class="kpi-card kpi-info">
                <div class="kpi-content" style="text-align:center;">
                    <div class="kpi-value"><?= number_format($total_horas, 1) ?>h</div>
                    <div class="kpi-label">Horas Registradas</div>
                </div>
            </div>
            <div class="kpi-card kpi-accent">
                <div class="kpi-content" style="text-align:center;">
                    <div class="kpi-value"><?= count($trabajadores) ?></div>
                    <div class="kpi-label">Total Plantilla</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de actividad mensual -->
    <div class="charts-section">
        <div class="chart-card" style="max-width:100%;">
            <h3>Actividad Mensual — <?= $anio ?></h3>
            <canvas id="chart-personal-mensual" height="100"></canvas>
        </div>
    </div>

    <!-- Tabla de trabajadores -->
    <div class="analytics-section" style="margin-top:20px;">
        <div class="analytics-card" style="max-width:100%;">
            <h3>Detalle por Trabajador</h3>
            <div style="overflow-x:auto;">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Estado</th>
                            <th>Tareas (<?= $anio ?>)</th>
                            <th>Horas (<?= $anio ?>)</th>
                            <th>Meses Activo</th>
                            <th>Parcelas</th>
                            <th>Última Actividad</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($trabajadores)): ?>
                        <tr><td colspan="8" style="text-align:center; color:#888;">Sin trabajadores registrados</td></tr>
                        <?php else: ?>
                        <?php foreach ($trabajadores as $i => $t): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><strong><?= htmlspecialchars($t['nombre'] . ' ' . ($t['apellidos'] ?? '')) ?></strong></td>
                            <td>
                                <span class="status-badge status-<?= $t['estado'] === 'activo' ? 'active' : 'inactive' ?>">
                                    <?= htmlspecialchars(ucfirst($t['estado'] ?? 'activo')) ?>
                                </span>
                            </td>
                            <td><?= intval($t['tareas_anio']) ?></td>
                            <td><?= number_format(floatval($t['horas_anio']), 1) ?>h</td>
                            <td><?= intval($t['meses_activo']) ?>/12</td>
                            <td><?= intval($t['parcelas_distintas']) ?></td>
                            <td><?= $t['ultima_actividad'] ? date('d-m-Y', strtotime($t['ultima_actividad'])) : '—' ?></td>
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
.report-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 12px;
}
.report-table th,
.report-table td {
    padding: 10px 14px;
    text-align: left;
    border-bottom: 1px solid #333;
}
.report-table th {
    background: #1a1a1a;
    color: #a8d5ab;
    font-weight: 600;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.report-table tbody tr:hover {
    background: rgba(76, 175, 80, 0.08);
}
.status-badge {
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}
.status-active {
    background: rgba(76, 175, 80, 0.2);
    color: #4caf50;
}
.status-inactive {
    background: rgba(244, 67, 54, 0.2);
    color: #f44336;
}
.btn-back {
    font-size: 16px;
    font-weight: 500;
}
.periodo-selector select {
    background: #2a2a2a;
    color: #fff;
    border: 1px solid #444;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 14px;
    cursor: pointer;
}
.periodo-selector select:focus {
    border-color: #4caf50;
    outline: none;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function initPersonal() {
    var ctx = document.getElementById('chart-personal-mensual');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($chart_labels) ?>,
            datasets: [
                {
                    label: 'Tareas',
                    data: <?= json_encode($chart_tareas) ?>,
                    backgroundColor: 'rgba(76, 175, 80, 0.7)',
                    borderRadius: 4,
                    yAxisID: 'y'
                },
                {
                    label: 'Horas',
                    data: <?= json_encode($chart_horas) ?>,
                    type: 'line',
                    borderColor: '#2196f3',
                    backgroundColor: 'rgba(33, 150, 243, 0.1)',
                    fill: true,
                    tension: 0.3,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { labels: { color: '#ccc' } }
            },
            scales: {
                x: { ticks: { color: '#888' }, grid: { color: '#333' } },
                y: {
                    position: 'left',
                    title: { display: true, text: 'Tareas', color: '#4caf50' },
                    ticks: { color: '#888' },
                    grid: { color: '#333' }
                },
                y1: {
                    position: 'right',
                    title: { display: true, text: 'Horas', color: '#2196f3' },
                    ticks: { color: '#888' },
                    grid: { drawOnChartArea: false }
                }
            }
        }
    });
}
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPersonal);
} else {
    initPersonal();
}
</script>

</div>
