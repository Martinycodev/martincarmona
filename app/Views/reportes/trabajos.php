<?php $title = 'Trabajos - Reportes'; ?>
<div class="container">
<div class="reports-container">
    <!-- Header -->
    <div class="reports-header">
        <div class="reports-header-left">
            <a href="<?= $this->url('/reportes') ?>" style="color:#4caf50; text-decoration:none; margin-right:12px;">&larr; Reportes</a>
            <div>
                <h1>Análisis de Trabajos</h1>
                <p class="reports-subtitle">Estacionalidad y frecuencia — Datos históricos completos</p>
            </div>
        </div>
    </div>

    <!-- KPIs -->
    <div class="kpis-section">
        <div class="kpis-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
            <div class="kpi-card kpi-primary">
                <div class="kpi-content" style="text-align:center;">
                    <div class="kpi-value"><?= $tipos_distintos ?></div>
                    <div class="kpi-label">Tipos de Trabajo</div>
                </div>
            </div>
            <div class="kpi-card kpi-success">
                <div class="kpi-content" style="text-align:center;">
                    <div class="kpi-value"><?= $total_registros ?></div>
                    <div class="kpi-label">Registros Totales</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de estacionalidad -->
    <?php if (!empty($estacionalidad)): ?>
    <div class="charts-section">
        <div class="chart-card" style="max-width:100%;">
            <h3>Estacionalidad — ¿Qué trabajos se hacen en cada mes?</h3>
            <p style="color:#888; font-size:13px; margin-bottom:12px;">Datos acumulados de todos los años. Muestra patrones estacionales reales.</p>
            <canvas id="chart-estacionalidad" height="120"></canvas>
        </div>
    </div>
    <?php endif; ?>

    <!-- Tabla de mapa de calor (estacionalidad como tabla) -->
    <?php if (!empty($estacionalidad)): ?>
    <div class="analytics-section" style="margin-top:20px;">
        <div class="analytics-card" style="max-width:100%;">
            <h3>Mapa de Calor — Trabajos por Mes</h3>
            <div style="overflow-x:auto;">
                <table class="report-table heatmap-table">
                    <thead>
                        <tr>
                            <th>Trabajo</th>
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                            <th style="text-align:center; min-width:50px;"><?= $chart_labels[$m - 1] ?></th>
                            <?php endfor; ?>
                            <th style="text-align:center;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Calcular max global para la escala de color
                        $maxGlobal = 1;
                        foreach ($estacionalidad as $meses) {
                            $maxGlobal = max($maxGlobal, max($meses));
                        }
                        foreach ($estacionalidad as $nombre => $meses):
                            $total = array_sum($meses);
                        ?>
                        <tr>
                            <td><strong><?= $nombre ?></strong></td>
                            <?php for ($m = 1; $m <= 12; $m++):
                                $val = $meses[$m];
                                $intensidad = $val > 0 ? max(0.15, $val / $maxGlobal) : 0;
                            ?>
                            <td style="text-align:center;<?= $val > 0 ? "background:rgba(76,175,80,{$intensidad}); font-weight:600;" : 'color:#555;' ?>">
                                <?= $val > 0 ? $val : '·' ?>
                            </td>
                            <?php endfor; ?>
                            <td style="text-align:center; font-weight:700; color:#a8d5ab;"><?= $total ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Tabla de frecuencia histórica -->
    <div class="analytics-section" style="margin-top:20px;">
        <div class="analytics-card" style="max-width:100%;">
            <h3>Frecuencia Histórica</h3>
            <div style="overflow-x:auto;">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Trabajo</th>
                            <th>Veces Realizado</th>
                            <th>Horas Totales</th>
                            <th>Promedio h/vez</th>
                            <th>Años con Datos</th>
                            <th>Primera Vez</th>
                            <th>Última Vez</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($frecuencia)): ?>
                        <tr><td colspan="8" style="text-align:center; color:#888;">Sin datos</td></tr>
                        <?php else: ?>
                        <?php foreach ($frecuencia as $i => $f):
                            $cant = intval($f['total']);
                            $horas = floatval($f['horas']);
                            $prom = $cant > 0 ? round($horas / $cant, 1) : 0;
                        ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><strong><?= htmlspecialchars($f['nombre']) ?></strong></td>
                            <td><?= $cant ?></td>
                            <td><?= $horas > 0 ? number_format($horas, 1) . 'h' : '—' ?></td>
                            <td><?= $prom > 0 ? $prom . 'h' : '—' ?></td>
                            <td><?= intval($f['anios_con_datos']) ?></td>
                            <td><?= $f['primera_vez'] ? date('d-m-Y', strtotime($f['primera_vez'])) : '—' ?></td>
                            <td><?= $f['ultima_vez'] ? date('d-m-Y', strtotime($f['ultima_vez'])) : '—' ?></td>
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
.heatmap-table td { transition: background 0.2s ease; }
</style>

<?php if (!empty($estacionalidad)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function initTrabajosChart() {
    var ctx = document.getElementById('chart-estacionalidad');
    if (!ctx) return;

    var colores = ['#4caf50','#2196f3','#ff9800','#e91e63','#9c27b0','#00bcd4','#ff5722','#8bc34a','#3f51b5','#cddc39'];
    var nombres = <?= json_encode(array_keys($estacionalidad)) ?>;
    var datos = <?= json_encode(array_values(array_map(fn($m) => array_values($m), $estacionalidad))) ?>;
    var datasets = [];

    // Solo top 8 para legibilidad
    var limit = Math.min(nombres.length, 8);
    for (var i = 0; i < limit; i++) {
        datasets.push({
            label: nombres[i],
            data: datos[i],
            backgroundColor: colores[i % colores.length] + '99',
            borderColor: colores[i % colores.length],
            borderWidth: 1,
            borderRadius: 3
        });
    }

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($chart_labels) ?>,
            datasets: datasets
        },
        options: {
            responsive: true,
            plugins: {
                legend: { labels: { color: '#ccc', font: { size: 11 } } }
            },
            scales: {
                x: {
                    stacked: true,
                    ticks: { color: '#888' },
                    grid: { color: '#333' }
                },
                y: {
                    stacked: true,
                    ticks: { color: '#888' },
                    grid: { color: '#333' },
                    title: { display: true, text: 'Veces realizado', color: '#ccc' }
                }
            }
        }
    });
}
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTrabajosChart);
} else {
    initTrabajosChart();
}
</script>
<?php endif; ?>

</div>
