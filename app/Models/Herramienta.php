<?php

namespace App\Models;

require_once BASE_PATH . '/config/database.php';

class Herramienta
{
    private $db;
    
    public function __construct()
    {
        $this->db = \Database::connect();
    }
    
    /**
     * Crear una nueva herramienta
     */
    public function create($data, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO herramientas (nombre, descripcion, categoria, marca, modelo, numero_serie, estado, ubicacion, fecha_adquisicion, precio_compra, vida_util, id_user, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), CURDATE())
            ");
            
            $stmt->bind_param("ssssssssdsdi", 
                $data['nombre'],
                $data['descripcion'],
                $data['categoria'],
                $data['marca'],
                $data['modelo'],
                $data['numero_serie'],
                $data['estado'],
                $data['ubicacion'],
                $data['fecha_adquisicion'],
                $data['precio_compra'],
                $data['vida_util'],
                $userId
            );
            
            $result = $stmt->execute();
            $insertId = $this->db->insert_id;
            $stmt->close();
            
            return $result ? $insertId : false;
            
        } catch (\Exception $e) {
            error_log("Error creando herramienta: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener todas las herramientas del usuario
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
                    marca,
                    modelo,
                    numero_serie,
                    estado,
                    ubicacion,
                    fecha_adquisicion,
                    precio_compra,
                    vida_util,
                    created_at
                FROM herramientas 
                WHERE id_user = ?
                ORDER BY categoria ASC, nombre ASC
            ");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $herramientas = [];
            while ($row = $result->fetch_assoc()) {
                $herramientas[] = $row;
            }
            
            $stmt->close();
            return $herramientas;
            
        } catch (\Exception $e) {
            error_log("Error obteniendo herramientas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener una herramienta por ID
     */
    public function getById($id, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM herramientas 
                WHERE id = ? AND id_user = ?
            ");
            $stmt->bind_param("ii", $id, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $herramienta = $result->fetch_assoc();
            $stmt->close();
            
            return $herramienta;
            
        } catch (\Exception $e) {
            error_log("Error obteniendo herramienta: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar una herramienta existente
     */
    public function update($data, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE herramientas 
                SET nombre = ?, descripcion = ?, categoria = ?, marca = ?, modelo = ?, numero_serie = ?, estado = ?, ubicacion = ?, fecha_adquisicion = ?, precio_compra = ?, vida_util = ?, updated_at = CURDATE()
                WHERE id = ? AND id_user = ?
            ");
            
            $stmt->bind_param("ssssssssdsdii", 
                $data['nombre'],
                $data['descripcion'],
                $data['categoria'],
                $data['marca'],
                $data['modelo'],
                $data['numero_serie'],
                $data['estado'],
                $data['ubicacion'],
                $data['fecha_adquisicion'],
                $data['precio_compra'],
                $data['vida_util'],
                $data['id'],
                $userId
            );
            
            $result = $stmt->execute();
            $stmt->close();
            
            return $result;
            
        } catch (\Exception $e) {
            error_log("Error actualizando herramienta: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar una herramienta
     */
    public function delete($id, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM herramientas 
                WHERE id = ? AND id_user = ?
            ");
            
            $stmt->bind_param("ii", $id, $userId);
            $result = $stmt->execute();
            $stmt->close();
            
            return $result;
            
        } catch (\Exception $e) {
            error_log("Error eliminando herramienta: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener herramientas activas
     */
    public function getActivas($userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, nombre, categoria, marca, modelo, estado, ubicacion 
                FROM herramientas 
                WHERE estado = 'activa' AND id_user = ?
                ORDER BY categoria ASC, nombre ASC
            ");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $herramientas = [];
            while ($row = $result->fetch_assoc()) {
                $herramientas[] = $row;
            }
            
            $stmt->close();
            return $herramientas;
            
        } catch (\Exception $e) {
            error_log("Error obteniendo herramientas activas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener herramientas por categoría
     */
    public function getByCategoria($categoria, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, nombre, marca, modelo, estado, ubicacion 
                FROM herramientas 
                WHERE categoria = ? AND estado = 'activa' AND id_user = ?
                ORDER BY nombre ASC
            ");
            $stmt->bind_param("si", $categoria, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $herramientas = [];
            while ($row = $result->fetch_assoc()) {
                $herramientas[] = $row;
            }
            
            $stmt->close();
            return $herramientas;
            
        } catch (\Exception $e) {
            error_log("Error obteniendo herramientas por categoría: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Buscar herramienta por número de serie
     */
    public function getByNumeroSerie($numeroSerie, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM herramientas 
                WHERE numero_serie = ? AND id_user = ?
            ");
            $stmt->bind_param("si", $numeroSerie, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $herramienta = $result->fetch_assoc();
            $stmt->close();
            
            return $herramienta;
            
        } catch (\Exception $e) {
            error_log("Error buscando herramienta por número de serie: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener herramientas por ubicación
     */
    public function getByUbicacion($ubicacion, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, nombre, categoria, marca, modelo, estado 
                FROM herramientas 
                WHERE ubicacion = ? AND id_user = ?
                ORDER BY categoria ASC, nombre ASC
            ");
            $stmt->bind_param("si", $ubicacion, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $herramientas = [];
            while ($row = $result->fetch_assoc()) {
                $herramientas[] = $row;
            }
            
            $stmt->close();
            return $herramientas;
            
        } catch (\Exception $e) {
            error_log("Error obteniendo herramientas por ubicación: " . $e->getMessage());
            return [];
        }
    }
}
