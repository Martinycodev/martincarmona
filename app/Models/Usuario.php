<?php

namespace App\Models;

/**
 * Modelo para gestión de usuarios.
 * Tabla: usuarios
 */
class Usuario
{
    private $db;

    public function __construct()
    {
        $this->db = \Database::connect();
    }

    /**
     * Obtener todos los usuarios con datos de propietario/trabajador vinculado
     */
    public function getAll()
    {
        $stmt = $this->db->prepare("
            SELECT u.id, u.name, u.email, u.rol, u.propietario_id, u.trabajador_id,
                   CONCAT(p.nombre, IF(p.apellidos IS NOT NULL AND p.apellidos != '', CONCAT(' ', p.apellidos), '')) AS propietario_nombre,
                   t.nombre AS trabajador_nombre
            FROM usuarios u
            LEFT JOIN propietarios p ON u.propietario_id = p.id
            LEFT JOIN trabajadores t ON u.trabajador_id = t.id
            ORDER BY u.rol ASC, u.name ASC
        ");
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $result;
    }

    /**
     * Buscar usuario por email (para autenticación)
     */
    public function findByEmail($email)
    {
        $stmt = $this->db->prepare("SELECT id, name, email, password, rol, propietario_id, trabajador_id FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }

    /**
     * Buscar usuario por ID
     */
    public function find($id)
    {
        $stmt = $this->db->prepare("SELECT id, name, email, rol, propietario_id, trabajador_id FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }

    /**
     * Crear nuevo usuario
     */
    public function create($name, $email, $password, $rol, $propietarioId = null, $trabajadorId = null)
    {
        // Verificar email único
        $check = $this->db->prepare("SELECT id FROM usuarios WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $check->close();
            return ['error' => 'Ya existe un usuario con ese email'];
        }
        $check->close();

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("
            INSERT INTO usuarios (name, email, password, rol, propietario_id, trabajador_id, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->bind_param("ssssii", $name, $email, $passwordHash, $rol, $propietarioId, $trabajadorId);
        $ok = $stmt->execute();
        $id = $this->db->insert_id;
        $stmt->close();
        return $ok ? $id : false;
    }

    /**
     * Actualizar usuario (con o sin cambio de contraseña)
     */
    public function update($id, $name, $email, $rol, $propietarioId = null, $trabajadorId = null, $password = null)
    {
        // Email único (excluyendo el propio)
        $check = $this->db->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
        $check->bind_param("si", $email, $id);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $check->close();
            return ['error' => 'Ya existe otro usuario con ese email'];
        }
        $check->close();

        if (!empty($password)) {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("
                UPDATE usuarios SET name=?, email=?, password=?, rol=?, propietario_id=?, trabajador_id=?, updated_at=NOW()
                WHERE id=?
            ");
            $stmt->bind_param("ssssiii", $name, $email, $passwordHash, $rol, $propietarioId, $trabajadorId, $id);
        } else {
            $stmt = $this->db->prepare("
                UPDATE usuarios SET name=?, email=?, rol=?, propietario_id=?, trabajador_id=?, updated_at=NOW()
                WHERE id=?
            ");
            $stmt->bind_param("sssiii", $name, $email, $rol, $propietarioId, $trabajadorId, $id);
        }

        $ok = $stmt->execute() && $stmt->affected_rows >= 0;
        $stmt->close();
        return $ok;
    }

    /**
     * Eliminar usuario
     */
    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $id);
        $ok = $stmt->execute() && $stmt->affected_rows > 0;
        $stmt->close();
        return $ok;
    }

    /**
     * Contar usuarios con un rol específico
     */
    public function countByRol($rol)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS cnt FROM usuarios WHERE rol = ?");
        $stmt->bind_param("s", $rol);
        $stmt->execute();
        $count = $stmt->get_result()->fetch_assoc()['cnt'];
        $stmt->close();
        return $count;
    }

    /**
     * Obtener el rol actual de un usuario
     */
    public function getRol($id)
    {
        $stmt = $this->db->prepare("SELECT rol FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ? $row['rol'] : null;
    }
}
