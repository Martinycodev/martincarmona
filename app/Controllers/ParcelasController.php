<?php
namespace App\Controllers;
require_once BASE_PATH . '/config/database.php';

class ParcelasController extends BaseController
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

    public function index()
    {
        $db = \Database::connect();
        $result = $db->query("SELECT * FROM parcelas ORDER BY nombre");
        $parcelas = $result->fetch_all(MYSQLI_ASSOC);
        $db->close();
        $data = [
            'parcelas' => $parcelas,
            'user' => [
                'name' => $_SESSION['user_name'] ?? 'Usuario'
            ]
        ];
        $this->render('parcelas/index', $data);
    }

    public function buscar()
    {
        header('Content-Type: application/json');
        error_log("Método buscar llamado en ParcelasController");
        error_log("Query recibida: " . ($_GET['q'] ?? 'no definida'));

        if (!isset($_GET['q']) || strlen($_GET['q']) < 3) {
            error_log("Query muy corta o no definida");
            echo json_encode([]);
            return;
        }

        try {
            $query = "%" . $_GET['q'] . "%";
            error_log("Buscando parcelas con query: " . $query);
            $stmt = $this->db->prepare("
                SELECT id, nombre, olivos
                FROM parcelas 
                WHERE nombre LIKE ?
                ORDER BY nombre
                LIMIT 10
            ");

            $stmt->bind_param("s", $query);
            $stmt->execute();
            $result = $stmt->get_result();

            $parcelas = [];
            while ($row = $result->fetch_assoc()) {
                $parcelas[] = $row;
            }

            echo json_encode($parcelas);

        } catch (\Exception $e) {
            error_log("Error en búsqueda de parcelas: " . $e->getMessage());
            echo json_encode(['error' => 'Error en la búsqueda']);
        }
    }

    /**
     * Crear una nueva parcela
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
            $ubicacion = trim($input['ubicacion'] ?? '');
            $empresa = trim($input['empresa'] ?? '');
            $propietario = trim($input['propietario'] ?? '');
            $olivos = intval($input['olivos'] ?? 0);
            $hidrante = intval($input['hidrante'] ?? 0);
            $descripcion = trim($input['descripcion'] ?? '');
            $referencia_catastral = trim($input['referencia_catastral'] ?? '');
            $tipo_olivos          = trim($input['tipo_olivos'] ?? '');
            $año_plantacion       = !empty($input['año_plantacion']) ? intval($input['año_plantacion']) : null;
            $tipo_plantacion      = in_array($input['tipo_plantacion'] ?? '', ['tradicional','intensivo','superintensivo'])
                                    ? $input['tipo_plantacion'] : null;
            $riego_secano         = in_array($input['riego_secano'] ?? '', ['riego','secano'])
                                    ? $input['riego_secano'] : null;
            $corta                = in_array($input['corta'] ?? '', ['par','impar','siempre'])
                                    ? $input['corta'] : null;

            if (empty($nombre)) {
                echo json_encode(['success' => false, 'message' => 'El nombre es requerido']);
                return;
            }

            $db = \Database::connect();

            // Verificar si ya existe una parcela con el mismo nombre
            $stmt = $db->prepare("SELECT id FROM parcelas WHERE nombre = ?");
            $stmt->bind_param("s", $nombre);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                echo json_encode(['success' => false, 'message' => 'Ya existe una parcela con ese nombre']);
                return;
            }
            $stmt->close();

            $stmt = $db->prepare("INSERT INTO parcelas (nombre, olivos, ubicacion, empresa, propietario, hidrante, descripcion, referencia_catastral, tipo_olivos, `año_plantacion`, tipo_plantacion, riego_secano, corta, id_user) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sisssiissssssi", $nombre, $olivos, $ubicacion, $empresa, $propietario, $hidrante, $descripcion, $referencia_catastral, $tipo_olivos, $año_plantacion, $tipo_plantacion, $riego_secano, $corta, $_SESSION['user_id']);

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Parcela creada correctamente', 'id' => $db->insert_id]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al crear la parcela: ' . $stmt->error]);
            }

            $stmt->close();
            $db->close();

        } catch (\Exception $e) {
            error_log("Error creando parcela: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }

    /**
     * Obtener una parcela por ID
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
            $stmt = $db->prepare("SELECT * FROM parcelas WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                echo json_encode(['success' => true, 'parcela' => $row]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Parcela no encontrada']);
            }

            $stmt->close();
            $db->close();

        } catch (\Exception $e) {
            error_log("Error obteniendo parcela: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
        }
    }

    /**
     * Actualizar una parcela
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
            $ubicacion = trim($input['ubicacion'] ?? '');
            $empresa = trim($input['empresa'] ?? '');
            $propietario = trim($input['propietario'] ?? '');
            $olivos = intval($input['olivos'] ?? 0);
            $hidrante = intval($input['hidrante'] ?? 0);
            $descripcion = trim($input['descripcion'] ?? '');
            $referencia_catastral = trim($input['referencia_catastral'] ?? '');
            $tipo_olivos          = trim($input['tipo_olivos'] ?? '');
            $año_plantacion       = !empty($input['año_plantacion']) ? intval($input['año_plantacion']) : null;
            $tipo_plantacion      = in_array($input['tipo_plantacion'] ?? '', ['tradicional','intensivo','superintensivo'])
                                    ? $input['tipo_plantacion'] : null;
            $riego_secano         = in_array($input['riego_secano'] ?? '', ['riego','secano'])
                                    ? $input['riego_secano'] : null;
            $corta                = in_array($input['corta'] ?? '', ['par','impar','siempre'])
                                    ? $input['corta'] : null;

            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID no válido']);
                return;
            }

            if (empty($nombre)) {
                echo json_encode(['success' => false, 'message' => 'El nombre es requerido']);
                return;
            }

            $db = \Database::connect();

            // Verificar si ya existe otra parcela con el mismo nombre
            $stmt = $db->prepare("SELECT id FROM parcelas WHERE nombre = ? AND id != ?");
            $stmt->bind_param("si", $nombre, $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                echo json_encode(['success' => false, 'message' => 'Ya existe otra parcela con ese nombre']);
                return;
            }
            $stmt->close();

            $stmt = $db->prepare("UPDATE parcelas SET nombre = ?, olivos = ?, ubicacion = ?, empresa = ?, propietario = ?, hidrante = ?, descripcion = ?, referencia_catastral = ?, tipo_olivos = ?, `año_plantacion` = ?, tipo_plantacion = ?, riego_secano = ?, corta = ? WHERE id = ?");
            $stmt->bind_param("sisssiissssssi", $nombre, $olivos, $ubicacion, $empresa, $propietario, $hidrante, $descripcion, $referencia_catastral, $tipo_olivos, $año_plantacion, $tipo_plantacion, $riego_secano, $corta, $id);

            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    echo json_encode(['success' => true, 'message' => 'Parcela actualizada correctamente']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'No se encontró la parcela o no hubo cambios']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar la parcela: ' . $stmt->error]);
            }

            $stmt->close();
            $db->close();

        } catch (\Exception $e) {
            error_log("Error actualizando parcela: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }

    /**
     * Eliminar una parcela
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

            // Verificar si la parcela está siendo usada en tareas
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM tarea_parcelas WHERE parcela_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            if ($row['count'] > 0) {
                echo json_encode(['success' => false, 'message' => 'No se puede eliminar la parcela porque está asignada a tareas']);
                return;
            }
            $stmt->close();

            $stmt = $db->prepare("DELETE FROM parcelas WHERE id = ?");
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    echo json_encode(['success' => true, 'message' => 'Parcela eliminada correctamente']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'No se encontró la parcela']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar la parcela: ' . $stmt->error]);
            }

            $stmt->close();
            $db->close();

        } catch (\Exception $e) {
            error_log("Error eliminando parcela: " . $e->getMessage());
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
