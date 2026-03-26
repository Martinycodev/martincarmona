# ROADMAP — Sistema de Gestion Agricola

> Documento de planificacion y estado del proyecto.
> **Ultima actualizacion:** 21 de marzo de 2026

---

## Estado General

Aplicacion **operativa en produccion** en martincarmona.com. Arquitectura MVC con 26 controladores, 15 modelos, ~143 rutas. Sistema multi-rol, PWA instalable con soporte offline, accesibilidad WCAG AA, UX movil optimizada. Las **fases 1-11 estan cerradas**. El foco actual es pulido visual, exportacion de datos, seguridad avanzada y preparacion del camino hacia SaaS.

---

## Tabla de modulos

| Modulo | Estado | Notas |
|---|---|---|
| Dashboard | ✅ | Calendario + quick buttons + pendientes + meteo + drag&drop |
| Tareas | ✅ | CRUD + calendario + sidebar + pendientes + formularios reactivos |
| Trabajadores | ✅ | Docs, historial, deuda, foto, activo/inactivo auto |
| Trabajos | ✅ | Precio/hora, categorias con colores, documentos metodo |
| Parcelas | ✅ | Catastro, plantacion, documentos, imagen, productividad |
| Propietarios | ✅ | DNI, contacto, vista detalle, parcelas vinculadas |
| Vehiculos | ✅ | Ficha tecnica, poliza, seguro, ITV con recordatorio |
| Herramientas | ✅ | PDF instrucciones |
| Proveedores | ✅ | CRUD completo |
| Riego | ✅ | CRUD por parcela/ano, temporadas, filtro por ano |
| Economia | ✅ | Dashboard financiero, gastos, ingresos, deudas, cierre mes |
| Campanas | ✅ | Registro kilos, rendimiento, calidad (Vuelo/Suelo), cierre |
| Fitosanitarios | ✅ | Inventario + aplicaciones + descuento auto stock |
| Reportes | ✅ | 6 sub-paginas, KPIs reales, tendencias, alertas |
| Busqueda | ✅ | Multi-filtro: texto, fecha, trabajador, parcela, trabajo, propietario |
| Enlaces | ✅ | SIGPAC, IFAPA, meteo, PAC, laboral, tecnologia |
| Notificaciones | ✅ | Recordatorios auto (ITV, cuentas, jornadas) + personalizados |
| Perfil | ✅ | Nombre, contrasena, config notificaciones |
| PWA | ✅ | Service worker, cache, IndexedDB offline, banner instalacion |
| Multi-rol | ✅ | empresa, admin, propietario, trabajador |
| Admin | ✅ | Gestion de usuarios y roles |
| Landing | ✅ | SEO (OG, JSON-LD, sitemap), contacto con anti-spam |

---

## Historial de fases completadas

| Fase | Descripcion |
|------|-------------|
| 1 | **Economia** — Dashboard financiero, gastos, ingresos, deudas, cierre mes |
| 2 | **Ampliaciones** — Trabajadores (DNI, docs), parcelas (catastro, FK propietario), riego, vehiculos |
| 3 | **Modulos nuevos** — Campanas aceituna, fitosanitarios (inventario + aplicaciones) |
| 4 | **Multi-rol** — Roles empresa/admin/propietario/trabajador, middleware, panel admin |
| 5 | **Calidad tecnica** — Validator, PSR-4, router dinamico, error handler, Monolog, PHPUnit |
| 6 | **Funcionalidades sueltas** — UX tablas, detalle propietarios, meteo, enlaces, reportes reales, SEO homepage |
| 7 | **Arreglar lo roto** — Riego, fitosanitarios, limpieza controllers duplicados, 5 modelos nuevos, .gitignore |
| 8 | **UX Movil** — Bottom-sheet, swipe calendario, sidebar fullscreen, tablas responsive, modales fullscreen |
| 9 | **Feedback visual** — Toast global (`showToast`), `showConfirm`, skeleton loaders, `setButtonLoading`, transiciones |
| 10 | **Accesibilidad** — Skip-nav, ARIA, focus visible, contraste WCAG AA, cierre con Escape |
| 11 | **PWA y offline** — Service worker, manifest, IndexedDB offline queue, pantalla sin conexion, banner instalacion |
| 13 | **Seguridad avanzada** — Rate limiting login (5/15min), .htaccess uploads, ACL en obtener() |

Ademas se completaron ~60 mejoras sueltas del backlog: formularios reactivos en sidebar, notificaciones/recordatorios, categorias de trabajos con colores, drag&drop de tareas, imagenes en parcelas y tareas, detalle vehiculos con seguro/ITV, seed de datos desde Notion, backups automaticos, multi-tenancy por id_user en economia, y muchas correcciones de UX.

Mejoras adicionales (marzo 2026): touch drag & drop en dashboard (chips pendientes y tareas del calendario funcionan en movil), multi-tenancy en trabajos (busqueda y eliminacion filtran por id_user).

