<?php
namespace App\Controllers;
class VehiculosController extends BaseController
{
    public function index()
    {
        $this->requireEmpresa();
        $db = \Database::connect();
        $userId = intval($_SESSION['user_id']);
        $stmt = $db->prepare("SELECT * FROM vehiculos WHERE id_user = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $vehiculos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $data = [
            'vehiculos' => $vehiculos,
            'user' => ['name' => $_SESSION['user_name'] ?? 'Usuario']
        ];
        $this->render('vehiculos/index', $data);
    }

    /**
     * GET /vehiculos/{id} — Vista detalle de un vehículo
     */
    public function detalle()
    {
        $this->requireEmpresa();
        $id = intval($_GET['id'] ?? 0);

        // Validación ACL centralizada
        $vehiculo = $this->validateAndGetResource($id, 'vehiculos');
        if (!$vehiculo) {
            $this->redirect('/datos/vehiculos');
            return;
        }

        $data = [
            'vehiculo' => $vehiculo,
            'user' => ['name' => $_SESSION['user_name'] ?? 'Usuario']
        ];
        $this->render('vehiculos/detalle', $data);
    }

    /**
     * POST /vehiculos/actualizar — Actualizar datos del vehículo (JSON)
     */
    public function actualizar()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        $this->validateCsrf();
        $userId = intval($_SESSION['user_id']);

        $input = json_decode(file_get_contents('php://input'), true);
        $id = intval($input['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            return;
        }

        // Campos permitidos para actualización
        $permitidos = ['nombre', 'matricula', 'pasa_itv', 'fecha_matriculacion', 'seguro', 'precio_seguro', 'telefono_aseguradora'];
        $sets = [];
        $params = [];
        $types = '';

        foreach ($permitidos as $campo) {
            if (array_key_exists($campo, $input)) {
                $valor = trim($input[$campo]);
                // Convertir vacío a NULL para campos date y decimal
                if (in_array($campo, ['pasa_itv', 'fecha_matriculacion', 'precio_seguro']) && $valor === '') {
                    $sets[] = "`{$campo}` = NULL";
                } else {
                    $sets[] = "`{$campo}` = ?";
                    $params[] = $valor;
                    $types .= 's';
                }
            }
        }

        if (empty($sets)) {
            echo json_encode(['success' => false, 'message' => 'Sin datos para actualizar']);
            return;
        }

        $types .= 'ii';
        $params[] = $id;
        $params[] = $userId;

        $db = \Database::connect();
        $sql = "UPDATE vehiculos SET " . implode(', ', $sets) . ", updated_at = NOW() WHERE id = ? AND id_user = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            // Si se actualizó la ITV, regenerar recordatorio automático
            if (array_key_exists('pasa_itv', $input)) {
                $this->regenerarRecordatorioITV($userId, $id, $input['pasa_itv']);
            }
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al guardar']);
        }
        $stmt->close();
    }

    /**
     * POST /vehiculos/eliminar — Eliminar vehículo (JSON)
     */
    public function eliminar()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        $this->validateCsrf();
        $userId = intval($_SESSION['user_id']);

        $input = json_decode(file_get_contents('php://input'), true);
        $id = intval($input['id'] ?? 0);

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            return;
        }

        $db = \Database::connect();
        $stmt = $db->prepare("DELETE FROM vehiculos WHERE id = ? AND id_user = ?");
        $stmt->bind_param("ii", $id, $userId);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar']);
        }
        $stmt->close();
    }

    /**
     * POST /vehiculos/crear — Crear vehículo (JSON)
     */
    public function crear()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        $this->validateCsrf();
        $userId = intval($_SESSION['user_id']);

        $input = json_decode(file_get_contents('php://input'), true);
        $nombre = trim($input['nombre'] ?? '');

        if (empty($nombre)) {
            echo json_encode(['success' => false, 'message' => 'El nombre es obligatorio']);
            return;
        }

        $matricula = trim($input['matricula'] ?? '');

        $db = \Database::connect();
        $stmt = $db->prepare("INSERT INTO vehiculos (id_user, nombre, matricula) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $userId, $nombre, $matricula);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'id' => $db->insert_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear']);
        }
        $stmt->close();
    }

    public function subirDocumento()
    {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        $this->validateCsrf();

        $userId = intval($_SESSION['user_id']);
        $id     = intval($_POST['id'] ?? 0);
        $tipo   = $_POST['tipo'] ?? '';

        if (!$id || !in_array($tipo, ['ficha_tecnica', 'poliza_seguro'])) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
            return;
        }

        if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'No se recibió archivo o hubo un error en la subida']);
            return;
        }

        $file    = $_FILES['archivo'];
        $finfo   = new \finfo(FILEINFO_MIME_TYPE);
        $mime    = $finfo->file($file['tmp_name']);
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];

        if (!in_array($mime, $allowed)) {
            echo json_encode(['success' => false, 'message' => 'Tipo no permitido. Use JPG, PNG, WebP o PDF']);
            return;
        }

        $ext       = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $uploadDir = BASE_PATH . '/public/uploads/vehiculos/' . $id . '/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename   = $tipo . '.' . $ext;
        $targetPath = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            echo json_encode(['success' => false, 'message' => 'Error al guardar el archivo']);
            return;
        }

        $relativePath = '/public/uploads/vehiculos/' . $id . '/' . $filename;

        $db   = \Database::connect();
        $stmt = $db->prepare("UPDATE vehiculos SET `{$tipo}` = ?, updated_at = NOW() WHERE id = ? AND id_user = ?");
        $stmt->bind_param("sii", $relativePath, $id, $userId);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'path' => $relativePath]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar la base de datos']);
        }
        $stmt->close();
    }

    /**
     * Cuando se actualiza la fecha de ITV, eliminar recordatorios antiguos
     * y dejar que el sistema genere el nuevo automáticamente
     */
    private function regenerarRecordatorioITV($userId, $vehiculoId, $nuevaFechaItv)
    {
        $db = \Database::connect();
        // Eliminar recordatorios de ITV anteriores para este vehículo
        $stmt = $db->prepare("DELETE FROM recordatorios WHERE id_user = ? AND tipo = 'itv' AND entidad_id = ?");
        $stmt->bind_param("ii", $userId, $vehiculoId);
        $stmt->execute();
        $stmt->close();
    }
}
