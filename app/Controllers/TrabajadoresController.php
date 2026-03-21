<?php
namespace App\Controllers;
class TrabajadoresController extends BaseController
{
    private $db;

    public function __construct()
    {
        $this->requireEmpresa();
        $this->db = \Database::connect();
    }

    public function buscar()
    {
        header('Content-Type: application/json');
        \Core\Logger::app()->error("Método buscar llamado en TrabajadoresController");
        \Core\Logger::app()->error("Query recibida: " . ($_GET['q'] ?? 'no definida'));

        if (!isset($_GET['q']) || strlen($_GET['q']) < 3) {
            \Core\Logger::app()->error("Query muy corta o no definida");
            echo json_encode([]);
            return;
        }

        try {
            if (!$this->db || $this->db->connect_error) {
                $this->db = \Database::connect();
            }

            $query = "%" . $_GET['q'] . "%";
            \Core\Logger::app()->error("Buscando trabajadores con query: " . $query);

            $tableCheck = $this->db->query("SHOW TABLES LIKE 'trabajadores'");
            if ($tableCheck->num_rows == 0) {
                throw new \Exception("La tabla 'trabajadores' no existe");
            }

            $stmt = $this->db->prepare("
                SELECT id, nombre, dni, ss
                FROM trabajadores
                WHERE nombre LIKE ? AND id_user = ?
                ORDER BY nombre
                LIMIT 10
            ");

            if (!$stmt) {
                throw new \Exception("Error en la preparación de la consulta: " . $this->db->error);
            }

            $stmt->bind_param("si", $query, $_SESSION['user_id']);

            if (!$stmt->execute()) {
                throw new \Exception("Error al ejecutar la consulta: " . $stmt->error);
            }

            $result = $stmt->get_result();

            $trabajadores = [];
            while ($row = $result->fetch_assoc()) {
                $trabajadores[] = $row;
            }

            \Core\Logger::app()->error("Trabajadores encontrados: " . json_encode($trabajadores));
            echo json_encode($trabajadores);

        } catch (\Exception $e) {
            \Core\Logger::app()->error("Error en búsqueda de trabajadores: " . $e->getMessage());
            echo json_encode(['error' => 'Error en la búsqueda: ' . $e->getMessage()]);
        }
    }

    public function index()
    {
        $db = \Database::connect();
        $mesActual = (int) date('n');
        $anioActual = (int) date('Y');

        // Consulta que detecta si el trabajador tiene tareas asignadas en el mes actual
        // Filtrada por id_user para que cada usuario solo vea sus trabajadores
        $stmt = $db->prepare("
            SELECT t.*,
                   (SELECT COUNT(*) FROM tarea_trabajadores tt
                    JOIN tareas ta ON tt.tarea_id = ta.id
                    WHERE tt.trabajador_id = t.id
                      AND MONTH(ta.fecha) = ?
                      AND YEAR(ta.fecha) = ?) AS tareas_mes
            FROM trabajadores t
            WHERE t.id_user = ?
            ORDER BY t.nombre
        ");
        $stmt->bind_param("iii", $mesActual, $anioActual, $_SESSION['user_id']);
        $stmt->execute();
        $trabajadores = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Marcar como activo si tiene tareas en el mes actual
        foreach ($trabajadores as &$trab) {
            if (intval($trab['tareas_mes']) > 0) {
                $trab['activo'] = 1;
            }
        }
        unset($trab);

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

            $v = \Core\Validator::make($input, [
                'nombre'  => 'required|max_length:100',
                'dni'     => 'max_length:20',
                'ss'      => 'max_length:30',
                'alta_ss' => 'date',
                'baja_ss' => 'date',
            ]);
            if ($v->fails()) {
                echo json_encode(['success' => false, 'message' => implode(' ', $v->allErrors())]);
                return;
            }

            $nombre = strip_tags($nombre);

            $db = \Database::connect();

            if (!empty($dni)) {
                $stmt = $db->prepare("SELECT id FROM trabajadores WHERE dni = ? AND id_user = ?");
                $stmt->bind_param("si", $dni, $_SESSION['user_id']);
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
            \Core\Logger::app()->error("Error creando trabajador: " . $e->getMessage());
            \Core\Logger::app()->error("Stack trace: " . $e->getTraceAsString());
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
            $stmt = $db->prepare("SELECT * FROM trabajadores WHERE id = ? AND id_user = ?");
            $stmt->bind_param("ii", $id, $_SESSION['user_id']);
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
            \Core\Logger::app()->error("Error obteniendo trabajador: " . $e->getMessage());
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
            \Core\Logger::app()->error("Método actualizar llamado");
            $input = json_decode(file_get_contents('php://input'), true);
            \Core\Logger::app()->error("Input recibido: " . json_encode($input));

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

            $v = \Core\Validator::make($input, [
                'id'      => 'required|integer',
                'nombre'  => 'required|max_length:100',
                'dni'     => 'max_length:20',
                'ss'      => 'max_length:30',
                'alta_ss' => 'date',
                'baja_ss' => 'date',
            ]);
            if ($v->fails()) {
                echo json_encode(['success' => false, 'message' => implode(' ', $v->allErrors())]);
                return;
            }

            $nombre = strip_tags($nombre);

            $db = \Database::connect();
            \Core\Logger::app()->error("Conexión a BD establecida");

            if (!empty($dni)) {
                \Core\Logger::app()->error("Verificando DNI duplicado: $dni para ID: $id");
                $stmt = $db->prepare("SELECT id FROM trabajadores WHERE dni = ? AND id != ? AND id_user = ?");
                if (!$stmt) {
                    throw new \Exception("Error preparando consulta DNI: " . $db->error);
                }
                $stmt->bind_param("sii", $dni, $id, $_SESSION['user_id']);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $stmt->close();
                    $db->close();
                    echo json_encode(['success' => false, 'message' => 'Ya existe otro trabajador con ese DNI']);
                    return;
                }
                $stmt->close();
                \Core\Logger::app()->error("DNI verificado - no hay duplicados");
            }

            \Core\Logger::app()->error("Preparando consulta UPDATE");
            $stmt = $db->prepare("UPDATE trabajadores SET nombre = ?, dni = ?, ss = ?, alta_ss = ?, baja_ss = ?, cuadrilla = ? WHERE id = ? AND id_user = ?");
            if (!$stmt) {
                throw new \Exception("Error preparando consulta UPDATE: " . $db->error);
            }
            $stmt->bind_param("sssssiii", $nombre, $dni, $ss, $alta_ss, $baja_ss, $cuadrilla, $id, $_SESSION['user_id']);

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
            \Core\Logger::app()->error("Error actualizando trabajador: " . $e->getMessage());
            \Core\Logger::app()->error("Stack trace: " . $e->getTraceAsString());
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

        // Optimizar imagen: redimensionar y comprimir con GD (reduce peso de fotos de móvil)
        $basePath = $uploadDir . 'foto';
        $saved = $this->optimizarImagen($file['tmp_name'], $mimeType, $basePath, strtolower($ext));

        if (!$saved) {
            echo json_encode(['success' => false, 'message' => 'Error al guardar la imagen']);
            return;
        }

        $filename = basename($saved['path']);
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
            \Core\Logger::app()->error("Error guardando foto: " . $e->getMessage());
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
            \Core\Logger::app()->error("Error obteniendo cuadrilla: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
        }
    }

    /**
     * Dar de baja o eliminar un trabajador
     * Si tiene tareas asignadas → se da de baja (fecha_baja + estado inactivo)
     * Si no tiene tareas → se elimina definitivamente
     * Si se envía forzar=true → se elimina definitivamente aunque tenga tareas
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
            $forzar = !empty($input['forzar']);

            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID no válido']);
                return;
            }

            $db = \Database::connect();

            // Verificar si tiene tareas asignadas
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM tarea_trabajadores WHERE trabajador_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $tieneTareas = intval($stmt->get_result()->fetch_assoc()['count']) > 0;
            $stmt->close();

            if ($tieneTareas && !$forzar) {
                // Dar de baja: marcar fecha_baja y estado inactivo
                $fechaBaja = date('Y-m-d');
                $stmt = $db->prepare("UPDATE trabajadores SET fecha_baja = ?, activo = 0 WHERE id = ? AND id_user = ?");
                $stmt->bind_param("sii", $fechaBaja, $id, $_SESSION['user_id']);

                if ($stmt->execute() && $stmt->affected_rows > 0) {
                    echo json_encode([
                        'success' => true,
                        'tipo' => 'baja',
                        'message' => 'Trabajador dado de baja. Su historial se conserva.'
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'No se encontró el trabajador']);
                }
                $stmt->close();
                $db->close();
                return;
            }

            // Eliminar definitivamente (sin tareas o con forzar=true)
            if ($forzar && $tieneTareas) {
                // Primero eliminar las asignaciones a tareas
                $stmt = $db->prepare("DELETE FROM tarea_trabajadores WHERE trabajador_id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->close();
            }

            // Eliminar foto de perfil y documentos si existen
            $uploadDir = BASE_PATH . '/public/uploads/trabajadores/' . $id . '/';
            if (is_dir($uploadDir)) {
                foreach (glob($uploadDir . '*') as $file) {
                    unlink($file);
                }
                rmdir($uploadDir);
            }

            $stmt = $db->prepare("DELETE FROM trabajadores WHERE id = ? AND id_user = ?");
            $stmt->bind_param("ii", $id, $_SESSION['user_id']);

            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    echo json_encode(['success' => true, 'tipo' => 'eliminado', 'message' => 'Trabajador eliminado definitivamente']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'No se encontró el trabajador']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar: ' . $stmt->error]);
            }

            $stmt->close();
            $db->close();

        } catch (\Exception $e) {
            \Core\Logger::app()->error("Error eliminando trabajador: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
        }
    }

    /**
     * Reactivar un trabajador dado de baja (quitar fecha_baja)
     */
    public function reactivar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $this->validateCsrf();

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $id = intval($input['id'] ?? 0);

            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID no válido']);
                return;
            }

            $db = \Database::connect();
            $stmt = $db->prepare("UPDATE trabajadores SET fecha_baja = NULL WHERE id = ? AND id_user = ?");
            $stmt->bind_param("ii", $id, $_SESSION['user_id']);

            if ($stmt->execute() && $stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Trabajador reactivado correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se encontró el trabajador']);
            }

            $stmt->close();
            $db->close();
        } catch (\Exception $e) {
            \Core\Logger::app()->error("Error reactivando trabajador: " . $e->getMessage());
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

        if ($file['size'] > 10 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'El archivo no puede superar 10MB']);
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

