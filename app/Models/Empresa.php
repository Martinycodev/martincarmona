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
                INSERT INTO empresas (nombre, razon_social, cif, direccion, telefono, email, contacto_principal, sector, tipo_cliente, estado, notas, id_user, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), CURDATE())
            ");
            
            $stmt->bind_param("sssssssssssi", 
                $data['nombre'],
                $data['razon_social'],
                $data['cif'],
                $data['direccion'],
                $data['telefono'],
                $data['email'],
                $data['contacto_principal'],
                $data['sector'],
                $data['tipo_cliente'],
                $data['estado'],
                $data['notas'],
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
                    razon_social,
                    cif,
                    direccion,
                    telefono,
                    email,
                    contacto_principal,
                    sector,
                    tipo_cliente,
                    estado,
                    notas,
                    created_at
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
                SET nombre = ?, razon_social = ?, cif = ?, direccion = ?, telefono = ?, email = ?, contacto_principal = ?, sector = ?, tipo_cliente = ?, estado = ?, notas = ?, updated_at = CURDATE()
                WHERE id = ? AND id_user = ?
            ");
            
            $stmt->bind_param("sssssssssssii", 
                $data['nombre'],
                $data['razon_social'],
                $data['cif'],
                $data['direccion'],
                $data['telefono'],
                $data['email'],
                $data['contacto_principal'],
                $data['sector'],
                $data['tipo_cliente'],
                $data['estado'],
                $data['notas'],
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
     * Obtener empresas activas
     */
    public function getActivas($userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, nombre, sector, tipo_cliente, contacto_principal 
                FROM empresas 
                WHERE estado = 'activa' AND id_user = ?
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
            error_log("Error obteniendo empresas activas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener empresas por tipo de cliente
     */
    public function getByTipoCliente($tipoCliente, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, nombre, sector, contacto_principal, telefono, email 
                FROM empresas 
                WHERE tipo_cliente = ? AND estado = 'activa' AND id_user = ?
                ORDER BY nombre ASC
            ");
            $stmt->bind_param("si", $tipoCliente, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $empresas = [];
            while ($row = $result->fetch_assoc()) {
                $empresas[] = $row;
            }
            
            $stmt->close();
            return $empresas;
            
        } catch (\Exception $e) {
            error_log("Error obteniendo empresas por tipo de cliente: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Buscar empresa por CIF
     */
    public function getByCIF($cif, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM empresas 
                WHERE cif = ? AND id_user = ?
            ");
            $stmt->bind_param("si", $cif, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $empresa = $result->fetch_assoc();
            $stmt->close();
            
            return $empresa;
            
        } catch (\Exception $e) {
            error_log("Error buscando empresa por CIF: " . $e->getMessage());
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
                SELECT id, nombre, sector, tipo_cliente, contacto_principal 
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
    
    public function __destruct()
    {
        if ($this->db) {
            $this->db->close();
        }
    }
}
