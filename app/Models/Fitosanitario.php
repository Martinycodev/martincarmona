<?php

namespace App\Models;

/**
 * Modelo para gestión de fitosanitarios: inventario y aplicaciones.
 * Tablas: fitosanitarios_inventario, fitosanitarios_aplicaciones
 */
class Fitosanitario
{
    private $db;

    public function __construct()
    {
        $this->db = \Database::connect();
    }

    // ── Inventario ────────────────────────────────────────────────────────────

    /**
     * Obtener todo el inventario del usuario con nombre del proveedor
     */
    public function getInventario($userId)
    {
        $stmt = $this->db->prepare("
            SELECT fi.*, pr.nombre AS proveedor_nombre
            FROM fitosanitarios_inventario fi
            LEFT JOIN proveedores pr ON fi.proveedor_id = pr.id
            WHERE fi.id_user = ?
            ORDER BY fi.producto ASC
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $result;
    }

    /**
     * Obtener un producto del inventario por ID
     */
    public function findInventario($id, $userId)
    {
        $stmt = $this->db->prepare("SELECT * FROM fitosanitarios_inventario WHERE id = ? AND id_user = ?");
        $stmt->bind_param("ii", $id, $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }

    /**
     * Crear producto en inventario
     */
    public function crearInventario($producto, $fechaCompra, $cantidad, $unidad, $proveedorId, $userId)
    {
        $stmt = $this->db->prepare("
            INSERT INTO fitosanitarios_inventario
              (producto, fecha_compra, cantidad, unidad, proveedor_id, id_user, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->bind_param("ssdsii", $producto, $fechaCompra, $cantidad, $unidad, $proveedorId, $userId);
        $ok = $stmt->execute();
        $id = $this->db->insert_id;
        $stmt->close();
        return $ok ? $id : false;
    }

    /**
     * Actualizar producto en inventario
     */
    public function actualizarInventario($id, $producto, $fechaCompra, $cantidad, $unidad, $proveedorId, $userId)
    {
        $stmt = $this->db->prepare("
            UPDATE fitosanitarios_inventario
            SET producto = ?, fecha_compra = ?, cantidad = ?, unidad = ?, proveedor_id = ?, updated_at = NOW()
            WHERE id = ? AND id_user = ?
        ");
        $stmt->bind_param("ssdsiii", $producto, $fechaCompra, $cantidad, $unidad, $proveedorId, $id, $userId);
        $ok = $stmt->execute() && $stmt->affected_rows >= 0;
        $stmt->close();
        return $ok;
    }

    /**
     * Eliminar producto del inventario
     */
    public function eliminarInventario($id, $userId)
    {
        $stmt = $this->db->prepare("DELETE FROM fitosanitarios_inventario WHERE id = ? AND id_user = ?");
        $stmt->bind_param("ii", $id, $userId);
        $ok = $stmt->execute() && $stmt->affected_rows > 0;
        $stmt->close();
        return $ok;
    }

    /**
     * Obtener nombres de productos distintos (para autocompletar)
     */
    public function getProductosDistintos($userId)
    {
        $stmt = $this->db->prepare("SELECT DISTINCT producto FROM fitosanitarios_inventario WHERE id_user = ? ORDER BY producto");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'producto');
        $stmt->close();
        return $result;
    }

    /**
     * Descontar cantidad del inventario al aplicar un producto.
     * Busca por nombre de producto y resta la cantidad indicada.
     * Devuelve true si encontró el producto y descontó, false si no había stock o no se encontró.
     */
    public function descontarStock($producto, $cantidadUsada, $userId)
    {
        // Buscar el producto con stock disponible (cantidad > 0)
        $stmt = $this->db->prepare("
            SELECT id, cantidad
            FROM fitosanitarios_inventario
            WHERE producto = ? AND id_user = ? AND cantidad > 0
            ORDER BY fecha_compra ASC
            LIMIT 1
        ");
        $stmt->bind_param("si", $producto, $userId);
        $stmt->execute();
        $item = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$item) {
            return false;
        }

        $nuevaCantidad = max(0, $item['cantidad'] - $cantidadUsada);
        $stmt = $this->db->prepare("
            UPDATE fitosanitarios_inventario SET cantidad = ?, updated_at = NOW() WHERE id = ?
        ");
        $stmt->bind_param("di", $nuevaCantidad, $item['id']);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    // ── Aplicaciones ──────────────────────────────────────────────────────────

    /**
     * Obtener aplicaciones recientes del usuario
     */
    public function getAplicaciones($userId, $limit = 50)
    {
        $stmt = $this->db->prepare("
            SELECT fa.*, p.nombre AS parcela_nombre
            FROM fitosanitarios_aplicaciones fa
            LEFT JOIN parcelas p ON fa.parcela_id = p.id
            WHERE fa.id_user = ?
            ORDER BY fa.fecha DESC
            LIMIT ?
        ");
        $stmt->bind_param("ii", $userId, $limit);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $result;
    }

    /**
     * Crear aplicación y descontar stock del inventario
     */
    public function crearAplicacion($parcelaId, $producto, $fecha, $cantidad, $userId)
    {
        $stmt = $this->db->prepare("
            INSERT INTO fitosanitarios_aplicaciones
              (parcela_id, producto, fecha, cantidad, id_user, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->bind_param("isddi", $parcelaId, $producto, $fecha, $cantidad, $userId);
        $ok = $stmt->execute();
        $id = $this->db->insert_id;
        $stmt->close();

        // Descontar stock si se indicó cantidad
        if ($ok && $cantidad !== null && $cantidad > 0) {
            $this->descontarStock($producto, $cantidad, $userId);
        }

        return $ok ? $id : false;
    }

    /**
     * Eliminar aplicación (solo las manuales, no las auto-generadas por tarea)
     */
    public function eliminarAplicacion($id, $userId)
    {
        $stmt = $this->db->prepare("DELETE FROM fitosanitarios_aplicaciones WHERE id = ? AND id_user = ? AND tarea_id IS NULL");
        $stmt->bind_param("ii", $id, $userId);
        $ok = $stmt->execute() && $stmt->affected_rows > 0;
        $stmt->close();
        return $ok;
    }
}
