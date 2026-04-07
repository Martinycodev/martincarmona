# ROADMAP — martincarmona.com · Marca Personal

> Objetivo: Posicionarse como freelance técnico-creativo ante empresas y recruiters técnicos,
> con una presencia web minimalista de alto impacto respaldada por proyectos reales.

---

## Contexto del proyecto

**Propietario**: Martín Carmona, 32 años, Jaén, España
**Dominio**: martincarmona.com
**Hosting**: Hostinger (PHP + MySQL)
**Estado actual**: Placeholder público desplegado. Deploy automático vía GitHub webhook.

La web no es un CV estático, sino un hub personal que sirva tanto para freelance como para oportunidades laborales. Unifica trayectoria en desarrollo web, diseño, fotografía y vídeo.

---

## Metodología

Antes de tocar CSS o código de frontend, se define la narrativa. Todo lo demás —web, materiales, case studies— cuelga de ahí. El proceso sigue tres capas en orden:

1. **Narrativa** → quién eres, qué resuelves, por qué importas
2. **Evidencia** → los proyectos que demuestran la narrativa
3. **Canal** → la web y materiales donde todo convive

---

## Stack técnico

### Backend (existente)
- PHP 8.x con estructura MVC propia (decisión deliberada, no carencia)
- MySQL 8
- Composer + PSR-4
- Sistema de routing propio

### Frontend
| Tecnología | Propósito |
|------------|-----------|
| **Vite** | Bundler, dev server, build optimizado |
| **Tailwind CSS** | Sistema de diseño, tema oscuro custom |
| **Alpine.js** | Interactividad ligera (menús, filtros, modales) |
| **GSAP + ScrollTrigger** | Animaciones de scroll, reveals, transiciones |
| **Lenis** | Scroll suave premium |
| **Swiper** | Carruseles/sliders de portfolio |
| **GLightbox** | Lightbox para imágenes y vídeos |

### Tipografía
- **Títulos**: Clash Display (Fontshare)
- **Cuerpo**: Satoshi (Fontshare)
- **Placeholder actual**: Switzer (Fontshare, neo-grotesque)

### Paleta (tema oscuro)
- Fondo principal: #0a0a0a
- Texto principal: #f5f5f5
- Acentos: a definir según identidad visual final

---

## Skills de Claude Code

### Existentes
| Skill | Uso en este proyecto |
|---|---|
| `frontend-design` | Hero section, portfolio, layout web |
| `brand-guidelines` | Sistema de colores, tipografía, identidad |
| `docx` | CV exportable, brief de proyecto |
| `pdf` | CV en PDF, case studies imprimibles |
| `pptx` | Slide deck de presentación de proyectos |

### A crear (específicas del proyecto)

#### `personal-brand-strategist`
Experto en marca personal para perfiles técnico-creativos. Ayuda a construir mensajes claros, directos y memorables orientados a recruiters técnicos. Usa la estructura: contexto real → decisión tomada → resultado concreto. Nunca usa: "apasionado por", "full-stack", "me encanta aprender".

#### `project-case-study`
Estructura la historia de cada proyecto: problema real → decisiones clave (y por qué) → resultado concreto + aprendizaje. Produce el texto del case study y la estructura para la web.

#### `portfolio-content-planner`
Curador de portfolio. Prioriza proyectos según impacto, rareza del perfil y coherencia con la narrativa. Recomienda qué destacar, qué omitir y qué complementar con material visual.

---

## Referencias visuales

Webs que definen el tono estético buscado:

1. **[Warwick Acoustics](https://warwickacoustics.com/)** — Elegancia oscura, tipografía grande, animaciones de scroll cuidadas
2. **[Design by Brandin](https://designbybrandin.com/)** — Minimalismo con personalidad, transiciones suaves
3. **[Nikola Radeski](https://nikolaradeski.com/)** — Fondo oscuro, tipografía expresiva, scroll horizontal en portfolio

### Características comunes a replicar
- Tema oscuro
- Tipografía bold/expresiva, tipo helvetica
- Animaciones de scroll (GSAP/Lenis)
- Espacios generosos
- Contenido visual como protagonista

---

## Plan de ejecución por fases

---

### FASE 1 — Narrativa y posicionamiento
**Estado:** 🔲 Pendiente
**Prioridad:** ALTA — todo lo demás depende de esto
**Duración estimada:** 1-2 sesiones

**Tareas:**
- [x] Definir la propuesta de valor en 1 frase (el elevator pitch)
- [x] Escribir la bio en 3 versiones: 1 línea / 3 líneas / párrafo completo
- [x] Identificar el perfil diferenciador: técnico que diseña, o diseñador que desarrolla
- [x] Definir el tono de comunicación (directo, sin hype, con evidencia)
- [x] Listar los proyectos disponibles y clasificarlos por tipo

**Skill:** `personal-brand-strategist` (a crear)
**Output:** Documento `narrativa.md` con bio, propuesta de valor y tono definidos

---

### FASE 2 — Inventario de contenido y case studies
**Estado:** 🔲 Pendiente
**Prioridad:** ALTA — sin contenido real no hay web
**Duración estimada:** 2-3 sesiones

#### 2.1 Inventario por categoría

**Desarrollo web:**
- [ ] Listar todas las webs creadas (clientes, personales, académicas)
- [ ] Capturas de pantalla o URLs si siguen online
- [ ] Descripción breve + tecnologías usadas

**Diseño gráfico:**
- [ ] Cartelería: recopilar JPGs/PDFs finales
- [ ] Maquetación: catálogos, revistas, documentos
- [ ] RRSS: posts, stories, templates
- [ ] Para quién se hizo cada pieza

**Fotografía:**
- [ ] Clasificar por tipo: eventos, producto, retrato, paisaje, documental
- [ ] Seleccionar 10-15 fotos representativas
- [ ] Preparar en alta resolución + versiones optimizadas

**Vídeo:**
- [ ] Listar proyectos: spots, corporativos, redes, cortometrajes
- [ ] URLs de YouTube/Vimeo o archivos
- [ ] Thumbnails para cada uno

#### 2.2 Case studies (los más relevantes)

**Sistema de Gestión Agrícola (PHP MVC):**
- [ ] Contexto: explotación familiar, gestión en papel → digital
- [ ] Decisiones técnicas: por qué MVC propio, CSRF manual, estructura de módulos
- [ ] Resultado: módulos completados, en producción real
- [ ] Material visual: capturas, diagrama de arquitectura

**Otros proyectos:**
- [ ] Seleccionar 2-3 proyectos más de diseño, vídeo o foto
- [ ] Documentar con el marco: problema → decisión → resultado

#### 2.3 Textos y assets

- [ ] Frase hero definitiva
- [ ] Descripción de servicios por categoría
- [ ] Descripción por proyecto: título + párrafo + herramientas
- [ ] Foto personal profesional
- [ ] Logo/logotipo (o decidir si solo tipografía)
- [ ] Favicon

**Skill:** `project-case-study` + `portfolio-content-planner` (a crear)
**Output:** Carpeta `case-studies/` con un `.md` por proyecto + assets organizados

---

### FASE 3 — Setup técnico y maquetación
**Estado:** 🟡 Parcialmente completado (boilerplate existe)
**Duración estimada:** 3-5 sesiones

#### 3.1 Setup (ya completado en su mayoría)
- [x] Estructura de carpetas
- [x] Composer con autoload PSR-4
- [x] Package.json con dependencias
- [x] Vite configurado para PHP
- [x] Tailwind con tema oscuro custom
- [x] GSAP, Lenis, Alpine.js instalados
- [x] Layout base con estructura HTML semántica
- [x] Fuentes configuradas (Clash Display + Satoshi)
- [x] Helper PHP para cargar assets de Vite
- [x] Deploy automático con GitHub webhook

#### 3.2 Maquetación

**Estructura de la web:**
```
/
├── Hero           → nombre, propuesta de valor, CTA
├── Sobre mí       → bio expandida, foto, forma de trabajar
├── Servicios      → web, diseño, fotografía, vídeo
├── Portfolio      → grid/scroll, filtrable por categoría
├── Proceso        → cómo trabajas (opcional, diferenciador fuerte)
├── Blog           → (Fase 5, puede posponerse)
└── Contacto       → formulario simple + enlaces
```

**Tareas de maquetación:**
- [ ] Hero section con animación de entrada
- [ ] Sección "Sobre mí" con layout texto + imagen
- [ ] Sección "Servicios" con grid de categorías
- [ ] Sección "Portfolio" con grid/scroll horizontal
- [ ] Navegación con scroll suave entre secciones
- [ ] Footer con contacto
- [ ] Responsive completo (mobile-first)
- [ ] Animaciones de scroll (reveal, parallax)
- [ ] Hover effects en portfolio

**Skills:** `frontend-design`, `brand-guidelines`

---

### FASE 4 — Funcionalidad dinámica
**Estado:** 🔲 Pendiente
**Duración estimada:** 2-3 sesiones

- [ ] Modelo `Project` para portfolio
- [ ] CRUD de proyectos (admin simple o por BD)
- [ ] Filtros de portfolio por categoría (Alpine.js)
- [ ] Lightbox para imágenes y vídeos (GLightbox)
- [ ] Formulario de contacto funcional (validación + email)
- [ ] Protección CSRF en formularios

---

### FASE 5 — Blog (opcional, puede posponerse)
**Estado:** 🔲 Pendiente
**Duración estimada:** 2-3 sesiones

- [ ] Modelo `Post`
- [ ] Listado con paginación
- [ ] Vista individual
- [ ] Parseo de Markdown con league/commonmark
- [ ] Panel de admin básico

---

### FASE 6 — Pulido, materiales y lanzamiento
**Estado:** 🔲 Pendiente
**Duración estimada:** 1-2 sesiones

**Web:**
- [ ] Revisión de textos: coherencia de tono
- [ ] Optimización de imágenes (formatos modernos, lazy loading)
- [ ] Build de producción con Vite
- [ ] SEO: meta tags, og:image, structured data
- [ ] Test en dispositivos reales (móvil, tablet, desktop)
- [ ] Test en diferentes navegadores
- [ ] SSL verificado (Let's Encrypt en Hostinger)
- [ ] Retirar placeholder y activar rutas completas

**Materiales colaterales:**
- [ ] CV en PDF (máximo 1 página, descargable desde la web)
- [ ] Brief de presentación en DOCX (para enviar a empresas)

---

### FASE 7 — Mindful AI Planner (módulo backend independiente)
**Estado:** 🔲 Pendiente — pendiente de aprobación de diseño
**Prioridad:** MEDIA (no bloquea la web pública)
**Naturaleza:** Módulo aislado dentro del mismo backend. No comparte rutas, vistas ni assets con la marca personal.

#### 7.0 Concepto

Planificador personal asistido por IA cuyo objetivo es **combatir la procrastinación mediante minimalismo radical**:

- **Una sola tarea visible a la vez** (la actual + un teaser de la siguiente)
- **Notificaciones solo en transiciones** (cambio de actividad o descanso)
- **Replanificación dinámica con IA** cuando el usuario se desvía, sin generar ansiedad ni dependencia tóxica
- **Check-in diario retrospectivo** que ajusta la carga de los días siguientes según estado anímico/energía

**Stack confirmado (2026-04-07):**
| Capa | Tecnología |
|---|---|
| Backend | PHP 8.x + MVC propio existente ✅ |
| BD | MySQL 8 (mismas credenciales, prefijo `planner_`) ✅ |
| IA | Anthropic API — `claude-sonnet-4-6` con salida JSON estricta ✅ |
| Notificaciones | Bot de Telegram (`sendMessage` + webhook para botones inline) ✅ |
| Scheduler | Cron de Hostinger cada 1 min → endpoint interno `/planner/tick` con token ✅ |
| Multi-usuario | **No** — uso personal de Martín. Esquema sin `user_id` ✅ |

---

#### 7.1 Roadmap de implementación (paso a paso)

> Orden lógico para integrar el módulo sin tocar la web pública. Cada paso es un PR independiente y desplegable.

**Paso 1 — Aislamiento estructural**
- [ ] Crear namespace `App\Modules\Planner\` bajo `src/Modules/Planner/`
- [ ] Subcarpetas: `Controllers/`, `Models/`, `Services/`, `Prompts/`, `Jobs/`
- [ ] Registrar grupo de rutas `/planner/*` en `src/routes.php` con middleware propio (`PlannerAuthMiddleware`)
- [ ] Variables de entorno nuevas en `.env`: `ANTHROPIC_API_KEY`, `TELEGRAM_BOT_TOKEN`, `TELEGRAM_CHAT_ID`, `PLANNER_TICK_TOKEN`

**Paso 2 — Capa de datos**
- [ ] Migración SQL con las 5 tablas del apartado 7.2
- [ ] Modelos PHP correspondientes (PSR-4, sin ORM, PDO directo como el resto del proyecto)
- [ ] Seeder con un objetivo de prueba

**Paso 3 — Servicio de IA (`PlannerAIService`)**
- [ ] Cliente HTTP minimalista para Anthropic Messages API (Guzzle o cURL nativo)
- [ ] Método `generateDailySchedule(array $goals, array $constraints): array`
- [ ] Validación estricta del JSON devuelto (esquema fijo, rechazar y reintentar 1 vez si no valida)
- [ ] Logging de cada request/response en `planner_ai_logs` para auditoría y debugging

**Paso 4 — Endpoints minimalistas (Dashboard API)**
- [ ] `GET  /planner/api/now` → `{ current_task: {...completo...}, next_task: { title } }`
- [ ] `POST /planner/api/tasks/{id}/complete` → marca completada, devuelve la siguiente
- [ ] `POST /planner/api/tasks/{id}/postpone` → registra en `planner_postpone_log`, recalcula resto del día
- [ ] Todas con CSRF y token de sesión personal (no auth pública)

**Paso 5 — Gestor de transiciones (cron + notificaciones)**
- [ ] `Jobs/TickJob.php`: ejecuta cada minuto vía cron, comprueba si hay transición pendiente en los próximos 60s
- [ ] `Services/TelegramNotifier.php`: envía mensajes con botones inline (`Completar` / `Posponer 15min` / `Estoy en ello`)
- [ ] Webhook `/planner/telegram/webhook` para recibir respuestas a botones
- [ ] **Regla anti-ansiedad:** máximo 1 notificación cada 25 minutos, silencio total fuera de horario activo

**Paso 6 — Check-in diario retrospectivo**
- [ ] Cron a las 22:00 → `Jobs/DailyCheckinJob.php`
- [ ] Lee `planner_postpone_log` del día + tareas completadas
- [ ] IA genera 1-2 preguntas contextuales (estado de ánimo, energía)
- [ ] Respuestas se guardan en `planner_checkins`
- [ ] IA recalcula el calendario de los próximos 3 días aplicando coeficiente de carga (0.7 si estrés detectado)

**Paso 7 — Frontend mínimo (opcional, fase posterior)**
- [ ] Vista única `/planner/now` con la tarea actual a pantalla completa
- [ ] Sin menús, sin navegación, solo: título, descripción, tiempo restante, 2 botones
- [ ] Reutiliza Tailwind del proyecto principal pero con su propio entry de Vite

**Paso 8 — Hardening**
- [ ] Rate limiting en endpoints expuestos
- [ ] Token rotativo para `/planner/tick`
- [ ] Backup diario de tablas `planner_*`
- [ ] Tests unitarios del validador de JSON de la IA

---

#### 7.2 Diseño de base de datos

> Todas las tablas con prefijo `planner_` para no colisionar con futuras tablas de la web. InnoDB, utf8mb4.

**`planner_goals`** — propósitos a largo plazo del usuario
```sql
CREATE TABLE planner_goals (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title           VARCHAR(255) NOT NULL,
  description     TEXT,
  horizon_weeks   SMALLINT UNSIGNED NOT NULL COMMENT 'Plazo en semanas',
  priority        TINYINT UNSIGNED NOT NULL DEFAULT 3 COMMENT '1=máxima, 5=mínima',
  status          ENUM('active','paused','done','dropped') DEFAULT 'active',
  created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;
```

**`planner_constraints`** — restricciones de tiempo recurrentes (sueño, comidas, citas fijas)
```sql
CREATE TABLE planner_constraints (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  label         VARCHAR(120) NOT NULL,
  type          ENUM('sleep','meal','fixed_event','focus_window','no_work') NOT NULL,
  weekday_mask  TINYINT UNSIGNED NOT NULL COMMENT 'Bitmask 0-127 (L=1, M=2, X=4...)',
  start_time    TIME NOT NULL,
  end_time      TIME NOT NULL,
  is_active     BOOLEAN DEFAULT TRUE
) ENGINE=InnoDB;
```

**`planner_schedule_blocks`** — calendario generado por la IA (la verdad operativa del día)
```sql
CREATE TABLE planner_schedule_blocks (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  goal_id         INT UNSIGNED NULL,
  title           VARCHAR(255) NOT NULL,
  description     TEXT,
  block_type      ENUM('deep_work','admin','rest','meal','exercise','review') NOT NULL,
  scheduled_date  DATE NOT NULL,
  start_at        DATETIME NOT NULL,
  end_at          DATETIME NOT NULL,
  status          ENUM('pending','in_progress','done','postponed','skipped') DEFAULT 'pending',
  generated_by    ENUM('ai','manual','recalc') NOT NULL,
  ai_log_id       INT UNSIGNED NULL,
  created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_date_status (scheduled_date, status),
  INDEX idx_start (start_at),
  FOREIGN KEY (goal_id) REFERENCES planner_goals(id) ON DELETE SET NULL
) ENGINE=InnoDB;
```

**`planner_postpone_log`** — bitácora de aplazamientos (combustible del check-in)
```sql
CREATE TABLE planner_postpone_log (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  block_id        INT UNSIGNED NOT NULL,
  postponed_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
  reason          VARCHAR(255) NULL,
  reschedule_to   DATETIME NULL,
  FOREIGN KEY (block_id) REFERENCES planner_schedule_blocks(id) ON DELETE CASCADE
) ENGINE=InnoDB;
```

**`planner_checkins`** — retrospectivas diarias
```sql
CREATE TABLE planner_checkins (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  checkin_date  DATE NOT NULL UNIQUE,
  mood          TINYINT UNSIGNED COMMENT '1-5',
  energy        TINYINT UNSIGNED COMMENT '1-5',
  notes         TEXT,
  ai_summary    TEXT,
  load_factor   DECIMAL(3,2) DEFAULT 1.00 COMMENT 'Multiplicador aplicado a próximos días',
  created_at    DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
```

**`planner_ai_logs`** — auditoría de cada llamada a la API
```sql
CREATE TABLE planner_ai_logs (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  endpoint      VARCHAR(64) NOT NULL,
  model         VARCHAR(64) NOT NULL,
  prompt_hash   CHAR(64) NOT NULL,
  request_json  JSON NOT NULL,
  response_json JSON NULL,
  tokens_in     INT UNSIGNED,
  tokens_out    INT UNSIGNED,
  latency_ms    INT UNSIGNED,
  status        ENUM('ok','validation_failed','api_error','retry') NOT NULL,
  created_at    DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
```

---

#### 7.3 Arquitectura de prompts

> Estrategia: **system prompt fijo y estricto** + **user message dinámico con contexto**. El modelo SOLO debe devolver JSON validable contra un esquema. Si no valida, reintento con mensaje de error y, si vuelve a fallar, fallback al calendario del día anterior.

**System prompt (borrador, fijo en `src/Modules/Planner/Prompts/scheduler.system.txt`):**

```
Eres un planificador personal minimalista. Tu única función es generar un horario
diario en formato JSON estricto, optimizado para combatir la procrastinación.

Reglas inviolables:
1. Devuelves EXCLUSIVAMENTE un objeto JSON válido. Sin texto antes o después,
   sin markdown, sin explicaciones.
2. Máximo 6 bloques de trabajo profundo (deep_work) por día.
3. Cada bloque de deep_work dura entre 45 y 90 minutos.
4. Tras cada deep_work debe haber un bloque rest de mínimo 10 minutos.
5. Respeta TODAS las constraints recibidas como ventanas inviolables.
6. Si el usuario tiene load_factor < 1.0, reduce proporcionalmente el número
   de bloques de deep_work, NUNCA su duración mínima.
7. No solapes bloques. Las horas son en formato 24h ISO 8601.
8. Los títulos son accionables: empiezan con verbo en infinitivo.
9. Las descripciones son una sola frase, máximo 140 caracteres.

Esquema de salida (obligatorio):
{
  "date": "YYYY-MM-DD",
  "blocks": [
    {
      "title": "string",
      "description": "string",
      "block_type": "deep_work|admin|rest|meal|exercise|review",
      "goal_id": number|null,
      "start_at": "YYYY-MM-DDTHH:MM:00",
      "end_at": "YYYY-MM-DDTHH:MM:00"
    }
  ],
  "rationale": "string (máx 200 chars, explicación interna)"
}
```

**User message (dinámico, construido por `PlannerAIService::buildUserMessage()`):**

```json
{
  "target_date": "2026-04-08",
  "active_goals": [
    { "id": 1, "title": "...", "priority": 1, "horizon_weeks": 8 }
  ],
  "constraints": [
    { "type": "sleep",       "start": "00:00", "end": "07:30" },
    { "type": "meal",        "start": "14:00", "end": "15:00" },
    { "type": "fixed_event", "start": "11:00", "end": "12:00", "label": "Reunión cliente" }
  ],
  "recent_postponements": [
    { "block_title": "Escribir case study", "postponed_at": "2026-04-07T16:00:00" }
  ],
  "load_factor": 0.85,
  "last_checkin_summary": "Ayer reportó energía 2/5, evitar bloques largos por la mañana"
}
```

**Validación post-respuesta (en PHP):**
1. `json_decode` con `JSON_THROW_ON_ERROR`
2. Validar esquema con `justinrainbow/json-schema` (única dependencia nueva de Composer)
3. Verificar reglas de negocio: no solapes, no exceder 6 deep_work, respetar constraints
4. Si falla → reintento UNA vez con mensaje `"El JSON anterior no cumplía X. Corrígelo."`
5. Si vuelve a fallar → log crítico + fallback al calendario del día anterior

**Prompt del check-in (`checkin.system.txt`, separado):**
- Recibe la lista de aplazamientos del día y las tareas completadas
- Devuelve JSON con: `{ "questions": [...], "suggested_load_factor": number, "tone": "neutral|gentle|firm" }`
- El tono nunca es punitivo; el sistema está diseñado para ser un compañero, no un capataz

---

#### 7.4 Decisiones tomadas (2026-04-07)

| Pregunta | Decisión |
|---|---|
| Stack | ✅ PHP 8.x + MySQL 8 sobre el MVC propio existente |
| Proveedor IA | ✅ Anthropic Claude Sonnet 4.6 (Martín ya tiene suscripción activa) |
| Canal de notificación | ✅ Bot de Telegram |
| Auth del módulo | ✅ Sesión PHP existente + IP whitelist (uso personal) |
| Multi-usuario | ✅ No — solo Martín. Esquema sin `user_id`, sin lógica de tenancy |

**Estado del diseño:** ✅ APROBADO por Martín el 2026-04-07.
**Siguiente acción:** Comenzar Paso 1 del apartado 7.1 (aislamiento estructural). **No empezar hasta nueva orden.**

---

## Estructura de carpetas

```
martincarmona.com/
├── public/                     # Document root
│   ├── index.php              # Entry point
│   ├── dist/                  # Assets compilados por Vite
│   ├── uploads/               # Imágenes del portfolio
│   └── .htaccess
├── src/
│   ├── Controllers/
│   ├── Models/
│   ├── Views/
│   │   ├── layouts/
│   │   ├── home/
│   │   ├── portfolio/
│   │   ├── blog/
│   │   └── contact/
│   ├── Core/                  # MVC base
│   └── routes.php
├── resources/
│   ├── css/
│   │   └── app.css           # Entry point Tailwind
│   └── js/
│       ├── app.js            # Entry point JS
│       ├── animations.js     # GSAP + ScrollTrigger
│       ├── scroll.js         # Lenis
│       └── components/       # Alpine components
├── database/
│   ├── migrations/
│   └── seeds/
├── case-studies/              # Markdown de cada proyecto
├── deploy.php                 # GitHub webhook
├── vite.config.js
├── tailwind.config.js
├── composer.json
└── package.json
```

---

## Criterios de calidad

Antes de publicar, cada sección debe superar estas preguntas:

1. **¿Se entiende en 5 segundos qué hace Martín y para quién?**
2. **¿Hay al menos un proyecto que un recruiter técnico pueda leer en profundidad?**
3. **¿El diseño refuerza el mensaje o compite con él?**
4. **¿Hay alguna frase que suene a LinkedIn genérico?** (eliminar)
5. **¿El CV es descargable y encaja con lo que dice la web?**

---

## Principios de diseño

- **Espacio como elemento de diseño:** el vacío no es ausencia, es estructura
- **Una cosa por pantalla en mobile:** el usuario no hace scroll para encontrar valor
- **Tipografía > imágenes decorativas:** las palabras correctas valen más que un hero genérico
- **Contexto antes que skill:** "construí X para resolver Y" gana a "sé PHP y JavaScript"
- **El código es visible:** enlace a GitHub en cada proyecto técnico, sin miedo
- **Animaciones con propósito:** no abusar, cada animación debe aportar a la experiencia
- **Performance:** Hostinger no es el hosting más potente, optimizar todo lo posible
- **Iterativo:** lanzar una v1 funcional y mejorar con el tiempo

---

## Comandos de desarrollo

```bash
# Dependencias
composer install
npm install

# Desarrollo local
npm run dev

# Build para producción
npm run build
```

---

## Estado actual

| Fase | Estado | Siguiente acción |
|---|---|---|
| 1. Narrativa | 🔲 Pendiente | Sesión con `personal-brand-strategist` |
| 2. Contenido y case studies | 🔲 Pendiente | Inventariar proyectos disponibles |
| 3. Maquetación web | 🟡 Boilerplate listo | Esperando Fase 1 y 2 |
| 4. Funcionalidad dinámica | 🔲 Pendiente | Esperando Fase 3 |
| 5. Blog | 🔲 Pendiente (opcional) | — |
| 6. Lanzamiento | 🔲 Pendiente | — |
| 7. Mindful AI Planner | 🟢 Diseño aprobado (2026-04-07) | Pendiente: arrancar Paso 1 (aislamiento estructural) |
| **Placeholder** | ✅ Desplegado | Activo en producción |
| **Deploy webhook** | ✅ Configurado | GitHub → Hostinger automático |

---

*Última actualización: Abril 2026*
