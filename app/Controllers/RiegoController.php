<?php
namespace App\Controllers;
require_once BASE_PATH . '/config/database.php';

class RiegoController extends BaseController
{
    public function __construct()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /');
            exit;
        }
    }

    public function index()
    {
        $db = \Database::connect();
        $stmt = $db->prepare("
            SELECT r.*, p.nombre as parcela_nombre
            FROM riegos r
            LEFT JOIN parcelas p ON r.parcela_id = p.id
            WHERE r.id_user = ?
            ORDER BY r.fecha_ini DESC
        ");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $riegos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Cargar parcelas del usuario para el select del formulario
        $stmt = $db->prepare("SELECT id, nombre FROM parcelas WHERE id_user = ? ORDER BY nombre");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $parcelas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $db->close();

        $this->render('riego/index', [
            'riegos'   => $riegos,
            'parcelas' => $parcelas,
            'user'     => ['name' => $_SESSION['user_name'] ?? 'Usuario']
        ]);
    }

    public function crear()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        $this->validateCsrf();

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                echo json_encode(['success' => false, 'message' => 'Datos no válidos']);
                return;
            }

            $parcela_id   = !empty($input['parcela_id']) ? intval($input['parcela_id']) : null;
            $hidrante     = trim($input['hidrante'] ?? '');
            $fecha_ini    = !empty($input['fecha_ini']) ? $input['fecha_ini'] : null;
            $fecha_fin    = !empty($input['fecha_fin']) ? $input['fecha_fin'] : null;
            $cantidad_ini = isset($input['cantidad_ini']) ? floatval($input['cantidad_ini']) : null;
            $cantidad_fin = isset($input['cantidad_fin']) ? floatval($input['cantidad_fin']) : null;
            $dias         = !empty($input['dias']) ? intval($input['dias']) : null;

            // Calcular total_m3 automáticamente
            $total_m3 = ($cantidad_fin !== null && $cantidad_ini !== null)
                        ? round($cantidad_fin - $cantidad_ini, 2)
                        : null;

            $db   = \Database::connect();
            $stmt = $db->prepare("
                INSERT INTO riegos (parcela_id, hidrante, fecha_ini, fecha_fin, dias, cantidad_ini, cantidad_fin, total_m3, id_user)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("isssidddi",
                $parcela_id, $hidrante, $fecha_ini, $fecha_fin,
                $dias, $cantidad_ini, $cantidad_fin, $total_m3,
                $_SESSION['user_id']
            );

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'id' => $db->insert_id]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al crear el riego: ' . $stmt->error]);
            }
            $stmt->close();
            $db->close();

        } catch (\Exception $e) {
            error_log("Error creando riego: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
        }
    }

    public function obtener()
    {
        header('Content-Type: application/json');
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID no válido']);
            return;
        }

        try {
            $db   = \Database::connect();
            $stmt = $db->prepare("SELECT * FROM riegos WHERE id = ? AND id_user = ?");
            $stmt->bind_param("ii", $id, $_SESSION['user_id']);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            $db->close();

            if ($row) {
                echo json_encode(['success' => true, 'riego' => $row]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Riego no encontrado']);
            }
        } catch (\Exception $e) {
            error_log("Error obteniendo riego: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
        }
    }

    public function actualizar()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        $this->validateCsrf();

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                echo json_encode(['success' => false, 'message' => 'Datos no válidos']);
                return;
            }

            $id           = intval($input['id'] ?? 0);
            $parcela_id   = !empty($input['parcela_id']) ? intval($input['parcela_id']) : null;
            $hidrante     = trim($input['hidrante'] ?? '');
            $fecha_ini    = !empty($input['fecha_ini']) ? $input['fecha_ini'] : null;
            $fecha_fin    = !empty($input['fecha_fin']) ? $input['fecha_fin'] : null;
            $cantidad_ini = isset($input['cantidad_ini']) ? floatval($input['cantidad_ini']) : null;
            $cantidad_fin = isset($input['cantidad_fin']) ? floatval($input['cantidad_fin']) : null;
            $dias         = !empty($input['dias']) ? intval($input['dias']) : null;

            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID no válido']);
                return;
            }

            $total_m3 = ($cantidad_fin !== null && $cantidad_ini !== null)
                        ? round($cantidad_fin - $cantidad_ini, 2)
                        : null;

            $db   = \Database::connect();
            $stmt = $db->prepare("
                UPDATE riegos
                SET parcela_id = ?, hidrante = ?, fecha_ini = ?, fecha_fin = ?,
                    dias = ?, cantidad_ini = ?, cantidad_fin = ?, total_m3 = ?
                WHERE id = ? AND id_user = ?
            ");
            $stmt->bind_param("isssidddii",
                $parcela_id, $hidrante, $fecha_ini, $fecha_fin,
                $dias, $cantidad_ini, $cantidad_fin, $total_m3,
                $id, $_SESSION['user_id']
            );

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Riego actualizado correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar: ' . $stmt->error]);
            }
            $stmt->close();
            $db->close();

        } catch (\Exception $e) {
            error_log("Error actualizando riego: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
        }
    }

    public function eliminar()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        $this->validateCsrf();

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $id    = intval($input['id'] ?? 0);

            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID no válido']);
                return;
            }

            $db   = \Database::connect();
            $stmt = $db->prepare("DELETE FROM riegos WHERE id = ? AND id_user = ?");
            $stmt->bind_param("ii", $id, $_SESSION['user_id']);

            if ($stmt->execute() && $stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Riego eliminado correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se encontró el riego']);
            }
            $stmt->close();
            $db->close();

        } catch (\Exception $e) {
            error_log("Error eliminando riego: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
        }
    }
}
