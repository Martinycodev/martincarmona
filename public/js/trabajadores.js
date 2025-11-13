/**
 * Funciones específicas para la vista de trabajadores
 */

// Función para abrir modal de crear trabajador
function openCreateModal() {
    openCreateSection();
}

// Función para abrir modal de edición de trabajador
function openEditModal(buttonElement = null) {
    openModal('editModal', buttonElement);
}

// Función para cerrar modal de edición de trabajador
function closeEditModal() {
    closeModal('editModal');
}

// Función para editar trabajador
async function editWorker(id, buttonElement = null) {
    console.log('editWorker called with id:', id, 'buttonElement:', buttonElement);
    try {
        // Mostrar indicador de carga
        showToast('Cargando datos del trabajador...', 'info');
        
        const url = buildUrl(`trabajadores/obtener?id=${id}`);
        console.log('Fetching URL:', url);
        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        console.log('Response status:', response.status);
        console.log('Response URL:', response.url);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('Data received:', data);
        
        if (data.success && data.trabajador) {
            const worker = data.trabajador;
            document.getElementById('editId').value = worker.id;
            document.getElementById('editNombre').value = worker.nombre || '';
            document.getElementById('editDni').value = worker.dni || '';
            document.getElementById('editSs').value = worker.ss || '';

            openEditModal(buttonElement);
            showToast('Datos cargados correctamente', 'success');
        } else {
            console.error('Error en respuesta:', data);
            showToast('Error: ' + (data.message || 'No se pudieron cargar los datos'), 'error');
        }
    } catch (error) {
        console.error('Error completo:', error);
        showToast('Error de conexión: ' + error.message, 'error');
    }
}

// Función para eliminar trabajador
async function deleteWorker(id, nombre) {
    if (confirmDelete(nombre, 'trabajador')) {
        try {
            const response = await fetch(buildUrl('trabajadores/eliminar'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ id: id })
            });
            
            const data = await response.json();
            
            if (data.success) {
                document.querySelector(`tr[data-id="${id}"]`).remove();
                showToast('Trabajador eliminado correctamente', 'success');
            } else {
                showToast('Error al eliminar el trabajador: ' + data.message, 'error');
            }
        } catch (error) {
            handleFetchError(error, 'eliminar trabajador');
        }
    }
}

// Función para recargar tabla
function reloadTable() {
    location.reload();
}

// Inicializar formularios cuando se carga el DOM
document.addEventListener('DOMContentLoaded', function() {
    console.log('Trabajadores script loaded');
    
    // Manejo del formulario de crear
    const createForm = document.getElementById('createWorkerForm');
    if (createForm) {
        createForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Validar DNI antes de enviar
            const dni = document.getElementById('dni').value.trim();
            if (dni && !validarDNI(dni)) {
                showToast('El DNI debe tener el formato: 8 números + 1 letra mayúscula (ej: 12345678A)', 'error');
                return;
            }
            
            const formData = new FormData(this);
            const workerData = Object.fromEntries(formData);
            
            try {
                showToast('Creando trabajador...', 'info');
                
                const response = await fetch(buildUrl('trabajadores/crear'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(workerData)
                });
                
                const data = await handleApiResponse(response, 'crear trabajador');
                
                if (data.success) {
                    closeCreateSection();
                    updateTableAfterOperation('Trabajador creado', data);
                } else {
                    showToast('Error al crear: ' + (data.message || 'Error desconocido'), 'error');
                }
            } catch (error) {
                handleFetchError(error, 'crear trabajador');
            }
        });
    }

    // Manejo del formulario de editar
    const editForm = document.getElementById('editWorkerForm');
    if (editForm) {
        editForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Validar DNI antes de enviar
            const dni = document.getElementById('editDni').value.trim();
            if (dni && !validarDNI(dni)) {
                showToast('El DNI debe tener el formato: 8 números + 1 letra mayúscula (ej: 12345678A)', 'error');
                return;
            }
            
            const formData = new FormData(this);
            const workerData = Object.fromEntries(formData);
            
            try {
                showToast('Actualizando trabajador...', 'info');
                
                const response = await fetch(buildUrl('trabajadores/actualizar'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(workerData)
                });
                
                const data = await handleApiResponse(response, 'actualizar trabajador');
                
                if (data.success) {
                    closeEditModal();
                    updateTableAfterOperation('Trabajador actualizado', data);
                } else {
                    showToast('Error al actualizar: ' + (data.message || 'Error desconocido'), 'error');
                }
            } catch (error) {
                handleFetchError(error, 'actualizar trabajador');
            }
        });
    }
});

// Función de test para verificar URLs
function testBuildUrl() {
    console.log('=== TEST BUILD URL ===');
    console.log('Current location:', window.location.href);
    console.log('Current pathname:', window.location.pathname);
    console.log('APP_BASE_URL:', window.APP_BASE_URL);
    
    const testUrls = [
        'trabajadores/obtener?id=1',
        'trabajadores/crear',
        'trabajadores/actualizar',
        'trabajadores/eliminar'
    ];
    
    testUrls.forEach(url => {
        const builtUrl = buildUrl(url);
        console.log(`buildUrl('${url}') = '${builtUrl}'`);
    });
    
    // Probar fetch real
    console.log('Testing real fetch...');
    fetch(buildUrl('trabajadores/obtener?id=1'), {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        console.log('Fetch response status:', response.status);
        console.log('Fetch response URL:', response.url);
        return response.text();
    })
    .then(text => {
        console.log('Fetch response text:', text.substring(0, 200) + '...');
    })
    .catch(error => {
        console.error('Fetch error:', error);
    });
}

// Exportar funciones para uso global
window.openCreateModal = openCreateModal;
window.openEditModal = openEditModal;
window.closeEditModal = closeEditModal;
window.editWorker = editWorker;
window.deleteWorker = deleteWorker;
window.reloadTable = reloadTable;
window.testBuildUrl = testBuildUrl;
