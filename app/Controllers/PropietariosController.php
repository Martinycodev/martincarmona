<?php
namespace App\Controllers;
require_once BASE_PATH . '/config/database.php';

class PropietariosController extends BaseController
{
    public function __construct()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /');
            exit;
        }
    }

    public function index()
    {
        $db = \Database::connect();
        $stmt = $db->prepare("SELECT * FROM propietarios WHERE id_user = ? ORDER BY nombre");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $propietarios = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $db->close();

        $data = [
            'propietarios' => $propietarios,
            'user' => [
                'name' => $_SESSION['user_name'] ?? 'Usuario'
            ]
        ];
        $this->render('propietarios/index', $data);
    }

    /**
     * Crear un nuevo propietario
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
            $apellidos = trim($input['apellidos'] ?? '');
            $dni       = trim($input['dni'] ?? '');
            $telefono  = trim($input['telefono'] ?? '');
            $email     = trim($input['email'] ?? '');

            if (empty($nombre)) {
                echo json_encode(['success' => false, 'message' => 'El nombre es requerido']);
                return;
            }

            $db = \Database::connect();

            $stmt = $db->prepare(
                "INSERT INTO propietarios (nombre, apellidos, dni, telefono, email, id_user) VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param(
                "sssssi",
                $nombre, $apellidos, $dni, $telefono, $email, $_SESSION['user_id']
            );

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Propietario creado correctamente', 'id' => $db->insert_id]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al crear el propietario: ' . $stmt->error]);
            }

            $stmt->close();
            $db->close();

        } catch (\Exception $e) {
            error_log("Error creando propietario: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }

    /**
     * Obtener un propietario por ID
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
            $stmt = $db->prepare("SELECT * FROM propietarios WHERE id = ? AND id_user = ?");
            $stmt->bind_param("ii", $id, $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                echo json_encode(['success' => true, 'propietario' => $row]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Propietario no encontrado']);
            }

            $stmt->close();
            $db->close();

        } catch (\Exception $e) {
            error_log("Error obteniendo propietario: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
        }
    }

    /**
     * Actualizar un propietario
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

            $id        = intval($input['id'] ?? 0);
            $nombre    = trim($input['nombre'] ?? '');
            $apellidos = trim($input['apellidos'] ?? '');
            $dni       = trim($input['dni'] ?? '');
            $telefono  = trim($input['telefono'] ?? '');
            $email     = trim($input['email'] ?? '');

            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID no válido']);
                return;
            }

            if (empty($nombre)) {
                echo json_encode(['success' => false, 'message' => 'El nombre es requerido']);
                return;
            }

            $db = \Database::connect();

            $stmt = $db->prepare(
                "UPDATE propietarios SET nombre = ?, apellidos = ?, dni = ?, telefono = ?, email = ? WHERE id = ? AND id_user = ?"
            );
            $stmt->bind_param(
                "sssssii",
                $nombre, $apellidos, $dni, $telefono, $email, $id, $_SESSION['user_id']
            );

            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    echo json_encode(['success' => true, 'message' => 'Propietario actualizado correctamente']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'No se encontró el propietario']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar el propietario: ' . $stmt->error]);
            }

            $stmt->close();
            $db->close();

        } catch (\Exception $e) {
            error_log("Error actualizando propietario: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }

    /**
     * Eliminar un propietario
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

            // Verificar si tiene parcelas asignadas (columna propietario_id se añade en Task 3)
            // Solo verificar si la columna existe
            $columnCheck = $db->query("SHOW COLUMNS FROM parcelas LIKE 'propietario_id'");
            if ($columnCheck && $columnCheck->num_rows > 0) {
                $stmt = $db->prepare("SELECT COUNT(*) as count FROM parcelas WHERE propietario_id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $stmt->close();

                if ($row['count'] > 0) {
                    echo json_encode(['success' => false, 'message' => 'No se puede eliminar el propietario porque tiene parcelas asignadas']);
                    return;
                }
            }

            // Clean up uploaded files
            $uploadDir = BASE_PATH . '/public/uploads/propietarios/' . $id . '/';
            if (is_dir($uploadDir)) {
                foreach (glob($uploadDir . '*') as $file) {
                    unlink($file);
                }
                rmdir($uploadDir);
            }

            $stmt = $db->prepare("DELETE FROM propietarios WHERE id = ? AND id_user = ?");
            $stmt->bind_param("ii", $id, $_SESSION['user_id']);

            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    echo json_encode(['success' => true, 'message' => 'Propietario eliminado correctamente']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'No se encontró el propietario']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar el propietario: ' . $stmt->error]);
            }

            $stmt->close();
            $db->close();

        } catch (\Exception $e) {
            error_log("Error eliminando propietario: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
        }
    }

    /**
     * Subir imagen del DNI (anverso o reverso)
     */
    public function subirImagenDni()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $this->validateCsrf();

        $id   = intval($_POST['id'] ?? 0);
        $lado = $_POST['lado'] ?? '';

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID no válido']);
            return;
        }

        if (!in_array($lado, ['anverso', 'reverso'])) {
            echo json_encode(['success' => false, 'message' => 'Lado no válido (debe ser anverso o reverso)']);
            return;
        }

        if (empty($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'No se recibió ningún archivo válido']);
            return;
        }

        $file     = $_FILES['imagen'];
        $allowed  = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $mimeType = mime_content_type($file['tmp_name']);

        if (!in_array($mimeType, $allowed)) {
            echo json_encode(['success' => false, 'message' => 'Formato de imagen no permitido']);
            return;
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'El archivo no puede superar 5MB']);
            return;
        }

        // Verify ownership before writing file
        $db = \Database::connect();
        $stmt = $db->prepare("SELECT id FROM propietarios WHERE id = ? AND id_user = ?");
        $stmt->bind_param("ii", $id, $_SESSION['user_id']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) {
            $stmt->close();
            $db->close();
            echo json_encode(['success' => false, 'message' => 'Propietario no encontrado']);
            return;
        }
        $stmt->close();

        $ext       = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $uploadDir = BASE_PATH . '/public/uploads/propietarios/' . $id . '/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Eliminar versión anterior si existe
        foreach (glob($uploadDir . 'dni_' . $lado . '.*') as $old) {
            unlink($old);
        }

        $filename = 'dni_' . $lado . '.' . $ext;
        $dest     = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            echo json_encode(['success' => false, 'message' => 'Error al guardar la imagen']);
            return;
        }

        $imagePath = '/public/uploads/propietarios/' . $id . '/' . $filename;
        $campo     = ($lado === 'anverso') ? 'imagen_dni_anverso' : 'imagen_dni_reverso';

        try {
            $stmt = $db->prepare("UPDATE propietarios SET {$campo} = ? WHERE id = ? AND id_user = ?");
            $stmt->bind_param("sii", $imagePath, $id, $_SESSION['user_id']);
            $stmt->execute();
            $stmt->close();
            $db->close();

            echo json_encode(['success' => true, 'imagen' => $imagePath]);
        } catch (\Exception $e) {
            error_log("Error guardando imagen DNI: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error al actualizar la base de datos']);
        }
    }
}
