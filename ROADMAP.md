# ROADMAP — Sistema de Gestión Agrícola

> Documento unificado de objetivos, estado y planificación.
> **Última actualización:** 20 de febrero de 2026

---

## Estado General

La aplicación está **operativa** con arquitectura MVC funcional, 8 módulos CRUD completos y conexión a base de datos remota. La fase de seguridad crítica está prácticamente terminada. El siguiente bloque prioritario es el **Módulo de Economía**.

---

## Checklist de Objetivos Pendientes

---

### PRIORIDAD ALTA

#### Seguridad — Input Validation

> **Motivo:** Actualmente los datos de formularios (fechas, horas, IDs) se usan directamente sin validar rangos ni formatos. Esto permite valores absurdos (ej. `horas: 999`) o entradas malformadas que pueden romper la lógica de negocio o la base de datos.

- [ ] Crear `core/Validator.php` con reglas: `required`, `date`, `numeric`, `min`, `max`, `integer`, `max_length`
- [ ] Aplicar validación en `TareasController` (crear, actualizar): `fecha`, `horas`, `trabajo`
- [ ] Aplicar validación en `TrabajadoresController`, `ParcelasController` y demás POST
- [ ] Sanitizar textos libres con `htmlspecialchars()` antes de guardar o mostrar

---

#### Módulo de Economía

> **Motivo:** Es el objetivo principal de la aplicación. Sin él no se puede calcular la rentabilidad de una parcela, controlar deudas de trabajadores ni tener una visión financiera de la explotación.

**Base de datos:**

- [ ] Crear tabla `deudas_trabajadores` (trabajador\_id, monto, fecha, descripcion, pagado)
  > Permite registrar anticipos, deudas y pagos a trabajadores de forma trazable
- [ ] Crear tabla `cuentas_bancarias` (nombre, tipo, saldo\_actual)
  > Permite saber en cada momento cuánto dinero hay en banco vs efectivo


**Backend:**

- [ ] Crear `EconomiaController` con métodos:
  - [ ] `index()` — Dashboard financiero con resumen de gastos, ingresos y saldo
  - [ ] `gastos()` — Listado y CRUD de gastos (combustible, insumos, reparaciones, etc.)
  - [ ] `ingresos()` — Listado y CRUD de ingresos (venta de cosecha, subvenciones, etc.)
  - [ ] `trabajadores_finanzas()` — Deudas, anticipos y pagos por trabajador
  - [ ] `reportes()` — Balance mensual y anual

**Vistas:**

- [ ] Dashboard económico: resumen de saldo, últimas transacciones, gráfico mensual
- [ ] Formularios CRUD para gastos e ingresos
- [ ] Vista de cuenta por trabajador (lo que se le debe, lo que ha cobrado)
- [ ] Generación de facturas o recibos en PDF (futura fase)
  > Facilita la justificación de pagos y el control fiscal

**Integración con tareas:**

- [ ] Calcular coste real de cada tarea: `horas × precio_hora_trabajo`
  > Ya existe `precios_trabajo` en BD. Conectar con el resumen económico
- [ ] Mostrar coste acumulado por parcela en el módulo de parcelas.
-  Valorar qué ocurre si este precio cambia.


---

### PRIORIDAD MEDIA

---

#### Base de Datos — Correcciones y Optimización

> **Motivo:** Hay errores de tipo de datos heredados del diseño inicial que pueden causar fallos al guardar nombres o DNIs largos. Los índices ausentes hacen que las búsquedas sean lentas a medida que crece la cantidad de datos.

- [ ] `ALTER TABLE empresas MODIFY nombre VARCHAR(255)` (actualmente es INT — error de diseño)
- [ ] `ALTER TABLE empresas MODIFY dni VARCHAR(20)` (mismo motivo)
- [ ] Añadir índices en columnas de búsqueda frecuente:
  - [ ] `CREATE INDEX idx_tareas_fecha ON tareas(fecha)`
  - [ ] `CREATE INDEX idx_tarea_trabajadores_trabajador ON tarea_trabajadores(trabajador_id)`
  - [ ] `CREATE INDEX idx_parcelas_propietario ON parcelas(propietario)`
  - [ ] `CREATE INDEX idx_movimientos_fecha ON movimientos(fecha)`
- [ ] Revisar si faltan `timestamps` (`created_at`, `updated_at`) en tablas principales

---

#### Arquitectura Backend — Limpieza de Deuda Técnica

> **Motivo:** El código actual mezcla responsabilidades (controladores con lógica de negocio directa, `require_once` manuales cuando ya existe un autoloader PSR-4). Esto hace el código más difícil de mantener y escalar.

- [ ] Eliminar `require_once` manuales de archivos que ya gestiona el autoloader PSR-4
  > El autoloader ya carga clases por namespace; los `require_once` son redundantes y confusos
- [ ] Mover las rutas de `index.php` a `routes/web.php`
  > Actualmente hay ~165 líneas de rutas en el archivo de entrada. Separarlas mejora la legibilidad
- [ ] Añadir soporte de parámetros dinámicos al Router (`/tareas/{id}`)
  > Permite URLs limpias y RESTful en lugar de query params (`?id=5`)
- [ ] Centralizar el manejo de errores y excepciones en un único punto
  > Actualmente los `error_log()` están dispersos; un handler central facilita el debug
- [ ] Eliminar `console.log()` del código JavaScript de producción
  > Expone información interna en la consola del navegador

---

#### Testing

> **Motivo:** Sin tests es imposible saber si un cambio en el código rompe algo que antes funcionaba. Con la aplicación creciendo (módulo de economía, nuevas reglas de negocio), el riesgo de regresiones aumenta.

