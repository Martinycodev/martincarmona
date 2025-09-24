<?php

namespace App\Controllers;

class BaseController
{
    protected function render($view, $data = [])
    {
        // Extraer los datos para que estén disponibles en la vista
        extract($data);
        
        // Incluir la vista usando la ruta absoluta
        $viewPath = BASE_PATH . "/app/Views/{$view}.php";
        
        // Debug: mostrar la ruta que estamos buscando
        if (!file_exists($viewPath)) {
            throw new \Exception("Vista no encontrada: {$view} en {$viewPath}. BASE_PATH: " . BASE_PATH);
        }
        
        ob_start();
        include $viewPath;
        $content = ob_get_clean();
        echo $content;
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
