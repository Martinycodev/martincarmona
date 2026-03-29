<?php

namespace App\Core;

use App\Core\Application;

class Controller
{
    protected string $layout = 'main';

    /**
     * Renderiza una vista dentro del layout activo.
     */
    public function render(string $view, array $params = []): string
    {
        $viewContent = $this->renderView($view, $params);
        return $this->renderLayout($viewContent, $params);
    }

    /**
     * Renderiza una vista parcial (sin layout).
     */
    public function renderView(string $view, array $params = []): string
    {
        $viewPath = Application::$app->rootPath . '/src/Views/' . $view . '.php';

        if (!file_exists($viewPath)) {
            throw new \Exception("View [{$view}] not found", 500);
        }

        extract($params);
        ob_start();
        include $viewPath;
        return ob_get_clean();
    }

    private function renderLayout(string $content, array $params = []): string
    {
        $layoutPath = Application::$app->rootPath . '/src/Views/layouts/' . $this->layout . '.php';

        if (!file_exists($layoutPath)) {
            throw new \Exception("Layout [{$this->layout}] not found", 500);
        }

        extract($params);
        ob_start();
        include $layoutPath;
        return ob_get_clean();
    }

    public function setLayout(string $layout): void
    {
        $this->layout = $layout;
    }
}
