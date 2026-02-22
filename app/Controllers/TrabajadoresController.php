<?php
namespace App\Controllers;
require_once BASE_PATH . '/config/database.php';
class TrabajadoresController extends BaseController
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
        error_log("Método buscar llamado en TrabajadoresController");
        error_log("Query recibida: " . ($_GET['q'] ?? 'no definida'));

        if (!isset($_GET['q']) || strlen($_GET['q']) < 3) {
            error_log("Query muy corta o no definida");
            echo json_encode([]);
            return;
        }

        try {
            if (!$this->db || $this->db->connect_error) {
                $this->db = \Database::connect();
            }

            $query = "%" . $_GET['q'] . "%";
            error_log("Buscando trabajadores con query: " . $query);

            $tableCheck = $this->db->query("SHOW TABLES LIKE 'trabajadores'");
            if ($tableCheck->num_rows == 0) {
                throw new \Exception("La tabla 'trabajadores' no existe");
            }

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

        $this->validateCsrf();

        try {
            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input) {
                echo json_encode(['success' => false, 'message' => 'Datos no válidos']);
                return;
            }

            $nombre    = trim($input['nombre'] ?? '');
            $dni       = trim($input['dni'] ?? '');
            $ss        = trim($input['ss'] ?? '');
            $alta_ss   = !empty($input['alta_ss']) ? $input['alta_ss'] : null;
            $cuadrilla = !empty($input['cuadrilla']) ? 1 : 0;

            if (empty($nombre)) {
                echo json_encode(['success' => false, 'message' => 'El nombre es requerido']);
                return;
            }

            $db = \Database::connect();

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

            $stmt = $db->prepare("INSERT INTO trabajadores (nombre, dni, ss, alta_ss, cuadrilla, id_user) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssii", $nombre, $dni, $ss, $alta_ss, $cuadrilla, $_SESSION['user_id']);

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

        $this->validateCsrf();

        try {
            error_log("Método actualizar llamado");
            $input = json_decode(file_get_contents('php://input'), true);
            error_log("Input recibido: " . json_encode($input));

            if (!$input) {
                echo json_encode(['success' => false, 'message' => 'Datos no válidos']);
                return;
            }

            $id        = intval($input['id'] ?? 0);
            $nombre    = trim($input['nombre'] ?? '');
            $dni       = trim($input['dni'] ?? '');
            $ss        = trim($input['ss'] ?? '');
            $alta_ss   = !empty($input['alta_ss']) ? $input['alta_ss'] : null;
            $cuadrilla = !empty($input['cuadrilla']) ? 1 : 0;

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
            $stmt = $db->prepare("UPDATE trabajadores SET nombre = ?, dni = ?, ss = ?, alta_ss = ?, cuadrilla = ? WHERE id = ?");
            if (!$stmt) {
                throw new \Exception("Error preparando consulta UPDATE: " . $db->error);
            }
            $stmt->bind_param("ssssii", $nombre, $dni, $ss, $alta_ss, $cuadrilla, $id);

            if ($stmt->execute()) {
                if ($stmt->affected_rows >= 0) {
                    echo json_encode(['success' => true, 'message' => 'Trabajador actualizado correctamente']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'No se encontró el trabajador']);
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
     * Subir foto de perfil de un trabajador
     */
    public function subirFoto()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $this->validateCsrf();

        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID no válido']);
            return;
        }

        if (empty($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'No se recibió ningún archivo válido']);
            return;
        }

        $file     = $_FILES['foto'];
        $allowed  = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $mimeType = mime_content_type($file['tmp_name']);

        if (!in_array($mimeType, $allowed)) {
            echo json_encode(['success' => false, 'message' => 'Formato de imagen no permitido']);
            return;
        }

        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $uploadDir = BASE_PATH . '/public/uploads/trabajadores/' . $id . '/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Eliminar foto anterior si existe
        foreach (glob($uploadDir . 'foto.*') as $old) {
            unlink($old);
        }

        $filename = 'foto.' . strtolower($ext);
        $dest     = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            echo json_encode(['success' => false, 'message' => 'Error al guardar la imagen']);
            return;
        }

        $fotoPath = '/public/uploads/trabajadores/' . $id . '/' . $filename;

        try {
            $db   = \Database::connect();
            $stmt = $db->prepare("UPDATE trabajadores SET foto = ? WHERE id = ? AND id_user = ?");
            $stmt->bind_param("sii", $fotoPath, $id, $_SESSION['user_id']);
            $stmt->execute();
            $stmt->close();
            $db->close();

            echo json_encode(['success' => true, 'foto' => $fotoPath]);
        } catch (\Exception $e) {
            error_log("Error guardando foto: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error al actualizar la base de datos']);
        }
    }

    /**
     * Obtener trabajadores de la cuadrilla (cuadrilla = 1)
     */
    public function obtenerCuadrilla()
    {
        header('Content-Type: application/json');

        try {
            $db   = \Database::connect();
            $stmt = $db->prepare("SELECT id, nombre FROM trabajadores WHERE cuadrilla = 1 AND id_user = ? ORDER BY nombre");
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();

            $trabajadores = [];
            while ($row = $result->fetch_assoc()) {
                $trabajadores[] = $row;
            }

            $stmt->close();
            $db->close();

            echo json_encode(['success' => true, 'trabajadores' => $trabajadores]);
        } catch (\Exception $e) {
            error_log("Error obteniendo cuadrilla: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
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

            // Eliminar foto de perfil si existe
            $uploadDir = BASE_PATH . '/public/uploads/trabajadores/' . $id . '/';
            if (is_dir($uploadDir)) {
                foreach (glob($uploadDir . '*') as $file) {
                    unlink($file);
                }
                rmdir($uploadDir);
            }

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
