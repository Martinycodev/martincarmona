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
        
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success && data.trabajo) {
            const job = data.trabajo;
            document.getElementById('editId').value = job.id;
            document.getElementById('editNombre').value = job.nombre || '';
            document.getElementById('editPrecioHora').value = job.precio_hora ?? 0;
            document.getElementById('editDescripcion').value = job.descripcion || '';
            // Seleccionar categoría en el dropdown
            var catSelect = document.getElementById('editCategoria');
            if (catSelect) catSelect.value = job.categoria || 'otro';

            // Mostrar/ocultar sección de documento
            const preview = document.getElementById('documentoPreview');
            const link = document.getElementById('documentoLink');
            if (job.documento) {
                link.href = buildUrl(job.documento.replace(/^\//, ''));
                preview.style.display = 'flex';
            } else {
                preview.style.display = 'none';
            }
            // Reset input file
            const fileInput = document.getElementById('editDocumento');
            if (fileInput) fileInput.value = '';

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

// Subir documento de método de trabajo
async function subirDocumento() {
    const fileInput = document.getElementById('editDocumento');
    const id = document.getElementById('editId').value;

    if (!fileInput.files.length) return;

    const file = fileInput.files[0];
    if (file.size > 10 * 1024 * 1024) {
        showToast('El archivo no puede superar 10MB', 'error');
        fileInput.value = '';
        return;
    }

    const formData = new FormData();
    formData.append('documento', file);
    formData.append('id', id);
    // CSRF se inyecta automáticamente por modal-functions.js en POST

    const progress = document.getElementById('documentoProgress');
    progress.style.display = 'block';

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
        const response = await fetch(buildUrl('trabajos/subirDocumento'), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            // Actualizar preview
            const preview = document.getElementById('documentoPreview');
            const link = document.getElementById('documentoLink');
            link.href = buildUrl(data.documento.replace(/^\//, ''));
            preview.style.display = 'flex';
            showToast('Documento subido correctamente', 'success');
        } else {
            showToast('Error: ' + data.message, 'error');
        }
    } catch (error) {
        handleFetchError(error, 'subir documento');
    } finally {
        progress.style.display = 'none';
        fileInput.value = '';
    }
}

// Eliminar documento de método de trabajo
async function eliminarDocumento() {
    if (!confirm('¿Eliminar el documento de método de trabajo?')) return;

    const id = document.getElementById('editId').value;

    try {
        const response = await fetch(buildUrl('trabajos/eliminarDocumento'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ id: id })
        });

        const data = await response.json();

        if (data.success) {
            document.getElementById('documentoPreview').style.display = 'none';
            showToast('Documento eliminado', 'success');
        } else {
            showToast('Error: ' + data.message, 'error');
        }
    } catch (error) {
        handleFetchError(error, 'eliminar documento');
    }
}

// Función para recargar tabla
function reloadTable() {
    location.reload();
}

// Inicializar formularios — funciona tanto en carga normal como en carga AJAX dinámica
function initTrabajosForms() {
    
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
}

// Ejecutar al cargar: si DOMContentLoaded ya disparó (carga AJAX), inicializar ya; si no, esperar
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTrabajosForms);
} else {
    initTrabajosForms();
}

// Exportar funciones para uso global
window.openCreateModal = openCreateModal;
window.openEditModal = openEditModal;
window.closeEditModal = closeEditModal;
window.editJob = editJob;
window.deleteJob = deleteJob;
window.subirDocumento = subirDocumento;
window.eliminarDocumento = eliminarDocumento;
window.reloadTable = reloadTable;
