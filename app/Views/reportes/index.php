<?php 
$title = 'Reportes - MartinCarmona.com';
?>
<div class="container">
<div class="reports-container">
    <!-- Header principal del panel de reportes -->
    <div class="reports-header">
        <div class="reports-header-left">
            <h1>📊 Panel de Reportes y Estadísticas</h1>
            <p class="reports-subtitle">Dashboard completo de gestión agrícola - <?= date('F Y') ?></p>
        </div>
        <div class="reports-header-right">
            <button class="btn-export">📥 Exportar Datos</button>
            <button class="btn-refresh">🔄 Actualizar</button>
            <button class="btn-settings">⚙️ Configurar</button>
        </div>
    </div>

    <!-- KPIs principales -->
    <div class="kpis-section">
        <h2>🎯 Indicadores Clave del Mes</h2>
        <div class="kpis-grid">
            <div class="kpi-card kpi-primary">
                <div class="kpi-icon">⏱️</div>
                <div class="kpi-content">
                    <div class="kpi-value"><?= number_format($kpis['total_horas_mes'], 1) ?>h</div>
                    <div class="kpi-label">Horas Trabajadas</div>
                    <div class="kpi-trend positive">+12.5% vs mes anterior</div>
                </div>
            </div>
            
            <div class="kpi-card kpi-success">
                <div class="kpi-icon">✅</div>
                <div class="kpi-content">
                    <div class="kpi-value"><?= $kpis['tareas_completadas'] ?></div>
                    <div class="kpi-label">Tareas Completadas</div>
                    <div class="kpi-trend positive">+8.3% vs mes anterior</div>
                </div>
            </div>
            
            <div class="kpi-card kpi-info">
                <div class="kpi-icon">👥</div>
                <div class="kpi-content">
                    <div class="kpi-value"><?= $kpis['trabajadores_activos'] ?></div>
                    <div class="kpi-label">Trabajadores Activos</div>
                    <div class="kpi-trend neutral">Sin cambios</div>
                </div>
            </div>
            
            <div class="kpi-card kpi-warning">
                <div class="kpi-icon">🌾</div>
                <div class="kpi-content">
                    <div class="kpi-value"><?= $kpis['parcelas_trabajadas'] ?></div>
                    <div class="kpi-label">Parcelas Trabajadas</div>
                    <div class="kpi-trend positive">+2 vs mes anterior</div>
                </div>
            </div>
            
            <div class="kpi-card kpi-accent">
                <div class="kpi-icon">⚡</div>
                <div class="kpi-content">
                    <div class="kpi-value"><?= $kpis['eficiencia_promedio'] ?>%</div>
                    <div class="kpi-label">Eficiencia Promedio</div>
                    <div class="kpi-trend positive">+5.2% vs mes anterior</div>
                </div>
            </div>
            
            <div class="kpi-card kpi-economy">
                <div class="kpi-icon">💰</div>
                <div class="kpi-content">
                    <div class="kpi-value">€<?= number_format($kpis['costo_total_mes'], 0) ?></div>
                    <div class="kpi-label">Costo Total Mes</div>
                    <div class="kpi-trend negative">-3.1% vs mes anterior</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Secciones principales -->
    <div class="reports-sections">
        <!-- Sección de navegación rápida -->
        <div class="quick-nav-section">
            <h2>🚀 Acceso Rápido</h2>
            <div class="quick-nav-grid">
                <a href="<?= $this->url('/reportes/personal') ?>" class="nav-card personal">
                    <div class="nav-icon">👥</div>
                    <div class="nav-title">Gestión de Personal</div>
                    <div class="nav-desc">Rendimiento y análisis de trabajadores</div>
                    <div class="nav-stats"><?= $kpis['trabajadores_activos'] ?> activos</div>
                </a>
                
                <a href="<?= $this->url('/reportes/parcelas') ?>" class="nav-card parcelas">
                    <div class="nav-icon">🌾</div>
                    <div class="nav-title">Análisis de Parcelas</div>
                    <div class="nav-desc">Productividad y estado de terrenos</div>
                    <div class="nav-stats"><?= $kpis['parcelas_trabajadas'] ?> en uso</div>
                </a>
                
                <a href="<?= $this->url('/reportes/trabajos') ?>" class="nav-card trabajos">
                    <div class="nav-icon">🔧</div>
                    <div class="nav-title">Análisis de Trabajos</div>
                    <div class="nav-desc">Tipos y eficiencia de tareas</div>
                    <div class="nav-stats"><?= $kpis['tareas_completadas'] ?> completadas</div>
                </a>
                
                <a href="<?= $this->url('/reportes/economia') ?>" class="nav-card economia">
                    <div class="nav-icon">💰</div>
                    <div class="nav-title">Análisis Económico</div>
                    <div class="nav-desc">Costos, ROI y presupuestos</div>
                    <div class="nav-stats">€<?= number_format($kpis['costo_total_mes'], 0) ?> este mes</div>
                </a>
                
                <a href="<?= $this->url('/reportes/recursos') ?>" class="nav-card recursos">
                    <div class="nav-icon">🚜</div>
                    <div class="nav-title">Gestión de Recursos</div>
                    <div class="nav-desc">Herramientas y vehículos</div>
                    <div class="nav-stats">15 equipos</div>
                </a>
                
                <a href="<?= $this->url('/reportes/proveedores') ?>" class="nav-card proveedores">
                    <div class="nav-icon">🤝</div>
                    <div class="nav-title">Análisis de Proveedores</div>
                    <div class="nav-desc">Gastos y evaluación de proveedores</div>
                    <div class="nav-stats">8 activos</div>
                </a>
            </div>
        </div>

        <!-- Sección de gráficos de productividad -->
        <div class="charts-section">
            <div class="charts-row">
                <div class="chart-card">
                    <h3>📈 Productividad Semanal</h3>
                    <div class="chart-placeholder">
                        <div class="chart-bars">
                            <?php foreach($productividad_semanal as $semana): ?>
                            <div class="chart-bar">
                                <div class="bar" style="height: <?= ($semana['horas'] / 80) * 100 ?>%"></div>
                                <div class="bar-label"><?= $semana['semana'] ?></div>
                                <div class="bar-value"><?= $semana['horas'] ?>h</div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <div class="chart-card">
                    <h3>🏆 Top 5 Trabajadores</h3>
                    <div class="ranking-list">
                        <?php foreach($top_trabajadores as $index => $trabajador): ?>
                        <div class="ranking-item">
                            <div class="rank-number"><?= $index + 1 ?></div>
                            <div class="rank-info">
                                <div class="rank-name"><?= $trabajador['nombre'] ?></div>
                                <div class="rank-details"><?= $trabajador['horas'] ?>h • <?= $trabajador['tareas'] ?> tareas</div>
                            </div>
                            <div class="rank-score"><?= $trabajador['eficiencia'] ?>%</div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección de parcelas y trabajos -->
        <div class="analytics-section">
            <div class="analytics-row">
                <div class="analytics-card">
                    <h3>🌱 Parcelas Más Productivas</h3>
                    <div class="parcelas-grid">
                        <?php foreach($top_parcelas as $parcela): ?>
                        <div class="parcela-item">
                            <div class="parcela-header">
                                <h4><?= $parcela['nombre'] ?></h4>
                                <span class="roi-badge">ROI: <?= $parcela['roi'] ?>%</span>
                            </div>
                            <div class="parcela-stats">
                                <div class="stat">
                                    <span class="stat-value"><?= $parcela['horas'] ?>h</span>
                                    <span class="stat-label">Horas</span>
                                </div>
                                <div class="stat">
                                    <span class="stat-value"><?= $parcela['tareas'] ?></span>
                                    <span class="stat-label">Tareas</span>
                                </div>
                                <div class="stat">
                                    <span class="stat-value"><?= $parcela['olivos'] ?></span>
                                    <span class="stat-label">Olivos</span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="analytics-card">
                    <h3>🔧 Trabajos Más Frecuentes</h3>
                    <div class="trabajos-list">
                        <?php foreach($trabajos_frecuentes as $trabajo): ?>
                        <div class="trabajo-item">
                            <div class="trabajo-info">
                                <h4><?= $trabajo['tipo'] ?></h4>
                                <div class="trabajo-details">
                                    <?= $trabajo['cantidad'] ?> veces • <?= $trabajo['horas_total'] ?>h total
                                </div>
                            </div>
                            <div class="trabajo-average">
                                <span class="average-value"><?= $trabajo['promedio'] ?>h</span>
                                <span class="average-label">promedio</span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección de costos y alertas -->
        <div class="bottom-section">
            <div class="bottom-row">
                <div class="costs-card">
                    <h3>💸 Distribución de Costos</h3>
                    <div class="costs-chart">
                        <?php foreach($costos_categorias as $categoria): ?>
                        <div class="cost-item">
                            <div class="cost-bar-container">
                                <div class="cost-bar" style="width: <?= $categoria['porcentaje'] ?>%"></div>
                            </div>
                            <div class="cost-info">
                                <span class="cost-category"><?= $categoria['categoria'] ?></span>
                                <span class="cost-amount">€<?= number_format($categoria['monto'], 2) ?></span>
                                <span class="cost-percentage"><?= $categoria['porcentaje'] ?>%</span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="alerts-card">
                    <h3>🔔 Alertas del Sistema</h3>
                    <div class="alerts-list">
                        <?php foreach($alertas as $alerta): ?>
                        <div class="alert-item alert-<?= $alerta['tipo'] ?>">
                            <div class="alert-icon">
                                <?php 
                                echo match($alerta['tipo']) {
                                    'warning' => '⚠️',
                                    'info' => 'ℹ️',
                                    'success' => '✅',
                                    'error' => '❌',
                                    default => '📢'
                                };
                                ?>
                            </div>
                            <div class="alert-message"><?= $alerta['mensaje'] ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Funcionalidad básica para los botones del header
document.addEventListener('DOMContentLoaded', function() {
    // Botón de exportar
    document.querySelector('.btn-export')?.addEventListener('click', function() {
        showToast('Funcionalidad de exportación en desarrollo...', 'info');
    });
    
    // Botón de actualizar
    document.querySelector('.btn-refresh')?.addEventListener('click', function() {
        this.innerHTML = '🔄 Actualizando...';
        this.disabled = true;
        
        setTimeout(() => {
            location.reload();
        }, 1000);
    });
    
    // Botón de configuración
    document.querySelector('.btn-settings')?.addEventListener('click', function() {
        showToast('Panel de configuración en desarrollo...', 'info');
    });
    
    // Animación de entrada para las tarjetas
    const cards = document.querySelectorAll('.kpi-card, .nav-card, .chart-card, .analytics-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.6s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
});
</script>

</div>

