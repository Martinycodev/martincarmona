<?php

namespace App\Models;

require_once BASE_PATH . '/config/database.php';

class Proveedor
{
    private $db;
    
    public function __construct()
    {
        $this->db = \Database::connect();
    }
    
    /**
     * Crear un nuevo proveedor
     */
    public function create($data, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO proveedores (nombre, razon_social, cif, direccion, telefono, email, contacto_principal, sector, productos_servicios, condiciones_pago, estado, notas, id_user, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), CURDATE())
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
                $data['productos_servicios'],
                $data['condiciones_pago'],
                $data['estado'],
                $data['notas'],
                $userId
            );
            
            $result = $stmt->execute();
            $insertId = $this->db->insert_id;
            $stmt->close();
            
            return $result ? $insertId : false;
            
        } catch (\Exception $e) {
            error_log("Error creando proveedor: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener todos los proveedores del usuario
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
                    productos_servicios,
                    condiciones_pago,
                    estado,
                    notas,
                    created_at
                FROM proveedores 
                WHERE id_user = ?
                ORDER BY nombre ASC
            ");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $proveedores = [];
            while ($row = $result->fetch_assoc()) {
                $proveedores[] = $row;
            }
            
            $stmt->close();
            return $proveedores;
            
        } catch (\Exception $e) {
            error_log("Error obteniendo proveedores: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener un proveedor por ID
     */
    public function getById($id, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM proveedores 
                WHERE id = ? AND id_user = ?
            ");
            $stmt->bind_param("ii", $id, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $proveedor = $result->fetch_assoc();
            $stmt->close();
            
            return $proveedor;
            
        } catch (\Exception $e) {
            error_log("Error obteniendo proveedor: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar un proveedor existente
     */
    public function update($data, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE proveedores 
                SET nombre = ?, razon_social = ?, cif = ?, direccion = ?, telefono = ?, email = ?, contacto_principal = ?, sector = ?, productos_servicios = ?, condiciones_pago = ?, estado = ?, notas = ?, updated_at = CURDATE()
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
                $data['productos_servicios'],
                $data['condiciones_pago'],
                $data['estado'],
                $data['notas'],
                $data['id'],
                $userId
            );
            
            $result = $stmt->execute();
            $stmt->close();
            
            return $result;
            
        } catch (\Exception $e) {
            error_log("Error actualizando proveedor: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar un proveedor
     */
    public function delete($id, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM proveedores 
                WHERE id = ? AND id_user = ?
            ");
            
            $stmt->bind_param("ii", $id, $userId);
            $result = $stmt->execute();
            $stmt->close();
            
            return $result;
            
        } catch (\Exception $e) {
            error_log("Error eliminando proveedor: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener proveedores activos
     */
    public function getActivos($userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, nombre, sector, productos_servicios, contacto_principal, telefono 
                FROM proveedores 
                WHERE estado = 'activo' AND id_user = ?
                ORDER BY nombre ASC
            ");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $proveedores = [];
            while ($row = $result->fetch_assoc()) {
                $proveedores[] = $row;
            }
            
            $stmt->close();
            return $proveedores;
            
        } catch (\Exception $e) {
            error_log("Error obteniendo proveedores activos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener proveedores por sector
     */
    public function getBySector($sector, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, nombre, productos_servicios, contacto_principal, telefono, email 
                FROM proveedores 
                WHERE sector = ? AND estado = 'activo' AND id_user = ?
                ORDER BY nombre ASC
            ");
            $stmt->bind_param("si", $sector, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $proveedores = [];
            while ($row = $result->fetch_assoc()) {
                $proveedores[] = $row;
            }
            
            $stmt->close();
            return $proveedores;
            
        } catch (\Exception $e) {
            error_log("Error obteniendo proveedores por sector: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Buscar proveedor por CIF
     */
    public function getByCIF($cif, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM proveedores 
                WHERE cif = ? AND id_user = ?
            ");
            $stmt->bind_param("si", $cif, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $proveedor = $result->fetch_assoc();
            $stmt->close();
            
            return $proveedor;
            
        } catch (\Exception $e) {
            error_log("Error buscando proveedor por CIF: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Buscar proveedores por nombre
     */
    public function searchByName($nombre, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, nombre, sector, productos_servicios, contacto_principal 
                FROM proveedores 
                WHERE nombre LIKE ? AND id_user = ?
                ORDER BY nombre ASC
            ");
            $searchTerm = "%{$nombre}%";
            $stmt->bind_param("si", $searchTerm, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $proveedores = [];
            while ($row = $result->fetch_assoc()) {
                $proveedores[] = $row;
            }
            
            $stmt->close();
            return $proveedores;
            
        } catch (\Exception $e) {
            error_log("Error buscando proveedores por nombre: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Buscar proveedores por productos o servicios
     */
    public function searchByProductos($productos, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, nombre, sector, productos_servicios, contacto_principal, telefono 
                FROM proveedores 
                WHERE productos_servicios LIKE ? AND estado = 'activo' AND id_user = ?
                ORDER BY nombre ASC
            ");
            $searchTerm = "%{$productos}%";
            $stmt->bind_param("si", $searchTerm, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $proveedores = [];
            while ($row = $result->fetch_assoc()) {
                $proveedores[] = $row;
            }
            
            $stmt->close();
            return $proveedores;
            
        } catch (\Exception $e) {
            error_log("Error buscando proveedores por productos: " . $e->getMessage());
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
