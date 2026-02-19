<?php

namespace App\Controllers;

use App\Models\Movimiento;
use App\Models\Proveedor;
use App\Models\Trabajador;
use App\Models\Vehiculo;
use App\Models\Parcela;

class EconomiaController extends BaseController {
    private $movimientoModel;
    private $proveedorModel;
    private $trabajadorModel;
    private $vehiculoModel;
    private $parcelaModel;
    
    public function __construct() {
        // Verificar autenticación
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/');
            return;
        }
        
        $this->movimientoModel = new Movimiento();
        $this->proveedorModel = new Proveedor();
        $this->trabajadorModel = new Trabajador();
        $this->vehiculoModel = new Vehiculo();
        $this->parcelaModel = new Parcela();
    }
    
    private function getUserId() {
        return $_SESSION['user_id'];
    }
    
    public function index() {
        $resumen = $this->movimientoModel->getResumenFinanciero();
        $movimientosRecientes = $this->movimientoModel->getMovimientosRecientes(20);
        $movimientosPorCategoria = $this->movimientoModel->getMovimientosPorCategoria();
        
        $this->render('economia/index', [
            'resumen' => $resumen,
            'movimientosRecientes' => $movimientosRecientes,
            'movimientosPorCategoria' => $movimientosPorCategoria,
            'user' => [
                'id' => $_SESSION['user_id'],
                'name' => $_SESSION['user_name'] ?? $_SESSION['username'] ?? 'Usuario'
            ]
        ]);
    }
    
    public function crear() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();

            try {
                $data = [
                    'fecha' => $_POST['fecha'],
                    'tipo' => $_POST['tipo'],
                    'concepto' => $_POST['concepto'],
                    'categoria' => $_POST['categoria'],
                    'importe' => $_POST['importe'],
                    'proveedor_id' => !empty($_POST['proveedor_id']) ? $_POST['proveedor_id'] : null,
                    'trabajador_id' => !empty($_POST['trabajador_id']) ? $_POST['trabajador_id'] : null,
                    'vehiculo_id' => !empty($_POST['vehiculo_id']) ? $_POST['vehiculo_id'] : null,
                    'parcela_id' => !empty($_POST['parcela_id']) ? $_POST['parcela_id'] : null,
                    'estado' => $_POST['estado']
                ];
                
                // Log para depuración
                error_log("Datos del movimiento: " . json_encode($data));
                
                $result = $this->movimientoModel->create($data);
                
                if ($result) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'Movimiento creado correctamente']);
                } else {
                    header('Content-Type: application/json');
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => 'Error al crear el movimiento']);
                }
            } catch (\Exception $e) {
                error_log("Error en crear movimiento: " . $e->getMessage());
                header('Content-Type: application/json');
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
        }
    }
    
    public function editar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();

            $id = $_POST['id'];
            $data = [
                'fecha' => $_POST['fecha'],
                'tipo' => $_POST['tipo'],
                'concepto' => $_POST['concepto'],
                'categoria' => $_POST['categoria'],
                'importe' => $_POST['importe'],
                'proveedor_id' => !empty($_POST['proveedor_id']) ? $_POST['proveedor_id'] : null,
                'trabajador_id' => !empty($_POST['trabajador_id']) ? $_POST['trabajador_id'] : null,
                'vehiculo_id' => !empty($_POST['vehiculo_id']) ? $_POST['vehiculo_id'] : null,
                'parcela_id' => !empty($_POST['parcela_id']) ? $_POST['parcela_id'] : null,
                'estado' => $_POST['estado']
            ];
            
            if ($this->movimientoModel->update($id, $data)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Movimiento actualizado correctamente']);
            } else {
                header('Content-Type: application/json');
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Error al actualizar el movimiento']);
            }
        }
    }
    
    public function eliminar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();

            $id = $_POST['id'];
            
            if ($this->movimientoModel->delete($id)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Movimiento eliminado correctamente']);
            } else {
                header('Content-Type: application/json');
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Error al eliminar el movimiento']);
            }
        }
    }
    
    public function obtener() {
        $id = $_GET['id'] ?? null;
        
        if ($id) {
            $movimiento = $this->movimientoModel->getById($id);
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $movimiento]);
        } else {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
        }
    }
    
    public function buscar() {
        $query = $_GET['q'] ?? '';
        
        if (strlen($query) >= 2) {
            $resultados = $this->movimientoModel->buscar($query);
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $resultados]);
        } else {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Término de búsqueda muy corto']);
        }
    }
    
    public function obtenerProveedores() {
        $proveedores = $this->proveedorModel->getAll($this->getUserId());
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $proveedores]);
    }
    
    public function obtenerTrabajadores() {
        $trabajadores = $this->trabajadorModel->getAll($this->getUserId());
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $trabajadores]);
    }
    
    public function obtenerVehiculos() {
        $vehiculos = $this->vehiculoModel->getAll($this->getUserId());
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $vehiculos]);
    }
    
    public function obtenerParcelas() {
        $parcelas = $this->parcelaModel->getAll($this->getUserId());
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $parcelas]);
    }
    
    public function obtenerResumen() {
        $resumen = $this->movimientoModel->getResumenFinanciero();
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $resumen]);
    }
}
