# ROADMAP — Sistema de Gestión Agrícola

> Documento de planificación y estado del proyecto.
> **Última actualización:** 15 de marzo de 2026

---

## Estado General

Aplicación **operativa en producción** con arquitectura MVC, 26 controladores, 9 modelos, sistema multi-rol y todos los módulos core completados. Las fases 1-6 están cerradas. Queda trabajo de pulido, UX móvil y automatizaciones.

---

## Tabla de módulos

| Módulo | Estado | Notas |
|---|---|---|
| Tareas | ✅ | CRUD + calendario + sidebar + pendientes + filtros |
| Trabajadores | ✅ | Documentos, historial, deuda, foto |
| Trabajos | ✅ | Precio/hora, creación inline desde sidebar |
| Parcelas | ✅ | Catastro, plantación, documentos, productividad |
| Propietarios | ✅ | DNI, contacto, vista detalle, parcelas vinculadas |
| Vehículos | ✅ | Ficha técnica + póliza adjuntas |
| Herramientas | ✅ | PDF instrucciones |
| Empresas | ✅ | Gestoras de parcelas |
| Proveedores | ✅ | CRUD completo |
| Riego | ✅ | CRUD por parcela y año |
| Economía | ✅ | Dashboard, gastos, ingresos, deudas, saldo total |
| Campañas | ✅ | Registro kilos, rendimiento, cierre con precio |
| Fitosanitarios | ✅ | Inventario + aplicaciones + hook automático |
| Reportes | ✅ | KPIs reales, productividad, alertas dinámicas |
| Enlaces | ✅ | SIGPAC, IFAPA, meteo, PAC, laboral, tecnología |
| Multi-rol | ✅ | empresa, admin, propietario, trabajador |
| Admin | ✅ | Gestión de usuarios y roles |

---

## Fases completadas

### FASE 1 — Módulo Economía ✅
Dashboard financiero (saldo banco/efectivo/total, deuda trabajadores), CRUD gastos e ingresos con categoría y cuenta, deuda acumulada por trabajador, cierre de mes, registro de pagos. Integración con tareas para coste real.

### FASE 2 — Ampliaciones módulos existentes ✅
Trabajadores (DNI, docs, baja SS). Parcelas (catastro, plantación, riego/secano, documentos, FK propietario). Propietarios como entidad propia. Riego (controller + vistas). Vehículos y herramientas (documentos adjuntos). Tareas pendientes (fecha nullable, estado).

### FASE 3 — Módulos nuevos ✅
Campañas de aceituna (nov→feb, kilos, rendimiento, precio, beneficio). Fitosanitarios (inventario + aplicaciones + hook automático con tareas).

### FASE 4 — Multi-rol ✅
Roles empresa/admin/propietario/trabajador. Middleware de autorización. Panel admin. Vistas específicas por rol.

### FASE 5 — Calidad técnica ✅
Validator, PSR-4 autoloader, router dinámico, error handler, logging con Monolog, PHPUnit (Validator y Router al 100%).

### FASE 6 — Funcionalidades sueltas ✅
UX tablas (click → detalle/modal), vista detalle propietarios, widget meteo (Open-Meteo, Jaén), enlaces de interés, reportes con datos reales, creación inline de trabajos en sidebar, selección directa de trabajadores/parcelas sin botón "+", saldo total en economía, deuda visible en dashboard. SEO homepage: meta description/keywords (Martín Carmona, gestión olivar, Jaén, Arjonilla), canonical, Open Graph, Twitter Cards, JSON-LD, robots.txt, sitemap.xml, stats bar actualizada.

---

## FASE 7 — Arreglar lo roto 🔧

> Prioridad máxima. No tiene sentido pulir UX si hay features que no funcionan.

- [x] **Riego:** arreglar registro nuevo riego. Selector de año activo que filtre resultados. Panel resumen de agua total usada y m³ consumidos
- [x] **Fitosanitarios:** arreglar flujo de uso/aplicación. Revisar que el inventario se descuente correctamente al aplicar producto

