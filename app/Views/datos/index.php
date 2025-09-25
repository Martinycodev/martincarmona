<?php 
$title = 'Datos - MartinCarmona.com';
?>
<div class="container">
        <div class="welcome-section">
            <h1>📊 Bases de Datos</h1>
        </div>

        <div class="actions-grid">
            <a href="<?= $this->url('/datos/trabajadores') ?>" class="action-card">
                <span class="action-icon">👷‍♂️</span>
                <div class="action-title">Trabajadores</div>
                <div class="action-desc">Listado de todos los trabajadores</div>
            </a>
            <a href="<?= $this->url('/datos/trabajos') ?>" class="action-card">
                <span class="action-icon">🛠️</span>
                <div class="action-title">Trabajos</div>
                <div class="action-desc">Listado de todos los trabajos</div>
            </a>
            <a href="<?= $this->url('/datos/vehiculos') ?>" class="action-card">
                <span class="action-icon">🚗</span>
                <div class="action-title">Vehículos</div>
                <div class="action-desc">Listado de todos los vehículos</div>
            </a>
            <a href="<?= $this->url('/datos/herramientas') ?>" class="action-card">
                <span class="action-icon">🔧</span>
                <div class="action-title">Herramientas</div>
                <div class="action-desc">Listado de todas las herramientas</div>
            </a>
            <a href="<?= $this->url('/datos/empresas') ?>" class="action-card">
                <span class="action-icon">🏢</span>
                <div class="action-title">Empresas</div>
                <div class="action-desc">Listado de todas las empresas</div>
            </a>
            <a href="<?= $this->url('/datos/parcelas') ?>" class="action-card">
                <span class="action-icon">🌾</span>
                <div class="action-title">Parcelas</div>
                <div class="action-desc">Listado de todas las parcelas</div>
            </a>
            <a href="<?= $this->url('/datos/proveedores') ?>" class="action-card">
                <span class="action-icon">🚚</span>
                <div class="action-title">Proveedores</div>
                <div class="action-desc">Listado de todos los proveedores</div>
            </a>
            <a href="<?= $this->url('/tareas') ?>" class="action-card">
                <span class="action-icon">📝</span>
                <div class="action-title">Tareas</div>
                <div class="action-desc">Gestión de todas las tareas</div>
            </a>
            <a href="<?= $this->url('/tareas') ?>" class="action-card">
                <span class="action-icon">💧</span>
                <div class="action-title">Riegos</div>
                <div class="action-desc">Gestión del riego</div>
            </a>
            <a href="<?= $this->url('/tareas') ?>" class="action-card">
                <span class="action-icon">💊</span>
                <div class="action-title">Fitosanitarios</div>
                <div class="action-desc">Gestión de tratamientos</div>
            </a>
        </div>
    </div>
</body>
</html>
