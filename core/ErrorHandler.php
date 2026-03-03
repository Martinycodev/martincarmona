<?php

namespace Core;

class ErrorHandler
{
    /**
     * Registra los tres handlers globales de PHP.
     * Llamar una vez, lo antes posible en el bootstrap.
     */
    public static function register(): void
    {
        set_exception_handler([self::class, 'handleException']);
        set_error_handler([self::class, 'handleError']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    /**
     * Captura cualquier Throwable no manejado.
     */
    public static function handleException(\Throwable $e): void
    {
        $isDev = ($_ENV['APP_ENV'] ?? 'production') === 'development';

        // Evitar cabeceras duplicadas si ya se envió output parcial
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/html; charset=UTF-8');
        }

        error_log(sprintf(
            '[%s] %s en %s:%d',
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        ));

        if ($isDev) {
            self::renderDev($e);
        } else {
            self::renderProd();
        }
    }

    /**
     * Convierte errores de PHP en ErrorException para que pasen por handleException.
     */
    public static function handleError(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        // Respetar el operador de silencio (@)
        if (!(error_reporting() & $errno)) {
            return false;
        }

        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    /**
     * Captura errores fatales que PHP no lanza como excepciones (E_ERROR, E_PARSE...).
     */
    public static function handleShutdown(): void
    {
        $error = error_get_last();

        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
            $e = new \ErrorException(
                $error['message'],
                0,
                $error['type'],
                $error['file'],
                $error['line']
            );
            self::handleException($e);
        }
    }

    // -------------------------------------------------------------------------

    private static function renderDev(\Throwable $e): void
    {
        $class   = htmlspecialchars(get_class($e));
        $message = htmlspecialchars($e->getMessage());
        $file    = htmlspecialchars($e->getFile());
        $line    = $e->getLine();
        $trace   = htmlspecialchars($e->getTraceAsString());

        echo <<<HTML
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Error — {$class}</title>
            <style>
                * { box-sizing: border-box; margin: 0; padding: 0; }
                body { font-family: 'Segoe UI', sans-serif; background: #1e1e2e; color: #cdd6f4; padding: 2rem; }
                .card { background: #313244; border-radius: 8px; padding: 2rem; max-width: 900px; margin: 0 auto; }
                .badge { display: inline-block; background: #f38ba8; color: #1e1e2e; padding: .2rem .7rem; border-radius: 4px; font-size: .85rem; font-weight: bold; margin-bottom: 1rem; }
                h1 { font-size: 1.4rem; color: #f38ba8; margin-bottom: .5rem; }
                .location { font-size: .9rem; color: #a6adc8; margin-bottom: 1.5rem; }
                pre { background: #1e1e2e; padding: 1.2rem; border-radius: 6px; overflow-x: auto; font-size: .82rem; line-height: 1.6; color: #cdd6f4; }
            </style>
        </head>
        <body>
            <div class="card">
                <span class="badge">500 — Error interno</span>
                <h1>{$class}: {$message}</h1>
                <p class="location">{$file} : línea {$line}</p>
                <pre>{$trace}</pre>
            </div>
        </body>
        </html>
        HTML;
    }

    private static function renderProd(): void
    {
        $base = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';

        echo <<<HTML
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Error — Sistema de Gestión Agrícola</title>
            <style>
                * { box-sizing: border-box; margin: 0; padding: 0; }
                body { font-family: Arial, sans-serif; background: #1e1e1e; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
                .card { background: white; padding: 2.5rem; border-radius: 8px; text-align: center; max-width: 480px; width: 90%; box-shadow: 0 4px 20px rgba(0,0,0,.3); }
                h1 { color: #dc3545; margin-bottom: .75rem; font-size: 1.5rem; }
                p { color: #555; margin-bottom: 1.5rem; }
                a { display: inline-block; padding: .6rem 1.4rem; background: #007bff; color: white; text-decoration: none; border-radius: 5px; }
            </style>
        </head>
        <body>
            <div class="card">
                <h1>Ha ocurrido un error</h1>
                <p>Algo ha ido mal. El problema ha sido registrado y se revisará próximamente.</p>
                <a href="{$base}/">Volver al inicio</a>
            </div>
        </body>
        </html>
        HTML;
    }
}