### Limpieza de controladores duplicados
- [x] **Eliminar `DatosTrabajadoresController`** — duplica `TrabajadoresController`. Migrar rutas `/datos/trabajadores` y `/datos/trabajadores/actualizar` a `TrabajadoresController`. Eliminar controller y vistas asociadas en `app/Views/datos/trabajadores/`
- [x] **Eliminar `DatosParcelasController`** — duplica `ParcelasController`. Migrar rutas `/datos/parcelas/*` a `ParcelasController`. Eliminar controller y vistas en `app/Views/datos/parcelas/`
- [x] **Revisar `DatosController`** — movido como método `datos()` en `DashboardController`. Eliminado `DatosController`
- [x] **Ruta duplicada:** `/datos/trabajadores` — eliminada la ruta duplicada que apuntaba a `DatosTrabajadoresController`

### Naming confuso (singular vs plural)
- [x] **`PropietarioController`** → renombrado a `PropietarioDashboardController` (dashboard del rol propietario)
- [x] **`TrabajadorController`** → renombrado a `TrabajadorDashboardController` (dashboard del rol trabajador)
- [x] Actualizar rutas en `routes/web.php` tras los renombrados

### Modelos que faltan (SQL directo en controllers)
- [x] Crear `app/Models/Campana.php` — extraer queries de `CampanaController`
- [x] Crear `app/Models/Fitosanitario.php` — extraer queries de `FitosanitariosController` (incluye descuento automático de stock)
- [x] Crear `app/Models/Riego.php` — extraer queries de `RiegoController` (incluye filtro por año y resumen)
- [x] Crear `app/Models/Propietario.php` — extraer queries de `PropietariosController`
- [x] Crear `app/Models/Usuario.php` — extraer queries de `AuthController` y `AdminController`

### Limpieza de .gitignore (duplicados)
- [x] Eliminar archivos obsoletos: `Proyecto funcionalidades.md`, `miRoadmap.md`
- [x] Añadir `*.code-workspace` y `.phpunit.result.cache` al `.gitignore`
- [x] Eliminar entradas duplicadas en `.gitignore` (`*.log` y `*.tmp` aparecen dos veces)

---

## FASE 8 — UX Móvil ✅

> El 80% del uso real es en campo con el móvil. Esta fase tiene el mayor impacto en productividad.

### Calendario móvil
- [x] Al pulsar un día → modal bottom-sheet con las tareas de ese día + botón "Nueva tarea" (solo en `@media max-width: 768px`)
- [x] Reducir información visible por celda en móvil (celdas compactas, nav touch-friendly)
- [x] Swipe horizontal para cambiar de mes (touch events + animación CSS)

### Dashboard móvil
- [x] Rediseñar botones rápidos: grid 2x2 con iconos grandes, touch-friendly (min 56px)
- [x] Cards del dashboard apiladas verticalmente, sin scroll horizontal
- [x] Sidebar de tarea: ocupar pantalla completa en móvil (width: 100%)

### Tablas responsive
- [x] Tablas de listados: scroll horizontal con indicador visual (gradiente sombra derecha)
- [x] Aumentar tamaño de targets táctiles en filas de tabla (min 44px de alto, padding ampliado)

### Formularios y modales
- [x] Modales en móvil: ocupar pantalla completa (`height: 100vh`) con header/footer sticky
- [x] Inputs: `font-size:16px` mínimo para evitar zoom automático en iOS
- [x] Selects y combobox: área de toque ampliada, custom appearance con flecha SVG

---

## FASE 9 — Feedback visual y microinteracciones ✅

> El usuario necesita saber que su acción funcionó. Actualmente hay acciones silenciosas.

### Sistema de notificaciones (toast)
- [x] Componente toast reutilizable global en `modal-functions.js` (éxito verde, error rojo, info azul) — posición bottom-right en desktop, top en móvil
- [x] Toast aplicado en: riego, fitosanitarios, campañas, economía, propietarios, parcelas, vehículos, herramientas, admin, tareas, reportes — reemplazados 53 `alert()` por `showToast()`
- [x] `showConfirm()` como reemplazo de `confirm()` nativo — toast con botones Cancelar/Eliminar, devuelve Promise
- [x] Eliminadas 6 definiciones duplicadas de `showToast()` en vistas (ya es global)

### Estados de carga
- [x] CSS skeleton loaders (`.skeleton`, `.skeleton-text`, `.skeleton-card`) con shimmer animation
- [x] `setButtonLoading(btn, loading)` global — spinner CSS + disabled, evita doble submit
- [x] Indicador de "guardando..." en sidebar de tarea (ya existía: `#sidebar-save-status`)

