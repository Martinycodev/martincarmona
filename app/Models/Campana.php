<?php

namespace App\Models;

/**
 * Modelo para gestión de campañas de aceituna y sus registros.
 * Tablas: campanas, campana_registros
 */
class Campana
{
    private $db;

    public function __construct()
    {
        $this->db = \Database::connect();
    }

    // ── Campañas ──────────────────────────────────────────────────────────────

    /**
     * Obtener todas las campañas del usuario con totales agregados
     */
    public function getAll($userId)
    {
        $stmt = $this->db->prepare("
            SELECT c.*,
                   COUNT(r.id)       AS num_registros,
                   COALESCE(SUM(r.kilos), 0) AS total_kilos,
                   COALESCE(SUM(r.beneficio), 0) AS total_beneficio
            FROM campanas c
            LEFT JOIN campana_registros r ON r.campana_id = c.id
            WHERE c.id_user = ?
            GROUP BY c.id
            ORDER BY c.fecha_inicio DESC
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $result;
    }

    /**
     * Obtener una campaña por ID
     */
    public function find($id, $userId)
    {
        $stmt = $this->db->prepare("SELECT * FROM campanas WHERE id = ? AND id_user = ?");
        $stmt->bind_param("ii", $id, $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }

    /**
     * Obtener la campaña activa del usuario (solo puede haber una)
     */
    public function getActiva($userId)
    {
        $stmt = $this->db->prepare("SELECT * FROM campanas WHERE activa = 1 AND id_user = ? LIMIT 1");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }

    /**
     * Crear nueva campaña
     */
    public function create($nombre, $fechaInicio, $userId)
    {
        $stmt = $this->db->prepare("
            INSERT INTO campanas (nombre, fecha_inicio, activa, id_user, created_at, updated_at)
            VALUES (?, ?, 1, ?, NOW(), NOW())
        ");
        $stmt->bind_param("ssi", $nombre, $fechaInicio, $userId);
        $ok = $stmt->execute();
        $id = $this->db->insert_id;
        $stmt->close();
        return $ok ? $id : false;
    }

    /**
     * Actualizar campaña
     */
    public function update($id, $nombre, $fechaInicio, $userId)
    {
        $stmt = $this->db->prepare("
            UPDATE campanas SET nombre = ?, fecha_inicio = ?, updated_at = NOW()
            WHERE id = ? AND id_user = ?
        ");
        $stmt->bind_param("ssii", $nombre, $fechaInicio, $id, $userId);
        $ok = $stmt->execute() && $stmt->affected_rows >= 0;
        $stmt->close();
        return $ok;
    }

    /**
     * Eliminar campaña (cascade elimina registros por FK)
     */
    public function delete($id, $userId)
    {
        $stmt = $this->db->prepare("DELETE FROM campanas WHERE id = ? AND id_user = ?");
        $stmt->bind_param("ii", $id, $userId);
        $ok = $stmt->execute() && $stmt->affected_rows > 0;
        $stmt->close();
        return $ok;
    }

    /**
     * Cerrar campaña: calcular beneficios y marcar como inactiva
     */
    public function cerrar($campanaId, $precioVenta, $fechaFin, $userId)
    {
        // Calcular beneficio en cada registro: kilos * rendimiento_pct/100 * precio_venta
        $stmt = $this->db->prepare("
            UPDATE campana_registros
            SET beneficio = kilos * COALESCE(rendimiento_pct, 0) / 100 * ?,
                updated_at = NOW()
            WHERE campana_id = ? AND id_user = ?
        ");
        $stmt->bind_param("dii", $precioVenta, $campanaId, $userId);
        $stmt->execute();
        $stmt->close();

        // Marcar campaña como cerrada
        $stmt = $this->db->prepare("
            UPDATE campanas
            SET activa = 0, precio_venta = ?, fecha_fin = ?, updated_at = NOW()
            WHERE id = ? AND id_user = ?
        ");
        $stmt->bind_param("dsii", $precioVenta, $fechaFin, $campanaId, $userId);
        $ok = $stmt->execute() && $stmt->affected_rows > 0;
        $stmt->close();
        return $ok;
    }

    // ── Registros ─────────────────────────────────────────────────────────────

    /**
     * Obtener registros de una campaña con nombre de parcela
     */
    public function getRegistros($campanaId, $userId)
    {
        $stmt = $this->db->prepare("
            SELECT r.*, p.nombre AS parcela_nombre
            FROM campana_registros r
            LEFT JOIN parcelas p ON r.parcela_id = p.id
            WHERE r.campana_id = ? AND r.id_user = ?
            ORDER BY r.fecha ASC, p.nombre ASC
        ");
        $stmt->bind_param("ii", $campanaId, $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $result;
    }

    /**
     * Crear registro en una campaña
     */
    public function crearRegistro($campanaId, $parcelaId, $fecha, $kilos, $rendimiento, $userId)
    {
        $stmt = $this->db->prepare("
            INSERT INTO campana_registros (campana_id, parcela_id, fecha, kilos, rendimiento_pct, id_user, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->bind_param("iisddi", $campanaId, $parcelaId, $fecha, $kilos, $rendimiento, $userId);
        $ok = $stmt->execute();
        $id = $this->db->insert_id;
        $stmt->close();
        return $ok ? $id : false;
    }

    /**
     * Actualizar registro
     */
    public function actualizarRegistro($id, $parcelaId, $fecha, $kilos, $rendimiento, $userId)
    {
        $stmt = $this->db->prepare("
            UPDATE campana_registros
            SET parcela_id = ?, fecha = ?, kilos = ?, rendimiento_pct = ?, updated_at = NOW()
            WHERE id = ? AND id_user = ?
        ");
        $stmt->bind_param("isddii", $parcelaId, $fecha, $kilos, $rendimiento, $id, $userId);
        $ok = $stmt->execute() && $stmt->affected_rows >= 0;
        $stmt->close();
        return $ok;
    }

    /**
     * Eliminar registro
     */
    public function eliminarRegistro($id, $userId)
    {
        $stmt = $this->db->prepare("DELETE FROM campana_registros WHERE id = ? AND id_user = ?");
        $stmt->bind_param("ii", $id, $userId);
        $ok = $stmt->execute() && $stmt->affected_rows > 0;
        $stmt->close();
        return $ok;
    }

    /**
     * Reporte: beneficio vs coste por parcela en el periodo de la campaña
     */
    public function getReporteParcelas($campanaId, $fechaInicio, $fechaFin, $userId)
    {
        // Beneficio por parcela
        $stmt = $this->db->prepare("
            SELECT r.parcela_id,
                   p.nombre AS parcela_nombre,
                   SUM(r.kilos) AS total_kilos,
                   AVG(r.rendimiento_pct) AS avg_rendimiento,
                   SUM(r.beneficio) AS total_beneficio
            FROM campana_registros r
            LEFT JOIN parcelas p ON r.parcela_id = p.id
            WHERE r.campana_id = ? AND r.id_user = ?
            GROUP BY r.parcela_id, p.nombre
        ");
        $stmt->bind_param("ii", $campanaId, $userId);
        $stmt->execute();
        $beneficiosParcela = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Coste de producción por parcela en el periodo
        $stmt = $this->db->prepare("
            SELECT tp.parcela_id,
                   COALESCE(SUM(tt.horas_trabajo * tt.precio_hora), 0) AS coste_produccion
            FROM tarea_parcelas tp
            JOIN tareas t ON tp.tarea_id = t.id
            LEFT JOIN tarea_trabajos tt ON tt.tarea_id = t.id
            WHERE t.id_user = ?
              AND t.fecha BETWEEN ? AND ?
            GROUP BY tp.parcela_id
        ");
        $stmt->bind_param("iss", $userId, $fechaInicio, $fechaFin);
        $stmt->execute();
        $costesMap = [];
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $costesMap[$row['parcela_id']] = $row['coste_produccion'];
        }
        $stmt->close();

        $reporte = [];
        foreach ($beneficiosParcela as $b) {
            $coste = $costesMap[$b['parcela_id']] ?? 0;
            $reporte[] = [
                'parcela_nombre'   => $b['parcela_nombre'],
                'total_kilos'      => $b['total_kilos'],
                'avg_rendimiento'  => $b['avg_rendimiento'],
                'total_beneficio'  => $b['total_beneficio'],
                'coste_produccion' => $coste,
                'margen'           => $b['total_beneficio'] - $coste,
            ];
        }
        return $reporte;
    }
}
