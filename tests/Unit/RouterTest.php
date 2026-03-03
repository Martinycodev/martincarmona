<?php

namespace Tests\Unit;

use Core\Router;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    private Router $router;

    protected function setUp(): void
    {
        $this->router = new Router();
        // Simular la constante que usa el Router para quitar el prefijo de ruta
        if (!defined('APP_BASE_PATH')) {
            define('APP_BASE_PATH', '/');
        }
    }

    // -------------------------------------------------------------------------
    // Registro de rutas estáticas
    // -------------------------------------------------------------------------

    public function test_static_get_route_is_registered(): void
    {
        $called = false;
        $this->router->get('/tareas', function () use (&$called) {
            $called = true;
        });

        $this->simulateRequest('GET', '/tareas');
        $this->assertTrue($called, 'La ruta GET /tareas debería haberse ejecutado');
    }

    public function test_static_post_route_is_registered(): void
    {
        $called = false;
        $this->router->post('/tareas/crear', function () use (&$called) {
            $called = true;
        });

        $this->simulateRequest('POST', '/tareas/crear');
        $this->assertTrue($called, 'La ruta POST /tareas/crear debería haberse ejecutado');
    }

    public function test_wrong_method_does_not_match(): void
    {
        $called = false;
        $this->router->get('/tareas', function () use (&$called) {
            $called = true;
        });

        // Mismo path pero método diferente → no debe ejecutar el callback
        $this->simulateRequest('POST', '/tareas');
        $this->assertFalse($called);
    }

    // -------------------------------------------------------------------------
    // Rutas dinámicas con {param}
    // -------------------------------------------------------------------------

    public function test_dynamic_route_matches_and_injects_param(): void
    {
        $capturedId = null;
        $this->router->get('/tareas/{id}', function () use (&$capturedId) {
            $capturedId = $_GET['id'] ?? null;
        });

        $this->simulateRequest('GET', '/tareas/42');
        $this->assertSame('42', $capturedId, 'El parámetro {id} debería inyectarse en $_GET');
    }

    public function test_dynamic_route_with_multiple_params(): void
    {
        $captured = [];
        $this->router->get('/parcelas/{parcela_id}/riegos/{riego_id}', function () use (&$captured) {
            $captured = ['parcela_id' => $_GET['parcela_id'] ?? null, 'riego_id' => $_GET['riego_id'] ?? null];
        });

        $this->simulateRequest('GET', '/parcelas/5/riegos/12');
        $this->assertSame('5', $captured['parcela_id']);
        $this->assertSame('12', $captured['riego_id']);
    }

    public function test_dynamic_route_does_not_match_extra_segments(): void
    {
        $called = false;
        $this->router->get('/tareas/{id}', function () use (&$called) {
            $called = true;
        });

        // Tiene un segmento extra → no debe coincidir
        $this->simulateRequest('GET', '/tareas/42/detalle');
        $this->assertFalse($called);
    }

    public function test_static_route_takes_priority_over_dynamic(): void
    {
        $staticCalled  = false;
        $dynamicCalled = false;

        $this->router->get('/tareas/pendientes', function () use (&$staticCalled) {
            $staticCalled = true;
        });
        $this->router->get('/tareas/{id}', function () use (&$dynamicCalled) {
            $dynamicCalled = true;
        });

        $this->simulateRequest('GET', '/tareas/pendientes');
        $this->assertTrue($staticCalled, 'La ruta estática debería tener prioridad');
        $this->assertFalse($dynamicCalled);
    }

    // -------------------------------------------------------------------------
    // Handler 404
    // -------------------------------------------------------------------------

    public function test_not_found_handler_called_for_unknown_route(): void
    {
        $notFoundCalled = false;
        $this->router->notFound(function () use (&$notFoundCalled) {
            $notFoundCalled = true;
        });

        $this->simulateRequest('GET', '/ruta-que-no-existe');
        $this->assertTrue($notFoundCalled);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Simula una petición HTTP sin levantar un servidor real.
     * Sobrescribe las variables de servidor que usa Router::run().
     */
    private function simulateRequest(string $method, string $path): void
    {
        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['REQUEST_URI']    = $path;

        // Capturar la salida para no contaminar el output de PHPUnit
        ob_start();
        $this->router->run();
        ob_end_clean();
    }
}
