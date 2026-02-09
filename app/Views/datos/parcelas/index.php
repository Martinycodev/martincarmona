<?php
$title = htmlspecialchars($parcela['nombre'] ?? 'Sin nombre');
?>
<div class="container">
    <!-- Información Principal de la Parcela -->
    <div class="parcela-detail-container">
        <div class="parcela-card">
            <div class="parcela-header">
                <div class="parcela-main-info">
                    <div class="parcela-avatar">
                        <?php if (!empty($parcela['foto'])): ?>
                            <img src="<?= htmlspecialchars($parcela['foto']) ?>" alt="Foto de la parcela"
                                class="avatar-img">
                        <?php else: ?>
                            <div class="avatar-placeholder">🌱</div>
                        <?php endif; ?>
                    </div>
                    <div class="parcela-info">
                        <h2><?= htmlspecialchars($parcela['nombre'] ?? 'Sin nombre') ?></h2>
                        <p class="parcela-id">ID: <?= htmlspecialchars($parcela['id'] ?? '-') ?></p>
                        <div class="status-badge <?= strtolower($parcela['estado'] ?? 'activa') ?>">
                            <?= htmlspecialchars($parcela['estado'] ?? 'Activa') ?>
                        </div>
                    </div>
                </div>
                <div class="parcela-header-actions">
                    <a href="<?= $this->url('/datos/parcelas') ?>" class="btn btn-secondary">← Volver a Parcelas</a>
                </div>
            </div>

            <div class="parcela-details">
                <div class="detail-section">
                    <h3>📋 Información Básica</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Nombre:</label>
                            <span><?= htmlspecialchars($parcela['nombre'] ?? '-') ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Número de Olivos:</label>
                            <span><?= number_format($parcela['olivos'] ?? 0) ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Ubicación:</label>
                            <span><?= htmlspecialchars($parcela['ubicacion'] ?? '-') ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Propietario:</label>
                            <span><?= htmlspecialchars($parcela['propietario'] ?? '-') ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Empresa:</label>
                            <span><?= htmlspecialchars($parcela['empresa'] ?? '-') ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Hidrante:</label>
                            <span><?= htmlspecialchars($parcela['hidrante'] ?? '-') ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Descripción:</label>
                            <span><?= htmlspecialchars($parcela['descripcion'] ?? '-') ?></span>
                        </div>
                    </div>
                </div>

                <!-- Campos Futuros -->
                <div class="detail-section">
                    <h3>📊 Información Técnica (Futuro)</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Referencia Catastral:</label>
                            <span
                                class="future-field"><?= htmlspecialchars($parcela['referencia_catastral'] ?? 'No disponible') ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Superficie:</label>
                            <span
                                class="future-field"><?= $parcela['superficie'] ? number_format($parcela['superficie'], 2) . ' ha' : 'No disponible' ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Tipo de Cultivo:</label>
                            <span
                                class="future-field"><?= htmlspecialchars($parcela['tipo_cultivo'] ?? 'No disponible') ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Fecha de Plantación:</label>
                            <span
                                class="future-field"><?= $parcela['fecha_plantacion'] ? date('d/m/Y', strtotime($parcela['fecha_plantacion'])) : 'No disponible' ?></span>
                        </div>
                    </div>
                </div>

                <div class="detail-section">
                    <h3>📈 Rendimientos (Futuro)</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Rendimiento Anual:</label>
                            <span
                                class="future-field"><?= $parcela['rendimiento_anual'] ? number_format($parcela['rendimiento_anual'], 2) . ' kg/ha' : 'No disponible' ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Kilos Último Año:</label>
                            <span
                                class="future-field"><?= $parcela['kilos_ultimo_ano'] ? number_format($parcela['kilos_ultimo_ano'], 2) . ' kg' : 'No disponible' ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Última Cosecha:</label>
                            <span
                                class="future-field"><?= $parcela['ultima_cosecha'] ? date('d/m/Y', strtotime($parcela['ultima_cosecha'])) : 'No disponible' ?></span>
                        </div>
                    </div>
                </div>

                <!-- Documentos Futuros -->
                <div class="detail-section">
                    <h3>📄 Documentos (Futuro)</h3>
                    <div class="documents-grid">
                        <div class="document-item">
                            <div class="document-icon">📷</div>
                            <div class="document-info">
                                <h4>Fotos de la Parcela</h4>
                                <p>Imágenes de la parcela y cultivos</p>
                                <div class="no-data">No disponible aún</div>
                            </div>
                        </div>
                        <div class="document-item">
                            <div class="document-icon">🏛️</div>
                            <div class="document-info">
                                <h4>Documentos Catastrales</h4>
                                <p>Referencias y documentos oficiales</p>
                                <div class="no-data">No disponible aún</div>
                            </div>
                        </div>
                        <div class="document-item">
                            <div class="document-icon">📊</div>
                            <div class="document-info">
                                <h4>Informes de Rendimiento</h4>
                                <p>Historial de cosechas y producción</p>
                                <div class="no-data">No disponible aún</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Estilos específicos para la página de detalles de la parcela */
        .parcela-detail-container {
            margin-bottom: 2rem;
        }

        .parcela-card {
            background: #2a2a2a;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            border: 1px solid #404040;
        }

        .parcela-header {
            background: linear-gradient(135deg, #4CAF50 0%, #388E3C 100%);
            color: white;
            padding: 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.5rem;
        }

        .parcela-main-info {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            flex: 1;
        }

        .parcela-header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .parcela-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            border: 3px solid rgba(255, 255, 255, 0.3);
        }

        .avatar-img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        .avatar-placeholder {
            font-size: 2.5rem;
            opacity: 0.8;
        }

        .parcela-info h2 {
            margin: 0 0 0.5rem 0;
            font-size: 2rem;
            font-weight: 700;
        }

        .parcela-id {
            margin: 0 0 0.75rem 0;
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
        }

        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-badge.activa {
            background: rgba(76, 175, 80, 0.2);
            color: #4CAF50;
            border: 1px solid rgba(76, 175, 80, 0.3);
        }

        .status-badge.inactiva {
            background: rgba(244, 67, 54, 0.2);
            color: #f44336;
            border: 1px solid rgba(244, 67, 54, 0.3);
        }

        .parcela-details {
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

        .future-field {
            color: #888 !important;
            font-style: italic;
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
            padding: 1rem;
            color: #888;
            background: #1e1e1e;
            border-radius: 6px;
            border: 1px solid #404040;
            font-size: 0.85rem;
            font-style: italic;
        }

        @media (max-width: 768px) {
            .parcela-header {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }

            .parcela-main-info {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }

            .parcela-header-actions {
                width: 100%;
                justify-content: center;
            }

            .parcela-avatar {
                width: 60px;
                height: 60px;
            }

            .detail-grid {
                grid-template-columns: 1fr;
            }

            .documents-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    </body>

    </html>