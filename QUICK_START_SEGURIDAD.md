# ⚡ Quick Start - Seguridad Crítica

*Guía rápida para resolver los problemas de seguridad más urgentes*

---

## 🎯 **OBJETIVO: Hacer la app segura en 1 día**

Este documento te guía paso a paso para implementar las medidas de seguridad críticas detectadas en la revisión técnica.

**⏱️ Tiempo estimado:** 4-6 horas
**⚠️ Importancia:** CRÍTICA - No usar en producción sin completar esto

---

## 📋 **CHECKLIST RÁPIDO**

```
[ ] Paso 1: Instalar Composer y phpdotenv (10 min)
[ ] Paso 2: Crear archivo .env y mover credenciales (15 min)
[ ] Paso 3: Actualizar database.php para usar .env (10 min)
[ ] Paso 4: Implementar CSRF Protection (45 min)
[ ] Paso 5: Session Hardening (30 min)
[ ] Paso 6: Input Validation (60 min)
[ ] Paso 7: Testing de seguridad (30 min)
```

---

## 🚀 **PASO 1: Instalar Composer y Dependencias**

### 1.1 Verificar si tienes Composer

```bash
# En tu terminal (Git Bash en Windows)
composer --version
```

Si no lo tienes, descárgalo de: https://getcomposer.org/download/

### 1.2 Crear composer.json

```bash
cd c:/xampp/htdocs/martincarmona
composer init --no-interaction
```

### 1.3 Instalar phpdotenv

```bash
composer require vlucas/phpdotenv
```

**✅ Resultado esperado:** Carpeta `vendor/` creada con las dependencias

---

## 🔐 **PASO 2: Crear Archivo .env**

### 2.1 Crear .env en la raíz del proyecto

```bash
# Crear archivo
touch .env
```

### 2.2 Contenido del .env

Copia esto en el archivo `.env`:

```env
# Aplicación
APP_NAME="Sistema Gestión Agrícola"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost/martincarmona

# Base de datos - LOCAL
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=u873002419_campo

# Base de datos - PRODUCCIÓN (comentado)
# DB_HOST=srv699.hstgr.io
# DB_USER=u873002419_campo
# DB_PASS=LgBuRjxeYRnEi!8
# DB_NAME=u873002419_campo

# Sesión
SESSION_LIFETIME=3600
SESSION_SECURE=false
SESSION_HTTPONLY=true
SESSION_SAMESITE=Strict
```

### 2.3 Crear .env.example (plantilla para git)

```bash
cp .env .env.example
```

Edita `.env.example` y reemplaza los valores sensibles:

```env
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=nombre_base_datos
```

### 2.4 Actualizar .gitignore

Añade al final del archivo `.gitignore`:

```
# Variables de entorno
.env
.env.backup
vendor/
```

**✅ Resultado esperado:** Archivo `.env` creado con tus credenciales

---

## 🔧 **PASO 3: Actualizar database.php**

Reemplaza **TODO** el contenido de `config/database.php` con esto:

```php
<?php

// Cargar variables de entorno
require_once BASE_PATH . '/vendor/autoload.php';

use Dotenv\Dotenv;

// Solo cargar .env si existe
if (file_exists(BASE_PATH . '/.env')) {
    $dotenv = Dotenv::createImmutable(BASE_PATH);
    $dotenv->load();
}

class Database
{
    private static $connection = null;

    public static function connect()
    {
        // Singleton - una sola conexión
        if (self::$connection !== null) {
            return self::$connection;
        }

        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $user = $_ENV['DB_USER'] ?? 'root';
        $pass = $_ENV['DB_PASS'] ?? '';
        $name = $_ENV['DB_NAME'] ?? 'u873002419_campo';

        self::$connection = new mysqli($host, $user, $pass, $name);

        if (self::$connection->connect_error) {
            error_log("Database connection failed: " . self::$connection->connect_error);

            // En producción, no mostrar detalles del error
            if (($_ENV['APP_ENV'] ?? 'production') === 'production') {
                die('Error de conexión a la base de datos');
            } else {
                die('Database Error: ' . self::$connection->connect_error);
            }
        }

        self::$connection->query("SET NAMES 'utf8mb4'");
        return self::$connection;
    }

    public static function testConnection()
    {
        try {
            $db = self::connect();
            $result = $db->query("SELECT 1 as test");

            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Conexión exitosa a la base de datos'
                ];
            }

            return [
                'success' => false,
                'error' => 'Error en consulta de prueba'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Excepción: ' . $e->getMessage()
            ];
        }
    }
}
```

