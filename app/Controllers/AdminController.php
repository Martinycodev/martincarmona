<?php

namespace App\Controllers;

require_once BASE_PATH . '/config/database.php';

class AdminController extends BaseController
{
    public function __construct()
    {
        $this->requireEmpresaOAdmin();
    }

    // ── Lista de usuarios ────────────────────────────────────────────────────

    public function usuarios()
    {
        $db = \Database::connect();

        $stmt = $db->prepare("
            SELECT u.id, u.name, u.email, u.rol, u.propietario_id, u.trabajador_id,
                   CONCAT(p.nombre, IF(p.apellidos IS NOT NULL AND p.apellidos != '', CONCAT(' ', p.apellidos), '')) AS propietario_nombre,
                   t.nombre AS trabajador_nombre
            FROM usuarios u
            LEFT JOIN propietarios p ON u.propietario_id = p.id
            LEFT JOIN trabajadores t ON u.trabajador_id = t.id
            ORDER BY u.rol ASC, u.name ASC
        ");
        $stmt->execute();
        $usuarios = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $res = $db->prepare("
            SELECT p.id,
                   CONCAT(p.nombre, IF(p.apellidos IS NOT NULL AND p.apellidos != '', CONCAT(' ', p.apellidos), '')) AS nombre
            FROM propietarios p
            ORDER BY p.nombre
        ");
        $res->execute();
        $propietarios = $res->get_result()->fetch_all(MYSQLI_ASSOC);
        $res->close();

        $res = $db->prepare("SELECT id, nombre FROM trabajadores ORDER BY nombre");
        $res->execute();
        $trabajadores = $res->get_result()->fetch_all(MYSQLI_ASSOC);
        $res->close();

        $db->close();

        $this->render('admin/usuarios', [
            'usuarios'      => $usuarios,
            'propietarios'  => $propietarios,
            'trabajadores'  => $trabajadores,
            'currentUserId' => $_SESSION['user_id'],
            'user'          => ['name' => $_SESSION['user_name'] ?? 'Usuario'],
        ]);
    }

    // ── CRUD Usuarios ────────────────────────────────────────────────────────

    public function crearUsuario()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']); return;
        }
        $this->validateCsrf();

        $input         = json_decode(file_get_contents('php://input'), true);
        $name          = trim($input['name'] ?? '');
        $email         = trim($input['email'] ?? '');
        $password      = $input['password'] ?? '';
        $rol           = $input['rol'] ?? 'empresa';
        $propietarioId = !empty($input['propietario_id']) ? intval($input['propietario_id']) : null;
        $trabajadorId  = !empty($input['trabajador_id'])  ? intval($input['trabajador_id'])  : null;

        if (empty($name) || empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Nombre, email y contraseña son requeridos']); return;
        }

        $rolesValidos = ['empresa', 'admin', 'propietario', 'trabajador'];
        if (!in_array($rol, $rolesValidos)) {
            echo json_encode(['success' => false, 'message' => 'Rol inválido']); return;
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $db = \Database::connect();

        $check = $db->prepare("SELECT id FROM usuarios WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $check->close(); $db->close();
            echo json_encode(['success' => false, 'message' => 'Ya existe un usuario con ese email']); return;
        }
        $check->close();

        $stmt = $db->prepare("
            INSERT INTO usuarios (name, email, password, rol, propietario_id, trabajador_id, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->bind_param("ssssii", $name, $email, $passwordHash, $rol, $propietarioId, $trabajadorId);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'id' => $db->insert_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt->error]);
        }
        $stmt->close();
        $db->close();
    }

