# CLAUDE.md — Guia de desarrollo para el asistente IA

> Este archivo lo lee Claude antes de hacer cualquier cambio en el codigo.
> Ultima actualizacion: 21 de marzo de 2026

---

## 1. Resumen del proyecto

Aplicacion web MVC en PHP puro para la gestion integral de una explotacion agricola de olivar en Jaen. Sustituye el registro en papel: tareas de campo, trabajadores, parcelas, economia, campanas de aceituna, fitosanitarios, riego y mas.

- **Entorno local:** XAMPP (Windows) — `http://localhost/martincarmona`
- **Produccion:** hosting compartido (Hostinger) — `https://martincarmona.com`
- **Base de datos remota:** MySQL en produccion (`u873002419_campo`)
- **Repositorio:** `https://github.com/Martinycodev/martincarmona`

---

## 2. Arquitectura

```
martincarmona/
├── app/
│   ├── Controllers/   # 26 controladores (BaseController como padre)
│   ├── Models/        # 15 modelos (acceso directo a mysqli)
│   └── Views/         # 23 carpetas de vistas + layouts/
├── config/
│   ├── config.php     # APP_BASE_PATH, debug, nombre app
│   ├── database.php   # Singleton mysqli, credenciales desde .env
│   └── session.php    # SessionConfig: timeout 2h, regen 30min
├── core/
│   ├── Autoloader.php # PSR-4
│   ├── CsrfMiddleware.php
│   ├── ErrorHandler.php
│   ├── Logger.php     # Monolog: app.log + security.log
│   ├── Router.php     # Rutas estaticas + dinamicas {id}
│   └── Validator.php  # required, date, numeric, min, max, email, max_length, etc.
├── database/
│   ├── Dump.sql       # Dump principal de la BD
│   ├── backup.php     # Script de backup automatico
│   ├── seed_demo.sql  # Datos de demo
│   └── seed_notion.php # Importador de datos historicos desde Notion
├── migrations/        # ~25 migraciones SQL (001_ a 016_ + migration_*)
├── public/
│   ├── css/           # styles.css, autocomplete.css, search.css
│   ├── js/            # 8 archivos JS vanilla
│   ├── img/           # Favicon SVG, og-cover.jpg, icons/ (PWA)
│   ├── uploads/       # Imagenes y documentos subidos por usuarios
│   ├── video/         # Videos pesados (NO en git, subir manualmente)
│   ├── offline.html   # Pantalla sin conexion (PWA)
│   └── docs/          # Documentos estaticos
├── routes/web.php     # ~143 rutas
├── index.php          # Entry point
├── .env               # Variables de entorno (no en git)
├── composer.json      # phpdotenv, monolog, phpunit
├── robots.txt         # SEO
├── sitemap.xml        # SEO
├── CLAUDE.md          # Este archivo
├── README.md          # Documentacion general del proyecto
└── ROADMAP.md         # Planificacion y estado de fases
```

### Flujo de una peticion

1. `index.php` → carga autoloader, .env, config, sesion, DB
2. `Router::run()` → match exacto o dinamico `{id}` → `Controller@method`
3. `BaseController::render($view, $data)` → inyecta CSRF, detecta AJAX
4. Si es AJAX → devuelve solo el contenido (sin layout header/footer)
5. Si es normal → envuelve en `layouts/header.php` + `layouts/footer.php`

### Navegacion AJAX (SPA-like)

`ajax-navigation.js` intercepta clicks en links de navegacion y solo extrae el `.container` del HTML devuelto. Consecuencia importante: **los `<script>` fuera de `.container` no se ejecutan en navegacion AJAX**. Para que el JS funcione tras navegacion AJAX usa este patron:

```js
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
```

El sistema incluye:
- Fade-in animado al cargar contenido (`updateContentWithAnimation()`)
- Proteccion contra multiples recargas encadenadas (debounce)

### CSRF

