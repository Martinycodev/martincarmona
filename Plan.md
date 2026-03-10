Plan: Categorización y Orden de Tareas Pendientes
Context
El proyecto MartinCarmona es una aplicación PHP MVC para gestión agrícola (parcelas, trabajadores, campañas, fitosanitarios, economía). Se han identificado 11 tareas pendientes que incluyen bugs críticos, mejoras de vistas y nuevas funcionalidades. El objetivo es ordenarlas por impacto, dependencias y tiempo estimado para ejecutarlas de manera eficiente.

Categorización de Tareas
🔴 CATEGORÍA 1 — Bugs Críticos (bloquean funcionalidad)
Prioridad máxima. Son bugs que impiden usar partes del sistema.

#	Tarea	Archivo principal	Estimación
1	Botón "Añadir Movimiento" del dashboard no va	app/Views/dashboard/index.php, app/Views/economia/index.php, app/Controllers/EconomiaController.php	15–30 min
2	Botón "Añadir Producto" de fitosanitarios no va (y aplicaciones tampoco)	app/Views/fitosanitarios/index.php, app/Controllers/FitosanitariosController.php	30–60 min
3	Botón "Nueva Campaña" no funciona	app/Views/campana/index.php, app/Controllers/CampanaController.php	20–30 min
4	Botón "Eliminar Campaña" no funciona	Mismo contexto que tarea 3	10–15 min (junto con tarea 3)
Nota de dependencia crítica: Las tareas 2 (fitosanitarios) son especialmente importantes porque afectan al inventario automático y al registro de aplicaciones. Hay lógica en Tarea::create() que descuenta del inventario cuando se crean tareas de tipo "Sulfato" o "Herbicida". Si el modal de añadir producto no va, el inventario no se puede gestionar correctamente, y las aplicaciones quedan inutilizadas.

🟠 CATEGORÍA 2 — Bugs de Datos y Presentación
Rápidos de resolver, impactan la fiabilidad visual del sistema.

#	Tarea	Archivo principal	Estimación
5	Deuda muestra 59,99€ en vez de 60€ (problema de redondeo)	app/Models/PagoMensual.php, app/Views/economia/deudas.php	10–20 min
6	Modal de "Gestión de Trabajos" no funciona correctamente	app/Views/trabajos/index.php, public/js/trabajos.js	20–40 min
🟡 CATEGORÍA 3 — Mejoras de Vistas Existentes
Trabajo de refactorización y UX moderado.

#	Tarea	Archivo principal	Estimación
7	Arreglar estilo de parcelas/detalle	app/Views/parcelas/detalle.php, CSS relevante	30–60 min
8	Vista propietarios: eliminar columna acciones + crear vista de detalle individual	app/Views/propietarios/index.php, nuevo app/Views/propietarios/detalle.php, app/Controllers/PropietariosController.php	60–90 min
Nota: La vista de detalle de propietarios requiere una nueva ruta en routes/web.php y posiblemente un nuevo método en el controlador. Es moderadamente complejo porque hay que diseñar qué información mostrar (parcelas asignadas, deudas, historial).

🟢 CATEGORÍA 4 — Nuevas Funcionalidades Simples/Medias
No dependen de bugs anteriores para funcionar (salvo la tarea 9).

#	Tarea	Archivo principal	Estimación
9	Crear un trabajo desde la vista de crear tareas	app/Views/tareas/crear.php, public/js/trabajos.js (modal reutilizable)	45–75 min
10	Sección con enlaces de interés	Nueva vista app/Views/enlaces/index.php, nueva ruta en routes/web.php	30–45 min
Nota tarea 9: Depende de que el modal de Gestión de Trabajos (tarea 6) esté arreglado, ya que se reutilizaría la misma lógica de creación.

🔵 CATEGORÍA 5 — Funcionalidades Complejas / Alta Inversión
Requieren más diseño, APIs externas o conectar múltiples partes del sistema.

#	Tarea	Archivo principal	Estimación
11	Widget de previsión meteorológica en el dashboard	app/Views/dashboard/index.php, nueva integración API (OpenWeatherMap u otra)	90–150 min
12	Vista de reportes funcional (conectar datos reales)	app/Views/reportes/index.php, app/Controllers/ReportesController.php, múltiples modelos	3–6 horas
Nota tarea 12 (Reportes): Es la tarea más compleja del backlog. La vista ya tiene estructura visual con placeholders (KPIs, gráficos, rankings), pero necesita conectarse a datos reales de: tareas, trabajadores, parcelas, economía, fitosanitarios. Hay rutas definidas (/reportes/personal, /reportes/parcelas, etc.) que no existen aún. Además, influye indirectamente en todas las demás áreas porque muestra el estado global de la aplicación.

Orden de Ejecución Recomendado

SPRINT 1 — Desbloquear el sistema (bugs críticos)
  → Tarea 1: Botón añadir movimiento dashboard       [15–30 min]
  → Tarea 3+4: Botones campaña (nueva + eliminar)    [20–40 min]
  → Tarea 2: Fitosanitarios (producto + aplicaciones) [30–60 min]

SPRINT 2 — Fiabilidad y datos correctos
  → Tarea 5: Redondeo deuda 59,99€ → 60€            [10–20 min]
  → Tarea 6: Modal Gestión de Trabajos               [20–40 min]

SPRINT 3 — Mejoras de vistas
  → Tarea 7: Estilo parcelas/detalle                 [30–60 min]
  → Tarea 8: Vista propietarios + detalle            [60–90 min]

SPRINT 4 — Nuevas funcionalidades
  → Tarea 9: Crear trabajo desde tareas              [45–75 min]
  → Tarea 10: Sección enlaces de interés             [30–45 min]

SPRINT 5 — Funcionalidades complejas
  → Tarea 11: Widget meteorología                    [90–150 min]
  → Tarea 12: Reportes funcional                     [3–6 horas]
Tiempo total estimado: ~10–16 horas de trabajo

Mapa de Dependencias

Tarea 2 (Fitosanitarios)
  └── afecta → lógica Tarea::create() (auto-descuento inventario)
  └── afecta → Reportes (datos de aplicaciones)

Tarea 6 (Modal Trabajos)
  └── es prerequisito → Tarea 9 (Crear trabajo desde tareas)

Tareas 1–4 (Bugs críticos)
  └── son prerequisitos → Tarea 12 (Reportes necesitan datos correctos)

Tarea 8 (Vista propietarios con detalle)
  └── independiente, pero puede influir en el diseño de Reportes
Archivos Críticos por Tarea
Tarea	Archivos a modificar
1 - Movimiento dashboard	app/Views/dashboard/index.php, app/Views/economia/index.php
2 - Fitosanitarios	app/Views/fitosanitarios/index.php, app/Controllers/FitosanitariosController.php
3+4 - Campañas	app/Views/campana/index.php, app/Controllers/CampanaController.php
5 - Redondeo deuda	app/Models/PagoMensual.php, app/Views/economia/deudas.php
6 - Modal trabajos	app/Views/trabajos/index.php, public/js/trabajos.js
7 - Estilo parcelas	app/Views/parcelas/detalle.php
8 - Vista propietarios	app/Views/propietarios/index.php, nueva detalle.php, app/Controllers/PropietariosController.php, routes/web.php
9 - Trabajo desde tarea	app/Views/tareas/crear.php, public/js/trabajos.js
10 - Enlaces interés	Nueva vista + ruta
11 - Widget meteorología	app/Views/dashboard/index.php, nueva integración API
12 - Reportes	app/Views/reportes/index.php, app/Controllers/ReportesController.php
User approved the plan