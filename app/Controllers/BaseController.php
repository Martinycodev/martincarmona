<?php

namespace App\Controllers;

// Importar CsrfMiddleware para protección CSRF
use Core\CsrfMiddleware;

class BaseController
{
    protected function render($view, $data = [])
    {
        // Incluir token CSRF automáticamente en todas las vistas
        // Esto permite usar echo CsrfMiddleware::getTokenField() en los formularios
        $data['csrf_token'] = CsrfMiddleware::generateToken();

        // Verificar si es una petición AJAX
        $isAjax = $this->isAjaxRequest();
        
        // Decidir si usar layout
        $useLayout = true;

        // Permitir desactivar layout desde la vista/controlador
        if (isset($data['_layout']) && $data['_layout'] === false) {
            $useLayout = false;
        }

        // No envolver vistas del propio layout ni la pantalla de login
        if ($view === 'home' || substr($view, 0, 8) === 'layouts/') {
            $useLayout = false;
        }

        // Para peticiones AJAX, solo renderizar el contenido sin layout
        if ($isAjax) {
            $useLayout = false;
        }

        // Hacer variables disponibles en la vista
        extract($data);

        $viewPath = BASE_PATH . "/app/Views/{$view}.php";
        if (!file_exists($viewPath)) {
            throw new \Exception("Vista no encontrada: {$view} en {$viewPath}. BASE_PATH: " . BASE_PATH);
        }

        if ($useLayout) {
            include BASE_PATH . '/app/Views/layouts/header.php';
            include $viewPath;
            include BASE_PATH . '/app/Views/layouts/footer.php';
            return;
        }

        include $viewPath;
    }

    protected function json($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    protected function redirect($url)
    {
        // Agregar la ruta base si no está presente
        if (defined('APP_BASE_PATH') && strpos($url, 'http') !== 0 && strpos($url, '/') === 0) {
            $url = APP_BASE_PATH . $url;
        }
        
        header("Location: {$url}");
        exit;
    }
    
    protected function url($path = '')
    {
        // Generar URL completa con la ruta base
        if (defined('APP_BASE_PATH')) {
            return APP_BASE_PATH . $path;
        }
        return $path;
    }
    
    protected function isAjaxRequest()
    {
        return (
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        ) || (
            isset($_SERVER['HTTP_ACCEPT']) &&
            strpos($_SERVER['HTTP_ACCEPT'], 'text/html') !== false &&
            isset($_SERVER['HTTP_X_REQUESTED_WITH'])
        );
    }

    /**
     * Valida el token CSRF de una petición POST/PUT/DELETE
     *
     * Protege contra ataques CSRF (Cross-Site Request Forgery)
     *
     * ¿Cómo funciona?
     * 1. Obtiene el token del formulario (POST) o del header AJAX
     * 2. Valida que coincida con el token guardado en la sesión
     * 3. Si NO coincide, rechaza la petición con error 403
     *
     * ¿Cuándo usar?
     * Al INICIO de cualquier método POST/PUT/DELETE de tus controladores
     *
     * Ejemplo:
     * public function crear() {
     *     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
     *         $this->validateCsrf(); // ← Validar CSRF primero
     *         // ... procesar formulario ...
     *     }
     * }
     *
     * @return bool true si el token es válido
     * @throws void Sale con error 403 si el token es inválido
     */
    protected function validateCsrf(): bool
    {
        // Obtener token de dos posibles lugares:

        // 1. Desde formulario HTML normal (viene en $_POST['csrf_token'])
        $tokenFromPost = $_POST['csrf_token'] ?? null;

        // 2. Desde petición AJAX (viene en header HTTP X-CSRF-TOKEN)
        $tokenFromHeader = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

        // Usar el que exista (prioridad a POST)
        $token = $tokenFromPost ?? $tokenFromHeader;

        // Validar el token usando CsrfMiddleware
        if (!CsrfMiddleware::validateToken($token)) {
            // Token inválido o ausente - rechazar petición

            // Verificar si es petición AJAX
            $isAjax = $this->isAjaxRequest();

            if ($isAjax) {
                // Respuesta JSON para peticiones AJAX
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'error' => 'Token CSRF inválido o ausente',
                    'message' => 'Por seguridad, esta acción requiere un token CSRF válido. Recarga la página e intenta nuevamente.'
                ]);
            } else {
                // Respuesta HTML para formularios normales
                http_response_code(403);
                echo '<!DOCTYPE html>
                <html lang="es">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>403 - Token CSRF Inválido</title>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            text-align: center;
                            padding: 50px;
                            background: #f5f5f5;
                            margin: 0;
                        }
                        .container {
                            background: white;
                            padding: 40px;
                            border-radius: 8px;
                            max-width: 500px;
                            margin: 0 auto;
                            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                        }
                        h1 {
                            color: #dc3545;
                            font-size: 2em;
                            margin-bottom: 20px;
                        }
                        p {
                            color: #666;
                            line-height: 1.6;
                            margin: 10px 0;
                        }
                        .btn {
                            display: inline-block;
                            padding: 12px 24px;
                            background: #007bff;
                            color: white;
                            text-decoration: none;
                            border-radius: 5px;
                            margin-top: 20px;
                            font-weight: bold;
                        }
                        .btn:hover {
                            background: #0056b3;
                        }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <h1>🔒 403 - Token CSRF Inválido</h1>
                        <p><strong>La petición fue rechazada por motivos de seguridad.</strong></p>
                        <p>El token CSRF es inválido, ha expirado, o no está presente.</p>
                        <p>Esto puede ocurrir si:</p>
                        <ul style="text-align: left; color: #666;">
                            <li>La sesión expiró</li>
                            <li>El formulario fue enviado desde otra página</li>
                            <li>Hay un problema con las cookies del navegador</li>
                        </ul>
                        <a href="' . $this->url('/dashboard') . '" class="btn">Volver al Dashboard</a>
                    </div>
                </body>
                </html>';
            }

            exit; // Detener ejecución inmediatamente
        }

        // Token válido - permitir continuar
        return true;
    }
}
