<?php

namespace App\Core;

class Router
{
    protected array $routes = [];
    private Request  $request;
    private Response $response;

    public function __construct(Request $request, Response $response)
    {
        $this->request  = $request;
        $this->response = $response;
    }

    public function get(string $path, array|callable $callback): void
    {
        $this->routes['get'][$path] = $callback;
    }

    public function post(string $path, array|callable $callback): void
    {
        $this->routes['post'][$path] = $callback;
    }

    public function resolve(): string
    {
        $path     = $this->request->getPath();
        $method   = $this->request->getMethod();
        $callback = $this->routes[$method][$path] ?? false;

        if ($callback === false) {
            throw new \Exception("Page not found", 404);
        }

        if (is_array($callback)) {
            [$controllerClass, $action] = $callback;
            $controller = new $controllerClass();
            return $controller->$action($this->request, $this->response);
        }

        return call_user_func($callback, $this->request, $this->response);
    }
}