        // Optimizar si es imagen (no PDFs). Redimensiona y comprime con GD.
        if (str_starts_with($mimeType, 'image/')) {
            $basePath = $uploadDir . $filenameBase;
            $saved = $this->optimizarImagen($file['tmp_name'], $mimeType, $basePath, $ext);

            if (!$saved) {
                $db->close();
                echo json_encode(['success' => false, 'message' => 'Error al guardar el archivo']);
                return;
            }
            $filename = basename($saved['path']);
        } else {
            // PDF: guardar tal cual
            $filename = $filenameBase . '.' . $ext;
            $dest     = $uploadDir . $filename;
            if (!move_uploaded_file($file['tmp_name'], $dest)) {
                $db->close();
                echo json_encode(['success' => false, 'message' => 'Error al guardar el archivo']);
                return;
            }
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

        // Deuda de meses cerrados y no pagados
        $stmt = $db->prepare("
            SELECT COALESCE(SUM(importe_total), 0) as deuda_cerrada
            FROM pagos_mensuales_trabajadores
            WHERE trabajador_id = ? AND pagado = 0
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $deuda_cerrada = floatval($stmt->get_result()->fetch_assoc()['deuda_cerrada'] ?? 0);
        $stmt->close();

        // Deuda del mes actual (calculada en tiempo real desde las tareas)
        // Solo cuenta si el mes actual NO está ya cerrado para este trabajador
        $mesActual = (int) date('n');
        $anioActual = (int) date('Y');
        $stmt = $db->prepare("
            SELECT id FROM pagos_mensuales_trabajadores
            WHERE trabajador_id = ? AND month = ? AND year = ? LIMIT 1
        ");
        $stmt->bind_param("iii", $id, $mesActual, $anioActual);
        $stmt->execute();
        $mesCerrado = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $deudaMesActual = 0;
        if (!$mesCerrado) {
            // El mes no está cerrado → calcular deuda en tiempo real
            $stmt = $db->prepare("
                SELECT ROUND(COALESCE(SUM(
                    tt.horas_asignadas * COALESCE(ttrab.precio_hora, trab.precio_hora, 0)
                ), 0), 2) AS deuda_mes
                FROM tarea_trabajadores tt
                JOIN tareas ta ON tt.tarea_id = ta.id
                LEFT JOIN tarea_trabajos ttrab ON ta.id = ttrab.tarea_id
                LEFT JOIN trabajos trab ON ttrab.trabajo_id = trab.id
                WHERE tt.trabajador_id = ?
                  AND MONTH(ta.fecha) = ?
                  AND YEAR(ta.fecha) = ?
                  AND ta.id_user = ?
            ");
            $stmt->bind_param("iiii", $id, $mesActual, $anioActual, $_SESSION['user_id']);
            $stmt->execute();
            $deudaMesActual = floatval($stmt->get_result()->fetch_assoc()['deuda_mes'] ?? 0);
            $stmt->close();
        }

        $deuda_pendiente = $deuda_cerrada + $deudaMesActual;
        $db->close();

        $this->render('trabajadores/detalle', [
            'trabajador'       => $trabajador,
            'historial'        => $historial,
            'deuda_pendiente'  => $deuda_pendiente,
            'deuda_cerrada'    => $deuda_cerrada,
            'deuda_mes_actual' => $deudaMesActual,
            'user'             => ['name' => $_SESSION['user_name'] ?? 'Usuario']
        ]);
    }

    public function __destruct()
    {
        if ($this->db) {
            try { $this->db->close(); } catch (\Throwable $e) {}
        }
    }
}
