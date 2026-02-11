# **Sistema de Gestión Agrícola**

## **📄 Descripción**

Sistema web integral diseñado para la digitalización y optimización de explotaciones agrícolas.

El objetivo principal de la aplicación es **informatizar y centralizar** toda la información que actualmente se gestiona en papel, permitiendo un análisis detallado de la productividad y rentabilidad del campo. El sistema actúa tanto como un diario de campo para el registro de trabajos diarios, como una herramienta de planificación y gestión financiera.

### **🎯 Propuesta de Valor**

* **Gestión Centralizada:** Unifica tareas, economía, inventario y personal en una sola plataforma.  
* **Análisis de Rentabilidad:** Permite conocer el beneficio real de cada parcela contrastando gastos (trabajadores, maquinaria, insumos) contra ingresos (cosecha).  
* **Control de Riego:** Registro detallado de metros cúbicos y fechas para cumplimiento normativo.  
* **Gestión de Cosecha:** Trazabilidad de tickets de pesaje, rendimientos grasos e industriales y seguimiento de precios del aceite.

## **🚀 Capturas de Pantalla (Mockups)**

\<\!-- Cuando tengas la interfaz lista, sube las imágenes a una carpeta /assets/screenshots y enlázalas aquí \--\>

| Dashboard General | Gestión de Parcelas |
| :---- | :---- |
|  |  |
| *Vista general de ingresos, gastos y tareas pendientes* | *Mapa y listado de parcelas con sus estados* |

## **✨ Funcionalidades Principales**

### **🚜 Gestión de Campo**

* **Parcelas:** Ficha completa con referencia catastral, tipo (riego/secano), número de árboles y propietario.  
* **Tareas:** Registro de fecha, trabajador, herramienta usada, tiempo invertido y coste imputado.  
* **Riego:** Control de campañas de riego, lecturas de contadores y volúmenes cúbicos.  
* **Cosecha (Aceituna):** Gestión de tickets de pesaje, rendimientos y precios de liquidación.

### **💰 Economía y Finanzas**

* **Control de Costes:** Cálculo automático de costes por tarea basado en precio/hora de trabajadores y amortización de maquinaria.  
* **Cuentas Trabajadores:** Gestión de saldos, deudas y pagos a empleados.  
* **Inventario y Maquinaria:** Control de gastos de vehículos (ITV, Seguros, Reparaciones) y herramientas.  
* **Reportes:** Balances de ingresos vs. gastos, productividad por parcela y trabajador.

### **🛠️ Administración**

* **Usuarios:** Sistema de login seguro.  
* **Vehículos:** Alertas de mantenimiento y revisiones.

## **📅 Estado del Proyecto (Roadmap)**

El proyecto se encuentra en fase de desarrollo activo. A continuación se detalla el plan de trabajo:

### **🚨 PRIORIDAD ALTA \- Objetivos Críticos**

* \[ \] **Módulo de Economía**  
  * \[x\] Estructura de base de datos (precios\_trabajo, costos\_tarea).  
  * \[ \] Controladores de Economía (Dashboard, Gastos, Ingresos).  
  * \[ \] Vistas de reportes financieros y deudas de trabajadores.

### **🔧 PRIORIDAD MEDIA \- Mejoras Técnicas**

* \[ \] **Seguridad:** Implementación de CSRF tokens, sanitización de inputs y encriptación de contraseñas.  
* \[ \] **Optimización:** Refactorización a patrón MVC estricto, centralización de configuraciones.  
* \[ \] **Testing:** Tests unitarios para modelos y de integración para controladores.

### **🚀 PRIORIDAD BAJA \- Futuro**

* \[ \] **Gestión Multi-usuario:** Roles y permisos (Admin/Empleado).  
* \[ \] **API REST:** Para futuras integraciones móviles.  
* \[ \] **Reportes Avanzados:** Exportación a PDF/Excel y gráficos con Chart.js.

## **🛠️ Tecnologías Utilizadas (Tech Stack)**

### **Backend**

* **Lenguaje:** PHP (Arquitectura MVC personalizada).  
* **Base de Datos:** MySQL.

### **Frontend**

* **Estructura:** HTML5 / CSS3.  
* **Interactividad:** JavaScript (Vanilla & Chart.js para reportes).  
* **Diseño:** Responsivo (Mobile-first para uso en campo).

### **Herramientas**

* **Control de Versiones:** Git.  
* **Entorno Local:** XAMPP / Docker (Opcional).

## **⚙️ Instalación y Configuración Local**

Sigue estos pasos para desplegar el proyecto en tu entorno local.

### **Prerrequisitos**

* Servidor Web (Apache/Nginx).  
* PHP 8.0 o superior.  
* MySQL / MariaDB.  
* Composer (Opcional, si se usa para dependencias).

### **Pasos**

1. **Clonar el repositorio**  
   git clone \[https://github.com/tu-usuario/gestion-agricola.git\](https://github.com/tu-usuario/gestion-agricola.git)  
   cd gestion-agricola

2. **Base de Datos**  
   * Crea una base de datos vacía en MySQL (ej: db\_agricola).  
   * Importa el archivo database/schema.sql (o el dump más reciente).  
   * Configura la conexión en config/database.php o renombra .env.example a .env.

// Ejemplo de config  
define('DB\_HOST', 'localhost');  
define('DB\_USER', 'root');  
define('DB\_PASS', '');  
define('DB\_NAME', 'db\_agricola');

3. **Configurar Servidor**  
   * Apunta tu servidor web a la carpeta public/ del proyecto.  
   * Si usas el servidor interno de PHP para pruebas rápidas:

php \-S localhost:8000 \-t public

4. **Acceso**  
   * Abre tu navegador en http://localhost:8000.  
   * Credenciales por defecto (si aplica): admin / password.

## **📂 Estructura del Proyecto (MVC)**

/gestion-agricola  
  ├── /app  
  │   ├── /Controllers  \# Lógica de negocio  
  │   ├── /Models       \# Interacción con BD  
  │   └── /Views        \# Plantillas HTML/PHP  
  ├── /config           \# Configuración de BD y rutas  
  ├── /public           \# Entry point (index.php), CSS, JS, Assets  
  ├── /database         \# Scripts SQL y migraciones  
  └── /vendor           \# Librerías externas

## **🤝 Contribución**

Este es un proyecto de uso interno, pero las sugerencias son bienvenidas.

1. Haz un Fork.  
2. Crea una rama (git checkout \-b feature/nueva-funcionalidad).  
3. Commit (git commit \-m 'Add: Nueva funcionalidad').  
4. Push (git push origin feature/nueva-funcionalidad).  
5. Pull Request.

## **📄 Licencia**

Este proyecto es privado y para uso interno de la gestión agrícola.