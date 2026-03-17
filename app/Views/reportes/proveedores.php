<?php $title = 'Proveedores - Reportes'; ?>
<div class="container">
<div class="reports-container">
    <!-- Header -->
    <div class="reports-header">
        <div class="reports-header-left">
            <a href="<?= $this->url('/reportes') ?>" style="color:#4caf50; text-decoration:none; margin-right:12px;">&larr; Reportes</a>
            <div>
                <h1>Análisis de Proveedores</h1>
                <p class="reports-subtitle">Gastos y relación con proveedores — <?= $anio ?></p>
            </div>
        </div>
        <div class="reports-header-right">
            <form class="periodo-selector" method="get" action="<?= $this->url('/reportes/proveedores') ?>">
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
            <div class="kpi-card kpi-primary">
                <div class="kpi-content" style="text-align:center;">
                    <div class="kpi-value"><?= count($proveedores) ?></div>
                    <div class="kpi-label">Total Proveedores</div>
                </div>
            </div>
            <div class="kpi-card kpi-success">
                <div class="kpi-content" style="text-align:center;">
                    <div class="kpi-value"><?= $proveedores_activos ?></div>
                    <div class="kpi-label">Con Compras en <?= $anio ?></div>
                </div>
            </div>
            <div class="kpi-card kpi-economy" style="border-left: 3px solid #f44336;">
                <div class="kpi-content" style="text-align:center;">
                    <div class="kpi-value" style="color:#f44336;">&euro;<?= number_format($total_gasto_anio, 2) ?></div>
                    <div class="kpi-label">Gasto Total <?= $anio ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de distribución de gasto (top proveedores) -->
    <?php
    $proveedoresConGasto = array_filter($proveedores, fn($p) => $p['gasto_anio'] > 0);
    if (!empty($proveedoresConGasto)):
    ?>
    <div class="charts-section">
        <div class="chart-card" style="max-width:100%;">
            <h3>Distribución de Gasto por Proveedor — <?= $anio ?></h3>
            <canvas id="chart-proveedores" height="100"></canvas>
        </div>
    </div>
    <?php endif; ?>

    <!-- Tabla de proveedores -->
    <div class="analytics-section" style="margin-top:20px;">
        <div class="analytics-card" style="max-width:100%;">
            <h3>Detalle por Proveedor</h3>
            <div style="overflow-x:auto;">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Teléfono</th>
                            <th>Ubicación</th>
                            <th>Compras (<?= $anio ?>)</th>
                            <th>Gasto (<?= $anio ?>)</th>
                            <th>Gasto Total</th>
                            <th>Compras Total</th>
                            <th>Última Compra</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($proveedores)): ?>
                        <tr><td colspan="9" style="text-align:center; color:#888;">Sin proveedores registrados</td></tr>
                        <?php else: ?>
                        <?php foreach ($proveedores as $i => $p): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><strong><?= htmlspecialchars($p['nombre']) ?></strong></td>
                            <td><?= htmlspecialchars($p['telefono'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($p['ubicacion'] ?? '—') ?></td>
                            <td><?= intval($p['movimientos_anio']) ?></td>
                            <td style="font-weight:600; color:<?= $p['gasto_anio'] > 0 ? '#f44336' : '#888' ?>;">
                                <?= $p['gasto_anio'] > 0 ? '&euro;' . number_format($p['gasto_anio'], 2) : '—' ?>
                            </td>
                            <td><?= $p['gasto_total'] > 0 ? '&euro;' . number_format($p['gasto_total'], 2) : '—' ?></td>
                            <td><?= intval($p['movimientos_total']) ?></td>
                            <td><?= $p['ultima_compra'] ? date('d-m-Y', strtotime($p['ultima_compra'])) : '—' ?></td>
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

<?php if (!empty($proveedoresConGasto)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function initProveedoresChart() {
    var ctx = document.getElementById('chart-proveedores');
    if (!ctx) return;

    var nombres = <?= json_encode(array_column($proveedoresConGasto, 'nombre')) ?>;
    var gastos = <?= json_encode(array_map(fn($p) => round(floatval($p['gasto_anio']), 2), $proveedoresConGasto)) ?>;
    var colores = ['#f44336','#ff9800','#ff5722','#e91e63','#9c27b0','#673ab7','#3f51b5','#2196f3','#00bcd4','#009688'];

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: nombres,
            datasets: [{
                data: gastos,
                backgroundColor: colores.slice(0, nombres.length),
                borderColor: '#1a1a1a',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right',
                    labels: { color: '#ccc', padding: 12, font: { size: 12 } }
                },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            return ctx.label + ': \u20AC' + ctx.raw.toLocaleString('es-ES', {minimumFractionDigits: 2});
                        }
                    }
                }
            }
        }
    });
}
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initProveedoresChart);
} else {
    initProveedoresChart();
}
</script>
<?php endif; ?>

</div>
