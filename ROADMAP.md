# ROADMAP — Sistema de Gestión Agrícola

> Documento de planificación y estado del proyecto.
> **Última actualización:** 21 de marzo de 2026

---

## Estado General

Aplicación **operativa en producción** con arquitectura MVC, 26 controladores, 15 modelos, ~143 rutas, sistema multi-rol, PWA instalable con soporte offline. Las fases 1-11 están cerradas. Queda trabajo de exportación, DevOps y ajustes de colores.

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
- [x] Fase 2 (avanzado): almacenar formularios pendientes en IndexedDB y sincronizar al recuperar conexión

---

## Pendiente — Backlog suelto

### Funcionalidades
- [x] Tareas pendientes en dashboard: panel dragable al calendario. Casilla "sin fecha" debajo del calendario para arrastrar tareas
- [x] Recordatorios/notificaciones push en perfil: cerrar cuentas del mes, ITV vehículos, otros. En el perfil poder activar y desactivar notificaciones. Abriendo la posibilidad de crear notificaciones personalizadas

- [x] La cuenta del valor de la tarea en el sidebar tiene que tener en cuenta el número de empleados.
- [x] En la vista de trabajador no se actualiza la deuda pendiente
- [x] En la vista de riego el select de filtro por años no funciona.

- [x] Todos los trabajos valen 0 € hasta que se editen. no quiero que se pueda quedar null
- [x] Los trabajadores pasan a ser "inactivos" cada dia 1 de mes y si hacen alguna tarea pasan a activos hasta que se acabe el mes. Si ese atributo no está en la bbdd hay que crearlo.
- [x] En el dashboard la opción de crear tarea pendiente junto a la lista de estas tareas. Si una tarea se edita y queda sin fecha se va automaticamente a tareas pendientes. Además quiero esta sección dentro de la caja del calendario, que aparezca debajo pero dentro del mismo bloque. Además junto al título quiero un botón que te lleve a la seccion de tareas pendientes y que ponga algo como "ver todas".
- [x] Solucionar el poder borrar trabajadores si dejamos de querer tenerlo en la base de datos, por despido o cómo gestionar ese proceso.
- [x] Añadir campos de nºMunicipio/nºpoligono/nºparcela a las parcelas. tanto en la vista de detalle como en la bbdd.
- [x] Al estar una campaña activa que aparezca un botón en los quik buttons del dashboard. Solo puede haber una campaña activa.
- [x] Crear al igual que campaña una temporada de riego. que se registre por años. y añadirle un quickbutton en el dashboard para que sea más rapido acceder a la vista de riego. tambien añadir el botón de terminar temporada de riego.
- [x] Eliminar el quickbutton de ver tareas. Para eso está busqueda de tareas.
- [x] Al registrar una tarea con trabajo de "recoger aceituna" que se cree un registro automáticamente en la campaña activa que guarde la fecha y la parcela.
- [x] El texto que se muestra en la tarea del calendario quiero que se escriba tanto como coja en el espacio disponible, no que se pongan los ... antes de que se ocupe todo el espacio. Si la ventana es pequeña se cortará antes y si la ventana es ancha se leerá todo el título.
- [x] Arreglar el crud de proveedores.
- [x] El diseño del sandwich del menú en movil no se ve bien. Mantiene las dimensiones del monitor.
- [x] Me gustaría que en la vista de trabajos además de la descripción se pudiese subir un archivo tipo documento con el contenido de cómo es el método de trabajo con imagenes y descripciones detalladas.
- [x] Añadir la opción de cambiar contraseña en la vista de perfil.
- [x] La opcion de cambiar de nombre de perfil no funciona
- [x] Eliminar quick button de tareas pendientes.
- [x] Subir imágenes en tarea no funciona, buscar la manera de optimizarlas antes de subirlas o poner un limite de peso.
- [x] El diseño de la vista de perfil tiene colores que no se corresponden con el estilo de la web. Prefiero utilizar el modo oscuro y los tonos verdes.
- [x] Formato de fechas en tablas tiene que ser dd-mm--aaaa. No aaaa-mm-dd
- [x] En la tabla de registros de campaña el bg-color de la fila del total esta en color blanco y debería ser negro o un tono oscuro.
- [x] Añadir combobox en el modal de registro de campaña para las parcelas.
- [x] Añadir el atributo calidad (Vuelo/Suelo) a el registro de campaña
- [x] Eliminar cabecera o welcome-section de busqueda avanzada. Añadir un botón que sea "Ver todas" para ir a /tareas en la caja de los filtros junto a limpiar y ocultar.
- [x] Añadir al atributo calidad de registro de campaña las siguientes opciones (Vuelo noviembre, vuelo diciembre, vuelo enero, vuelo febrero, vuelo marzo) y eliminar la opcion vuelo.
- [x] Al abrir la vista riego quiero que el filtro por año comience en el año actual. y que esté el año actual seleccionado.
- [x] en la vista /campana quiero que no aparezcan los botones de ver y eliminar. Al pulsar sobre toda la fila te manda a la vista /campana/detalle?id= y que abajo del todo aparezca la opción de eliminar pero como algo peligroso, o que no se suele hacer.
- [x] si el trabajador tiene alguna tarea asignada en el mes actual cambia su estado a activo.
- [x] en la vista e datos/trabajadores quiero que haya 2 tablas, la primera que tenga los trabajadores activos. y a continuación la otra con todos los trabajadores. Además quiero que aparezca solo las columnas foto, nombre, teléfono y Dni
- [x] en la vista parcelas quiero que no estén las columnas hidrante ni ubicación, solo Nombre, Propietario y Olivos. Además quitaría el atributo de ubicación porque no le veo el sentido ya que tenemos la referencia catastral.
- [x] Si la parcela se le pone el atributo de Riego_Secano como "secano" automáticamente el hidrante es "0" y no tiene que aparecer en la vista de detalle.
- [x] Sigue sin verse las imágenes subidas a tareas. Parece que se suben y se guardan pero en la previsualización desaparecen o no aparecen.
- [x] En busqueda avanzada quiero añadir filtrar por propietarios
- [x] Eliminar las welcome-section y sustituilas por texto centrado sobre el fondo, sin una caja o contenedor que ocupe mucho espacio.
- [x] Cuando una tarea se le borra la fecha se añade automáticamente a la lista de tareas pendientes. Me ha pasado de borrar la fecha de un par de tareas y que desaparezcan.
- [x] Arreglar las notificaciones, se queda en cargando... Y las notificaciones me gustaría que valoremos los recordatorios de itv, los pagos de los trabajadores, añadir otra notificacion estandar de mandar Jornadas reales a la gestoria.
- [x] añadir la opción de detalle de vehículo, y dentro poder editar los datos, añadir la opción de última itv y programar el recordatorio para la nueva fecha de validez cada vez que se actualice. 
- [x] Dentro de vehículos/detalle que tengamos el tema del precio del seguro y aseguradora junto con teléfono de contacto de la aseguradora.
- [x] Me gustaría añadir una imagen de la parcela que aparezca en una columna a la derecha de datos de la parcela. en proporción 1:1 y que cuando pinches la veas completa.
- [x] Plantear que los trabajos tengan categoría. Aún no se cómo separarlos y que en el dashboard se vean por colores.
- [x] En el modo movil, cuando abro un día para ver las tareas me gustaría que junto a la fecha hubieran unas flechas que me permita pasar al día siguiente y al anterior.
- [x] Podríamos Poner en el centro del header el logo del icono de arbol y en la esquina izquierda añadir un icono  de Calendario para que se entienda como el inicio o el home. 
- [x] Cuando seleccionamos trabajadores o parcelas se seleccionan con un tag verde. Me gustaría que en el apartado de trabajo también ocurra, aunque solo se pueda seleccionar uno. Si está seleccionado y elegimos otro se sustituye por el nuevo.
- [x] El quickbutton de busqueda avanzada me gustaría que tubiese un nombre más corto, solo Tareas. y una vez dentro poder darle a ver todas. El boton de  ver todas en /busqueda lo quiero de color verde.
- [x] Valorar que haya formularios reactivos en el sidebar, Por ejemplo:
 - Para que en el caso de seleccionar "Abrir riego" se crea un registro en la gestión de riego y que contenga la fecha y la parcela.
 - En el caso de seleccionar Recoger Aceituna que cree un nuevo registro con la fecha y la parcela.
 - En el caso de echar herbicida o sulfato que se cree el registro de aplicación de fitosanitarios con la fecha y la parcela. Más adelante se completaría la información
 - Plantear la posibilidad que al actualizar la tarea y cambiar el tipo de trabajo se borre el registro creado.

