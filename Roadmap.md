# Roadmap: martincarmona.com — Web de Marca Personal

## Contexto del proyecto

**Propietario**: Martín Carmona, 32 años, Jaén, España  
**Dominio**: martincarmona.com  
**Hosting**: Hostinger (PHP + MySQL)  
**Objetivo**: Web de marca personal que unifique trayectoria profesional en web, diseño, fotografía y vídeo. No es un CV estático, sino un hub personal que sirva tanto para freelance como para oportunidades laborales.

---

## Stack técnico

### Backend (existente)
- PHP 8.x con estructura MVC propia
- MySQL 8
- Composer + PSR-4
- Sistema de routing propio

### Frontend (a implementar)
| Tecnología | Propósito |
|------------|-----------|
| **Vite** | Bundler, dev server, build optimizado |
| **Tailwind CSS** | Sistema de diseño, tema oscuro custom |
| **Alpine.js** | Interactividad ligera (menús, filtros, modales) |
| **GSAP + ScrollTrigger** | Animaciones de scroll, reveals, transiciones |
| **Lenis** | Scroll suave premium |
| **Swiper** | Carruseles/sliders de portfolio |
| **GLightbox** | Lightbox para imágenes y vídeos |

### Tipografía propuesta
- **Títulos**: Clash Display (Fontshare, gratis)
- **Cuerpo**: Satoshi (Fontshare, gratis)

### Paleta (tema oscuro elegante)
- Fondo principal: #0a0a0a o similar
- Texto principal: #f5f5f5
- Acentos: a definir según identidad visual

---

## Referencias visuales

Webs que definen el tono estético buscado:

