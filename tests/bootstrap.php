<?php

/**
 * Bootstrap para PHPUnit.
 * Define las constantes mínimas y registra el autoloader del proyecto
 * sin arrancar la app completa (sin sesión, sin router, sin BD real).
 */

define('BASE_PATH', dirname(__DIR__));

// Composer (Monolog, etc.)
require_once BASE_PATH . '/vendor/autoload.php';

// Autoloader PSR-4 del proyecto
require_once BASE_PATH . '/core/Autoloader.php';
$autoloader = new Core\Autoloader();
$autoloader->register();
$autoloader->addNamespace('Core',  BASE_PATH . '/core');
$autoloader->addNamespace('App',   BASE_PATH . '/app');
$autoloader->addNamespace('Tests', BASE_PATH . '/tests');

// Cargar la clase Database (sin conectar aún — la conexión es lazy via Singleton)
require_once BASE_PATH . '/config/database.php';
