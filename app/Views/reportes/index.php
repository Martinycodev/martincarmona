<?php
$title = 'Reportes - MartinCarmona.com';
$mesesNombres = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio',
                  'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
?>
<div class="container">
<div class="reports-container">
    <!-- Header con selector de periodo -->
    <div class="reports-header">
        <div class="reports-header-left">
            <h1>Panel de Reportes</h1>
            <p class="reports-subtitle"><?= htmlspecialchars($nombre_mes) ?> <?= $anio ?></p>
        </div>
        <div class="reports-header-right">
            <form class="periodo-selector" method="get" action="<?= $this->url('/reportes') ?>">
                <select name="mes" aria-label="Mes">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>" <?= $m === $mes ? 'selected' : '' ?>><?= $mesesNombres[$m] ?></option>
                    <?php endfor; ?>
                </select>
                <select name="anio" aria-label="Año">
                    <?php foreach ($anios_disponibles as $a): ?>
                    <option value="<?= $a ?>" <?= $a == $anio ? 'selected' : '' ?>><?= $a ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-refresh">Aplicar</button>
            </form>
        </div>
    </div>

    <!-- KPIs con trends reales -->
    <div class="kpis-section">
        <div class="kpis-grid">
            <div class="kpi-card kpi-primary">
                <div class="kpi-icon">&#9201;</div>
                <div class="kpi-content">
                    <div class="kpi-value"><?= number_format($kpis['total_horas_mes'], 1) ?>h</div>
                    <div class="kpi-label">Horas Trabajadas</div>
                    <div class="kpi-trend <?= $trends['horas']['clase'] ?>"><?= $trends['horas']['texto'] ?></div>
                </div>
            </div>
            <div class="kpi-card kpi-success">
                <div class="kpi-icon">&#9989;</div>
                <div class="kpi-content">
                    <div class="kpi-value"><?= $kpis['tareas_completadas'] ?></div>
                    <div class="kpi-label">Tareas Completadas</div>
                    <div class="kpi-trend <?= $trends['tareas']['clase'] ?>"><?= $trends['tareas']['texto'] ?></div>
                </div>
            </div>
            <div class="kpi-card kpi-info">
                <div class="kpi-icon">&#128101;</div>
                <div class="kpi-content">
                    <div class="kpi-value"><?= $kpis['trabajadores_activos'] ?></div>
                    <div class="kpi-label">Trabajadores Activos</div>
                    <div class="kpi-trend <?= $trends['trabajadores']['clase'] ?>"><?= $trends['trabajadores']['texto'] ?></div>
                </div>
            </div>
            <div class="kpi-card kpi-warning">
                <div class="kpi-icon">&#127806;</div>
                <div class="kpi-content">
                    <div class="kpi-value"><?= $kpis['parcelas_trabajadas'] ?></div>
                    <div class="kpi-label">Parcelas Trabajadas</div>
                    <div class="kpi-trend <?= $trends['parcelas']['clase'] ?>"><?= $trends['parcelas']['texto'] ?></div>
                </div>
            </div>
            <div class="kpi-card kpi-accent">
                <div class="kpi-icon">&#9889;</div>
                <div class="kpi-content">
                    <div class="kpi-value"><?= $kpis['eficiencia_promedio'] ?>%</div>
                    <div class="kpi-label">Eficiencia</div>
                    <div class="kpi-trend <?= $trends['eficiencia']['clase'] ?>"><?= $trends['eficiencia']['texto'] ?></div>
                </div>
            </div>
            <div class="kpi-card kpi-economy">
                <div class="kpi-icon">&#128176;</div>
                <div class="kpi-content">
                    <div class="kpi-value">&euro;<?= number_format($kpis['costo_total_mes'], 0) ?></div>
                    <div class="kpi-label">Costo Total</div>
                    <div class="kpi-trend <?= $trends['costo']['clase'] ?>"><?= $trends['costo']['texto'] ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Acceso rápido a sub-páginas -->
    <div class="quick-nav-section">
        <h2>Informes Detallados</h2>
        <div class="quick-nav-grid">
            <a href="<?= $this->url('/reportes/personal') ?>" class="nav-card personal">
                <div class="nav-icon">&#128101;</div>
                <div class="nav-title">Personal</div>
                <div class="nav-desc">Rendimiento por trabajador, actividad mensual</div>
                <div class="nav-stats"><?= $kpis['trabajadores_activos'] ?> activos</div>
            </a>
            <a href="<?= $this->url('/reportes/parcelas') ?>" class="nav-card parcelas">
                <div class="nav-icon">&#127806;</div>
                <div class="nav-title">Parcelas</div>
                <div class="nav-desc">Productividad, riego y actividad por parcela</div>
                <div class="nav-stats"><?= $kpis['parcelas_trabajadas'] ?> trabajadas</div>
            </a>
            <a href="<?= $this->url('/reportes/trabajos') ?>" class="nav-card trabajos">
                <div class="nav-icon">&#128295;</div>
                <div class="nav-title">Trabajos</div>
                <div class="nav-desc">Estacionalidad y frecuencia de cada tipo</div>
                <div class="nav-stats"><?= $kpis['tareas_completadas'] ?> tareas</div>
            </a>
            <a href="<?= $this->url('/reportes/economia') ?>" class="nav-card economia">
                <div class="nav-icon">&#128176;</div>
                <div class="nav-title">Economía</div>
                <div class="nav-desc">Ingresos vs gastos, balance y categorías</div>
                <div class="nav-stats">&euro;<?= number_format($kpis['costo_total_mes'], 0) ?> este mes</div>
            </a>
            <a href="<?= $this->url('/reportes/recursos') ?>" class="nav-card recursos">
                <div class="nav-icon">&#128668;</div>
                <div class="nav-title">Recursos</div>
                <div class="nav-desc">Vehículos con alertas ITV y herramientas</div>
                <div class="nav-stats"><?= $total_recursos ?> equipos</div>
            </a>
            <a href="<?= $this->url('/reportes/proveedores') ?>" class="nav-card proveedores">
                <div class="nav-icon">&#129309;</div>
                <div class="nav-title">Proveedores</div>
                <div class="nav-desc">Gasto acumulado y detalle por proveedor</div>
                <div class="nav-stats"><?= $total_proveedores ?> proveedores</div>
            </a>
        </div>
    </div>
</div>

<style>
.periodo-selector {
    display: flex;
    gap: 8px;
    align-items: center;
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

<script>
(function() {
    // Auto-submit al cambiar selector de periodo
    document.querySelectorAll('.periodo-selector select').forEach(function(sel) {
        sel.addEventListener('change', function() { this.closest('form').submit(); });
    });
    // Animación de entrada
    document.querySelectorAll('.kpi-card, .nav-card').forEach(function(card, i) {
        card.style.opacity = '0';
        card.style.transform = 'translateY(16px)';
        setTimeout(function() {
            card.style.transition = 'all 0.4s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, i * 60);
    });
})();
</script>

</div>
