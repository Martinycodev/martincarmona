<?php 
$title = 'Economía - MartinCarmona.com';
?>
<div class="container">
<div class="economy-container">
    <!-- Header de Economía -->
    <div class="economy-header">
        <div class="economy-header-left">
            <h1>💰 Economía</h1>
            <p>Gestión financiera y control de gastos</p>
        </div>
        <div class="economy-header-right">
            <button class="btn btn-primary" onclick="openCreateModal()">
                ➕ Nuevo Movimiento
            </button>
            <button class="btn btn-secondary" onclick="refreshData()">
                🔄 Actualizar
            </button>
        </div>
    </div>

    <!-- Resumen Financiero -->
    <div class="financial-summary">
        <div class="summary-card income">
            <div class="summary-icon">📈</div>
            <div class="summary-content">
                <div class="summary-value" id="totalIngresos">
                    <?= number_format($resumen['total_ingresos'] ?? 0, 2) ?> €
                </div>
                <div class="summary-label">Total Ingresos</div>
            </div>
        </div>
        
        <div class="summary-card expenses">
            <div class="summary-icon">📉</div>
            <div class="summary-content">
                <div class="summary-value" id="totalGastos">
                    <?= number_format($resumen['total_gastos'] ?? 0, 2) ?> €
                </div>
                <div class="summary-label">Total Gastos</div>
            </div>
        </div>
        
        <div class="summary-card balance">
            <div class="summary-icon">💰</div>
            <div class="summary-content">
                <div class="summary-value" id="saldoTotal">
                    <?= number_format($resumen['saldo_total'] ?? 0, 2) ?> €
                </div>
                <div class="summary-label">Saldo Total</div>
            </div>
        </div>
        
        <div class="summary-card pending">
            <div class="summary-icon">⏳</div>
            <div class="summary-content">
                <div class="summary-value" id="movimientosPendientes">
                    <?= $resumen['importe_pendiente'] ?? 0 ?> €
                </div>
                <div class="summary-label">Pendiente de pago</div>
            </div>
        </div>
    </div>

    <!-- Filtros y Búsqueda -->
    <div class="filters-section">
        <div class="filters-left">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="🔍 Buscar movimientos..." onkeyup="searchMovements()">
            </div>
            <select id="categoryFilter" onchange="filterByCategory()">
                <option value="">Todas las categorías</option>
                <option value="personal">Personal</option>
                <option value="gasto">Gasto</option>
                <option value="impuestos">Impuestos</option>
                <option value="maquinaria">Maquinaria</option>
                <option value="parcela">Parcela</option>
                <option value="servicios">Servicios</option>
                <option value="subvencion">Subvención</option>
                <option value="otros">Otros</option>
            </select>
            <select id="typeFilter" onchange="filterByType()">
                <option value="">Todos los tipos</option>
                <option value="ingreso">Ingresos</option>
                <option value="gasto">Gastos</option>
            </select>
        </div>
        <div class="filters-right">
            <button class="btn btn-info" onclick="exportData()">📊 Exportar</button>
        </div>
    </div>

    <!-- Tabla de Movimientos -->
    <div class="movements-section">
        <div class="section-header">
            <h3>📋 Movimientos Recientes</h3>
            <div class="section-actions">
                <span id="movementsCount"><?= count($movimientosRecientes) ?> movimientos</span>
            </div>
        </div>
        
        <div class="table-container">
            <table class="movements-table" id="movementsTable">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Concepto</th>
                        <th>Categoría</th>
                        <th>Importe</th>
                        <th>Relacionado</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="movementsTableBody">
                    <?php foreach ($movimientosRecientes as $movimiento): ?>
                    <tr data-id="<?= $movimiento['id'] ?>">
                        <td><?= date('d/m/Y', strtotime($movimiento['fecha'])) ?></td>
                        <td>
                            <span class="type-badge <?= $movimiento['tipo'] ?>">
                                <?= $movimiento['tipo'] === 'ingreso' ? '📈 Ingreso' : '📉 Gasto' ?>
                            </span>
                        </td>
                        <td class="concept-cell"><?= htmlspecialchars($movimiento['concepto']) ?></td>
                        <td>
                            <span class="category-badge category-<?= $movimiento['categoria'] ?>">
                                <?= ucfirst($movimiento['categoria']) ?>
                            </span>
                        </td>
                        <td class="amount-cell <?= $movimiento['categoria'] === 'pago' ? 'pago' : $movimiento['tipo'] ?>">
                            <?= $movimiento['tipo'] === 'ingreso' ? '+' : '-' ?>
                            <?= number_format($movimiento['importe'], 2) ?> €
                        </td>
                        <td class="related-cell">
                            <?php
                            $related = [];
                            if ($movimiento['proveedor_nombre']) $related[] = "Prov: " . $movimiento['proveedor_nombre'];
                            if ($movimiento['trabajador_nombre']) $related[] = "Trab: " . $movimiento['trabajador_nombre'];
                            if ($movimiento['vehiculo_nombre']) $related[] = "Veh: " . $movimiento['vehiculo_nombre'] . " (" . $movimiento['vehiculo_matricula'] . ")";
                            if ($movimiento['parcela_nombre']) $related[] = "Parc: " . $movimiento['parcela_nombre'];
                            echo implode('<br>', $related) ?: '-';
                            ?>
                        </td>
                        <td>
                            <span class="status-badge status-<?= $movimiento['estado'] ?>">
                                <?= $movimiento['estado'] === 'pagado' ? '✅ Pagado' : '⏳ Pendiente' ?>
                            </span>
                        </td>
                        <td class="actions-cell">
                            <button class="btn-icon btn-edit" onclick="editMovement(<?= $movimiento['id'] ?>)" title="Editar">
                                ✏️
                            </button>
                            <button class="btn-icon btn-delete" onclick="deleteMovement(<?= $movimiento['id'] ?>)" title="Eliminar">
                                🗑️
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para Crear/Editar Movimiento -->
<div id="movementModal" class="modal">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">➕ Nuevo Movimiento</h3>
            <span class="close" onclick="closeModal('movementModal')">&times;</span>
        </div>
        <form id="movementForm" action="javascript:void(0);">
            <input type="hidden" id="movementId" name="id">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="movementFecha">Fecha:</label>
                    <input type="date" id="movementFecha" name="fecha" required>
                </div>
                
                <div class="form-group">
                    <label for="movementTipo">Tipo:</label>
                    <select id="movementTipo" name="tipo" required onchange="toggleAmountSign()">
                        <option value="ingreso">📈 Ingreso</option>
                        <option value="gasto">📉 Gasto</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="movementConcepto">Concepto:</label>
                <input type="text" id="movementConcepto" name="concepto" required placeholder="Descripción del movimiento">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="movementCategoria">Categoría:</label>
                    <select id="movementCategoria" name="categoria" required>
                        <option value="personal">Personal</option>
                        <option value="gasto">Gasto</option>
                        <option value="impuestos">Impuestos</option>
                        <option value="maquinaria">Maquinaria</option>
                        <option value="parcela">Parcela</option>
                        <option value="servicios">Servicios</option>
                        <option value="subvencion">Subvención</option>
                        <option value="otros">Otros</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="movementImporte">Importe (€):</label>
                    <input type="number" id="movementImporte" name="importe" step="0.01" min="0" required placeholder="0.00">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="movementProveedor">Proveedor:</label>
                    <div class="autocomplete-wrapper">
                        <input type="text" id="movementProveedor" name="proveedor_nombre" autocomplete="off" placeholder="Buscar proveedor...">
                        <input type="hidden" id="movementProveedorId" name="proveedor_id">
                        <div id="proveedorResults" class="autocomplete-results" style="display: none;"></div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="movementTrabajador">Trabajador:</label>
                    <div class="autocomplete-wrapper">
                        <input type="text" id="movementTrabajador" name="trabajador_nombre" autocomplete="off" placeholder="Buscar trabajador...">
                        <input type="hidden" id="movementTrabajadorId" name="trabajador_id">
                        <div id="trabajadorResults" class="autocomplete-results" style="display: none;"></div>
                    </div>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="movementVehiculo">Vehículo:</label>
                    <div class="autocomplete-wrapper">
                        <input type="text" id="movementVehiculo" name="vehiculo_nombre" autocomplete="off" placeholder="Buscar vehículo...">
                        <input type="hidden" id="movementVehiculoId" name="vehiculo_id">
                        <div id="vehiculoResults" class="autocomplete-results" style="display: none;"></div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="movementParcela">Parcela:</label>
                    <div class="autocomplete-wrapper">
                        <input type="text" id="movementParcela" name="parcela_nombre" autocomplete="off" placeholder="Buscar parcela...">
                        <input type="hidden" id="movementParcelaId" name="parcela_id">
                        <div id="parcelaResults" class="autocomplete-results" style="display: none;"></div>
                    </div>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="movementEstado">Estado:</label>
                    <select id="movementEstado" name="estado" required>
                        <option value="pendiente">⏳ Pendiente</option>
                        <option value="pagado">✅ Pagado</option>
                    </select>
                </div>
            </div>
            
            <div class="modal-buttons">
                <button type="button" class="btn-modal btn-secondary" onclick="closeModal('movementModal')">
                    ❌ Cancelar
                </button>
                <button type="submit" class="btn-modal btn-primary" id="submitButton">
                    💾 Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Toast de notificaciones -->
