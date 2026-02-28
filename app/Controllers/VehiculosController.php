<?php
namespace App\Controllers;
require_once BASE_PATH . '/config/database.php';
class VehiculosController extends BaseController
{
    public function index()
    {
        $db = \Database::connect();
        $result = $db->query("SELECT * FROM vehiculos");
        $vehiculos = $result->fetch_all(MYSQLI_ASSOC);
        $db->close();
        $data = [
            'vehiculos' => $vehiculos,
            'user' => [
                'name' => $_SESSION['user_name'] ?? 'Usuario'
            ]
        ];
        $this->render('vehiculos/index', $data);
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
        $db->close();
    }
}