1. **[Warwick Acoustics](https://warwickacoustics.com/)**
   - Elegancia oscura
   - Tipografía grande
   - Animaciones de scroll muy cuidadas
   - Producto como protagonista

2. **[Design by Brandin](https://designbybrandin.com/)**
   - Minimalismo con personalidad
   - Transiciones suaves
   - Portfolio con presencia

3. **[Nikola Radeski](https://nikolaradeski.com/)**
   - Fondo oscuro
   - Tipografía expresiva
   - Scroll horizontal en portfolio
   - Efectos de hover con vida

### Características comunes a replicar
- Tema oscuro
- Tipografía bold/expresiva
- Animaciones de scroll (GSAP/Lenis)
- Espacios generosos
- Contenido visual como protagonista
- Cursor personalizado (opcional)

---

## Estructura de la web

```
┌─────────────────────────────────────────────────────────┐
│  1. HERO                                                │
│     → Nombre grande                                     │
│     → Frase definitoria                                 │
│     → Movimiento sutil / animación de entrada           │
├─────────────────────────────────────────────────────────┤
│  2. SOBRE MÍ                                            │
│     → Bio corta                                         │
│     → Foto personal                                     │
│     → Historia condensada                               │
├─────────────────────────────────────────────────────────┤
│  3. SERVICIOS / LO QUE HAGO                             │
│     → Web                                               │
│     → Diseño (cartelería, maquetación, RRSS)            │
│     → Fotografía                                        │
│     → Vídeo                                             │
│     → Con iconos o ilustraciones                        │
├─────────────────────────────────────────────────────────┤
│  4. PORTFOLIO                                           │
│     → Grid o scroll horizontal                          │
│     → Filtrable por categoría                           │
│     → Cada proyecto abre a detalle o lightbox           │
│     Categorías:                                         │
│       - Web                                             │
│       - Diseño (cartelería, maquetación, RRSS)          │
│       - Fotografía                                      │
│       - Vídeo                                           │
├─────────────────────────────────────────────────────────┤
│  5. BLOG (Fase 2)                                       │
│     → CRUD con MySQL                                    │
│     → Posts en Markdown (parseado con league/commonmark)│
│     → Artículos, reflexiones, aprendizajes              │
├─────────────────────────────────────────────────────────┤
│  6. CONTACTO                                            │
│     → Formulario simple                                 │
│     → Email                                             │
│     → Enlaces a redes (LinkedIn, GitHub, etc.)          │
└─────────────────────────────────────────────────────────┘
```

---

## Estructura de carpetas del proyecto

```
martincarmona.com/
├── public/                     # Document root en Hostinger
│   ├── index.php              # Entry point
│   ├── dist/                  # Assets compilados por Vite (git-ignored)
│   ├── uploads/               # Imágenes del portfolio
│   └── .htaccess              # Rewrite rules
├── src/
│   ├── Controllers/
│   │   ├── HomeController.php
│   │   ├── PortfolioController.php
│   │   ├── BlogController.php
│   │   └── ContactController.php
│   ├── Models/
│   │   ├── Project.php
│   │   └── Post.php
│   ├── Views/
│   │   ├── layouts/
│   │   │   └── main.php
│   │   ├── home/
│   │   │   └── index.php
│   │   ├── portfolio/
│   │   │   ├── index.php
│   │   │   └── show.php
│   │   ├── blog/
│   │   │   ├── index.php
│   │   │   └── show.php
│   │   └── contact/
│   │       └── index.php
│   ├── Core/                  # Tu MVC base
│   └── routes.php
├── resources/
│   ├── css/
│   │   ├── app.css           # Entry point Tailwind
│   │   └── components/       # Estilos custom si los hay
│   └── js/
│       ├── app.js            # Entry point JS
│       ├── animations.js     # GSAP + ScrollTrigger
│       ├── scroll.js         # Lenis
│       └── components/       # Alpine components
├── database/
│   ├── migrations/           # SQL de creación de tablas
│   └── seeds/                # Datos de ejemplo
├── vite.config.js
├── tailwind.config.js
├── postcss.config.js
├── composer.json
├── package.json
└── README.md
```

---

## Plan de ejecución por fases

### FASE 1: Setup del proyecto (1-2 días)
- [ ] Crear estructura de carpetas
- [ ] Inicializar `composer.json` con autoload PSR-4
- [ ] Inicializar `package.json`
- [ ] Configurar Vite para PHP (vite-plugin-php o similar)
- [ ] Configurar Tailwind con tema oscuro custom
- [ ] Instalar y configurar GSAP, Lenis, Alpine.js
- [ ] Crear layout base con estructura HTML semántica
- [ ] Configurar fuentes (Clash Display + Satoshi)
- [ ] Crear helper PHP para cargar assets de Vite

### FASE 2: Maquetación estática (3-5 días)
- [ ] Hero section con animación de entrada
- [ ] Sección "Sobre mí" con layout de texto + imagen
- [ ] Sección "Servicios" con grid de categorías
- [ ] Sección "Portfolio" con grid/scroll horizontal
- [ ] Footer con contacto básico
- [ ] Navegación con scroll suave entre secciones
- [ ] Responsive completo (mobile-first)
- [ ] Implementar animaciones de scroll (reveal, parallax)
- [ ] Hover effects en portfolio

### FASE 3: Funcionalidad dinámica (2-3 días)
- [ ] Modelo `Project` para portfolio
- [ ] CRUD de proyectos (admin simple o manual por BD)
- [ ] Filtros de portfolio por categoría (Alpine.js)
- [ ] Lightbox para imágenes y vídeos
- [ ] Formulario de contacto funcional (con validación + envío email)
- [ ] Protección CSRF en formularios

### FASE 4: Blog (2-3 días) — Opcional, puede posponerse
- [ ] Modelo `Post`
- [ ] Listado de posts con paginación
- [ ] Vista individual de post
- [ ] Parseo de Markdown con league/commonmark
- [ ] Panel de admin básico o gestión por BD

### FASE 5: Optimización y deploy (1-2 días)
- [ ] Build de producción con Vite
- [ ] Optimización de imágenes (formatos modernos, lazy loading)
- [ ] Minificación CSS/JS
- [ ] Testing en diferentes navegadores
- [ ] Configuración de Hostinger (document root, PHP version)
- [ ] Deploy inicial
- [ ] Configuración de SSL (Let's Encrypt en Hostinger)

---

## Contenido a recopilar (en paralelo)

### Inventario por categoría

#### WEB — Proyectos de desarrollo
- [ ] Listar todas las webs creadas (clientes, personales, académicas)
- [ ] Capturas de pantalla o URLs si siguen online
- [ ] Descripción breve de cada una
- [ ] Tecnologías usadas

#### FOTOGRAFÍA
- [ ] Tipo: eventos, producto, retrato, paisaje, documental...
- [ ] Seleccionar 10-15 fotos representativas
- [ ] Preparar en alta resolución + versiones optimizadas

#### VÍDEO
- [ ] Listar proyectos: spots, corporativos, redes, cortometrajes...
- [ ] URLs de YouTube/Vimeo o archivos
- [ ] Thumbnails para cada uno

#### DISEÑO
- [ ] Cartelería: recopilar JPGs/PDFs finales
- [ ] Maquetación: catálogos, revistas, documentos
- [ ] RRSS: posts, stories, templates
- [ ] Para quién se hizo cada pieza

### Textos a escribir

- [ ] **Frase hero**: una línea que te defina
- [ ] **Bio corta**: 2-3 párrafos sobre quién eres
- [ ] **Descripción de servicios**: qué ofreces en cada categoría
- [ ] **Descripción por proyecto**: título + párrafo + tecnologías/herramientas

### Assets visuales

- [ ] **Foto personal**: profesional pero con personalidad
- [ ] **Logo o logotipo**: si lo tienes, o decidir si se usa solo tipografía
- [ ] **Favicon**: versión simplificada de identidad

---

## Comandos útiles para desarrollo

```bash
# Instalar dependencias
composer install
npm install

# Desarrollo local
npm run dev          # Vite en modo watch
php -S localhost:8000 -t public  # Server PHP

# Build para producción
npm run build

# Deploy (ejemplo con rsync)
rsync -avz --exclude 'node_modules' --exclude '.git' ./ user@hostinger:/path/
```

---

## Notas adicionales

- **No cerrarse puertas**: la web debe funcionar tanto para captar clientes freelance como para mostrar perfil a empleadores potenciales.
- **Tema oscuro**: es la línea estética elegida, consistente con las referencias.
- **Animaciones con propósito**: no abusar, cada animación debe aportar a la experiencia.
- **Performance**: Hostinger no es el hosting más potente, optimizar todo lo posible.
- **Iterativo**: lanzar una v1 funcional y mejorar con el tiempo.

---

## Próximos pasos inmediatos

1. **Configurar el boilerplate** con Claude Code (estructura, Vite, Tailwind, GSAP, Lenis)
2. **Empezar a recopilar contenido** en paralelo (proyectos, fotos, textos)
3. **Maquetar el hero y la primera sección** como prueba de concepto visual
4. **Iterar** hasta conseguir el look deseado

---

*Última actualización: Marzo 2026*
