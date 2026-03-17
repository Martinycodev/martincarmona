<?php

namespace App\Controllers;


class PerfilController extends BaseController
{
    public function __construct()
    {
    }

    public function index()
    {
        $this->requireEmpresa();

        // Obtener información del usuario desde la sesión
        $userId = $_SESSION['user_id'];
        $userName = $_SESSION['user_name'] ?? '';
        $userEmail = $_SESSION['user_email'] ?? '';
        
        // Preparar datos para la vista
        $data = [
            'user' => [
                'id' => $userId,
                'name' => $userName,
                'email' => $userEmail
            ]
        ];
        
        $this->render('perfil/index', $data);
    }

    public function actualizarNombre()
    {
        // Verificar si el usuario está autenticado
        if (!isset($_SESSION['user_id'])) {
            $this->json([
                'success' => false,
                'message' => 'No autorizado'
            ]);
            return;
        }

        // Verificar que sea una petición POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json([
                'success' => false,
                'message' => 'Método no permitido'
            ]);
            return;
        }

        $this->validateCsrf();

        // Obtener datos del cuerpo de la petición
        $input = json_decode(file_get_contents('php://input'), true);
        $nuevoNombre = trim($input['nombre'] ?? '');

        // Validar que el nombre no esté vacío
        if (empty($nuevoNombre)) {
            $this->json([
                'success' => false,
                'message' => 'El nombre no puede estar vacío'
            ]);
            return;
        }

        // Validar longitud del nombre
        if (strlen($nuevoNombre) > 255) {
            $this->json([
                'success' => false,
                'message' => 'El nombre es demasiado largo'
            ]);
            return;
        }

        try {
            $db = \Database::connect();
            $userId = $_SESSION['user_id'];
            
            // Actualizar el nombre en la base de datos
            $stmt = $db->prepare("UPDATE usuarios SET name = ? WHERE id = ?");
            $stmt->bind_param("si", $nuevoNombre, $userId);
            
            if ($stmt->execute()) {
                // Actualizar la sesión con el nuevo nombre
                $_SESSION['user_name'] = $nuevoNombre;
                
                $stmt->close();
                $db->close();
                
                $this->json([
                    'success' => true,
                    'message' => 'Nombre actualizado correctamente',
                    'nombre' => $nuevoNombre
                ]);
            } else {
                $stmt->close();
                $db->close();
                
                $this->json([
                    'success' => false,
                    'message' => 'Error al actualizar el nombre'
                ]);
            }
        } catch (\Exception $e) {
            \Core\Logger::app()->error("Error actualizando nombre: " . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Error en el servidor'
            ]);
        }
    }

    /**
     * Cambiar contraseña del usuario
     */
    public function cambiarPassword()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->json(['success' => false, 'message' => 'No autorizado']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $this->validateCsrf();

        $input = json_decode(file_get_contents('php://input'), true);
        $actual = $input['password_actual'] ?? '';
        $nueva = $input['password_nueva'] ?? '';
        $confirmacion = $input['password_confirmacion'] ?? '';

        // Validaciones
        if (empty($actual) || empty($nueva) || empty($confirmacion)) {
            $this->json(['success' => false, 'message' => 'Todos los campos son obligatorios']);
            return;
        }

        if ($nueva !== $confirmacion) {
            $this->json(['success' => false, 'message' => 'La nueva contraseña y su confirmación no coinciden']);
            return;
        }

        if (strlen($nueva) < 6) {
            $this->json(['success' => false, 'message' => 'La nueva contraseña debe tener al menos 6 caracteres']);
            return;
        }

        try {
            $db = \Database::connect();
            $userId = $_SESSION['user_id'];

            // Obtener contraseña actual de la BD
            $stmt = $db->prepare("SELECT password FROM usuarios WHERE id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            if (!$user) {
                $db->close();
                $this->json(['success' => false, 'message' => 'Usuario no encontrado']);
                return;
            }

            // Verificar contraseña actual
            if (!password_verify($actual, $user['password'])) {
                $db->close();
                $this->json(['success' => false, 'message' => 'La contraseña actual es incorrecta']);
                return;
            }

            // Hash de la nueva contraseña
            $hash = password_hash($nueva, PASSWORD_BCRYPT);

            $stmt = $db->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hash, $userId);

            if ($stmt->execute()) {
                $stmt->close();
                $db->close();
                $this->json(['success' => true, 'message' => 'Contraseña actualizada correctamente']);
            } else {
                $stmt->close();
                $db->close();
                $this->json(['success' => false, 'message' => 'Error al actualizar la contraseña']);
            }
        } catch (\Exception $e) {
            \Core\Logger::app()->error("Error cambiando contraseña: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error en el servidor']);
        }
    }
}

