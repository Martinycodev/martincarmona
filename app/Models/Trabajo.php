<?php

namespace App\Models;

require_once BASE_PATH . '/config/database.php';

class Trabajo
{
    private $db;
    
    public function __construct()
    {
        $this->db = \Database::connect();
    }
    
    /**
     * Crear un nuevo tipo de trabajo
     */
    public function create($data, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO trabajos (nombre, descripcion, categoria, precio_hora, estado, id_user, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, CURDATE(), CURDATE())
            ");
            
            $stmt->bind_param("sssdsi", 
                $data['nombre'],
                $data['descripcion'],
                $data['categoria'],
                $data['precio_hora'],
                $data['estado'],
                $userId
            );
            
            $result = $stmt->execute();
            $insertId = $this->db->insert_id;
            $stmt->close();
            
            return $result ? $insertId : false;
            
        } catch (\Exception $e) {
            error_log("Error creando trabajo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener todos los tipos de trabajo del usuario
     */
    public function getAll($userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    id,
                    nombre,
                    descripcion,
                    categoria,
                    precio_hora,
                    estado,
                    created_at
                FROM trabajos 
                WHERE id_user = ?
                ORDER BY categoria ASC, nombre ASC
            ");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $trabajos = [];
            while ($row = $result->fetch_assoc()) {
                $trabajos[] = $row;
            }
            
            $stmt->close();
            return $trabajos;
            
        } catch (\Exception $e) {
            error_log("Error obteniendo trabajos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener un trabajo por ID
     */
    public function getById($id, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM trabajos 
                WHERE id = ? AND id_user = ?
            ");
            $stmt->bind_param("ii", $id, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $trabajo = $result->fetch_assoc();
            $stmt->close();
            
            return $trabajo;
            
        } catch (\Exception $e) {
            error_log("Error obteniendo trabajo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar un trabajo existente
     */
    public function update($data, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE trabajos 
                SET nombre = ?, descripcion = ?, categoria = ?, precio_hora = ?, estado = ?, updated_at = CURDATE()
                WHERE id = ? AND id_user = ?
            ");
            
            $stmt->bind_param("sssdii", 
                $data['nombre'],
                $data['descripcion'],
                $data['categoria'],
                $data['precio_hora'],
                $data['estado'],
                $data['id'],
                $userId
            );
            
            $result = $stmt->execute();
            $stmt->close();
            
            return $result;
            
        } catch (\Exception $e) {
            error_log("Error actualizando trabajo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar un trabajo
     */
    public function delete($id, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM trabajos 
                WHERE id = ? AND id_user = ?
            ");
            
            $stmt->bind_param("ii", $id, $userId);
            $result = $stmt->execute();
            $stmt->close();
            
            return $result;
            
        } catch (\Exception $e) {
            error_log("Error eliminando trabajo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener trabajos activos
     */
    public function getActivos($userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, nombre, categoria, precio_hora 
                FROM trabajos 
                WHERE estado = 'activo' AND id_user = ?
                ORDER BY categoria ASC, nombre ASC
            ");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $trabajos = [];
            while ($row = $result->fetch_assoc()) {
                $trabajos[] = $row;
            }
            
            $stmt->close();
            return $trabajos;
            
        } catch (\Exception $e) {
            error_log("Error obteniendo trabajos activos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener trabajos por categoría
     */
    public function getByCategoria($categoria, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, nombre, descripcion, precio_hora 
                FROM trabajos 
                WHERE categoria = ? AND estado = 'activo' AND id_user = ?
                ORDER BY nombre ASC
            ");
            $stmt->bind_param("si", $categoria, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $trabajos = [];
            while ($row = $result->fetch_assoc()) {
                $trabajos[] = $row;
            }
            
            $stmt->close();
            return $trabajos;
            
        } catch (\Exception $e) {
            error_log("Error obteniendo trabajos por categoría: " . $e->getMessage());
            return [];
        }
    }
    
    public function __destruct()
    {
        if ($this->db) {
            $this->db->close();
        }
    }
}
