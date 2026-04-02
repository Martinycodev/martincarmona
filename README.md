# martincarmona.com

Web personal de Martín Carmona — Fotógrafo, Videógrafo, Diseñador y Desarrollador.

> Actualmente en desarrollo. La versión pública es una página placeholder provisional.

## Stack

- **Backend:** PHP 8+ (MVC propio, sin framework)
- **Frontend:** Tailwind CSS, Alpine.js, GSAP, Vite
- **Base de datos:** MySQL
- **Servidor:** Apache (XAMPP)

## Estructura

```
├── public/             # Document root (index.php, assets compilados)
├── src/
│   ├── Controllers/    # Controladores
│   ├── Core/           # Kernel: Router, Request, Response, Controller, ViteHelper
│   ├── Models/         # Modelos
│   ├── Views/          # Vistas y layouts
│   └── routes.php      # Definición de rutas
├── resources/          # CSS y JS fuente (procesados por Vite)
├── config/             # Configuración
├── database/           # Migraciones y seeds
├── vite.config.js
├── tailwind.config.js
└── composer.json
```

## Instalación local

```bash
# Clonar en htdocs de XAMPP
git clone https://github.com/MartinycoDev/martincarmona.git

# Dependencias PHP
composer install

# Dependencias frontend
npm install

# Copiar y configurar variables de entorno
cp .env.example .env

# Compilar assets (desarrollo)
npm run dev

# Compilar assets (producción)
npm run build
```

## Licencia

Todos los derechos reservados.
