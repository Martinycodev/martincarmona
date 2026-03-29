<?php

namespace App\Core;

class Application
{
    public static Application $app;
    public Router $router;
    public Request $request;
    public Response $response;

    public string $rootPath;

    public function __construct(string $rootPath)
    {
        self::$app = $this;
        $this->rootPath = $rootPath;
        $this->request  = new Request();
        $this->response = new Response();
        $this->router   = new Router($this->request, $this->response);
    }

    public function run(): void
    {
        try {
            echo $this->router->resolve();
        } catch (\Exception $e) {
            $this->response->setStatusCode($e->getCode() ?: 500);
            echo $this->renderError($e);
        }
    }

    private function renderError(\Exception $e): string
    {
        $code    = $e->getCode() ?: 500;
        $message = $e->getMessage();
        ob_start();
        include $this->rootPath . '/src/Views/error.php';
        return ob_get_clean();
    }
}