**✅ Resultado esperado:** La aplicación funciona igual pero ahora usa `.env`

**🧪 Testear:**

```bash
# Verifica que la app sigue funcionando
# Abre en navegador: http://localhost/martincarmona
```

---

## 🛡️ **PASO 4: CSRF Protection**

### 4.1 Crear CsrfMiddleware.php

Crea el archivo `core/CsrfMiddleware.php`:

```php
<?php

namespace Core;

class CsrfMiddleware
{
    /**
     * Genera un token CSRF único para la sesión
     */
    public static function generateToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Valida el token CSRF enviado
     */
    public static function validateToken(?string $token): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['csrf_token']) || $token === null) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Obtiene el input HTML del token
     */
    public static function getTokenField(): string
    {
        $token = self::generateToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Obtiene el token para meta tag
     */
    public static function getMetaTag(): string
    {
        $token = self::generateToken();
        return '<meta name="csrf-token" content="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
}
```

### 4.2 Actualizar BaseController.php

Añade este método a `app/Controllers/BaseController.php`:

```php
protected function validateCsrf(): bool
{
    // Obtener token de POST o header AJAX
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

    if (!Core\CsrfMiddleware::validateToken($token)) {
        http_response_code(403);

        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Token CSRF inválido']);
        } else {
            echo '403 Forbidden - Token CSRF inválido';
        }

        exit;
    }

    return true;
}
```

### 4.3 Proteger Controladores POST

En **cada método POST** de tus controladores, añade `$this->validateCsrf();` al inicio:

```php
// Ejemplo en TareasController.php
public function crear()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // AÑADIR ESTA LÍNEA
        $this->validateCsrf();

        // ... resto del código
    }
}
```

**Archivos a actualizar:**
- `app/Controllers/TareasController.php` (crear, actualizar, eliminar)
- `app/Controllers/TrabajadoresController.php` (crear, actualizar, eliminar)
- `app/Controllers/ParcelasController.php` (crear, actualizar, eliminar)
- `app/Controllers/AuthController.php` (login)
- Todos los demás controladores con métodos POST

### 4.4 Actualizar Formularios HTML

En **todos tus formularios**, añade el campo CSRF:

```php
<!-- Ejemplo: app/Views/tareas/index.php -->
<form method="POST" action="<?= APP_BASE_PATH ?>/tareas/crear">
    <?= Core\CsrfMiddleware::getTokenField() ?>

    <input name="fecha" type="date" required>
    <input name="descripcion" required>
    <button type="submit">Crear</button>
</form>
```

### 4.5 Actualizar Peticiones AJAX

En `public/js/modal-functions.js` y otros archivos JS, añade el token CSRF:

```javascript
// Al inicio del archivo, después de las funciones helper
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

// En cada fetch POST
async function crearTarea(tareaData) {
    const response = await fetch(buildUrl('/tareas/crear'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken()  // ✅ AÑADIR ESTO
        },
        body: JSON.stringify(tareaData)
    });

    return response.json();
}
```

### 4.6 Añadir Meta Tag en Layout

En `app/Views/layouts/main.php` (o donde tengas tu <head>):

```php
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= Core\CsrfMiddleware::getMetaTag() ?>
    <!-- resto del head -->
</head>
```

**✅ Resultado esperado:** Todos los formularios POST están protegidos contra CSRF

**🧪 Testear:**

1. Intenta crear una tarea desde el formulario → debería funcionar
2. Intenta enviar POST sin token (con Postman) → debería dar error 403
3. Verifica que AJAX sigue funcionando

---

## 🔒 **PASO 5: Session Hardening**

### 5.1 Crear SessionConfig.php

Crea el archivo `config/session.php`:

```php
<?php

class SessionConfig
{
    public static function configure(): void
    {
        // No iniciar si ya está iniciada
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        // Configurar parámetros ANTES de session_start()
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_secure', $_ENV['SESSION_SECURE'] ?? '0');
        ini_set('session.cookie_samesite', $_ENV['SESSION_SAMESITE'] ?? 'Strict');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');

        // Configurar cookies
        session_set_cookie_params([
            'lifetime' => 0,  // Hasta cerrar navegador
            'path' => '/',
            'domain' => '',
            'secure' => (bool)($_ENV['SESSION_SECURE'] ?? false),
            'httponly' => true,
            'samesite' => $_ENV['SESSION_SAMESITE'] ?? 'Strict'
        ]);

        session_start();

        // Regenerar ID periódicamente
        self::regenerateIdPeriodically();

        // Timeout de inactividad
        self::checkTimeout();
    }

    private static function regenerateIdPeriodically(): void
    {
        if (!isset($_SESSION['last_regeneration'])) {
            $_SESSION['last_regeneration'] = time();
        }

        // Regenerar cada 30 minutos
        if (time() - $_SESSION['last_regeneration'] > 1800) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }

    private static function checkTimeout(): void
    {
        $timeout = (int)($_ENV['SESSION_LIFETIME'] ?? 3600);

        if (isset($_SESSION['last_activity'])) {
            $inactive = time() - $_SESSION['last_activity'];

            if ($inactive > $timeout) {
                session_unset();
                session_destroy();
                header('Location: ' . ($_ENV['APP_URL'] ?? '/') . '?session_expired=1');
                exit;
            }
        }

        $_SESSION['last_activity'] = time();
    }

    public static function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function login(int $userId, string $userName): void
    {
        // Regenerar ID al hacer login
        session_regenerate_id(true);

        $_SESSION['user_id'] = $userId;
        $_SESSION['user_name'] = $userName;
        $_SESSION['last_activity'] = time();
        $_SESSION['last_regeneration'] = time();
    }

    public static function logout(): void
    {
        $_SESSION = [];

        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        session_destroy();
    }
}
```

### 5.2 Actualizar index.php

En `index.php`, reemplaza las primeras líneas:

```php
<?php

error_reporting(E_ALL);
ini_set('display_errors', $_ENV['APP_DEBUG'] ?? '0');

define('BASE_PATH', __DIR__);

// Cargar .env
require_once BASE_PATH . '/vendor/autoload.php';
use Dotenv\Dotenv;
if (file_exists(BASE_PATH . '/.env')) {
    $dotenv = Dotenv::createImmutable(BASE_PATH);
    $dotenv->load();
}

// Cargar configuración
$config = require_once BASE_PATH . '/config/config.php';
define('APP_BASE_PATH', $config['base_path']);

// CONFIGURAR SESIÓN GLOBALMENTE
require_once BASE_PATH . '/config/session.php';
SessionConfig::configure();

// Cargar autoloader
require_once BASE_PATH . '/core/Autoloader.php';
$autoloader = new Core\Autoloader();
$autoloader->register();
$autoloader->addNamespace('Core', BASE_PATH . '/core');
$autoloader->addNamespace('App', BASE_PATH . '/app');

// ... resto del código
```

### 5.3 Remover session_start() de Controladores

En **TODOS** tus controladores, **ELIMINA** las líneas `session_start();`:

```php
// ANTES (ELIMINAR ESTO)
public function __construct()
{
    session_start();  // ❌ ELIMINAR
    if (!isset($_SESSION['user_id'])) {
        $this->redirect('/');
    }
}

// DESPUÉS (CORRECTO)
public function __construct()
{
    // session_start() ya se llama en index.php
    if (!SessionConfig::isAuthenticated()) {
        $this->redirect('/');
        return;
    }
}
```

**Archivos a actualizar:**
- `app/Controllers/TareasController.php`
- `app/Controllers/TrabajadoresController.php`
- `app/Controllers/ParcelasController.php`
- `app/Controllers/DashboardController.php`
- Todos los demás controladores

### 5.4 Actualizar AuthController

En `app/Controllers/AuthController.php`, usa `SessionConfig::login()`:

