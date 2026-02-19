<?php

/**
 * Configuración centralizada de sesión.
 * Se llama una única vez desde index.php antes del router.
 */
class SessionConfig
{
    /** Inactividad máxima antes de cerrar sesión automáticamente: 2 horas */
    const TIMEOUT = 7200;

    /** Intervalo entre regeneraciones del ID de sesión: 30 minutos */
    const REGEN_INTERVAL = 1800;

    /**
     * Configura e inicia la sesión con parámetros seguros.
     * Gestiona timeout de inactividad y regeneración periódica de ID.
     */
    public static function configure(): void
    {
        if (session_status() !== PHP_SESSION_NONE) {
            return;
        }

        // Detectar HTTPS para marcar la cookie como secure en producción
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                   || (int)($_SERVER['SERVER_PORT'] ?? 80) === 443;

        session_set_cookie_params([
            'lifetime' => 0,        // Cookie de sesión: se borra al cerrar el navegador
            'path'     => '/',
            'domain'   => '',
            'secure'   => $isHttps, // Solo enviar por HTTPS (activo en producción)
            'httponly' => true,     // No accesible desde JavaScript
            'samesite' => 'Lax',   // Protección CSRF adicional
        ]);

        session_name('SESS_MC');
        session_start();

        // Cerrar sesión si ha superado el tiempo de inactividad
        if (isset($_SESSION['_last_activity'])) {
            if (time() - $_SESSION['_last_activity'] > self::TIMEOUT) {
                session_unset();
                session_destroy();
                session_start();
            }
        }
        $_SESSION['_last_activity'] = time();

        // Regenerar ID de sesión periódicamente para prevenir session hijacking
        if (!isset($_SESSION['_sess_created'])) {
            $_SESSION['_sess_created'] = time();
        } elseif (time() - $_SESSION['_sess_created'] > self::REGEN_INTERVAL) {
            session_regenerate_id(true);
            $_SESSION['_sess_created'] = time();
        }
    }
}
