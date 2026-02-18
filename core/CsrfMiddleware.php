<?php

namespace Core;

/**
 * CSRF Middleware - Protección contra ataques Cross-Site Request Forgery
 * 
 * ¿Qué es CSRF?
 * Es un ataque donde un sitio malicioso engaña al navegador del usuario
 * para que envíe peticiones no autorizadas a nuestra aplicación.
 * 
 * ¿Cómo prevenimos CSRF?
 * Generamos un token secreto aleatorio que solo nosotros conocemos.
 * Incluimos este token en todos los formularios.
 * Al recibir una petición POST, verificamos que el token sea correcto.
 * Si no coincide, rechazamos la petición.
 */
class CsrfMiddleware
{
    /**
     * Nombre de la clave donde se guarda el token en la sesión
     */
    private const TOKEN_KEY = 'csrf_token';

    /**
     * Genera un token CSRF único para la sesión actual
     * 
     * ¿Cómo funciona?
     * 1. Verifica si la sesión está iniciada
     * 2. Si NO existe un token en la sesión, crea uno nuevo
     * 3. Usa random_bytes(32) para generar 32 bytes aleatorios
     * 4. Convierte los bytes a hexadecimal (64 caracteres)
     * 5. Guarda el token en $_SESSION para futuras verificaciones
     * 6. Retorna el token para incluirlo en el formulario
     * 
     * @return string Token CSRF (64 caracteres hexadecimales)
     */
    public static function generateToken(): string
    {
        // Verificar que la sesión esté iniciada
        // session_status() devuelve PHP_SESSION_ACTIVE si la sesión está activa
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Si ya existe un token en la sesión, reutilizarlo
        // ¿Por qué reutilizar? Para que el mismo token funcione en múltiples formularios
        if (!isset($_SESSION[self::TOKEN_KEY])) {
            // Generar nuevo token aleatorio
            // random_bytes(32) = 32 bytes aleatorios seguros criptográficamente
            // bin2hex() = convierte bytes binarios a hexadecimal
            // Resultado: 64 caracteres hexadecimales (a-f, 0-9)
            $_SESSION[self::TOKEN_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::TOKEN_KEY];
    }

    /**
     * Valida que el token CSRF enviado sea correcto
     * 
     * ¿Cómo funciona?
     * 1. Obtiene el token de la sesión (generado previamente)
     * 2. Compara con el token enviado en el formulario
     * 3. Usa hash_equals() para comparación segura (previene timing attacks)
     * 4. Retorna true si coinciden, false si no
     * 
     * @param string|null $token Token enviado en el formulario (puede ser null)
     * @return bool true si el token es válido, false si no
     */
    public static function validateToken(?string $token): bool
    {
        // Verificar que la sesión esté iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Si no hay token en la sesión, la validación falla
        // Esto puede pasar si la sesión expiró
        if (!isset($_SESSION[self::TOKEN_KEY])) {
            return false;
        }

        // Si el token enviado es null o vacío, la validación falla
        if ($token === null || $token === '') {
            return false;
        }

        // Comparar los tokens de forma segura
        // ¿Por qué hash_equals() en lugar de ===?
        // hash_equals() tarda el mismo tiempo siempre (previene timing attacks)
        // === puede ser vulnerable a timing attacks (mide tiempo de comparación)
        return hash_equals($_SESSION[self::TOKEN_KEY], $token);
    }

    /**
     * Regenera el token CSRF (útil después de login o cambios importantes)
     * 
     * ¿Cuándo usar?
     * - Después de hacer login
     * - Después de cambiar contraseña
     * - Cualquier acción crítica de seguridad
     * 
     * ¿Por qué regenerar?
     * Para invalidar tokens antiguos y prevenir ataques de replay
     */
    public static function regenerateToken(): void
    {
        // Eliminar el token antiguo
        unset($_SESSION[self::TOKEN_KEY]);
        
        // Generar uno nuevo
        self::generateToken();
    }

    /**
     * Obtiene el campo HTML oculto con el token CSRF
     * 
     * ¿Cómo se usa?
     * En tus formularios HTML:
     * <form method="POST">
     *     <?= CsrfMiddleware::getTokenField() ?>
     *     <input name="nombre" required>
     * </form>
     * 
     * Resultado HTML:
     * <input type="hidden" name="csrf_token" value="abc123...">
     * 
     * @return string HTML del input hidden
     */
    public static function getTokenField(): string
    {
        $token = self::generateToken();
        
        // Escapar el token para prevenir XSS (aunque random_bytes es seguro)
        // htmlspecialchars() convierte caracteres especiales a HTML entities
        // ENT_QUOTES = escapar comillas simples y dobles
        // 'UTF-8' = codificación de caracteres
        $escapedToken = htmlspecialchars($token, ENT_QUOTES, 'UTF-8');
        
        // Retornar el HTML del input hidden
        return '<input type="hidden" name="csrf_token" value="' . $escapedToken . '">';
    }

    /**
     * Obtiene el meta tag HTML con el token CSRF (para peticiones AJAX)
     * 
     * ¿Cómo se usa?
     * En el <head> de tu layout:
     * <head>
     *     <?= CsrfMiddleware::getMetaTag() ?>
     * </head>
     * 
     * Resultado HTML:
     * <meta name="csrf-token" content="abc123...">
     * 
     * Luego en JavaScript:
     * const token = document.querySelector('meta[name="csrf-token"]').content;
     * fetch('/api/endpoint', {
     *     headers: { 'X-CSRF-TOKEN': token }
     * });
     * 
     * @return string HTML del meta tag
     */
    public static function getMetaTag(): string
    {
        $token = self::generateToken();
        $escapedToken = htmlspecialchars($token, ENT_QUOTES, 'UTF-8');
        
        return '<meta name="csrf-token" content="' . $escapedToken . '">';
    }

    /**
     * Obtiene solo el valor del token (sin HTML)
     * 
     * Útil cuando necesitas el token en JavaScript o para casos especiales
     * 
     * @return string Token CSRF
     */
    public static function getToken(): string
    {
        return self::generateToken();
    }
}
