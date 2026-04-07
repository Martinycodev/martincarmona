<?php

namespace App\Modules\Planner\Support;

/**
 * Csrf
 * ----
 * Helper minimalista de protección CSRF basado en sesión.
 *
 * Funcionamiento:
 *   1. token() genera (o devuelve si ya existe) un token aleatorio
 *      almacenado en $_SESSION['planner_csrf'].
 *   2. El formulario incluye ese token como input oculto.
 *   3. validate() compara el token recibido contra el de sesión usando
 *      hash_equals (timing-safe).
 *
 * Por simplicidad el token vive durante toda la sesión. Si quisieras
 * un token por petición, regenerarías tras cada validate(), pero eso
 * complica formularios con varias pestañas abiertas.
 */
class Csrf
{
    /** Devuelve el token actual de la sesión, o crea uno nuevo. */
    public static function token(): string
    {
        if (empty($_SESSION['planner_csrf'])) {
            $_SESSION['planner_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['planner_csrf'];
    }

    /** Valida el token recibido contra el de la sesión. */
    public static function validate(?string $provided): bool
    {
        if (!is_string($provided) || $provided === '') {
            return false;
        }
        $expected = $_SESSION['planner_csrf'] ?? '';
        return $expected !== '' && hash_equals($expected, $provided);
    }

    /** Devuelve el HTML de un input oculto listo para meter en un <form>. */
    public static function field(): string
    {
        $token = htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8');
        return '<input type="hidden" name="_csrf" value="' . $token . '">';
    }
}
