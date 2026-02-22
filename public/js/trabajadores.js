/**
 * Funciones específicas para la vista de trabajadores
 */

// Previsualizar foto seleccionada
function previewFoto(input, previewId) {
    const preview = document.getElementById(previewId);
    if (!preview || !input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = (e) => {
        preview.innerHTML = `<img src="${e.target.result}" alt="Foto">`;
    };
    reader.readAsDataURL(input.files[0]);
}

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
    console.log('editWorker called with id:', id);
    try {
        showToast('Cargando datos del trabajador...', 'info');

        const url = buildUrl(`trabajadores/obtener?id=${id}`);
        const response = await fetch(url, {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

        const data = await response.json();

        if (data.success && data.trabajador) {
            const worker = data.trabajador;
            document.getElementById('editId').value         = worker.id;
            document.getElementById('editNombre').value     = worker.nombre || '';
            document.getElementById('editDni').value        = worker.dni || '';
            document.getElementById('editSs').value         = worker.ss || '';
            document.getElementById('editAltaSs').value     = worker.alta_ss || '';
            document.getElementById('editCuadrilla').checked = !!parseInt(worker.cuadrilla);

            // Mostrar foto actual en el preview
            const preview = document.getElementById('editFotoPreview');
            if (worker.foto) {
                preview.innerHTML = `<img src="${buildUrl(worker.foto.replace(/^\//, ''))}" alt="Foto">`;
            } else {
                preview.innerHTML = '<span>👤</span>';
            }

            openEditModal(buttonElement);
            showToast('Datos cargados correctamente', 'success');
        } else {
            showToast('Error: ' + (data.message || 'No se pudieron cargar los datos'), 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error de conexión: ' + error.message, 'error');
    }
}

// Subir foto de perfil
async function subirFoto(workerId, fileInput) {
    const file = fileInput.files && fileInput.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('foto', file);
    formData.append('id', workerId);

    // CSRF token
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    if (csrfMeta) formData.append('csrf_token', csrfMeta.content);
    const csrfInput = document.querySelector('input[name="csrf_token"]');
    if (csrfInput) formData.append('csrf_token', csrfInput.value);

    try {
        const response = await fetch(buildUrl('trabajadores/subirFoto'), {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        });
        const data = await response.json();
        if (!data.success) {
            showToast('Error al subir la foto: ' + (data.message || ''), 'error');
        }
    } catch (e) {
        showToast('Error de conexión al subir la foto', 'error');
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

            const dni = document.getElementById('dni').value.trim();
            if (dni && !validarDNI(dni)) {
                showToast('El DNI debe tener el formato: 8 números + 1 letra mayúscula (ej: 12345678A)', 'error');
                return;
            }

            const cuadrillaEl = document.getElementById('cuadrilla');
            const workerData = {
                nombre:    document.getElementById('nombre').value.trim(),
                dni:       document.getElementById('dni').value.trim(),
                ss:        document.getElementById('ss').value.trim(),
                alta_ss:   document.getElementById('alta_ss').value || null,
                cuadrilla: cuadrillaEl && cuadrillaEl.checked ? 1 : 0,
            };

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
                    // Subir foto si se seleccionó una
                    const fotoInput = document.getElementById('createFotoInput');
                    if (fotoInput && fotoInput.files && fotoInput.files[0] && data.id) {
                        await subirFoto(data.id, fotoInput);
                    }
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

            const dni = document.getElementById('editDni').value.trim();
            if (dni && !validarDNI(dni)) {
                showToast('El DNI debe tener el formato: 8 números + 1 letra mayúscula (ej: 12345678A)', 'error');
                return;
            }

            const id          = document.getElementById('editId').value;
            const cuadrillaEl = document.getElementById('editCuadrilla');
            const workerData  = {
                id:        id,
                nombre:    document.getElementById('editNombre').value.trim(),
                dni:       document.getElementById('editDni').value.trim(),
                ss:        document.getElementById('editSs').value.trim(),
                alta_ss:   document.getElementById('editAltaSs').value || null,
                cuadrilla: cuadrillaEl && cuadrillaEl.checked ? 1 : 0,
            };

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
                    // Subir foto si se seleccionó una nueva
                    const fotoInput = document.getElementById('editFotoInput');
                    if (fotoInput && fotoInput.files && fotoInput.files[0]) {
                        await subirFoto(id, fotoInput);
                    }
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

// Exportar funciones para uso global
window.openCreateModal  = openCreateModal;
window.openEditModal    = openEditModal;
window.closeEditModal   = closeEditModal;
window.editWorker       = editWorker;
window.deleteWorker     = deleteWorker;
window.reloadTable      = reloadTable;
window.previewFoto      = previewFoto;
