# CLAUDE.md — Guía de desarrollo para el asistente IA

> Este archivo lo lee Claude antes de hacer cualquier cambio en el código.
> Última actualización: 15 de marzo de 2026

---

## 1. Resumen del proyecto

Aplicación web MVC en PHP puro para la gestión integral de una explotación agrícola de olivar en Jaén. Sustituye el registro en papel: tareas de campo, trabajadores, parcelas, economía, campañas de aceituna, fitosanitarios, riego y más.

- **Entorno local:** XAMPP (Windows) — `http://localhost/martincarmona`
- **Producción:** hosting compartido (Hostinger)
- **Base de datos remota:** MySQL en producción (`u873002419_campo`)
- **Repositorio:** `https://github.com/Martinycodev/martincarmona`

---

## 2. Arquitectura

```
martincarmona/
├── app/
│   ├── Controllers/   # 26 controladores (BaseController como padre)
│   ├── Models/        # 9 modelos (acceso directo a mysqli)
│   └── Views/         # 22 carpetas de vistas + layouts/
├── config/
│   ├── config.php     # APP_BASE_PATH, debug, nombre app
│   ├── database.php   # Singleton mysqli, credenciales desde .env
│   └── session.php    # SessionConfig: timeout 2h, regen 30min
├── core/
│   ├── Autoloader.php # PSR-4
│   ├── CsrfMiddleware.php
│   ├── ErrorHandler.php
│   ├── Logger.php     # Monolog: app.log + security.log
│   ├── Router.php     # Rutas estáticas + dinámicas {id}
│   └── Validator.php  # required, date, numeric, min, max, etc.
├── public/
│   ├── css/           # styles.css, autocomplete.css, search.css
│   ├── js/            # 6 archivos JS vanilla
│   ├── uploads/       # Imágenes subidas por usuarios
│   └── video/         # Videos pesados (NO en git, subir manualmente)
├── routes/web.php     # ~107 rutas
├── index.php          # Entry point
├── .env               # Variables de entorno (no en git)
└── composer.json       # phpdotenv, monolog, phpunit
```

### Flujo de una petición

1. `index.php` → carga autoloader, .env, config, sesión, DB
2. `Router::run()` → match exacto o dinámico `{id}` → `Controller@method`
3. `BaseController::render($view, $data)` → inyecta CSRF, detecta AJAX
4. Si es AJAX → devuelve solo el contenido (sin layout header/footer)
5. Si es normal → envuelve en `layouts/header.php` + `layouts/footer.php`

### Navegación AJAX

`ajax-navigation.js` intercepta clicks en links de navegación y solo extrae el `.container` del HTML devuelto. Consecuencia importante: **los `<script>` fuera de `.container` no se ejecutan en navegación AJAX**. Para que el JS funcione tras navegación AJAX usa este patrón:

```js
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
```

### CSRF

- Backend: `BaseController::validateCsrf()` lee de `$_POST['csrf_token']` o header `X-CSRF-TOKEN`
- Frontend: `modal-functions.js` auto-inyecta `X-CSRF-TOKEN` en todos los `fetch()` POST/PUT/DELETE
- Meta tag en header: `<meta name="csrf-token" content="...">`
- Lectura defensiva en JS: `document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? ''`

---

## 3. Stack técnico

| Capa | Tecnología |
|------|------------|
| Backend | PHP 8.2, MVC propio, mysqli |
| Base de datos | MySQL / MariaDB |
| Frontend | HTML5, CSS3 puro (tema oscuro), JS vanilla |
| Gráficos | Chart.js |
| Calendario | FullCalendar |
| Logs | Monolog (app.log + security.log) |
| Tests | PHPUnit 11.5 |
| Dependencias | Composer (phpdotenv, monolog) |

**No se usan frameworks CSS ni JS.** Todo es vanilla. No añadir jQuery, Bootstrap, Tailwind ni similar salvo que se pida explícitamente.

---

## 4. Base de datos — Tablas principales

| Tabla | Descripción |
|-------|-------------|
| `usuarios` | Login, rol (empresa/admin/propietario/trabajador) |
| `tareas` | Tareas de campo (fecha nullable = pendiente) |
| `tarea_trabajadores` | N:M tarea↔trabajador con horas_asignadas |
| `tarea_parcelas` | N:M tarea↔parcela |
| `tarea_trabajos` | N:M tarea↔trabajo con horas_trabajo y precio_hora |
| `trabajadores` | Empleados (nombre, docs, baja_ss) |
| `trabajos` | Tipos de trabajo con precio/hora |
| `parcelas` | Fincas (catastro, olivos, tipo plantación, propietario_id FK) |
| `propietarios` | Dueños de parcelas (DNI, contacto) |
| `movimientos` | Gastos e ingresos (tipo, categoría, cuenta banco/efectivo) |
| `pagos_mensuales_trabajadores` | Deuda mensual por trabajador |
| `fitosanitarios` | Inventario de productos fitosanitarios |
| `fitosanitarios_aplicaciones` | Registro de aplicaciones por parcela |
| `campanas` | Campañas de aceituna (nov→feb) |
| `campana_registros` | Kilos, rendimiento, precio por parcela y campaña |
| `riegos` | Registros de riego por parcela |
| `vehiculos`, `herramientas`, `proveedores` | CRUDs básicos |

