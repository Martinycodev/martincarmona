<?php

namespace App\Controllers;

require_once BASE_PATH . '/config/database.php';

class AuthController extends BaseController
{
    public function __construct()
    {
        // Restaurar sesión desde cookie si existe y no hay sesión activa
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id']) && isset($_COOKIE['user_id'])) {
            $userId = $_COOKIE['user_id'];
            try {
                $db = \Database::connect();
                $stmt = $db->prepare("SELECT id, name, email FROM usuarios WHERE id = ?");
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows === 1) {
                    $user = $result->fetch_assoc();
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                }
                $stmt->close();
                $db->close();
            } catch (\Exception $e) {
                error_log("Error restaurando sesión desde cookie: " . $e->getMessage());
            }
        }
    }
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if (empty($email) || empty($password)) {
                $this->redirect('/?error=missing_fields');
                return;
            }
            
            // Intentar autenticar al usuario
            $user = $this->authenticateUser($email, $password);
            
            if ($user) {
                // Iniciar sesión
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];

                // Solo guardar cookie si el usuario marcó "Recuérdame"
                if (isset($_POST['remember']) && $_POST['remember'] === 'on') {
                    setcookie('user_id', $user['id'], time() + (30 * 24 * 60 * 60), "/");
                }

                $this->redirect('/dashboard');
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
    session_start();
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
            $stmt = $db->prepare("SELECT id, name, email, password FROM usuarios WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                // Verificar contraseña (hash bcrypt)
                if (password_verify($password, $user['password'])) {
                    $stmt->close();
                    $db->close();
                    return $user;
                }
            }
            
            $stmt->close();
            $db->close();
            return false;
            
        } catch (\Exception $e) {
            error_log("Error en autenticación: " . $e->getMessage());
            return false;
        }
    }
}
