<?php

namespace App\Controllers;

class FitosanitariosController extends BaseController
{
    private $modelo;

    public function __construct()
    {
        $this->requireEmpresa();
        $this->modelo = new \App\Models\Fitosanitario();
    }

    // ── Inventario (vista principal) ────────────────────────────────────────

    public function inventario()
    {
        $userId = $_SESSION['user_id'];

        $inventario   = $this->modelo->getInventario($userId);
        $aplicaciones = $this->modelo->getAplicaciones($userId);
        $productos    = $this->modelo->getProductosDistintos($userId);

        // Proveedores y parcelas para los selects
        $db = \Database::connect();

        $res = $db->prepare("SELECT id, nombre FROM proveedores WHERE id_user = ? ORDER BY nombre");
        $res->bind_param("i", $userId);
        $res->execute();
        $proveedores = $res->get_result()->fetch_all(MYSQLI_ASSOC);
        $res->close();

        $res = $db->prepare("SELECT id, nombre FROM parcelas WHERE id_user = ? ORDER BY nombre");
        $res->bind_param("i", $userId);
        $res->execute();
        $parcelas = $res->get_result()->fetch_all(MYSQLI_ASSOC);
        $res->close();

        $this->render('fitosanitarios/index', [
            'inventario'   => $inventario,
            'aplicaciones' => $aplicaciones,
            'proveedores'  => $proveedores,
            'parcelas'     => $parcelas,
            'productos'    => $productos,
            'user'         => ['name' => $_SESSION['user_name'] ?? 'Usuario'],
        ]);
    }

    // ── CRUD Inventario ─────────────────────────────────────────────────────

    public function crearInventario()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']); return;
        }
        $this->validateCsrf();

        $userId      = $_SESSION['user_id'];
        $input       = json_decode(file_get_contents('php://input'), true);
        $producto    = trim($input['producto'] ?? '');
        $fechaCompra = !empty($input['fecha_compra']) ? trim($input['fecha_compra']) : null;
        $cantidad    = isset($input['cantidad']) && $input['cantidad'] !== '' ? floatval($input['cantidad']) : null;
        $unidad      = trim($input['unidad'] ?? '') ?: null;
        $proveedorId = !empty($input['proveedor_id']) ? intval($input['proveedor_id']) : null;

        if (empty($producto)) {
            echo json_encode(['success' => false, 'message' => 'El nombre del producto es requerido']); return;
        }

        $id = $this->modelo->crearInventario($producto, $fechaCompra, $cantidad, $unidad, $proveedorId, $userId);

        if ($id) {
            echo json_encode(['success' => true, 'id' => $id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear el producto']);
        }
    }

    public function actualizarInventario()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']); return;
        }
        $this->validateCsrf();

        $userId      = $_SESSION['user_id'];
        $input       = json_decode(file_get_contents('php://input'), true);
        $id          = intval($input['id'] ?? 0);
        $producto    = trim($input['producto'] ?? '');
        $fechaCompra = !empty($input['fecha_compra']) ? trim($input['fecha_compra']) : null;
        $cantidad    = isset($input['cantidad']) && $input['cantidad'] !== '' ? floatval($input['cantidad']) : null;
        $unidad      = trim($input['unidad'] ?? '') ?: null;
        $proveedorId = !empty($input['proveedor_id']) ? intval($input['proveedor_id']) : null;

        if (!$id || empty($producto)) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos']); return;
        }

        $ok = $this->modelo->actualizarInventario($id, $producto, $fechaCompra, $cantidad, $unidad, $proveedorId, $userId);
        echo json_encode(['success' => $ok]);
    }

    public function eliminarInventario()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']); return;
        }
        $this->validateCsrf();

        $userId = $_SESSION['user_id'];
        $input  = json_decode(file_get_contents('php://input'), true);
        $id     = intval($input['id'] ?? 0);

        if (!$id) { echo json_encode(['success' => false, 'message' => 'ID inválido']); return; }

        $ok = $this->modelo->eliminarInventario($id, $userId);
        echo json_encode(['success' => $ok]);
    }

    // ── CRUD Aplicaciones ───────────────────────────────────────────────────

    /**
     * Crear aplicación manual — ahora descuenta stock automáticamente del inventario
     */
    public function crearAplicacion()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']); return;
        }
        $this->validateCsrf();

        $userId    = $_SESSION['user_id'];
        $input     = json_decode(file_get_contents('php://input'), true);
        $producto  = trim($input['producto'] ?? '');
        $fecha     = trim($input['fecha'] ?? '');
        $parcelaId = !empty($input['parcela_id']) ? intval($input['parcela_id']) : null;
        $cantidad  = isset($input['cantidad']) && $input['cantidad'] !== '' ? floatval($input['cantidad']) : null;

        if (empty($producto) || empty($fecha)) {
            echo json_encode(['success' => false, 'message' => 'Producto y fecha son requeridos']); return;
        }

        // crearAplicacion() del modelo descuenta stock automáticamente
        $id = $this->modelo->crearAplicacion($parcelaId, $producto, $fecha, $cantidad, $userId);

        if ($id) {
            echo json_encode(['success' => true, 'id' => $id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al registrar la aplicación']);
        }
    }

    public function eliminarAplicacion()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']); return;
        }
        $this->validateCsrf();

        $userId = $_SESSION['user_id'];
        $input  = json_decode(file_get_contents('php://input'), true);
        $id     = intval($input['id'] ?? 0);

        if (!$id) { echo json_encode(['success' => false, 'message' => 'ID inválido']); return; }

        $ok = $this->modelo->eliminarAplicacion($id, $userId);
        echo json_encode(['success' => $ok, 'message' => $ok ? 'Eliminado' : 'No se puede eliminar (puede ser auto-generado)']);
    }
}
