<?php

namespace App\Controllers;

require_once BASE_PATH . '/config/database.php';

class FitosanitariosController extends BaseController
{
    public function __construct()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/');
        }
    }

    // ── Inventario (vista principal) ────────────────────────────────────────

    public function inventario()
    {
        $userId = $_SESSION['user_id'];
        $db     = \Database::connect();

        // Inventario
        $stmt = $db->prepare("
            SELECT fi.*, pr.nombre AS proveedor_nombre
            FROM fitosanitarios_inventario fi
            LEFT JOIN proveedores pr ON fi.proveedor_id = pr.id
            WHERE fi.id_user = ?
            ORDER BY fi.producto ASC
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $inventario = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Aplicaciones recientes (últimas 50)
        $stmt = $db->prepare("
            SELECT fa.*, p.nombre AS parcela_nombre
            FROM fitosanitarios_aplicaciones fa
            LEFT JOIN parcelas p ON fa.parcela_id = p.id
            WHERE fa.id_user = ?
            ORDER BY fa.fecha DESC
            LIMIT 50
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $aplicaciones = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Proveedores para el select
        $proveedores = [];
        $res = $db->prepare("SELECT id, nombre FROM proveedores WHERE id_user = ? ORDER BY nombre");
        $res->bind_param("i", $userId);
        $res->execute();
        $proveedores = $res->get_result()->fetch_all(MYSQLI_ASSOC);
        $res->close();

        // Parcelas para el select
        $parcelas = [];
        $res = $db->prepare("SELECT id, nombre FROM parcelas WHERE id_user = ? ORDER BY nombre");
        $res->bind_param("i", $userId);
        $res->execute();
        $parcelas = $res->get_result()->fetch_all(MYSQLI_ASSOC);
        $res->close();

        // Productos distintos (para autocompletar en nueva aplicación)
        $productos = [];
        $res = $db->prepare("SELECT DISTINCT producto FROM fitosanitarios_inventario WHERE id_user = ? ORDER BY producto");
        $res->bind_param("i", $userId);
        $res->execute();
        $productos = array_column($res->get_result()->fetch_all(MYSQLI_ASSOC), 'producto');
        $res->close();

        $db->close();

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

        $db   = \Database::connect();
        $stmt = $db->prepare("
            INSERT INTO fitosanitarios_inventario
              (producto, fecha_compra, cantidad, unidad, proveedor_id, id_user, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->bind_param("ssdsii", $producto, $fechaCompra, $cantidad, $unidad, $proveedorId, $userId);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'id' => $db->insert_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt->error]);
        }
        $stmt->close();
        $db->close();
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

        $db   = \Database::connect();
        $stmt = $db->prepare("
            UPDATE fitosanitarios_inventario
            SET producto = ?, fecha_compra = ?, cantidad = ?, unidad = ?, proveedor_id = ?, updated_at = NOW()
            WHERE id = ? AND id_user = ?
        ");
        $stmt->bind_param("ssdsiii", $producto, $fechaCompra, $cantidad, $unidad, $proveedorId, $id, $userId);
        $ok = $stmt->execute() && $stmt->affected_rows >= 0;
        $stmt->close();
        $db->close();

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

        $db   = \Database::connect();
        $stmt = $db->prepare("DELETE FROM fitosanitarios_inventario WHERE id = ? AND id_user = ?");
        $stmt->bind_param("ii", $id, $userId);
        $ok = $stmt->execute() && $stmt->affected_rows > 0;
        $stmt->close();
        $db->close();

        echo json_encode(['success' => $ok]);
    }

    // ── CRUD Aplicaciones (manual) ──────────────────────────────────────────

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

        $db   = \Database::connect();
        $stmt = $db->prepare("
            INSERT INTO fitosanitarios_aplicaciones
              (parcela_id, producto, fecha, cantidad, id_user, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->bind_param("isddi", $parcelaId, $producto, $fecha, $cantidad, $userId);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'id' => $db->insert_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt->error]);
        }
        $stmt->close();
        $db->close();
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

        $db   = \Database::connect();
        $stmt = $db->prepare("DELETE FROM fitosanitarios_aplicaciones WHERE id = ? AND id_user = ? AND tarea_id IS NULL");
        $stmt->bind_param("ii", $id, $userId);
        $ok = $stmt->execute() && $stmt->affected_rows > 0;
        $stmt->close();
        $db->close();

        echo json_encode(['success' => $ok, 'message' => $ok ? 'Eliminado' : 'No se puede eliminar (puede ser auto-generado)']);
    }
}
