<?php 
$title = 'Listado de Trabajadores';
include BASE_PATH . '/app/Views/layouts/header.php'; 
?>

<!-- Incluir estilos y scripts del buscador -->
<link rel="stylesheet" href="<?= $this->url('/public/css/search.css') ?>">
<script src="<?= $this->url('/public/js/search.js') ?>"></script>

<div class="page-header">
    <h1>👷‍♂️ Listado de Trabajadores</h1>
    <div class="header-actions">
        <a href="<?= $this->url('/datos/trabajadores') ?>" class="btn btn-primary">➕ Nuevo Trabajador</a>
        <a href="<?= $this->url('/datos') ?>" class="btn btn-secondary">← Volver</a>
    </div>
</div>

<!-- Buscador -->
<div class="search-container">
    <div class="search-box">
        <input type="text" id="searchInput" class="search-input" placeholder="🔍 Buscar trabajadores (escribe 3+ caracteres para filtrar)..." autocomplete="off">
        <div class="search-results-info" id="searchResultsInfo"></div>
    </div>
</div>

<!-- Tabla de Trabajadores -->
<div class="table-container">
    <table class="styled-table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>DNI</th>
                <th>Teléfono</th>
                <th class="actions-column">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($trabajadores)): ?>
                <?php foreach ($trabajadores as $trabajador): ?>
                <tr data-id="<?= $trabajador['id'] ?>">
                    <td><?= htmlspecialchars($trabajador['nombre'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($trabajador['dni'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($trabajador['telefono'] ?? '-') ?></td>
                    <td class="actions">
                        <a href="<?= $this->url('/datos/trabajadores?id=' . $trabajador['id']) ?>" class="btn-icon btn-view" title="Ver detalles">
                            👁️
                        </a>
                        <a href="<?= $this->url('/datos/trabajadores?id=' . $trabajador['id']) ?>" class="btn-icon btn-edit" title="Editar">
                            ✏️
                        </a>
                        <button class="btn-icon btn-delete" onclick="deleteWorker(<?= $trabajador['id'] ?>, '<?= htmlspecialchars($trabajador['nombre']) ?>')" title="Eliminar">
                            🗑️
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="no-data">
                        <p>No hay trabajadores registrados.</p>
                        <a href="<?= $this->url('/datos/trabajadores') ?>" class="btn btn-primary">Crear primer trabajador</a>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
/* Estilos específicos para el listado de trabajadores */

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

.debt-amount {
    color: #dc3545;
    font-weight: 600;
}

.no-debt {
    color: #28a745;
    font-weight: 600;
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

/* Estilos para el botón de eliminar */
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
</style>

<script>
// Variable global para el buscador
let tableSearch;

// Inicializar el buscador cuando se carga la página
document.addEventListener('DOMContentLoaded', function() {
    // Configurar el buscador para trabajadores
    tableSearch = initializeTableSearch({
        searchInputId: 'searchInput',
        resultsInfoId: 'searchResultsInfo',
        tableRowsSelector: 'tbody tr[data-id]',
        searchFields: ['nombre', 'dni', 'telefono'], // Índices de las columnas: 0=nombre, 1=dni, 2=telefono
        minSearchLength: 3,
        showAllWhenEmpty: true,
        showAllWhenLessThanMin: true
    });
});

// Función para eliminar trabajador
async function deleteWorker(id, nombre) {
    if (confirm(`¿Estás seguro de que quieres eliminar al trabajador "${nombre}"?`)) {
        try {
            const response = await fetch(`<?= $this->url("/trabajadores/eliminar") ?>`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ id: id })
            });
            
            const data = await response.json();
            
            if (data.success) {
                const row = document.querySelector(`tr[data-id="${id}"]`);
                if (row) {
                    row.remove();
                    
                    // Actualizar el buscador
                    if (tableSearch) {
                        tableSearch.removeItem(id);
                    }
                }
                showToast('Trabajador eliminado correctamente', 'success');
            } else {
                showToast('Error al eliminar el trabajador: ' + data.message, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('Error al eliminar el trabajador', 'error');
        }
    }
}

// Función para mostrar notificaciones
function showToast(message, type = 'info') {
    // Crear toast si no existe
    let toast = document.getElementById('toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'toast';
        toast.className = 'toast';
        document.body.appendChild(toast);
    }
    
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
        }, 400);
    }, 3000);
}
</script>

</body>
</html>
