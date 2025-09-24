<?php

namespace App\Models;

require_once BASE_PATH . '/config/database.php';

class Vehiculo
{
    private $db;
    
    public function __construct()
    {
        $this->db = \Database::connect();
    }
    
    /**
     * Crear un nuevo vehículo
     */
    public function create($data, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO vehiculos (matricula, marca, modelo, tipo, ano, color, combustible, capacidad, estado, fecha_adquisicion, precio_compra, id_user, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), CURDATE())
            ");
            
            $stmt->bind_param("ssssisssdsdi", 
                $data['matricula'],
                $data['marca'],
                $data['modelo'],
                $data['tipo'],
                $data['ano'],
                $data['color'],
                $data['combustible'],
                $data['capacidad'],
                $data['estado'],
                $data['fecha_adquisicion'],
                $data['precio_compra'],
                $userId
            );
            
            $result = $stmt->execute();
            $insertId = $this->db->insert_id;
            $stmt->close();
            
            return $result ? $insertId : false;
            
        } catch (\Exception $e) {
            error_log("Error creando vehículo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener todos los vehículos del usuario
     */
    public function getAll($userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    id,
                    matricula,
                    marca,
                    modelo,
                    tipo,
                    ano,
                    color,
                    combustible,
                    capacidad,
                    estado,
                    fecha_adquisicion,
                    precio_compra,
                    created_at
                FROM vehiculos 
                WHERE id_user = ?
                ORDER BY marca ASC, modelo ASC
            ");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $vehiculos = [];
            while ($row = $result->fetch_assoc()) {
                $vehiculos[] = $row;
            }
            
            $stmt->close();
            return $vehiculos;
            
        } catch (\Exception $e) {
            error_log("Error obteniendo vehículos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener un vehículo por ID
     */
    public function getById($id, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM vehiculos 
                WHERE id = ? AND id_user = ?
            ");
            $stmt->bind_param("ii", $id, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $vehiculo = $result->fetch_assoc();
            $stmt->close();
            
            return $vehiculo;
            
        } catch (\Exception $e) {
            error_log("Error obteniendo vehículo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar un vehículo existente
     */
    public function update($data, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE vehiculos 
                SET matricula = ?, marca = ?, modelo = ?, tipo = ?, ano = ?, color = ?, combustible = ?, capacidad = ?, estado = ?, fecha_adquisicion = ?, precio_compra = ?, updated_at = CURDATE()
                WHERE id = ? AND id_user = ?
            ");
            
            $stmt->bind_param("ssssisssdsdii", 
                $data['matricula'],
                $data['marca'],
                $data['modelo'],
                $data['tipo'],
                $data['ano'],
                $data['color'],
                $data['combustible'],
                $data['capacidad'],
                $data['estado'],
                $data['fecha_adquisicion'],
                $data['precio_compra'],
                $data['id'],
                $userId
            );
            
            $result = $stmt->execute();
            $stmt->close();
            
            return $result;
            
        } catch (\Exception $e) {
            error_log("Error actualizando vehículo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar un vehículo
     */
    public function delete($id, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM vehiculos 
                WHERE id = ? AND id_user = ?
            ");
            
            $stmt->bind_param("ii", $id, $userId);
            $result = $stmt->execute();
            $stmt->close();
            
            return $result;
            
        } catch (\Exception $e) {
            error_log("Error eliminando vehículo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener vehículos activos
     */
    public function getActivos($userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, matricula, marca, modelo, tipo, estado 
                FROM vehiculos 
                WHERE estado = 'activo' AND id_user = ?
                ORDER BY marca ASC, modelo ASC
            ");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $vehiculos = [];
            while ($row = $result->fetch_assoc()) {
                $vehiculos[] = $row;
            }
            
            $stmt->close();
            return $vehiculos;
            
        } catch (\Exception $e) {
            error_log("Error obteniendo vehículos activos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener vehículos por tipo
     */
    public function getByTipo($tipo, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, matricula, marca, modelo, estado 
                FROM vehiculos 
                WHERE tipo = ? AND estado = 'activo' AND id_user = ?
                ORDER BY marca ASC, modelo ASC
            ");
            $stmt->bind_param("si", $tipo, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $vehiculos = [];
            while ($row = $result->fetch_assoc()) {
                $vehiculos[] = $row;
            }
            
            $stmt->close();
            return $vehiculos;
            
        } catch (\Exception $e) {
            error_log("Error obteniendo vehículos por tipo: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Buscar vehículo por matrícula
     */
    public function getByMatricula($matricula, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM vehiculos 
                WHERE matricula = ? AND id_user = ?
            ");
            $stmt->bind_param("si", $matricula, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $vehiculo = $result->fetch_assoc();
            $stmt->close();
            
            return $vehiculo;
            
        } catch (\Exception $e) {
            error_log("Error buscando vehículo por matrícula: " . $e->getMessage());
            return false;
        }
    }
    
    public function __destruct()
    {
        if ($this->db) {
            $this->db->close();
        }
    }
}
