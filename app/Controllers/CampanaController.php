<?php

namespace App\Controllers;

require_once BASE_PATH . '/config/database.php';

class CampanaController extends BaseController
{
    public function __construct()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/');
        }
    }

    // ── Listado de campañas ─────────────────────────────────────────────────

    public function index()
    {
        $userId = $_SESSION['user_id'];
        $db     = \Database::connect();

        $stmt = $db->prepare("
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
        $campanas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $db->close();

        $this->render('campana/index', [
            'campanas' => $campanas,
            'user'     => ['name' => $_SESSION['user_name'] ?? 'Usuario'],
        ]);
    }

    // ── Detalle de una campaña ──────────────────────────────────────────────

    public function detalle()
    {
        $userId    = $_SESSION['user_id'];
        $campanaId = intval($_GET['id'] ?? 0);
        if (!$campanaId) { $this->redirect('/campana'); return; }

        $db = \Database::connect();

        // Datos de la campaña
        $stmt = $db->prepare("SELECT * FROM campanas WHERE id = ? AND id_user = ?");
        $stmt->bind_param("ii", $campanaId, $userId);
        $stmt->execute();
        $campana = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$campana) { $this->redirect('/campana'); return; }

        // Registros de la campaña con nombre de parcela
        $stmt = $db->prepare("
            SELECT r.*, p.nombre AS parcela_nombre
            FROM campana_registros r
            LEFT JOIN parcelas p ON r.parcela_id = p.id
            WHERE r.campana_id = ? AND r.id_user = ?
            ORDER BY r.fecha ASC, p.nombre ASC
        ");
        $stmt->bind_param("ii", $campanaId, $userId);
        $stmt->execute();
        $registros = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Parcelas del usuario para el select
        $parcelas = [];
        $res = $db->prepare("SELECT id, nombre FROM parcelas WHERE id_user = ? ORDER BY nombre");
        $res->bind_param("i", $userId);
        $res->execute();
        $parcelas = $res->get_result()->fetch_all(MYSQLI_ASSOC);
        $res->close();

        // Reporte: beneficio vs coste producción por parcela (en el periodo de la campaña)
        $reporte = [];
        if ($campana['fecha_inicio']) {
            $fechaFin = $campana['fecha_fin'] ?? date('Y-m-d');

            // Beneficio por parcela (desde registros)
            $stmt = $db->prepare("
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
            $fechaIni = $campana['fecha_inicio'];
            $stmt = $db->prepare("
                SELECT tp.parcela_id,
                       COALESCE(SUM(tt.horas_trabajo * tt.precio_hora), 0) AS coste_produccion
                FROM tarea_parcelas tp
                JOIN tareas t ON tp.tarea_id = t.id
                LEFT JOIN tarea_trabajos tt ON tt.tarea_id = t.id
                WHERE t.id_user = ?
                  AND t.fecha BETWEEN ? AND ?
                GROUP BY tp.parcela_id
            ");
            $stmt->bind_param("iss", $userId, $fechaIni, $fechaFin);
            $stmt->execute();
            $costesMap = [];
            $res2 = $stmt->get_result();
            while ($row = $res2->fetch_assoc()) {
                $costesMap[$row['parcela_id']] = $row['coste_produccion'];
            }
            $stmt->close();

            foreach ($beneficiosParcela as $b) {
                $coste  = $costesMap[$b['parcela_id']] ?? 0;
                $margen = $b['total_beneficio'] - $coste;
                $reporte[] = [
                    'parcela_nombre'    => $b['parcela_nombre'],
                    'total_kilos'       => $b['total_kilos'],
                    'avg_rendimiento'   => $b['avg_rendimiento'],
                    'total_beneficio'   => $b['total_beneficio'],
                    'coste_produccion'  => $coste,
                    'margen'            => $margen,
                ];
            }
        }

        $db->close();

        $this->render('campana/detalle', [
            'campana'   => $campana,
            'registros' => $registros,
            'parcelas'  => $parcelas,
            'reporte'   => $reporte,
            'user'      => ['name' => $_SESSION['user_name'] ?? 'Usuario'],
        ]);
    }

    // ── CRUD Campañas ────────────────────────────────────────────────────────

    public function crear()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']); return;
        }
        $this->validateCsrf();

        $userId = $_SESSION['user_id'];
        $input  = json_decode(file_get_contents('php://input'), true);
        $nombre      = trim($input['nombre'] ?? '');
        $fechaInicio = trim($input['fecha_inicio'] ?? '');

        if (empty($nombre) || empty($fechaInicio)) {
            echo json_encode(['success' => false, 'message' => 'Nombre y fecha de inicio son requeridos']);
            return;
        }

        $db   = \Database::connect();
        $stmt = $db->prepare("
            INSERT INTO campanas (nombre, fecha_inicio, activa, id_user, created_at, updated_at)
            VALUES (?, ?, 1, ?, NOW(), NOW())
        ");
        $stmt->bind_param("ssi", $nombre, $fechaInicio, $userId);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'id' => $db->insert_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear: ' . $stmt->error]);
        }
        $stmt->close();
        $db->close();
    }

    public function actualizar()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']); return;
        }
        $this->validateCsrf();

        $userId = $_SESSION['user_id'];
        $input  = json_decode(file_get_contents('php://input'), true);
        $id          = intval($input['id'] ?? 0);
        $nombre      = trim($input['nombre'] ?? '');
        $fechaInicio = trim($input['fecha_inicio'] ?? '');

        if (!$id || empty($nombre) || empty($fechaInicio)) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos']); return;
        }

        $db   = \Database::connect();
        $stmt = $db->prepare("
            UPDATE campanas SET nombre = ?, fecha_inicio = ?, updated_at = NOW()
            WHERE id = ? AND id_user = ?
        ");
        $stmt->bind_param("ssii", $nombre, $fechaInicio, $id, $userId);
        $ok = $stmt->execute() && $stmt->affected_rows >= 0;
        $stmt->close();
        $db->close();

        echo json_encode(['success' => $ok, 'message' => $ok ? 'Actualizado' : 'Error al actualizar']);
    }

    public function eliminar()
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
        $stmt = $db->prepare("DELETE FROM campanas WHERE id = ? AND id_user = ?");
        $stmt->bind_param("ii", $id, $userId);
        $ok = $stmt->execute() && $stmt->affected_rows > 0;
        $stmt->close();
        $db->close();

        echo json_encode(['success' => $ok, 'message' => $ok ? 'Campaña eliminada' : 'Error al eliminar']);
    }

    // ── CRUD Registros ──────────────────────────────────────────────────────

    public function crearRegistro()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']); return;
        }
        $this->validateCsrf();

        $userId = $_SESSION['user_id'];
        $input  = json_decode(file_get_contents('php://input'), true);

        $campanaId   = intval($input['campana_id'] ?? 0);
        $parcelaId   = !empty($input['parcela_id']) ? intval($input['parcela_id']) : null;
        $fecha       = trim($input['fecha'] ?? '');
        $kilos       = floatval($input['kilos'] ?? 0);
        $rendimiento = isset($input['rendimiento_pct']) && $input['rendimiento_pct'] !== '' ? floatval($input['rendimiento_pct']) : null;

        if (!$campanaId || empty($fecha) || $kilos <= 0) {
            echo json_encode(['success' => false, 'message' => 'Campaña, fecha y kilos son requeridos']); return;
        }

        $db   = \Database::connect();
        $stmt = $db->prepare("
            INSERT INTO campana_registros (campana_id, parcela_id, fecha, kilos, rendimiento_pct, id_user, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->bind_param("iisddi", $campanaId, $parcelaId, $fecha, $kilos, $rendimiento, $userId);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'id' => $db->insert_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt->error]);
        }
        $stmt->close();
        $db->close();
    }

    public function actualizarRegistro()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']); return;
        }
        $this->validateCsrf();

        $userId = $_SESSION['user_id'];
        $input  = json_decode(file_get_contents('php://input'), true);

        $id          = intval($input['id'] ?? 0);
        $parcelaId   = !empty($input['parcela_id']) ? intval($input['parcela_id']) : null;
        $fecha       = trim($input['fecha'] ?? '');
        $kilos       = floatval($input['kilos'] ?? 0);
        $rendimiento = isset($input['rendimiento_pct']) && $input['rendimiento_pct'] !== '' ? floatval($input['rendimiento_pct']) : null;

        if (!$id || empty($fecha) || $kilos <= 0) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos']); return;
        }

        $db   = \Database::connect();
        $stmt = $db->prepare("
            UPDATE campana_registros
            SET parcela_id = ?, fecha = ?, kilos = ?, rendimiento_pct = ?, updated_at = NOW()
            WHERE id = ? AND id_user = ?
        ");
        $stmt->bind_param("isddii", $parcelaId, $fecha, $kilos, $rendimiento, $id, $userId);
        $ok = $stmt->execute() && $stmt->affected_rows >= 0;
        $stmt->close();
        $db->close();

        echo json_encode(['success' => $ok]);
    }

    public function eliminarRegistro()
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
        $stmt = $db->prepare("DELETE FROM campana_registros WHERE id = ? AND id_user = ?");
        $stmt->bind_param("ii", $id, $userId);
        $ok = $stmt->execute() && $stmt->affected_rows > 0;
        $stmt->close();
        $db->close();

        echo json_encode(['success' => $ok]);
    }

    // ── Cerrar campaña ──────────────────────────────────────────────────────

    public function cerrar()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']); return;
        }
        $this->validateCsrf();

        $userId = $_SESSION['user_id'];
        $input  = json_decode(file_get_contents('php://input'), true);

        $campanaId   = intval($input['id'] ?? 0);
        $precioVenta = floatval($input['precio_venta'] ?? 0);
        $fechaFin    = trim($input['fecha_fin'] ?? date('Y-m-d'));

        if (!$campanaId || $precioVenta <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID y precio de venta son requeridos']); return;
        }

        $db = \Database::connect();

        // Calcular beneficio en cada registro: kilos * rendimiento_pct/100 * precio_venta
        $stmt = $db->prepare("
            UPDATE campana_registros
            SET beneficio = kilos * COALESCE(rendimiento_pct, 0) / 100 * ?,
                updated_at = NOW()
            WHERE campana_id = ? AND id_user = ?
        ");
        $stmt->bind_param("dii", $precioVenta, $campanaId, $userId);
        $stmt->execute();
        $stmt->close();

        // Marcar campaña como cerrada
        $stmt = $db->prepare("
            UPDATE campanas
            SET activa = 0, precio_venta = ?, fecha_fin = ?, updated_at = NOW()
            WHERE id = ? AND id_user = ?
        ");
        $stmt->bind_param("dsii", $precioVenta, $fechaFin, $campanaId, $userId);
        $ok = $stmt->execute() && $stmt->affected_rows > 0;
        $stmt->close();
        $db->close();

        echo json_encode(['success' => $ok, 'message' => $ok ? 'Campaña cerrada correctamente' : 'Error al cerrar']);
    }
}
