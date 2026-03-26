<?php 
$title = 'Datos - MiOlivar.es';
?>
<div class="container">
        <h2 style="text-align:center; margin:1.5rem 0;">📊 Bases de Datos</h2>

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
            <a href="<?= $this->url('/datos/propietarios') ?>" class="action-card">
                <span class="action-icon">🧑‍💼</span>
                <div class="action-title">Propietarios</div>
                <div class="action-desc">Gestión de propietarios de parcelas</div>
            </a>
            <a href="<?= $this->url('/tareas') ?>" class="action-card">
                <span class="action-icon">📝</span>
                <div class="action-title">Tareas</div>
                <div class="action-desc">Gestión de todas las tareas</div>
            </a>
            <a href="<?= $this->url('/datos/riego') ?>" class="action-card">
                <span class="action-icon">💧</span>
                <div class="action-title">Riegos</div>
                <div class="action-desc">Gestión del riego</div>
            </a>
            <a href="<?= $this->url('/datos/fitosanitarios') ?>" class="action-card">
                <span class="action-icon">💊</span>
                <div class="action-title">Fitosanitarios</div>
                <div class="action-desc">Inventario y registro de tratamientos</div>
            </a>
            <a href="<?= $this->url('/campana') ?>" class="action-card">
                <span class="action-icon">🫒</span>
                <div class="action-title">Campaña</div>
                <div class="action-desc">Registro de recolección y rentabilidad</div>
            </a>
        </div>
    </div>
