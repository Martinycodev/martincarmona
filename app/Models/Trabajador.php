<?php

namespace App\Models;

require_once BASE_PATH . '/config/database.php';

class Trabajador
{
    private $db;
    
    public function __construct()
    {
        $this->db = \Database::connect();
    }
    
    /**
     * Crear un nuevo trabajador
     */
    public function create($data, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO trabajadores (nombre, apellidos, dni, telefono, email, direccion, especialidad, fecha_contratacion, estado, foto, id_user, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), CURDATE())
            ");
            
            $foto = $data['foto'] ?? '';
            $stmt->bind_param("sssssssssi", 
                $data['nombre'],
                $data['apellidos'],
                $data['dni'],
                $data['telefono'],
                $data['email'],
                $data['direccion'],
                $data['especialidad'],
                $data['fecha_contratacion'],
                $data['estado'],
                $foto,
                $userId
            );
            
            $result = $stmt->execute();
            $insertId = $this->db->insert_id;
            $stmt->close();
            
            return $result ? $insertId : false;
            
        } catch (\Exception $e) {
            error_log("Error creando trabajador: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener todos los trabajadores del usuario
     */
    public function getAll($userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    id,
                    nombre,
                    apellidos,
                    dni,
                    telefono,
                    email,
                    direccion,
                    especialidad,
                    fecha_contratacion,
                    estado,
                    foto,
                    created_at
                FROM trabajadores 
                WHERE id_user = ?
                ORDER BY nombre ASC, apellidos ASC
            ");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $trabajadores = [];
            while ($row = $result->fetch_assoc()) {
                $trabajadores[] = $row;
            }
            
            $stmt->close();
            return $trabajadores;
            
        } catch (\Exception $e) {
            error_log("Error obteniendo trabajadores: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener un trabajador por ID
     */
    public function getById($id, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM trabajadores 
                WHERE id = ? AND id_user = ?
            ");
            $stmt->bind_param("ii", $id, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $trabajador = $result->fetch_assoc();
            $stmt->close();
            
            return $trabajador;
            
        } catch (\Exception $e) {
            error_log("Error obteniendo trabajador: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar un trabajador existente
     */
    public function update($data, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE trabajadores 
                SET nombre = ?, apellidos = ?, dni = ?, telefono = ?, email = ?, direccion = ?, especialidad = ?, fecha_contratacion = ?, estado = ?, foto = ?, updated_at = CURDATE()
                WHERE id = ? AND id_user = ?
            ");
            
            $foto = $data['foto'] ?? '';
            $stmt->bind_param("sssssssssii", 
                $data['nombre'],
                $data['apellidos'],
                $data['dni'],
                $data['telefono'],
                $data['email'],
                $data['direccion'],
                $data['especialidad'],
                $data['fecha_contratacion'],
                $data['estado'],
                $foto,
                $data['id'],
                $userId
            );
            
            $result = $stmt->execute();
            $stmt->close();
            
            return $result;
            
        } catch (\Exception $e) {
            error_log("Error actualizando trabajador: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar un trabajador
     */
    public function delete($id, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM trabajadores 
                WHERE id = ? AND id_user = ?
            ");
            
            $stmt->bind_param("ii", $id, $userId);
            $result = $stmt->execute();
            $stmt->close();
            
            return $result;
            
        } catch (\Exception $e) {
            error_log("Error eliminando trabajador: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener trabajadores activos
     */
    public function getActivos($userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, nombre, apellidos, especialidad 
                FROM trabajadores 
                WHERE estado = 'activo' AND id_user = ?
                ORDER BY nombre ASC
            ");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $trabajadores = [];
            while ($row = $result->fetch_assoc()) {
                $trabajadores[] = $row;
            }
            
            $stmt->close();
            return $trabajadores;
            
        } catch (\Exception $e) {
            error_log("Error obteniendo trabajadores activos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener información detallada del trabajador con estadísticas
     */
    public function getDetalleConEstadisticas($id, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    t.*,
                    CASE 
                        WHEN t.estado = 'activo' THEN 'Sí'
                        ELSE 'No'
                    END as dado_alta_texto,
                    t.fecha_contratacion as ultima_fecha_alta,
                    COALESCE(deuda.total_deuda, 0) as deuda_total,
                    COALESCE(stats.total_trabajos, 0) as total_trabajos,
                    COALESCE(stats.total_ganado, 0) as total_ganado,
                    COALESCE(stats.total_pagado, 0) as total_pagado
                FROM trabajadores t
                LEFT JOIN (
                    SELECT 
                        trabajador_id,
                        SUM(COALESCE(precio_tarea, 0) - COALESCE(pagado, 0)) as total_deuda
                    FROM trabajos_trabajadores 
                    WHERE trabajador_id = ?
                    GROUP BY trabajador_id
                ) deuda ON t.id = deuda.trabajador_id
                LEFT JOIN (
                    SELECT 
                        trabajador_id,
                        COUNT(*) as total_trabajos,
                        SUM(COALESCE(precio_tarea, 0)) as total_ganado,
                        SUM(COALESCE(pagado, 0)) as total_pagado
                    FROM trabajos_trabajadores 
                    WHERE trabajador_id = ?
                    GROUP BY trabajador_id
                ) stats ON t.id = stats.trabajador_id
                WHERE t.id = ? AND t.id_user = ?
            ");
            
            $stmt->bind_param("iiii", $id, $id, $id, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $trabajador = $result->fetch_assoc();
            $stmt->close();
            
            return $trabajador;
            
        } catch (\Exception $e) {
            error_log("Error obteniendo detalle del trabajador: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener historial de trabajos del trabajador
     */
    public function getHistorialTrabajos($trabajadorId, $limit = 10)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    tr.id,
                    tr.nombre as trabajo_nombre,
                    tr.fecha_inicio,
                    tr.fecha_fin,
                    tt.precio_tarea,
                    tt.pagado,
                    tt.precio_tarea as total_ganado,
                    (tt.precio_tarea - tt.pagado) as pendiente_pago
                FROM trabajos_trabajadores tt
                JOIN trabajos tr ON tt.trabajo_id = tr.id
                WHERE tt.trabajador_id = ?
                ORDER BY tr.fecha_inicio DESC
                LIMIT ?
            ");
            
            $stmt->bind_param("ii", $trabajadorId, $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $trabajos = [];
            while ($row = $result->fetch_assoc()) {
                $trabajos[] = $row;
            }
            
            $stmt->close();
            return $trabajos;
            
        } catch (\Exception $e) {
            error_log("Error obteniendo historial de trabajos: " . $e->getMessage());
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
