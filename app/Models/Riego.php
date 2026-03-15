<?php

namespace App\Models;

/**
 * Modelo para gestión de riegos.
 * Tablas: riegos, riego_parcelas
 */
class Riego
{
    private $db;

    public function __construct()
    {
        $this->db = \Database::connect();
    }

    /**
     * Obtener todos los riegos del usuario con nombre de parcela.
     * Si se especifica $anio, filtra por año de fecha_ini.
     */
    public function getAll($userId, $anio = null)
    {
        $sql = "
            SELECT r.*, p.nombre AS parcela_nombre
            FROM riegos r
            LEFT JOIN parcelas p ON r.parcela_id = p.id
            WHERE r.id_user = ?
        ";
        $params = [$userId];
        $types = "i";

        if ($anio) {
            $sql .= " AND YEAR(r.fecha_ini) = ?";
            $params[] = $anio;
            $types .= "i";
        }

        $sql .= " ORDER BY r.fecha_ini DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $result;
    }

    /**
     * Obtener un riego por ID
     */
    public function find($id, $userId)
    {
        $stmt = $this->db->prepare("SELECT * FROM riegos WHERE id = ? AND id_user = ?");
        $stmt->bind_param("ii", $id, $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }

    /**
     * Crear nuevo riego
     */
    public function create($data, $userId)
    {
        $parcelaId   = $data['parcela_id'] ?? null;
        $hidrante    = $data['hidrante'] ?? '';
        $fechaIni    = $data['fecha_ini'] ?? null;
        $fechaFin    = $data['fecha_fin'] ?? null;
        $cantidadIni = $data['cantidad_ini'] ?? null;
        $cantidadFin = $data['cantidad_fin'] ?? null;
        $dias        = $data['dias'] ?? null;

        // Calcular total_m3 automáticamente
        $totalM3 = ($cantidadFin !== null && $cantidadIni !== null)
                   ? round($cantidadFin - $cantidadIni, 2)
                   : null;

        $stmt = $this->db->prepare("
            INSERT INTO riegos (parcela_id, hidrante, fecha_ini, fecha_fin, dias, cantidad_ini, cantidad_fin, total_m3, id_user)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("isssidddi",
            $parcelaId, $hidrante, $fechaIni, $fechaFin,
            $dias, $cantidadIni, $cantidadFin, $totalM3, $userId
        );
        $ok = $stmt->execute();
        $id = $this->db->insert_id;
        $stmt->close();
        return $ok ? $id : false;
    }

    /**
     * Actualizar riego existente
     */
    public function update($id, $data, $userId)
    {
        $parcelaId   = $data['parcela_id'] ?? null;
        $hidrante    = $data['hidrante'] ?? '';
        $fechaIni    = $data['fecha_ini'] ?? null;
        $fechaFin    = $data['fecha_fin'] ?? null;
        $cantidadIni = $data['cantidad_ini'] ?? null;
        $cantidadFin = $data['cantidad_fin'] ?? null;
        $dias        = $data['dias'] ?? null;

        $totalM3 = ($cantidadFin !== null && $cantidadIni !== null)
                   ? round($cantidadFin - $cantidadIni, 2)
                   : null;

        $stmt = $this->db->prepare("
            UPDATE riegos
            SET parcela_id = ?, hidrante = ?, fecha_ini = ?, fecha_fin = ?,
                dias = ?, cantidad_ini = ?, cantidad_fin = ?, total_m3 = ?
            WHERE id = ? AND id_user = ?
        ");
        $stmt->bind_param("isssidddii",
            $parcelaId, $hidrante, $fechaIni, $fechaFin,
            $dias, $cantidadIni, $cantidadFin, $totalM3, $id, $userId
        );
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    /**
     * Eliminar riego
     */
    public function delete($id, $userId)
    {
        $stmt = $this->db->prepare("DELETE FROM riegos WHERE id = ? AND id_user = ?");
        $stmt->bind_param("ii", $id, $userId);
        $ok = $stmt->execute() && $stmt->affected_rows > 0;
        $stmt->close();
        return $ok;
    }

    /**
     * Obtener años disponibles (para el selector de año)
     */
    public function getAniosDisponibles($userId)
    {
        $stmt = $this->db->prepare("
            SELECT DISTINCT YEAR(fecha_ini) AS anio
            FROM riegos
            WHERE id_user = ? AND fecha_ini IS NOT NULL
            ORDER BY anio DESC
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'anio');
        $stmt->close();
        return $result;
    }

    /**
     * Resumen: total m³ consumidos y número de riegos, opcionalmente por año
     */
    public function getResumen($userId, $anio = null)
    {
        $sql = "
            SELECT COUNT(*) AS total_riegos,
                   COALESCE(SUM(total_m3), 0) AS total_m3,
                   COALESCE(SUM(dias), 0) AS total_dias
            FROM riegos
            WHERE id_user = ?
        ";
        $params = [$userId];
        $types = "i";

        if ($anio) {
            $sql .= " AND YEAR(fecha_ini) = ?";
            $params[] = $anio;
            $types .= "i";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }
}
