<?php 
$title = htmlspecialchars($trabajador['nombre'] ?? 'Sin nombre');
include BASE_PATH . '/app/Views/layouts/header.php'; 
?>

<!-- Información Principal del Trabajador -->
<div class="worker-detail-container">
    <div class="worker-card">
        <div class="worker-header">
            <div class="worker-main-info">
                <div class="worker-avatar">
                    <?php if (!empty($trabajador['foto'])): ?>
                        <img src="<?= htmlspecialchars($trabajador['foto']) ?>" alt="Foto del trabajador" class="avatar-img">
                    <?php else: ?>
                        <div class="avatar-placeholder">👤</div>
                    <?php endif; ?>
                </div>
                <div class="worker-info">
                    <h2><?= htmlspecialchars($trabajador['nombre'] ?? 'Sin nombre') ?></h2>
                    <p class="worker-id">ID: <?= htmlspecialchars($trabajador['id'] ?? '-') ?></p>
                    <div class="status-badge <?= strtolower($trabajador['estado'] ?? 'inactivo') ?>">
                        <?= htmlspecialchars($trabajador['dado_alta_texto'] ?? 'No') ?>
                    </div>
                </div>
            </div>
            <div class="worker-header-actions">
                <a href="<?= $this->url('/datos/trabajadores') ?>" class="btn btn-secondary">← Volver a Trabajadores</a>
            </div>
        </div>
        
        <div class="worker-details">
            <div class="detail-section">
                <h3>📋 Información Personal</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <label>DNI:</label>
                        <span><?= htmlspecialchars($trabajador['dni'] ?? '-') ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Número SS:</label>
                        <span><?= htmlspecialchars($trabajador['ss'] ?? '-') ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Teléfono:</label>
                        <span><?= htmlspecialchars($trabajador['telefono'] ?? '-') ?></span>
                    </div>
                </div>
            </div>
            
            <div class="detail-section">
                <h3>💰 Información Laboral</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <label>Estado:</label>
                        <span><?= htmlspecialchars($trabajador['dado_alta_texto'] ?? 'Sí') ?></span>
                    </div>
                </div>
            </div>
            
            <div class="detail-section">
                <h3>💳 Estado de Deuda</h3>
                <div class="debt-info">
                    <div class="debt-amount <?= ($trabajador['deuda_total'] ?? 0) > 0 ? 'has-debt' : 'no-debt' ?>">
                        <span class="debt-label">Deuda pendiente:</span>
                        <span class="debt-value"><?= number_format($trabajador['deuda_total'] ?? 0, 2) ?> €</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Estadísticas del Trabajador -->
<div class="stats-container">
    <h3>📊 Estadísticas del Trabajador</h3>
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">🔨</div>
            <div class="stat-content">
                <div class="stat-value"><?= $estadisticas['total_trabajos'] ?? 0 ?></div>
                <div class="stat-label">Trabajos realizados</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($estadisticas['total_ganado'] ?? 0, 2) ?> €</div>
                <div class="stat-label">Total ganado</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($estadisticas['total_pagado'] ?? 0, 2) ?> €</div>
                <div class="stat-label">Total pagado</div>
            </div>
        </div>
    </div>
</div>