- Backend: `BaseController::validateCsrf()` lee de `$_POST['csrf_token']` o header `X-CSRF-TOKEN`
- Frontend: `modal-functions.js` auto-inyecta `X-CSRF-TOKEN` en todos los `fetch()` POST/PUT/DELETE
- Meta tag en header: `<meta name="csrf-token" content="...">`
- Lectura defensiva en JS: `document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? ''`

### PWA (Progressive Web App)

- `PwaController` sirve `manifest.json` y `sw.js` dinamicamente (con APP_BASE_PATH)
- Service Worker: cache-first para assets estaticos (CSS, JS, img), network-first para HTML
- Pantalla offline: `public/offline.html` con boton reintentar
- Banner de instalacion: se muestra automaticamente en Chrome/Edge si no esta instalada
- Meta tags iOS: `apple-mobile-web-app-capable`, `apple-touch-icon`

### Cola offline (IndexedDB)

- `public/js/offline-queue.js` → objeto global `OfflineQueue`
- Guarda formularios pendientes en IndexedDB cuando no hay conexion
- Se sincronizan automaticamente al recuperar la conexion
- Badge en header muestra numero de peticiones pendientes

---

## 3. Stack tecnico

| Capa | Tecnologia |
|------|------------|
| Backend | PHP 8.2, MVC propio, mysqli |
| Base de datos | MySQL / MariaDB |
| Frontend | HTML5, CSS3 puro (tema oscuro), JS vanilla |
| Graficos | Chart.js |
| Calendario | FullCalendar custom (vanilla JS) |
| Logs | Monolog (app.log + security.log) |
| Tests | PHPUnit 11.5 |
| PWA | Service Worker + manifest.json + IndexedDB |
| Dependencias | Composer (phpdotenv, monolog) |

**No se usan frameworks CSS ni JS.** Todo es vanilla. No anadir jQuery, Bootstrap, Tailwind ni similar salvo que se pida explicitamente.

---

## 4. Base de datos — Tablas principales

| Tabla | Descripcion |
|-------|-------------|
| `usuarios` | Login, rol (empresa/admin/propietario/trabajador), ultimo_login |
| `tareas` | Tareas de campo (fecha nullable = pendiente, estado, id_user) |
| `tarea_trabajadores` | N:M tarea↔trabajador con horas_asignadas |
| `tarea_parcelas` | N:M tarea↔parcela |
| `tarea_trabajos` | N:M tarea↔trabajo con horas_trabajo y precio_hora |
| `trabajadores` | Empleados (nombre, apellidos, DNI, telefono, foto, docs, baja_ss, activo) |
| `trabajos` | Tipos de trabajo con precio/hora, categoria (campo, recoleccion, mantenimiento, tratamiento, riego) |
| `parcelas` | Fincas (catastro, municipio, poligono, parcela, olivos, tipo plantacion, riego_secano, hidrante, propietario_id FK, imagen) |
| `propietarios` | Duenos de parcelas (DNI anverso/reverso, contacto) |
| `movimientos` | Gastos e ingresos (tipo, categoria, cuenta banco/efectivo, id_user, proveedor_id, trabajador_id, vehiculo_id, parcela_id) |
| `pagos_mensuales_trabajadores` | Deuda mensual por trabajador (importe_total, pagado, fecha_pago) |
| `fitosanitarios` | Inventario de productos fitosanitarios (stock se descuenta auto al aplicar) |
| `fitosanitarios_aplicaciones` | Registro de aplicaciones por parcela |
| `campanas` | Campanas de aceituna (nov→feb, estado activa/cerrada) |
| `campana_registros` | Kilos, rendimiento, precio por parcela, campaña y calidad (Vuelo nov/dic/ene/feb/mar, Suelo) |
| `riegos` | Registros de riego por parcela, ano y temporada |
| `vehiculos` | Inventario (matricula, ficha tecnica, poliza, seguro, aseguradora, ultima ITV) |
| `herramientas` | Inventario con PDF instrucciones |
| `proveedores` | CRUD basico (nombre, telefono, email, notas) |
| `recordatorios` | Notificaciones (tipo: itv/cuentas/jornadas/personalizado, fecha_aviso, repeticion, activo, leido) |
| `recordatorios_config` | Config por usuario (activar/desactivar tipos de recordatorio) |

