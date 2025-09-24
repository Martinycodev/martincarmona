<?php

namespace App\Controllers;

require_once BASE_PATH . '/app/Models/Tarea.php';
require_once BASE_PATH . '/config/database.php';

class TareasController extends BaseController
{
    private $tareaModel;
    private $db;
    
    public function __construct()
    {
        // Verificar autenticación
        session_start();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/');
            return;
        }
        
        $this->tareaModel = new \App\Models\Tarea();
        $this->db = \Database::connect();
    }
    
    public function index()
    {
        $userId = $_SESSION['user_id'];
        
        // Obtener página actual y límite
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $limit = 20; // 20 tareas por página
        
        // Obtener tareas paginadas y total
        $tareas = $this->tareaModel->getAllPaginated($userId, $page, $limit);
        $totalTareas = $this->tareaModel->getTotalCount($userId);
        $totalPaginas = ceil($totalTareas / $limit);
        
        // Incluir la información del usuario desde la sesión
        $user = [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'] ?? $_SESSION['username'] ?? 'Usuario'
        ];
        
        $data = [
            'titulo' => 'Gestión de Tareas',
            'mensaje' => 'Sistema de gestión de tareas del campo',
            'tareas' => $tareas,
            'user' => $user,
            'pagination' => [
                'currentPage' => $page,
                'totalPages' => $totalPaginas,
                'totalItems' => $totalTareas,
                'itemsPerPage' => $limit,
                'hasNext' => $page < $totalPaginas,
                'hasPrev' => $page > 1
            ]
        ];
        
        $this->render('tareas/index', $data);
    }

    public function crear()
    {
        $userId = $_SESSION['user_id'];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Verificar si es una petición AJAX
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                      strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
            
            if ($isAjax) {
                header('Content-Type: application/json');
                
                // Obtener datos JSON
                $input = json_decode(file_get_contents('php://input'), true);
                error_log('Datos recibidos: ' . print_r($input, true));
                
                if ($input === null) {
                    echo json_encode(['success' => false, 'message' => 'Error al procesar los datos JSON']);
                    return;
                }
                
                // Extraer user_id de la sesión o del input
                $userId = $_SESSION['user_id'] ?? ($input['user_id'] ?? 0);
                
                $tareaData = [
                    'fecha' => $input['fecha'] ?? date('Y-m-d'),
                    'descripcion' => $input['descripcion'] ?? '',
                    'parcela' => intval($input['parcela'] ?? 0),
                    'trabajo' => intval($input['trabajo'] ?? 0),
                    'horas' => floatval($input['horas'] ?? 0)
                ];
                
                // Manejar trabajadores múltiples
                if (isset($input['trabajadores']) && is_array($input['trabajadores'])) {
                    // Modo múltiple: array de IDs
                    $tareaData['trabajadores'] = array_map('intval', $input['trabajadores']);
                } elseif (isset($input['trabajador']) && $input['trabajador'] > 0) {
                    // Modo único: compatible con formato anterior
                    $tareaData['trabajador'] = intval($input['trabajador']);
                }
                
                error_log('Intentando crear tarea con datos: ' . print_r($tareaData, true));
                error_log('User ID: ' . $userId);
                
                // Validar tipos de datos
                $tareaData['fecha'] = (string) $tareaData['fecha'];
                $tareaData['descripcion'] = (string) $tareaData['descripcion'];
                $tareaData['parcela'] = (int) $tareaData['parcela'];
                $tareaData['trabajo'] = (int) $tareaData['trabajo'];
                $tareaData['horas'] = (float) $tareaData['horas'];
                $userId = (int) $userId;
                
                // No convertir trabajadores si es array (ya están convertidos)
                if (isset($tareaData['trabajador'])) {
                    $tareaData['trabajador'] = (int) $tareaData['trabajador'];
                }
                
                error_log('Datos después de la conversión: ' . print_r($tareaData, true));
                
                $result = $this->tareaModel->create($tareaData, $userId);
                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Tarea creada exitosamente', 'id' => $result]);
                } else {
                    $error = error_get_last();
                    $errorMessage = $error ? $error['message'] : 'Error desconocido';
                    error_log('Error al crear tarea: ' . $errorMessage);
                    echo json_encode(['success' => false, 'message' => 'Error al crear la tarea: ' . $errorMessage]);
                }
                return;
            }
            
            // Si no es AJAX, redirigir al dashboard
            $this->redirect('/dashboard');
            return;
        }
        
        $data = [
            'titulo' => 'Crear Nueva Tarea'
        ];
        
        $this->render('tareas/crear', $data);
    }
    
    public function actualizar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $input = json_decode(file_get_contents('php://input'), true);
        
        $tareaData = [
            'id' => $input['id'] ?? 0,
            'fecha' => $input['fecha'] ?? date('Y-m-d'),
            'descripcion' => $input['descripcion'] ?? '',
            'horas' => $input['horas'] ?? 0
        ];
        
        // Manejar trabajadores múltiples en actualización
        if (isset($input['trabajadores']) && is_array($input['trabajadores'])) {
            // Modo múltiple: array de IDs
            $tareaData['trabajadores'] = array_map('intval', $input['trabajadores']);
        } elseif (isset($input['trabajador']) && $input['trabajador'] > 0) {
            // Modo único: compatible con formato anterior
            $tareaData['trabajador'] = intval($input['trabajador']);
        }
        
        // Manejar parcela
        if (isset($input['parcela']) && $input['parcela'] > 0) {
            $tareaData['parcela'] = intval($input['parcela']);
        }
        
        // Manejar trabajo
        if (isset($input['trabajo']) && $input['trabajo'] > 0) {
            $tareaData['trabajo'] = intval($input['trabajo']);
        }
        
        if ($this->tareaModel->update($tareaData, $userId)) {
            echo json_encode(['success' => true, 'message' => 'Tarea actualizada exitosamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar la tarea']);
        }
    }
    
    public function eliminar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $input = json_decode(file_get_contents('php://input'), true);
        $taskId = $input['id'] ?? 0;
        
        if ($this->tareaModel->delete($taskId, $userId)) {
            echo json_encode(['success' => true, 'message' => 'Tarea eliminada exitosamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar la tarea']);
        }
    }
    
    /**
     * Obtener una tarea individual con todos sus detalles
     */
    public function obtener()
    {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }
        
        // Obtener ID del parámetro GET
        $id = $_GET['id'] ?? null;
        
        if (!$id || !is_numeric($id)) {
            echo json_encode(['success' => false, 'message' => 'ID no válido']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        
        try {
            // Obtener la tarea principal
            $stmt = $this->db->prepare("
                SELECT 
                    id,
                    fecha,
                    descripcion,
                    horas,
                    created_at,
                    updated_at
                FROM tareas 
                WHERE id = ? AND id_user = ?
            ");
            $stmt->bind_param("ii", $id, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if (!($tarea = $result->fetch_assoc())) {
                echo json_encode(['success' => false, 'message' => 'Tarea no encontrada']);
                return;
            }
            $stmt->close();
            
            // Obtener trabajadores asignados
            $stmt = $this->db->prepare("
                SELECT 
                    t.id,
                    t.nombre,
                    tt.horas_asignadas
                FROM tarea_trabajadores tt
                JOIN trabajadores t ON tt.trabajador_id = t.id
                WHERE tt.tarea_id = ?
                ORDER BY t.nombre
            ");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $trabajadores = [];
            while ($row = $result->fetch_assoc()) {
                $trabajadores[] = $row;
            }
            $stmt->close();
            
            // Obtener parcelas asignadas
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
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $parcelas = [];
            while ($row = $result->fetch_assoc()) {
                $parcelas[] = $row;
            }
            $stmt->close();
            
            // Obtener trabajos asignados
            $stmt = $this->db->prepare("
                SELECT 
                    tr.id,
                    tr.nombre,
                    tt.horas_trabajo,
                    tt.precio_hora
                FROM tarea_trabajos tt
                JOIN trabajos tr ON tt.trabajo_id = tr.id
                WHERE tt.tarea_id = ?
                ORDER BY tr.nombre
            ");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $trabajos = [];
            while ($row = $result->fetch_assoc()) {
                $trabajos[] = $row;
            }
            $stmt->close();
            
            // Construir respuesta completa
            $tarea['trabajadores'] = $trabajadores;
            $tarea['parcelas'] = $parcelas;
            $tarea['trabajos'] = $trabajos;
            
            echo json_encode(['success' => true, 'tarea' => $tarea]);
            
        } catch (\Exception $e) {
            error_log("Error obteniendo tarea: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
        }
    }
    
    /**
     * Obtener tareas de un mes específico (OPTIMIZADO para calendario)
     */
    public function obtenerPorMes()
    {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $year = $_GET['year'] ?? date('Y');
        $month = $_GET['month'] ?? date('m');
        
        // Validar parámetros
        $year = (int) $year;
        $month = (int) $month;
        
        if ($year < 2020 || $year > 2050) {
            echo json_encode(['success' => false, 'message' => 'Año inválido']);
            return;
        }
        
        if ($month < 1 || $month > 12) {
            echo json_encode(['success' => false, 'message' => 'Mes inválido']);
            return;
        }
        
        try {
            $tareas = $this->tareaModel->getTareasByMonth($userId, $year, $month);
            echo json_encode([
                'success' => true, 
                'tareas' => $tareas,
                'year' => $year,
                'month' => $month,
                'total' => count($tareas)
            ]);
            
        } catch (\Exception $e) {
            error_log("Error obteniendo tareas del mes: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error al obtener tareas']);
        }
    }
}
