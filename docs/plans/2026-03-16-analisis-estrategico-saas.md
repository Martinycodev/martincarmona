# Plan Estratégico — De uso personal a SaaS multiusuario

> **Fecha:** 16 de marzo de 2026
> **Estado:** Planificación
> **Contexto:** Análisis completo del proyecto tras completar fases 1-11 del ROADMAP

---

## Resumen ejecutivo

La aplicación está **operativa en producción** con 26 controladores, 9+ modelos, sistema multi-rol, PWA, accesibilidad y UX móvil completados. La seguridad es sólida (queries parametrizadas, CSRF con `hash_equals`, sesiones con regeneración automática, output escaping). El objetivo es consolidar la app para uso personal y preparar el camino hacia SaaS multiusuario.

---

## Bloque 1 — Mejoras técnicas inmediatas (uso personal)

Ordenadas de mayor a menor impacto. Todas son independientes entre sí.

---

### 1.1 Rate Limiting en login

- **Problema:** No existe throttling en intentos de login. Un atacante puede probar contraseñas sin límite.
- **Esfuerzo:** Bajo

**Pasos:**
1. Crear tabla `login_attempts`:
   ```sql
   CREATE TABLE login_attempts (
       id INT AUTO_INCREMENT PRIMARY KEY,
       ip VARCHAR(45) NOT NULL,
       email VARCHAR(255) NOT NULL,
       attempted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
       INDEX idx_ip_time (ip, attempted_at),
       INDEX idx_email_time (email, attempted_at)
   );
   ```
2. En `app/Controllers/AuthController.php`, antes de `password_verify()`:
   - Contar intentos en los últimos 15 minutos para esa IP
   - Si > 5 intentos → responder 429 + loguear en `security.log`
3. Tras login exitoso → limpiar intentos de esa IP/email
4. Cron o limpieza periódica de registros > 24h

**Archivos afectados:**
- `app/Controllers/AuthController.php`
- Migration nueva para `login_attempts`

---

### 1.2 `.htaccess` en directorio de uploads

- **Problema:** Si alguien sube un archivo `.php` disfrazado, Apache podría ejecutarlo en `public/uploads/`.
- **Esfuerzo:** Bajo

**Pasos:**
1. Crear `public/uploads/.htaccess`:
   ```apache
   php_flag engine off
   <FilesMatch "\.php$">
       Deny from all
   </FilesMatch>
   ```
2. Verificar que los controllers que manejan uploads validan MIME type con `finfo` (ya implementado en la mayoría)

**Archivos afectados:**
- `public/uploads/.htaccess` (nuevo)

---

### 1.3 Exportación CSV de datos clave

- **Problema:** No hay forma de sacar datos para análisis externo o respaldo legible. Pendiente en ROADMAP.
- **Esfuerzo:** Medio

**Pasos:**
1. Crear `app/Controllers/ExportController.php` con método genérico:
   ```php
   protected function exportCsv(array $headers, array $rows, string $filename): void {
       header('Content-Type: text/csv; charset=utf-8');
       header('Content-Disposition: attachment; filename=' . $filename);
       $output = fopen('php://output', 'w');
       fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8 para Excel
       fputcsv($output, $headers, ';');
       foreach ($rows as $row) {
           fputcsv($output, $row, ';');
       }
       fclose($output);
       exit;
   }
   ```
2. Métodos específicos: `exportTareas()`, `exportGastos()`, `exportDeudaMensual($id)`
3. Rutas en `routes/web.php`:
   - `GET /exportar/tareas`
   - `GET /exportar/gastos`
   - `GET /exportar/deuda/{id}`
4. Botones de descarga en las vistas correspondientes

**Archivos afectados:**
- `app/Controllers/ExportController.php` (nuevo)
- `routes/web.php`
- Vistas de tareas, economía y trabajadores (añadir botón)

---

### 1.4 PDF de balance mensual por trabajador

- **Problema:** Se calcula manualmente la cuenta de cada trabajador para pagarle.
- **Esfuerzo:** Medio

**Pasos:**
1. `composer require dompdf/dompdf`
2. Crear vista HTML: `app/Views/reportes/balance-trabajador-pdf.php`
   - Cabecera: nombre trabajador, mes, año
   - Tabla: tareas realizadas, horas, precio/hora, subtotal
   - Pie: total bruto, pagos realizados, deuda pendiente
