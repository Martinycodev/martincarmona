<?php

namespace App\Models;

require_once BASE_PATH . '/config/database.php';

class Empresa
{
    private $db;
    
    public function __construct()
    {
        $this->db = \Database::connect();
    }
    
    /**
     * Crear una nueva empresa
     */
    public function create($data, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO empresas (nombre, dni, id_user)
                VALUES (?, ?, ?)
            ");
            
            $stmt->bind_param("ssi", 
                $data['nombre'],
                $data['dni'],
                $userId
            );
            
            $result = $stmt->execute();
            $insertId = $this->db->insert_id;
            $stmt->close();
            
            return $result ? $insertId : false;
            
        } catch (\Exception $e) {
            error_log("Error creando empresa: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener todas las empresas del usuario
     */
    public function getAll($userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    id,
                    nombre,
                    dni
                FROM empresas 
                WHERE id_user = ?
                ORDER BY nombre ASC
            ");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $empresas = [];
            while ($row = $result->fetch_assoc()) {
                $empresas[] = $row;
            }
            
            $stmt->close();
            return $empresas;
            
        } catch (\Exception $e) {
            error_log("Error obteniendo empresas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener una empresa por ID
     */
    public function getById($id, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM empresas 
                WHERE id = ? AND id_user = ?
            ");
            $stmt->bind_param("ii", $id, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $empresa = $result->fetch_assoc();
            $stmt->close();
            
            return $empresa;
            
        } catch (\Exception $e) {
            error_log("Error obteniendo empresa: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar una empresa existente
     */
    public function update($data, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE empresas 
                SET nombre = ?, dni = ?
                WHERE id = ? AND id_user = ?
            ");
            
            $stmt->bind_param("ssii", 
                $data['nombre'],
                $data['dni'],
                $data['id'],
                $userId
            );
            
            $result = $stmt->execute();
            $stmt->close();
            
            return $result;
            
        } catch (\Exception $e) {
            error_log("Error actualizando empresa: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar una empresa
     */
    public function delete($id, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM empresas 
                WHERE id = ? AND id_user = ?
            ");
            
            $stmt->bind_param("ii", $id, $userId);
            $result = $stmt->execute();
            $stmt->close();
            
            return $result;
            
        } catch (\Exception $e) {
            error_log("Error eliminando empresa: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Buscar empresa por DNI
     */
    public function getByDNI($dni, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, nombre, dni 
                FROM empresas 
                WHERE dni = ? AND id_user = ?
            ");
            $stmt->bind_param("si", $dni, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $empresa = $result->fetch_assoc();
            $stmt->close();
            
            return $empresa;
            
        } catch (\Exception $e) {
            error_log("Error buscando empresa por DNI: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Buscar empresas por nombre
     */
    public function searchByName($nombre, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, nombre, dni 
                FROM empresas 
                WHERE nombre LIKE ? AND id_user = ?
                ORDER BY nombre ASC
            ");
            $searchTerm = "%{$nombre}%";
            $stmt->bind_param("si", $searchTerm, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $empresas = [];
            while ($row = $result->fetch_assoc()) {
                $empresas[] = $row;
            }
            
            $stmt->close();
            return $empresas;
            
        } catch (\Exception $e) {
            error_log("Error buscando empresas por nombre: " . $e->getMessage());
            return [];
        }
    }
    
    }
}
