<?php
namespace App\Controllers;
require_once BASE_PATH . '/config/database.php';
class TrabajadoresController extends BaseController
{
    private $db;
    
    public function __construct()
    {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /');
            exit;
        }
        $this->db = \Database::connect();
    }
    
    public function buscar()
    {
        header('Content-Type: application/json');
        error_log("Método buscar llamado en TrabajadoresController");
        error_log("Query recibida: " . ($_GET['q'] ?? 'no definida'));
        
        if (!isset($_GET['q']) || strlen($_GET['q']) < 3) {
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
            error_log("Buscando trabajadores con query: " . $query);
            
            // Verificar que la tabla existe
            $tableCheck = $this->db->query("SHOW TABLES LIKE 'trabajadores'");
            if ($tableCheck->num_rows == 0) {
                throw new \Exception("La tabla 'trabajadores' no existe");
            }
            
            // Preparar y ejecutar la consulta
            $stmt = $this->db->prepare("
                SELECT id, nombre, dni, ss
                FROM trabajadores 
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
            
            $trabajadores = [];
            while ($row = $result->fetch_assoc()) {
                $trabajadores[] = $row;
            }
            
            error_log("Trabajadores encontrados: " . json_encode($trabajadores));
            echo json_encode($trabajadores);
            
        } catch (\Exception $e) {
            error_log("Error en búsqueda de trabajadores: " . $e->getMessage());
            echo json_encode(['error' => 'Error en la búsqueda: ' . $e->getMessage()]);
        }
    }

    public function index()
    {
        $db = \Database::connect();
        $result = $db->query("SELECT * FROM trabajadores ORDER BY nombre");
        $trabajadores = $result->fetch_all(MYSQLI_ASSOC);
        $db->close();
        $data = [
            'trabajadores' => $trabajadores,
            'user' => [
                'name' => $_SESSION['user_name'] ?? 'Usuario'
            ]
        ];
        $this->render('trabajadores/index', $data);
    }
    
    /**
     * Crear un nuevo trabajador
     */
    public function crear()
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                echo json_encode(['success' => false, 'message' => 'Datos no válidos']);
                return;
            }
            
            $nombre = trim($input['nombre'] ?? '');
            $dni = trim($input['dni'] ?? '');
            $ss = trim($input['ss'] ?? '');
            
            if (empty($nombre)) {
                echo json_encode(['success' => false, 'message' => 'El nombre es requerido']);
                return;
            }
            
            $db = \Database::connect();
            
            // Verificar si ya existe un trabajador con el mismo DNI (si se proporciona)
            if (!empty($dni)) {
                $stmt = $db->prepare("SELECT id FROM trabajadores WHERE dni = ?");
                $stmt->bind_param("s", $dni);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    echo json_encode(['success' => false, 'message' => 'Ya existe un trabajador con ese DNI']);
                    return;
                }
                $stmt->close();
            }
            
            $stmt = $db->prepare("INSERT INTO trabajadores (nombre, dni, ss, id_user) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssi", $nombre, $dni, $ss, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Trabajador creado correctamente', 'id' => $db->insert_id]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al crear el trabajador: ' . $stmt->error]);
            }
            
            $stmt->close();
            $db->close();
            
        } catch (\Exception $e) {
            error_log("Error creando trabajador: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Obtener un trabajador por ID
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
            $stmt = $db->prepare("SELECT * FROM trabajadores WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                echo json_encode(['success' => true, 'trabajador' => $row]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Trabajador no encontrado']);
            }
            
            $stmt->close();
            $db->close();
            
        } catch (\Exception $e) {
            error_log("Error obteniendo trabajador: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
        }
    }
    
    /**
     * Actualizar un trabajador
     */
    public function actualizar()
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        
        try {
            error_log("Método actualizar llamado");
            $input = json_decode(file_get_contents('php://input'), true);
            error_log("Input recibido: " . json_encode($input));
            
            if (!$input) {
                echo json_encode(['success' => false, 'message' => 'Datos no válidos']);
                return;
            }
            
            $id = intval($input['id'] ?? 0);
            $nombre = trim($input['nombre'] ?? '');
            $dni = trim($input['dni'] ?? '');
            $ss = trim($input['ss'] ?? '');
            
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID no válido']);
                return;
            }
            
            if (empty($nombre)) {
                echo json_encode(['success' => false, 'message' => 'El nombre es requerido']);
                return;
            }
            
            $db = \Database::connect();
            error_log("Conexión a BD establecida");
            
            // Verificar si ya existe otro trabajador con el mismo DNI (si se proporciona)
            if (!empty($dni)) {
                error_log("Verificando DNI duplicado: $dni para ID: $id");
                $stmt = $db->prepare("SELECT id FROM trabajadores WHERE dni = ? AND id != ?");
                if (!$stmt) {
                    throw new \Exception("Error preparando consulta DNI: " . $db->error);
                }
                $stmt->bind_param("si", $dni, $id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $stmt->close();
                    $db->close();
                    echo json_encode(['success' => false, 'message' => 'Ya existe otro trabajador con ese DNI']);
                    return;
                }
                $stmt->close();
                error_log("DNI verificado - no hay duplicados");
            }
            
            error_log("Preparando consulta UPDATE");
            $stmt = $db->prepare("UPDATE trabajadores SET nombre = ?, dni = ?, ss = ? WHERE id = ?");
            if (!$stmt) {
                throw new \Exception("Error preparando consulta UPDATE: " . $db->error);
            }
            error_log("Binding parameters: nombre=$nombre, dni=$dni, ss=$ss, id=$id");
            $stmt->bind_param("sssi", $nombre, $dni, $ss, $id);
            
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    echo json_encode(['success' => true, 'message' => 'Trabajador actualizado correctamente']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'No se encontró el trabajador o no hubo cambios']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar el trabajador: ' . $stmt->error]);
            }
            
            $stmt->close();
            $db->close();
            
        } catch (\Exception $e) {
            error_log("Error actualizando trabajador: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Eliminar un trabajador
     */
    public function eliminar()
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        
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
            
            // Verificar si el trabajador está siendo usado en tareas
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM tarea_trabajadores WHERE trabajador_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            if ($row['count'] > 0) {
                echo json_encode(['success' => false, 'message' => 'No se puede eliminar el trabajador porque está asignado a tareas']);
                return;
            }
            $stmt->close();
            
            $stmt = $db->prepare("DELETE FROM trabajadores WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    echo json_encode(['success' => true, 'message' => 'Trabajador eliminado correctamente']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'No se encontró el trabajador']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar el trabajador: ' . $stmt->error]);
            }
            
            $stmt->close();
            $db->close();
            
        } catch (\Exception $e) {
            error_log("Error eliminando trabajador: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
        }
    }

    public function __destruct()
    {
        if ($this->db) {
            $this->db->close();
        }
    }
}
