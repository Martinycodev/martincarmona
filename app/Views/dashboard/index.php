<?php
$title = 'Datos - MartinCarmona.com';
?>
<div class="container">

    <div class="quick-actions">

        <div class="quick-buttons">
            <a href="<?= $this->url('/busqueda') ?>" class="btn btn-info">🔍 Tareas</a>
            <a href="<?= $this->url('/economia?openModal=true') ?>" class="btn btn-primary">💰 Añadir Movimiento</a>
            <?php if (!empty($campanaActiva)): ?>
                <a href="<?= $this->url('/campana') ?>" class="btn btn-success">🫒 Campaña activa</a>
            <?php endif; ?>
            <a href="<?= $this->url('/riego') ?>" class="btn btn-secondary">💧 Riego</a>
        </div>
    </div>
    <br>

    <!-- Calendario Dinámico -->
    <div class="calendar-section">

        <div class="calendar-header">
            <div class="calendar-nav-left">
                <button class="calendar-nueva-btn" onclick="window.taskSidebar && window.taskSidebar.open()" title="Nueva tarea" aria-label="Crear nueva tarea">+</button>
                <button onclick="prevMonth()" class="calendar-nav-btn" aria-label="Mes anterior">◀</button>
            </div>


            <h3 id="monthYear" aria-live="polite"></h3>
            <div class="calendar-nav-right">

                <button onclick="nextMonth()" class="calendar-nav-btn" aria-label="Mes siguiente">▶</button>
                <button onclick="goToToday()" class="calendar-today-btn" aria-label="Ir al mes actual">📅 Hoy</button>
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

        <div class="calendar" id="calendar">
            <!-- Spinner de carga inicial — se reemplaza al renderizar el calendario -->
            <div class="calendar-spinner">
                <div class="calendar-spinner-ring"></div>
            </div>
        </div>

        <!-- Zona "sin fecha": arrastrar tareas aquí para quitarles la fecha -->
        <div id="dropzone-sin-fecha" class="dropzone-sin-fecha">
            ⏳ Arrastra aquí para quitar la fecha
        </div>

        <!-- Panel de tareas pendientes (dentro del bloque calendario) -->
        <div class="pending-panel" id="pending-panel">
            <div class="pending-header">
                <div class="pending-header-left">
                    <h3>⏳ Tareas pendientes</h3>
                    <span id="pending-count" class="pending-count">0</span>
                </div>
                <a href="<?= $this->url('/tareas/pendientes') ?>" class="btn btn-sm btn-secondary">Ver todas →</a>
            </div>

            <!-- Formulario inline para crear tarea pendiente -->
            <div class="pending-create-form" id="pendingCreateForm">
                <input type="text" id="pendingTaskTitle" placeholder="Nueva tarea pendiente..." class="pending-create-input" maxlength="200">
                <button type="button" id="pendingCreateBtn" class="btn btn-primary btn-sm" onclick="crearTareaPendiente()">+</button>
            </div>

            <div class="pending-list" id="pending-list">
                <!-- Se rellena por JS -->
            </div>
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
<style>
/* Spinner de carga del calendario */
.calendar-spinner {
    grid-column: 1 / -1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 4rem 0;
}
.calendar-spinner-ring {
    width: 32px;
    height: 32px;
    border: 2px solid #333;
    border-top-color: #4caf50;
    border-radius: 50%;
    animation: cal-spin .7s linear infinite;
}
@keyframes cal-spin {
    to { transform: rotate(360deg); }
}
</style>

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
            tasks[fecha].push(tarea.titulo || tarea.trabajo_nombre || 'Sin título');
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
        const daysInPrevMonth = new Date(year, month, 0).getDate();

        const calendar = document.getElementById("calendar");

        // Animación fade-in al cambiar de mes
        calendar.classList.add('ajax-fade-in');
        setTimeout(function() { calendar.classList.remove('ajax-fade-in'); }, 350);
        calendar.innerHTML = "";

        // Espacios en blanco antes del día 1 (ajustado para que la semana empiece en lunes)
        const startOffset = firstDay === 0 ? 6 : firstDay - 1;

        // Renderizar días del mes anterior
        for (let i = startOffset - 1; i >= 0; i--) {
            const prevMonthDay = daysInPrevMonth - i;
            calendar.innerHTML += `<div class="day other-month">
                    <div class="day-number">${prevMonthDay}</div>
                </div>`;
        }

        // Días del mes actual
        for (let day = 1; day <= daysInMonth; day++) {
            const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const isToday = dateStr === new Date().toISOString().split('T')[0];

            let dayHTML = `<div class="day ${isToday ? 'today' : ''}" data-date="${dateStr}" style="position: relative;">
                    <div class="day-number">${day}</div>
                    <button class="add-task-btn" onclick="window.taskSidebar && window.taskSidebar.open(null, '${dateStr}')" title="Nueva tarea">+</button>`;

            if (tasksData[dateStr]) {
                const tareas = tasksData[dateStr];
                const mobile = window.innerWidth <= 768;

                if (mobile && tareas.length > 2) {
                    // Móvil con +2 tareas: un punto verde + "x3"
                    dayHTML += `<div class="task-dots"><span class="task"></span><span class="task-count">x${tareas.length}</span></div>`;
                } else {
                    tareas.forEach((tarea) => {
                        // Priorizar título sobre nombre del trabajo, limitar a 7 palabras
                        let displayText = tarea.titulo || tarea.trabajo_nombre || 'Sin título';
                        const words = displayText.split(/\s+/);
                        if (words.length > 7) displayText = words.slice(0, 7).join(' ') + '…';
                        const cat = tarea.trabajo_categoria || 'otro';
                        dayHTML += `<div class="task task-cat-${cat}" draggable="true" data-id="${tarea.id}" data-fecha="${dateStr}" onclick="window.taskSidebar && window.taskSidebar.open(${tarea.id})" title="${tarea.descripcion || ''}">${displayText}</div>`;
                    });
                }
            }

            dayHTML += `</div>`;
            calendar.innerHTML += dayHTML;
        }

        // Calcular cuántos días del siguiente mes se necesitan para completar la cuadrícula
        const totalCells = startOffset + daysInMonth;
        const remainingCells = totalCells % 7 === 0 ? 0 : 7 - (totalCells % 7);

        // Renderizar días del siguiente mes
        for (let day = 1; day <= remainingCells; day++) {
            calendar.innerHTML += `<div class="day other-month">
                    <div class="day-number">${day}</div>
                </div>`;
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

    // Función para cargar datos y renderizar calendario
    async function cargarYRenderizarCalendario() {
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth() + 1; // Los meses en JavaScript empiezan en 0

        try {
            await cargarTareasMes(year, month);
            renderCalendar();
        } catch (error) {
            console.error('Error cargando calendario:', error);
        }
    }

    // ── Drag & Drop ────────────────────────────────────────────────────────────
    let _dragTareaId   = null;
    let _dragFechaOrig = null;

    function initDragDrop() {
        const calendar = document.getElementById('calendar');

        calendar.addEventListener('dragstart', function (e) {
            const task = e.target.closest('.task[draggable]');
            if (!task) return;
            _dragTareaId   = task.dataset.id;
            _dragFechaOrig = task.dataset.fecha;
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', _dragTareaId);
            setTimeout(() => task.classList.add('dragging'), 0);
            // Mostrar zona "sin fecha" al arrastrar tarea del calendario
            var dz = document.getElementById('dropzone-sin-fecha');
            if (dz) dz.style.display = 'flex';
        });

        calendar.addEventListener('dragend', function (e) {
            const task = e.target.closest('.task');
            if (task) task.classList.remove('dragging');
            calendar.querySelectorAll('.day.drag-over').forEach(d => d.classList.remove('drag-over'));
            // Ocultar zona "sin fecha"
            var dz = document.getElementById('dropzone-sin-fecha');
            if (dz) dz.style.display = 'none';
        });

        calendar.addEventListener('dragover', function (e) {
            const day = e.target.closest('.day');
            if (!day || day.classList.contains('other-month')) return;
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            calendar.querySelectorAll('.day.drag-over').forEach(d => d.classList.remove('drag-over'));
            day.classList.add('drag-over');
        });

        calendar.addEventListener('dragleave', function (e) {
            const day = e.target.closest('.day');
            if (day && !day.contains(e.relatedTarget)) {
                day.classList.remove('drag-over');
            }
        });

        calendar.addEventListener('drop', async function (e) {
            e.preventDefault();
            const day = e.target.closest('.day');
            calendar.querySelectorAll('.day.drag-over').forEach(d => d.classList.remove('drag-over'));

            if (!day || day.classList.contains('other-month') || !_dragTareaId) return;

            const newDate  = day.dataset.date;
            const tareaId  = _dragTareaId;
            const oldDate  = _dragFechaOrig;

            if (!newDate || newDate === oldDate) return;

            try {
                const res = await fetch('<?= $this->url("/tareas/actualizarCampo") ?>', {
                    method:  'POST',
                    headers: {
                        'Content-Type':     'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ id: tareaId, campo: 'fecha', valor: newDate })
                });
                const data = await res.json();

                if (data.success) {
                    // Invalidar cache de los meses afectados
                    tareasCache.delete(oldDate.substring(0, 7));
                    tareasCache.delete(newDate.substring(0, 7));
                    await cargarYRenderizarCalendario();
                } else {
                    console.error('Error al mover tarea:', data.message);
                }
            } catch (err) {
                console.error('Error en drag & drop:', err);
            }
        });
    }
    // ───────────────────────────────────────────────────────────────────────────

    // Exponer para que el sidebar pueda refrescar sin recargar la página
    window.refreshCalendar = async function() {
        tareasCache.clear();
        await cargarYRenderizarCalendario();
        await cargarPendientes();
    };

    // ── Modal de día en móvil ────────────────────────────────────────────
    // Al pulsar un día en pantallas ≤768px, se abre un bottom-sheet
    // con las tareas de ese día y un botón "Nueva tarea".
    // En desktop el click directo en la tarea abre el sidebar (sin cambios).

    function isMobile() {
        return window.innerWidth <= 768;
    }

    function initMobileDayModal() {
        // Crear el modal en el DOM si no existe
        if (!document.getElementById('mobileDayModal')) {
            var modalHTML = '<div id="mobileDayModal">'
                + '<div class="mobile-day-sheet">'
                +   '<div class="mobile-day-sheet-header">'
                +     '<div class="mobile-day-nav">'
                +       '<button class="mobile-day-nav-btn" onclick="navegarDia(-1)" title="Día anterior">◀</button>'
                +       '<h3 id="mobileDayTitle">—</h3>'
                +       '<button class="mobile-day-nav-btn" onclick="navegarDia(1)" title="Día siguiente">▶</button>'
                +     '</div>'
                +     '<button class="mobile-day-sheet-close" onclick="cerrarModalDia()">&times;</button>'
                +   '</div>'
                +   '<div class="mobile-day-sheet-body" id="mobileDayBody"></div>'
                + '</div>'
                + '</div>';
            document.body.insertAdjacentHTML('beforeend', modalHTML);

            // Cerrar al pulsar fuera del sheet
            document.getElementById('mobileDayModal').addEventListener('click', function(e) {
                if (e.target === this) cerrarModalDia();
            });
        }
    }

    // Fecha activa en el modal de día (para navegación con flechas)
    var _currentModalDate = null;

    // Navegar al día anterior o siguiente desde el modal
    window.navegarDia = function(offset) {
        if (!_currentModalDate) return;
        var d = new Date(_currentModalDate + 'T00:00:00');
        d.setDate(d.getDate() + offset);
        var newDate = d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
        abrirModalDia(newDate);
    };

    function abrirModalDia(dateStr) {
        _currentModalDate = dateStr;
        var modal = document.getElementById('mobileDayModal');
        var title = document.getElementById('mobileDayTitle');
        var body  = document.getElementById('mobileDayBody');

        // Formatear fecha legible: "15 de marzo"
        var parts = dateStr.split('-');
        var meses = ['enero','febrero','marzo','abril','mayo','junio',
                     'julio','agosto','septiembre','octubre','noviembre','diciembre'];
        title.textContent = parseInt(parts[2]) + ' de ' + meses[parseInt(parts[1]) - 1];

        // Construir lista de tareas del día
        var html = '';
        var tareasDelDia = tasksData[dateStr] || [];

        if (tareasDelDia.length === 0) {
            html += '<div class="mobile-day-empty">No hay tareas este día</div>';
        } else {
            tareasDelDia.forEach(function(tarea) {
                var nombre = tarea.titulo || tarea.trabajo_nombre || 'Sin título';
                html += '<div class="mobile-day-task" onclick="cerrarModalDia(); window.taskSidebar && window.taskSidebar.open(' + tarea.id + ')">'
                    + '<span class="mobile-day-task-name">' + nombre + '</span>'
                    + '<span class="mobile-day-task-arrow">›</span>'
                    + '</div>';
            });
        }

        html += '<button class="mobile-day-new-btn" onclick="cerrarModalDia(); window.taskSidebar && window.taskSidebar.open(null, \'' + dateStr + '\')">'
            + '+ Nueva tarea</button>';

        body.innerHTML = html;
        modal.classList.add('open');
    }

    window.cerrarModalDia = function() {
        var modal = document.getElementById('mobileDayModal');
        if (modal) modal.classList.remove('open');
    };

    // Interceptar clicks en días del calendario en móvil
    function initMobileDayTap() {
        var calendar = document.getElementById('calendar');
        calendar.addEventListener('click', function(e) {
            if (!isMobile()) return;

            // En móvil, cualquier toque en un día abre el modal (tareas incluidas)
            // para evitar problemas de precisión con guantes/dedos gordos
            var dayEl = e.target.closest('.day:not(.other-month)');
            if (!dayEl) return;

            var dateStr = dayEl.dataset.date;
            if (dateStr) {
                e.preventDefault();
                e.stopPropagation();
                abrirModalDia(dateStr);
            }
        });
    }

    // ── Swipe horizontal para cambiar de mes ─────────────────────────────
    // Detecta gestos de deslizamiento en el calendario y cambia de mes
    // con una animación CSS (clases .swipe-left / .swipe-right ya definidas).

    function initSwipeCalendar() {
        var calendar = document.getElementById('calendar');
        var touchStartX = 0;
        var touchStartY = 0;
        var swiping = false;

        calendar.addEventListener('touchstart', function(e) {
            touchStartX = e.touches[0].clientX;
            touchStartY = e.touches[0].clientY;
            swiping = true;
        }, { passive: true });

        calendar.addEventListener('touchmove', function(e) {
            if (!swiping) return;
            // Si el movimiento vertical es mayor que el horizontal, no es swipe
            var diffY = Math.abs(e.touches[0].clientY - touchStartY);
            var diffX = Math.abs(e.touches[0].clientX - touchStartX);
            if (diffY > diffX) {
                swiping = false;
            }
        }, { passive: true });

        calendar.addEventListener('touchend', function(e) {
            if (!swiping) return;
            swiping = false;

            var touchEndX = e.changedTouches[0].clientX;
            var diff = touchEndX - touchStartX;
            var MIN_SWIPE = 60; // px mínimos para considerar swipe

            if (Math.abs(diff) < MIN_SWIPE) return;

            if (diff < 0) {
                // Swipe izquierda → mes siguiente
                calendar.classList.add('swipe-left');
                setTimeout(async function() {
                    await nextMonth();
                    calendar.classList.remove('swipe-left');
                }, 300);
            } else {
                // Swipe derecha → mes anterior
                calendar.classList.add('swipe-right');
                setTimeout(async function() {
                    await prevMonth();
                    calendar.classList.remove('swipe-right');
                }, 300);
            }
        }, { passive: true });
    }

    // ── Panel de tareas pendientes (sin fecha) ─────────────────────────
    // Carga las tareas pendientes y las muestra como chips draggables.
    // Se pueden arrastrar al calendario para asignarles fecha.
    // Y se pueden arrastrar tareas del calendario a la zona "sin fecha".

    async function cargarPendientes() {
        try {
            var res = await fetch('<?= $this->url("/tareas/obtenerPendientes") ?>', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            var data = await res.json();
            if (!data.success) return;

            var list  = document.getElementById('pending-list');
            var count = document.getElementById('pending-count');
            if (!list) return;

            list.innerHTML = '';
            var tareas = data.tareas || [];
            count.textContent = tareas.length;

            if (tareas.length === 0) {
                list.innerHTML = '<div class="pending-empty">No hay tareas pendientes</div>';
                return;
            }

            tareas.forEach(function(t) {
                var chip = document.createElement('div');
                chip.className = 'pending-chip';
                chip.draggable = true;
                chip.dataset.id = t.id;
                chip.dataset.tipo = 'pendiente';

                var nombre = t.titulo || t.trabajos || 'Sin título';
                chip.innerHTML = '<span class="pending-chip-name">' + nombre + '</span>';

                // Click → abrir sidebar
                chip.addEventListener('click', function() {
                    window.taskSidebar && window.taskSidebar.open(t.id);
                });

                // Drag start
                chip.addEventListener('dragstart', function(e) {
                    e.dataTransfer.effectAllowed = 'move';
                    e.dataTransfer.setData('text/plain', t.id);
                    e.dataTransfer.setData('application/x-pendiente', 'true');
                    chip.classList.add('dragging');
                    // Mostrar zonas de drop en el calendario
                    document.getElementById('calendar').classList.add('receiving-pending');
                });

                chip.addEventListener('dragend', function() {
                    chip.classList.remove('dragging');
                    document.getElementById('calendar').classList.remove('receiving-pending');
                });

                list.appendChild(chip);
            });
        } catch (e) {
            // Silencioso
        }
    }

    // Drop de tarea pendiente → día del calendario
    function initPendingDrop() {
        var calendar = document.getElementById('calendar');

        // Las celdas .day ya aceptan drops del drag & drop existente.
        // Añadimos lógica para detectar si viene de pendientes.
        calendar.addEventListener('drop', async function(e) {
            var isPendiente = e.dataTransfer.types.includes('application/x-pendiente');
            if (!isPendiente) return; // El handler de drag & drop normal se encarga

            e.preventDefault();
            var day = e.target.closest('.day:not(.other-month)');
            if (!day) return;

            var tareaId = e.dataTransfer.getData('text/plain');
            var dateStr = day.dataset.date;
            if (!tareaId || !dateStr) return;

            calendar.querySelectorAll('.day.drag-over').forEach(function(d) { d.classList.remove('drag-over'); });

            try {
                var res = await fetch('<?= $this->url("/tareas/fechar") ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ id: parseInt(tareaId), fecha: dateStr })
                });
                var data = await res.json();

                if (data.success) {
                    showToast('Tarea asignada al ' + dateStr, 'success');
                    tareasCache.clear();
                    await cargarYRenderizarCalendario();
                    await cargarPendientes();
                }
            } catch (err) {
                showToast('Error al asignar fecha', 'error');
            }
        });
    }

    // Drop de tarea del calendario → zona "sin fecha"
    function initDropzoneSinFecha() {
        var dropzone = document.getElementById('dropzone-sin-fecha');
        if (!dropzone) return;

        dropzone.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            dropzone.classList.add('drag-over');
        });

        dropzone.addEventListener('dragleave', function() {
            dropzone.classList.remove('drag-over');
        });

        dropzone.addEventListener('drop', async function(e) {
            e.preventDefault();
            dropzone.classList.remove('drag-over');

            // Solo aceptar tareas del calendario (no pendientes)
            var isPendiente = e.dataTransfer.types.includes('application/x-pendiente');
            if (isPendiente) return;

            var tareaId = e.dataTransfer.getData('text/plain') || _dragTareaId;
            if (!tareaId) return;

            try {
                var res = await fetch('<?= $this->url("/tareas/desfechar") ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ id: parseInt(tareaId) })
                });
                var data = await res.json();

                if (data.success) {
                    showToast('Tarea movida a pendientes', 'success');
                    tareasCache.clear();
                    await cargarYRenderizarCalendario();
                    await cargarPendientes();
                }
            } catch (err) {
                showToast('Error al quitar fecha', 'error');
            }
        });
    }

    // ── Crear tarea pendiente inline ────────────────────────────────────
    async function crearTareaPendiente() {
        var input = document.getElementById('pendingTaskTitle');
        var titulo = (input.value || '').trim();
        if (!titulo) { input.focus(); return; }

        var btn = document.getElementById('pendingCreateBtn');
        if (typeof setButtonLoading === 'function') setButtonLoading(btn, true);

        try {
            var res = await fetch('<?= $this->url("/tareas/crearVacio") ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ titulo: titulo })
            });
            var data = await res.json();

            if (data.success) {
                input.value = '';
                showToast('Tarea pendiente creada', 'success');
                await cargarPendientes();
            } else {
                showToast(data.message || 'Error al crear tarea', 'error');
            }
        } catch (err) {
            showToast('Error de conexión', 'error');
        } finally {
            if (typeof setButtonLoading === 'function') setButtonLoading(btn, false);
        }
    }

    // Permitir crear con Enter
    document.addEventListener('keydown', function(e) {
        if (e.target && e.target.id === 'pendingTaskTitle' && e.key === 'Enter') {
            e.preventDefault();
            crearTareaPendiente();
        }
    });

    // Inicializar el calendario (AJAX-safe: funciona tanto en carga normal como AJAX)
    async function initCalendario() {
        await cargarYRenderizarCalendario();
        initDragDrop();
        initPendingDrop();
        initDropzoneSinFecha();
        initMobileDayModal();
        initMobileDayTap();
        initSwipeCalendar();
        await cargarPendientes();

        // Registrar y lanzar tour guiado del dashboard
        if (window.guidedTour) {
            window.guidedTour.register({
                id: 'dashboard',
                steps: [
                    {
                        selector: '.quick-buttons',
                        title: 'Acciones rapidas',
                        description: 'Desde aqui accedes a buscar tareas, registrar gastos/ingresos o gestionar el riego.',
                        position: 'bottom'
                    },
                    {
                        selector: '.calendar-nueva-btn',
                        title: 'Crear nueva tarea',
                        description: 'Pulsa este boton "+" para crear una nueva tarea. Se abrira un panel lateral donde podras anadir todos los detalles: trabajadores, parcelas y tipo de trabajo.',
                        position: 'right'
                    },
                    {
                        selector: '#calendar',
                        title: 'Calendario de tareas',
                        description: 'Aqui ves tus tareas organizadas por dia. Puedes <strong>arrastrar y soltar</strong> tareas entre dias para cambiar su fecha.',
                        position: 'top'
                    },
                    {
                        selector: '#pending-panel',
                        title: 'Tareas pendientes',
                        description: 'Las tareas sin fecha aparecen aqui. Puedes arrastrarlas al calendario cuando quieras planificarlas, o crear nuevas directamente.',
                        position: 'top'
                    },
                    {
                        selector: '.actions-grid',
                        title: 'Secciones principales',
                        description: 'Accede a las <strong>Bases de datos</strong> (trabajadores, parcelas, vehiculos...), la <strong>Economia</strong> (gastos e ingresos) y los <strong>Reportes</strong> (estadisticas y analisis).',
                        position: 'top'
                    }
                ]
            });
            // Auto-iniciar solo si no fue completado antes
            window.guidedTour.start('dashboard');
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCalendario);
    } else {
        initCalendario();
    }

   


</script>