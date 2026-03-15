# Sistema de Gestión Agrícola

Aplicación web para la digitalización de una explotación agrícola de olivar en Jaén. Centraliza la gestión de tareas de campo, trabajadores, maquinaria, parcelas, economía, campañas de aceituna y fitosanitarios en una sola plataforma, sustituyendo el registro en papel.

---

## Propósito

El objetivo principal es tener un **diario de campo digital** que permita:

- Registrar qué trabajo se hizo, quién lo hizo, en qué parcela y cuántas horas
- Calcular el coste real de cada tarea y la deuda acumulada por trabajador
- Controlar saldos bancarios y en efectivo, gastos e ingresos por categoría
- Gestionar campañas de aceituna (kilos, rendimiento, beneficio por parcela)
- Llevar el inventario de fitosanitarios y su aplicación por parcela
- Controlar riego, vehículos, herramientas y proveedores
- Dar acceso limitado a propietarios de parcelas y trabajadores desde sus dispositivos

---

## Módulos

| Módulo | Estado | Descripción |
|--------|--------|-------------|
| Tareas | ✅ | CRUD + calendario interactivo + sidebar detalle + tareas pendientes (sin fecha) |
| Trabajadores | ✅ | Gestión completa, documentos, historial, deuda |
| Trabajos | ✅ | Tipos de trabajo con precio/hora, creación inline desde sidebar |
| Parcelas | ✅ | Fichas con catastro, tipo plantación, documentos, productividad |
| Propietarios | ✅ | DNI, contacto, parcelas vinculadas, vista detalle |
| Economía | ✅ | Dashboard (saldo total/banco/efectivo/deuda), gastos, ingresos, deudas trabajadores |
| Campañas | ✅ | Registro diario kilos, rendimiento aceite, cierre con precio venta |
| Fitosanitarios | ✅ | Inventario + aplicaciones por parcela, hook automático con tareas |
| Riego | ✅ | Registro por parcela y año, resumen m³ |
| Vehículos | ✅ | Inventario, ficha técnica y póliza adjuntas |
| Herramientas | ✅ | Inventario con PDF de instrucciones |
| Empresas | ✅ | Gestoras de parcelas |
| Proveedores | ✅ | Gestión de proveedores |
| Reportes | ✅ | KPIs reales: productividad, top trabajadores/parcelas, costes, alertas |
| Enlaces | ✅ | Links de interés agrícola (SIGPAC, IFAPA, meteo, PAC...) |
| Multi-rol | ✅ | Acceso diferenciado: empresa, admin, propietario, trabajador |

---

## Tech Stack

**Backend**
- PHP 8.2 — arquitectura MVC personalizada (sin framework)
- MySQL (base de datos remota en producción)
- Composer — `vlucas/phpdotenv`, `monolog/monolog`
- PHPUnit 11.5 para tests

**Frontend**
- HTML5 / CSS3 (tema oscuro, sin framework CSS)
- JavaScript vanilla (sin jQuery ni frameworks)
- Chart.js para gráficos
- FullCalendar para el calendario de tareas

**Seguridad**
- Credenciales en `.env` (nunca en el código)
- Protección CSRF en formularios y peticiones AJAX
- Sesiones endurecidas: httponly, samesite=Lax, timeout 2h, regeneración de ID
- Validación y sanitización de inputs (`Validator`, `htmlspecialchars`)
- Roles con middleware de autorización en backend

---

## Instalación local

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

Importa el dump SQL en tu MySQL local.

**5. Subir assets pesados**

Los videos (`public/video/`) no están en el repositorio. Súbelos manualmente si los necesitas.

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
│   ├── Models/         # 9 modelos (mysqli directo)
│   └── Views/          # 22 carpetas de vistas + layouts/
├── config/
│   ├── config.php      # APP_BASE_PATH, debug
│   ├── database.php    # Singleton mysqli (.env)
│   └── session.php     # Sesión segura (2h timeout)
├── core/
│   ├── Autoloader.php  # PSR-4
│   ├── CsrfMiddleware.php
│   ├── ErrorHandler.php
│   ├── Logger.php      # Monolog (app + security)
│   ├── Router.php      # Rutas estáticas + dinámicas {id}
│   └── Validator.php   # Validación de inputs
├── public/
│   ├── css/            # styles.css, autocomplete.css, search.css
│   ├── js/             # 6 archivos JS vanilla
│   ├── uploads/        # Archivos subidos por usuarios
│   └── video/          # Videos (no en git)
├── routes/web.php      # ~107 rutas
├── index.php           # Entry point
├── .env                # Variables de entorno (no en git)
├── CLAUDE.md           # Guía técnica para el asistente IA
└── ROADMAP.md          # Planificación y estado
```

---

## Planificación

Ver [ROADMAP.md](ROADMAP.md) para el backlog detallado de funcionalidades pendientes.

---

## Licencia

Proyecto privado de uso interno para gestión de explotación agrícola familiar.