### Relaciones clave
- `tareas.id_user` → filtra tareas por usuario (multi-tenancy)
- `movimientos.id_user` → filtra economia por usuario
- `parcelas.propietario_id` → FK a propietarios
- `trabajos.categoria` → agrupa trabajos por tipo, define color en calendario
- `tarea_trabajos` → formularios reactivos: si trabajo = "Recoger aceituna" crea registro en campana; si = "Abrir riego" crea registro en riego; si = herbicida/sulfato crea aplicacion fitosanitaria

---

## 5. Controladores — Lista completa

| Controlador | Responsabilidad |
|-------------|-----------------|
| `BaseController` | Padre: render(), url(), validateCsrf(), requireEmpresa(), json() |
| `HomeController` | Landing page publica (SEO, formulario contacto) |
| `AuthController` | Login/logout, registro ultimo_login |
| `DashboardController` | Dashboard principal + vista `/datos` (bases de datos) |
| `TareasController` | CRUD tareas, calendario, pendientes, sidebar, drag&drop, imagenes |
| `TrabajadoresController` | CRUD trabajadores, fotos, documentos, detalle, cuadrilla, estado activo/inactivo |
| `TrabajosController` | CRUD trabajos, categorias, documentos de metodo de trabajo |
| `ParcelasController` | CRUD parcelas, detalle, documentos, imagenes, propietarios vinculados |
| `PropietariosController` | CRUD propietarios, detalle, imagenes DNI |
| `EconomiaController` | Dashboard financiero, gastos, ingresos, deudas, cierre mes, pagos |
| `CampanaController` | Campanas aceituna, registros diarios, cierre con precio |
| `FitosanitariosController` | Inventario + aplicaciones, descuento auto de stock |
| `RiegoController` | Registros por parcela/ano, temporadas, filtro por ano |
| `VehiculosController` | CRUD vehiculos, detalle, docs (ficha, poliza), seguro, ITV |
| `HerramientasController` | Inventario + PDF instrucciones |
| `ProveedoresController` | CRUD proveedores |
| `ReportesController` | KPIs reales, 6 sub-paginas (personal, parcelas, trabajos, economia, recursos, proveedores), selector periodo, trends |
| `BusquedaController` | Busqueda avanzada de tareas (multi-filtro: texto, fecha, trabajador, parcela, trabajo, propietario, horas) |
| `EnlacesController` | Links de interes agricola (9 categorias) |
| `PerfilController` | Vista perfil, cambiar nombre, cambiar contrasena |
| `NotificacionesController` | API JSON recordatorios (CRUD, config, auto-generacion ITV/cuentas/jornadas) |
| `ContactoController` | Formulario contacto landing (honeypot, timestamp, rate-limit, email) |
| `PwaController` | Sirve manifest.json y sw.js dinamicamente |
| `AdminController` | Gestion usuarios y roles |
| `PropietarioDashboardController` | Dashboard del rol propietario (sus parcelas) |
| `TrabajadorDashboardController` | Dashboard del rol trabajador (su deuda, calendario, tareas) |

---

## 6. Modelos — Lista completa

| Modelo | Metodos principales |
|--------|---------------------|
| `Tarea` | create, update, delete, obtenerPorMes, pendientes, agregarTrabajador/Parcela, cambiarTrabajo |
| `Trabajador` | getAll, find, create, update, eliminar (soft), reactivar, activosPorMes |
| `Trabajo` | getAll, find, create, update, delete |
| `Parcela` | getAll, find, create, update, delete, detalle |
| `Propietario` | getAll, find, create, update, delete |
| `Movimiento` | getAllGastos, getAllIngresos, getSaldos, create, update, delete (LABELS_CATEGORIA, CATEGORIAS_GASTO, CATEGORIAS_INGRESO) |
| `PagoMensual` | getDeudaPorTrabajador, cerrarMes, registrarPago |
| `Campana` | getAll, find, create, update, delete, crearRegistro, cerrar |
| `Fitosanitario` | getInventario, crearProducto, crearAplicacion (descuento auto stock) |
| `Riego` | getRegistros, crear, actualizar, eliminar, resumen, filtrarPorAno, temporadas |
| `Vehiculo` | getAll, find, create, update, delete |
| `Herramienta` | getAll, find, subirInstrucciones |
| `Proveedor` | getAll, find, create, update, delete |
| `Usuario` | find, create, update, delete, login, registrarUltimoLogin |
| `Recordatorio` | getActivos, crear, marcarLeido, eliminar, getConfig, toggleConfig, generarITV, generarCuentas, generarJornadas |

