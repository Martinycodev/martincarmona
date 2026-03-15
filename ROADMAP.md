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

- [ ] **Riego:** arreglar registro nuevo riego. Selector de año activo que filtre resultados. Panel resumen de agua total usada y m³ consumidos
- [ ] **Fitosanitarios:** arreglar flujo de uso/aplicación. Revisar que el inventario se descuente correctamente al aplicar producto

---

## FASE 8 — UX Móvil 📱

> El 80% del uso real es en campo con el móvil. Esta fase tiene el mayor impacto en productividad.

### Calendario móvil
- [ ] Al pulsar un día → modal con las tareas de ese día + botón "Nueva tarea" (solo en `@media max-width`)
- [ ] Reducir información visible por celda en móvil (solo indicador de color/punto, sin texto)
- [ ] Swipe horizontal para cambiar de mes (touch events)

### Dashboard móvil
- [ ] Rediseñar botones rápidos: grid 2x2 con iconos grandes, touch-friendly (min 48x48px)
- [ ] Cards del dashboard apiladas verticalmente, sin scroll horizontal
- [ ] Sidebar de tarea: ocupar pantalla completa en móvil (100vh) en lugar de panel lateral y ¿poder salir de el pulsando el botón de atrás en el movil?

### Tablas responsive
- [ ] Tablas de listados: en móvil convertir a cards apilables o usar scroll horizontal con indicador visual
- [ ] Aumentar tamaño de targets táctiles en filas de tabla (min 44px de alto)

### Formularios y modales
- [ ] Modales en móvil: ocupar pantalla completa (`position:fixed; inset:0`) en vez de centrados con margin
- [ ] Inputs: `font-size:16px` mínimo para evitar zoom automático en iOS
- [ ] Selects y combobox: ampliar área de toque

---

## FASE 9 — Feedback visual y microinteracciones ✨

> El usuario necesita saber que su acción funcionó. Actualmente hay acciones silenciosas.

### Sistema de notificaciones (toast)
- [ ] Crear componente toast reutilizable (éxito verde, error rojo, info azul) — posición bottom-right en desktop, top en móvil
- [ ] Aplicar toast en: crear/editar/eliminar tarea, movimiento, pago, registro de riego, aplicación fitosanitario
- [ ] Toast de confirmación antes de eliminar (en lugar de `confirm()` nativo del navegador)

### Estados de carga (Siempre y cuando no ralenticen el uso)
- [ ] Skeleton loaders en cards y tablas mientras se cargan datos AJAX
- [ ] Botones con estado loading (spinner + disabled) al enviar formularios — evitar doble submit
- [ ] Indicador de "guardando..." en el sidebar de tarea al hacer cambios

### Transiciones
- [ ] Animación suave al abrir/cerrar sidebar (ya existe parcialmente, unificar)
- [ ] Fade-in al cargar contenido via AJAX (en lugar de salto brusco)
- [ ] Transición suave entre meses del calendario

---

## FASE 10 — Accesibilidad (a11y) ♿

> Rápido de implementar y mejora la UX para todos, no solo usuarios con discapacidad.

### Navegación por teclado
- [ ] Skip-nav link oculto: "Saltar al contenido" al inicio del body (visible con focus)
- [ ] Focus visible en todos los elementos interactivos (outline personalizado que encaje con el tema oscuro)
- [ ] Trampas de foco en modales: Tab/Shift+Tab solo cicla dentro del modal abierto
- [ ] Cerrar modales con Escape

### Semántica y ARIA
- [ ] `role="alert"` en mensajes de error y toasts
- [ ] `aria-label` en botones de solo icono (hamburguesa, +, ◀, ▶, cerrar modal)
- [ ] `aria-expanded` en el menú hamburguesa y acordeones
- [ ] `aria-live="polite"` en el calendario al cambiar de mes (lector de pantalla anuncia el cambio)
- [ ] Usar `<main>`, `<nav>`, `<aside>`, `<section>` en lugar de `<div>` genéricos donde corresponda

### Contraste y legibilidad
- [ ] Revisar contraste de textos secundarios (`#666`, `#888`) — WCAG AA exige ratio 4.5:1 sobre `#1e1e1e`
- [ ] Los textos `#888` sobre fondo `#2a2a2a` dan ratio ~3.5:1 → subir a `#999` o `#aaa`
- [ ] Nunca transmitir información solo con color — añadir icono o texto (ej: deuda roja → "Pendiente" + rojo)

---

## FASE 11 — PWA y uso offline 📶

> En el campo la conexión es inestable. Poder registrar tareas offline y sincronizar después es un gran valor.

- [ ] `manifest.json`: nombre, iconos, theme_color, start_url, display: standalone
- [ ] Service Worker básico: cachear shell de la app (CSS, JS, header, fuentes)
- [ ] Pantalla de "Sin conexión" amigable cuando falla una petición
- [ ] Fase 2 (avanzado): almacenar formularios pendientes en IndexedDB y sincronizar al recuperar conexión
- [ ] Instalar como app en móvil (prompt de instalación A2HS)

---

## Pendiente — Backlog suelto

### Funcionalidades
- [ ] Tareas pendientes en dashboard: panel dragable al calendario. Casilla "sin fecha" debajo del calendario para arrastrar tareas
- [ ] Recordatorios/notificaciones push en perfil: cerrar cuentas del mes, ITV vehículos, otros
- [ ] Video de fondo en hero de la homepage (autoplay, baja opacidad, fallback si mala conexión)

### Infraestructura
- [ ] Backups automáticos de la base de datos
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
