# Sistema de Gestion Agricola — MartinCarmona.com

Aplicacion web para la digitalizacion de una explotacion agricola de olivar en Jaen. Centraliza la gestion de tareas de campo, trabajadores, maquinaria, parcelas, economia, campanas de aceituna, fitosanitarios, riego, vehiculos, herramientas y proveedores en una sola plataforma, sustituyendo el registro en papel.

Disponible como **PWA** (Progressive Web App): se puede instalar en movil y funciona offline.

---

## Proposito

El objetivo principal es tener un **diario de campo digital** que permita:

- Registrar que trabajo se hizo, quien lo hizo, en que parcela y cuantas horas
- Calcular el coste real de cada tarea y la deuda acumulada por trabajador
- Controlar saldos bancarios y en efectivo, gastos e ingresos por categoria
- Gestionar campanas de aceituna (kilos, rendimiento, calidad, beneficio por parcela)
- Llevar el inventario de fitosanitarios y su aplicacion por parcela
- Controlar riego con temporadas, vehiculos con ITV y seguros, herramientas y proveedores
- Recibir recordatorios automaticos (ITV, cuentas, jornadas) y personalizados
- Consultar reportes con KPIs reales, tendencias y alertas
- Dar acceso limitado a propietarios de parcelas y trabajadores desde sus dispositivos

---

## Modulos

| Modulo | Estado | Descripcion |
|--------|--------|-------------|
| Dashboard | ✅ | Calendario interactivo + quick buttons + tareas pendientes + meteorologia + drag&drop |
| Tareas | ✅ | CRUD + calendario + sidebar detalle + pendientes (sin fecha) + imagenes + formularios reactivos |
| Trabajadores | ✅ | Gestion completa, documentos, historial, deuda, foto, activo/inactivo automatico |
| Trabajos | ✅ | Tipos de trabajo con precio/hora, categorias con colores, documentos de metodo |
| Parcelas | ✅ | Fichas con catastro (municipio/poligono/parcela), tipo plantacion, documentos, imagen, productividad |
| Propietarios | ✅ | DNI (anverso/reverso), contacto, parcelas vinculadas, vista detalle |
| Economia | ✅ | Dashboard (saldo total/banco/efectivo/deuda), gastos, ingresos, deudas trabajadores, cierre mes |
| Campanas | ✅ | Registro diario kilos, rendimiento aceite, calidad (Vuelo por mes/Suelo), cierre con precio venta |
| Fitosanitarios | ✅ | Inventario + aplicaciones por parcela, descuento automatico de stock |
| Riego | ✅ | Registro por parcela y ano, temporadas, resumen m3, filtro por ano |
| Vehiculos | ✅ | Inventario, detalle, ficha tecnica, poliza, seguro/aseguradora, ITV con recordatorio |
| Herramientas | ✅ | Inventario con PDF de instrucciones |
| Proveedores | ✅ | Gestion completa |
| Reportes | ✅ | 6 sub-paginas: personal, parcelas, trabajos, economia, recursos, proveedores. KPIs reales con tendencias |
| Busqueda | ✅ | Busqueda avanzada de tareas (multi-filtro: texto, fecha, trabajador, parcela, trabajo, propietario) |
| Enlaces | ✅ | Links de interes agricola (SIGPAC, IFAPA, meteo, PAC, laboral, tecnologia) |
| Notificaciones | ✅ | Recordatorios automaticos (ITV, cuentas, jornadas) + personalizados con repeticion |
| Perfil | ✅ | Cambiar nombre, contrasena, configurar notificaciones |
| PWA | ✅ | Instalable en movil, service worker, cache offline, cola de formularios (IndexedDB) |
| Multi-rol | ✅ | Acceso diferenciado: empresa, admin, propietario, trabajador |
| Admin | ✅ | Gestion de usuarios y roles |
| Landing | ✅ | Pagina publica con SEO (Open Graph, JSON-LD, sitemap), formulario de contacto con anti-spam |

---

## Tech Stack

**Backend**
- PHP 8.2 — arquitectura MVC personalizada (sin framework)
- MySQL (base de datos remota en produccion)
- Composer — `vlucas/phpdotenv`, `monolog/monolog`
- PHPUnit 11.5 para tests

**Frontend**
- HTML5 / CSS3 (tema oscuro, sin framework CSS)
- JavaScript vanilla (sin jQuery ni frameworks)
- Chart.js para graficos
- Calendario custom vanilla JS con drag&drop y swipe tactil

**PWA**
- Service Worker (cache-first assets, network-first HTML)
- Web App Manifest con iconos multi-tamano
- IndexedDB para cola de formularios offline
- Banner de instalacion nativo