```php
public function login()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $this->validateCsrf();

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        // ... validar credenciales

        if ($userValid) {
            // Usar SessionConfig::login() en lugar de setear manualmente
            SessionConfig::login($user['id'], $user['name']);

            header('Location: ' . APP_BASE_PATH . '/dashboard');
            exit;
        }
    }
}
```

**✅ Resultado esperado:**
- Sesión configurada de forma segura
- Timeout de 1 hora de inactividad
- Regeneración automática de ID cada 30 min

**🧪 Testear:**

1. Login → debería funcionar
2. Esperar 1 hora inactivo → debería cerrar sesión automáticamente
3. Verificar cookies en DevTools → debería tener `HttpOnly` y `SameSite=Strict`

---

## ✅ **PASO 6: Input Validation**

### 6.1 Crear Validator.php

Crea el archivo `core/Validator.php`:

```php
<?php

namespace Core;

class Validator
{
    private array $errors = [];

    public function validate(array $data, array $rules): bool
    {
        $this->errors = [];

        foreach ($rules as $field => $ruleSet) {
            $value = $data[$field] ?? null;
            $fieldRules = explode('|', $ruleSet);

            foreach ($fieldRules as $rule) {
                $this->applyRule($field, $value, $rule);
            }
        }

        return empty($this->errors);
    }

    private function applyRule(string $field, $value, string $rule): void
    {
        // required
        if ($rule === 'required' && ($value === null || $value === '')) {
            $this->errors[$field][] = "El campo {$field} es requerido";
            return;
        }

        // Skip validación si el campo está vacío y no es required
        if ($value === null || $value === '') {
            return;
        }

        // numeric
        if ($rule === 'numeric' && !is_numeric($value)) {
            $this->errors[$field][] = "El campo {$field} debe ser numérico";
            return;
        }

        // integer
        if ($rule === 'integer' && !filter_var($value, FILTER_VALIDATE_INT)) {
            $this->errors[$field][] = "El campo {$field} debe ser un número entero";
            return;
        }

        // min:value
        if (preg_match('/^min:(\d+\.?\d*)$/', $rule, $matches)) {
            $min = (float)$matches[1];
            if ((float)$value < $min) {
                $this->errors[$field][] = "El campo {$field} debe ser al menos {$min}";
            }
        }

        // max:value
        if (preg_match('/^max:(\d+\.?\d*)$/', $rule, $matches)) {
            $max = (float)$matches[1];
            if ((float)$value > $max) {
                $this->errors[$field][] = "El campo {$field} no puede exceder {$max}";
            }
        }

        // date
        if ($rule === 'date') {
            $d = \DateTime::createFromFormat('Y-m-d', $value);
            if (!$d || $d->format('Y-m-d') !== $value) {
                $this->errors[$field][] = "El campo {$field} debe ser una fecha válida (Y-m-d)";
            }
        }

        // max_length:value
        if (preg_match('/^max_length:(\d+)$/', $rule, $matches)) {
            $maxLen = (int)$matches[1];
            if (strlen($value) > $maxLen) {
                $this->errors[$field][] = "El campo {$field} no puede exceder {$maxLen} caracteres";
            }
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getFirstError(): ?string
    {
        foreach ($this->errors as $fieldErrors) {
            return $fieldErrors[0] ?? null;
        }
        return null;
    }
}
```

### 6.2 Usar Validador en Controladores

Ejemplo en `app/Controllers/TareasController.php`:

```php
public function crear()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $this->validateCsrf();

        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

        if ($isAjax) {
            header('Content-Type: application/json');
            $input = json_decode(file_get_contents('php://input'), true);
        } else {
            $input = $_POST;
        }

        // ✅ VALIDAR INPUTS
        $validator = new \Core\Validator();
        $isValid = $validator->validate($input, [
            'fecha' => 'required|date',
            'descripcion' => 'required|max_length:500',
            'trabajo' => 'required|integer|min:1',
            'horas' => 'required|numeric|min:0|max:24',
        ]);

        if (!$isValid) {
            http_response_code(422);
            echo json_encode([
                'success' => false,
                'errors' => $validator->getErrors(),
                'message' => $validator->getFirstError()
            ]);
            return;
        }

        // ✅ SANITIZAR INPUTS
        $tareaData = [
            'fecha' => $input['fecha'],
            'descripcion' => htmlspecialchars($input['descripcion'], ENT_QUOTES, 'UTF-8'),
            'trabajo' => (int)$input['trabajo'],
            'horas' => (float)$input['horas']
        ];

        // ... crear tarea
    }
}
```

