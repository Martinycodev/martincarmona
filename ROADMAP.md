# ROADMAP — Sistema de Gestión Agrícola

> Documento unificado de objetivos, estado y planificación.
> **Última actualización:** 25 de febrero de 2026

---

## Estado General

La aplicación está **operativa** con arquitectura MVC funcional, 8 módulos CRUD completos y conexión a base de datos remota. El módulo de Economía está en desarrollo activo. Las siguientes fases completan la visión definida en `Proyecto.md`.

---

## Tabla de módulos

| Módulo | Estado | Notas |
|---|---|---|
| Tareas | ✅ Completo | CRUD + calendario + filtros |
| Trabajadores | ✅ Completo | Faltan imágenes de documentos (Fase 2) |
| Trabajos | ✅ Completo | Tipos de trabajo con precio/hora |
| Parcelas | ✅ Completo | Faltan campos técnicos y propietario FK (Fase 2) |
| Vehículos | ✅ Completo | Falta adjuntar documentación (Fase 2) |
| Herramientas | ✅ Completo | Falta PDF instrucciones (Fase 2) |
| Empresas | ✅ Completo | Gestoras de parcelas |
| Proveedores | ✅ Completo | |
| Riego | 🟡 BD existe | Tabla `riegos` en BD. Falta controller y vistas (Fase 2) |
| Economía | 🚧 En desarrollo | Dashboard, gastos, ingresos, deudas (Fase 1) |
| Propietarios | ⬜ Pendiente | Entidad propia separada de Empresas (Fase 2) |
| Tareas pendientes | ⬜ Pendiente | Tareas sin fecha asignada (Fase 2) |
| Fitosanitarios | ⬜ Pendiente | Registro automático por tipo de tarea (Fase 3) |
| Campaña | ⬜ Pendiente | Módulo completo nov→feb (Fase 3) |
| Multi-rol | ⬜ Pendiente | Acceso Propietario y Trabajador (Fase 4) |

---

## FASE 1 — Completar módulo Economía 🚧 EN CURSO

> Motor financiero de la aplicación. Sin él no se puede controlar deudas de trabajadores ni calcular rentabilidad.

**Base de datos:**
- [x] Añadir campo `cuenta` ENUM('banco','efectivo') a tabla `movimientos`
- [x] Crear tabla `pagos_mensuales_trabajadores` (trabajador_id, mes, año, importe_total, pagado, fecha_pago)

**Backend — `EconomiaController`:**
- [x] `index()` — Dashboard financiero: saldo banco, saldo efectivo, deuda total trabajadores
- [x] `gastos()` — CRUD de gastos con categoría (compras, reparaciones, inversiones, seguros, impuestos, gestoría) y cuenta (banco/efectivo)
- [x] `ingresos()` — CRUD de ingresos con categoría (labores a terceros, subvenciones, liquidación aceite) y cuenta
- [x] `deudas_trabajadores()` — Deuda acumulada por trabajador (suma de tareas del mes)
- [x] `cerrar_mes()` — Genera registro mensual de deuda por trabajador y lo marca como pendiente de pago
- [x] `registrar_pago()` — Marca el pago mensual como pagado, deuda → cero

**Integración con tareas:**
- [x] Calcular coste real al crear tarea: `horas_asignadas × precio_hora_trabajo` → acumular en deuda de cada trabajador en la tarea.
- [x] Mostrar coste acumulado por parcela en ficha de parcela.

**Vistas:**
- [x] Dashboard económico: saldo banco/efectivo, últimos movimientos, deudas pendientes
- [x] Formularios CRUD para gastos e ingresos
- [x] Vista de cuenta por trabajador: deuda actual, historial de pagos mensuales

---

## FASE 2 — Ampliaciones a módulos existentes

> Completar entidades ya iniciadas con los campos y funcionalidades que define Proyecto.md.

### Trabajadores
- [x] Añadir campo `baja_ss` DATE a tabla `trabajadores`
- [x] Subir y almacenar imagen DNI anverso + reverso
- [x] Subir y almacenar imagen documento de Seguridad Social
- [x] Vista individual de trabajador: datos, historial de tareas, estado de deuda

### Parcelas
- [x] Añadir campos: `referencia_catastral`, `tipo_olivos`, `año_plantacion`, `tipo_plantacion` ENUM('tradicional','intensivo','superintensivo'), `riego_secano` ENUM('riego','secano'), `corta` ENUM('par','impar','siempre')
- [x] Crear tabla `documentos_parcelas` (parcela_id, tipo ENUM('escritura','permiso_riego','otro'), archivo, nombre)
- [x] Vista individual de parcela: ficha completa + documentos + resumen productividad (coste anual por olivo)
- [x] Cambiar `propietario` de texto plano → FK a tabla `propietarios` (Fase 2 — ver abajo)

### Propietarios (entidad propia)
- [x] Crear tabla `propietarios`: `dni`, `imagen_dni_anverso`, `imagen_dni_reverso`, `nombre`, `apellidos`, `telefono`, `email`
- [x] Migrar datos actuales de `parcelas.propietario` (texto) a registros de la nueva tabla
- [x] CRUD completo de propietarios
- [x] Vista individual de propietario: sus parcelas y datos de contacto

### Riego (BD ya existe, solo falta frontend)
- [x] Reestructurar tabla `riegos`: cambiar campo `propiedad` (texto) → `parcela_id` INT FK a `parcelas`
- [x] Crear `RiegoController` con CRUD completo
- [x] Vistas: listado de riegos por año, formulario de nueva fase de riego
- [x] Resumen anual de m³ por parcela visible en la ficha de la parcela

