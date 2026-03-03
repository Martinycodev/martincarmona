<?php

namespace Core;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Level;

/**
 * Factoría de loggers centralizada.
 *
 * Canales disponibles:
 *   Logger::app()      → logs/app.log      (INFO+)
 *   Logger::security() → logs/security.log (WARNING+)
 *
 * Uso:
 *   Logger::app()->info('Tarea creada', ['id' => 42]);
 *   Logger::security()->warning('Intento de login fallido', ['email' => $email]);
 *   Logger::app()->error('Error BD', ['exception' => $e->getMessage()]);
 */
class Logger
{
    private static array $instances = [];

    /**
     * Canal general de la aplicación.
     */
    public static function app(): MonologLogger
    {
        return self::channel('app', Level::Info);
    }

    /**
     * Canal de eventos de seguridad (login, CSRF, accesos denegados).
     */
    public static function security(): MonologLogger
    {
        return self::channel('security', Level::Warning);
    }

    // -------------------------------------------------------------------------

    private static function channel(string $name, Level $minLevel): MonologLogger
    {
        if (isset(self::$instances[$name])) {
            return self::$instances[$name];
        }

        $logDir = defined('BASE_PATH') ? BASE_PATH . '/logs' : __DIR__ . '/../logs';

        // Formato: [2026-03-01 14:23:05] app.INFO: mensaje {"key":"val"} []
        $formatter = new LineFormatter(
            "[%datetime%] %channel%.%level_name%: %message% %context%\n",
            'Y-m-d H:i:s',
            false,
            true
        );

        // Rotación diaria, máximo 30 ficheros
        $handler = new RotatingFileHandler(
            $logDir . '/' . $name . '.log',
            30,
            $minLevel
        );
        $handler->setFormatter($formatter);

        $logger = new MonologLogger($name);
        $logger->pushHandler($handler);

        self::$instances[$name] = $logger;
        return $logger;
    }
}
