/**
 * Funciones específicas para la vista de trabajos
 */

// Función para abrir modal de crear trabajo
function openCreateModal() {
    openCreateSection();
}

// Función para abrir modal de edición de trabajo
function openEditModal(buttonElement = null) {
    openModal('editModal', buttonElement);
}

// Función para cerrar modal de edición de trabajo
function closeEditModal() {
    closeModal('editModal');
}

// Función para editar trabajo
async function editJob(id, buttonElement = null) {
    try {
        // Mostrar indicador de carga
        showToast('Cargando datos del trabajo...', 'info');
        
        const response = await fetch(buildUrl(`trabajos/obtener?id=${id}`), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        console.log('Response status:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('Data received:', data);
        
        if (data.success && data.trabajo) {
            const job = data.trabajo;
            document.getElementById('editId').value = job.id;
            document.getElementById('editNombre').value = job.nombre || '';
            document.getElementById('editPrecioHora').value = job.precio_hora || '';
            document.getElementById('editDescripcion').value = job.descripcion || '';

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

// Función para eliminar trabajo
async function deleteJob(id, nombre) {
    if (confirmDelete(nombre, 'trabajo')) {
        try {
            const response = await fetch(buildUrl('trabajos/eliminar'), {
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
                showToast('Trabajo eliminado correctamente', 'success');
            } else {
                showToast('Error al eliminar el trabajo: ' + data.message, 'error');
            }
        } catch (error) {
            handleFetchError(error, 'eliminar trabajo');
        }
    }
}

// Función para recargar tabla
function reloadTable() {
    location.reload();
}

// Inicializar formularios cuando se carga el DOM
document.addEventListener('DOMContentLoaded', function() {
    console.log('Trabajos script loaded');
    
    // Manejo del formulario de crear
    const createForm = document.getElementById('createJobForm');
    if (createForm) {
        createForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const jobData = Object.fromEntries(formData);
            
            try {
                showToast('Creando trabajo...', 'info');
                
                const response = await fetch(buildUrl('trabajos/crear'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(jobData)
                });
                
                const data = await handleApiResponse(response, 'crear trabajo');
                
                if (data.success) {
                    closeCreateSection();
                    updateTableAfterOperation('Trabajo creado', data);
                } else {
                    showToast('Error al crear el trabajo: ' + data.message, 'error');
                }
            } catch (error) {
                handleFetchError(error, 'crear trabajo');
            }
        });
    }

    // Manejo del formulario de editar
    const editForm = document.getElementById('editJobForm');
    if (editForm) {
        editForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const jobData = Object.fromEntries(formData);
            
            try {
                showToast('Actualizando trabajo...', 'info');
                
                const response = await fetch(buildUrl('trabajos/actualizar'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(jobData)
                });
                
                const data = await handleApiResponse(response, 'actualizar trabajo');
                
                if (data.success) {
                    closeEditModal();
                    updateTableAfterOperation('Trabajo actualizado', data);
                } else {
                    showToast('Error al actualizar el trabajo: ' + data.message, 'error');
                }
            } catch (error) {
                handleFetchError(error, 'actualizar trabajo');
            }
        });
    }
});

// Exportar funciones para uso global
window.openCreateModal = openCreateModal;
window.openEditModal = openEditModal;
window.closeEditModal = closeEditModal;
window.editJob = editJob;
window.deleteJob = deleteJob;
window.reloadTable = reloadTable;
