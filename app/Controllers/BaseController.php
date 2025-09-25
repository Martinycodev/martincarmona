<?php

namespace App\Controllers;

class BaseController
{
    protected function render($view, $data = [])
    {
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
}
