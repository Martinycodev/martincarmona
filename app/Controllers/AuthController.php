<?php

namespace App\Controllers;


class AuthController extends BaseController
{
    // Rate limiting: máximo de intentos y ventana de tiempo
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOCKOUT_MINUTES = 15;

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();

            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

            if (empty($email) || empty($password)) {
                $this->redirect('/login?error=missing_fields');
                return;
            }

            // Rate limiting: comprobar intentos fallidos de esta IP
            if ($this->isRateLimited($ip)) {
                \Core\Logger::security()->warning("Login bloqueado por rate limiting", [
                    'ip' => $ip,
                    'email' => $email
                ]);
                $this->redirect('/login?error=too_many_attempts');
                return;
            }

            // Intentar autenticar al usuario
            $user = $this->authenticateUser($email, $password);

            if ($user) {
                // Login exitoso: limpiar intentos fallidos de esta IP
                $this->clearLoginAttempts($ip);

                // Regenerar ID para prevenir session fixation tras autenticación
                session_regenerate_id(true);
                $_SESSION['user_id']             = $user['id'];
                $_SESSION['user_name']           = $user['name'];
                $_SESSION['user_email']          = $user['email'];
                $_SESSION['user_rol']            = $user['rol'] ?? 'empresa';
                $_SESSION['user_propietario_id'] = $user['propietario_id'] ?? null;
                $_SESSION['user_trabajador_id']  = $user['trabajador_id'] ?? null;

                // Registrar último login (fecha + IP)
                $this->registrarLogin($user['id']);

                // Solo guardar cookie si el usuario marcó "Recuérdame"
                if (isset($_POST['remember']) && $_POST['remember'] === 'on') {
                    setcookie('user_id', $user['id'], time() + (30 * 24 * 60 * 60), "/");
                }

                $redirects = [
                    'empresa'     => '/dashboard',
                    'admin'       => '/admin/usuarios',
                    'propietario' => '/propietario',
                    'trabajador'  => '/trabajador',
                ];
                $this->redirect($redirects[$_SESSION['user_rol']] ?? '/dashboard');
            } else {
                // Login fallido: registrar intento
                $this->recordLoginAttempt($ip, $email);
                $this->redirect('/login?error=invalid_credentials');
            }
        } else {
            // Si no es POST, redirigir al formulario de login
            $this->redirect('/login');
        }
    }
    
    public function logout()
    {
    session_destroy();
    // Eliminar la cookie
    setcookie('user_id', '', time() - 3600, "/");
    $this->redirect('/');
    }
    
    private function authenticateUser($email, $password)
    {
        try {
            $db = \Database::connect();
            
            // Buscar usuario por email
            $stmt = $db->prepare("SELECT id, name, email, password, rol, propietario_id, trabajador_id FROM usuarios WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                // Verificar contraseña (hash bcrypt)
                if (password_verify($password, $user['password'])) {
                    $stmt->close();
                    return $user;
                }
            }

            $stmt->close();
            return false;

        } catch (\Throwable $e) {
            \Core\Logger::security()->error("Error en autenticación: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Registrar fecha e IP del último login en la tabla usuarios
     */
    private function registrarLogin($userId)
    {
        try {
            $db = \Database::connect();
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $stmt = $db->prepare("UPDATE usuarios SET ultimo_login = NOW(), ultimo_login_ip = ? WHERE id = ?");
            $stmt->bind_param("si", $ip, $userId);
            $stmt->execute();
            $stmt->close();
        } catch (\Throwable $e) {
            // No bloquear el login si falla el registro
            \Core\Logger::app()->error("Error registrando login: " . $e->getMessage());
        }
    }

    /**
     * Comprobar si una IP ha superado el límite de intentos de login
     */
    private function isRateLimited(string $ip): bool
    {
        try {
            $db = \Database::connect();
            $stmt = $db->prepare(
                "SELECT COUNT(*) as intentos FROM login_attempts
                 WHERE ip_address = ? AND attempted_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)"
            );
            $minutes = self::LOCKOUT_MINUTES;
            $stmt->bind_param("si", $ip, $minutes);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            return ($result['intentos'] ?? 0) >= self::MAX_LOGIN_ATTEMPTS;
        } catch (\Throwable $e) {
            // Si falla la comprobación, no bloquear el login (fail-open)
            \Core\Logger::app()->error("Error comprobando rate limit: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Registrar un intento fallido de login
     */
    private function recordLoginAttempt(string $ip, string $email): void
    {
        try {
            $db = \Database::connect();
            $stmt = $db->prepare("INSERT INTO login_attempts (ip_address, email) VALUES (?, ?)");
            $stmt->bind_param("ss", $ip, $email);
            $stmt->execute();
            $stmt->close();

            \Core\Logger::security()->info("Intento de login fallido", [
                'ip' => $ip,
                'email' => $email
            ]);
        } catch (\Throwable $e) {
            \Core\Logger::app()->error("Error registrando intento login: " . $e->getMessage());
        }
    }

    /**
     * Limpiar intentos fallidos tras login exitoso
     */
    private function clearLoginAttempts(string $ip): void
    {
        try {
            $db = \Database::connect();
            $stmt = $db->prepare("DELETE FROM login_attempts WHERE ip_address = ?");
            $stmt->bind_param("s", $ip);
            $stmt->execute();
            $stmt->close();
        } catch (\Throwable $e) {
            // No bloquear el login si falla la limpieza
            \Core\Logger::app()->error("Error limpiando login attempts: " . $e->getMessage());
        }
    }
}
