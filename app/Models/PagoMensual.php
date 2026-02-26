<?php

namespace App\Models;

require_once BASE_PATH . '/config/database.php';

class PagoMensual
{
    private $db;

    public function __construct()
    {
        $this->db = \Database::connect();
    }

    /**
     * Obtener todos los pagos mensuales del usuario, con nombre del trabajador
     */
    public function getAll(int $userId): array
    {
        $sql = "SELECT pmt.*, t.nombre as trabajador_nombre, t.apellidos as trabajador_apellidos
                FROM pagos_mensuales_trabajadores pmt
                JOIN trabajadores t ON pmt.trabajador_id = t.id
                WHERE pmt.id_user = ?
                ORDER BY pmt.year DESC, pmt.month DESC, t.nombre ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();
        return $rows;
    }

    /**
     * Pagos pendientes (pagado = 0)
     */
    public function getPendientes(int $userId): array
    {
        $sql = "SELECT pmt.*, t.nombre as trabajador_nombre, t.apellidos as trabajador_apellidos
                FROM pagos_mensuales_trabajadores pmt
                JOIN trabajadores t ON pmt.trabajador_id = t.id
                WHERE pmt.id_user = ? AND pmt.pagado = 0
                ORDER BY pmt.year DESC, pmt.month DESC, t.nombre ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();
        return $rows;
    }

    /**
     * Total deuda pendiente de pago
     */
    public function getTotalPendiente(int $userId): float
    {
        $sql = "SELECT COALESCE(SUM(importe_total), 0) as total
                FROM pagos_mensuales_trabajadores
                WHERE id_user = ? AND pagado = 0";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return (float) $row['total'];
    }

    /**
     * Comprobar si ya existe el registro para ese trabajador/mes/año
     */
    public function exists(int $trabajadorId, int $month, int $year): bool
    {
        $sql = "SELECT id FROM pagos_mensuales_trabajadores
                WHERE trabajador_id = ? AND month = ? AND year = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("iii", $trabajadorId, $month, $year);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();
        return $exists;
    }

    /**
     * Crear o actualizar el pago mensual de un trabajador
     */
    public function upsert(int $trabajadorId, int $month, int $year, float $importe, int $userId): bool
    {
        $sql = "INSERT INTO pagos_mensuales_trabajadores
                    (trabajador_id, month, year, importe_total, pagado, id_user)
                VALUES (?, ?, ?, ?, 0, ?)
                ON DUPLICATE KEY UPDATE importe_total = VALUES(importe_total)";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("iidii", $trabajadorId, $month, $year, $importe, $userId);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * Marcar un pago como pagado
     */
    public function marcarPagado(int $id, string $fechaPago): bool
    {
        $sql = "UPDATE pagos_mensuales_trabajadores
                SET pagado = 1, fecha_pago = ?
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("si", $fechaPago, $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * Calcular deuda acumulada por trabajador en un mes/año dado,
     * basada en tareas (horas_asignadas × precio_hora del trabajo).
     */
    public function calcularDeudaMes(int $month, int $year, int $userId): array
    {
        $sql = "SELECT
                    t.id          AS trabajador_id,
                    t.nombre      AS trabajador_nombre,
                    t.apellidos   AS trabajador_apellidos,
                    COALESCE(
                        SUM(tt.horas_asignadas * COALESCE(trab.precio_hora, 0)),
                        0
                    )             AS deuda_calculada
                FROM trabajadores t
                JOIN tarea_trabajadores tt ON t.id = tt.trabajador_id
                JOIN tareas ta            ON tt.tarea_id = ta.id
                LEFT JOIN tarea_trabajos ttrab ON ta.id = ttrab.tarea_id
                LEFT JOIN trabajos trab        ON ttrab.trabajo_id = trab.id
                WHERE MONTH(ta.fecha) = ?
                  AND YEAR(ta.fecha)  = ?
                  AND t.id_user = ?
                GROUP BY t.id, t.nombre, t.apellidos
                HAVING deuda_calculada > 0
                ORDER BY t.nombre ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("iii", $month, $year, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();
        return $rows;
    }

    /**
     * Deuda acumulada por trabajador en el mes actual (para el dashboard de deudas)
     */
    public function getDeudaMesActual(int $userId): array
    {
        $month = (int) date('n');
        $year  = (int) date('Y');
        return $this->calcularDeudaMes($month, $year, $userId);
    }
}
