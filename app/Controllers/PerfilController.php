<?php

namespace App\Controllers;


class PerfilController extends BaseController
{
    public function __construct()
    {
    }

    public function index()
    {
        $this->requireEmpresa();

        // Obtener información del usuario desde la sesión
        $userId = $_SESSION['user_id'];
        $userName = $_SESSION['user_name'] ?? '';
        $userEmail = $_SESSION['user_email'] ?? '';
        
        // Preparar datos para la vista
        $userRol = $_SESSION['user_rol'] ?? 'empresa';
        $data = [
            'user' => [
                'id' => $userId,
                'name' => $userName,
                'email' => $userEmail,
                'rol' => $userRol
            ]
        ];
        
        $this->render('perfil/index', $data);
    }

    public function actualizarNombre()
    {
        // Verificar si el usuario está autenticado
        if (!isset($_SESSION['user_id'])) {
            $this->json([
                'success' => false,
                'message' => 'No autorizado'
            ]);
            return;
        }

        // Verificar que sea una petición POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json([
                'success' => false,
                'message' => 'Método no permitido'
            ]);
            return;
        }

        $this->validateCsrf();

        // Obtener datos del cuerpo de la petición
        $input = json_decode(file_get_contents('php://input'), true);
        $nuevoNombre = trim($input['nombre'] ?? '');

        // Validar que el nombre no esté vacío
        if (empty($nuevoNombre)) {
            $this->json([
                'success' => false,
                'message' => 'El nombre no puede estar vacío'
            ]);
            return;
        }

        // Validar longitud del nombre
        if (strlen($nuevoNombre) > 255) {
            $this->json([
                'success' => false,
                'message' => 'El nombre es demasiado largo'
            ]);
            return;
        }

        try {
            $db = \Database::connect();
            $userId = $_SESSION['user_id'];
            
            // Actualizar el nombre en la base de datos
            $stmt = $db->prepare("UPDATE usuarios SET name = ? WHERE id = ?");
            $stmt->bind_param("si", $nuevoNombre, $userId);
            
            if ($stmt->execute()) {
                // Actualizar la sesión con el nuevo nombre
                $_SESSION['user_name'] = $nuevoNombre;
                
                $stmt->close();
                $db->close();
                
                $this->json([
                    'success' => true,
                    'message' => 'Nombre actualizado correctamente',
                    'nombre' => $nuevoNombre
                ]);
            } else {
                $stmt->close();
                $db->close();
                
                $this->json([
                    'success' => false,
                    'message' => 'Error al actualizar el nombre'
                ]);
            }
        } catch (\Exception $e) {
            \Core\Logger::app()->error("Error actualizando nombre: " . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Error en el servidor'
            ]);
        }
    }

    /**
     * Listar trabajadores y propietarios del usuario empresa con info de si tienen usuario creado.
     * Devuelve JSON para la sección de gestión de usuarios en el perfil.
     */
    public function usuariosVinculados()
    {
        $this->requireEmpresa();

        $userId = $_SESSION['user_id'];
        $db = \Database::connect();

        // Trabajadores del usuario empresa con su usuario vinculado (si existe)
        $stmt = $db->prepare("
            SELECT t.id, t.nombre, t.apellidos, t.dni, 'trabajador' AS tipo,
                   u.id AS usuario_id, u.email AS usuario_email
            FROM trabajadores t
            LEFT JOIN usuarios u ON u.trabajador_id = t.id AND u.rol = 'trabajador'
            WHERE t.id_user = ? AND t.estado = 'activo'
            ORDER BY t.nombre
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $trabajadores = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Propietarios del usuario empresa con su usuario vinculado (si existe)
        $stmt = $db->prepare("
            SELECT p.id, p.nombre, p.apellidos, p.dni, 'propietario' AS tipo,
                   u.id AS usuario_id, u.email AS usuario_email
            FROM propietarios p
            LEFT JOIN usuarios u ON u.propietario_id = p.id AND u.rol = 'propietario'
            WHERE p.id_user = ?
            ORDER BY p.nombre
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $propietarios = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $db->close();

        $this->json([
            'success' => true,
            'trabajadores' => $trabajadores,
            'propietarios' => $propietarios
        ]);
    }

    /**
     * Crear usuario vinculado a un trabajador o propietario.
     * El email tiene formato nombre@miolivar.es
     */
    public function crearUsuarioVinculado()
    {
        $this->requireEmpresa();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $this->validateCsrf();

        $input = json_decode(file_get_contents('php://input'), true);
        $tipo = $input['tipo'] ?? ''; // 'trabajador' o 'propietario'
        $vinculadoId = intval($input['vinculado_id'] ?? 0);
        $nickname = trim($input['nickname'] ?? '');
        $password = $input['password'] ?? '';

        // Validaciones básicas
        if (!in_array($tipo, ['trabajador', 'propietario'])) {
            $this->json(['success' => false, 'message' => 'Tipo inválido']);
            return;
        }
        if (!$vinculadoId) {
            $this->json(['success' => false, 'message' => 'ID de ' . $tipo . ' inválido']);
            return;
        }
        if (empty($nickname)) {
            $this->json(['success' => false, 'message' => 'El nombre de usuario es obligatorio']);
            return;
        }
        if (empty($password) || strlen($password) < 6) {
            $this->json(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres']);
            return;
        }

        // Validar formato del nickname (solo letras, números, puntos y guiones)
        if (!preg_match('/^[a-zA-Z0-9._-]+$/', $nickname)) {
            $this->json(['success' => false, 'message' => 'El nombre de usuario solo puede contener letras, números, puntos, guiones y guiones bajos']);
            return;
        }

        $email = $nickname . '@miolivar.es';
        $userId = $_SESSION['user_id'];
        $db = \Database::connect();

        // Verificar que el trabajador/propietario pertenece a este usuario empresa
        $tabla = $tipo === 'trabajador' ? 'trabajadores' : 'propietarios';
        $stmt = $db->prepare("SELECT id, nombre FROM $tabla WHERE id = ? AND id_user = ?");
        $stmt->bind_param("ii", $vinculadoId, $userId);
        $stmt->execute();
        $registro = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$registro) {
            $db->close();
            $this->json(['success' => false, 'message' => ucfirst($tipo) . ' no encontrado']);
            return;
        }

        // Verificar que no tenga ya un usuario creado
        $campoFk = $tipo === 'trabajador' ? 'trabajador_id' : 'propietario_id';
        $stmt = $db->prepare("SELECT id FROM usuarios WHERE $campoFk = ? AND rol = ?");
        $stmt->bind_param("is", $vinculadoId, $tipo);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $stmt->close();
            $db->close();
            $this->json(['success' => false, 'message' => 'Este ' . $tipo . ' ya tiene un usuario creado']);
            return;
        }
        $stmt->close();

        // Verificar que el email no exista
        $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $stmt->close();
            $db->close();
            $this->json(['success' => false, 'message' => 'Ya existe un usuario con el nombre "' . htmlspecialchars($nickname) . '@miolivar.es"']);
            return;
        }
        $stmt->close();

        // Crear el usuario
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $name = $registro['nombre'];
        $propietarioId = $tipo === 'propietario' ? $vinculadoId : null;
        $trabajadorId = $tipo === 'trabajador' ? $vinculadoId : null;

        $stmt = $db->prepare("
            INSERT INTO usuarios (name, email, password, rol, propietario_id, trabajador_id, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->bind_param("ssssii", $name, $email, $passwordHash, $tipo, $propietarioId, $trabajadorId);

        if ($stmt->execute()) {
            $nuevoId = $db->insert_id;
            $stmt->close();
            $db->close();
            \Core\Logger::app()->info("Usuario vinculado creado: $email (rol: $tipo, id: $nuevoId) por empresa user_id=$userId");
            $this->json([
                'success' => true,
                'message' => 'Usuario creado correctamente',
                'usuario' => [
                    'id' => $nuevoId,
                    'email' => $email
                ]
            ]);
        } else {
            $error = $stmt->error;
            $stmt->close();
            $db->close();
            \Core\Logger::app()->error("Error creando usuario vinculado: $error");
            $this->json(['success' => false, 'message' => 'Error al crear el usuario']);
        }
    }

    /**
     * Cambiar contraseña de un usuario vinculado (trabajador o propietario).
     * Solo el usuario empresa puede hacerlo.
     */
    public function cambiarPasswordUsuario()
    {
        $this->requireEmpresa();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $this->validateCsrf();

        $input = json_decode(file_get_contents('php://input'), true);
        $usuarioId = intval($input['usuario_id'] ?? 0);
        $nuevaPassword = $input['password'] ?? '';

        if (!$usuarioId) {
            $this->json(['success' => false, 'message' => 'ID de usuario inválido']);
            return;
        }
        if (empty($nuevaPassword) || strlen($nuevaPassword) < 6) {
            $this->json(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres']);
            return;
        }

        $userId = $_SESSION['user_id'];
        $db = \Database::connect();

        // Verificar que el usuario a modificar es un trabajador/propietario vinculado a este empresa
        $stmt = $db->prepare("
            SELECT u.id, u.rol, u.trabajador_id, u.propietario_id
            FROM usuarios u
            WHERE u.id = ? AND u.rol IN ('trabajador', 'propietario')
        ");
        $stmt->bind_param("i", $usuarioId);
        $stmt->execute();
        $usuario = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$usuario) {
            $db->close();
            $this->json(['success' => false, 'message' => 'Usuario no encontrado o no es un trabajador/propietario']);
            return;
        }

        // Verificar que el trabajador/propietario pertenece a este empresa
        $pertenece = false;
        if ($usuario['rol'] === 'trabajador' && $usuario['trabajador_id']) {
            $stmt = $db->prepare("SELECT id FROM trabajadores WHERE id = ? AND id_user = ?");
            $stmt->bind_param("ii", $usuario['trabajador_id'], $userId);
            $stmt->execute();
            $pertenece = $stmt->get_result()->num_rows > 0;
            $stmt->close();
        } elseif ($usuario['rol'] === 'propietario' && $usuario['propietario_id']) {
            $stmt = $db->prepare("SELECT id FROM propietarios WHERE id = ? AND id_user = ?");
            $stmt->bind_param("ii", $usuario['propietario_id'], $userId);
            $stmt->execute();
            $pertenece = $stmt->get_result()->num_rows > 0;
            $stmt->close();
        }

        if (!$pertenece) {
            $db->close();
            $this->json(['success' => false, 'message' => 'No tienes permiso para modificar este usuario']);
            return;
        }

        // Cambiar la contraseña
        $passwordHash = password_hash($nuevaPassword, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE usuarios SET password = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("si", $passwordHash, $usuarioId);
        $ok = $stmt->execute();
        $stmt->close();
        $db->close();

        if ($ok) {
            \Core\Logger::app()->info("Contraseña cambiada para usuario vinculado id=$usuarioId por empresa user_id=$userId");
            $this->json(['success' => true, 'message' => 'Contraseña actualizada correctamente']);
        } else {
            $this->json(['success' => false, 'message' => 'Error al actualizar la contraseña']);
        }
    }

    /**
     * Eliminar usuario vinculado (trabajador o propietario).
     * Solo el usuario empresa puede hacerlo.
     */
    public function eliminarUsuarioVinculado()
    {
        $this->requireEmpresa();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $this->validateCsrf();

        $input = json_decode(file_get_contents('php://input'), true);
        $usuarioId = intval($input['usuario_id'] ?? 0);

        if (!$usuarioId) {
            $this->json(['success' => false, 'message' => 'ID de usuario inválido']);
            return;
        }

        $userId = $_SESSION['user_id'];
        $db = \Database::connect();

        // Verificar que el usuario a eliminar es un trabajador/propietario
        $stmt = $db->prepare("
            SELECT u.id, u.rol, u.trabajador_id, u.propietario_id, u.email
            FROM usuarios u
            WHERE u.id = ? AND u.rol IN ('trabajador', 'propietario')
        ");
        $stmt->bind_param("i", $usuarioId);
        $stmt->execute();
        $usuario = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$usuario) {
            $db->close();
            $this->json(['success' => false, 'message' => 'Usuario no encontrado o no es un trabajador/propietario']);
            return;
        }

        // Verificar pertenencia al empresa
        $pertenece = false;
        if ($usuario['rol'] === 'trabajador' && $usuario['trabajador_id']) {
            $stmt = $db->prepare("SELECT id FROM trabajadores WHERE id = ? AND id_user = ?");
            $stmt->bind_param("ii", $usuario['trabajador_id'], $userId);
            $stmt->execute();
            $pertenece = $stmt->get_result()->num_rows > 0;
            $stmt->close();
        } elseif ($usuario['rol'] === 'propietario' && $usuario['propietario_id']) {
            $stmt = $db->prepare("SELECT id FROM propietarios WHERE id = ? AND id_user = ?");
            $stmt->bind_param("ii", $usuario['propietario_id'], $userId);
            $stmt->execute();
            $pertenece = $stmt->get_result()->num_rows > 0;
            $stmt->close();
        }

        if (!$pertenece) {
            $db->close();
            $this->json(['success' => false, 'message' => 'No tienes permiso para eliminar este usuario']);
            return;
        }

        // Eliminar el usuario
        $stmt = $db->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $usuarioId);
        $ok = $stmt->execute() && $stmt->affected_rows > 0;
        $stmt->close();
        $db->close();

        if ($ok) {
            \Core\Logger::app()->info("Usuario vinculado eliminado: {$usuario['email']} (id=$usuarioId) por empresa user_id=$userId");
            $this->json(['success' => true, 'message' => 'Usuario eliminado correctamente']);
        } else {
            $this->json(['success' => false, 'message' => 'Error al eliminar el usuario']);
        }
    }

    /**
     * Cambiar contraseña del usuario
     */
    public function cambiarPassword()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->json(['success' => false, 'message' => 'No autorizado']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $this->validateCsrf();

        $input = json_decode(file_get_contents('php://input'), true);
        $actual = $input['password_actual'] ?? '';
        $nueva = $input['password_nueva'] ?? '';
        $confirmacion = $input['password_confirmacion'] ?? '';

        // Validaciones
        if (empty($actual) || empty($nueva) || empty($confirmacion)) {
            $this->json(['success' => false, 'message' => 'Todos los campos son obligatorios']);
            return;
        }

        if ($nueva !== $confirmacion) {
            $this->json(['success' => false, 'message' => 'La nueva contraseña y su confirmación no coinciden']);
            return;
        }

        if (strlen($nueva) < 6) {
            $this->json(['success' => false, 'message' => 'La nueva contraseña debe tener al menos 6 caracteres']);
            return;
        }

        try {
            $db = \Database::connect();
            $userId = $_SESSION['user_id'];

            // Obtener contraseña actual de la BD
            $stmt = $db->prepare("SELECT password FROM usuarios WHERE id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            if (!$user) {
                $db->close();
                $this->json(['success' => false, 'message' => 'Usuario no encontrado']);
                return;
            }

            // Verificar contraseña actual
            if (!password_verify($actual, $user['password'])) {
                $db->close();
                $this->json(['success' => false, 'message' => 'La contraseña actual es incorrecta']);
                return;
            }

            // Hash de la nueva contraseña
            $hash = password_hash($nueva, PASSWORD_BCRYPT);

            $stmt = $db->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hash, $userId);

            if ($stmt->execute()) {
                $stmt->close();
                $db->close();
                $this->json(['success' => true, 'message' => 'Contraseña actualizada correctamente']);
            } else {
                $stmt->close();
                $db->close();
                $this->json(['success' => false, 'message' => 'Error al actualizar la contraseña']);
            }
        } catch (\Exception $e) {
            \Core\Logger::app()->error("Error cambiando contraseña: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error en el servidor']);
        }
    }
}