---

## 7. Roles y acceso

| Rol | Acceso |
|-----|--------|
| `empresa` | Todo — es el usuario principal (agricultor). Multi-tenancy por id_user |
| `admin` | Gestion de usuarios + lo mismo que empresa |
| `propietario` | Solo sus parcelas y tareas en ellas (sin horas/precios) |
| `trabajador` | Su deuda, calendario personal, tareas pendientes asignadas |

Middleware: `requireEmpresa()`, `requireEmpresaOAdmin()`, `redirectByRole($rol)`.

---

## 8. Funcionalidades del Dashboard

El dashboard (`/dashboard`) es la vista principal tras login e incluye:

1. **Quick buttons**: Tareas (busqueda), Anadir movimiento, Campana activa (condicional), Riego
2. **Calendario interactivo**: navegacion por meses, swipe en movil, drag&drop de tareas, colores por categoria de trabajo
3. **Tareas en calendario**: titulo visible (no nombre de trabajo), borde color segun categoria
4. **Bottom-sheet movil**: al pulsar un dia → modal con tareas del dia + flechas dia anterior/siguiente + boton nueva tarea
5. **Dropzone "sin fecha"**: arrastrar tarea aqui para convertirla en pendiente
6. **Panel tareas pendientes**: lista con formulario inline para crear, enlace "Ver todas"
7. **Sidebar de tarea** (panel lateral): editar titulo, fecha, descripcion, horas, trabajadores (combobox), parcelas (combobox), trabajo (combobox con tag), imagenes. Guardado inline con indicador de estado. Formularios reactivos segun tipo de trabajo
8. **Widget meteorologia**: Open-Meteo API, Jaen (37.78, -3.79), forecast 7 dias, codigos WMO → emojis
9. **Notificaciones (campanita)**: dropdown con recordatorios pendientes (ITV, cuentas, jornadas, personalizados)

---

## 9. Archivos JavaScript — Responsabilidades

| Archivo | Funcion |
|---------|---------|
| `ajax-navigation.js` | Navegacion SPA: intercepta links, extrae `.container`, fade-in, proteccion anti-bucle |
| `modal-functions.js` | Apertura/cierre modales, auto-inyeccion CSRF en fetch, `showToast()` global, `showConfirm()`, `setButtonLoading()`, cierre con Escape |
| `task-sidebar.js` | Clase `TaskSidebar`: sidebar lateral para editar tareas, combobox, formularios reactivos, guardado inline |
| `offline-queue.js` | `OfflineQueue`: IndexedDB para peticiones pendientes, sincronizacion auto al reconectar |
| `guided-tour.js` | Clase `GuidedTour`: visita guiada con overlay + tooltip, pasos configurables, compatible AJAX |
| `search.js` | Logica de busqueda avanzada de tareas (filtros, resultados, estadisticas) |
| `trabajadores.js` | Logica especifica de la vista de trabajadores |
| `trabajos.js` | Logica especifica de la vista de trabajos |

---

## 10. Convenciones de codigo

### PHP
- Controladores heredan de `BaseController`
- Modelos usan `mysqli` directamente (no ORM), instancian `\Database::connect()`
- Sanitizacion: `htmlspecialchars()` en toda salida de datos de BD a vista
- Validacion: `core/Validator.php` en todos los POST
- Rutas en `routes/web.php`: formato `$router->get('/ruta', 'Controller@method')`
- Multi-tenancy: filtrar siempre por `id_user = $_SESSION['user_id']`