### Transiciones
- [x] Sidebar: `transition: transform 0.3s ease` unificado en CSS
- [x] Fade-in al cargar contenido AJAX (ya implementado en `ajax-navigation.js` → `updateContentWithAnimation()`)
- [x] Transición suave entre meses del calendario (clase `.ajax-fade-in` + swipe CSS animations)

---

## FASE 10 — Accesibilidad (a11y) ✅

> Rápido de implementar y mejora la UX para todos, no solo usuarios con discapacidad.

### Navegación por teclado
- [x] Skip-nav link oculto: "Saltar al contenido" al inicio del body (visible con focus, verde #4caf50)
- [x] Focus visible con `:focus-visible` personalizado (outline verde + box-shadow en inputs)
- [x] Cerrar modales, sidebar, lightbox y toast de confirmación con Escape (handler global en `modal-functions.js`)

### Semántica y ARIA
- [x] `aria-label` en botones de solo icono: hamburguesa, +, ◀, ▶, cerrar modal/sidebar/lightbox, hoy
- [x] `aria-expanded` + `aria-controls` en hamburguesa, actualizado dinámicamente en `toggleMenu()`
- [x] `aria-live="polite"` en `#monthYear` del calendario (lector de pantalla anuncia cambio de mes)
- [x] `<main id="main-content">` envuelve el contenido entre header y footer, `<nav role="navigation">` en menú
- [x] Sidebar ya tiene `role="dialog"` + `aria-modal="true"` + `aria-label`

### Contraste y legibilidad
- [x] Textos secundarios revisados: CSS disponible para subir de `#888` a `#aaa` donde sea necesario (ratio 5.2:1)
- [x] Información nunca solo por color: estados usan icono + texto (● Activa, ✓ Cerrada, Pendiente)

---

## FASE 11 — PWA y uso offline ✅

> En el campo la conexión es inestable. Poder registrar tareas offline y sincronizar después es un gran valor.

- [x] `manifest.json`: nombre, iconos SVG, theme_color `#4caf50`, start_url, display: standalone
- [x] Service Worker (`public/sw.js`): cache-first para assets estáticos (CSS, JS, SVG), network-first para HTML
- [x] Pantalla de "Sin conexión" amigable (`public/offline.html`) con botón reintentar
- [x] Meta tags PWA: `theme-color`, `apple-mobile-web-app-capable`, manifest link
- [x] Registro del SW en el footer con scope correcto
- [ ] Fase 2 (avanzado): almacenar formularios pendientes en IndexedDB y sincronizar al recuperar conexión

---

## Pendiente — Backlog suelto

### Funcionalidades
- [ ] Tareas pendientes en dashboard: panel dragable al calendario. Casilla "sin fecha" debajo del calendario para arrastrar tareas
- [ ] Recordatorios/notificaciones push en perfil: cerrar cuentas del mes, ITV vehículos, otros
- [ ] Video de fondo en hero de la homepage (autoplay, baja opacidad, fallback si mala conexión)

### Infraestructura
- [ ] Backups automáticos de la base de datos
- [ ] Crear un dump(esqueleto) de la BBDD en sql y añadir al .gitignore la actual (u873002419_campo.sql) con toda la info.
- [ ] Seed de la base de datos con datos exportados de Notion

### Exportación y reportes
- [ ] Exportar CSV/Excel: tareas, gastos, cuenta mensual por trabajador
- [ ] PDF de balance mensual por trabajador
- [ ] Gráficos de productividad por parcela (Chart.js — ya en el stack)

### DevOps
- [ ] `docker-compose.yml` para desarrollo reproducible
- [ ] GitHub Actions para tests automáticos en cada push

---

## Criterios de calidad

- [ ] Todos los formularios POST validan y sanitizan inputs
- [ ] Tiempo de respuesta < 2 segundos en operaciones normales
- [ ] La aplicación es usable en móvil (uso en campo)
- [ ] Un cambio de código no rompe funcionalidad existente (tests)
- [ ] Cada rol solo ve lo que debe ver (autorización verificada en backend)
- [ ] Contraste WCAG AA en todos los textos (ratio ≥ 4.5:1)
- [ ] Todos los elementos interactivos son accesibles por teclado
- [ ] Targets táctiles ≥ 44x44px en móvil
