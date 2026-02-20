# Sistema de Gestión Agrícola

Aplicación web para la digitalización de una explotación agrícola familiar. Centraliza la gestión de tareas de campo, trabajadores, maquinaria, parcelas y economía en una sola plataforma, sustituyendo el registro en papel.

---

## Propósito

El objetivo principal es tener un **diario de campo digital** que permita:

- Registrar qué trabajo se hizo, quién lo hizo, en qué parcela y cuántas horas
- Calcular el coste real de cada tarea y su impacto económico por parcela
- Controlar saldos y deudas de trabajadores
- Gestionar el inventario de vehículos y herramientas con sus revisiones
- Tener un historial trazable de toda la actividad de la explotación

---

## Módulos

| Módulo | Estado | Descripción |
|--------|--------|-------------|
| Tareas | ✅ Completo | CRUD + calendario interactivo + buscador con filtros |
| Trabajadores | ✅ Completo | Gestión completa de empleados |
| Trabajos | ✅ Completo | Tipos de trabajo con precio/hora |
| Parcelas | ✅ Completo | Fichas de parcelas con referencia catastral |
| Vehículos | ✅ Completo | Control de maquinaria y alertas de mantenimiento |
| Herramientas | ✅ Completo | Inventario de herramientas |
| Empresas | ✅ Completo | Gestión de empresas colaboradoras |
| Proveedores | ✅ Completo | Gestión de proveedores |
| Economía | 🚧 En desarrollo | Dashboard financiero, gastos, ingresos, deudas |

---

## Tech Stack

**Backend**
- PHP 8.2 con arquitectura MVC personalizada
- MySQL (base de datos remota en producción)
- Composer — gestión de dependencias
- `vlucas/phpdotenv` — variables de entorno

**Frontend**
- HTML5 / CSS3 (sin framework CSS)
- JavaScript vanilla
- Chart.js para gráficos
- FullCalendar para el calendario de tareas

**Seguridad implementada**
- Credenciales en `.env` (nunca en el código)
- Protección CSRF en todos los formularios y peticiones AJAX
- Sesiones endurecidas: `httponly`, `samesite=Lax`, timeout 2h, regeneración de ID

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
git clone https://github.com/tu-usuario/gestion-agricola.git
cd gestion-agricola
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

Importa el dump SQL más reciente desde `database/` en tu MySQL local.

**5. Configurar Apache**

Apunta el `DocumentRoot` a la carpeta raíz del proyecto o accede directamente desde XAMPP en:

```
http://localhost/martincarmona
```

---

## Estructura del Proyecto

```
martincarmona/
├── app/
│   ├── Controllers/    # Lógica de cada módulo (19 controladores)
│   ├── Models/         # Acceso a base de datos (9 modelos)
│   └── Views/          # Plantillas HTML/PHP
├── config/
│   ├── config.php      # Configuración general
│   ├── database.php    # Conexión MySQL (usa .env)
│   └── session.php     # Configuración segura de sesión
├── core/
│   ├── Autoloader.php  # PSR-4
│   ├── Router.php      # Sistema de rutas
│   └── CsrfMiddleware.php
├── public/
│   ├── css/
│   ├── js/             # JavaScript vanilla (7 archivos)
│   └── uploads/
├── vendor/             # Dependencias Composer
├── .env                # Variables de entorno (no en git)
├── .env.example        # Plantilla de configuración
├── index.php           # Entry point
└── ROADMAP.md          # Objetivos y estado del proyecto
```

---

## Planificación

Ver [ROADMAP.md](ROADMAP.md) para el estado detallado de objetivos pendientes, próximas funcionalidades y criterios de calidad.

---

## Licencia

Proyecto privado de uso interno para gestión de explotación agrícola familiar.
