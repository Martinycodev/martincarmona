<?php

namespace App\Controllers;

require_once BASE_PATH . '/config/database.php';

class PerfilController extends BaseController
{
    public function __construct()
    {
        // Iniciar sesión si no está iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function index()
    {
        // Verificar si el usuario está autenticado
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/');
            return;
        }
        
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
            error_log("Error actualizando nombre: " . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Error en el servidor'
            ]);
        }
    }
}

