<?php

namespace App\Models;

require_once BASE_PATH . '/config/database.php';

class Parcela
{
    private $db;

    public function __construct()
    {
        $this->db = \Database::connect();
    }

    /**
     * Crear una nueva parcela
     */
    public function create($data, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO parcelas (nombre, olivos, ubicacion, empresa, propietario, hidrante, descripcion, id_user)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->bind_param(
                "sisssiis",
                $data['nombre'],
                $data['olivos'],
                $data['ubicacion'],
                $data['empresa'],
                $data['propietario'],
                $data['hidrante'],
                $data['descripcion'],
                $userId
            );

            $result = $stmt->execute();
            $insertId = $this->db->insert_id;
            $stmt->close();

            return $result ? $insertId : false;

        } catch (\Exception $e) {
            error_log("Error creando parcela: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener todas las parcelas del usuario
     */
    public function getAll($userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    id,
                    nombre,
                    olivos,
                    ubicacion,
                    empresa,
                    propietario,
                    hidrante,
                    descripcion
                FROM parcelas 
                WHERE id_user = ?
                ORDER BY nombre ASC
            ");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();

            $parcelas = [];
            while ($row = $result->fetch_assoc()) {
                $parcelas[] = $row;
            }

            $stmt->close();
            return $parcelas;

        } catch (\Exception $e) {
            error_log("Error obteniendo parcelas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener una parcela por ID
     */
    public function getById($id, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM parcelas 
                WHERE id = ? AND id_user = ?
            ");
            $stmt->bind_param("ii", $id, $userId);
            $stmt->execute();
            $result = $stmt->get_result();

            $parcela = $result->fetch_assoc();
            $stmt->close();

            return $parcela;

        } catch (\Exception $e) {
            error_log("Error obteniendo parcela: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener detalle de parcela con estadísticas (para vista detallada)
     */
    public function getDetalleConEstadisticas($id, $userId)
    {
        try {
            // Obtener datos básicos de la parcela
            $parcela = $this->getById($id, $userId);
            if (!$parcela) {
                return false;
            }

            // Agregar campos futuros (placeholder por ahora)
            $parcela['foto'] = ''; // Campo futuro
            $parcela['referencia_catastral'] = ''; // Campo futuro
            $parcela['rendimiento_anual'] = 0; // Campo futuro
            $parcela['kilos_ultimo_ano'] = 0; // Campo futuro
            $parcela['superficie'] = 0; // Campo futuro
            $parcela['tipo_cultivo'] = 'Olivo'; // Campo futuro
            $parcela['estado'] = 'Activa'; // Campo futuro
            $parcela['fecha_plantacion'] = null; // Campo futuro
            $parcela['ultima_cosecha'] = null; // Campo futuro

            return $parcela;

        } catch (\Exception $e) {
            error_log("Error obteniendo detalle de parcela: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar una parcela existente
     */
    public function update($data, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE parcelas 
                SET nombre = ?, olivos = ?, ubicacion = ?, empresa = ?, propietario = ?, hidrante = ?, descripcion = ?
                WHERE id = ? AND id_user = ?
            ");

            $stmt->bind_param(
                "sisssiis",
                $data['nombre'],
                $data['olivos'],
                $data['ubicacion'],
                $data['empresa'],
                $data['propietario'],
                $data['hidrante'],
                $data['descripcion'],
                $data['id'],
                $userId
            );

            $result = $stmt->execute();
            $stmt->close();

            return $result;

        } catch (\Exception $e) {
            error_log("Error actualizando parcela: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar una parcela
     */
    public function delete($id, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM parcelas 
                WHERE id = ? AND id_user = ?
            ");

            $stmt->bind_param("ii", $id, $userId);
            $result = $stmt->execute();
            $stmt->close();

            return $result;

        } catch (\Exception $e) {
            error_log("Error eliminando parcela: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Buscar parcelas por nombre
     */
    public function buscarPorNombre($query, $userId)
    {
        try {
            $query = "%{$query}%";
            $stmt = $this->db->prepare("
                SELECT id, nombre, olivos, ubicacion
                FROM parcelas 
                WHERE nombre LIKE ? AND id_user = ?
                ORDER BY nombre
                LIMIT 10
            ");

            $stmt->bind_param("si", $query, $userId);
            $stmt->execute();
            $result = $stmt->get_result();

            $parcelas = [];
            while ($row = $result->fetch_assoc()) {
                $parcelas[] = $row;
            }

            $stmt->close();
            return $parcelas;

        } catch (\Exception $e) {
            error_log("Error buscando parcelas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener estadísticas de parcelas
     */
    public function getStats($userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_parcelas,
                    SUM(olivos) as total_olivos,
                    COUNT(DISTINCT propietario) as total_propietarios,
                    COUNT(DISTINCT empresa) as total_empresas
                FROM parcelas 
                WHERE id_user = ?
            ");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();

            $stats = $result->fetch_assoc();
            $stmt->close();

            return [
                'total_parcelas' => $stats['total_parcelas'] ?? 0,
                'total_olivos' => $stats['total_olivos'] ?? 0,
                'total_propietarios' => $stats['total_propietarios'] ?? 0,
                'total_empresas' => $stats['total_empresas'] ?? 0
            ];

        } catch (\Exception $e) {
            error_log("Error obteniendo estadísticas de parcelas: " . $e->getMessage());
            return [
                'total_parcelas' => 0,
                'total_olivos' => 0,
                'total_propietarios' => 0,
                'total_empresas' => 0
            ];
        }
    }

}