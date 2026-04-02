<?php

use App\Controllers\HomeController;

/** @var \App\Core\Router $router */

// ┌─────────────────────────────────────────────────────────┐
// │  MODO PLACEHOLDER — Solo home pública.                  │
// │  Descomentar las rutas cuando la web esté lista.        │
// └─────────────────────────────────────────────────────────┘

$router->get('/', [HomeController::class, 'index']);

// use App\Controllers\PortfolioController;
// use App\Controllers\ContactController;
//
// $router->get('/portfolio', [PortfolioController::class, 'index']);
// $router->get('/portfolio/proyecto', [PortfolioController::class, 'show']);
// $router->get('/contacto', [ContactController::class, 'index']);
// $router->post('/contacto/enviar', [ContactController::class, 'send']);
