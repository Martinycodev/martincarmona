<?php

namespace App\Models;

require_once BASE_PATH . '/config/database.php';

class Movimiento {
    private $db;
    
    public function __construct() {
        $this->db = \Database::connect();
    }
    
    public function getAll($limit = null, $offset = 0) {
        $sql = "SELECT m.*, 
                       p.nombre as proveedor_nombre,
                       t.nombre as trabajador_nombre,
                       v.nombre as vehiculo_nombre,
                       v.matricula as vehiculo_matricula,
                       par.nombre as parcela_nombre
                FROM movimientos m
                LEFT JOIN proveedores p ON m.proveedor_id = p.id
                LEFT JOIN trabajadores t ON m.trabajador_id = t.id
                LEFT JOIN vehiculos v ON m.vehiculo_id = v.id
                LEFT JOIN parcelas par ON m.parcela_id = par.id
                ORDER BY m.fecha DESC, m.id DESC";
        
        if ($limit) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }
        
        $result = $this->db->query($sql);
        $movimientos = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $movimientos[] = $row;
            }
        }
        
        return $movimientos;
    }
    
    public function getById($id) {
        $sql = "SELECT m.*, 
                       p.nombre as proveedor_nombre,
                       t.nombre as trabajador_nombre,
                       v.nombre as vehiculo_nombre,
                       v.matricula as vehiculo_matricula,
                       par.nombre as parcela_nombre
                FROM movimientos m
                LEFT JOIN proveedores p ON m.proveedor_id = p.id
                LEFT JOIN trabajadores t ON m.trabajador_id = t.id
                LEFT JOIN vehiculos v ON m.vehiculo_id = v.id
                LEFT JOIN parcelas par ON m.parcela_id = par.id
                WHERE m.id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        
        return $result->fetch_assoc();
    }
    
    public function create($data) {
        $sql = "INSERT INTO movimientos (fecha, tipo, concepto, categoria, importe, proveedor_id, trabajador_id, vehiculo_id, parcela_id, estado) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssssdiiiis", 
            $data['fecha'],
            $data['tipo'],
            $data['concepto'],
            $data['categoria'],
            $data['importe'],
            $data['proveedor_id'],
            $data['trabajador_id'],
            $data['vehiculo_id'],
            $data['parcela_id'],
            $data['estado']
        );
        
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
    
    public function update($id, $data) {
        $sql = "UPDATE movimientos 
                SET fecha = ?, tipo = ?, concepto = ?, categoria = ?, 
                    importe = ?, proveedor_id = ?, trabajador_id = ?, 
                    vehiculo_id = ?, parcela_id = ?, estado = ?
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssssdiiiisi", 
            $data['fecha'],
            $data['tipo'],
            $data['concepto'],
            $data['categoria'],
            $data['importe'],
            $data['proveedor_id'],
            $data['trabajador_id'],
            $data['vehiculo_id'],
            $data['parcela_id'],
            $data['estado'],
            $id
        );
        
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
    
    public function delete($id) {
        $sql = "DELETE FROM movimientos WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    
    public function getResumenFinanciero() {
        $sql = "SELECT 
                    SUM(CASE WHEN tipo = 'ingreso' THEN importe ELSE 0 END) as total_ingresos,
                    SUM(CASE WHEN tipo = 'gasto' THEN importe ELSE 0 END) as total_gastos,
                    SUM(CASE WHEN tipo = 'ingreso' THEN importe ELSE -importe END) as saldo_total,
                    COUNT(CASE WHEN tipo = 'ingreso' THEN 1 END) as num_ingresos,
                    COUNT(CASE WHEN tipo = 'gasto' THEN 1 END) as num_gastos,
                    COUNT(CASE WHEN estado = 'pendiente' THEN 1 END) as movimientos_pendientes
                FROM movimientos";
        
        $result = $this->db->query($sql);
        return $result->fetch_assoc();
    }
    
    public function getMovimientosPorCategoria() {
        $sql = "SELECT categoria, 
                       SUM(CASE WHEN tipo = 'ingreso' THEN importe ELSE 0 END) as ingresos,
                       SUM(CASE WHEN tipo = 'gasto' THEN importe ELSE 0 END) as gastos,
                       COUNT(*) as total_movimientos
                FROM movimientos 
                GROUP BY categoria 
                ORDER BY total_movimientos DESC";
        
        $result = $this->db->query($sql);
        $categorias = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $categorias[] = $row;
            }
        }
        
        return $categorias;
    }
    
    public function getMovimientosRecientes($limit = 10) {
        return $this->getAll($limit);
    }
    
    public function buscar($query) {
        $sql = "SELECT m.*, 
                       p.nombre as proveedor_nombre,
                       t.nombre as trabajador_nombre,
                       v.nombre as vehiculo_nombre,
                       v.matricula as vehiculo_matricula,
                       par.nombre as parcela_nombre
                FROM movimientos m
                LEFT JOIN proveedores p ON m.proveedor_id = p.id
                LEFT JOIN trabajadores t ON m.trabajador_id = t.id
                LEFT JOIN vehiculos v ON m.vehiculo_id = v.id
                LEFT JOIN parcelas par ON m.parcela_id = par.id
                WHERE m.concepto LIKE ? 
                   OR p.nombre LIKE ? 
                   OR t.nombre LIKE ?
                   OR par.nombre LIKE ?
                ORDER BY m.fecha DESC, m.id DESC";
        
        $stmt = $this->db->prepare($sql);
        $searchTerm = "%$query%";
        $stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        $movimientos = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $movimientos[] = $row;
            }
        }
        
        $stmt->close();
        return $movimientos;
    }
    
    /**
     * Buscar o crear movimiento mensual de un trabajador
     * @param int $trabajadorId ID del trabajador
     * @param string $fecha Fecha en formato Y-m-d
     * @return array|false Datos del movimiento o false si hay error
     */
    public function getOrCreateMovimientoMensualTrabajador($trabajadorId, $fecha) {
        try {
            // Obtener año y mes de la fecha
            $year = date('Y', strtotime($fecha));
            $month = date('m', strtotime($fecha));
            
            // Buscar movimiento existente para el trabajador en ese mes
            $sql = "SELECT * FROM movimientos 
                    WHERE trabajador_id = ? 
                    AND YEAR(fecha) = ? 
                    AND MONTH(fecha) = ?
                    AND tipo = 'gasto'
                    AND categoria = 'personal'";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("iii", $trabajadorId, $year, $month);
            $stmt->execute();
            $result = $stmt->get_result();
            $movimiento = $result->fetch_assoc();
            $stmt->close();
            
            if ($movimiento) {
                return $movimiento;
            }
            
            // Si no existe, crear uno nuevo
            $fechaInicioMes = sprintf('%04d-%02d-01', $year, $month);
            $concepto = "Ganancias mensuales - " . date('F Y', strtotime($fechaInicioMes));
            
            $sql = "INSERT INTO movimientos (fecha, tipo, concepto, categoria, importe, trabajador_id, estado) 
                    VALUES (?, 'gasto', ?, 'personal', 0.00, ?, 'pagado')";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("ssi", $fechaInicioMes, $concepto, $trabajadorId);
            $result = $stmt->execute();
            $movimientoId = $this->db->insert_id;
            $stmt->close();
            
            if (!$result) {
                return false;
            }
            
            // Obtener el movimiento recién creado
            return $this->getById($movimientoId);
            
        } catch (\Exception $e) {
            error_log("Error en getOrCreateMovimientoMensualTrabajador: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar ganancias de un trabajador en su movimiento mensual
     * @param int $trabajadorId ID del trabajador
     * @param string $fecha Fecha de la tarea
     * @param float $gananciaAdicional Ganancia a sumar
     * @return bool True si se actualizó correctamente
     */
    public function actualizarGananciasTrabajador($trabajadorId, $fecha, $gananciaAdicional) {
        try {
            $this->db->begin_transaction();
            
            // Obtener o crear el movimiento mensual
            $movimiento = $this->getOrCreateMovimientoMensualTrabajador($trabajadorId, $fecha);
            
            if (!$movimiento) {
                throw new \Exception("No se pudo obtener o crear el movimiento mensual");
            }
            
            // Actualizar el importe sumando la ganancia adicional
            $nuevoImporte = $movimiento['importe'] + $gananciaAdicional;
            
            $sql = "UPDATE movimientos 
                    SET importe = ? 
                    WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("di", $nuevoImporte, $movimiento['id']);
            $result = $stmt->execute();
            $stmt->close();
            
            if (!$result) {
                throw new \Exception("Error actualizando el importe del movimiento");
            }
            
            $this->db->commit();
            return true;
            
        } catch (\Exception $e) {
            $this->db->rollback();
            error_log("Error actualizando ganancias del trabajador: " . $e->getMessage());
            return false;
        }
    }
}
