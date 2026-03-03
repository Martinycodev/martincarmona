<?php

namespace Core;

class Router
{
    private $routes = [];
    private $dynamicRoutes = [];
    private $notFoundCallback;

    public function get($path, $callback)
    {
        if (strpos($path, '{') !== false) {
            $this->dynamicRoutes['GET'][] = $this->compile($path, $callback);
        } else {
            $this->routes['GET'][$path] = $callback;
        }
    }

    public function post($path, $callback)
    {
        if (strpos($path, '{') !== false) {
            $this->dynamicRoutes['POST'][] = $this->compile($path, $callback);
        } else {
            $this->routes['POST'][$path] = $callback;
        }
    }

    public function notFound($callback)
    {
        $this->notFoundCallback = $callback;
    }

    public function run()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        if (defined('APP_BASE_PATH') && APP_BASE_PATH !== '/') {
            $path = str_replace(APP_BASE_PATH, '', $path);
        }

        if (empty($path)) {
            $path = '/';
        }

        // 1. Coincidencia exacta (más rápida)
        if (isset($this->routes[$method][$path])) {
            return $this->dispatch($this->routes[$method][$path]);
        }

        // 2. Coincidencia con parámetros dinámicos
        foreach ($this->dynamicRoutes[$method] ?? [] as $route) {
            if (preg_match($route['pattern'], $path, $matches)) {
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $_GET[$key] = $value;
                    }
                }
                return $this->dispatch($route['callback']);
            }
        }

        // 404
        if ($this->notFoundCallback) {
            return call_user_func($this->notFoundCallback);
        }

        http_response_code(404);
        echo "404 - Página no encontrada";
    }

    private function compile($path, $callback): array
    {
        $pattern = preg_replace('/\{([a-z_]+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';
        return ['pattern' => $pattern, 'callback' => $callback];
    }

    private function dispatch($callback)
    {
        if (is_callable($callback)) {
            return call_user_func($callback);
        }

        if (is_string($callback)) {
            return $this->callController($callback);
        }
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