- [ ] Instalar PHPUnit o Pest como dependencia de desarrollo
- [ ] Tests unitarios para modelos (Tarea, Trabajador, Parcela)
- [ ] Tests de integración para los controladores POST (crear, actualizar, eliminar)
- [ ] Cubrir los casos límite de la validación de inputs
- [ ] Objetivo mínimo: 50% de cobertura en lógica de negocio

---

#### Sistema de Logging

> **Motivo:** Cuando algo falla en producción, los `error_log()` dispersos no son suficientes para diagnosticar el problema. Un log estructurado permite filtrar por nivel, módulo y fecha.

- [ ] Instalar `monolog/monolog`
- [ ] Configurar canales: `app.log` para errores generales, `security.log` para eventos de autenticación
- [ ] Reemplazar los `error_log()` actuales por el logger centralizado
- [ ] Configurar rotación de logs para que no crezcan indefinidamente

---

### PRIORIDAD BAJA

---

#### Frontend — Modernización (Opcional / Largo Plazo)

> **Motivo:** El stack actual (JS vanilla, CSS plano de 1000+ líneas) funciona, pero es difícil de mantener cuando crece. La modernización no es urgente pero sí necesaria si el proyecto escala o necesita más interactividad.

- [ ] Evaluar si merece la pena introducir Alpine.js (ligero, reactivo, sin build complejo)
  > Alternativa liviana a Vue.js. Útil para modales, formularios dinámicos y dropdowns
- [ ] Crear `package.json` para gestionar dependencias JS formalmente
- [ ] Sustituir CDN de librerías por dependencias locales
  > Las CDN de terceros son una dependencia externa fuera de control. Las locales garantizan disponibilidad offline

---

#### Gestión de Usuarios Multi-rol

> **Motivo:** Actualmente solo existe un usuario. Si en el futuro varios trabajadores o el gestor necesitan acceder, hace falta control de acceso por rol (quién puede ver qué, quién puede editar).

- [ ] Crear tabla `usuarios` con roles (admin, empleado, consulta)
- [ ] Middleware de autorización por rol para rutas protegidas
- [ ] Registro y edición de usuarios desde panel de administración
- [ ] Log de actividad por usuario (quién creó, editó o eliminó qué)

---

#### Reportes Avanzados

> **Motivo:** Los datos ya están en la base de datos; lo que falta es presentarlos de forma analítica. Un PDF de balance mensual o un Excel de tareas por parcela ahorra mucho trabajo manual.

- [ ] Exportar listados a CSV/Excel (tareas, gastos, trabajadores)
- [ ] Generar PDF de balance económico mensual
- [ ] Gráficos de productividad por parcela con Chart.js (ya incluido en el stack)

---

#### DevOps

> **Motivo:** Docker y CI/CD son herramientas para equipos o proyectos que van a producción con despliegues frecuentes. Para uso local/familiar actual, son opcionales pero convenientes si se despliega en servidor.

- [ ] `docker-compose.yml` con PHP 8.3 + MySQL para desarrollo reproducible
  > Elimina la dependencia de XAMPP; cualquier máquina puede levantar el proyecto igual
- [ ] GitHub Actions para ejecutar los tests automáticamente en cada push
  > Evita subir código roto al repositorio sin darse cuenta
- [ ] Sistema de backups automáticos de la base de datos
  > Crítico si los datos de producción son reales (parcelas, cosechas, economía real de la explotación)

---

## Criterios de Calidad

- [ ] Todos los formularios POST validan y sanitizan inputs
- [ ] No hay credenciales en el código fuente (solo en `.env`)
- [ ] Tiempo de respuesta < 2 segundos en operaciones normales
- [ ] La aplicación es usable en móvil (uso en campo)
- [ ] Un cambio de código no rompe funcionalidad existente (tests)

---


## IDEAS SUELTAS
- [ ] añadir imagen a cada trabajador como foto de perfil.
- [ ] Añadir el campo "Alta en Seguridad social" para los trabajadores. y otro campo que sea "Cuadrilla" para añadirlo directamente en grupo a una tarea.
- [ ] Crear vista individual de trabajador, parcela, empresa, vehículo con información relevante de cada uno.
- [ ] añadir una vista de "campaña" que muestre una visión general de la tarea de "recoger aceituna" separada por fechas que empiecen en noviembre poniendo "campaña 25/26" por ejemplo.
- [ ] Añadir la opción de subir documentos a cada parcela (escritura y catastro), vehículos (documentación relevante).
- [ ] Dar importancia a la economía de los trabajadores y servicios a empresas externas.
- [ ] Mostrar trabajadores, vehículos, empresas en cajas en lugar de tabla. (ya que no van a ser muchos)
- [ ] Valorar la diferencia entre el concepto de Propietario, Empresa.
- [ ] Crear el módulo de Riego, Inventario, Fitosanitarios, Herramientas y Proveedores.
- [ ] Exportar cuentas de cada trabajador al final del mes.
- [ ] Añadir la posibilidad de Tareas sin realizar que aparezcan en el calendario fechadas para preveerlas. Por ejemplo pasar itv, sabes la fecha pero aún no la has hecho.
- [ ] Añadir la vista de enlaces de interes, en el footer por ejemplo.
- [ ] Añadir tareas pendientes que aún no estén agendadas.
- [ ] dar la posibilidad de hacer "drag and drop" con las tareas en el calendario.
- [ ] eliminar el botón del ojo en las tablas sustituyendolo por el click encima del campo.
 
