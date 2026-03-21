<?php

namespace App\Controllers;


class AuthController extends BaseController
{
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();

            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if (empty($email) || empty($password)) {
                $this->redirect('/?error=missing_fields');
                return;
            }
            
            // Intentar autenticar al usuario
            $user = $this->authenticateUser($email, $password);
            
            if ($user) {
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
                $this->redirect('/?error=invalid_credentials');
            }
        } else {
            // Si no es POST, redirigir al inicio
            $this->redirect('/');
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
}
