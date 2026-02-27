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
            $baja_ss   = !empty($input['baja_ss']) ? $input['baja_ss'] : null;
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

            $stmt = $db->prepare("INSERT INTO trabajadores (nombre, dni, ss, alta_ss, baja_ss, cuadrilla, id_user) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssii", $nombre, $dni, $ss, $alta_ss, $baja_ss, $cuadrilla, $_SESSION['user_id']);

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
            $baja_ss   = !empty($input['baja_ss']) ? $input['baja_ss'] : null;
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
            $stmt = $db->prepare("UPDATE trabajadores SET nombre = ?, dni = ?, ss = ?, alta_ss = ?, baja_ss = ?, cuadrilla = ? WHERE id = ?");
            if (!$stmt) {
                throw new \Exception("Error preparando consulta UPDATE: " . $db->error);
            }
            $stmt->bind_param("sssssii", $nombre, $dni, $ss, $alta_ss, $baja_ss, $cuadrilla, $id);

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

    /**
     * Subir documento (DNI anverso, DNI reverso, Seguridad Social) de un trabajador
     */
    public function subirDocumento()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        $this->validateCsrf();

        $id   = intval($_POST['id'] ?? 0);
        // $tipo is whitelisted to only one of these three values, preventing SQL injection
        $tipo = in_array($_POST['tipo'] ?? '', ['dni_anverso', 'dni_reverso', 'ss'])
                ? $_POST['tipo'] : null;

        if ($id <= 0 || !$tipo) {
            echo json_encode(['success' => false, 'message' => 'Datos no válidos']);
            return;
        }

        if (empty($_FILES['documento']) || $_FILES['documento']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'No se recibió ningún archivo válido']);
            return;
        }

        $file    = $_FILES['documento'];
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
        $mimeType = mime_content_type($file['tmp_name']);

        if (!in_array($mimeType, $allowed)) {
            echo json_encode(['success' => false, 'message' => 'Formato no permitido (jpg, png, webp, pdf)']);
            return;
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'El archivo no puede superar 5MB']);
            return;
        }

        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $uploadDir = BASE_PATH . '/public/uploads/trabajadores/' . $id . '/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Verify ownership before writing file
        $db = \Database::connect();
        $stmt = $db->prepare("SELECT id FROM trabajadores WHERE id = ? AND id_user = ?");
        $stmt->bind_param("ii", $id, $_SESSION['user_id']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) {
            $stmt->close();
            $db->close();
            echo json_encode(['success' => false, 'message' => 'Trabajador no encontrado']);
            return;
        }
        $stmt->close();

        // Map tipo to column name and filename
        // $campo is built from whitelisted $tipo values only: dni_anverso, dni_reverso, ss — no SQL injection risk
        $campo = 'imagen_' . $tipo;  // imagen_dni_anverso, imagen_dni_reverso, imagen_ss
        $filenameBase = $tipo;       // dni_anverso, dni_reverso, ss

        // Delete previous file
        foreach (glob($uploadDir . $filenameBase . '.*') as $old) {
            unlink($old);
        }

        $filename = $filenameBase . '.' . $ext;
        $dest     = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            $db->close();
            echo json_encode(['success' => false, 'message' => 'Error al guardar el archivo']);
            return;
        }

        $path = '/public/uploads/trabajadores/' . $id . '/' . $filename;

        $stmt = $db->prepare("UPDATE trabajadores SET {$campo} = ? WHERE id = ? AND id_user = ?");
        $stmt->bind_param("sii", $path, $id, $_SESSION['user_id']);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'imagen' => $path]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar la base de datos']);
        }

        $stmt->close();
        $db->close();
    }

    /**
     * Ficha individual de un trabajador (GET ?id=X)
     */
    public function detalle()
    {
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            header('Location: /datos/trabajadores');
            exit;
        }

        $db = \Database::connect();

        // Cargar trabajador
        $stmt = $db->prepare("SELECT * FROM trabajadores WHERE id = ? AND id_user = ?");
        $stmt->bind_param("ii", $id, $_SESSION['user_id']);
        $stmt->execute();
        $trabajador = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$trabajador) {
            header('Location: /datos/trabajadores');
            exit;
        }

        // Historial de tareas (las últimas 50)
        $stmt = $db->prepare("
            SELECT ta.id, ta.fecha, ta.titulo, tt.horas_asignadas,
                   COALESCE(ttrab.precio_hora, trab.precio_hora, 0) as precio_hora,
                   tt.horas_asignadas * COALESCE(ttrab.precio_hora, trab.precio_hora, 0) as coste
            FROM tarea_trabajadores tt
            JOIN tareas ta ON tt.tarea_id = ta.id
            LEFT JOIN tarea_trabajos ttrab ON ta.id = ttrab.tarea_id
            LEFT JOIN trabajos trab ON ttrab.trabajo_id = trab.id
            WHERE tt.trabajador_id = ? AND ta.id_user = ?
            ORDER BY ta.fecha DESC
            LIMIT 50
        ");
        $stmt->bind_param("ii", $id, $_SESSION['user_id']);
        $stmt->execute();
        $historial = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Deuda actual: suma de pagos mensuales pendientes
        $stmt = $db->prepare("
            SELECT COALESCE(SUM(importe_total), 0) as deuda_pendiente
            FROM pagos_mensuales_trabajadores
            WHERE trabajador_id = ? AND pagado = 0
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $deuda_row = $stmt->get_result()->fetch_assoc();
        $deuda_pendiente = $deuda_row['deuda_pendiente'] ?? 0;
        $stmt->close();
        $db->close();

        $this->render('trabajadores/detalle', [
            'trabajador'      => $trabajador,
            'historial'       => $historial,
            'deuda_pendiente' => $deuda_pendiente,
            'user'            => ['name' => $_SESSION['user_name'] ?? 'Usuario']
        ]);
    }

    public function __destruct()
    {
        if ($this->db) {
            $this->db->close();
        }
    }
}