---

## FASE X — Terminar la funcionalidad de fitosanitarios, Campaña y economía.

- [ ] Hacer más intuitivo y cómodo registrar los productos y las aplicaciones mediante combobox, y poder seleccionar varios productos en varias parcelas ya que con una cuba de 3000 litros y de varios productos se puede aplicar a varias parcelas.
- [ ] Habría que hacer un cálculo de cuanto líquido se ha aplicado a cada parcela en funcion de los olivos para dividir la dosis de la cuba.
- [ ] Para terminar esta sección tendríamos que valorar qué nos van a pedir desde el cuaderno de campo de la Union Europea para que podamos exportarlo en un formato que sirva.

- [ ] Hay que terminar de entender cómo funciona el cobro de la liquidación de la campaña porque hay que añadir variables como Rendimiento industrial, Rendimiento de laboratorio, prima por vuelo de cada mes, precio de venta del aceite (que pueden ser en varias veces) y cómo se cobra que también se hace en varios pagos.

- [ ] Pagar a trabajadores que no tenemos contratados se paga con el dinero efectivo. Por lo que los pagos hay que seleccionar con qué dinero se paga.
- [ ] El concepto de una deuda mensual y que tenga que cerrar el mes manualmente me preocupa, lo suyo es que sea automático.
- [ ] Plantear la posibilidad de hacer pagos mensuales recurrentes que se añaden solos por ejemplo, gestoría, autónomo, seguros, etc.

- [ ] Comprobar la subida de archivos de parcelas, trabajadores, vehículos, trabajos.

- [ ] Crear vista general campaña, días trabajados, y lluvias. como en mi excel. (buscar referencia)

- [x] Al crear una tarea con abrir riego, se crea un registro automáticamente en la bbdd de riego. Lo malo es que al borrar la tarea no se elimina el registro. por lo que tenemos dos opciones, que se borre por cascade, o que tengamos la opcion de borrar el registro en la tabla riego.

- [x] El usuario con rol de empresa es el responsable de crear los usuarios de los trabajadores y propietarios. Por lo que en la vista mi perfil tiene que poder crear usuarios y contraseñas para sus trabajadores y propietarios creando la conexion de su trabajador con el nickname y el password correspondiente. Me gustaría que los trabajadores o propietarios que tengan un usuario tengan por defecto @miolivar.es como prefijo. Podríamos poner su DNI por defecto o dejar que la empresa elija el nombre y comprobar que no hay ningún usuario con el mismo nickname.

-
---

## FASE 13 — Seguridad avanzada ✅

> Mejoras de seguridad para uso en produccion. Completada 24 marzo 2026.

- [x] **Rate limiting en login** — Tabla `login_attempts`, max 5 intentos/15min por IP, log en `security.log`, limpieza tras login exitoso.
- [x] **`.htaccess` en uploads** — `php_flag engine off` + `Deny from all` para `.php/.phtml/.php5` en `public/uploads/`.
- [x] **Validacion ACL en `obtener()`** — `ParcelasController::obtener()` ahora filtra por `id_user`. `TrabajadoresController` ya lo tenía.

> Detalle completo en `docs/plans/2026-03-16-analisis-estrategico-saas.md` (secciones 1.1 y 1.2)

---

## FASE 14 — URLs REST y helper ACL

> Refactorizar URLs de detalle a path parameters REST y centralizar validacion de propiedad.

**Objetivo:** Cambiar `/propietarios/detalle?id=123` → `/propietarios/123` en los 5 modelos con vista detalle.

**Incluye:**
- [x] Crear helper `validateAndGetResource($id, $table)` en `BaseController`
- [x] Registrar rutas dinamicas: `/propietarios/{id}`, `/trabajadores/{id}`, `/parcelas/{id}`, `/campana/{id}`, `/propietario/parcela/{id}`
- [x] Actualizar metodos `detalle()` en los 5 controladores
- [x] Actualizar links en vistas (index.php de cada modulo)
- [x] Actualizar URLs en JavaScript (grep `detalle?id=`)
- [x] Eliminar rutas antiguas `/detalle?id=`

> Detalle completo en `docs/plans/refactorizar-urls-rest.md`

---

## FASE 15 — Exportacion de datos

> Sacar datos del sistema para analisis externo, respaldo legible y pagos a trabajadores.

- [ ] **Exportacion CSV** — Controller `ExportController` con metodo generico `exportCsv()`. Endpoints: `/exportar/tareas`, `/exportar/gastos`, `/exportar/deuda/{id}`. Separador `;`, BOM UTF-8 para Excel. Botones de descarga en vistas.
- [ ] **PDF balance mensual por trabajador** — Dependencia `dompdf/dompdf`. Vista HTML con cabecera (trabajador, mes), tabla de tareas/horas/precio, pie con total y deuda. Ruta `/reportes/balance-pdf/{id}?mes=X&year=Y`. Boton en detalle trabajador.
- [ ] **PDF balance anual por propietario** — Dependencia `dompdf/dompdf`. Vista HTML con cabecera (propietario, año), tabla de tareas/horas/precio, pie con total y deuda. Ruta `/reportes/balance-pdf/{id}?year=Y`. Boton en detalle propietario.
- [ ] **Graficos de productividad por parcela** — Endpoint JSON con horas/tareas por parcela agrupadas por mes (12 meses). Canvas Chart.js en reportes.

