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
        $result = $db->query("
            SELECT p.*, pr.nombre as propietario_nombre, pr.apellidos as propietario_apellidos
            FROM parcelas p
            LEFT JOIN propietarios pr ON p.propietario_id = pr.id
            WHERE p.id_user = {$_SESSION['user_id']}
            ORDER BY p.nombre
        ");
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

    public function obtenerListaPropietarios()
    {
        header('Content-Type: application/json');
        try {
            $db = \Database::connect();
            $stmt = $db->prepare("SELECT id, nombre, apellidos FROM propietarios WHERE id_user = ? ORDER BY apellidos, nombre");
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $propietarios = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            $db->close();
            echo json_encode(['success' => true, 'propietarios' => $propietarios]);
        } catch (\Exception $e) {
            error_log("Error obteniendo propietarios: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno']);
        }
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
            $propietario_id = !empty($input['propietario_id']) ? intval($input['propietario_id']) : null;
            $olivos = intval($input['olivos'] ?? 0);
            $hidrante = intval($input['hidrante'] ?? 0);
            $descripcion = trim($input['descripcion'] ?? '');
            $referencia_catastral = !empty(trim($input['referencia_catastral'] ?? '')) ? trim($input['referencia_catastral']) : null;
            $tipo_olivos          = !empty(trim($input['tipo_olivos'] ?? '')) ? trim($input['tipo_olivos']) : null;
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

            $stmt = $db->prepare("INSERT INTO parcelas (nombre, olivos, ubicacion, empresa, propietario, propietario_id, hidrante, descripcion, referencia_catastral, tipo_olivos, `año_plantacion`, tipo_plantacion, riego_secano, corta, id_user) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sissssissssssssi", $nombre, $olivos, $ubicacion, $empresa, $propietario, $propietario_id, $hidrante, $descripcion, $referencia_catastral, $tipo_olivos, $año_plantacion, $tipo_plantacion, $riego_secano, $corta, $_SESSION['user_id']);

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
            $propietario_id = !empty($input['propietario_id']) ? intval($input['propietario_id']) : null;
            $olivos = intval($input['olivos'] ?? 0);
            $hidrante = intval($input['hidrante'] ?? 0);
            $descripcion = trim($input['descripcion'] ?? '');
            $referencia_catastral = !empty(trim($input['referencia_catastral'] ?? '')) ? trim($input['referencia_catastral']) : null;
            $tipo_olivos          = !empty(trim($input['tipo_olivos'] ?? '')) ? trim($input['tipo_olivos']) : null;
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

            $stmt = $db->prepare("UPDATE parcelas SET nombre = ?, olivos = ?, ubicacion = ?, empresa = ?, propietario = ?, propietario_id = ?, hidrante = ?, descripcion = ?, referencia_catastral = ?, tipo_olivos = ?, `año_plantacion` = ?, tipo_plantacion = ?, riego_secano = ?, corta = ? WHERE id = ?");
            $stmt->bind_param("sissssissssssssi", $nombre, $olivos, $ubicacion, $empresa, $propietario, $propietario_id, $hidrante, $descripcion, $referencia_catastral, $tipo_olivos, $año_plantacion, $tipo_plantacion, $riego_secano, $corta, $id);

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

    /**
     * Ficha individual de una parcela con documentos y resumen de coste acumulado
     */
    public function detalle()
    {
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            header('Location: /datos/parcelas');
            exit;
        }

        $db = \Database::connect();

        // Cargar parcela con propietario
        $stmt = $db->prepare("
            SELECT p.*, pr.nombre as propietario_nombre, pr.apellidos as propietario_apellidos,
                   pr.telefono as propietario_telefono, pr.email as propietario_email
            FROM parcelas p
            LEFT JOIN propietarios pr ON p.propietario_id = pr.id
            WHERE p.id = ? AND p.id_user = ?
        ");
        $stmt->bind_param("ii", $id, $_SESSION['user_id']);
        $stmt->execute();
        $parcela = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$parcela) {
            header('Location: /datos/parcelas');
            exit;
        }

        // Cargar documentos
        $stmt = $db->prepare("SELECT * FROM documentos_parcelas WHERE parcela_id = ? AND id_user = ? ORDER BY created_at DESC");
        $stmt->bind_param("ii", $id, $_SESSION['user_id']);
        $stmt->execute();
        $documentos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Coste acumulado: suma de horas_asignadas * precio_hora por parcela
        // Estructura real confirmada leyendo PagoMensual.php:
        // - tarea_parcelas: tarea_id, parcela_id
        // - tarea_trabajadores: tarea_id, trabajador_id, horas_asignadas
        // - tarea_trabajos: tarea_id, trabajo_id, precio_hora (snapshot)
        // - trabajos: id, precio_hora (fallback)
        $stmt = $db->prepare("
            SELECT COALESCE(
                SUM(tt.horas_asignadas * COALESCE(ttrab.precio_hora, trab.precio_hora, 0)),
                0
            ) as coste_acumulado
            FROM tarea_parcelas tp
            JOIN tareas ta ON tp.tarea_id = ta.id
            JOIN tarea_trabajadores tt ON ta.id = tt.tarea_id
            LEFT JOIN tarea_trabajos ttrab ON ta.id = ttrab.tarea_id
            LEFT JOIN trabajos trab ON ttrab.trabajo_id = trab.id
            WHERE tp.parcela_id = ? AND ta.id_user = ?
        ");
        $stmt->bind_param("ii", $id, $_SESSION['user_id']);
        $stmt->execute();
        $coste_row = $stmt->get_result()->fetch_assoc();
        $coste_acumulado = $coste_row['coste_acumulado'] ?? 0;
        $stmt->close();
        $db->close();

        $this->render('parcelas/detalle', [
            'parcela' => $parcela,
            'documentos' => $documentos,
            'coste_acumulado' => $coste_acumulado,
            'user' => ['name' => $_SESSION['user_name'] ?? 'Usuario']
        ]);
    }

    /**
     * Subir un documento adjunto a una parcela
     */
    public function subirDocumento()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        $this->validateCsrf();

        $parcela_id = intval($_POST['parcela_id'] ?? 0);
        $tipo = in_array($_POST['tipo'] ?? '', ['escritura', 'permiso_riego', 'otro'])
                ? $_POST['tipo'] : 'otro';
        $nombre = trim($_POST['nombre'] ?? '');

        if ($parcela_id <= 0 || empty($nombre)) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }

        if (empty($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'No se recibió ningún archivo válido']);
            return;
        }

        $file = $_FILES['archivo'];
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
        $mimeType = mime_content_type($file['tmp_name']);

        if (!in_array($mimeType, $allowed)) {
            echo json_encode(['success' => false, 'message' => 'Formato no permitido (jpg, png, webp, pdf)']);
            return;
        }

        if ($file['size'] > 10 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'El archivo no puede superar 10MB']);
            return;
        }

        // Verify ownership
        $db = \Database::connect();
        $stmt = $db->prepare("SELECT id FROM parcelas WHERE id = ? AND id_user = ?");
        $stmt->bind_param("ii", $parcela_id, $_SESSION['user_id']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) {
            $stmt->close();
            $db->close();
            echo json_encode(['success' => false, 'message' => 'Parcela no encontrada']);
            return;
        }
        $stmt->close();

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $uploadDir = BASE_PATH . '/public/uploads/parcelas/' . $parcela_id . '/docs/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = uniqid('doc_') . '.' . $ext;
        $dest = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            $db->close();
            echo json_encode(['success' => false, 'message' => 'Error al guardar el archivo']);
            return;
        }

        $archivoPath = '/public/uploads/parcelas/' . $parcela_id . '/docs/' . $filename;
        $today = date('Y-m-d');

        $stmt = $db->prepare("INSERT INTO documentos_parcelas (parcela_id, tipo, nombre, archivo, id_user, created_at) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $parcela_id, $tipo, $nombre, $archivoPath, $_SESSION['user_id'], $today);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Documento subido correctamente', 'id' => $db->insert_id, 'archivo' => $archivoPath]);
        } else {
            unlink($dest);
            echo json_encode(['success' => false, 'message' => 'Error al guardar en la base de datos']);
        }

        $stmt->close();
        $db->close();
    }

    /**
     * Eliminar un documento adjunto de una parcela
     */
    public function eliminarDocumento()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        $this->validateCsrf();

        $input = json_decode(file_get_contents('php://input'), true);
        $id = intval($input['id'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID no válido']);
            return;
        }

        $db = \Database::connect();
        $stmt = $db->prepare("SELECT archivo FROM documentos_parcelas WHERE id = ? AND id_user = ?");
        $stmt->bind_param("ii", $id, $_SESSION['user_id']);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) {
            $db->close();
            echo json_encode(['success' => false, 'message' => 'Documento no encontrado']);
            return;
        }

        // Delete physical file
        $filePath = BASE_PATH . $row['archivo'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $stmt = $db->prepare("DELETE FROM documentos_parcelas WHERE id = ? AND id_user = ?");
        $stmt->bind_param("ii", $id, $_SESSION['user_id']);
        $stmt->execute();
        $stmt->close();
        $db->close();

        echo json_encode(['success' => true, 'message' => 'Documento eliminado correctamente']);
    }

    public function __destruct()
    {
        if ($this->db) {
            $this->db->close();
        }
    }
}
