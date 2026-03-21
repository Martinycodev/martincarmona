<?php

namespace App\Models;


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

    public function getAllGastos(int $userId): array
    {
        $sql = self::SELECT_JOINS . "WHERE m.tipo = 'gasto' AND m.id_user = ? ORDER BY m.fecha DESC, m.id DESC";
        return $this->fetchAllPrepared($sql, "i", $userId);
    }

    public function getAllIngresos(int $userId): array
    {
        $sql = self::SELECT_JOINS . "WHERE m.tipo = 'ingreso' AND m.id_user = ? ORDER BY m.fecha DESC, m.id DESC";
        return $this->fetchAllPrepared($sql, "i", $userId);
    }

    public function getById(int $id, int $userId): ?array
    {
        $sql = self::SELECT_JOINS . "WHERE m.id = ? AND m.id_user = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ii", $id, $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    /**
     * Resumen financiero: saldo banco, saldo efectivo (filtrado por usuario)
     */
    public function getResumen(int $userId): array
    {
        $sql = "SELECT
            COALESCE(SUM(CASE WHEN tipo='ingreso' AND cuenta='banco'     THEN importe ELSE 0 END), 0)
            - COALESCE(SUM(CASE WHEN tipo='gasto'   AND cuenta='banco'     THEN importe ELSE 0 END), 0)  AS saldo_banco,

            COALESCE(SUM(CASE WHEN tipo='ingreso' AND cuenta='efectivo'  THEN importe ELSE 0 END), 0)
            - COALESCE(SUM(CASE WHEN tipo='gasto'   AND cuenta='efectivo'  THEN importe ELSE 0 END), 0)  AS saldo_efectivo,

            COALESCE(SUM(CASE WHEN tipo='ingreso' THEN importe ELSE 0 END), 0)  AS total_ingresos,
            COALESCE(SUM(CASE WHEN tipo='gasto'   THEN importe ELSE 0 END), 0)  AS total_gastos
        FROM movimientos WHERE id_user = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ?? [];
    }

    // ─── Escritura ────────────────────────────────────────────────────────────

    public function create(array $data, int $userId): bool
    {
        $sql = "INSERT INTO movimientos
                    (id_user, fecha, tipo, concepto, categoria, importe, cuenta,
                     proveedor_id, trabajador_id, vehiculo_id, parcela_id, estado)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param(
            "issssdsiiiis",
            $userId,
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

    public function update(int $id, array $data, int $userId): bool
    {
        $sql = "UPDATE movimientos
                SET fecha = ?, tipo = ?, concepto = ?, categoria = ?,
                    importe = ?, cuenta = ?,
                    proveedor_id = ?, trabajador_id = ?, vehiculo_id = ?,
                    parcela_id = ?, estado = ?
                WHERE id = ? AND id_user = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param(
            "ssssdsiiiisii",
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
            $id,
            $userId
        );
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function delete(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM movimientos WHERE id = ? AND id_user = ?");
        $stmt->bind_param("ii", $id, $userId);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // ─── Helpers privados ─────────────────────────────────────────────────────

    private function fetchAllPrepared(string $sql, string $types, ...$params): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();
        return $rows;
    }
}