> Detalle completo en `docs/plans/2026-03-16-analisis-estrategico-saas.md` (secciones 1.3, 1.4, 1.5)

---

## FASE 16 — DevOps

> Entorno reproducible y tests automaticos.

- [ ] **Docker** — `Dockerfile` (PHP 8.2 + Apache + mysqli), `docker-compose.yml` (web + MySQL), `.env.docker`. Documentar en README.
- [ ] **GitHub Actions CI** — Workflow `.github/workflows/tests.yml` con PHPUnit en cada push/PR. MySQL como service container. Badge en README.

> Detalle completo en `docs/plans/2026-03-16-analisis-estrategico-saas.md` (secciones 1.6, 1.7)

---

## FUTURO — Camino hacia SaaS multiusuario

> Vision a largo plazo. No ejecutar hasta que las fases 12-16 esten cerradas.
> Plan detallado en `docs/plans/2026-03-16-analisis-estrategico-saas.md`

### Fase A — Cimientos tecnicos

| Item | Descripcion | Esfuerzo |
|------|-------------|----------|
| Sistema de migrations | Script `migrate.php` + tabla `migrations` + archivos numerados | Medio |
| Tenant isolation automatica | Clase base `Model` con `scopedQuery()` que inyecte `WHERE id_user = ?` | Alto |
| QueryBuilder ligero | Encadenamiento sobre mysqli (`->table()->where()->get()`) | Alto |
| Tests de integracion tenant | Un test por modelo CRUD verificando aislamiento de datos | Medio |

### Fase B — Registro, onboarding y billing

| Item | Descripcion | Esfuerzo |
|------|-------------|----------|
| Registro publico | Alta con verificacion de email, contrasena con requisitos | Medio |
| Onboarding wizard | Configuracion inicial: nombre explotacion, ubicacion, parcelas | Medio |
| Planes y billing | Free/Pro/Enterprise + Stripe Checkout + middleware `requirePlan()` | Alto |
| Panel admin global | Dashboard operador: usuarios activos, metricas, soporte | Medio |
| RGPD y legal | Consentimiento, export datos personales, eliminacion cuenta | Medio |

### Fase C — Hardening y escala

| Item | Descripcion | Esfuerzo |
|------|-------------|----------|
| API REST versionada | `/api/v1/*` + JWT/API keys + rate limiting por tenant + OpenAPI | Alto |
| Cache layer | Redis/APCu para queries frecuentes, invalidacion en escritura | Medio |
| Queue system | Tabla `jobs` para PDFs, CSVs, emails + worker cron | Medio |
| Monitoring | Metricas rendimiento, alertas errores (Sentry), uptime checks | Medio |

### Fase D — Crecimiento y diferenciacion

- App movil nativa o PWA avanzada consumiendo API REST
- Integraciones: SIGPAC, sensores IoT riego, estaciones meteo locales, cuaderno explotacion EU
- Marketplace de modulos por tenant
- Multi-idioma (i18n)
- White-label (logo, colores por tenant)
- Naming (martincarmona) es mi nombre y apellido.

---

## Estado de seguridad

| Categoria | Estado | Notas |
|-----------|--------|-------|
| SQL Injection | ✅ | Queries parametrizadas en todos los modelos |
| XSS | ✅ | `htmlspecialchars()` en todas las vistas |
| CSRF | ✅ | `hash_equals()`, meta tag, auto-inject en fetch |
| Autenticacion | ✅ | `password_hash()` + `password_verify()` + session regen |
| Autorizacion | ✅ | Middleware por rol en `BaseController` |
| Sesiones | ✅ | httponly, samesite, regeneracion 30 min, timeout 2h |
| Uploads | ✅ | MIME validation + `.htaccess` anti-PHP |
| Rate Limiting | ✅ | 5 intentos/15min por IP, log en security.log |
| ACL en obtener() | ✅ | Todos los endpoints filtran por id_user |

---

## Criterios de calidad

- [ ] Todos los formularios POST validan y sanitizan inputs
- [ ] Tiempo de respuesta < 2 segundos en operaciones normales
- [ ] La aplicacion es usable en movil (uso en campo)
- [ ] Un cambio de codigo no rompe funcionalidad existente (tests)
- [ ] Cada rol solo ve lo que debe ver (autorizacion verificada en backend)
- [ ] Contraste WCAG AA en todos los textos (ratio >= 4.5:1)
- [ ] Todos los elementos interactivos son accesibles por teclado
- [ ] Targets tactiles >= 44x44px en movil