- [x] Arreglar modal de trabajos.
- [x] en gestión de tareas /tareas en la tabla, me gustaría cambiar la columna de acciones por la de trabajo, y que aparezca el tipo de trabajo. quitando el botón de eliminar con el icono de papelera.
- [x] He creado una tarea en el dashboar de tareas pendientes y no se ha añadido, aparece el toast de tarea pendiente creada pero no aparece por ningun sitio
- [x] Que en el calendario del dashboard se vea el título de la tarea y no el nombre del trabajo. (podemos poner un límite de caracteres para que no ocupe muchas lineas. por ejemplo 6 o 7 palabras como máximo)
- [x] El recordatorio de jornadas reales tiene que aparecer 2 días antes de que acabe el mes, y permanecer hasta el día 5.
- [x] Sería interesante que debajo de la configuración de notificaciones, las que están con el swicht para activar o desactivar tengan un pequeño texto en pequeño que indiquen cuando te avisarán y porqué. para decidir si quiero que me avisen o nó.
- [x] Al navergar por los apartados del menú lateral hay un pequeño parpadeo en el que se recargan varias veces la página. Por ejemplo pasar de reportes a tareas pendientes hace una acumulación de recargas de la web que puede ser muy incomodo de esperar. Por qué puede ser esto? Además se nota porque hay una animación de fadein que se repite muy rápido.
- [ ] Los colores de los trabajos son muy chulos pero el de recolección es naranja y con el texto blanco por lo que cuesta leerlo, y el de mantenimiento es amarillo y has puesto el texto negro, me gustaría que mantuviesen unos colores oscuros para que faciliten la lectura del texto blanco, podemos añadir el color verde olivo para la recolección, el marron para el campo (laboreo), y un tono purpura o morado para el mantenimiento. Podemos poner un tono rosa oscuro para los tratamientos sulfatos ya que el azul es muy parecido al de riego.
- [x] Me he dado cuenta que puede ser confuso la selección del tipo de trabajo. Quiero que al seleccionar el trabajo se borre el campo de escribir y aparezca de nuevo el place-holder. que quiero que ponga - Buscar y seleccionar.
- [x] Añadir en crear recordatorio personalizado la opción de periodicidad, que llamaremos repetición. Y pondremos la opción de "cada mes", "Cada año", "Cada X días"
- [x] Al hacer logout se queda en martincarmona.com/logout y sigue teniendo el header y se queda un poco pillado. Lo suyo es que al cerrar sesión te mande a martincarmon.com y ya.
- [x] El color morado de la categoría resulta ser demasiado oscuro. podemos pasarlo a un color un poco más violeta. que tenga buen contraste con el gris del fondo y que el texto blanco se lea bien.
- [x] En el modo movil las tareas se ven desde el mobiledaysheetbody tienen un pequeño borde izquierdo de color verde. Podríamos hacer que ese borde sea del color de la categoría.
- [x] El apartado de economía tiene el error de que muestra todos los registros de la base de datos, por lo que necesito que estos registros sean de cada usuario de manera privada. Ahora desde mi cuenta estoy viendo las finanzas de la cuenta demo.


### Infraestructura
- [x] Backups automáticos de la base de datos
- [x] Seed de la base de datos con datos exportados de Notion (Exportar csv de Notion y traducirlo a SQL respetando las conexiones) queremos añadirlo para estadísticas y comparar con datos del año anterior pero que no se refleje en la economía actual. Que aparezca como todas las tareas completadas y pagadas o que no generen ningún gasto ni deuda.

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
