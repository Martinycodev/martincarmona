<?php
$title = 'Parcelas - Listado';
?>
<div class="container">
    <div class="page-header">
        <h1>🌱 Parcelas</h1>
        <div class="header-actions">
            <a href="<?= $this->url('/parcelas') ?>" class="btn btn-primary">+ Nueva Parcela</a>
        </div>
    </div>

    <!-- Incluir estilos y scripts del buscador -->
    <link rel="stylesheet" href="<?= $this->url('/public/css/search.css') ?>">
    <script src="<?= $this->url('/public/js/search.js') ?>"></script>

    <div class="search-container">
        <div class="search-box">
            <input type="text" id="searchInput" class="search-input"
                placeholder="🔍 Buscar parcelas (escribe 3+ caracteres para filtrar)..." autocomplete="off">
            <div class="search-results-info" id="searchResultsInfo"></div>
        </div>
    </div>

    <div class="table-container">
        <table class="styled-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Olivos</th>
                    <th>Ubicación</th>
                    <th>Propietario</th>
                    <th class="actions-column">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($parcelas)): ?>
                    <tr>
                        <td colspan="5" class="no-data">
                            <p>No hay parcelas registradas.</p>
                            <a href="<?= $this->url('/parcelas') ?>" class="btn btn-primary">Crear primera parcela</a>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($parcelas as $parcela): ?>
                        <tr data-id="<?= htmlspecialchars($parcela['id']) ?>">
                            <td><?= htmlspecialchars($parcela['nombre']) ?></td>
                            <td>
                                <span class="olivos-count"><?= number_format($parcela['olivos']) ?></span>
                            </td>
                            <td><?= htmlspecialchars($parcela['ubicacion']) ?></td>
                            <td><?= htmlspecialchars($parcela['propietario']) ?></td>
                            <td class="actions">
                                <a href="<?= $this->url('/datos/parcelas?id=' . $parcela['id']) ?>" class="btn-icon btn-view"
                                    title="Ver detalles">
                                    👁️
                                </a>
                                <a href="<?= $this->url('/parcelas/editar/' . $parcela['id']) ?>" class="btn-icon btn-edit"
                                    title="Editar">
                                    ✏️
                                </a>
                                <button class="btn-icon btn-delete"
                                    onclick="deleteParcela(<?= $parcela['id'] ?>, '<?= htmlspecialchars($parcela['nombre']) ?>')"
                                    title="Eliminar">
                                    🗑️
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <style>
        /* Estilos específicos para el listado de parcelas */

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

        .olivos-count {
            font-weight: 600;
            color: #4CAF50;
            background: rgba(76, 175, 80, 0.1);
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.9rem;
            display: inline-block;
            border: 1px solid rgba(76, 175, 80, 0.2);
        }

        .no-data {
            text-align: center;
            padding: 2rem;
            color: #666;
        }

        .no-data p {
            margin-bottom: 1rem;
        }

        .actions-column {
            width: 120px;
        }

        .actions {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
        }

        .btn-icon {
            background: none;
            border: none;
            padding: 6px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            min-width: 32px;
            height: 32px;
        }

        .btn-view {
            background: linear-gradient(135deg, #4CAF50, #388E3C);
            color: white;
        }

        .btn-view:hover {
            background: linear-gradient(135deg, #388E3C, #2E7D32);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(76, 175, 80, 0.4);
            color: white;
            text-decoration: none;
        }

        .btn-edit {
            background: linear-gradient(135deg, #2196F3, #1976D2);
            color: white;
        }

        .btn-edit:hover {
            background: linear-gradient(135deg, #1976D2, #1565C0);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(33, 150, 243, 0.4);
            color: white;
            text-decoration: none;
        }

        .btn-delete {
            background: linear-gradient(135deg, #f44336, #d32f2f);
            color: white;
            border: none;
            cursor: pointer;
        }

        .btn-delete:hover {
            background: linear-gradient(135deg, #d32f2f, #c62828);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(244, 67, 54, 0.4);
        }

        @media (max-width: 768px) {
            .table-container {
                overflow-x: auto;
            }

            .styled-table {
                min-width: 600px;
            }

            .actions {
                flex-direction: column;
                gap: 0.25rem;
            }
        }
    </style>

    <script>
        let tableSearch;

        document.addEventListener('DOMContentLoaded', function () {
            // Inicializar el buscador
            tableSearch = initializeTableSearch({
                searchInputId: 'searchInput',
                resultsInfoId: 'searchResultsInfo',
                tableRowsSelector: 'tbody tr[data-id]',
                searchFields: ['nombre', 'ubicacion', 'propietario'],
                minSearchLength: 3,
                showAllWhenEmpty: true,
                showAllWhenLessThanMin: true
            });
        });

        function deleteParcela(id, nombre) {
            if (confirm(`¿Estás seguro de que quieres eliminar la parcela "${nombre}"?\n\nEsta acción no se puede deshacer.`)) {
                fetch('<?= $this->url('/datos/parcelas/eliminar') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: id })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Remover la fila de la tabla
                            const row = document.querySelector(`tr[data-id="${id}"]`);
                            if (row) {
                                tableSearch.removeItem(row);
                            }

                            showToast('Parcela eliminada correctamente', 'success');
                        } else {
                            showToast(data.message || 'Error al eliminar la parcela', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('Error de conexión', 'error');
                    });
            }
        }

        function showToast(message, type = 'info') {
            // Crear toast si no existe
            let toast = document.getElementById('toast');
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'toast';
                toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            z-index: 10000;
            transform: translateX(100%);
            transition: transform 0.3s ease;
            max-width: 300px;
            word-wrap: break-word;
        `;
                document.body.appendChild(toast);
            }

            // Configurar colores según el tipo
            const colors = {
                success: '#4CAF50',
                error: '#f44336',
                info: '#2196F3',
                warning: '#ff9800'
            };

            toast.style.backgroundColor = colors[type] || colors.info;
            toast.textContent = message;

            // Mostrar toast
            toast.style.transform = 'translateX(0)';

            // Ocultar después de 3 segundos
            setTimeout(() => {
                toast.style.transform = 'translateX(100%)';
            }, 3000);
        }
    </script>

    </body>

    </html>