### JavaScript
- Vanilla JS — sin frameworks ni jQuery
- Variable global `window._APP_BASE_PATH` o `basePath` para URLs
- Patron AJAX-safe: comprobar `document.readyState` antes de `addEventListener`
- CSRF: leer meta tag, no hardcodear tokens
- Toast global: `showToast(mensaje, tipo)` (success/error/info) — definido en `modal-functions.js`
- Confirmacion: `showConfirm(mensaje)` → devuelve Promise (reemplaza `confirm()` nativo)
- Loading: `setButtonLoading(btn, loading)` → spinner CSS + disabled

### CSS
- Tema oscuro: fondos `#1a1a1a` / `#2a2a2a`, texto `#ccc` / `#fff`
- Acento verde: `#4caf50` (primario), `#a8d5ab` (secundario/claro)
- Acento rojo: `#f44336` (errores, deudas, negativos)
- Acento azul: `#2196f3` (banco, info)
- Border-radius: `10px` en cards, `8px` en inputs
- Sin framework CSS — todo en `public/css/styles.css` + estilos inline en vistas
- Responsive: mobile-first para campo (80% uso movil). Breakpoint principal: `768px`
- Targets tactiles: minimo `44px` en movil, botones `56px`
- Modales movil: pantalla completa (`height: 100vh`) con header/footer sticky
- Inputs: `font-size: 16px` minimo (evita zoom iOS)
- Skeleton loaders: `.skeleton`, `.skeleton-text`, `.skeleton-card` con shimmer animation
- Colores de categorias de trabajo: campo (marron), recoleccion (verde olivo), mantenimiento (purpura/violeta), tratamiento (rosa oscuro), riego (azul)

### Accesibilidad (a11y)
- Skip-nav link: "Saltar al contenido" (visible con focus)
- `aria-label` en botones de solo icono
- `aria-expanded` + `aria-controls` en hamburguesa
- `aria-live="polite"` en elementos que cambian (calendario, mes)
- `<main id="main-content">`, `<nav role="navigation">`
- Sidebar: `role="dialog"` + `aria-modal="true"`
- `:focus-visible` personalizado (outline verde + box-shadow)
- Cierre con Escape: modales, sidebar, lightbox, toast confirmacion
- Contraste WCAG AA: textos secundarios minimo `#aaa` (ratio 5.2:1)
- Estados no solo por color: icono + texto (● Activa, ✓ Cerrada)

### SEO (Landing page)
- Meta description, keywords, canonical
- Open Graph + Twitter Cards
- JSON-LD (SoftwareApplication)
- `robots.txt` + `sitemap.xml`

### Git
- Mensajes en espanol o ingles, formato convencional (`feat:`, `fix:`, `chore:`)
- Assets pesados (videos, dumps) NO van al repo — subir manualmente al host
- `public/video/` esta en `.gitignore`

---

## 11. Patrones frecuentes

### Crear un nuevo CRUD
1. Modelo en `app/Models/` con constructor `$this->db = \Database::connect()` y metodos de consulta
2. Controlador en `app/Controllers/` heredando `BaseController`
3. Vistas en `app/Views/{modulo}/` (index.php, detalle.php, etc.)
4. Rutas en `routes/web.php`
5. Link en `app/Views/layouts/header.php` si es menu principal

### Modales
- Se definen en la misma vista con `<div class="modal" id="...">`
- `modal-functions.js` maneja apertura/cierre y auto-inyecta CSRF en fetch
- El controlador devuelve JSON → el JS actualiza el DOM sin recargar
- En movil: ocupan pantalla completa automaticamente

### Combobox / Typeahead
- Se inicializan con `initCombobox(containerId, opciones, onSelectCallback)`
- Permiten crear nuevos items si el texto no existe en las opciones
- Seleccion por click muestra tag verde (o tag del color de la categoria en trabajos)
- Estilos en `public/css/autocomplete.css`