3. En `ReportesController.php`, método `balancePdf($id)`:
   - Recibir `?mes=3&year=2026` por GET
   - Consultar tareas + tarea_trabajadores + tarea_trabajos del trabajador en ese mes
   - Renderizar vista → Dompdf → stream PDF
4. Ruta: `GET /reportes/balance-pdf/{id}`
5. Botón "Descargar PDF" en vista detalle del trabajador

**Archivos afectados:**
- `composer.json` (nueva dependencia)
- `app/Controllers/ReportesController.php`
- `app/Views/reportes/balance-trabajador-pdf.php` (nuevo)
- `routes/web.php`
- Vista detalle trabajador (botón)

---

### 1.5 Gráficos de productividad por parcela

- **Problema:** Los datos de productividad existen en BD pero no se visualizan gráficamente.
- **Esfuerzo:** Medio

**Pasos:**
1. En `ReportesController.php`, nuevo endpoint JSON:
   ```php
   public function productividadParcelaData() {
       // Query: horas y tareas por parcela agrupadas por mes (últimos 12 meses)
       $this->json(['labels' => [...], 'datasets' => [...]]);
   }
   ```
2. Ruta: `GET /reportes/productividad-parcela-data`
3. En vista de reportes, añadir `<canvas id="chart-productividad-parcela">`
4. JS: fetch al endpoint → Chart.js bar chart con colores del tema (`#4caf50`, `#2196f3`)

**Archivos afectados:**
- `app/Controllers/ReportesController.php`
- `app/Views/reportes/index.php`
- `routes/web.php`

---

### 1.6 Docker para desarrollo reproducible

- **Problema:** Dependencia de XAMPP local. Imposible replicar el entorno.
- **Esfuerzo:** Medio

**Pasos:**
1. Crear `Dockerfile`:
   ```dockerfile
   FROM php:8.2-apache
   RUN docker-php-ext-install mysqli
   RUN a2enmod rewrite
   COPY . /var/www/html/
   ```
2. Crear `docker-compose.yml`:
   ```yaml
   services:
     web:
       build: .
       ports: ["8080:80"]
       volumes: [".:/var/www/html"]
     db:
       image: mysql:8.0
       environment:
         MYSQL_DATABASE: u873002419_campo
         MYSQL_ROOT_PASSWORD: root
       volumes: ["db_data:/var/lib/mysql"]
   volumes:
     db_data:
   ```
3. Crear `.env.docker` con credenciales del contenedor
4. Documentar en README: `docker-compose up -d`

**Archivos afectados:**
- `Dockerfile` (nuevo)
- `docker-compose.yml` (nuevo)
- `.env.docker` (nuevo)

---

### 1.7 GitHub Actions para tests automáticos

- **Problema:** PHPUnit no se ejecuta automáticamente. Se pueden romper cosas sin detectarlo.
- **Esfuerzo:** Bajo-Medio

**Pasos:**
1. Crear `.github/workflows/tests.yml`:
   ```yaml
   name: Tests
   on: [push, pull_request]
   jobs:
     test:
       runs-on: ubuntu-latest
       services:
         mysql:
           image: mysql:8.0
           env:
             MYSQL_DATABASE: test_campo
             MYSQL_ROOT_PASSWORD: root
           ports: ["3306:3306"]
       steps:
         - uses: actions/checkout@v4
         - uses: shivammathur/setup-php@v2
           with: { php-version: '8.2', extensions: mysqli }
         - run: composer install --no-progress
         - run: cp .env.testing .env
         - run: vendor/bin/phpunit
   ```
2. Crear `.env.testing` con credenciales del service container
3. Badge en README (opcional)

**Archivos afectados:**
- `.github/workflows/tests.yml` (nuevo)
- `.env.testing` (nuevo)

---

## Bloque 2 — Deuda técnica y riesgos para SaaS

### Riesgos críticos