<!-- Historial de Trabajos -->
<div class="history-container">
    <h3>📝 Historial de Trabajos Recientes</h3>
    <?php if (!empty($historialTrabajos)): ?>
        <div class="table-container">
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Trabajo</th>
                        <th>Fecha Inicio</th>
                        <th>Fecha Fin</th>
                        <th>Precio Tarea</th>
                        <th>Total Ganado</th>
                        <th>Pagado</th>
                        <th>Pendiente</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($historialTrabajos as $trabajo): ?>
                    <tr>
                        <td><?= htmlspecialchars($trabajo['trabajo_nombre'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($trabajo['fecha_inicio'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($trabajo['fecha_fin'] ?? '-') ?></td>
                        <td><?= number_format($trabajo['precio_tarea'] ?? 0, 2) ?> €</td>
                        <td><?= number_format($trabajo['total_ganado'] ?? 0, 2) ?> €</td>
                        <td><?= number_format($trabajo['pagado'] ?? 0, 2) ?> €</td>
                        <td class="<?= ($trabajo['pendiente_pago'] ?? 0) > 0 ? 'pending-payment' : 'paid' ?>">
                            <?= number_format($trabajo['pendiente_pago'] ?? 0, 2) ?> €
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="no-data">
            <p>No hay trabajos registrados para este trabajador.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Sección de Documentos (para futuras implementaciones) -->
<div class="documents-container">
    <h3>📄 Documentos</h3>
    <div class="documents-grid">
        <div class="document-item">
            <div class="document-icon">🆔</div>
            <div class="document-info">
                <h4>DNI</h4>
                <p>Documento de identidad</p>
                <button class="btn btn-sm btn-secondary" disabled>Próximamente</button>
            </div>
        </div>
        <div class="document-item">
            <div class="document-icon">💳</div>
            <div class="document-info">
                <h4>Tarjeta SS</h4>
                <p>Seguridad Social</p>
                <button class="btn btn-sm btn-secondary" disabled>Próximamente</button>
            </div>
        </div>
        <div class="document-item">
            <div class="document-icon">📸</div>
            <div class="document-info">
                <h4>Foto</h4>
                <p>Fotografía del trabajador</p>
                <button class="btn btn-sm btn-secondary" disabled>Próximamente</button>
            </div>
        </div>
    </div>
</div>

<style>
/* Estilos específicos para la página de detalles del trabajador */
.worker-detail-container {
    margin-bottom: 2rem;
}

.worker-card {
    background: #2a2a2a;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
    overflow: hidden;
    border: 1px solid #404040;
}

.worker-header {
    background: linear-gradient(135deg, #4CAF50 0%, #388E3C 100%);
    color: white;
    padding: 2rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
}

.worker-main-info {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    flex: 1;
}

.worker-header-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.worker-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    overflow: hidden;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
}

.avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder {
    font-size: 2.5rem;
}

.worker-info h2 {
    margin: 0 0 0.5rem 0;
    font-size: 1.8rem;
    font-weight: 600;
}

.worker-id {
    margin: 0 0 1rem 0;
    opacity: 0.8;
    font-size: 0.9rem;
}

.status-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-badge.activo {
    background: #28a745;
    color: white;
}

.status-badge.inactivo {
    background: #dc3545;
    color: white;
}

.worker-details {
    padding: 2rem;
    background: #2a2a2a;
}

.detail-section {
    margin-bottom: 2rem;
}

.detail-section h3 {
    margin: 0 0 1rem 0;
    color: #fff;
    font-size: 1.2rem;
    border-bottom: 2px solid #404040;
    padding-bottom: 0.5rem;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.detail-item label {
    font-weight: 600;
    color: #ccc;
    font-size: 0.9rem;
}

.detail-item span {
    color: #fff;
    font-size: 1rem;
}

.debt-info {
    background: #1e1e1e;
    padding: 1rem;
    border-radius: 8px;
    border-left: 4px solid #dc3545;
    border: 1px solid #404040;
}

.debt-amount {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.debt-label {
    font-weight: 600;
    color: #ccc;
}

.debt-value {
    font-size: 1.5rem;
    font-weight: 700;
}

.debt-amount.has-debt .debt-value {
    color: #dc3545;
}

.debt-amount.no-debt .debt-value {
    color: #28a745;
}

.stats-container {
    margin-bottom: 2rem;
}

.stats-container h3 {
    margin-bottom: 1rem;
    color: #fff;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.stat-card {
    background: #2a2a2a;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    display: flex;
    align-items: center;
    gap: 1rem;
    border: 1px solid #404040;
}

.stat-icon {
    font-size: 2rem;
    opacity: 0.7;
}

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: 1.8rem;
    font-weight: 700;
    color: #fff;
    margin-bottom: 0.25rem;
}

.stat-label {
    font-size: 0.9rem;
    color: #ccc;
    font-weight: 500;
}

.history-container {
    margin-bottom: 2rem;
}

.history-container h3 {
    margin-bottom: 1rem;
    color: #fff;
}

.pending-payment {
    color: #dc3545;
    font-weight: 600;
}

.paid {
    color: #28a745;
    font-weight: 600;
}

.documents-container {
    margin-bottom: 2rem;
}

.documents-container h3 {
    margin-bottom: 1rem;
    color: #fff;
}

.documents-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.document-item {
    background: #2a2a2a;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    display: flex;
    align-items: center;
    gap: 1rem;
    border: 1px solid #404040;
}

.document-icon {
    font-size: 2rem;
    opacity: 0.7;
}

.document-info h4 {
    margin: 0 0 0.25rem 0;
    color: #fff;
}

.document-info p {
    margin: 0 0 0.75rem 0;
    color: #ccc;
    font-size: 0.9rem;
}

.no-data {
    text-align: center;
    padding: 2rem;
    color: #ccc;
    background: #1e1e1e;
    border-radius: 8px;
    border: 1px solid #404040;
}

@media (max-width: 768px) {
    .worker-header {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }
    
    .worker-main-info {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }
    
    .worker-header-actions {
        width: 100%;
        justify-content: center;
    }
    
    .worker-avatar {
        width: 60px;
        height: 60px;
    }
    
    .detail-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .documents-grid {
        grid-template-columns: 1fr;
    }
}
</style>

</body>
</html>
