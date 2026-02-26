<?php

namespace App\Models;

require_once BASE_PATH . '/config/database.php';

class Movimiento
{
    private $db;

    // Categorías válidas por tipo
    public const CATEGORIAS_GASTO   = ['compras', 'reparaciones', 'inversiones', 'seguros', 'impuestos', 'gestoria'];
    public const CATEGORIAS_INGRESO = ['labores_terceros', 'subvenciones', 'liquidacion_aceite'];

    public const LABELS_CATEGORIA = [
        'compras'            => 'Compras y materiales',
        'reparaciones'       => 'Reparaciones',
        'inversiones'        => 'Inversiones',
        'seguros'            => 'Seguros',
        'impuestos'          => 'Impuestos y tasas',
        'gestoria'           => 'Gestoría',
        'labores_terceros'   => 'Labores a terceros',
        'subvenciones'       => 'Subvenciones',
        'liquidacion_aceite' => 'Liquidación aceite',
        // legacy
        'personal'  => 'Personal',
        'pago'      => 'Pago trabajador',
        'maquinaria'=> 'Maquinaria',
        'parcela'   => 'Parcela',
        'servicios' => 'Servicios',
        'subvencion'=> 'Subvención (legacy)',
        'otros'     => 'Otros',
    ];

    private const SELECT_JOINS = "
        SELECT m.*,
               p.nombre    AS proveedor_nombre,
               t.nombre    AS trabajador_nombre,
               t.apellidos AS trabajador_apellidos,
               v.nombre    AS vehiculo_nombre,
               v.matricula AS vehiculo_matricula,
               par.nombre  AS parcela_nombre
        FROM movimientos m
        LEFT JOIN proveedores p  ON m.proveedor_id  = p.id
        LEFT JOIN trabajadores t ON m.trabajador_id = t.id
        LEFT JOIN vehiculos v    ON m.vehiculo_id   = v.id
        LEFT JOIN parcelas par   ON m.parcela_id    = par.id
    ";

    public function __construct()
    {
        $this->db = \Database::connect();
    }

    // ─── Lecturas ─────────────────────────────────────────────────────────────

    public function getAllGastos(): array
    {
        $sql = self::SELECT_JOINS . "WHERE m.tipo = 'gasto' ORDER BY m.fecha DESC, m.id DESC";
        return $this->fetchAll($sql);
    }

    public function getAllIngresos(): array
    {
        $sql = self::SELECT_JOINS . "WHERE m.tipo = 'ingreso' ORDER BY m.fecha DESC, m.id DESC";
        return $this->fetchAll($sql);
    }

    public function getById(int $id): ?array
    {
        $sql = self::SELECT_JOINS . "WHERE m.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    /**
     * Resumen financiero: saldo banco, saldo efectivo, deuda pendiente trabajadores
     */
    public function getResumen(): array
    {
        $sql = "SELECT
            COALESCE(SUM(CASE WHEN tipo='ingreso' AND cuenta='banco'     THEN importe ELSE 0 END), 0)
            - COALESCE(SUM(CASE WHEN tipo='gasto'   AND cuenta='banco'     THEN importe ELSE 0 END), 0)  AS saldo_banco,

            COALESCE(SUM(CASE WHEN tipo='ingreso' AND cuenta='efectivo'  THEN importe ELSE 0 END), 0)
            - COALESCE(SUM(CASE WHEN tipo='gasto'   AND cuenta='efectivo'  THEN importe ELSE 0 END), 0)  AS saldo_efectivo,

            COALESCE(SUM(CASE WHEN tipo='ingreso' THEN importe ELSE 0 END), 0)  AS total_ingresos,
            COALESCE(SUM(CASE WHEN tipo='gasto'   THEN importe ELSE 0 END), 0)  AS total_gastos
        FROM movimientos";

        $result = $this->db->query($sql);
        return $result->fetch_assoc() ?? [];
    }

    // ─── Escritura ────────────────────────────────────────────────────────────

    public function create(array $data): bool
    {
        $sql = "INSERT INTO movimientos
                    (fecha, tipo, concepto, categoria, importe, cuenta,
                     proveedor_id, trabajador_id, vehiculo_id, parcela_id, estado)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param(
            "ssssdsiiiis",
            $data['fecha'],
            $data['tipo'],
            $data['concepto'],
            $data['categoria'],
            $data['importe'],
            $data['cuenta'],
            $data['proveedor_id'],
            $data['trabajador_id'],
            $data['vehiculo_id'],
            $data['parcela_id'],
            $data['estado']
        );
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE movimientos
                SET fecha = ?, tipo = ?, concepto = ?, categoria = ?,
                    importe = ?, cuenta = ?,
                    proveedor_id = ?, trabajador_id = ?, vehiculo_id = ?,
                    parcela_id = ?, estado = ?
                WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param(
            "ssssdsiiiisi",
            $data['fecha'],
            $data['tipo'],
            $data['concepto'],
            $data['categoria'],
            $data['importe'],
            $data['cuenta'],
            $data['proveedor_id'],
            $data['trabajador_id'],
            $data['vehiculo_id'],
            $data['parcela_id'],
            $data['estado'],
            $id
        );
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM movimientos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // ─── Helpers privados ─────────────────────────────────────────────────────

    private function fetchAll(string $sql): array
    {
        $result = $this->db->query($sql);
        $rows = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
        }
        return $rows;
    }
}
