<?php

namespace App\Controllers;

require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/Models/Parcela.php';

class DatosParcelasController extends BaseController
{
    private $db;
    private $parcelaModel;
    
    public function __construct()
    {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /');
            exit;
        }
        $this->db = \Database::connect();
        $this->parcelaModel = new \App\Models\Parcela();
    }
    
    /**
     * Mostrar listado de parcelas o detalle de una parcela específica
     */
    public function index()
    {
        $userId = $_SESSION['user_id'];
        
        // Si hay un ID en la URL, mostrar detalle de la parcela
        if (isset($_GET['id']) && !empty($_GET['id'])) {
            $this->mostrarDetalle($_GET['id'], $userId);
        } else {
            // Mostrar listado de parcelas
            $this->mostrarListado($userId);
        }
    }
    
    /**
     * Mostrar listado de parcelas
     */
    private function mostrarListado($userId)
    {
        try {
            $parcelas = $this->getAllParcelas($userId);
            
            $data = [
                'parcelas' => $parcelas,
                'user' => [
                    'name' => $_SESSION['user_name'] ?? 'Usuario'
                ]
            ];
            
            $this->render('datos/parcelas/listado', $data);
            
        } catch (\Exception $e) {
            error_log("Error mostrando listado de parcelas: " . $e->getMessage());
            $this->render('error', ['message' => 'Error al cargar las parcelas']);
        }
    }
    
    /**
     * Mostrar detalle de una parcela específica
     */
    private function mostrarDetalle($id, $userId)
    {
        try {
            $parcela = $this->parcelaModel->getDetalleConEstadisticas($id, $userId);
            
            if (!$parcela) {
                $this->render('error', ['message' => 'Parcela no encontrada']);
                return;
            }
            
            $data = [
                'parcela' => $parcela,
                'user' => [
                    'name' => $_SESSION['user_name'] ?? 'Usuario'
                ]
            ];
            
            $this->render('datos/parcelas/index', $data);
            
        } catch (\Exception $e) {
            error_log("Error mostrando detalle de parcela: " . $e->getMessage());
            $this->render('error', ['message' => 'Error al cargar la parcela']);
        }
    }
    
    /**
     * Obtener todas las parcelas del usuario
     */
    private function getAllParcelas($userId)
    {
        try {
            return $this->parcelaModel->getAll($userId);
        } catch (\Exception $e) {
            error_log("Error obteniendo parcelas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener detalle de una parcela específica
     */
    public function getParcelaDetalle()
    {
        header('Content-Type: application/json');
        
        try {
            $id = $_GET['id'] ?? null;
            $userId = $_SESSION['user_id'];
            
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'ID de parcela requerido']);
                return;
            }
            
            $parcela = $this->parcelaModel->getDetalleConEstadisticas($id, $userId);
            
            if (!$parcela) {
                echo json_encode(['success' => false, 'message' => 'Parcela no encontrada']);
                return;
            }
            
            echo json_encode(['success' => true, 'parcela' => $parcela]);
            
        } catch (\Exception $e) {
            error_log("Error obteniendo detalle de parcela: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
        }
    }
    
    /**
     * Eliminar una parcela
     */
    public function eliminar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $this->validateCsrf();

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $id = $input['id'] ?? null;
            $userId = $_SESSION['user_id'];
            
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'ID de parcela requerido']);
                return;
            }
            
            $result = $this->parcelaModel->delete($id, $userId);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Parcela eliminada correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar la parcela']);
            }
            
        } catch (\Exception $e) {
            error_log("Error eliminando parcela: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
        }
    }
    
    /**
     * Buscar parcelas
     */
    public function buscar()
    {
        header('Content-Type: application/json');
        
        try {
            $query = $_GET['q'] ?? '';
            $userId = $_SESSION['user_id'];
            
            if (strlen($query) < 2) {
                echo json_encode(['success' => true, 'parcelas' => []]);
                return;
            }
            
            $parcelas = $this->parcelaModel->buscarPorNombre($query, $userId);
            
            echo json_encode(['success' => true, 'parcelas' => $parcelas]);
            
        } catch (\Exception $e) {
            error_log("Error buscando parcelas: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
        }
    }
}
