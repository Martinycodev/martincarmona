<?php
namespace App\Controllers;
require_once BASE_PATH . '/config/database.php';
class HerramientasController extends BaseController
{
    public function index()
    {
        $db = \Database::connect();
        $result = $db->query("SELECT * FROM herramientas");
        $herramientas = $result->fetch_all(MYSQLI_ASSOC);
        $db->close();
        $data = [
            'herramientas' => $herramientas,
            'user' => [
                'name' => $_SESSION['user_name'] ?? 'Usuario'
            ]
        ];
        $this->render('herramientas/index', $data);
    }

    public function subirInstrucciones()
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

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            return;
        }

        if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'No se recibió archivo o hubo un error en la subida']);
            return;
        }

        $file  = $_FILES['archivo'];
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);

        if ($mime !== 'application/pdf') {
            echo json_encode(['success' => false, 'message' => 'Solo se permiten archivos PDF']);
            return;
        }

        $uploadDir = BASE_PATH . '/public/uploads/herramientas/' . $id . '/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $targetPath = $uploadDir . 'instrucciones.pdf';

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            echo json_encode(['success' => false, 'message' => 'Error al guardar el archivo']);
            return;
        }

        $relativePath = '/public/uploads/herramientas/' . $id . '/instrucciones.pdf';

        $db   = \Database::connect();
        $stmt = $db->prepare("UPDATE herramientas SET instrucciones_pdf = ?, updated_at = NOW() WHERE id = ? AND id_user = ?");
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
