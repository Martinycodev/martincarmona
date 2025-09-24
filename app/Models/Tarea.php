<?php

namespace App\Models;

require_once BASE_PATH . '/config/database.php';

class Tarea
{
    private $db;
    
    public function __construct()
    {
        $this->db = \Database::connect();
    }
    
    /**
     * Crear una nueva tarea (compatible con múltiples trabajadores)
     */
    public function create($data, $userId)
    {
        try {
            $this->db->begin_transaction();
            
            // Validar datos
            if (!isset($data['horas']) || $data['horas'] === '') {
                $data['horas'] = 0;
            }
            
            // Manejar compatibilidad: trabajadores múltiples vs trabajador único
            $trabajadores = [];
            if (isset($data['trabajadores']) && is_array($data['trabajadores']) && !empty($data['trabajadores'])) {
                // Modo múltiple: array de IDs de trabajadores
                $trabajadores = $data['trabajadores'];
                $trabajadorPrincipal = $trabajadores[0]; // Primer trabajador para compatibilidad
            } elseif (isset($data['trabajador']) && $data['trabajador'] > 0) {
                // Modo único: un solo trabajador (compatibilidad)
                $trabajadorPrincipal = $data['trabajador'];
                $trabajadores = [$trabajadorPrincipal];
            } else {
                $trabajadorPrincipal = null;
            }
            
            // Crear la tarea principal (solo campos esenciales)
            $stmt = $this->db->prepare("
                INSERT INTO tareas (fecha, descripcion, horas, id_user, created_at, updated_at)
                VALUES (?, ?, ?, ?, NOW(), NOW())
            ");
            
            $stmt->bind_param("ssdi", 
                $data['fecha'],
                $data['descripcion'],
                $data['horas'],
                $userId
            );
            
            $result = $stmt->execute();
            $tareaId = $this->db->insert_id;
            $stmt->close();
            
            if (!$result) {
                throw new \Exception("Error insertando tarea principal");
            }
            
            // Insertar trabajadores en la tabla de relaciones N:N
            if (!empty($trabajadores)) {
                $this->insertarTrabajadores($tareaId, $trabajadores, $data['horas']);
            }
            
            // Insertar parcelas en la tabla de relaciones N:N (si existen)
            if (isset($data['parcelas']) && is_array($data['parcelas']) && !empty($data['parcelas'])) {
                // Modo múltiple: array de IDs de parcelas
                $this->insertarParcelas($tareaId, $data['parcelas']);
            } elseif (isset($data['parcela']) && $data['parcela'] > 0) {
                // Modo único: una sola parcela (compatibilidad)
                $this->insertarParcelas($tareaId, [$data['parcela']]);
            }
            
            // Insertar trabajo en la tabla de relaciones N:N (si existe)
            if (isset($data['trabajo']) && $data['trabajo'] > 0) {
                $this->insertarTrabajos($tareaId, [$data['trabajo']], $data['horas']);
            }
            
            // ===== NUEVA LÓGICA: ACTUALIZAR MOVIMIENTOS MENSUALES =====
            // Solo procesar si hay trabajadores y trabajo asignado
            if (!empty($trabajadores) && isset($data['trabajo']) && $data['trabajo'] > 0) {
                $this->actualizarMovimientosMensuales($trabajadores, $data['trabajo'], $data['horas'], $data['fecha']);
            }
            
            $this->db->commit();
            return $tareaId;
            
        } catch (\Exception $e) {
            $this->db->rollback();
            error_log("Error creando tarea: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Insertar trabajadores en la tabla de relaciones
     */
    private function insertarTrabajadores($tareaId, $trabajadores, $horasDefault = 0)
    {
        $stmt = $this->db->prepare("
            INSERT INTO tarea_trabajadores (tarea_id, trabajador_id, horas_asignadas)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE horas_asignadas = VALUES(horas_asignadas)
        ");
        
        foreach ($trabajadores as $trabajadorId) {
            if ($trabajadorId > 0) {
                $stmt->bind_param("iid", $tareaId, $trabajadorId, $horasDefault);
                $stmt->execute();
            }
        }
        
        $stmt->close();
    }
    
    /**
     * Insertar parcelas en la tabla de relaciones
     */
    private function insertarParcelas($tareaId, $parcelas)
    {
        $stmt = $this->db->prepare("
            INSERT INTO tarea_parcelas (tarea_id, parcela_id)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE parcela_id = VALUES(parcela_id)
        ");
        
        foreach ($parcelas as $parcelaId) {
            if ($parcelaId > 0) {
                $stmt->bind_param("ii", $tareaId, $parcelaId);
                $stmt->execute();
            }
        }
        
        $stmt->close();
    }
    
    /**
     * Insertar trabajos en la tabla de relaciones
     */
    private function insertarTrabajos($tareaId, $trabajos, $horasDefault = 0)
    {
        $stmt = $this->db->prepare("
            INSERT INTO tarea_trabajos (tarea_id, trabajo_id, horas_trabajo)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE horas_trabajo = VALUES(horas_trabajo)
        ");
        
        foreach ($trabajos as $trabajoId) {
            if ($trabajoId > 0) {
                $stmt->bind_param("iid", $tareaId, $trabajoId, $horasDefault);
                $stmt->execute();
            }
        }
        
        $stmt->close();
    }
    
    /**
     * Obtener estadísticas básicas del usuario
     */
    public function getStats($userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total,
                    SUM(horas) as total_horas
                FROM tareas 
                WHERE id_user = ?
            ");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $stats = $result->fetch_assoc();
            $stmt->close();
            
            return [
                'total_tareas' => $stats['total'] ?? 0,
                'total_horas' => $stats['total_horas'] ?? 0
            ];
            
        } catch (\Exception $e) {
            error_log("Error obteniendo estadísticas: " . $e->getMessage());
            return [
                'total_tareas' => 0,
                'total_horas' => 0
            ];
        }
    }
    
    /**
     * Obtener todas las tareas del usuario con trabajadores, parcelas y trabajos relacionados
     * OPTIMIZADO: Evita N+1 queries usando consultas agrupadas
     */
    /**
     * Obtener tareas con paginación
     */
    public function getAllPaginated($userId, $page = 1, $limit = 20)
    {
        try {
            // Calcular offset
            $offset = ($page - 1) * $limit;
            
            // Obtener tareas del usuario con paginación
            $stmt = $this->db->prepare("
                SELECT 
                    id,
                    fecha,
                    descripcion,
                    horas,
                    created_at,
                    updated_at
                FROM tareas 
                WHERE id_user = ?
                ORDER BY fecha DESC, created_at DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->bind_param("iii", $userId, $limit, $offset);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $tareas = [];
            $tareaIds = [];
            
            while ($row = $result->fetch_assoc()) {
                $tareas[$row['id']] = $row;
                $tareas[$row['id']]['trabajadores'] = [];
                $tareas[$row['id']]['parcelas'] = [];
                $tareas[$row['id']]['trabajos'] = [];
                $tareaIds[] = $row['id'];
            }
            $stmt->close();
            
            // Si hay tareas, cargar datos relacionados
            if (!empty($tareaIds)) {
                $this->cargarDatosRelacionadosPaginado($tareas, $tareaIds);
            }
            
            return array_values($tareas);
            
        } catch (\Exception $e) {
            error_log("Error obteniendo tareas paginadas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener total de tareas para paginación
     */
    public function getTotalCount($userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total
                FROM tareas 
                WHERE id_user = ?
            ");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            
            return (int)$row['total'];
            
        } catch (\Exception $e) {
            error_log("Error obteniendo total de tareas: " . $e->getMessage());
            return 0;
        }
    }

    public function getAll($userId)
    {
        try {
            // Obtener todas las tareas del usuario
            $stmt = $this->db->prepare("
                SELECT 
                    id,
                    fecha,
                    descripcion,
                    horas,
                    created_at
                FROM tareas 
                WHERE id_user = ?
                ORDER BY fecha DESC, created_at DESC
            ");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $tareas = [];
            $tareaIds = [];
            
            while ($row = $result->fetch_assoc()) {
                $tareas[$row['id']] = $row;
                $tareas[$row['id']]['trabajadores'] = [];
                $tareas[$row['id']]['parcelas'] = [];
                $tareas[$row['id']]['trabajos'] = [];
                $tareaIds[] = $row['id'];
            }
            $stmt->close();
            
            if (empty($tareaIds)) {
                return [];
            }
            
            $placeholders = str_repeat('?,', count($tareaIds) - 1) . '?';
            
            // Obtener todos los trabajadores en una sola consulta
            $stmt = $this->db->prepare("
                SELECT 
                    tt.tarea_id,
                    t.id,
                    t.nombre,
                    tt.horas_asignadas
                FROM tarea_trabajadores tt
                JOIN trabajadores t ON tt.trabajador_id = t.id
                WHERE tt.tarea_id IN ($placeholders)
                ORDER BY tt.tarea_id, t.nombre
            ");
            $stmt->bind_param(str_repeat('i', count($tareaIds)), ...$tareaIds);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $tareas[$row['tarea_id']]['trabajadores'][] = [
                    'id' => $row['id'],
                    'nombre' => $row['nombre'],
                    'horas_asignadas' => $row['horas_asignadas']
                ];
            }
            $stmt->close();
            
            // Obtener todas las parcelas en una sola consulta
            $stmt = $this->db->prepare("
                SELECT 
                    tp.tarea_id,
                    p.id,
                    p.nombre
                FROM tarea_parcelas tp
                JOIN parcelas p ON tp.parcela_id = p.id
                WHERE tp.tarea_id IN ($placeholders)
                ORDER BY tp.tarea_id, p.nombre
            ");
            $stmt->bind_param(str_repeat('i', count($tareaIds)), ...$tareaIds);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $tareas[$row['tarea_id']]['parcelas'][] = [
                    'id' => $row['id'],
                    'nombre' => $row['nombre']
                ];
            }
            $stmt->close();
            
            // Obtener todos los trabajos en una sola consulta
            $stmt = $this->db->prepare("
                SELECT 
                    ttj.tarea_id,
                    tj.id,
                    tj.nombre
                FROM tarea_trabajos ttj
                JOIN trabajos tj ON ttj.trabajo_id = tj.id
                WHERE ttj.tarea_id IN ($placeholders)
                ORDER BY ttj.tarea_id, tj.nombre
            ");
            $stmt->bind_param(str_repeat('i', count($tareaIds)), ...$tareaIds);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $tareas[$row['tarea_id']]['trabajos'][] = [
                    'id' => $row['id'],
                    'nombre' => $row['nombre']
                ];
            }
            $stmt->close();
            
            // Procesar datos para compatibilidad con la interfaz
            foreach ($tareas as &$tarea) {
                // Trabajadores
                if (!empty($tarea['trabajadores'])) {
                    $trabajadoresNombres = array_map(function($t) {
                        return $t['nombre'];
                    }, $tarea['trabajadores']);
                    $tarea['trabajador_nombre'] = implode(', ', $trabajadoresNombres);
                    $tarea['trabajador'] = $tarea['trabajadores'][0]['id'];
                } else {
                    $tarea['trabajador_nombre'] = '';
                    $tarea['trabajador'] = null;
                }
                
                // Parcelas
                if (!empty($tarea['parcelas'])) {
                    $tarea['parcela_nombre'] = $tarea['parcelas'][0]['nombre'];
                    $tarea['parcela'] = $tarea['parcelas'][0]['id'];
                } else {
                    $tarea['parcela_nombre'] = '';
                    $tarea['parcela'] = null;
                }
                
                // Trabajos
                if (!empty($tarea['trabajos'])) {
                    $tarea['trabajo_nombre'] = $tarea['trabajos'][0]['nombre'];
                    $tarea['trabajo'] = $tarea['trabajos'][0]['id'];
                } else {
                    $tarea['trabajo_nombre'] = '';
                    $tarea['trabajo'] = null;
                }
            }
            
            // Convertir array asociativo a indexado manteniendo el orden
            return array_values($tareas);
            
        } catch (\Exception $e) {
            error_log("Error obteniendo tareas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener tareas de un mes específico (OPTIMIZADO para calendario)
     * @param int $userId ID del usuario
     * @param string $year Año (YYYY)
     * @param string $month Mes (MM)
     * @return array Tareas del mes especificado
     */
    public function getTareasByMonth($userId, $year, $month)
    {
        try {
            // Calcular primer y último día del mes
            $firstDay = sprintf('%04d-%02d-01', $year, $month);
            $lastDay = date('Y-m-t', strtotime($firstDay)); // último día del mes
            
            // Obtener tareas del mes específico
            $stmt = $this->db->prepare("
                SELECT 
                    id,
                    fecha,
                    descripcion,
                    horas,
                    created_at
                FROM tareas 
                WHERE id_user = ? 
                AND fecha BETWEEN ? AND ?
                ORDER BY fecha DESC, created_at DESC
            ");
            $stmt->bind_param("iss", $userId, $firstDay, $lastDay);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $tareas = [];
            $tareaIds = [];
            
            while ($row = $result->fetch_assoc()) {
                $tareas[$row['id']] = $row;
                $tareas[$row['id']]['trabajadores'] = [];
                $tareas[$row['id']]['parcelas'] = [];
                $tareas[$row['id']]['trabajos'] = [];
                $tareaIds[] = $row['id'];
            }
            $stmt->close();
            
            if (empty($tareaIds)) {
                return [];
            }
            
            $placeholders = str_repeat('?,', count($tareaIds) - 1) . '?';
            
            // Obtener todos los trabajadores en una sola consulta
            $stmt = $this->db->prepare("
                SELECT 
                    tt.tarea_id,
                    t.id,
                    t.nombre,
                    tt.horas_asignadas
                FROM tarea_trabajadores tt
                JOIN trabajadores t ON tt.trabajador_id = t.id
                WHERE tt.tarea_id IN ($placeholders)
                ORDER BY tt.tarea_id, t.nombre
            ");
            $stmt->bind_param(str_repeat('i', count($tareaIds)), ...$tareaIds);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $tareas[$row['tarea_id']]['trabajadores'][] = [
                    'id' => $row['id'],
                    'nombre' => $row['nombre'],
                    'horas_asignadas' => $row['horas_asignadas']
                ];
            }
            $stmt->close();
            
            // Obtener todas las parcelas en una sola consulta
            $stmt = $this->db->prepare("
                SELECT 
                    tp.tarea_id,
                    p.id,
                    p.nombre
                FROM tarea_parcelas tp
                JOIN parcelas p ON tp.parcela_id = p.id
                WHERE tp.tarea_id IN ($placeholders)
                ORDER BY tp.tarea_id, p.nombre
            ");
            $stmt->bind_param(str_repeat('i', count($tareaIds)), ...$tareaIds);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $tareas[$row['tarea_id']]['parcelas'][] = [
                    'id' => $row['id'],
                    'nombre' => $row['nombre']
                ];
            }
            $stmt->close();
            
            // Obtener todos los trabajos en una sola consulta
            $stmt = $this->db->prepare("
                SELECT 
                    ttj.tarea_id,
                    tj.id,
                    tj.nombre
                FROM tarea_trabajos ttj
                JOIN trabajos tj ON ttj.trabajo_id = tj.id
                WHERE ttj.tarea_id IN ($placeholders)
                ORDER BY ttj.tarea_id, tj.nombre
            ");
            $stmt->bind_param(str_repeat('i', count($tareaIds)), ...$tareaIds);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $tareas[$row['tarea_id']]['trabajos'][] = [
                    'id' => $row['id'],
                    'nombre' => $row['nombre']
                ];
            }
            $stmt->close();
            
            // Procesar datos para compatibilidad con la interfaz
            foreach ($tareas as &$tarea) {
                // Trabajadores
                if (!empty($tarea['trabajadores'])) {
                    $trabajadoresNombres = array_map(function($t) {
                        return $t['nombre'];
                    }, $tarea['trabajadores']);
                    $tarea['trabajador_nombre'] = implode(', ', $trabajadoresNombres);
                    $tarea['trabajador'] = $tarea['trabajadores'][0]['id'];
                } else {
                    $tarea['trabajador_nombre'] = '';
                    $tarea['trabajador'] = null;
                }
                
                // Parcelas
                if (!empty($tarea['parcelas'])) {
                    $tarea['parcela_nombre'] = $tarea['parcelas'][0]['nombre'];
                    $tarea['parcela'] = $tarea['parcelas'][0]['id'];
                } else {
                    $tarea['parcela_nombre'] = '';
                    $tarea['parcela'] = null;
                }
                
                // Trabajos
                if (!empty($tarea['trabajos'])) {
                    $tarea['trabajo_nombre'] = $tarea['trabajos'][0]['nombre'];
                    $tarea['trabajo'] = $tarea['trabajos'][0]['id'];
                } else {
                    $tarea['trabajo_nombre'] = '';
                    $tarea['trabajo'] = null;
                }
            }
            
            // Convertir array asociativo a indexado manteniendo el orden
            return array_values($tareas);
            
        } catch (\Exception $e) {
            error_log("Error obteniendo tareas del mes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener trabajadores asignados a una tarea específica
     */
    private function getTrabajadoresByTarea($tareaId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    tr.id,
                    tr.nombre,
                    tt.horas_asignadas
                FROM tarea_trabajadores tt
                JOIN trabajadores tr ON tt.trabajador_id = tr.id
                WHERE tt.tarea_id = ?
                ORDER BY tr.nombre
            ");
            $stmt->bind_param("i", $tareaId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $trabajadores = [];
            while ($row = $result->fetch_assoc()) {
                $trabajadores[] = $row;
            }
            
            $stmt->close();
            return $trabajadores;
            
        } catch (\Exception $e) {
            error_log("Error obteniendo trabajadores de tarea: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener parcelas asignadas a una tarea específica
     */
    private function getParcelasByTarea($tareaId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    p.id,
                    p.nombre,
                    p.ubicacion,
                    tp.superficie_trabajada
                FROM tarea_parcelas tp
                JOIN parcelas p ON tp.parcela_id = p.id
                WHERE tp.tarea_id = ?
                ORDER BY p.nombre
            ");
            $stmt->bind_param("i", $tareaId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $parcelas = [];
            while ($row = $result->fetch_assoc()) {
                $parcelas[] = $row;
            }
            
            $stmt->close();
            return $parcelas;
            
        } catch (\Exception $e) {
            error_log("Error obteniendo parcelas de tarea: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener trabajos asignados a una tarea específica
     */
    private function getTrabajosByTarea($tareaId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    tj.id,
                    tj.nombre,
                    ttj.horas_trabajo,
                    ttj.precio_hora
                FROM tarea_trabajos ttj
                JOIN trabajos tj ON ttj.trabajo_id = tj.id
                WHERE ttj.tarea_id = ?
                ORDER BY tj.nombre
            ");
            $stmt->bind_param("i", $tareaId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $trabajos = [];
            while ($row = $result->fetch_assoc()) {
                $trabajos[] = $row;
            }
            
            $stmt->close();
            return $trabajos;
            
        } catch (\Exception $e) {
            error_log("Error obteniendo trabajos de tarea: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Actualizar una tarea existente (compatible con múltiples trabajadores)
     */
    public function update($data, $userId)
    {
        try {
            $this->db->begin_transaction();
            
            // Validar datos
            if (!isset($data['horas']) || $data['horas'] === '') {
                $data['horas'] = 0;
            }
            
            // Manejar compatibilidad: trabajadores múltiples vs trabajador único
            $trabajadores = [];
            if (isset($data['trabajadores']) && is_array($data['trabajadores']) && !empty($data['trabajadores'])) {
                // Modo múltiple: array de IDs de trabajadores
                $trabajadores = $data['trabajadores'];
                $trabajadorPrincipal = $trabajadores[0]; // Primer trabajador para compatibilidad
            } elseif (isset($data['trabajador']) && $data['trabajador'] > 0) {
                // Modo único: un solo trabajador (compatibilidad)
                $trabajadorPrincipal = $data['trabajador'];
                $trabajadores = [$trabajadorPrincipal];
            } else {
                $trabajadorPrincipal = null;
            }
            
            // Actualizar la tarea principal (solo campos esenciales)
            $stmt = $this->db->prepare("
                UPDATE tareas 
                SET fecha = ?, descripcion = ?, horas = ?, updated_at = CURDATE()
                WHERE id = ? AND id_user = ?
            ");
            
            $stmt->bind_param("ssdii", 
                $data['fecha'],
                $data['descripcion'],
                $data['horas'],
                $data['id'],
                $userId
            );
            
            $result = $stmt->execute();
            $stmt->close();
            
            if (!$result) {
                throw new \Exception("Error actualizando tarea principal");
            }
            
            // Actualizar trabajadores: eliminar los existentes y agregar los nuevos
            $this->eliminarRelacionesTarea($data['id'], 'trabajadores');
            if (!empty($trabajadores)) {
                $this->insertarTrabajadores($data['id'], $trabajadores, $data['horas']);
            }
            
            // Actualizar parcelas en tabla de relaciones
            $this->eliminarRelacionesTarea($data['id'], 'parcelas');
            if (isset($data['parcelas']) && is_array($data['parcelas']) && !empty($data['parcelas'])) {
                // Modo múltiple: array de IDs de parcelas
                $this->insertarParcelas($data['id'], $data['parcelas']);
            } elseif (isset($data['parcela']) && $data['parcela'] > 0) {
                // Modo único: una sola parcela (compatibilidad)
                $this->insertarParcelas($data['id'], [$data['parcela']]);
            }
            
            // Actualizar trabajo en tabla de relaciones
            $this->eliminarRelacionesTarea($data['id'], 'trabajos');
            if (isset($data['trabajo']) && $data['trabajo'] > 0) {
                $this->insertarTrabajos($data['id'], [$data['trabajo']], $data['horas']);
            }
            
            $this->db->commit();
            return true;
            
        } catch (\Exception $e) {
            $this->db->rollback();
            error_log("Error actualizando tarea: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar relaciones existentes de una tarea
     */
    private function eliminarRelacionesTarea($tareaId, $tipo)
    {
        $tabla = '';
        switch ($tipo) {
            case 'trabajadores':
                $tabla = 'tarea_trabajadores';
                break;
            case 'parcelas':
                $tabla = 'tarea_parcelas';
                break;
            case 'trabajos':
                $tabla = 'tarea_trabajos';
                break;
            default:
                return false;
        }
        
        $stmt = $this->db->prepare("DELETE FROM {$tabla} WHERE tarea_id = ?");
        $stmt->bind_param("i", $tareaId);
        $stmt->execute();
        $stmt->close();
        
        return true;
    }
    
    /**
     * Eliminar una tarea y todas sus relaciones
     */
    public function delete($taskId, $userId)
    {
        try {
            $this->db->begin_transaction();
            
            // Verificar que la tarea pertenezca al usuario
            $stmt = $this->db->prepare("SELECT id FROM tareas WHERE id = ? AND id_user = ?");
            $stmt->bind_param("ii", $taskId, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
            
            if ($result->num_rows === 0) {
                throw new \Exception("Tarea no encontrada o sin permisos");
            }
            
            // Eliminar relaciones (se eliminan automáticamente por CASCADE en las FK)
            // Pero las incluimos explícitamente por si no están configuradas
            $this->eliminarRelacionesTarea($taskId, 'trabajadores');
            $this->eliminarRelacionesTarea($taskId, 'parcelas');
            $this->eliminarRelacionesTarea($taskId, 'trabajos');
            
            // Eliminar la tarea principal
            $stmt = $this->db->prepare("DELETE FROM tareas WHERE id = ? AND id_user = ?");
            $stmt->bind_param("ii", $taskId, $userId);
            $result = $stmt->execute();
            $stmt->close();
            
            if (!$result) {
                throw new \Exception("Error eliminando tarea principal");
            }
            
            $this->db->commit();
            return true;
            
        } catch (\Exception $e) {
            $this->db->rollback();
            error_log("Error eliminando tarea: " . $e->getMessage());
            return false;
        }
    }
    
    public function __destruct()
    {
        if ($this->db) {
            $this->db->close();
        }
    }
    
    /**
     * Cargar datos relacionados para tareas paginadas
     */
    private function cargarDatosRelacionadosPaginado(&$tareas, $tareaIds)
    {
        if (empty($tareaIds)) {
            return;
        }
        
        $placeholders = str_repeat('?,', count($tareaIds) - 1) . '?';
        
        // Obtener todos los trabajadores en una sola consulta
        $stmt = $this->db->prepare("
            SELECT 
                tt.tarea_id,
                t.id,
                t.nombre,
                tt.horas_asignadas
            FROM tarea_trabajadores tt
            JOIN trabajadores t ON tt.trabajador_id = t.id
            WHERE tt.tarea_id IN ($placeholders)
            ORDER BY tt.tarea_id, t.nombre
        ");
        $stmt->bind_param(str_repeat('i', count($tareaIds)), ...$tareaIds);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $tareas[$row['tarea_id']]['trabajadores'][] = [
                'id' => $row['id'],
                'nombre' => $row['nombre'],
                'horas_asignadas' => $row['horas_asignadas']
            ];
        }
        $stmt->close();
        
        // Obtener todas las parcelas en una sola consulta
        $stmt = $this->db->prepare("
            SELECT 
                tp.tarea_id,
                p.id,
                p.nombre,
                p.ubicacion
            FROM tarea_parcelas tp
            JOIN parcelas p ON tp.parcela_id = p.id
            WHERE tp.tarea_id IN ($placeholders)
            ORDER BY tp.tarea_id, p.nombre
        ");
        $stmt->bind_param(str_repeat('i', count($tareaIds)), ...$tareaIds);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $tareas[$row['tarea_id']]['parcelas'][] = [
                'id' => $row['id'],
                'nombre' => $row['nombre'],
                'ubicacion' => $row['ubicacion']
            ];
        }
        $stmt->close();
        
        // Obtener todos los trabajos en una sola consulta
        $stmt = $this->db->prepare("
            SELECT 
                ttj.tarea_id,
                tj.id,
                tj.nombre,
                ttj.horas_trabajo
            FROM tarea_trabajos ttj
            JOIN trabajos tj ON ttj.trabajo_id = tj.id
            WHERE ttj.tarea_id IN ($placeholders)
            ORDER BY ttj.tarea_id, tj.nombre
        ");
        $stmt->bind_param(str_repeat('i', count($tareaIds)), ...$tareaIds);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $tareas[$row['tarea_id']]['trabajos'][] = [
                'id' => $row['id'],
                'nombre' => $row['nombre'],
                'horas_trabajo' => $row['horas_trabajo']
            ];
        }
        $stmt->close();
    }
    
    /**
     * Obtener el precio por hora de un trabajo
     * @param int $trabajoId ID del trabajo
     * @return float Precio por hora del trabajo
     */
    private function getPrecioHoraTrabajo($trabajoId) {
        try {
            // Obtener precio_hora del trabajo desde la tabla trabajos
            $stmt = $this->db->prepare("
                SELECT precio_hora 
                FROM trabajos 
                WHERE id = ?
            ");
            $stmt->bind_param("i", $trabajoId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            
            if ($row && $row['precio_hora'] > 0) {
                return (float) $row['precio_hora'];
            }
            
            // Si no hay precio_hora en la tabla trabajos, usar precio por defecto
            return 15.0; // Precio por defecto de 15€/hora
            
        } catch (\Exception $e) {
            error_log("Error obteniendo precio del trabajo: " . $e->getMessage());
            return 15.0; // Precio por defecto en caso de error
        }
    }
    
    /**
     * Calcular el total de una tarea
     * @param int $trabajoId ID del trabajo
     * @param float $horas Horas trabajadas
     * @return float Total calculado (precio * horas)
     */
    private function calcularTotalTarea($trabajoId, $horas) {
        $precioHora = $this->getPrecioHoraTrabajo($trabajoId);
        return $precioHora * $horas;
    }
    
    /**
     * Actualizar movimientos mensuales para todos los trabajadores de una tarea
     * @param array $trabajadores Array de IDs de trabajadores
     * @param int $trabajoId ID del trabajo
     * @param float $horas Horas trabajadas
     * @param string $fecha Fecha de la tarea
     * @return bool True si se actualizaron correctamente todos los movimientos
     */
    private function actualizarMovimientosMensuales($trabajadores, $trabajoId, $horas, $fecha) {
        try {
            // Calcular el total de la tarea (mismo para todos los trabajadores)
            $totalTarea = $this->calcularTotalTarea($trabajoId, $horas);
            
            $todosExitosos = true;
            
            foreach ($trabajadores as $trabajadorId) {
                // Actualizar el movimiento mensual del trabajador
                $resultado = $this->actualizarMovimientoMensualTrabajador($trabajadorId, $fecha, $totalTarea);
                
                if (!$resultado) {
                    $todosExitosos = false;
                    error_log("Error actualizando movimientos para trabajador ID: $trabajadorId");
                } else {
                    error_log("Movimiento actualizado para trabajador ID: $trabajadorId, total: $totalTarea");
                }
            }
            
            return $todosExitosos;
            
        } catch (\Exception $e) {
            error_log("Error en actualizarMovimientosMensuales: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar o crear movimiento mensual para un trabajador específico
     * @param int $trabajadorId ID del trabajador
     * @param string $fecha Fecha de la tarea
     * @param float $importe Importe a sumar
     * @return bool True si se actualizó correctamente
     */
    private function actualizarMovimientoMensualTrabajador($trabajadorId, $fecha, $importe) {
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
                    AND categoria = 'gasto'";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("iii", $trabajadorId, $year, $month);
            $stmt->execute();
            $result = $stmt->get_result();
            $movimiento = $result->fetch_assoc();
            $stmt->close();
            
            if ($movimiento) {
                // Si existe, sumar el importe al existente
                $nuevoImporte = $movimiento['importe'] + $importe;
                
                $sql = "UPDATE movimientos 
                        SET importe = ? 
                        WHERE id = ?";
                
                $stmt = $this->db->prepare($sql);
                $stmt->bind_param("di", $nuevoImporte, $movimiento['id']);
                $resultado = $stmt->execute();
                $stmt->close();
                
                return $resultado;
            } else {
                // Si no existe, crear uno nuevo
                $fechaInicioMes = sprintf('%04d-%02d-01', $year, $month);
                $concepto = "Sueldo - " . date('F Y', strtotime($fechaInicioMes));
                
                $sql = "INSERT INTO movimientos (fecha, tipo, concepto, categoria, importe, trabajador_id, estado) 
                        VALUES (?, 'gasto', ?, 'gasto', ?, ?, 'pendiente')";
                
                $stmt = $this->db->prepare($sql);
                $stmt->bind_param("ssdi", $fechaInicioMes, $concepto, $importe, $trabajadorId);
                $resultado = $stmt->execute();
                $stmt->close();
                
                return $resultado;
            }
            
        } catch (\Exception $e) {
            error_log("Error actualizando movimiento mensual del trabajador: " . $e->getMessage());
            return false;
        }
    }
}
