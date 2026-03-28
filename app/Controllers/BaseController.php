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

        // No envolver vistas publicas (landing, login, contacto, legales...) ni layouts
        $publicViews = ['home', 'login', 'contacto', 'sobre-nosotros', 'privacidad', 'aviso-legal', 'cookies'];
        if (in_array($view, $publicViews) || substr($view, 0, 8) === 'layouts/') {
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
    
    /**
     * Verifica que el usuario esté autenticado con rol 'empresa'.
     * Redirige al dashboard propio si tiene otro rol, o al login si no está autenticado.
     * Backwards-compatible: sesiones sin user_rol se tratan como 'empresa'.
     */
    protected function requireEmpresa(): void
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/');
            exit;
        }
        $rol = $_SESSION['user_rol'] ?? 'empresa';
        if ($rol !== 'empresa') {
            $this->redirectByRole($rol);
            exit;
        }
    }

    /**
     * Permite acceso a roles 'empresa' O 'admin' (para el panel de administración).
     */
    protected function requireEmpresaOAdmin(): void
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/');
            exit;
        }
        $rol = $_SESSION['user_rol'] ?? 'empresa';
        if (!in_array($rol, ['empresa', 'admin'])) {
            $this->redirectByRole($rol);
            exit;
        }
    }

    /**
     * Redirige al usuario a su dashboard según su rol.
     */
    protected function redirectByRole(string $rol): void
    {
        $map = [
            'admin'       => '/admin/usuarios',
            'propietario' => '/propietario',
            'trabajador'  => '/trabajador',
        ];
        $this->redirect($map[$rol] ?? '/');
    }

    /**
     * Valida que un recurso existe y pertenece al usuario actual (ACL por id_user).
     * Útil para métodos detalle() donde se necesita verificar propiedad antes de mostrar.
     *
     * @param int    $id    ID del recurso a buscar
     * @param string $table Nombre de la tabla (debe tener columnas 'id' e 'id_user')
     * @return array|null   Fila completa del recurso, o null si no existe / no le pertenece
     */
    protected function validateAndGetResource(int $id, string $table): ?array
    {
        $db = \Database::connect();
        $stmt = $db->prepare("SELECT * FROM {$table} WHERE id = ? AND id_user = ?");
        $stmt->bind_param("ii", $id, $_SESSION['user_id']);
        $stmt->execute();
        $resource = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $resource ?: null;
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
                        <h1>' . emoji('lock', '1.2rem') . ' 403 - Token CSRF Inválido</h1>
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

    /**
     * Optimizar imagen: redimensionar si supera maxWidth y comprimir a JPEG.
     * Útil para fotos de móvil que suelen pesar 5-15MB.
     *
     * @param string $tmpPath    Ruta temporal del archivo subido
     * @param string $mimeType   Tipo MIME real del archivo
     * @param string $basePath   Ruta destino SIN extensión
     * @param string $originalExt Extensión original del archivo
     * @param int    $maxWidth   Ancho máximo en px (default 1920)
     * @param int    $quality    Calidad JPEG 0-100 (default 80)
     * @return array|false ['path' => ..., 'extension' => ..., 'mime' => ...] o false
     */
    protected function optimizarImagen($tmpPath, $mimeType, $basePath, $originalExt, $maxWidth = 1920, $quality = 80)
    {
        // Si GD está disponible, redimensionar y comprimir
        if (\function_exists('imagecreatefromjpeg')) {
            $image = null;

            switch ($mimeType) {
                case 'image/jpeg': $image = @\imagecreatefromjpeg($tmpPath); break;
                case 'image/png':  $image = @\imagecreatefrompng($tmpPath);  break;
                case 'image/webp': $image = @\imagecreatefromwebp($tmpPath); break;
            }

            if ($image) {
                $origWidth  = \imagesx($image);
                $origHeight = \imagesy($image);

                if ($origWidth > $maxWidth) {
                    $ratio     = $maxWidth / $origWidth;
                    $newWidth  = $maxWidth;
                    $newHeight = intval($origHeight * $ratio);
                    $resized   = \imagecreatetruecolor($newWidth, $newHeight);
                    \imagealphablending($resized, false);
                    \imagesavealpha($resized, true);
                    \imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
                    \imagedestroy($image);
                    $image = $resized;
                }

                $dest = $basePath . '.jpg';
                $ok = \imagejpeg($image, $dest, $quality);
                \imagedestroy($image);

                if ($ok) {
                    return ['path' => $dest, 'extension' => 'jpg', 'mime' => 'image/jpeg'];
                }
            }
        }

        // Fallback sin GD: copiar el archivo original tal cual
        $dest = $basePath . '.' . $originalExt;
        if (\move_uploaded_file($tmpPath, $dest)) {
            return ['path' => $dest, 'extension' => $originalExt, 'mime' => $mimeType];
        }
        return false;
    }
}