<div id="toast" class="toast"></div>

<script>
// Variables globales
let allMovements = <?= json_encode($movimientosRecientes) ?>;
let filteredMovements = allMovements;

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    setupAutocomplete();
    setupFormHandlers();
    setupEventListeners();
    
    // Verificar si se debe abrir el modal automáticamente
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('openModal') === 'true') {
        // Abrir el modal después de un pequeño delay para asegurar que todo esté cargado
        setTimeout(function() {
            openCreateModal();
            // Limpiar el parámetro de la URL sin recargar la página
            const newUrl = window.location.pathname;
            window.history.replaceState({}, document.title, newUrl);
        }, 100);
    }
});

// Configurar autocompletado
function setupAutocomplete() {
    setupAutocompleteField('movementProveedor', 'proveedorResults', 'movementProveedorId', 'proveedores');
    setupAutocompleteField('movementTrabajador', 'trabajadorResults', 'movementTrabajadorId', 'trabajadores');
    setupAutocompleteField('movementVehiculo', 'vehiculoResults', 'movementVehiculoId', 'vehiculos');
    setupAutocompleteField('movementParcela', 'parcelaResults', 'movementParcelaId', 'parcelas');
}

function setupAutocompleteField(inputId, resultsId, hiddenId, endpoint) {
    const input = document.getElementById(inputId);
    const results = document.getElementById(resultsId);
    const hidden = document.getElementById(hiddenId);
    
    input.addEventListener('input', function() {
        const query = this.value.trim();
        if (query.length >= 2) {
            fetch(`<?= $this->url("/economia/obtener") ?>${endpoint.charAt(0).toUpperCase() + endpoint.slice(1)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const filtered = data.data.filter(item => 
                            item.nombre.toLowerCase().includes(query.toLowerCase())
                        );
                        
                        if (filtered.length > 0) {
                            results.innerHTML = filtered.map(item => `
                                <div class="autocomplete-item" 
                                     data-id="${item.id}" 
                                     data-name="${item.nombre}"
                                     onclick="selectAutocompleteItem('${inputId}', '${resultsId}', '${hiddenId}', '${item.id}', '${item.nombre}')">
                                    ${item.nombre}
                                </div>
                            `).join('');
                        } else {
                            results.innerHTML = '<div class="autocomplete-item no-results">No hay coincidencias</div>';
                        }
                        results.style.display = 'block';
                    }
                })
                .catch(error => console.error('Error:', error));
        } else {
            results.style.display = 'none';
        }
    });
}

function selectAutocompleteItem(inputId, resultsId, hiddenId, id, name) {
    document.getElementById(inputId).value = name;
    document.getElementById(hiddenId).value = id;
    document.getElementById(resultsId).style.display = 'none';
}

// Configurar manejadores de formulario
function setupFormHandlers() {
    document.getElementById('movementForm').addEventListener('submit', function(e) {
        e.preventDefault();
        saveMovement();
    });
}

// Configurar event listeners
function setupEventListeners() {
    // Cerrar modal al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal')) {
            closeModal('movementModal');
        }
    });
}

// Funciones de modal
function openCreateModal() {
    document.getElementById('modalTitle').textContent = '➕ Nuevo Movimiento';
    document.getElementById('submitButton').textContent = '💾 Crear';
    document.getElementById('movementForm').reset();
    document.getElementById('movementFecha').value = new Date().toISOString().split('T')[0];
    openModal('movementModal');
}

function openModal(modalId) {
    document.getElementById(modalId).style.display = 'block';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Funciones de CRUD
function saveMovement() {
    const formData = new FormData(document.getElementById('movementForm'));
    const isEdit = document.getElementById('movementId').value !== '';
    const url = isEdit ? '<?= $this->url("/economia/editar") ?>' : '<?= $this->url("/economia/crear") ?>';
    
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            closeModal('movementModal');
            refreshData();
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error al guardar el movimiento', 'error');
    });
}

function editMovement(id) {
    fetch(`<?= $this->url("/economia/obtener") ?>?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const mov = data.data;
                document.getElementById('modalTitle').textContent = '✏️ Editar Movimiento';
                document.getElementById('submitButton').textContent = '💾 Actualizar';
                document.getElementById('movementId').value = mov.id;
                document.getElementById('movementFecha').value = mov.fecha;
                document.getElementById('movementTipo').value = mov.tipo;
                document.getElementById('movementConcepto').value = mov.concepto;
                document.getElementById('movementCategoria').value = mov.categoria;
                document.getElementById('movementImporte').value = mov.importe;
                document.getElementById('movementProveedor').value = mov.proveedor_nombre || '';
                document.getElementById('movementProveedorId').value = mov.proveedor_id || '';
                document.getElementById('movementTrabajador').value = mov.trabajador_nombre || '';
                document.getElementById('movementTrabajadorId').value = mov.trabajador_id || '';
                document.getElementById('movementVehiculo').value = mov.vehiculo_nombre ? `${mov.vehiculo_nombre} (${mov.vehiculo_matricula})` : '';
                document.getElementById('movementVehiculoId').value = mov.vehiculo_id || '';
                document.getElementById('movementParcela').value = mov.parcela_nombre || '';
                document.getElementById('movementParcelaId').value = mov.parcela_id || '';
                document.getElementById('movementEstado').value = mov.estado;
                
                openModal('movementModal');
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error al cargar el movimiento', 'error');
        });
}

function deleteMovement(id) {
    if (confirm('¿Estás seguro de que quieres eliminar este movimiento?')) {
        const formData = new FormData();
        formData.append('id', id);
        
        fetch('<?= $this->url("/economia/eliminar") ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                refreshData();
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error al eliminar el movimiento', 'error');
        });
    }
}

// Funciones de filtrado y búsqueda
function searchMovements() {
    const query = document.getElementById('searchInput').value.toLowerCase();
    filterMovements();
}

function filterByCategory() {
    filterMovements();
}

function filterByType() {
    filterMovements();
}

function filterMovements() {
    const searchQuery = document.getElementById('searchInput').value.toLowerCase();
    const categoryFilter = document.getElementById('categoryFilter').value;
    const typeFilter = document.getElementById('typeFilter').value;
    
    filteredMovements = allMovements.filter(mov => {
        const matchesSearch = !searchQuery || 
            mov.concepto.toLowerCase().includes(searchQuery) ||
            (mov.proveedor_nombre && mov.proveedor_nombre.toLowerCase().includes(searchQuery)) ||
            (mov.trabajador_nombre && mov.trabajador_nombre.toLowerCase().includes(searchQuery)) ||
            (mov.parcela_nombre && mov.parcela_nombre.toLowerCase().includes(searchQuery));
        
        const matchesCategory = !categoryFilter || mov.categoria === categoryFilter;
        const matchesType = !typeFilter || mov.tipo === typeFilter;
        
        return matchesSearch && matchesCategory && matchesType;
    });
    
    renderMovementsTable();
}

function renderMovementsTable() {
    const tbody = document.getElementById('movementsTableBody');
    const count = document.getElementById('movementsCount');
    
    count.textContent = `${filteredMovements.length} movimientos`;
    
    if (filteredMovements.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="no-data">No se encontraron movimientos</td></tr>';
        return;
    }
    
    tbody.innerHTML = filteredMovements.map(mov => `
        <tr data-id="${mov.id}">
            <td>${formatDate(mov.fecha)}</td>
            <td>
                <span class="type-badge ${mov.tipo}">
                    ${mov.tipo === 'ingreso' ? '📈 Ingreso' : '📉 Gasto'}
                </span>
            </td>
            <td class="concept-cell">${escapeHtml(mov.concepto)}</td>
            <td>
                <span class="category-badge category-${mov.categoria}">
                    ${capitalizeFirst(mov.categoria)}
                </span>
            </td>
            <td class="amount-cell ${mov.categoria === 'pago' ? 'pago' : mov.tipo}">
                ${mov.tipo === 'ingreso' ? '+' : '-'}
                ${formatNumber(mov.importe)} €
            </td>
            <td class="related-cell">
                ${getRelatedInfo(mov)}
            </td>
            <td>
                <span class="status-badge status-${mov.estado}">
                    ${mov.estado === 'pagado' ? '✅ Pagado' : '⏳ Pendiente'}
                </span>
            </td>
            <td class="actions-cell">
                <button class="btn-icon btn-edit" onclick="editMovement(${mov.id})" title="Editar">
                    ✏️
                </button>
                <button class="btn-icon btn-delete" onclick="deleteMovement(${mov.id})" title="Eliminar">
                    🗑️
                </button>
            </td>
        </tr>
    `).join('');
}

// Funciones de utilidad
function refreshData() {
    location.reload();
}

function toggleAmountSign() {
    const tipo = document.getElementById('movementTipo').value;
    const importeInput = document.getElementById('movementImporte');
    // No necesitamos cambiar el signo, solo el placeholder
    importeInput.placeholder = tipo === 'ingreso' ? '0.00' : '0.00';
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES');
}

function formatNumber(number) {
    return parseFloat(number).toFixed(2);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function capitalizeFirst(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function getRelatedInfo(mov) {
    const related = [];
    if (mov.proveedor_nombre) related.push(`Prov: ${mov.proveedor_nombre}`);
    if (mov.trabajador_nombre) related.push(`Trab: ${mov.trabajador_nombre}`);
    if (mov.vehiculo_nombre) related.push(`Veh: ${mov.vehiculo_nombre} (${mov.vehiculo_matricula})`);
    if (mov.parcela_nombre) related.push(`Parc: ${mov.parcela_nombre}`);
    return related.join('<br>') || '-';
}

function exportData() {
    showToast('Función de exportación en desarrollo', 'info');
}

function showToast(message, type = 'info') {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    
    // Limpiar clases previas
    toast.className = `toast toast-${type}`;
    
    // Forzar reflow para reiniciar animación
    toast.offsetHeight;
    
    // Mostrar con animación de entrada
    toast.classList.add('show');
    
    // Ocultar con animación de salida después de 3 segundos
    setTimeout(() => {
        toast.classList.remove('show');
        toast.classList.add('hide');
        
        // Limpiar clase hide después de la animación
        setTimeout(() => {
            toast.classList.remove('hide');
        }, 400); // Duración de la transición
    }, 3000);
}
</script>

    </body>
</html>