| # | Riesgo | Ubicación | Impacto SaaS | Severidad |
|---|--------|-----------|--------------|-----------|
| 1 | **Filtro `id_user` manual en cada query** | Todos los modelos (`Tarea.php`, `Movimiento.php`, etc.) | Si un desarrollador olvida `WHERE id_user = ?` en una sola query, un usuario ve datos de otro. Vector #1 de data leaks. | **Crítico** |
| 2 | **Singleton de BD sin tenant isolation** | `config/database.php` | Una sola conexión compartida sin filtro automático por tenant. | **Crítico** |
| 3 | **Sin sistema de migrations** | BD gestionada manualmente | Cada deploy debe aplicar cambios de schema automáticamente. Sin migrations es manual y propenso a errores. | **Alto** |
| 4 | **Roles hardcodeados** | `BaseController.php` — `requireEmpresa()`, `requireEmpresaOAdmin()` | SaaS necesita roles configurables por organización. | **Alto** |
| 5 | **Sin rate limiting** | `AuthController.php` | Con múltiples usuarios, ataques de fuerza bruta contra cualquier cuenta. | **Alto** |
| 6 | **Sin API REST** | `routes/web.php` | Todo es HTML+AJAX. SaaS necesita API para integraciones, apps móviles, webhooks. | **Medio** |
| 7 | **Modelos con `mysqli` directo** | Todos los modelos | Sin capa de abstracción. Cambiar de BD o añadir caching requiere reescribir cada modelo. | **Medio** |
| 8 | **CSS global monolítico** | `public/css/styles.css` | Al crecer, conflictos de selectores y dificultad de mantenimiento. | **Bajo** |
| 9 | **Credenciales vestigiales en config.php** | `config/config.php` | Aunque `.env` se usa, hay credenciales hardcodeadas residuales. | **Bajo** |

### Deuda técnica menor

- **Tests limitados:** Solo `Validator` y `Router` tienen cobertura. Modelos y controllers sin tests.
- **Sin cache:** Cada petición consulta la BD. Con 50+ tenants el rendimiento caerá.
- **Session hijacking:** No se valida IP/User-Agent en restauración de sesión (mitigado por httponly + HTTPS).

---

## Bloque 3 — Hoja de ruta hacia SaaS multiusuario

### Fase A — Cimientos técnicos

> **Prerequisito:** Completar antes de abrir a cualquier usuario externo.

**Construir:**

1. **Sistema de migrations**
   - Carpeta `database/migrations/` con archivos numerados: `001_create_usuarios.sql`, `002_add_login_attempts.sql`...
   - Script PHP `migrate.php` que lea la tabla `migrations`, aplique pendientes y registre
   - Esfuerzo: Medio

2. **Tenant isolation automática**
   - Clase base `Model` con método `scopedQuery()` que inyecte `WHERE id_user = ?` automáticamente
   - Todos los modelos heredan de esta clase
   - Test obligatorio: verificar que un usuario NO puede acceder a datos de otro
   - Esfuerzo: Alto

3. **QueryBuilder ligero**
   - Clase sobre `mysqli` que soporte encadenamiento: `->table('tareas')->where('id_user', $id)->get()`
   - No un ORM completo, solo protección automática y legibilidad
   - Esfuerzo: Alto

4. **Rate limiting** (ver mejora 1.1)
5. **Tests de integración por tenant** — Al menos un test por modelo CRUD verificando aislamiento de datos

**Decisiones arquitectónicas:**

| Decisión | Opciones | Recomendación |
|----------|----------|---------------|
| Estrategia multi-tenant | BD compartida con `id_user` (actual) vs schema por tenant | BD compartida — menos infraestructura, ya implementado parcialmente |
| Migrar de `mysqli` a PDO | PDO (más portable, named params) vs seguir con mysqli | PDO — hacerlo ahora antes de que haya más código |
| Abstracción de BD | QueryBuilder propio vs micro-ORM (Medoo, etc.) | QueryBuilder propio — mantiene filosofía "sin frameworks" |

**No tocar:** UI, nuevas features funcionales, optimizaciones de rendimiento.

---

### Fase B — Registro, onboarding y billing

> **Prerequisito:** Fase A completada y validada con tests.

**Construir:**

1. **Registro público**
   - Formulario de alta con verificación de email (token temporal en tabla `email_verifications`)
   - Contraseña con requisitos mínimos (8 chars, mayúscula, número)
   - Esfuerzo: Medio

2. **Onboarding wizard**
   - Primera configuración: nombre explotación, ubicación (lat/lon para meteo), parcelas iniciales
   - Wizard de 3 pasos con persistencia parcial
   - Esfuerzo: Medio