---

## 5. Roles y acceso

| Rol | Acceso |
|-----|--------|
| `empresa` | Todo — es el usuario principal (agricultor) |
| `admin` | Gestión de usuarios + lo mismo que empresa |
| `propietario` | Solo sus parcelas y tareas en ellas (sin horas/precios) |
| `trabajador` | Su deuda, calendario personal, tareas pendientes asignadas |

Middleware: `requireEmpresa()`, `requireEmpresaOAdmin()`, `redirectByRole($rol)`.

---

## 6. Convenciones de código

### PHP
- Controladores heredan de `BaseController`
- Modelos usan `mysqli` directamente (no ORM)
- Sanitización: `htmlspecialchars()` en toda salida de datos de BD a vista
- Validación: `core/Validator.php` en todos los POST
- Rutas en `routes/web.php`: formato `$router->get('/ruta', 'Controller@method')`

### JavaScript
- Vanilla JS — sin frameworks ni jQuery
- Variable global `window._APP_BASE_PATH` o `basePath` para URLs
- Patrón AJAX-safe: comprobar `document.readyState` antes de `addEventListener`
- CSRF: leer meta tag, no hardcodear tokens

### CSS
- Tema oscuro: fondos `#1a1a1a` / `#2a2a2a`, texto `#ccc` / `#fff`
- Acento verde: `#4caf50` (primario), `#a8d5ab` (secundario/claro)
- Acento rojo: `#f44336` (errores, deudas, negativos)
- Acento azul: `#2196f3` (banco, info)
- Border-radius: `10px` en cards, `8px` en inputs
- Sin framework CSS — todo en `public/css/styles.css` + estilos inline en vistas

### Git
- Mensajes en español o inglés, formato convencional (`feat:`, `fix:`, `chore:`)
- Assets pesados (videos, dumps) NO van al repo — subir manualmente al host
- `public/video/` está en `.gitignore`

---

## 7. Patrones frecuentes

### Crear un nuevo CRUD
1. Modelo en `app/Models/` con métodos estáticos (`getAll`, `find`, `create`, `update`, `delete`)
2. Controlador en `app/Controllers/` heredando `BaseController`
3. Vistas en `app/Views/{modulo}/` (index.php, detalle.php, etc.)
4. Rutas en `routes/web.php`
5. Link en `app/Views/layouts/header.php` si es menú principal

### Modales
- Se definen en la misma vista con `<div class="modal" id="...">`
- `modal-functions.js` maneja apertura/cierre y auto-inyecta CSRF en fetch
- El controlador devuelve JSON → el JS actualiza el DOM sin recargar

### Combobox / Typeahead
- Se inicializan con `initCombobox(containerId, opciones, onSelectCallback)`
- Permiten crear nuevos items si el texto no existe en las opciones
- Estilos en `public/css/autocomplete.css`

### Sidebar de tarea (Dashboard)
- `public/js/task-sidebar.js` — clase `TaskSidebar`
- Se abre al hacer click en una tarea del calendario
- Carga datos via AJAX, permite editar trabajadores, parcelas, trabajos inline
- Los trabajadores y parcelas se añaden al hacer click en la opción del combobox (sin botón "+")

---

## 8. Cosas a evitar

- **No añadir frameworks** (jQuery, Bootstrap, Tailwind, Alpine) salvo petición explícita
- **No usar ORM** — los modelos usan mysqli directamente
- **No crear archivos innecesarios** — editar los existentes siempre que sea posible
- **No subir videos ni assets pesados a git** — van al host manualmente
- **No romper la navegación AJAX** — los scripts deben funcionar tanto en carga normal como AJAX
- **No hardcodear URLs** — usar `$this->url()` en PHP y `window._APP_BASE_PATH` en JS
- **No añadir console.log en producción**
- **No mockear la BD en tests** — usar datos reales o test DB

---

## 9. Ubicación de la meteorología

Widget en dashboard: Open-Meteo API (gratuita, sin API key)
- Latitud: 37.78, Longitud: -3.79 (Jaén)
- Forecast 7 días con códigos WMO mapeados a emojis en español

---

## 10. Archivos clave para ediciones frecuentes

| Archivo | Para qué |
|---------|----------|
| `routes/web.php` | Añadir/modificar rutas |
| `app/Views/layouts/header.php` | Menú de navegación |
| `public/css/styles.css` | Estilos globales |
| `public/js/task-sidebar.js` | Sidebar del calendario |
| `public/js/ajax-navigation.js` | Navegación SPA |
| `public/js/modal-functions.js` | Lógica de modales + CSRF |
| `app/Controllers/BaseController.php` | Métodos compartidos |
| `app/Controllers/EconomiaController.php` | Módulo economía |
| `app/Controllers/TareasController.php` | Módulo tareas |
| `app/Controllers/ReportesController.php` | Reportes con datos reales |


## 11. Metodología de trabajo
- NO propongas cambios sin explicar
- Explica PASO A PASO cada acción
- Usa comentarios en el código
- Enseña o educa mientras codificamos