<?php
namespace App\Controllers;
require_once BASE_PATH . '/config/database.php';
class TrabajosController extends BaseController
{
    private $db;
    
    public function __construct()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /');
            exit;
        }
        $this->db = \Database::connect();
    }
    
    public function buscar()
    {
        header('Content-Type: application/json');
        error_log("Método buscar llamado en TrabajosController");
        error_log("Query recibida: " . ($_GET['q'] ?? 'no definida'));
        
        if (!isset($_GET['q']) || strlen($_GET['q']) < 2) {
            error_log("Query muy corta o no definida");
            echo json_encode([]);
            return;
        }

        try {
            // Asegurar que tenemos una conexión a la base de datos
            if (!$this->db || $this->db->connect_error) {
                $this->db = \Database::connect();
            }
            
            $query = "%" . $_GET['q'] . "%";
            error_log("Buscando trabajos con query: " . $query);
            
            $stmt = $this->db->prepare("
                SELECT id, nombre
                FROM trabajos 
                WHERE nombre LIKE ?
                ORDER BY nombre
                LIMIT 10
            ");
            
            if (!$stmt) {
                throw new \Exception("Error en la preparación de la consulta: " . $this->db->error);
            }
            
            $stmt->bind_param("s", $query);
            
            if (!$stmt->execute()) {
                throw new \Exception("Error al ejecutar la consulta: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            
            $trabajos = [];
            while ($row = $result->fetch_assoc()) {
                $trabajos[] = $row;
            }
            
            error_log("Trabajos encontrados: " . json_encode($trabajos));
            echo json_encode($trabajos);
            
        } catch (\Exception $e) {
            error_log("Error en búsqueda de trabajos: " . $e->getMessage());
            echo json_encode(['error' => 'Error en la búsqueda: ' . $e->getMessage()]);
        }
    }
    public function index()
    {
        $db = \Database::connect();
        $stmt = $db->prepare("SELECT * FROM trabajos WHERE id_user = ? ORDER BY nombre");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $trabajos = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $db->close();
        
        $data = [
            'trabajos' => $trabajos,
            'user' => [
                'name' => $_SESSION['user_name'] ?? 'Usuario'
            ]
        ];
        $this->render('trabajos/index', $data);
    }
    
    /**
     * Crear un nuevo trabajo
     */
    public function crear()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $this->validateCsrf();

        try {
            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input) {
                echo json_encode(['success' => false, 'message' => 'Datos no válidos']);
                return;
            }
            
            $nombre = trim($input['nombre'] ?? '');
            $descripcion = trim($input['descripcion'] ?? '');
            $precio_hora = floatval($input['precio_hora'] ?? 0);
            
            if (empty($nombre)) {
                echo json_encode(['success' => false, 'message' => 'El nombre es requerido']);
                return;
            }
            
            if ($precio_hora < 0) {
                echo json_encode(['success' => false, 'message' => 'El precio por hora debe ser mayor o igual a 0']);
                return;
            }
            
            $db = \Database::connect();
            
            // Verificar si ya existe un trabajo con el mismo nombre para este usuario
            $stmt = $db->prepare("SELECT id FROM trabajos WHERE nombre = ? AND id_user = ?");
            $stmt->bind_param("si", $nombre, $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                echo json_encode(['success' => false, 'message' => 'Ya existe un trabajo con ese nombre']);
                return;
            }
            $stmt->close();
            
            $stmt = $db->prepare("INSERT INTO trabajos (nombre, descripcion, precio_hora, id_user) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssdi", $nombre, $descripcion, $precio_hora, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Trabajo creado correctamente', 'id' => $db->insert_id]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al crear el trabajo: ' . $stmt->error]);
            }
            
            $stmt->close();
            $db->close();
            
        } catch (\Exception $e) {
            error_log("Error creando trabajo: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
        }
    }
    
    /**
     * Obtener un trabajo por ID
     */
    public function obtener()
    {
        header('Content-Type: application/json');
        
        // Obtener ID del parámetro GET
        $id = $_GET['id'] ?? null;
        
        if (!$id || !is_numeric($id)) {
            echo json_encode(['success' => false, 'message' => 'ID no válido']);
            return;
        }
        
        try {
            $db = \Database::connect();
            $stmt = $db->prepare("SELECT * FROM trabajos WHERE id = ? AND id_user = ?");
            $stmt->bind_param("ii", $id, $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                echo json_encode(['success' => true, 'trabajo' => $row]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Trabajo no encontrado']);
            }
            
            $stmt->close();
            $db->close();
            
        } catch (\Exception $e) {
            error_log("Error obteniendo trabajo: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
        }
    }
    
    /**
     * Actualizar un trabajo
     */
    public function actualizar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $this->validateCsrf();

        try {
            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input) {
                echo json_encode(['success' => false, 'message' => 'Datos no válidos']);
                return;
            }
            
            $id = intval($input['id'] ?? 0);
            $nombre = trim($input['nombre'] ?? '');
            $descripcion = trim($input['descripcion'] ?? '');
            $precio_hora = floatval($input['precio_hora'] ?? 0);
            
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID no válido']);
                return;
            }
            
            if (empty($nombre)) {
                echo json_encode(['success' => false, 'message' => 'El nombre es requerido']);
                return;
            }
            
            if ($precio_hora < 0) {
                echo json_encode(['success' => false, 'message' => 'El precio por hora debe ser mayor o igual a 0']);
                return;
            }
            
            $db = \Database::connect();
            
            // Verificar si ya existe otro trabajo con el mismo nombre para este usuario
            $stmt = $db->prepare("SELECT id FROM trabajos WHERE nombre = ? AND id != ? AND id_user = ?");
            $stmt->bind_param("sii", $nombre, $id, $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                echo json_encode(['success' => false, 'message' => 'Ya existe otro trabajo con ese nombre']);
                return;
            }
            $stmt->close();
            
            $stmt = $db->prepare("UPDATE trabajos SET nombre = ?, descripcion = ?, precio_hora = ? WHERE id = ? AND id_user = ?");
            $stmt->bind_param("ssdii", $nombre, $descripcion, $precio_hora, $id, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    echo json_encode(['success' => true, 'message' => 'Trabajo actualizado correctamente']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'No se encontró el trabajo o no hubo cambios']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar el trabajo: ' . $stmt->error]);
            }
            
            $stmt->close();
            $db->close();
            
        } catch (\Exception $e) {
            error_log("Error actualizando trabajo: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
        }
    }
    
    /**
     * Eliminar un trabajo
     */
    public function eliminar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $this->validateCsrf();

        try {
            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input) {
                echo json_encode(['success' => false, 'message' => 'Datos no válidos']);
                return;
            }
            
            $id = intval($input['id'] ?? 0);
            
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID no válido']);
                return;
            }
            
            $db = \Database::connect();
            
            // Verificar si el trabajo está siendo usado en tareas
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM tarea_trabajos WHERE trabajo_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            if ($row['count'] > 0) {
                echo json_encode(['success' => false, 'message' => 'No se puede eliminar el trabajo porque está asignado a tareas']);
                return;
            }
            $stmt->close();
            
            $stmt = $db->prepare("DELETE FROM trabajos WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    echo json_encode(['success' => true, 'message' => 'Trabajo eliminado correctamente']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'No se encontró el trabajo']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar el trabajo: ' . $stmt->error]);
            }
            
            $stmt->close();
            $db->close();
            
        } catch (\Exception $e) {
            error_log("Error eliminando trabajo: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
        }
    }
}