### Sidebar de tarea (Dashboard)
- `public/js/task-sidebar.js` — clase `TaskSidebar`
- Se abre al hacer click en una tarea del calendario o boton "+"
- Carga datos via AJAX, permite editar trabajadores, parcelas, trabajos inline
- Los trabajadores y parcelas se anaden al hacer click en la opcion del combobox (sin boton "+")
- El trabajo se selecciona como tag unico (sustituye al anterior)
- **Formularios reactivos**: segun el tipo de trabajo seleccionado se crean registros automaticos:
  - "Recoger aceituna" → registro en campana activa (fecha + parcela)
  - "Abrir riego" → registro en gestion de riego (fecha + parcela)
  - Herbicida/sulfato → aplicacion fitosanitaria (fecha + parcela)

### Notificaciones / Recordatorios
- Modelo `Recordatorio` genera automaticamente alertas de:
  - **ITV**: X dias antes del vencimiento de cada vehiculo
  - **Cuentas**: cerrar pagos del mes para cada trabajador con deuda
  - **Jornadas**: enviar jornadas reales a gestoria (2 dias antes de fin de mes hasta dia 5)
- Recordatorios personalizados con repeticion (mensual, anual, cada X dias)
- Configuracion por usuario en perfil (activar/desactivar cada tipo)
- Dropdown en header con campanita y badge de pendientes

### Toast y feedback visual
- `showToast(mensaje, 'success'|'error'|'info')` — posicion bottom-right desktop, top movil
- `showConfirm(mensaje)` — botones Cancelar/Eliminar, devuelve Promise
- `setButtonLoading(btn, true/false)` — spinner CSS + disabled, evita doble submit
- Skeleton loaders para estados de carga

---

## 12. Cosas a evitar

- **No anadir frameworks** (jQuery, Bootstrap, Tailwind, Alpine) salvo peticion explicita
- **No usar ORM** — los modelos usan mysqli directamente
- **No crear archivos innecesarios** — editar los existentes siempre que sea posible
- **No subir videos ni assets pesados a git** — van al host manualmente
- **No romper la navegacion AJAX** — los scripts deben funcionar tanto en carga normal como AJAX
- **No hardcodear URLs** — usar `$this->url()` en PHP y `window._APP_BASE_PATH` en JS
- **No anadir console.log en produccion**
- **No mockear la BD en tests** — usar datos reales o test DB
- **No olvidar filtrar por id_user** — multi-tenancy obligatorio en queries
- **No usar `alert()` ni `confirm()`** — usar `showToast()` y `showConfirm()` globales
- **No romper accesibilidad** — mantener `aria-*`, `role`, targets tactiles, contraste
- **No romper la PWA** — si se anaden assets al shell, actualizar `PwaController::sw()`

---

## 13. Archivos clave para ediciones frecuentes

| Archivo | Para que |
|---------|----------|
| `routes/web.php` | Anadir/modificar rutas (~143 rutas) |
| `app/Views/layouts/header.php` | Menu de navegacion, scripts globales, notificaciones |
| `app/Views/layouts/footer.php` | Sidebar tarea, lightbox, tour guiado, notificaciones JS, SW registro, PWA install |
| `public/css/styles.css` | Estilos globales (tema oscuro, responsive, a11y) |
| `public/js/task-sidebar.js` | Sidebar del calendario (formularios reactivos) |
| `public/js/ajax-navigation.js` | Navegacion SPA |
| `public/js/modal-functions.js` | Logica de modales + CSRF + toast + confirm + loading |
| `public/js/offline-queue.js` | Cola offline IndexedDB |
| `app/Controllers/BaseController.php` | Metodos compartidos (render, url, csrf, require*) |
| `app/Controllers/TareasController.php` | Modulo tareas (el mas complejo) |
| `app/Controllers/EconomiaController.php` | Modulo economia |
| `app/Controllers/ReportesController.php` | Reportes con datos reales y trends |
| `app/Controllers/NotificacionesController.php` | API de recordatorios |
| `app/Controllers/PwaController.php` | manifest.json + sw.js dinamicos |

---

## 14. Metodologia de trabajo
- NO propongas cambios sin explicar
- Explica PASO A PASO cada accion
- Usa comentarios en el codigo
- Ensena o educa mientras codificamos
