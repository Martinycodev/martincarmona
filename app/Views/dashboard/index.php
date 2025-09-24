<?php 
$title = 'Datos - MartinCarmona.com';
include BASE_PATH . '/app/Views/layouts/header.php'; 
?>
        
    <div class="quick-actions">
            
            <div class="quick-buttons">
                <a href="<?= $this->url('/tareas') ?>" class="btn">📋 Ver Tareas</a>
                <a href="<?= $this->url('/busqueda') ?>" class="btn btn-info">🔍 Búsqueda Avanzada</a>
                <a href="<?= $this->url('/reportes') ?>" class="btn btn-secondary">📊 Reportes</a>
            </div>
        </div>
        <br>

        <!-- Calendario Dinámico -->
        <div class="calendar-section">
            
            <div class="calendar-header">
                <div class="calendar-nav-left">
                    <button class="calendar-nueva-btn" onclick="openCreateTaskModal('')" title="Agregar tarea">+</button>
                    <button onclick="prevMonth()" class="calendar-nav-btn">◀</button>
                </div>
               
                
                <h3 id="monthYear"></h3>
                <div class="calendar-nav-right">
                    
                    <button onclick="nextMonth()" class="calendar-nav-btn">▶</button>
                    <button onclick="goToToday()" class="calendar-today-btn">📅 Hoy</button>
                </div>
            </div>
            
            <!-- Encabezados de días de la semana -->
            <div class="calendar-weekdays">
                <div class="weekday">Lun</div>
                <div class="weekday">Mar</div>
                <div class="weekday">Mié</div>
                <div class="weekday">Jue</div>
                <div class="weekday">Vie</div>
                <div class="weekday">Sáb</div>
                <div class="weekday">Dom</div>
            </div>
            
            <div class="calendar" id="calendar"></div>
            
            <!-- Skeleton loading para el calendario -->
            <div class="calendar-skeleton" id="calendarSkeleton" style="display: none;">
                <!-- Generar 42 días skeleton (6 semanas × 7 días) -->
                <div class="skeleton-day">
                    <div class="skeleton-day-number"></div>
                    <div class="skeleton-task"></div>
                    <div class="skeleton-task"></div>
                </div>
                <div class="skeleton-day">
                    <div class="skeleton-day-number"></div>
                    <div class="skeleton-task"></div>
                </div>
                <div class="skeleton-day">
                    <div class="skeleton-day-number"></div>
                    <div class="skeleton-task"></div>
                    <div class="skeleton-task"></div>
                    <div class="skeleton-task"></div>
                </div>
                <div class="skeleton-day">
                    <div class="skeleton-day-number"></div>
                </div>
                <div class="skeleton-day">
                    <div class="skeleton-day-number"></div>
                    <div class="skeleton-task"></div>
                    <div class="skeleton-task"></div>
                </div>
                <div class="skeleton-day">
                    <div class="skeleton-day-number"></div>
                    <div class="skeleton-task"></div>
                </div>
                <div class="skeleton-day">
                    <div class="skeleton-day-number"></div>
                    <div class="skeleton-task"></div>
                    <div class="skeleton-task"></div>
                    <div class="skeleton-task"></div>
                </div>
                <div class="skeleton-day">
                    <div class="skeleton-day-number"></div>
                    <div class="skeleton-task"></div>
                </div>
                <div class="skeleton-day">
                    <div class="skeleton-day-number"></div>
                    <div class="skeleton-task"></div>
                    <div class="skeleton-task"></div>
                </div>
                <div class="skeleton-day">
                    <div class="skeleton-day-number"></div>
                </div>
                <div class="skeleton-day">
                    <div class="skeleton-day-number"></div>
                    <div class="skeleton-task"></div>
                </div>
                <div class="skeleton-day">
                    <div class="skeleton-day-number"></div>
                    <div class="skeleton-task"></div>
                    <div class="skeleton-task"></div>
                </div>
                <div class="skeleton-day">
                    <div class="skeleton-day-number"></div>
                    <div class="skeleton-task"></div>
                </div>
                <div class="skeleton-day">
                    <div class="skeleton-day-number"></div>
                    <div class="skeleton-task"></div>
                    <div class="skeleton-task"></div>
                    <div class="skeleton-task"></div>
                </div>
                <div class="skeleton-day">
                    <div class="skeleton-day-number"></div>
                </div>
                <div class="skeleton-day">
                    <div class="skeleton-day-number"></div>
                    <div class="skeleton-task"></div>
                    <div class="skeleton-task"></div>
                </div>
                <div class="skeleton-day">
                    <div class="skeleton-day-number"></div>
                    <div class="skeleton-task"></div>
                </div>
                <div class="skeleton-day">
                    <div class="skeleton-day-number"></div>
                    <div class="skeleton-task"></div>
                    <div class="skeleton-task"></div>
                </div>
                <div class="skeleton-day">
                    <div class="skeleton-day-number"></div>
                    <div class="skeleton-task"></div>
                </div>
                <div class="skeleton-day">
                    <div class="skeleton-day-number"></div>
                    <div class="skeleton-task"></div>
                    <div class="skeleton-task"></div>
                    <div class="skeleton-task"></div>
                </div>
                <div class="skeleton-day">
                    <div class="skeleton-day-number"></div>
                </div>
                <div class="skeleton-day">
                    <div class="skeleton-day-number"></div>
                    <div class="skeleton-task"></div>
                </div>
                <div class="skeleton-day">
                    <div class="skeleton-day-number"></div>
                    <div class="skeleton-task"></div>
                    <div class="skeleton-task"></div>
                </div>
                <div class="skeleton-day">
                    <div class="skeleton-day-number"></div>
                    <div class="skeleton-task"></div>
                </div>
                <div class="skeleton-day">
                    <div class="skeleton-day-number"></div>
                    <div class="skeleton-task"></div>
                    <div class="skeleton-task"></div>
                </div>
                <div class="skeleton-day">
                    <div class="skeleton-day-number"></div>
                    <div class="skeleton-task"></div>
                </div>
                <div class="skeleton-day">
                    <div class="skeleton-day-number"></div>
                    <div class="skeleton-task"></div>
                    <div class="skeleton-task"></div>
                    <div class="skeleton-task"></div>
                </div>
                <div class="skeleton-day">
                    <div class="skeleton-day-number"></div>
                </div>
                <div class="skeleton-day">
                    <div class="skeleton-day-number"></div>
                    <div class="skeleton-task"></div>
                    <div class="skeleton-task"></div>
                </div>
                <div class="skeleton-day">
                    <div class="skeleton-day-number"></div>
                    <div class="skeleton-task"></div>
                </div>
                <div class="skeleton-day">
                    <div class="skeleton-day-number"></div>
                    <div class="skeleton-task"></div>
                    <div class="skeleton-task"></div>
                </div>
                <div class="skeleton-day">
                    <div class="skeleton-day-number"></div>
                    <div class="skeleton-task"></div>
                </div>
                <div class="skeleton-day">
                    <div class="skeleton-day-number"></div>
                    <div class="skeleton-task"></div>
                    <div class="skeleton-task"></div>
                    <div class="skeleton-task"></div>
                </div>
                <div class="skeleton-day">
                    <div class="skeleton-day-number"></div>
                </div>
                <div class="skeleton-day">
                    <div class="skeleton-day-number"></div>
                    <div class="skeleton-task"></div>
                </div>
                <div class="skeleton-day">
                    <div class="skeleton-day-number"></div>
                    <div class="skeleton-task"></div>
                    <div class="skeleton-task"></div>
                </div>
                <div class="skeleton-day">
                    <div class="skeleton-day-number"></div>
                    <div class="skeleton-task"></div>
                </div>
                <div class="skeleton-day">
                    <div class="skeleton-day-number"></div>
                    <div class="skeleton-task"></div>
                    <div class="skeleton-task"></div>
                </div>
                <div class="skeleton-day">
                    <div class="skeleton-day-number"></div>
                    <div class="skeleton-task"></div>
                </div>
                <div class="skeleton-day">
                    <div class="skeleton-day-number"></div>
                    <div class="skeleton-task"></div>
                    <div class="skeleton-task"></div>
                    <div class="skeleton-task"></div>
                </div>
            </div>
        </div>
        
        <!-- Modal para Editar Tarea -->
        <div id="editTaskModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">✏️ Editar Tarea</h3>
                    <span class="close" onclick="closeModal('editTaskModal')">&times;</span>
                </div>
                <form id="editTaskForm" action="javascript:void(0);">
                    <input type="hidden" id="editTaskId" name="id">
                    <input type="hidden" id="editTaskUserId" name="user_id">
                    
                    <div class="form-group">
                        <label for="editTaskFecha">Fecha:</label>
                        <input type="date" id="editTaskFecha" name="fecha" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="editTaskDescripcion">Descripción:</label>
                        <textarea id="editTaskDescripcion" name="descripcion" rows="3" required></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="editTaskParcela">Parcela:</label>
                            <div class="autocomplete-wrapper">
                                <input type="text" id="editTaskParcela" name="parcela_nombre" autocomplete="off">
                                <input type="hidden" id="editTaskParcelaId" name="parcela" value="">
                                <div id="editParcelaResults" class="autocomplete-results" style="display: none;"></div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="editTaskTrabajador">Trabajadores:</label>
                            <div class="multi-select-wrapper">
                                <!-- Tags de trabajadores seleccionados -->
                                <div class="selected-workers" id="editSelectedWorkers"></div>
                                
                                <!-- Input de búsqueda -->
                                <div class="autocomplete-wrapper">
                                    <input type="text" 
                                           id="editTaskTrabajador" 
                                           name="trabajador_busqueda" 
                                           placeholder="Buscar trabajador..." 
                                           autocomplete="off">
                                    <div id="editTrabajadorResults" class="autocomplete-results" style="display: none;"></div>
                                </div>
                                
                                <!-- Inputs hidden para enviar los IDs -->
                                <div id="editWorkerHiddenInputs"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="editTaskTrabajo">Tipo de Trabajo:</label>
                            <div class="autocomplete-wrapper">
                                <input type="text" id="editTaskTrabajo" name="trabajo_nombre" autocomplete="off">
                                <input type="hidden" id="editTaskTrabajoId" name="trabajo" value="">
                                <div id="editTrabajoResults" class="autocomplete-results" style="display: none;"></div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="editTaskHoras">Horas:</label>
                            <input type="number" id="editTaskHoras" name="horas" min="0" step="0.5">
                        </div>
                    </div>
                    
                    <div class="modal-buttons">
                        <button type="button" class="btn-modal btn-danger" onclick="deleteTask()">🗑️ Eliminar</button>
                        <button type="button" class="btn-modal btn-secondary" onclick="closeModal('editTaskModal')">❌ Cancelar</button>
                        <button type="submit" class="btn-modal btn-primary">💾 Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Modal para Crear Nueva Tarea -->
        <div id="createTaskModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">➕ Nueva Tarea</h3>
                    <span class="close" onclick="closeModal('createTaskModal')">&times;</span>
                </div>
                <form id="createTaskForm" action="javascript:void(0);">
                    <div class="form-group">
                        <label for="createTaskFecha">Fecha:</label>
                        <input type="date" id="createTaskFecha" name="fecha" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="createTaskDescripcion">Descripción:</label>
                        <textarea id="createTaskDescripcion" name="descripcion" rows="3" required></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="createTaskParcela">Parcela:</label>
                            <div class="autocomplete-wrapper">
                                <input type="text" id="createTaskParcela" name="parcela_nombre" autocomplete="off">
                                <input type="hidden" id="createTaskParcelaId" name="parcela" value="">
                                <div id="parcelaResults" class="autocomplete-results" style="display: none;"></div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="createTaskTrabajador">Trabajadores:</label>
                            <div class="multi-select-wrapper">
                                <!-- Tags de trabajadores seleccionados -->
                                <div class="selected-workers" id="selectedWorkers"></div>
                                
                                <!-- Input de búsqueda -->
                                <div class="autocomplete-wrapper">
                                    <input type="text" 
                                           id="createTaskTrabajador" 
                                           name="trabajador_busqueda" 
                                           placeholder="Buscar trabajador..." 
                                           autocomplete="off">
                                    <div id="trabajadorResults" class="autocomplete-results" style="display: none;"></div>
                                </div>
                                
                                <!-- Inputs hidden para enviar los IDs -->
                                <div id="workerHiddenInputs"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="createTaskTrabajo">Tipo de Trabajo:</label>
                            <div class="autocomplete-wrapper">
                                <input type="text" id="createTaskTrabajo" name="trabajo_nombre" autocomplete="off">
                                <input type="hidden" id="createTaskTrabajoId" name="trabajo" value="">
                                <div id="trabajoResults" class="autocomplete-results" style="display: none;"></div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="createTaskHoras">Horas:</label>
                            <input type="number" id="createTaskHoras" name="horas" min="0" step="0.5">
                        </div>
                    </div>
                    
                    <div class="modal-buttons">
                        <button type="button" class="btn-modal btn-secondary" onclick="closeModal('createTaskModal')">❌ Cancelar</button>
                        <button type="submit" class="btn-modal btn-primary">➕ Crear Tarea</button>
                    </div>
                </form>
            </div>
        </div>
        

        <div class="actions-grid">
            <a href="<?= $this->url('/datos') ?>" class="action-card">
                <span class="action-icon">📚</span>
                <div class="action-title">Bases de datos</div>
                <div class="action-desc">Gestiona y visualiza todas los datos</div>
            </a>
            
            <a href="<?= $this->url('/economia') ?>" class="action-card">
                <span class="action-icon">💶</span>
                <div class="action-title">Economía</div>
                <div class="action-desc">Gestiona las finanzas y control de gastos</div>
            </a>

            
            <a href="<?= $this->url('/reportes') ?>" class="action-card">
                <span class="action-icon">📊</span>
                <div class="action-title">Reportes</div>
                <div class="action-desc">Panel completo de estadísticas y análisis</div>
            </a>
        </div>


        
    </div>
    
    <script>
        // Cache de tareas por mes para optimizar rendimiento
        const tareasCache = new Map();
        let tasks = {};
        let tasksData = {}; // Para almacenar los datos completos de las tareas
        
        // Función para cargar tareas de un mes específico
        async function cargarTareasMes(year, month) {
            const cacheKey = `${year}-${month.toString().padStart(2, '0')}`;
            
            // Verificar si ya tenemos los datos en cache
            if (tareasCache.has(cacheKey)) {
                const cachedData = tareasCache.get(cacheKey);
                procesarTareas(cachedData.tareas);
                return cachedData.tareas;
            }
            
            try {
                const response = await fetch(`<?= $this->url("/tareas/obtenerPorMes") ?>?year=${year}&month=${month}`);
                const data = await response.json();
                
                if (data.success) {
                    // Guardar en cache
                    tareasCache.set(cacheKey, data);
                    
                    // Procesar las tareas
                    procesarTareas(data.tareas);
                    return data.tareas;
                } else {
                    console.error('Error cargando tareas:', data.message);
                    return [];
                }
            } catch (error) {
                console.error('Error en la petición:', error);
                return [];
            }
        }
        
        // Función para procesar las tareas y actualizar las estructuras de datos
        function procesarTareas(tareas) {
            // Limpiar datos anteriores del mes
            tasks = {};
            tasksData = {};
            
            tareas.forEach(tarea => {
                const fecha = tarea.fecha;
                if (!tasks[fecha]) {
                    tasks[fecha] = [];
                    tasksData[fecha] = [];
                }
                tasks[fecha].push(tarea.descripcion);
                tasksData[fecha].push(tarea);
            });
        }
        
        const monthNames = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", 
                            "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
        let currentDate = new Date();
        
        function renderCalendar() {
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();
            document.getElementById("monthYear").innerText = `${monthNames[month]} ${year}`;

            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            
            const calendar = document.getElementById("calendar");
            calendar.innerHTML = "";

            // Espacios en blanco antes del día 1 (ajustado para que la semana empiece en lunes)
            const startOffset = firstDay === 0 ? 6 : firstDay - 1;
            for (let i = 0; i < startOffset; i++) {
                calendar.innerHTML += `<div></div>`;
            }

            // Días del mes
            for (let day = 1; day <= daysInMonth; day++) {
                const dateStr = `${year}-${String(month+1).padStart(2,'0')}-${String(day).padStart(2,'0')}`;
                const isToday = dateStr === new Date().toISOString().split('T')[0];
                
                let dayHTML = `<div class="day ${isToday ? 'today' : ''}" style="position: relative;">
                    <div class="day-number">${day}</div>
                    <button class="add-task-btn" onclick="openCreateTaskModal('${dateStr}')" title="Agregar tarea">+</button>`;
                
                if (tasks[dateStr]) {
                    tasks[dateStr].forEach((tarea, index) => {
                        dayHTML += `<div class="task" onclick="editTask('${dateStr}', ${index})" title="${tarea}">${tarea.length > 20 ? tarea.substring(0, 20) + '...' : tarea}</div>`;
                    });
                }
                
                dayHTML += `</div>`;
                calendar.innerHTML += dayHTML;
            }
        }

        // Funciones para las ventanas modales
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        function openCreateTaskModal(fecha) {
            document.getElementById('createTaskFecha').value = fecha;
            createWorkerSelector.clearAll(); // Limpiar selecciones previas
            openModal('createTaskModal');
        }
        
        function editTask(fecha, index) {
            const tarea = tasksData[fecha][index];
            if (tarea) {
                // Llenar el formulario de edición
                document.getElementById('editTaskId').value = tarea.id;
                document.getElementById('editTaskUserId').value = tarea.id_user || <?= $_SESSION['user_id'] ?? 0 ?>;
                document.getElementById('editTaskFecha').value = tarea.fecha;
                document.getElementById('editTaskDescripcion').value = tarea.descripcion;
                document.getElementById('editTaskParcela').value = tarea.parcela_nombre || '';
                document.getElementById('editTaskParcelaId').value = tarea.parcela || '';
                document.getElementById('editTaskHoras').value = tarea.horas || '';
                
                // Precargar trabajadores (si hay trabajadores en formato array)
                if (tarea.trabajadores && Array.isArray(tarea.trabajadores)) {
                    editWorkerSelector.preloadWorkers(tarea.trabajadores);
                } else if (tarea.trabajador && tarea.trabajador_nombre) {
                    // Compatibilidad con formato antiguo
                    editWorkerSelector.preloadWorkers([{
                        id: tarea.trabajador,
                        nombre: tarea.trabajador_nombre
                    }]);
                }
                
                openModal('editTaskModal');
            }
        }
        
        function deleteTask() {
            const taskId = document.getElementById('editTaskId').value;
            if (confirm('¿Estás seguro de que quieres eliminar esta tarea?')) {
                // Aquí iría la lógica para eliminar la tarea
                fetch('<?= $this->url("/tareas/eliminar") ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ id: taskId })
                })
                .then(response => response.json())
                            .then(data => {
                if (data.success) {
                    closeModal('editTaskModal');
                    // Invalidar cache del mes actual y recargar
                    const year = currentDate.getFullYear();
                    const month = currentDate.getMonth() + 1;
                    const cacheKey = `${year}-${month.toString().padStart(2, '0')}`;
                    tareasCache.delete(cacheKey);
                    cargarYRenderizarCalendario();
                } else {
                    alert('Error al eliminar la tarea: ' + data.message);
                }
            })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al eliminar la tarea');
                });
            }
        }
        
        async function prevMonth() {
            currentDate.setMonth(currentDate.getMonth() - 1);
            await cargarYRenderizarCalendario();
        }

        async function nextMonth() {
            currentDate.setMonth(currentDate.getMonth() + 1);
            await cargarYRenderizarCalendario();
        }
        
        async function goToToday() {
            currentDate = new Date();
            await cargarYRenderizarCalendario();
        }
        
        // Funciones para manejar el skeleton loading con animaciones
        function showSkeleton() {
            const calendar = document.getElementById('calendar');
            const skeleton = document.getElementById('calendarSkeleton');
            
            // Ocultar calendario con transición
            calendar.classList.add('loading');
            
            // Mostrar skeleton después de un pequeño delay
            setTimeout(() => {
                calendar.style.display = 'none';
                skeleton.style.display = 'grid';
                skeleton.classList.remove('hiding');
            }, 100);
        }
        
        function hideSkeleton() {
            const calendar = document.getElementById('calendar');
            const skeleton = document.getElementById('calendarSkeleton');
            
            // Animar salida del skeleton
            skeleton.classList.add('hiding');
            
            setTimeout(() => {
                skeleton.style.display = 'none';
                calendar.style.display = 'grid';
                calendar.classList.remove('loading');
                calendar.classList.add('show');
                
                // Limpiar la clase de animación después de la animación
                setTimeout(() => {
                    calendar.classList.remove('show');
                }, 400);
            }, 200);
        }
        
        // Función combinada para cargar datos y renderizar calendario
        async function cargarYRenderizarCalendario() {
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth() + 1; // Los meses en JavaScript empiezan en 0
            
            // Mostrar skeleton loading
            showSkeleton();
            
            try {
                await cargarTareasMes(year, month);
                
                // Pequeña pausa para que se vea el skeleton (opcional, mejora la percepción de velocidad)
                await new Promise(resolve => setTimeout(resolve, 200));
                
                renderCalendar();
            } finally {
                // Ocultar skeleton y mostrar calendario real
                hideSkeleton();
            }
        }
        
        // Manejo de formularios
        document.getElementById('createTaskForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const selectedWorkerIds = createWorkerSelector.getSelectedWorkerIds();
            
            console.log('Trabajadores seleccionados:', selectedWorkerIds);
            
            const taskData = {
                user_id: <?= $_SESSION['user_id'] ?? 0 ?>,
                fecha: formData.get('fecha'),
                descripcion: formData.get('descripcion'),
                parcela: parseInt(document.getElementById('createTaskParcelaId').value) || 0,
                trabajadores: selectedWorkerIds, // Array de IDs
                trabajo: parseInt(document.getElementById('createTaskTrabajoId').value) || 0,
                horas: parseFloat(formData.get('horas')) || 0
            };
            
            console.log('TaskData a enviar:', taskData);
            
            fetch('<?= $this->url("/tareas/crear") ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(taskData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeModal('createTaskModal');
                    // Invalidar cache del mes actual y recargar con skeleton
                    const year = currentDate.getFullYear();
                    const month = currentDate.getMonth() + 1;
                    const cacheKey = `${year}-${month.toString().padStart(2, '0')}`;
                    tareasCache.delete(cacheKey);
                    cargarYRenderizarCalendario();
                } else {
                    alert('Error al crear la tarea: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al crear la tarea');
            });
        });
        
        document.getElementById('editTaskForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            console.log('FormData edición antes de enviar:', Object.fromEntries(formData));
            const taskData = {
                id: parseInt(formData.get('id')),
                user_id: parseInt(formData.get('user_id')),
                fecha: formData.get('fecha'),
                descripcion: formData.get('descripcion'),
                parcela: parseInt(document.getElementById('editTaskParcelaId').value) || 0,
                trabajadores: editWorkerSelector.getSelectedWorkerIds(), // Obtener IDs de trabajadores seleccionados
                trabajo: parseInt(formData.get('trabajo')) || 0,
                horas: parseFloat(formData.get('horas')) || 0
            };
            
            fetch('<?= $this->url("/tareas/actualizar") ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(taskData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeModal('editTaskModal');
                    // Invalidar cache del mes actual y recargar
                    const year = currentDate.getFullYear();
                    const month = currentDate.getMonth() + 1;
                    const cacheKey = `${year}-${month.toString().padStart(2, '0')}`;
                    tareasCache.delete(cacheKey);
                    cargarYRenderizarCalendario();
                } else {
                    alert('Error al actualizar la tarea: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al actualizar la tarea');
            });
        });

        // Inicializar el calendario con datos del mes actual
        document.addEventListener('DOMContentLoaded', async function() {
            await cargarYRenderizarCalendario();
        });

        // ====== PRESERVAR FUNCIONES EXISTENTES DE PARCELAS Y TRABAJOS ======

        // Función para manejar la selección de parcela (MANTENER ORIGINAL)
        function handleParcelaSelect(element, inputId) {
            console.log('Elemento seleccionado:', element);
            console.log('Input ID:', inputId);
            
            const id = element.getAttribute('data-id');
            const nombre = element.getAttribute('data-name');
            const hiddenInputId = inputId === 'createTaskParcela' ? 'createTaskParcelaId' : 'editTaskParcelaId';
            const resultsId = inputId === 'createTaskParcela' ? 'parcelaResults' : 'editParcelaResults';
            
            console.log('Datos a insertar:', { id, nombre, hiddenInputId, resultsId });
            
            // Actualizar campos
            document.getElementById(inputId).value = nombre;
            document.getElementById(hiddenInputId).value = id;
            document.getElementById(resultsId).style.display = 'none';
            
            console.log('Campos actualizados');
        }

        function highlightParcela(resultsId, index) {
            const items = document.getElementById(resultsId).getElementsByClassName('autocomplete-item');
            for (let item of items) {
                item.classList.remove('selected');
            }
            if (items[index]) {
                items[index].classList.add('selected');
            }
        }



        // Configurar autocompletado para parcelas (MANTENER ORIGINAL)
        function setupAutocomplete(inputId, resultsId, hiddenInputId) {
            const input = document.getElementById(inputId);
            const results = document.getElementById(resultsId);
            const hiddenInput = document.getElementById(hiddenInputId);
            let selectedIndex = -1;

            input.addEventListener('input', function() {
                console.log('Input event triggered');
                const query = this.value.trim();
                if (query.length >= 3) {
                    console.log('Fetching results for:', query);
                    fetch(`<?= $this->url("/parcelas/buscar") ?>?q=${encodeURIComponent(query)}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => {
                        console.log('Response received:', response);
                        return response.json();
                    })
                    .then(data => {
                        console.log('Data received:', data);
                        if (data.length > 0) {
                            results.innerHTML = data
                                .map((parcela, index) => `
                                    <div class="autocomplete-item" 
                                         data-id="${parcela.id}" 
                                         data-name="${parcela.nombre}"
                                         onclick="handleParcelaSelect(this, '${inputId}')"
                                         onmouseover="highlightParcela('${resultsId}', ${index})">
                                        ${parcela.nombre} (${parcela.olivos} olivos)
                                    </div>
                                `).join('');
                        } else {
                            results.innerHTML = `
                                <div class="autocomplete-item no-results">
                                    No hay coincidencias
                                </div>`;
                        }

                        results.style.display = 'block';
                    })
                    .catch(error => {
                        console.error('Error en autocompletado:', error);
                    });
                } else {
                    results.style.display = 'none';
                }
            });

            input.addEventListener('keydown', function(e) {
                const items = results.getElementsByClassName('autocomplete-item');
                if (items.length === 0) return;

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    selectedIndex = (selectedIndex + 1) % items.length;
                    highlightParcela(resultsId, selectedIndex);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    selectedIndex = selectedIndex <= 0 ? items.length - 1 : selectedIndex - 1;
                    highlightParcela(resultsId, selectedIndex);
                } else if (e.key === 'Enter' && selectedIndex >= 0) {
                    e.preventDefault();
                    const selected = items[selectedIndex];
                    handleParcelaSelect(selected, inputId);
                }
            });
        }

        // Configurar autocompletado para trabajos (MANTENER ORIGINAL)
        function setupTrabajoAutocomplete(inputId, resultsId, hiddenInputId) {
            const input = document.getElementById(inputId);
            const results = document.getElementById(resultsId);
            let selectedIndex = -1;

            // Crear el contenedor de resultados si no existe
            if (!document.getElementById(resultsId)) {
                const resultsDiv = document.createElement('div');
                resultsDiv.id = resultsId;
                resultsDiv.className = 'autocomplete-results';
                resultsDiv.style.display = 'none';
                input.parentNode.appendChild(resultsDiv);
            }

            input.addEventListener('input', function() {
                console.log('Input trabajo event triggered');
                const query = this.value.trim();
                if (query.length >= 2) {
                    console.log('Buscando trabajo:', query);
                    fetch(`<?= $this->url("/trabajos/buscar") ?>?q=${encodeURIComponent(query)}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Trabajos encontrados:', data);
                        try {
                            // Asegurarse de que data es un array
                            const trabajos = Array.isArray(data) ? data : [];
                            
                            if (trabajos.length > 0) {
                                results.innerHTML = trabajos
                                    .map((trabajo, index) => `
                                        <div class="autocomplete-item" 
                                             data-id="${trabajo.id}" 
                                             data-name="${trabajo.nombre}"
                                             onclick="handleTrabajoSelect(this, '${inputId}')"
                                             onmouseover="highlightTrabajo('${resultsId}', ${index})">
                                            ${trabajo.nombre}
                                        </div>
                                    `).join('');
                            } else {
                                results.innerHTML = `
                                    <div class="autocomplete-item no-results">
                                        No hay coincidencias
                                    </div>`;
                            }
                        } catch (error) {
                            console.error('Error al procesar datos:', error);
                            results.innerHTML = `
                                <div class="autocomplete-item no-results" style="cursor: default; background-color: #f5f5f5; color: #999;">
                                    No hay coincidencias
                                </div>`;
                        }

                        results.style.display = 'block';
                    })
                    .catch(error => {
                        console.error('Error en autocompletado de trabajo:', error);
                    });
                } else {
                    results.style.display = 'none';
                }
            });

            input.addEventListener('keydown', function(e) {
                const items = results.getElementsByClassName('autocomplete-item');
                if (items.length === 0) return;

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    selectedIndex = (selectedIndex + 1) % items.length;
                    highlightTrabajo(resultsId, selectedIndex);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    selectedIndex = selectedIndex <= 0 ? items.length - 1 : selectedIndex - 1;
                    highlightTrabajo(resultsId, selectedIndex);
                } else if (e.key === 'Enter' && selectedIndex >= 0) {
                    e.preventDefault();
                    const selected = items[selectedIndex];
                    handleTrabajoSelect(selected, inputId);
                }
            });
        }

        function handleTrabajoSelect(element, inputId) {
            console.log('Trabajo seleccionado:', element);
            console.log('Input ID:', inputId);
            
            const id = element.getAttribute('data-id');
            const nombre = element.getAttribute('data-name');
            const hiddenInputId = inputId === 'createTaskTrabajo' ? 'createTaskTrabajoId' : 'editTaskTrabajoId';
            const resultsId = inputId === 'createTaskTrabajo' ? 'trabajoResults' : 'editTrabajoResults';
            
            console.log('Datos de trabajo a insertar:', { id, nombre, hiddenInputId, resultsId });
            
            // Actualizar campos
            document.getElementById(inputId).value = nombre;
            if (document.getElementById(hiddenInputId)) {
                document.getElementById(hiddenInputId).value = id;
            }
            document.getElementById(resultsId).style.display = 'none';
            
            console.log('Campos de trabajo actualizados');
        }

        function highlightTrabajo(resultsId, index) {
            const items = document.getElementById(resultsId).getElementsByClassName('autocomplete-item');
            for (let item of items) {
                item.classList.remove('selected');
            }
            if (items[index]) {
                items[index].classList.add('selected');
            }
        }

        // ====== NUEVO SISTEMA DE SELECCIÓN MÚLTIPLE DE TRABAJADORES ======
        class MultiWorkerSelector {
            constructor(inputId, resultsId, selectedWorkersId, hiddenInputsId) {
                this.input = document.getElementById(inputId);
                this.results = document.getElementById(resultsId);
                this.selectedWorkersContainer = document.getElementById(selectedWorkersId);
                this.hiddenInputsContainer = document.getElementById(hiddenInputsId);
                this.selectedWorkers = new Map();
                this.selectedIndex = -1;
                
                this.init();
            }
            
            init() {
                this.setupEventListeners();
            }
            
            setupEventListeners() {
                this.input.addEventListener('input', (e) => {
                    this.handleSearch(e.target.value.trim());
                });
                
                this.input.addEventListener('keydown', (e) => {
                    this.handleKeydown(e);
                });
                
                this.input.addEventListener('focus', () => {
                    this.input.value = '';
                });
            }
            
            async handleSearch(query) {
                if (query.length >= 2) {
                    try {
                        const response = await fetch(`<?= $this->url("/trabajadores/buscar") ?>?q=${encodeURIComponent(query)}`, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        const workers = await response.json();
                        this.displayResults(workers);
                    } catch (error) {
                        console.error('Error en búsqueda de trabajadores:', error);
                    }
                } else {
                    this.hideResults();
                }
            }
            
            displayResults(workers) {
                if (workers.length === 0) {
                    this.results.innerHTML = '<div class="autocomplete-item no-results">No hay coincidencias</div>';
                } else {
                    this.results.innerHTML = workers
                        .map((worker, index) => {
                            const isSelected = this.selectedWorkers.has(worker.id);
                            const extraClass = isSelected ? 'selected-worker' : '';
                            
                            return `
                                <div class="autocomplete-item ${extraClass}" 
                                     data-id="${worker.id}" 
                                     data-name="${worker.nombre}"
                                     data-index="${index}"
                                     onclick="${isSelected ? '' : `this.multiSelector.selectWorker(${worker.id}, '${worker.nombre.replace(/'/g, "\\'")}')`}">
                                    <strong>${worker.nombre}</strong>
                                    ${worker.dni ? `<br><small>DNI: ${worker.dni}</small>` : ''}
                                    ${worker.ss ? `<br><small>SS: ${worker.ss}</small>` : ''}
                                </div>
                            `;
                        }).join('');
                        
                    this.results.querySelectorAll('.autocomplete-item').forEach(item => {
                        item.multiSelector = this;
                    });
                }
                this.showResults();
                this.selectedIndex = -1;
            }
            
            selectWorker(id, name) {
                if (this.selectedWorkers.has(id)) return;
                
                this.selectedWorkers.set(id, { id, name });
                this.createWorkerTag(id, name);
                this.createHiddenInput(id);
                this.input.value = '';
                this.hideResults();
                this.updatePlaceholder();
                
                console.log('Trabajador seleccionado:', { id, name });
            }
            
            createWorkerTag(id, name) {
                const tag = document.createElement('div');
                tag.className = 'worker-tag';
                tag.dataset.workerId = id;
                tag.innerHTML = `
                    <span>${name}</span>
                    <button type="button" class="remove-worker" onclick="this.closest('.worker-tag').multiSelector.removeWorker(${id})">×</button>
                `;
                
                tag.multiSelector = this;
                this.selectedWorkersContainer.appendChild(tag);
            }
            
            createHiddenInput(id) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'trabajadores[]';
                input.value = id;
                input.dataset.workerId = id;
                
                this.hiddenInputsContainer.appendChild(input);
            }
            
            removeWorker(id) {
                this.selectedWorkers.delete(id);
                
                const tag = this.selectedWorkersContainer.querySelector(`[data-worker-id="${id}"]`);
                if (tag) {
                    tag.style.animation = 'slideOut 0.3s ease-in forwards';
                    setTimeout(() => tag.remove(), 300);
                }
                
                const hiddenInput = this.hiddenInputsContainer.querySelector(`[data-worker-id="${id}"]`);
                if (hiddenInput) hiddenInput.remove();
                
                this.updatePlaceholder();
            }
            
            updatePlaceholder() {
                const count = this.selectedWorkers.size;
                if (count === 0) {
                    this.input.placeholder = 'Buscar trabajador...';
                } else {
                    this.input.placeholder = `${count} trabajador${count > 1 ? 'es' : ''} seleccionado${count > 1 ? 's' : ''}. Buscar más...`;
                }
            }
            
            handleKeydown(e) {
                const items = this.results.querySelectorAll('.autocomplete-item:not(.selected-worker)');
                if (items.length === 0) return;
                
                switch (e.key) {
                    case 'ArrowDown':
                        e.preventDefault();
                        this.selectedIndex = (this.selectedIndex + 1) % items.length;
                        this.highlightItem(items);
                        break;
                    case 'ArrowUp':
                        e.preventDefault();
                        this.selectedIndex = this.selectedIndex <= 0 ? items.length - 1 : this.selectedIndex - 1;
                        this.highlightItem(items);
                        break;
                    case 'Enter':
                        e.preventDefault();
                        if (this.selectedIndex >= 0 && items[this.selectedIndex]) {
                            const item = items[this.selectedIndex];
                            const id = item.dataset.id;
                            const name = item.dataset.name;
                            this.selectWorker(id, name);
                        }
                        break;
                    case 'Escape':
                        this.hideResults();
                        this.input.blur();
                        break;
                }
            }
            
            highlightItem(items) {
                items.forEach(item => item.classList.remove('selected'));
                if (items[this.selectedIndex]) {
                    items[this.selectedIndex].classList.add('selected');
                }
            }
            
            showResults() {
                this.results.style.display = 'block';
            }
            
            hideResults() {
                this.results.style.display = 'none';
                this.selectedIndex = -1;
            }
            
            clearAll() {
                this.selectedWorkers.clear();
                this.selectedWorkersContainer.innerHTML = '';
                this.hiddenInputsContainer.innerHTML = '';
                this.updatePlaceholder();
            }
            
            preloadWorkers(workers) {
                this.clearAll();
                workers.forEach(worker => {
                    this.selectWorker(worker.id, worker.nombre);
                });
            }
            
            getSelectedWorkerIds() {
                return Array.from(this.selectedWorkers.keys());
            }
        }

        // Instanciar selectores múltiples
        const createWorkerSelector = new MultiWorkerSelector(
            'createTaskTrabajador',
            'trabajadorResults', 
            'selectedWorkers',
            'workerHiddenInputs'
        );

        const editWorkerSelector = new MultiWorkerSelector(
            'editTaskTrabajador',
            'editTrabajadorResults',
            'editSelectedWorkers', 
            'editWorkerHiddenInputs'
        );

        // Función actualizada para abrir modal de crear
        function openCreateTaskModal(fecha) {
            document.getElementById('createTaskFecha').value = fecha;
            createWorkerSelector.clearAll();
            openModal('createTaskModal');
        }

        // Función actualizada para editar tarea
        function editTask(fecha, index) {
            const tarea = tasksData[fecha][index];
            if (tarea) {
                document.getElementById('editTaskId').value = tarea.id;
                document.getElementById('editTaskUserId').value = tarea.id_user || <?= $_SESSION['user_id'] ?? 0 ?>;
                document.getElementById('editTaskFecha').value = tarea.fecha;
                document.getElementById('editTaskDescripcion').value = tarea.descripcion;
                document.getElementById('editTaskParcela').value = tarea.parcela_nombre || '';
                document.getElementById('editTaskParcelaId').value = tarea.parcela || '';
                document.getElementById('editTaskTrabajo').value = tarea.trabajo_nombre || '';
                document.getElementById('editTaskTrabajoId').value = tarea.trabajo || '';
                document.getElementById('editTaskHoras').value = tarea.horas || '';
                
                // Precargar trabajadores
                if (tarea.trabajadores && Array.isArray(tarea.trabajadores)) {
                    editWorkerSelector.preloadWorkers(tarea.trabajadores);
                } else if (tarea.trabajador && tarea.trabajador_nombre) {
                    editWorkerSelector.preloadWorkers([{
                        id: tarea.trabajador,
                        nombre: tarea.trabajador_nombre
                    }]);
                }
                
                openModal('editTaskModal');
            }
        }



        // Cerrar resultados al hacer clic fuera
        document.addEventListener('click', function(e) {
            // Parcelas
            const createWrapper = document.querySelector('#createTaskParcela').closest('.autocomplete-wrapper');
            const editWrapper = document.querySelector('#editTaskParcela').closest('.autocomplete-wrapper');
            
            if (!createWrapper.contains(e.target)) {
                document.getElementById('parcelaResults').style.display = 'none';
            }
            if (!editWrapper.contains(e.target)) {
                document.getElementById('editParcelaResults').style.display = 'none';
            }

            // Trabajadores múltiples
            const createTrabajadorWrapper = document.querySelector('#createTaskTrabajador').closest('.multi-select-wrapper');
            const editTrabajadorWrapper = document.querySelector('#editTaskTrabajador').closest('.multi-select-wrapper');
            
            if (createTrabajadorWrapper && !createTrabajadorWrapper.contains(e.target)) {
                createWorkerSelector.hideResults();
            }
            if (editTrabajadorWrapper && !editTrabajadorWrapper.contains(e.target)) {
                editWorkerSelector.hideResults();
            }

            // Trabajos
            const createTrabajoWrapper = document.querySelector('#createTaskTrabajo').closest('.autocomplete-wrapper');
            const editTrabajoWrapper = document.querySelector('#editTaskTrabajo').closest('.autocomplete-wrapper');
            
            if (createTrabajoWrapper && !createTrabajoWrapper.contains(e.target)) {
                document.getElementById('trabajoResults').style.display = 'none';
            }
            if (editTrabajoWrapper && !editTrabajoWrapper.contains(e.target)) {
                document.getElementById('editTrabajoResults').style.display = 'none';
            }
        });



        // Inicializar autocompletado para todos los campos
        setupAutocomplete('createTaskParcela', 'parcelaResults', 'createTaskParcelaId');
        setupAutocomplete('editTaskParcela', 'editParcelaResults', 'editTaskParcelaId');
        setupTrabajoAutocomplete('createTaskTrabajo', 'trabajoResults', 'createTaskTrabajoId');
        setupTrabajoAutocomplete('editTaskTrabajo', 'editTrabajoResults', 'editTaskTrabajoId');


    </script>
</body>
</html>
