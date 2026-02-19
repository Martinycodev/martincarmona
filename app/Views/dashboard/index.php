<?php
$title = 'Datos - MartinCarmona.com';
?>
<div class="container">

    <div class="quick-actions">

        <div class="quick-buttons">
            <a href="<?= $this->url('/tareas') ?>" class="btn">📋 Ver Tareas</a>
            <a href="<?= $this->url('/busqueda') ?>" class="btn btn-info">🔍 Búsqueda Avanzada</a>

            <a href="<?= $this->url('/economia?openModal=true') ?>" class="btn btn-primary">💰 Añadir Movimiento</a>
        </div>
    </div>
    <br>

    <!-- Calendario Dinámico -->
    <div class="calendar-section">

        <div class="calendar-header">
            <div class="calendar-nav-left">
                <button class="calendar-nueva-btn" onclick="window.taskSidebar && window.taskSidebar.open()" title="Nueva tarea">+</button>
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
    </div>

    <!-- Los modales de crear/editar/ver tarea han sido reemplazados por el sidebar (task-sidebar.js) -->


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
<script src="<?= $this->url('/public/js/task-details-view.js') ?>"></script>
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
            tasks[fecha].push(tarea.trabajo_nombre || tarea.descripcion || 'Sin descripción');
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

            let dayHTML = `<div class="day ${isToday ? 'today' : ''}" style="position: relative;">
                    <div class="day-number">${day}</div>
                    <button class="add-task-btn" onclick="window.taskSidebar && window.taskSidebar.open()" title="Nueva tarea">+</button>`;

            if (tasksData[dateStr]) {
                tasksData[dateStr].forEach((tarea, index) => {
                    const displayText = tarea.trabajo_nombre || tarea.descripcion || 'Sin descripción';
                    dayHTML += `<div class="task" onclick="window.taskSidebar && window.taskSidebar.open(${tarea.id})" title="${tarea.descripcion || ''}">${displayText.length > 20 ? displayText.substring(0, 20) + '...' : displayText}</div>`;
                });
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

    // Inicializar el calendario con datos del mes actual
    document.addEventListener('DOMContentLoaded', async function () {
        await cargarYRenderizarCalendario();
    });


</script>
</body>

</html>