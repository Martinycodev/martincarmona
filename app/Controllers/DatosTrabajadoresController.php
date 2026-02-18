<?php
namespace App\Controllers;
require_once BASE_PATH . '/config/database.php';

class DatosTrabajadoresController extends BaseController
{
    private $db;
    
    public function __construct()
    {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /');
            exit;
        }
        $this->db = \Database::connect();
    }
    
    /**
     * Mostrar información detallada de un trabajador o listado si no se especifica ID
     */
    public function index()
    {
        // Obtener ID del trabajador desde la URL
        $trabajadorId = $_GET['id'] ?? null;
        
        // Si no se especifica ID, mostrar listado de trabajadores
        if (!$trabajadorId) {
            $this->mostrarListado();
            return;
        }
        
        if (!is_numeric($trabajadorId)) {
            $this->render('layouts/404', ['message' => 'ID de trabajador no válido']);
            return;
        }
        
        try {
            // Obtener datos del trabajador
            $trabajador = $this->getTrabajadorDetalle($trabajadorId, $_SESSION['user_id']);
            
            if (!$trabajador) {
                $this->render('layouts/404', ['message' => 'Trabajador no encontrado']);
                return;
            }
            
            // Obtener historial de trabajos del trabajador
            $historialTrabajos = $this->getHistorialTrabajos($trabajadorId);
            
            // Calcular estadísticas
            $estadisticas = $this->calcularEstadisticas($trabajadorId);
            
            $data = [
                'trabajador' => $trabajador,
                'historialTrabajos' => $historialTrabajos,
                'estadisticas' => $estadisticas,
                'user' => [
                    'name' => $_SESSION['user_name'] ?? 'Usuario'
                ]
            ];
            
            $this->render('datos/trabajadores/index', $data);
            
        } catch (\Exception $e) {
            error_log("Error en DatosTrabajadoresController: " . $e->getMessage());
            $this->render('layouts/404', ['message' => 'Error al cargar los datos del trabajador']);
        }
    }
    
    /**
     * Mostrar listado de trabajadores con enlaces a detalles
     */
    private function mostrarListado()
    {
        try {
            // Obtener todos los trabajadores del usuario
            $trabajadores = $this->getAllTrabajadores($_SESSION['user_id']);
            
            $data = [
                'trabajadores' => $trabajadores,
                'user' => [
                    'name' => $_SESSION['user_name'] ?? 'Usuario'
                ]
            ];
            
            $this->render('datos/trabajadores/listado', $data);
            
        } catch (\Exception $e) {
            error_log("Error mostrando listado de trabajadores: " . $e->getMessage());
            $this->render('layouts/404', ['message' => 'Error al cargar el listado de trabajadores']);
        }
    }
    
    /**
     * Obtener todos los trabajadores del usuario
     */
    private function getAllTrabajadores($userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    t.*,
                    'Sí' as dado_alta_texto,
                    0 as deuda_total
                FROM trabajadores t
                WHERE t.id_user = ?
                ORDER BY t.nombre ASC
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
     * Obtener información detallada de un trabajador
     */
    private function getTrabajadorDetalle($id, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    t.*,
                    'Sí' as dado_alta_texto,
                    NULL as ultima_fecha_alta,
                    0 as deuda_total
                FROM trabajadores t
                WHERE t.id = ? AND t.id_user = ?
            ");
            
            $stmt->bind_param("ii", $id, $userId);
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
    private function getHistorialTrabajos($trabajadorId)
    {
        // Por ahora devolvemos un array vacío ya que la tabla trabajos_trabajadores no existe
        return [];
    }
    
    /**
     * Calcular estadísticas del trabajador
     */
    private function calcularEstadisticas($trabajadorId)
    {
        // Por ahora devolvemos estadísticas vacías ya que la tabla trabajos_trabajadores no existe
        return [
            'total_trabajos' => 0,
            'total_ganado' => 0,
            'total_pagado' => 0,
            'deuda_total' => 0
        ];
    }
    
    /**
     * Actualizar información del trabajador
     */
    public function actualizar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $this->validateCsrf();

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                echo json_encode(['success' => false, 'message' => 'Datos no válidos']);
                return;
            }
            
            $id = intval($input['id'] ?? 0);
            $deuda = floatval($input['deuda'] ?? 0);
            $estado = trim($input['estado'] ?? '');
            
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID no válido']);
                return;
            }
            
            $db = \Database::connect();
            
            // Actualizar estado del trabajador
            if (!empty($estado)) {
                $stmt = $db->prepare("UPDATE trabajadores SET estado = ?, updated_at = CURDATE() WHERE id = ? AND id_user = ?");
                $stmt->bind_param("sii", $estado, $id, $_SESSION['user_id']);
                $stmt->execute();
                $stmt->close();
            }
            
            echo json_encode(['success' => true, 'message' => 'Información actualizada correctamente']);
            
        } catch (\Exception $e) {
            error_log("Error actualizando trabajador: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
        }
    }
    
    public function __destruct()
    {
        if ($this->db) {
            $this->db->close();
        }
    }
}
