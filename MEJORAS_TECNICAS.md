# 🚀 Guía de Mejoras Técnicas - Sistema de Gestión Agrícola

*Roadmap de modernización técnica 2026*

---

## 📋 **Índice**

1. [Seguridad Crítica](#seguridad-crítica)
2. [Modernización Backend](#modernización-backend)
3. [Modernización Frontend](#modernización-frontend)
4. [Base de Datos](#optimización-base-de-datos)
5. [DevOps y Testing](#devops-y-testing)
6. [Stack Recomendado 2026](#stack-recomendado-2026)

---

## 🔴 **SEGURIDAD CRÍTICA**

### 1. Variables de Entorno (.env)

**Problema Actual:**
```php
// config/database.php - LÍNEA 8 - ⚠️ CREDENCIALES EXPUESTAS
public static function connect() {
    $db = new mysqli('srv699.hstgr.io', 'u873002419_campo', 'LgBuRjxeYRnEi!8', 'u873002419_campo');
    return $db;
}
```

**Solución Implementada:**

```bash
# 1. Instalar dependencia
composer require vlucas/phpdotenv
```

```php
// config/database.php (MEJORADO)
<?php
require_once BASE_PATH . '/vendor/autoload.php';

use Dotenv\Dotenv;

// Cargar variables de entorno
$dotenv = Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

class Database
{
    public static function connect()
    {
        $host = $_ENV['DB_HOST'];
        $user = $_ENV['DB_USER'];
        $pass = $_ENV['DB_PASS'];
        $name = $_ENV['DB_NAME'];

        $db = new mysqli($host, $user, $pass, $name);

        if ($db->connect_error) {
            error_log("Database connection failed: " . $db->connect_error);
            throw new Exception("Error de conexión a la base de datos");
        }

        $db->query("SET NAMES 'utf8mb4'");
        return $db;
    }
}
```

```env
# .env (CREAR ESTE ARCHIVO)
APP_NAME="Sistema Gestión Agrícola"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=u873002419_campo

# Para producción
# DB_HOST=srv699.hstgr.io
# DB_USER=u873002419_campo
# DB_PASS=LgBuRjxeYRnEi!8
# DB_NAME=u873002419_campo

SESSION_LIFETIME=3600
SESSION_SECURE=false
SESSION_HTTPONLY=true
```

```env
# .env.example (CREAR PLANTILLA)
APP_NAME="Sistema Gestión Agrícola"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=nombre_base_datos

SESSION_LIFETIME=3600
SESSION_SECURE=false
SESSION_HTTPONLY=true
```

```gitignore
# .gitignore (AÑADIR)
.env
.env.backup
vendor/
node_modules/
```

---

### 2. Protección CSRF

**Problema Actual:**
```php
// Todos los formularios POST son vulnerables a CSRF
<form method="POST" action="/tareas/crear">
    <input name="fecha" type="date">
    <!-- Sin token CSRF -->
</form>
```

**Solución Implementada:**

```php
// core/CsrfMiddleware.php (CREAR)
<?php

namespace Core;

class CsrfMiddleware
{
    /**
     * Genera un token CSRF único para la sesión
     */
    public static function generateToken(): string
    {
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
        if (!isset($_SESSION['csrf_token']) || $token === null) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Regenera el token después de usarlo (one-time token)
     */
    public static function regenerateToken(): void
    {
        unset($_SESSION['csrf_token']);
        self::generateToken();
    }

    /**
     * Obtiene el input HTML del token
     */
    public static function getTokenField(): string
    {
        $token = self::generateToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
}
```

```php
// app/Controllers/BaseController.php (ACTUALIZAR)
<?php

namespace App\Controllers;

use Core\CsrfMiddleware;

class BaseController
{
    protected function validateCsrf(): bool
    {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

        if (!CsrfMiddleware::validateToken($token)) {
            http_response_code(403);
            echo json_encode(['error' => 'Token CSRF inválido']);
            exit;
        }

        return true;
    }

    protected function render($view, $data = [])
    {
        // Añadir token CSRF a todas las vistas
        $data['csrf_token'] = CsrfMiddleware::generateToken();

        // ... resto del código render
    }
}
```

```php
// Ejemplo en TareasController.php
public function crear()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // VALIDAR CSRF ANTES DE PROCESAR
        $this->validateCsrf();

        // ... resto del código
    }
}
```

```html
<!-- app/Views/tareas/index.php (ACTUALIZAR FORMULARIOS) -->
<form method="POST" action="/tareas/crear">
    <?= Core\CsrfMiddleware::getTokenField() ?>
    <input name="fecha" type="date" required>
    <input name="descripcion" required>
    <button type="submit">Crear Tarea</button>
</form>
```

```javascript
// public/js/modal-functions.js (PARA PETICIONES AJAX)
async function crearTarea(tareaData) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    const response = await fetch(buildUrl('/tareas/crear'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify(tareaData)
    });

    return response.json();
}
```

```html
<!-- app/Views/layouts/main.php (AÑADIR META TAG) -->
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="<?= Core\CsrfMiddleware::generateToken() ?>">
    <!-- ... resto -->
</head>
```

---

### 3. Session Hardening

**Problema Actual:**
```php
// Cada controlador llama session_start() sin configuración segura
session_start(); // Sin parámetros de seguridad
```

**Solución Implementada:**

```php
// config/session.php (CREAR)
<?php

class SessionConfig
{
    public static function configure(): void
    {
        // Configurar parámetros de sesión ANTES de session_start()
        ini_set('session.cookie_httponly', '1');  // Previene acceso via JavaScript
        ini_set('session.cookie_secure', $_ENV['SESSION_SECURE'] ?? '0');  // Solo HTTPS en producción
        ini_set('session.cookie_samesite', 'Strict');  // Previene CSRF
        ini_set('session.use_strict_mode', '1');  // Rechaza IDs no inicializados
        ini_set('session.use_only_cookies', '1');  // Solo cookies, no query params
        ini_set('session.cookie_lifetime', '0');  // Expira al cerrar navegador

        // Configurar cookie de sesión
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => (bool)($_ENV['SESSION_SECURE'] ?? false),
            'httponly' => true,
            'samesite' => 'Strict'
        ]);

        // Iniciar sesión
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Regenerar ID cada 30 minutos
        self::regenerateIdPeriodically();

        // Implementar timeout de inactividad
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
        $timeout = (int)($_ENV['SESSION_LIFETIME'] ?? 3600); // 1 hora por defecto

        if (isset($_SESSION['last_activity'])) {
            $inactive = time() - $_SESSION['last_activity'];

            if ($inactive > $timeout) {
                // Sesión expirada
                session_unset();
                session_destroy();
                header('Location: /?session_expired=1');
                exit;
            }
        }

        $_SESSION['last_activity'] = time();
    }

    public static function login(int $userId, string $userName): void
    {
        // Regenerar ID al hacer login
        session_regenerate_id(true);

        $_SESSION['user_id'] = $userId;
        $_SESSION['user_name'] = $userName;
        $_SESSION['last_activity'] = time();
        $_SESSION['last_regeneration'] = time();
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '';
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    public static function logout(): void
    {
        $_SESSION = [];

        // Destruir cookie de sesión
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        session_destroy();
    }

    public static function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']);
    }
}
```

```php
// index.php (ACTUALIZAR - LÍNEA 11)
<?php
error_reporting(E_ALL);
ini_set('display_errors', $_ENV['APP_DEBUG'] ?? '0');

define('BASE_PATH', __DIR__);

// Cargar .env
require_once BASE_PATH . '/vendor/autoload.php';
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

// CONFIGURAR SESIÓN GLOBALMENTE
require_once BASE_PATH . '/config/session.php';
SessionConfig::configure();

// Cargar configuración
$config = require_once BASE_PATH . '/config/config.php';
define('APP_BASE_PATH', $config['base_path']);

// ... resto del código
```

```php
// app/Controllers/TareasController.php (SIMPLIFICAR)
<?php

namespace App\Controllers;

class TareasController extends BaseController
{
    private $tareaModel;

    public function __construct()
    {
        // REMOVER session_start() - ya está centralizado
        // session_start(); ❌ ELIMINAR ESTO

        // Verificar autenticación
        if (!SessionConfig::isAuthenticated()) {
            $this->redirect('/');
            return;
        }

        $this->tareaModel = new \App\Models\Tarea();
    }

    // ... resto del código
}
```

---

### 4. Validación de Inputs

**Problema Actual:**
```php
// Sin validación robusta
$tareaData = [
    'fecha' => $input['fecha'] ?? date('Y-m-d'),
    'horas' => floatval($input['horas'] ?? 0)  // ¿Y si es negativo? ¿Y si es 999?
];
```

**Solución Implementada:**

```php
// core/Validator.php (CREAR)
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
        if ($rule === 'required' && empty($value)) {
            $this->errors[$field][] = "El campo {$field} es requerido";
            return;
        }

        // date format
        if (str_starts_with($rule, 'date')) {
            $format = 'Y-m-d';
            if (preg_match('/date:(.+)/', $rule, $matches)) {
                $format = $matches[1];
            }

            $d = \DateTime::createFromFormat($format, $value);
            if (!$d || $d->format($format) !== $value) {
                $this->errors[$field][] = "El campo {$field} debe ser una fecha válida ({$format})";
            }
        }

        // numeric
        if ($rule === 'numeric' && !is_numeric($value)) {
            $this->errors[$field][] = "El campo {$field} debe ser numérico";
        }

        // min:value
        if (preg_match('/min:(\d+\.?\d*)/', $rule, $matches)) {
            $min = (float)$matches[1];
            if ((float)$value < $min) {
                $this->errors[$field][] = "El campo {$field} debe ser al menos {$min}";
            }
        }

        // max:value
        if (preg_match('/max:(\d+\.?\d*)/', $rule, $matches)) {
            $max = (float)$matches[1];
            if ((float)$value > $max) {
                $this->errors[$field][] = "El campo {$field} no puede exceder {$max}";
            }
        }

        // in:value1,value2
        if (preg_match('/in:(.+)/', $rule, $matches)) {
            $allowed = explode(',', $matches[1]);
            if (!in_array($value, $allowed)) {
                $this->errors[$field][] = "El campo {$field} debe ser uno de: " . implode(', ', $allowed);
            }
        }

        // string length
        if (preg_match('/max_length:(\d+)/', $rule, $matches)) {
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

```php
// app/Controllers/TareasController.php (USAR VALIDADOR)
public function crear()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $this->validateCsrf();

        $input = json_decode(file_get_contents('php://input'), true);

        // VALIDAR INPUTS
        $validator = new \Core\Validator();
        $isValid = $validator->validate($input, [
            'fecha' => 'required|date:Y-m-d',
            'descripcion' => 'required|max_length:500',
            'trabajo' => 'required|numeric|min:1',
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

        // Datos ya validados, proceder
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

---

## 🏗️ **MODERNIZACIÓN BACKEND**

### 1. Eliminar require_once manual

**Problema Actual:**
```php
// Ya tienes autoloader PSR-4, pero sigues usando require_once
require_once BASE_PATH . '/app/Models/Tarea.php';
require_once BASE_PATH . '/config/database.php';
```

**Solución:**
```php
// SIMPLEMENTE REMOVER - El autoloader ya lo maneja
// require_once BASE_PATH . '/app/Models/Tarea.php'; ❌ ELIMINAR

// Usar directamente:
use App\Models\Tarea;

$tarea = new Tarea(); // ✅ El autoloader lo carga automáticamente
```

---

### 2. Router Mejorado con Middleware

**Problema Actual:**
```php
// 165 líneas de rutas repetitivas
$router->get('/tareas', 'TareasController@index');
$router->post('/tareas/crear', 'TareasController@crear');
// ... 163 líneas más
```

**Solución Implementada:**

```php
// core/Router.php (MEJORAR)
<?php

namespace Core;

class Router
{
    private array $routes = [];
    private array $middleware = [];
    private array $groupStack = [];
    private $notFoundCallback;

    // Soporte para parámetros dinámicos
    public function get(string $path, $callback, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $callback, $middleware);
    }

    public function post(string $path, $callback, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $callback, $middleware);
    }

    // Agrupar rutas con prefijo y middleware común
    public function group(array $attributes, callable $callback): void
    {
        $this->groupStack[] = $attributes;
        $callback($this);
        array_pop($this->groupStack);
    }

    private function addRoute(string $method, string $path, $callback, array $middleware): void
    {
        // Aplicar prefijos de grupos
        foreach ($this->groupStack as $group) {
            if (isset($group['prefix'])) {
                $path = $group['prefix'] . $path;
            }
            if (isset($group['middleware'])) {
                $middleware = array_merge((array)$group['middleware'], $middleware);
            }
        }

        $this->routes[$method][$path] = [
            'callback' => $callback,
            'middleware' => $middleware
        ];
    }

    // Registrar middleware
    public function registerMiddleware(string $name, callable $middleware): void
    {
        $this->middleware[$name] = $middleware;
    }

    public function run(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = $this->getPath();

        // Buscar ruta exacta
        if (isset($this->routes[$method][$path])) {
            $route = $this->routes[$method][$path];
            $this->executeRoute($route, []);
            return;
        }

        // Buscar ruta con parámetros dinámicos
        foreach ($this->routes[$method] ?? [] as $routePath => $route) {
            $pattern = $this->convertToPattern($routePath);
            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches); // Remover match completo
                $this->executeRoute($route, $matches);
                return;
            }
        }

        // 404
        if ($this->notFoundCallback) {
            call_user_func($this->notFoundCallback);
        } else {
            http_response_code(404);
            echo "404 - Página no encontrada";
        }
    }

    private function executeRoute(array $route, array $params): void
    {
        // Ejecutar middleware
        foreach ($route['middleware'] as $middlewareName) {
            if (!isset($this->middleware[$middlewareName])) {
                throw new \Exception("Middleware '{$middlewareName}' no registrado");
            }

            $result = call_user_func($this->middleware[$middlewareName]);
            if ($result === false) {
                return; // Middleware bloqueó la ruta
            }
        }

        // Ejecutar callback
        if (is_callable($route['callback'])) {
            call_user_func_array($route['callback'], $params);
        } elseif (is_string($route['callback'])) {
            $this->callController($route['callback'], $params);
        }
    }

    private function convertToPattern(string $path): string
    {
        // Convertir /tareas/{id} a regex
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '([^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    private function getPath(): string
    {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        if (defined('APP_BASE_PATH') && APP_BASE_PATH !== '/') {
            $path = str_replace(APP_BASE_PATH, '', $path);
        }

        return empty($path) ? '/' : $path;
    }

    private function callController(string $callback, array $params): void
    {
        [$controller, $method] = explode('@', $callback);
        $controllerClass = "App\\Controllers\\{$controller}";

        if (!class_exists($controllerClass)) {
            throw new \Exception("Controlador no encontrado: {$controller}");
        }

        $instance = new $controllerClass();

        if (!method_exists($instance, $method)) {
            throw new \Exception("Método no encontrado: {$controller}@{$method}");
        }

        call_user_func_array([$instance, $method], $params);
    }

    public function notFound(callable $callback): void
    {
        $this->notFoundCallback = $callback;
    }
}
```

```php
// routes/web.php (CREAR - SEPARAR RUTAS)
<?php

// Registrar middleware
$router->registerMiddleware('auth', function() {
    if (!SessionConfig::isAuthenticated()) {
        header('Location: /');
        exit;
        return false;
    }
    return true;
});

// Rutas públicas
$router->get('/', 'HomeController@index');
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');

// Rutas protegidas con middleware
$router->group(['middleware' => 'auth'], function($router) {

    // Dashboard
    $router->get('/dashboard', 'DashboardController@index');
    $router->get('/perfil', 'PerfilController@index');

    // Tareas con parámetros dinámicos
    $router->group(['prefix' => '/tareas'], function($router) {
        $router->get('', 'TareasController@index');
        $router->get('/{id}', 'TareasController@show');  // ✅ Parámetro dinámico
        $router->post('/crear', 'TareasController@crear');
        $router->post('/{id}/actualizar', 'TareasController@actualizar');
        $router->post('/{id}/eliminar', 'TareasController@eliminar');
    });

    // Trabajadores
    $router->group(['prefix' => '/trabajadores'], function($router) {
        $router->get('', 'TrabajadoresController@index');
        $router->get('/{id}', 'TrabajadoresController@show');
        $router->post('/crear', 'TrabajadoresController@crear');
    });

    // ... más rutas agrupadas
});

// 404
$router->notFound(function() {
    include BASE_PATH . '/app/Views/errors/404.php';
});
```

```php
// index.php (SIMPLIFICAR - cargar archivo de rutas)
<?php
// ... configuración inicial

$router = new Core\Router();

// Cargar rutas desde archivo separado
require_once BASE_PATH . '/routes/web.php';

// Ejecutar router
$router->run();
```

---

### 3. Dependency Injection Container

```php
// core/Container.php (CREAR)
<?php

namespace Core;

class Container
{
    private array $bindings = [];
    private array $instances = [];

    public function bind(string $abstract, callable $concrete): void
    {
        $this->bindings[$abstract] = $concrete;
    }

    public function singleton(string $abstract, callable $concrete): void
    {
        $this->bindings[$abstract] = $concrete;
        $this->instances[$abstract] = null;
    }

    public function make(string $abstract)
    {
        // Si es singleton y ya existe, retornar instancia
        if (isset($this->instances[$abstract]) && $this->instances[$abstract] !== null) {
            return $this->instances[$abstract];
        }

        // Resolver binding
        if (!isset($this->bindings[$abstract])) {
            // Auto-resolución si es una clase
            if (class_exists($abstract)) {
                return $this->resolve($abstract);
            }
            throw new \Exception("No se pudo resolver: {$abstract}");
        }

        $instance = call_user_func($this->bindings[$abstract], $this);

        // Guardar si es singleton
        if (array_key_exists($abstract, $this->instances)) {
            $this->instances[$abstract] = $instance;
        }

        return $instance;
    }

    private function resolve(string $class)
    {
        $reflection = new \ReflectionClass($class);
        $constructor = $reflection->getConstructor();

        if (!$constructor) {
            return new $class();
        }

        $parameters = $constructor->getParameters();
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $type = $parameter->getType();

            if (!$type || $type->isBuiltin()) {
                throw new \Exception("No se puede auto-resolver parámetro: {$parameter->getName()}");
            }

            $dependencies[] = $this->make($type->getName());
        }

        return $reflection->newInstanceArgs($dependencies);
    }
}
```

```php
// index.php (CONFIGURAR CONTAINER)
<?php
// ... después de cargar autoloader

$container = new Core\Container();

// Registrar bindings
$container->singleton(Database::class, function() {
    return Database::connect();
});

$container->bind(\App\Repositories\TareaRepository::class, function($container) {
    return new \App\Repositories\TareaRepository(
        $container->make(Database::class)
    );
});

// Hacer container global (o pasarlo al router)
app()->setContainer($container);
```

```php
// app/Repositories/TareaRepository.php (CREAR)
<?php

namespace App\Repositories;

use mysqli;

class TareaRepository
{
    public function __construct(
        private mysqli $db
    ) {}

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM tareas WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function all(): array
    {
        $result = $this->db->query("SELECT * FROM tareas ORDER BY fecha DESC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // ... más métodos
}
```

```php
// app/Controllers/TareasController.php (USAR REPOSITORY)
<?php

namespace App\Controllers;

use App\Repositories\TareaRepository;

class TareasController extends BaseController
{
    public function __construct(
        private TareaRepository $tareas  // ✅ Inyección automática
    ) {
        if (!SessionConfig::isAuthenticated()) {
            $this->redirect('/');
            return;
        }
    }

    public function show(int $id)
    {
        $tarea = $this->tareas->find($id);

        if (!$tarea) {
            http_response_code(404);
            echo json_encode(['error' => 'Tarea no encontrada']);
            return;
        }

        $this->render('tareas/show', ['tarea' => $tarea]);
    }
}
```

---

## 🎨 **MODERNIZACIÓN FRONTEND**

### 1. Setup de Vite

```bash
# Inicializar npm
npm init -y

# Instalar Vite
npm install --save-dev vite

# Instalar Alpine.js (o Vue.js)
npm install alpinejs

# Instalar Tailwind CSS (opcional)
npm install --save-dev tailwindcss postcss autoprefixer
npx tailwindcss init
```

```javascript
// vite.config.js
import { defineConfig } from 'vite';

export default defineConfig({
  root: 'resources',
  build: {
    outDir: '../public/build',
    manifest: true,
    rollupOptions: {
      input: {
        main: 'resources/js/app.js',
        styles: 'resources/css/app.css'
      }
    }
  },
  server: {
    proxy: {
      '/api': {
        target: 'http://localhost',
        changeOrigin: true
      }
    }
  }
});
```

```json
// package.json (scripts)
{
  "scripts": {
    "dev": "vite",
    "build": "vite build",
    "preview": "vite preview"
  }
}
```

```javascript
// resources/js/app.js
import Alpine from 'alpinejs';
import '../css/app.css';

// Inicializar Alpine
window.Alpine = Alpine;
Alpine.start();

// Importar módulos
import './components/modals.js';
import './components/calendar.js';
```

```css
/* resources/css/app.css */
@tailwind base;
@tailwind components;
@tailwind utilities;

/* Custom styles */
@layer components {
  .btn-primary {
    @apply bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded;
  }
}
```

---

### 2. Alpine.js para Modales

```html
<!-- app/Views/tareas/index.php -->
<div x-data="{ modalOpen: false, selectedTask: null }">
    <!-- Botón para abrir modal -->
    <button @click="modalOpen = true" class="btn-primary">
        Nueva Tarea
    </button>

    <!-- Modal con Alpine.js -->
    <div x-show="modalOpen"
         x-transition
         @click.away="modalOpen = false"
         class="modal-overlay">
        <div class="modal-content" @click.stop>
            <h2>Crear Tarea</h2>

            <form @submit.prevent="submitTask">
                <input type="date" x-model="taskData.fecha" required>
                <input type="text" x-model="taskData.descripcion" required>

                <button type="submit">Guardar</button>
                <button type="button" @click="modalOpen = false">Cancelar</button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('taskManager', () => ({
        modalOpen: false,
        taskData: {
            fecha: '',
            descripcion: '',
            horas: 0
        },

        async submitTask() {
            const response = await fetch('/tareas/crear', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(this.taskData)
            });

            if (response.ok) {
                this.modalOpen = false;
                this.resetForm();
                // Recargar lista de tareas
            }
        },

        resetForm() {
            this.taskData = { fecha: '', descripcion: '', horas: 0 };
        }
    }));
});
</script>
```

---

## 🗄️ **OPTIMIZACIÓN BASE DE DATOS**

### 1. Correcciones Críticas

```sql
-- Corregir tipo de datos incorrecto
ALTER TABLE empresas MODIFY nombre VARCHAR(255) NOT NULL;
ALTER TABLE empresas MODIFY dni VARCHAR(20) NOT NULL;

-- Añadir timestamps faltantes
ALTER TABLE trabajadores
    ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE parcelas
    ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
```

### 2. Índices para Performance

```sql
-- Índices para consultas frecuentes
CREATE INDEX idx_tareas_fecha ON tareas(fecha);
CREATE INDEX idx_tareas_user ON tareas(id_user);
CREATE INDEX idx_movimientos_fecha ON movimientos(fecha);
CREATE INDEX idx_movimientos_tipo ON movimientos(tipo);

-- Índices para relaciones N:N
CREATE INDEX idx_tarea_trabajadores_tarea ON tarea_trabajadores(tarea_id);
CREATE INDEX idx_tarea_trabajadores_trabajador ON tarea_trabajadores(trabajador_id);
CREATE INDEX idx_tarea_parcelas_tarea ON tarea_parcelas(tarea_id);
CREATE INDEX idx_tarea_parcelas_parcela ON tarea_parcelas(parcela_id);

-- Índices para búsquedas
CREATE INDEX idx_trabajadores_nombre ON trabajadores(nombre);
CREATE INDEX idx_parcelas_nombre ON parcelas(nombre);
CREATE INDEX idx_parcelas_propietario ON parcelas(propietario);

-- Índices compuestos
CREATE INDEX idx_tareas_user_fecha ON tareas(id_user, fecha);
CREATE INDEX idx_movimientos_user_tipo ON movimientos(id_user, tipo);
```

### 3. Sistema de Migraciones con Phinx

```bash
composer require robmorgan/phinx
vendor/bin/phinx init
```

```php
// phinx.php
<?php
require_once 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

return [
    'paths' => [
        'migrations' => 'migrations',
        'seeds' => 'database/seeds'
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_database' => 'development',
        'development' => [
            'adapter' => 'mysql',
            'host' => $_ENV['DB_HOST'],
            'name' => $_ENV['DB_NAME'],
            'user' => $_ENV['DB_USER'],
            'pass' => $_ENV['DB_PASS'],
            'charset' => 'utf8mb4',
        ]
    ]
];
```

```bash
# Crear migración
vendor/bin/phinx create AddIndexesToTareas

# Ejecutar migraciones
vendor/bin/phinx migrate

# Rollback
vendor/bin/phinx rollback
```

---

## 🧪 **DEVOPS Y TESTING**

### 1. PHPUnit Setup

```bash
composer require --dev phpunit/phpunit
```

```xml
<!-- phpunit.xml -->
<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="vendor/autoload.php"
         colors="true"
         stopOnFailure="false">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>
    <coverage>
        <include>
            <directory>app</directory>
        </include>
    </coverage>
</phpunit>
```

```php
// tests/Unit/TareaTest.php
<?php

use PHPUnit\Framework\TestCase;
use App\Models\Tarea;

class TareaTest extends TestCase
{
    public function testCreateTarea()
    {
        $tarea = new Tarea();

        $data = [
            'fecha' => '2026-02-15',
            'descripcion' => 'Test task',
            'horas' => 5.5
        ];

        $result = $tarea->create($data, 1);

        $this->assertIsInt($result);
        $this->assertGreaterThan(0, $result);
    }

    public function testValidateHorasRange()
    {
        $validator = new \Core\Validator();

        $result = $validator->validate(
            ['horas' => 25],
            ['horas' => 'required|numeric|min:0|max:24']
        );

        $this->assertFalse($result);
        $this->assertArrayHasKey('horas', $validator->getErrors());
    }
}
```

### 2. Docker para Desarrollo

```dockerfile
# Dockerfile
FROM php:8.3-fpm-alpine

# Instalar extensiones PHP
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos
COPY . .

# Instalar dependencias
RUN composer install --no-dev --optimize-autoloader

EXPOSE 9000
CMD ["php-fpm"]
```

```yaml
# docker-compose.yml
version: '3.8'

services:
  app:
    build: .
    volumes:
      - .:/var/www/html
    depends_on:
      - db
      - redis
    environment:
      DB_HOST: db
      DB_NAME: ${DB_NAME}
      DB_USER: ${DB_USER}
      DB_PASS: ${DB_PASS}

  web:
    image: nginx:alpine
    ports:
      - "8000:80"
    volumes:
      - .:/var/www/html
      - ./docker/nginx.conf:/etc/nginx/conf.d/default.conf
    depends_on:
      - app

  db:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: ${DB_PASS}
      MYSQL_DATABASE: ${DB_NAME}
    volumes:
      - dbdata:/var/lib/mysql
    ports:
      - "3306:3306"

  redis:
    image: redis:alpine
    ports:
      - "6379:6379"

volumes:
  dbdata:
```

```bash
# Comandos Docker
docker-compose up -d
docker-compose exec app php vendor/bin/phpunit
docker-compose exec app composer install
```

### 3. GitHub Actions CI/CD

```yaml
# .github/workflows/tests.yml
name: Tests

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main ]

jobs:
  test:
    runs-on: ubuntu-latest

    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: password
          MYSQL_DATABASE: test_db
        options: >-
          --health-cmd="mysqladmin ping"
          --health-interval=10s
          --health-timeout=5s
          --health-retries=3

    steps:
      - uses: actions/checkout@v3

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: mbstring, pdo_mysql, mysqli
          coverage: xdebug

      - name: Install Composer dependencies
        run: composer install --prefer-dist --no-progress

      - name: Copy .env
        run: cp .env.example .env

      - name: Run PHPStan
        run: ./vendor/bin/phpstan analyze --level=6

      - name: Run PHP-CS-Fixer
        run: ./vendor/bin/php-cs-fixer fix --dry-run --diff

      - name: Run PHPUnit tests
        run: ./vendor/bin/phpunit --coverage-text

      - name: Upload coverage to Codecov
        uses: codecov/codecov-action@v3
```

---

## 🎯 **STACK RECOMENDADO 2026**

### Backend
- **PHP 8.3+** - Tipos estrictos, enums, readonly properties
- **Laravel 11** o **Symfony 7** (o mejoras al MVC custom)
- **Composer** - Gestión de dependencias
- **MySQL 8.0** + **Redis** - Base de datos + cache
- **Monolog** - Logging profesional
- **PHPUnit/Pest** - Testing
- **PHPStan nivel 6+** - Análisis estático

### Frontend
- **Vite** - Build tool moderno (reemplaza Webpack)
- **Alpine.js** o **Vue 3** - Reactivity ligera
- **Tailwind CSS** - Utility-first CSS
- **pnpm** - Gestor de paquetes rápido
- **TypeScript** (opcional) - Type safety

### DevOps
- **Docker** + **Docker Compose** - Desarrollo local
- **GitHub Actions** - CI/CD
- **PHPStan** + **PHP-CS-Fixer** - Code quality
- **Sentry** - Error monitoring

### Seguridad
- **vlucas/phpdotenv** - Variables de entorno
- **CSRF tokens** - Protección contra ataques
- **Helmet headers** - Security headers HTTP
- **Rate limiting** - Prevenir abuso API

---

## 📅 **ROADMAP DE IMPLEMENTACIÓN**

### **Semana 1: Seguridad CRÍTICA** 🔴
- [ ] Día 1: .env + phpdotenv
- [ ] Día 2: CSRF tokens
- [ ] Día 3: Session hardening
- [ ] Día 4: Input validation
- [ ] Día 5: Testing de seguridad

### **Semana 2-3: Fundación**
- [ ] Router mejorado + middleware
- [ ] Dependency Injection Container
- [ ] Logging con Monolog
- [ ] PHPUnit setup + primeros tests

### **Semana 4-6: Modernización Backend**
- [ ] Repository Pattern
- [ ] Eliminación de require_once
- [ ] Migraciones de base de datos
- [ ] Code coverage 60%+

### **Semana 7-9: Modernización Frontend**
- [ ] Vite setup
- [ ] Alpine.js integration
- [ ] Tailwind CSS
- [ ] Refactor modales

### **Semana 10-12: DevOps**
- [ ] Docker Compose
- [ ] GitHub Actions
- [ ] Deployment automático
- [ ] Monitoring

---

*Documento creado: 15 de febrero de 2026*
*Para preguntas o aclaraciones, revisar el código actual o consultar documentación oficial*
