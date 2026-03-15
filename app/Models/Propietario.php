<?php

namespace App\Models;

/**
 * Modelo para gestión de propietarios de parcelas.
 * Tabla: propietarios
 */
class Propietario
{
    private $db;

    public function __construct()
    {
        $this->db = \Database::connect();
    }

    /**
     * Obtener todos los propietarios del usuario
     */
    public function getAll($userId)
    {
        $stmt = $this->db->prepare("SELECT * FROM propietarios WHERE id_user = ? ORDER BY nombre");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $result;
    }

    /**
     * Obtener un propietario por ID
     */
    public function find($id, $userId)
    {
        $stmt = $this->db->prepare("SELECT * FROM propietarios WHERE id = ? AND id_user = ?");
        $stmt->bind_param("ii", $id, $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }

    /**
     * Crear nuevo propietario
     */
    public function create($nombre, $apellidos, $dni, $telefono, $email, $userId)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO propietarios (nombre, apellidos, dni, telefono, email, id_user) VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("sssssi", $nombre, $apellidos, $dni, $telefono, $email, $userId);
        $ok = $stmt->execute();
        $id = $this->db->insert_id;
        $stmt->close();
        return $ok ? $id : false;
    }

    /**
     * Actualizar propietario
     */
    public function update($id, $nombre, $apellidos, $dni, $telefono, $email, $userId)
    {
        $stmt = $this->db->prepare(
            "UPDATE propietarios SET nombre = ?, apellidos = ?, dni = ?, telefono = ?, email = ? WHERE id = ? AND id_user = ?"
        );
        $stmt->bind_param("sssssii", $nombre, $apellidos, $dni, $telefono, $email, $id, $userId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    /**
     * Eliminar propietario (si no tiene parcelas asignadas)
     */
    public function delete($id, $userId)
    {
        // Verificar si tiene parcelas asignadas
        $stmt = $this->db->prepare("SELECT COUNT(*) AS count FROM parcelas WHERE propietario_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $count = $stmt->get_result()->fetch_assoc()['count'];
        $stmt->close();

        if ($count > 0) {
            return false;
        }

        $stmt = $this->db->prepare("DELETE FROM propietarios WHERE id = ? AND id_user = ?");
        $stmt->bind_param("ii", $id, $userId);
        $ok = $stmt->execute() && $stmt->affected_rows > 0;
        $stmt->close();
        return $ok;
    }

    /**
     * Obtener parcelas de un propietario
     */
    public function getParcelas($propietarioId, $userId)
    {
        $stmt = $this->db->prepare("SELECT id, nombre, ubicacion, olivos FROM parcelas WHERE propietario_id = ? AND id_user = ? ORDER BY nombre");
        $stmt->bind_param("ii", $propietarioId, $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $result;
    }

    /**
     * Actualizar imagen DNI (anverso o reverso)
     */
    public function actualizarImagenDni($id, $lado, $imagePath, $userId)
    {
        $campo = ($lado === 'anverso') ? 'imagen_dni_anverso' : 'imagen_dni_reverso';
        $stmt = $this->db->prepare("UPDATE propietarios SET {$campo} = ? WHERE id = ? AND id_user = ?");
        $stmt->bind_param("sii", $imagePath, $id, $userId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
}