3. **Planes y billing**
   - Tabla `planes` (free, pro, enterprise) + `suscripciones` (tenant_id, plan_id, stripe_id, estado, fecha_fin)
   - Integración con Stripe Checkout
   - Middleware `requirePlan('pro')` para features premium
   - Esfuerzo: Alto

4. **Panel admin global**
   - Dashboard para el operador: usuarios activos, métricas de uso, soporte
   - Separado de admin de tenant (gestión de usuarios dentro de la organización)
   - Esfuerzo: Medio

5. **RGPD y legal**
   - Consentimiento explícito al registrarse
   - Endpoint de exportación de datos personales
   - Endpoint de eliminación de cuenta (derecho al olvido)
   - Esfuerzo: Medio

**Decisiones arquitectónicas:**

| Decisión | Opciones | Recomendación |
|----------|----------|---------------|
| Modelo de precios | Por parcelas / por trabajadores / flat fee | Flat fee con límites por plan (ej: free=5 parcelas, pro=ilimitado) |
| Subdominios | `finca.tuapp.com` vs `tuapp.com/finca` | Ruta primero — más simple; subdominios después si hay demanda |
| Email service | SMTP propio vs servicio (Resend, Mailgun) | Servicio externo — fiabilidad y deliverability |

**No tocar:** Módulos funcionales (tareas, economía, etc.) — ya funcionan correctamente.

---

### Fase C — Hardening y escala

> **Prerequisito:** Fase B con al menos 5-10 usuarios beta validando.

**Construir:**

1. **API REST versionada**
   - Endpoints `/api/v1/tareas`, `/api/v1/parcelas`, etc.
   - Autenticación JWT o API keys por tenant
   - Rate limiting por tenant (ej: 1000 req/hora plan free, 10000 pro)
   - Documentación con OpenAPI/Swagger
   - Esfuerzo: Alto

2. **Cache layer**
   - Redis o APCu para queries frecuentes (dashboard, reportes)
   - Invalidación en escritura
   - Esfuerzo: Medio

3. **Queue system**
   - Para tareas pesadas: generación de PDFs, exportaciones CSV, emails
   - Tabla `jobs` con worker en cron o supervisor
   - Esfuerzo: Medio

4. **Backups automáticos por tenant**
   - Cada tenant puede exportar todos sus datos (RGPD)
   - Backup general diario automatizado
   - Esfuerzo: Medio

5. **Monitoring y alertas**
   - Métricas de rendimiento (tiempo de respuesta, queries lentas)
   - Alertas de errores (integración con Sentry o similar)
   - Uptime checks
   - Esfuerzo: Medio

**Decisiones arquitectónicas:**

| Decisión | Opciones | Recomendación |
|----------|----------|---------------|
| Hosting | Hostinger compartido vs VPS vs cloud | VPS (DigitalOcean/Hetzner) — control total, coste predecible |
| CDN | Sin CDN vs Cloudflare vs AWS CloudFront | Cloudflare free — DNS + CDN + SSL + protección DDoS |
| Base de datos | MySQL local vs managed (PlanetScale, RDS) | Managed cuando > 50 tenants — backups automáticos, réplicas |

**No tocar:** Reescrituras de arquitectura (migrar a Laravel/Symfony). Solo si el volumen lo justifica.

---

### Fase D — Crecimiento y diferenciación

> **Prerequisito:** Fase C estabilizada con 50+ usuarios activos.

**Construir:**

1. **App móvil nativa** (o PWA mejorada avanzada) consumiendo la API REST
2. **Integraciones:**
   - SIGPAC (importar parcelas automáticamente)
   - Sensores IoT de riego
   - Estaciones meteorológicas locales
   - Cuaderno de explotación digital (normativa EU)
3. **Marketplace de módulos** — Cada tenant activa/desactiva módulos (fitosanitarios, campañas, riego)
4. **Multi-idioma** — i18n si se quiere expandir fuera de España
5. **White-label** — Permitir personalización de marca por tenant (logo, colores)

---

## Bloque 4 — Skills aplicables del directorio /skills

### Mapa de skills por mejora