Aplica el mismo patrón en:
- TrabajadoresController
- ParcelasController
- Todos los métodos POST/PUT

**✅ Resultado esperado:** Inputs validados antes de procesarse

**🧪 Testear:**

1. Enviar formulario con `horas: 999` → debería dar error
2. Enviar formulario con `fecha: 'abc'` → debería dar error
3. Enviar datos válidos → debería funcionar

---

## 🧪 **PASO 7: Testing de Seguridad**

### 7.1 Checklist Manual

```
[ ] ¿Las credenciales ya NO están en database.php?
[ ] ¿El archivo .env existe y tiene las credenciales?
[ ] ¿El archivo .env está en .gitignore?
[ ] ¿Los formularios tienen token CSRF?
[ ] ¿Las peticiones POST validan CSRF?
[ ] ¿La sesión se configura solo una vez en index.php?
[ ] ¿Los controladores YA NO tienen session_start()?
[ ] ¿Los inputs se validan con la clase Validator?
[ ] ¿Los inputs se sanitizan con htmlspecialchars()?
```

### 7.2 Tests con DevTools

```javascript
// En Console del navegador:

// 1. Verificar meta tag CSRF
console.log(document.querySelector('meta[name="csrf-token"]')?.content);
// ✅ Debe mostrar un token de 64 caracteres

// 2. Verificar cookies de sesión
document.cookie
// ✅ Debe incluir PHPSESSID con HttpOnly

// 3. Intentar POST sin CSRF (debería fallar)
fetch('/tareas/crear', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ fecha: '2026-02-15', descripcion: 'test' })
}).then(r => r.json()).then(console.log);
// ❌ Debe dar error 403 "Token CSRF inválido"
```

### 7.3 Verificar con git

```bash
# Asegúrate de que .env NO está trackeado
git status

# NO debe aparecer .env en la lista
# SI debe aparecer .env.example
```

---

## ✅ **VERIFICACIÓN FINAL**

### Antes de continuar, verifica:

1. **✅ .env creado** y credenciales movidas
2. **✅ .env en .gitignore** (no se sube a git)
3. **✅ database.php usa $_ENV** en lugar de credenciales hardcodeadas
4. **✅ CSRF tokens en formularios** HTML y AJAX
5. **✅ validateCsrf() en métodos POST** de controladores
6. **✅ session.php centralizado** en index.php
7. **✅ session_start() ELIMINADO** de controladores
8. **✅ Validator usado en inputs** críticos

### Test de humo:

```bash
# 1. Abrir la app
# http://localhost/martincarmona

# 2. Login
# ✅ Debe funcionar

# 3. Crear tarea
# ✅ Debe funcionar

# 4. Verificar en DevTools > Application > Cookies
# ✅ Debe mostrar HttpOnly = ✓

# 5. Intentar enviar POST sin CSRF
# ❌ Debe dar error 403
```

---

## 🎉 **¡COMPLETADO!**

Tu aplicación ahora tiene:

✅ **Credenciales seguras** en .env
✅ **Protección CSRF** en formularios
✅ **Sesiones endurecidas** con timeout
✅ **Validación de inputs** centralizada

### Próximos pasos:

1. ✅ Commitear cambios (sin .env):
   ```bash
   git add .
   git commit -m "feat: implementar medidas de seguridad críticas (CSRF, sessions, .env)"
   ```

2. ⚡ Continuar con [MEJORAS_TECNICAS.md](MEJORAS_TECNICAS.md) para:
   - Logging profesional
   - Testing con PHPUnit
   - Router mejorado
   - Modernización frontend

---

**📚 Referencias:**
- [MEJORAS_TECNICAS.md](MEJORAS_TECNICAS.md) - Guía completa de modernización
- [Checklist_Objetivos_Pendientes.md](Checklist_Objetivos_Pendientes.md) - Roadmap completo

*Creado: 15 de febrero de 2026*
