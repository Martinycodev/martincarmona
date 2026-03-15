<?php
$title = 'Datos - MartinCarmona.com';
?>
<div class="container">

    <div class="quick-actions">

        <div class="quick-buttons">
            <a href="<?= $this->url('/tareas') ?>" class="btn">📋 Ver Tareas</a>
            <a href="<?= $this->url('/tareas/pendientes') ?>" class="btn btn-secondary">⏳ Tareas Pendientes</a>
            <a href="<?= $this->url('/busqueda') ?>" class="btn btn-info">🔍 Búsqueda Avanzada</a>
            <a href="<?= $this->url('/economia?openModal=true') ?>" class="btn btn-primary">💰 Añadir Movimiento</a>
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

        <div class="calendar" id="calendar"></div>
    </div>

    <!-- Widget Meteorología -->
    <div id="weather-widget" class="weather-widget">
        <div class="weather-loading">🌤️ Cargando tiempo…</div>
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
            tasks[fecha].push(tarea.trabajo_nombre || tarea.titulo || 'Sin título');
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
                tasksData[dateStr].forEach((tarea, index) => {
                    const displayText = tarea.trabajo_nombre || tarea.titulo || 'Sin título';
                    dayHTML += `<div class="task" draggable="true" data-id="${tarea.id}" data-fecha="${dateStr}" onclick="window.taskSidebar && window.taskSidebar.open(${tarea.id})" title="${tarea.descripcion || ''}">${displayText.length > 20 ? displayText.substring(0, 20) + '...' : displayText}</div>`;
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
        });

        calendar.addEventListener('dragend', function (e) {
            const task = e.target.closest('.task');
            if (task) task.classList.remove('dragging');
            calendar.querySelectorAll('.day.drag-over').forEach(d => d.classList.remove('drag-over'));
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
                +     '<h3 id="mobileDayTitle">—</h3>'
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

    function abrirModalDia(dateStr) {
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
                var nombre = tarea.trabajo_nombre || tarea.titulo || 'Sin título';
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

            // Si el click fue directamente en un .task, dejar que el onclick nativo actúe
            if (e.target.closest('.task')) return;
            // Si fue en el botón "+", dejar que actúe
            if (e.target.closest('.add-task-btn')) return;

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

    // Inicializar el calendario con datos del mes actual
    document.addEventListener('DOMContentLoaded', async function () {
        await cargarYRenderizarCalendario();
        initDragDrop();
        initMobileDayModal();
        initMobileDayTap();
        initSwipeCalendar();
    });

    // ── Widget Meteorología ────────────────────────────────────────────────────
    (function initWeatherWidget() {
        // ── Configuración de la explotación ──────────────────────────────────
        const LAT  = 38.00;       // Latitud de la explotación
        const LON  = -4.11;       // Longitud de la explotación
        const CITY = 'Arjonilla';      // Nombre del municipio
        // ─────────────────────────────────────────────────────────────────────

        const WMO_ICONS = {
            0:'☀️', 1:'🌤️', 2:'⛅', 3:'☁️',
            45:'🌫️', 48:'🌫️',
            51:'🌦️', 53:'🌦️', 55:'🌦️', 56:'🌧️', 57:'🌧️',
            61:'🌧️', 63:'🌧️', 65:'🌧️', 66:'🌧️', 67:'🌧️',
            71:'❄️',  73:'❄️',  75:'❄️',  77:'🌨️',
            80:'🌦️', 81:'🌦️', 82:'🌦️',
            85:'🌨️', 86:'🌨️',
            95:'⛈️', 96:'⛈️', 99:'⛈️'
        };

        const WMO_DESC = {
            0:'Despejado', 1:'Mayormente despejado', 2:'Parcialmente nublado', 3:'Nublado',
            45:'Niebla', 48:'Niebla con escarcha',
            51:'Llovizna ligera', 53:'Llovizna', 55:'Llovizna intensa',
            56:'Llovizna helada', 57:'Llovizna helada intensa',
            61:'Lluvia ligera', 63:'Lluvia', 65:'Lluvia intensa',
            66:'Lluvia helada', 67:'Lluvia helada intensa',
            71:'Nieve ligera', 73:'Nieve', 75:'Nieve intensa', 77:'Granizo',
            80:'Chubascos ligeros', 81:'Chubascos', 82:'Chubascos intensos',
            85:'Nieve con chubascos', 86:'Nieve intensa con chubascos',
            95:'Tormenta', 96:'Tormenta con granizo', 99:'Tormenta con granizo intenso'
        };

        const DIAS = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];

        function icon(code) { return WMO_ICONS[code] ?? '🌡️'; }
        function desc(code) { return WMO_DESC[code] ?? 'Desconocido'; }
        function round(n)   { return Math.round(n); }

        async function cargarTiempo() {
            const url = `https://api.open-meteo.com/v1/forecast`
                + `?latitude=${LAT}&longitude=${LON}`
                + `&current_weather=true`
                + `&daily=weathercode,temperature_2m_max,temperature_2m_min,precipitation_sum`
                + `&timezone=Europe%2FMadrid&forecast_days=7`;

            try {
                const res  = await fetch(url);
                const data = await res.json();
                renderWidget(data);
            } catch (e) {
                const w = document.getElementById('weather-widget');
                if (w) w.innerHTML = '<div class="weather-error">No se pudo cargar el tiempo</div>';
            }
        }

        function renderWidget(data) {
            const w = document.getElementById('weather-widget');
            if (!w) return;

            const cw   = data.current_weather;
            const temp = round(cw.temperature);
            const code = cw.weathercode;
            const daily = data.daily;

            // Pronóstico 7 días
            let forecastHTML = '';
            for (let i = 0; i < 7; i++) {
                const date  = new Date(daily.time[i] + 'T12:00:00');
                const label = i === 0 ? 'Hoy' : DIAS[date.getDay()];
                const wcode = daily.weathercode[i];
                const tmax  = round(daily.temperature_2m_max[i]);
                const tmin  = round(daily.temperature_2m_min[i]);
                const prec  = daily.precipitation_sum[i];
                forecastHTML += `
                    <div class="weather-day">
                        <span class="wd-label">${label}</span>
                        <span class="wd-icon">${icon(wcode)}</span>
                        <span class="wd-max">${tmax}°</span>
                        <span class="wd-min">${tmin}°</span>
                        ${prec > 0 ? `<span class="wd-prec">💧${prec.toFixed(1)}mm</span>` : '<span class="wd-prec"></span>'}
                    </div>`;
            }

            w.innerHTML = `
                <div class="weather-current">
                    <span class="wc-icon">${icon(code)}</span>
                    <div class="wc-info">
                        <span class="wc-temp">${temp}°C</span>
                        <span class="wc-desc">${desc(code)}</span>
                        <span class="wc-city">📍 ${CITY}</span>
                    </div>
                </div>
                <div class="weather-forecast">${forecastHTML}</div>`;
        }

        // Ejecutar inmediatamente (funciona en carga AJAX y carga directa)
        cargarTiempo();
    })();
    // ───────────────────────────────────────────────────────────────────────────


</script>