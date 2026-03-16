<?php
namespace App\Controllers;

class ProveedoresController extends BaseController
{
    public function __construct()
    {
        $this->requireEmpresa();
    }

    /**
     * Listado de proveedores
     */
    public function index()
    {
        $model = new \App\Models\Proveedor();
        $proveedores = $model->getAll($_SESSION['user_id']);

        $data = [
            'proveedores' => $proveedores,
            'user' => [
                'name' => $_SESSION['user_name'] ?? 'Usuario'
            ]
        ];
        $this->render('proveedores/index', $data);
    }

    /**
     * Obtener un proveedor por ID (JSON)
     */
    public function obtener()
    {
        header('Content-Type: application/json');

        $id = $_GET['id'] ?? null;
        if (!$id || !is_numeric($id)) {
            echo json_encode(['success' => false, 'message' => 'ID no válido']);
            return;
        }

        try {
            $model = new \App\Models\Proveedor();
            $proveedor = $model->getById(intval($id), $_SESSION['user_id']);

            if ($proveedor) {
                echo json_encode(['success' => true, 'proveedor' => $proveedor]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Proveedor no encontrado']);
            }
        } catch (\Exception $e) {
            \Core\Logger::app()->error("Error obteniendo proveedor: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
        }
    }

    /**
     * Crear un nuevo proveedor (JSON)
     */
    public function crear()
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

            $nombre = trim($input['nombre'] ?? '');
            if (empty($nombre)) {
                echo json_encode(['success' => false, 'message' => 'El nombre es obligatorio']);
                return;
            }

            $data = [
                'nombre'              => $nombre,
                'razon_social'        => trim($input['razon_social'] ?? ''),
                'cif'                 => trim($input['cif'] ?? ''),
                'direccion'           => trim($input['direccion'] ?? ''),
                'telefono'            => trim($input['telefono'] ?? ''),
                'email'               => trim($input['email'] ?? ''),
                'contacto_principal'  => trim($input['contacto_principal'] ?? ''),
                'sector'              => trim($input['sector'] ?? ''),
                'productos_servicios' => trim($input['productos_servicios'] ?? ''),
                'condiciones_pago'    => trim($input['condiciones_pago'] ?? ''),
                'estado'              => trim($input['estado'] ?? 'activo'),
                'notas'               => trim($input['notas'] ?? ''),
            ];

            $model = new \App\Models\Proveedor();
            $id = $model->create($data, $_SESSION['user_id']);

            if ($id) {
                echo json_encode(['success' => true, 'message' => 'Proveedor creado correctamente', 'id' => $id]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al crear el proveedor']);
            }
        } catch (\Exception $e) {
            \Core\Logger::app()->error("Error creando proveedor: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
        }
    }

    /**
     * Actualizar un proveedor existente (JSON)
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
            $nombre = trim($input['nombre'] ?? '');

            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID no válido']);
                return;
            }

            if (empty($nombre)) {
                echo json_encode(['success' => false, 'message' => 'El nombre es obligatorio']);
                return;
            }

            $data = [
                'id'                  => $id,
                'nombre'              => $nombre,
                'razon_social'        => trim($input['razon_social'] ?? ''),
                'cif'                 => trim($input['cif'] ?? ''),
                'direccion'           => trim($input['direccion'] ?? ''),
                'telefono'            => trim($input['telefono'] ?? ''),
                'email'               => trim($input['email'] ?? ''),
                'contacto_principal'  => trim($input['contacto_principal'] ?? ''),
                'sector'              => trim($input['sector'] ?? ''),
                'productos_servicios' => trim($input['productos_servicios'] ?? ''),
                'condiciones_pago'    => trim($input['condiciones_pago'] ?? ''),
                'estado'              => trim($input['estado'] ?? 'activo'),
                'notas'               => trim($input['notas'] ?? ''),
            ];

            $model = new \App\Models\Proveedor();
            $result = $model->update($data, $_SESSION['user_id']);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Proveedor actualizado correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar el proveedor']);
            }
        } catch (\Exception $e) {
            \Core\Logger::app()->error("Error actualizando proveedor: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
        }
    }

    /**
     * Eliminar un proveedor (JSON)
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

            if (!$input) {
                echo json_encode(['success' => false, 'message' => 'Datos no válidos']);
                return;
            }

            $id = intval($input['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID no válido']);
                return;
            }

            $model = new \App\Models\Proveedor();
            $result = $model->delete($id, $_SESSION['user_id']);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Proveedor eliminado correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar el proveedor']);
            }
        } catch (\Exception $e) {
            \Core\Logger::app()->error("Error eliminando proveedor: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
        }
    }
}