    public function actualizarUsuario()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']); return;
        }
        $this->validateCsrf();

        $input         = json_decode(file_get_contents('php://input'), true);
        $id            = intval($input['id'] ?? 0);
        $name          = trim($input['name'] ?? '');
        $email         = trim($input['email'] ?? '');
        $rol           = $input['rol'] ?? '';
        $password      = $input['password'] ?? '';
        $propietarioId = !empty($input['propietario_id']) ? intval($input['propietario_id']) : null;
        $trabajadorId  = !empty($input['trabajador_id'])  ? intval($input['trabajador_id'])  : null;

        if (!$id || empty($name) || empty($email) || empty($rol)) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos']); return;
        }

        $rolesValidos = ['empresa', 'admin', 'propietario', 'trabajador'];
        if (!in_array($rol, $rolesValidos)) {
            echo json_encode(['success' => false, 'message' => 'Rol inválido']); return;
        }

        $db = \Database::connect();

        // Si el usuario era 'empresa' y cambia de rol, verificar que quede al menos uno
        $checkRol = $db->prepare("SELECT rol FROM usuarios WHERE id = ?");
        $checkRol->bind_param("i", $id);
        $checkRol->execute();
        $currentRow = $checkRol->get_result()->fetch_assoc();
        $checkRol->close();

        if ($currentRow && $currentRow['rol'] === 'empresa' && $rol !== 'empresa') {
            $res = $db->prepare("SELECT COUNT(*) AS cnt FROM usuarios WHERE rol = 'empresa' AND id != ?");
            $res->bind_param("i", $id);
            $res->execute();
            $cnt = $res->get_result()->fetch_assoc()['cnt'];
            $res->close();
            if ($cnt == 0) {
                $db->close();
                echo json_encode(['success' => false, 'message' => 'Debe quedar al menos un usuario con rol empresa']); return;
            }
        }

        // Email único (excluyendo el propio)
        $checkEmail = $db->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
        $checkEmail->bind_param("si", $email, $id);
        $checkEmail->execute();
        if ($checkEmail->get_result()->num_rows > 0) {
            $checkEmail->close(); $db->close();
            echo json_encode(['success' => false, 'message' => 'Ya existe otro usuario con ese email']); return;
        }
        $checkEmail->close();

        if (!empty($password)) {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("
                UPDATE usuarios SET name=?, email=?, password=?, rol=?, propietario_id=?, trabajador_id=?, updated_at=NOW()
                WHERE id=?
            ");
            $stmt->bind_param("ssssiii", $name, $email, $passwordHash, $rol, $propietarioId, $trabajadorId, $id);
        } else {
            $stmt = $db->prepare("
                UPDATE usuarios SET name=?, email=?, rol=?, propietario_id=?, trabajador_id=?, updated_at=NOW()
                WHERE id=?
            ");
            $stmt->bind_param("sssiii", $name, $email, $rol, $propietarioId, $trabajadorId, $id);
        }

        $ok = $stmt->execute() && $stmt->affected_rows >= 0;
        $stmt->close();
        $db->close();

        echo json_encode(['success' => $ok, 'message' => $ok ? 'Usuario actualizado' : 'Error al actualizar']);
    }

    public function eliminarUsuario()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']); return;
        }
        $this->validateCsrf();

        $currentUserId = $_SESSION['user_id'];
        $input = json_decode(file_get_contents('php://input'), true);
        $id    = intval($input['id'] ?? 0);

        if (!$id) { echo json_encode(['success' => false, 'message' => 'ID inválido']); return; }

        if ($id === $currentUserId) {
            echo json_encode(['success' => false, 'message' => 'No puedes eliminar tu propio usuario']); return;
        }

        $db = \Database::connect();

        $check = $db->prepare("SELECT rol FROM usuarios WHERE id = ?");
        $check->bind_param("i", $id);
        $check->execute();
        $row = $check->get_result()->fetch_assoc();
        $check->close();

        if ($row && $row['rol'] === 'empresa') {
            $res = $db->prepare("SELECT COUNT(*) AS cnt FROM usuarios WHERE rol = 'empresa'");
            $res->execute();
            $cnt = $res->get_result()->fetch_assoc()['cnt'];
            $res->close();
            if ($cnt <= 1) {
                $db->close();
                echo json_encode(['success' => false, 'message' => 'No puedes eliminar el último usuario empresa']); return;
            }
        }

        $stmt = $db->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $id);
        $ok = $stmt->execute() && $stmt->affected_rows > 0;
        $stmt->close();
        $db->close();

        echo json_encode(['success' => $ok, 'message' => $ok ? 'Usuario eliminado' : 'Error al eliminar']);
    }
}