### Vehículos y Herramientas
- [x] Vehículos: subir imagen ficha técnica y póliza de seguro (PDF/imagen)
- [x] Herramientas: subir PDF de instrucciones

### Tareas pendientes (sin fecha)
- [x] Hacer `fecha` nullable en tabla `tareas` + añadir campo `estado` ENUM('realizada','pendiente') DEFAULT 'realizada'
- [x] Vista separada de tareas pendientes (sin fecha)
- [x] Acción "Fechar tarea" para asignar fecha y pasarla a realizada
- [x] Si la tarea pendiente tiene trabajador asignado → visible en la vista del rol Trabajador (Fase 4)

---

## FASE 3 — Módulos nuevos

### Campaña (nov → feb/mar)
- [x] Crear tabla `campanas` (nombre: '25/26', fecha_inicio, fecha_fin, activa)
- [x] Crear tabla `campaña_registros` (campaña_id, parcela_id, fecha, kilos, rendimiento_pct, precio_venta, beneficio)
- [x] Sección de campaña con vista organizada por campaña (25/26, 26/27...)
- [x] Registro diario: parcela + kilos recogidos
- [x] Añadir rendimiento (% aceite/kg) a un registro existente — edición posterior
- [x] Al cerrar campaña: aplicar precio de venta → calcular y guardar `beneficio`
- [x] Reporte: beneficio campaña vs coste de producción acumulado por parcela
- [x] Reset del coste de producción al abrir nueva campaña

### Fitosanitarios
- [x] Crear tabla `fitosanitarios_inventario` (producto, fecha_compra, cantidad, unidad, proveedor_id)
- [x] Crear tabla `fitosanitarios_aplicaciones` (parcela_id, producto, fecha, cantidad, tarea_id)
- [x] Hook automático: al crear una tarea con trabajo "Sulfato" o "Herbicida" → generar entrada en `fitosanitarios_aplicaciones`
- [x] Vista de inventario de productos
- [x] Vista de historial de aplicaciones filtrable por parcela y producto

---

## FASE 4 — Multi-rol (Propietario y Trabajador)

> Dar acceso controlado a propietarios de parcelas y trabajadores desde sus propios dispositivos. Crear un usuario "admin" para gestionar usuarios.

**Base de datos:**
- [ ] Añadir campo `rol` ENUM('empresa','propietario','trabajador', 'admin') a tabla `usuarios`
- [ ] Añadir columna `propietario_id` a `usuarios` (FK a `propietarios`) para vincular login con propietario
- [ ] Añadir columna `trabajador_id` a `usuarios` (FK a `trabajadores`) para vincular login con trabajador

**Backend:**
- [ ] Middleware de autorización por rol para todas las rutas
- [ ] Panel de administración para crear y gestionar usuarios (solo rol empresa)

**Vista Propietario:**
- [ ] Sus parcelas y las tareas realizadas en ellas (sin mostrar trabajadores, horas ni precio)
- [ ] Datos de contacto de la empresa

**Vista Trabajador:**
- [ ] Deuda acumulada (lo que va a percibir este mes)
- [ ] Calendario con sus tareas realizadas
- [ ] Lista de tareas pendientes asignadas
- [ ] Datos de contacto de la empresa

**Vista Admin:**
- [ ] Lista de Usuarios
- [ ] Crear y gestionar usuarios y roles

---

## FASE 5 — Calidad técnica

> Consolidar la arquitectura antes de escalar más.

### Seguridad — Input Validation
- [ ] Crear `core/Validator.php` con reglas: `required`, `date`, `numeric`, `min`, `max`, `integer`, `max_length`
- [ ] Aplicar validación en `TareasController`, `TrabajadoresController`, `ParcelasController` y demás POST
- [ ] Sanitizar textos libres con `htmlspecialchars()` antes de guardar o mostrar

### Deuda técnica — Arquitectura
- [ ] Eliminar `require_once` manuales redundantes con el autoloader PSR-4
- [ ] Mover rutas de `index.php` a `routes/web.php`
- [ ] Añadir soporte de parámetros dinámicos al Router (`/tareas/{id}`)
- [ ] Centralizar el manejo de errores en un único handler
- [ ] Eliminar `console.log()` del JS de producción

### Testing
- [ ] Instalar PHPUnit o Pest como dependencia de desarrollo
- [ ] Tests unitarios para modelos (Tarea, Trabajador, Parcela)
- [ ] Tests de integración para controladores POST
- [ ] Objetivo mínimo: 50% de cobertura en lógica de negocio

### Logging
- [ ] Instalar `monolog/monolog`
- [ ] Configurar canales: `app.log` y `security.log`
- [ ] Reemplazar `error_log()` dispersos por el logger centralizado

---

## FASE 6 — Largo plazo / Extras

- [ ] Exportar CSV/Excel: tareas, gastos, cuenta mensual por trabajador
- [ ] PDF de balance mensual por trabajador
- [ ] Gráficos de productividad por parcela (Chart.js — ya en el stack)
- [ ] Evaluar Alpine.js para reactividad ligera en formularios complejos
- [ ] `docker-compose.yml` con PHP + MySQL para desarrollo reproducible
- [ ] GitHub Actions para ejecutar tests automáticamente en cada push
- [ ] Backups automáticos de la base de datos

---

## Criterios de calidad

- [ ] Todos los formularios POST validan y sanitizan inputs
- [ ] Tiempo de respuesta < 2 segundos en operaciones normales
- [ ] La aplicación es usable en móvil (uso en campo)
- [ ] Un cambio de código no rompe funcionalidad existente (tests)
- [ ] Cada rol solo ve lo que debe ver (autorización verificada en backend)
