<?php

namespace App\Controllers;

require_once BASE_PATH . '/app/Models/Tarea.php';
require_once BASE_PATH . '/config/database.php';

class BusquedaController extends BaseController
{
    private $db;
    
    public function __construct()
    {
        // Verificar autenticación
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/');
            return;
        }
        
        $this->db = \Database::connect();
    }
    
    public function index()
    {
        $userId = $_SESSION['user_id'];
        
        // Incluir la información del usuario desde la sesión
        $user = [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'] ?? $_SESSION['username'] ?? 'Usuario'
        ];
        
        $data = [
            'titulo' => 'Búsqueda Avanzada de Tareas',
            'user' => $user
        ];
        
        $this->render('busqueda/index', $data);
    }
    
    /**
     * Búsqueda avanzada con múltiples filtros
     */
    public function buscar()
    {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        
        try {
            // Obtener parámetros de búsqueda
            $filtros = [
                'texto' => $_GET['texto'] ?? '',
                'fecha_desde' => $_GET['fecha_desde'] ?? '',
                'fecha_hasta' => $_GET['fecha_hasta'] ?? '',
                'trabajador_id' => $_GET['trabajador_id'] ?? '',
                'parcela_id' => $_GET['parcela_id'] ?? '',
                'trabajo_id' => $_GET['trabajo_id'] ?? '',
                'horas_min' => $_GET['horas_min'] ?? '',
                'horas_max' => $_GET['horas_max'] ?? '',
                'orden' => $_GET['orden'] ?? 'fecha_desc',
                'limite' => intval($_GET['limite'] ?? 50)
            ];
            
            // Debug logging
            error_log("Búsqueda avanzada - Filtros recibidos: " . json_encode($filtros));
            error_log("Búsqueda avanzada - GET completo: " . json_encode($_GET));
            
            // Construir consulta SQL dinámica
            $sql = "
                SELECT DISTINCT
                    t.id,
                    t.fecha,
                    t.descripcion,
                    t.horas,
                    t.created_at,
                    t.updated_at
                FROM tareas t
                LEFT JOIN tarea_trabajadores tt ON t.id = tt.tarea_id
                LEFT JOIN tarea_parcelas tp ON t.id = tp.tarea_id
                LEFT JOIN tarea_trabajos ttj ON t.id = ttj.tarea_id
                WHERE t.id_user = ?
            ";
            
            $params = [$userId];
            $paramTypes = 'i';
            
            // Filtros adicionales
            if (!empty($filtros['texto'])) {
                $sql .= " AND t.descripcion LIKE ?";
                $params[] = '%' . $filtros['texto'] . '%';
                $paramTypes .= 's';
            }
            
            if (!empty($filtros['fecha_desde'])) {
                $sql .= " AND t.fecha >= ?";
                $params[] = $filtros['fecha_desde'];
                $paramTypes .= 's';
            }
            
            if (!empty($filtros['fecha_hasta'])) {
                $sql .= " AND t.fecha <= ?";
                $params[] = $filtros['fecha_hasta'];
                $paramTypes .= 's';
            }
            
            if (!empty($filtros['trabajador_id'])) {
                $sql .= " AND tt.trabajador_id = ?";
                $params[] = intval($filtros['trabajador_id']);
                $paramTypes .= 'i';
            }
            
            if (!empty($filtros['parcela_id'])) {
                $sql .= " AND tp.parcela_id = ?";
                $params[] = intval($filtros['parcela_id']);
                $paramTypes .= 'i';
            }
            
            if (!empty($filtros['trabajo_id'])) {
                $sql .= " AND ttj.trabajo_id = ?";
                $params[] = intval($filtros['trabajo_id']);
                $paramTypes .= 'i';
            }
            
            if (!empty($filtros['horas_min'])) {
                $sql .= " AND t.horas >= ?";
                $params[] = floatval($filtros['horas_min']);
                $paramTypes .= 'd';
            }
            
            if (!empty($filtros['horas_max'])) {
                $sql .= " AND t.horas <= ?";
                $params[] = floatval($filtros['horas_max']);
                $paramTypes .= 'd';
            }
            
            // Ordenamiento
            switch ($filtros['orden']) {
                case 'fecha_asc':
                    $sql .= " ORDER BY t.fecha ASC, t.created_at ASC";
                    break;
                case 'fecha_desc':
                    $sql .= " ORDER BY t.fecha DESC, t.created_at DESC";
                    break;
                case 'horas_asc':
                    $sql .= " ORDER BY t.horas ASC, t.fecha DESC";
                    break;
                case 'horas_desc':
                    $sql .= " ORDER BY t.horas DESC, t.fecha DESC";
                    break;
                case 'descripcion_asc':
                    $sql .= " ORDER BY t.descripcion ASC, t.fecha DESC";
                    break;
                case 'descripcion_desc':
                    $sql .= " ORDER BY t.descripcion DESC, t.fecha DESC";
                    break;
                default:
                    $sql .= " ORDER BY t.fecha DESC, t.created_at DESC";
            }
            
            // Límite
            $sql .= " LIMIT ?";
            $params[] = $filtros['limite'];
            $paramTypes .= 'i';
            
            // Ejecutar consulta
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param($paramTypes, ...$params);
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
            
            if (!empty($tareaIds)) {
                // Obtener datos relacionados de forma optimizada
                $this->cargarDatosRelacionados($tareas, $tareaIds);
            }
            
            // Calcular estadísticas
            $estadisticas = $this->calcularEstadisticas(array_values($tareas));
            
            echo json_encode([
                'success' => true,
                'tareas' => array_values($tareas),
                'estadisticas' => $estadisticas,
                'total' => count($tareas)
            ]);
            
        } catch (\Exception $e) {
            error_log("Error en búsqueda avanzada: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
        }
    }
    
    /**
     * Cargar datos relacionados (trabajadores, parcelas, trabajos)
     */
    private function cargarDatosRelacionados(&$tareas, $tareaIds)
    {
        $placeholders = str_repeat('?,', count($tareaIds) - 1) . '?';
        
        // Obtener trabajadores
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
            $tareas[$row['tarea_id']]['trabajadores'][] = $row;
        }
        $stmt->close();
        
        // Obtener parcelas
        $stmt = $this->db->prepare("
            SELECT 
                tp.tarea_id,
                p.id,
                p.nombre,
                p.ubicacion,
                tp.superficie_trabajada
            FROM tarea_parcelas tp
            JOIN parcelas p ON tp.parcela_id = p.id
            WHERE tp.tarea_id IN ($placeholders)
            ORDER BY tp.tarea_id, p.nombre
        ");
        $stmt->bind_param(str_repeat('i', count($tareaIds)), ...$tareaIds);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $tareas[$row['tarea_id']]['parcelas'][] = $row;
        }
        $stmt->close();
        
        // Obtener trabajos
        $stmt = $this->db->prepare("
            SELECT 
                tt.tarea_id,
                tr.id,
                tr.nombre,
                tt.horas_trabajo,
                tt.precio_hora
            FROM tarea_trabajos tt
            JOIN trabajos tr ON tt.trabajo_id = tr.id
            WHERE tt.tarea_id IN ($placeholders)
            ORDER BY tt.tarea_id, tr.nombre
        ");
        $stmt->bind_param(str_repeat('i', count($tareaIds)), ...$tareaIds);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $tareas[$row['tarea_id']]['trabajos'][] = $row;
        }
        $stmt->close();
    }
    
    /**
     * Calcular estadísticas de los resultados
     */
    private function calcularEstadisticas($tareas)
    {
        if (empty($tareas)) {
            return [
                'total_tareas' => 0,
                'total_horas' => 0,
                'promedio_horas' => 0,
                'fecha_mas_antigua' => null,
                'fecha_mas_reciente' => null
            ];
        }
        
        $totalHoras = array_sum(array_column($tareas, 'horas'));
        $fechas = array_column($tareas, 'fecha');
        
        return [
            'total_tareas' => count($tareas),
            'total_horas' => round($totalHoras, 2),
            'promedio_horas' => round($totalHoras / count($tareas), 2),
            'fecha_mas_antigua' => min($fechas),
            'fecha_mas_reciente' => max($fechas)
        ];
    }
    
    public function __destruct()
    {
        if ($this->db) {
            $this->db->close();
        }
    }
}
