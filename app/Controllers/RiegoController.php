<?php
namespace App\Controllers;

class RiegoController extends BaseController
{
    private $modelo;

    public function __construct()
    {
        $this->requireEmpresa();
        $this->modelo = new \App\Models\Riego();
    }

    public function index()
    {
        $userId = $_SESSION['user_id'];

        // Año seleccionado (GET param o null para todos)
        $anio = isset($_GET['anio']) && $_GET['anio'] !== '' ? intval($_GET['anio']) : null;

        // Datos del modelo
        $riegos   = $this->modelo->getAll($userId, $anio);
        $anios    = $this->modelo->getAniosDisponibles($userId);
        $resumen  = $this->modelo->getResumen($userId, $anio);

        // Parcelas del usuario que tienen hidrante asignado (para el select del formulario)
        $db = \Database::connect();
        $stmt = $db->prepare("SELECT id, nombre, hidrante FROM parcelas WHERE id_user = ? AND hidrante IS NOT NULL AND hidrante > 0 ORDER BY nombre");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $parcelas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Temporada activa
        $temporadaActiva = $this->modelo->getTemporadaActiva($userId);

        $this->render('riego/index', [
            'riegos'           => $riegos,
            'parcelas'         => $parcelas,
            'anios'            => $anios,
            'anioActual'       => $anio,
            'resumen'          => $resumen,
            'temporadaActiva'  => $temporadaActiva,
            'user'             => ['name' => $_SESSION['user_name'] ?? 'Usuario']
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

            $data = [
                'parcela_id'   => !empty($input['parcela_id']) ? intval($input['parcela_id']) : null,
                'hidrante'     => trim($input['hidrante'] ?? ''),
                'fecha_ini'    => !empty($input['fecha_ini']) ? $input['fecha_ini'] : null,
                'fecha_fin'    => !empty($input['fecha_fin']) ? $input['fecha_fin'] : null,
                'cantidad_ini' => isset($input['cantidad_ini']) ? floatval($input['cantidad_ini']) : null,
                'cantidad_fin' => isset($input['cantidad_fin']) ? floatval($input['cantidad_fin']) : null,
                'dias'         => !empty($input['dias']) ? intval($input['dias']) : null,
            ];

            $id = $this->modelo->create($data, $_SESSION['user_id']);

            if ($id) {
                echo json_encode(['success' => true, 'id' => $id]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al crear el riego']);
            }

        } catch (\Exception $e) {
            \Core\Logger::app()->error("Error creando riego: " . $e->getMessage());
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
            $row = $this->modelo->find($id, $_SESSION['user_id']);
            if ($row) {
                echo json_encode(['success' => true, 'riego' => $row]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Riego no encontrado']);
            }
        } catch (\Exception $e) {
            \Core\Logger::app()->error("Error obteniendo riego: " . $e->getMessage());
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

            $id = intval($input['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID no válido']);
                return;
            }

            $data = [
                'parcela_id'   => !empty($input['parcela_id']) ? intval($input['parcela_id']) : null,
                'hidrante'     => trim($input['hidrante'] ?? ''),
                'fecha_ini'    => !empty($input['fecha_ini']) ? $input['fecha_ini'] : null,
                'fecha_fin'    => !empty($input['fecha_fin']) ? $input['fecha_fin'] : null,
                'cantidad_ini' => isset($input['cantidad_ini']) ? floatval($input['cantidad_ini']) : null,
                'cantidad_fin' => isset($input['cantidad_fin']) ? floatval($input['cantidad_fin']) : null,
                'dias'         => !empty($input['dias']) ? intval($input['dias']) : null,
            ];

            $ok = $this->modelo->update($id, $data, $_SESSION['user_id']);

            if ($ok) {
                echo json_encode(['success' => true, 'message' => 'Riego actualizado correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar']);
            }

        } catch (\Exception $e) {
            \Core\Logger::app()->error("Error actualizando riego: " . $e->getMessage());
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

            $ok = $this->modelo->delete($id, $_SESSION['user_id']);

            if ($ok) {
                echo json_encode(['success' => true, 'message' => 'Riego eliminado correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se encontró el riego']);
            }

        } catch (\Exception $e) {
            \Core\Logger::app()->error("Error eliminando riego: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
        }
    }

    /**
     * Iniciar temporada de riego
     */
    public function iniciarTemporada()
    {
        header('Content-Type: application/json');
        $this->validateCsrf();

        $input = json_decode(file_get_contents('php://input'), true);
        $anio = intval($input['anio'] ?? date('Y'));

        $ok = $this->modelo->iniciarTemporada($anio, $_SESSION['user_id']);
        echo json_encode([
            'success' => $ok,
            'message' => $ok ? "Temporada $anio iniciada" : 'Error al iniciar temporada'
        ]);
    }

    /**
     * Terminar temporada de riego activa
     */
    public function terminarTemporada()
    {
        header('Content-Type: application/json');
        $this->validateCsrf();

        $ok = $this->modelo->terminarTemporada($_SESSION['user_id']);
        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Temporada de riego finalizada' : 'No hay temporada activa'
        ]);
    }
}
