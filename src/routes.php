<?php

use App\Controllers\HomeController;
use App\Controllers\PortfolioController;
use App\Controllers\ContactController;

/** @var \App\Core\Router $router */

// Home
$router->get('/', [HomeController::class, 'index']);

// Portfolio
$router->get('/portfolio', [PortfolioController::class, 'index']);
$router->get('/portfolio/proyecto', [PortfolioController::class, 'show']);

// Contacto
$router->get('/contacto', [ContactController::class, 'index']);
$router->post('/contacto/enviar', [ContactController::class, 'send']);