| Mejora / Fase | Skills a aplicar | Orden de ejecución |
|---------------|-----------------|-------------------|
| Rate limiting login | `systematic-debugging` + `test-driven-development` | TDD → implementar → verificar |
| `.htaccess` uploads | `verification-before-completion` | Verificar que PHP no se ejecuta tras el cambio |
| Exportación CSV | `writing-plans` → `executing-plans` | Plan primero, ejecutar en batches |
| PDF balance | `writing-plans` → `executing-plans` | Plan primero, ejecutar en batches |
| Gráficos Chart.js | `brainstorming` → `frontend-design` | Diseñar visualización antes de implementar |
| Docker + CI/CD | `writing-plans` | Plan con pasos exactos |
| Tenant isolation (Fase A) | `brainstorming` → `writing-plans` → `subagent-driven-development` | Diseño → plan → ejecución con subagentes |
| Migrations (Fase A) | `writing-plans` → `subagent-driven-development` | Plan → ejecución con subagentes |
| Registro + Onboarding (Fase B) | `brainstorming` → `frontend-design` → `writing-plans` | Diseño UX → plan → implementar |
| API REST (Fase C) | `brainstorming` → `writing-plans` → `subagent-driven-development` → `requesting-code-review` | Diseño → plan → implementar → review |

### Flujo recomendado para cualquier mejora

```
brainstorming (explorar enfoques, decidir diseño)
  → writing-plans (plan con tareas atómicas de 2-5 min)
    → using-git-worktrees (rama aislada)
      → subagent-driven-development (ejecución)
        → test-driven-development (por cada tarea)
        → requesting-code-review (tras cada tarea)
      → verification-before-completion (antes de cerrar)
    → finishing-a-development-branch (merge/PR)
```

### Skills de proceso (usar siempre)

- **`verification-before-completion`** — Antes de marcar CUALQUIER tarea como completada
- **`systematic-debugging`** — Ante CUALQUIER bug o comportamiento inesperado
- **`receiving-code-review`** — Evaluar feedback técnicamente, no performativamente
- **`test-driven-development`** — Red-Green-Refactor en toda nueva funcionalidad

---

## Prioridad de ejecución global

| Orden | Mejora | Tipo | Esfuerzo | Prerequisitos |
|-------|--------|------|----------|---------------|
| 1 | Rate limiting login | Seguridad | Bajo | Ninguno |
| 2 | `.htaccess` uploads | Seguridad | Bajo | Ninguno |
| 3 | Exportación CSV | Feature | Medio | Ninguno |
| 4 | PDF balance trabajador | Feature | Medio | Composer (dompdf) |
| 5 | Gráficos productividad | Feature | Medio | Ninguno |
| 6 | GitHub Actions CI | DevOps | Bajo-Medio | Ninguno |
| 7 | Docker | DevOps | Medio | Ninguno |
| 8 | Sistema de migrations | Fase A - SaaS | Medio | Ninguno |
| 9 | Tenant isolation automática | Fase A - SaaS | Alto | Migrations (#8) |
| 10 | QueryBuilder ligero | Fase A - SaaS | Alto | Tenant isolation (#9) |
| 11 | Tests de integración tenant | Fase A - SaaS | Medio | Tenant isolation (#9) |
| 12 | Registro + email verification | Fase B - SaaS | Medio | Fase A completa |
| 13 | Onboarding wizard | Fase B - SaaS | Medio | Registro (#12) |
| 14 | Planes + Stripe | Fase B - SaaS | Alto | Registro (#12) |
| 15 | API REST | Fase C - SaaS | Alto | Fase B completa |

---

## Estado actual de seguridad (referencia)

| Categoría | Estado | Notas |
|-----------|--------|-------|
| SQL Injection | ✅ Seguro | Queries parametrizadas en todos los modelos |
| XSS | ✅ Seguro | `htmlspecialchars()` en todas las vistas |
| CSRF | ✅ Seguro | `hash_equals()`, meta tag, auto-inject en fetch |
| Autenticación | ✅ Seguro | `password_hash()` + `password_verify()` + session regen |
| Autorización | ✅ Seguro | Middleware por rol en `BaseController` |
| Sesiones | ✅ Seguro | httponly, samesite, regeneración cada 30 min, timeout 2h |
| Uploads | ⚠️ Mejorable | MIME validation OK, falta `.htaccess` anti-PHP |
| Rate Limiting | ❌ Ausente | Prioritario implementar |
| Error Handling | ✅ Seguro | No expone detalles en producción |
| Logging | ✅ Seguro | Monolog con `app.log` + `security.log` |
