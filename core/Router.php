<?php

namespace Core;

class Router
{
    private $routes = [];
    private $notFoundCallback;

    public function get($path, $callback)
    {
        $this->routes['GET'][$path] = $callback;
    }

    public function post($path, $callback)
    {
        $this->routes['POST'][$path] = $callback;
    }

    public function notFound($callback)
    {
        $this->notFoundCallback = $callback;
    }

    public function run()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remover la ruta base del proyecto de la URL
        if (defined('APP_BASE_PATH') && APP_BASE_PATH !== '/') {
            $path = str_replace(APP_BASE_PATH, '', $path);
        }
        
        // Si la ruta está vacía, usar '/'
        if (empty($path)) {
            $path = '/';
        }
        
        // Debug: mostrar información de la ruta
        if (defined('APP_BASE_PATH')) {
            error_log("APP_BASE_PATH: " . APP_BASE_PATH);
        }
        error_log("REQUEST_URI: " . $_SERVER['REQUEST_URI']);
        error_log("Path procesado: " . $path);

        if (isset($this->routes[$method][$path])) {
            $callback = $this->routes[$method][$path];
            
            if (is_callable($callback)) {
                return call_user_func($callback);
            } elseif (is_string($callback)) {
                // Formato: "Controller@method"
                return $this->callController($callback);
            }
        }

        // Ruta no encontrada
        if ($this->notFoundCallback) {
            return call_user_func($this->notFoundCallback);
        }
        
        http_response_code(404);
        echo "404 - Página no encontrada";
    }

    private function callController($callback)
    {
        list($controller, $method) = explode('@', $callback);
        $controllerClass = "App\\Controllers\\{$controller}";
        
        if (class_exists($controllerClass)) {
            $controllerInstance = new $controllerClass();
            if (method_exists($controllerInstance, $method)) {
                return $controllerInstance->$method();
            }
        }
        
        throw new \Exception("Controlador o método no encontrado: {$controller}@{$method}");
    }
}