**Seguridad**
- Credenciales en `.env` (nunca en el codigo)
- Proteccion CSRF en formularios y peticiones AJAX
- Sesiones endurecidas: httponly, samesite=Lax, timeout 2h, regeneracion de ID
- Validacion y sanitizacion de inputs (`Validator`, `htmlspecialchars`)
- Roles con middleware de autorizacion en backend
- Multi-tenancy: datos filtrados por `id_user` en todas las queries
- Formulario contacto: honeypot + timestamp + rate-limit por IP

**Accesibilidad**
- Navegacion por teclado con skip-nav y focus visible
- ARIA labels, roles y live regions
- Contraste WCAG AA (ratio ≥ 4.5:1)
- Targets tactiles ≥ 44px en movil
- Estados no solo por color (icono + texto)

---

## Instalacion local

### Requisitos

- PHP 8.0 o superior
- MySQL / MariaDB
- Apache (XAMPP recomendado en Windows)
- Composer

### Pasos

**1. Clonar el repositorio**

```bash
git clone https://github.com/Martinycodev/martincarmona.git
cd martincarmona
```

**2. Instalar dependencias**

```bash
composer install
```

**3. Configurar variables de entorno**

```bash
cp .env.example .env
```

Edita `.env` con tus datos:

```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost/martincarmona

DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=nombre_base_datos

SESSION_LIFETIME=7200
SESSION_SECURE=false
SESSION_SAMESITE=Lax
```

**4. Importar la base de datos**

Importa `database/Dump.sql` en tu MySQL local. Luego ejecuta las migraciones de la carpeta `migrations/` en orden numerico.

**5. Subir assets pesados**

Los videos (`public/video/`) no estan en el repositorio. Subelos manualmente si los necesitas.

**6. Acceder**

```
http://localhost/martincarmona
```

---

## Estructura del Proyecto

```
martincarmona/
├── app/
│   ├── Controllers/    # 26 controladores
│   ├── Models/         # 15 modelos (mysqli directo)
│   └── Views/          # 23 carpetas de vistas + layouts/
├── config/
│   ├── config.php      # APP_BASE_PATH, debug
│   ├── database.php    # Singleton mysqli (.env)
│   └── session.php     # Sesion segura (2h timeout)
├── core/
│   ├── Autoloader.php  # PSR-4
│   ├── CsrfMiddleware.php
│   ├── ErrorHandler.php
│   ├── Logger.php      # Monolog (app + security)
│   ├── Router.php      # Rutas estaticas + dinamicas {id}
│   └── Validator.php   # Validacion de inputs
├── database/
│   ├── Dump.sql        # Dump principal
│   ├── backup.php      # Script de backups
│   └── seed_*.php/sql  # Seeds de datos (demo, Notion)
├── migrations/         # ~25 archivos SQL de migraciones
├── public/
│   ├── css/            # styles.css, autocomplete.css, search.css
│   ├── js/             # 8 archivos JS vanilla
│   ├── img/            # Favicon, og-cover, iconos PWA
│   ├── uploads/        # Archivos subidos por usuarios
│   ├── video/          # Videos (no en git)
│   └── offline.html    # Pantalla sin conexion
├── routes/web.php      # ~143 rutas
├── index.php           # Entry point
├── .env                # Variables de entorno (no en git)
├── CLAUDE.md           # Guia tecnica para el asistente IA
├── ROADMAP.md          # Planificacion y estado de fases
└── TAREAS.md           # Tareas pendientes por aplicar
```

---

## Planificacion

Ver [ROADMAP.md](ROADMAP.md) para el historial completo de fases (1-11 completadas) y el backlog de funcionalidades pendientes.

### Fases completadas

| Fase | Descripcion |
|------|-------------|
| 1 | Modulo Economia (dashboard financiero, gastos, ingresos, deudas) |
| 2 | Ampliaciones modulos existentes (trabajadores, parcelas, propietarios, riego, vehiculos) |
| 3 | Modulos nuevos (campanas aceituna, fitosanitarios) |
| 4 | Multi-rol (empresa, admin, propietario, trabajador) |
| 5 | Calidad tecnica (Validator, PSR-4, router, error handler, logging, PHPUnit) |
| 6 | Funcionalidades sueltas (UX, meteo, reportes, SEO homepage) |
| 7 | Arreglar lo roto (riego, fitosanitarios, limpieza controllers duplicados, modelos faltantes) |
| 8 | UX Movil (calendario movil, bottom-sheet, tablas responsive, formularios tactiles) |
| 9 | Feedback visual (toasts, skeleton loaders, loading buttons, transiciones) |
| 10 | Accesibilidad (skip-nav, ARIA, focus visible, contraste WCAG AA) |
| 11 | PWA y uso offline (service worker, manifest, IndexedDB, pantalla offline) |

---

## Licencia

Proyecto privado de uso interno para gestion de explotacion agricola familiar.
