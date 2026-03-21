<?php

namespace App\Controllers;

use App\Models\Recordatorio;

/**
 * API JSON para el sistema de recordatorios/notificaciones.
 * Todos los métodos devuelven JSON.
 */
class NotificacionesController extends BaseController
{
    private $modelo;

    public function __construct()
    {
        $this->requireEmpresa();
        $this->modelo = new Recordatorio();
    }

    /**
     * GET /notificaciones/pendientes — badge + lista para el dropdown del header
     * También genera automáticamente recordatorios de ITV y cuentas.
     */
    public function pendientes()
    {
        header('Content-Type: application/json');
        $userId = $_SESSION['user_id'];

        // Generar recordatorios automáticos si toca
        $config = $this->modelo->getConfig($userId);

        if (!empty($config['itv']['activo'])) {
            $dias = intval($config['itv']['dias_antelacion'] ?? 15);
            $this->modelo->generarITV($userId, $dias);
        }
        if (!empty($config['cuentas']['activo'])) {
            $this->modelo->generarCuentas($userId);
        }
        if (!empty($config['jornadas']['activo'])) {
            $this->modelo->generarJornadas($userId);
        }

        // Obtener recordatorios activos
        $recordatorios = $this->modelo->getActivos($userId);
        $total = $this->modelo->contarPendientes($userId);

        echo json_encode([
            'success' => true,
            'total' => $total,
            'recordatorios' => $recordatorios
        ]);
    }

    /**
     * POST /notificaciones/crear — crear recordatorio personalizado
     */
    public function crear()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        $this->validateCsrf();

        $input = json_decode(file_get_contents('php://input'), true);
        $titulo = trim($input['titulo'] ?? '');
        $descripcion = trim($input['descripcion'] ?? '');
        $fechaAviso = trim($input['fecha_aviso'] ?? '');
        $repeticion = isset($input['repeticion']) ? trim($input['repeticion']) : null;

        if (empty($titulo) || empty($fechaAviso)) {
            echo json_encode(['success' => false, 'message' => 'Título y fecha son obligatorios']);
            return;
        }

        // Validar repetición: 'mensual', 'anual', o número de días
        if ($repeticion !== null && $repeticion !== '') {
            if (!in_array($repeticion, ['mensual', 'anual']) && !ctype_digit($repeticion)) {
                $repeticion = null;
            }
        } else {
            $repeticion = null;
        }

        $id = $this->modelo->crear($_SESSION['user_id'], $titulo, $descripcion, $fechaAviso, $repeticion);

        if ($id) {
            echo json_encode(['success' => true, 'id' => $id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear recordatorio']);
        }
    }

    /**
     * POST /notificaciones/leido — marcar como leído
     */
    public function leido()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        $this->validateCsrf();

        $input = json_decode(file_get_contents('php://input'), true);
        $id = intval($input['id'] ?? 0);

        if ($id > 0 && $this->modelo->marcarLeido($id, $_SESSION['user_id'])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error']);
        }
    }

    /**
     * POST /notificaciones/eliminar
     */
    public function eliminar()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        $this->validateCsrf();

        $input = json_decode(file_get_contents('php://input'), true);
        $id = intval($input['id'] ?? 0);

        if ($id > 0 && $this->modelo->eliminar($id, $_SESSION['user_id'])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error']);
        }
    }

    /**
     * GET /notificaciones/config — obtener configuración
     */
    public function config()
    {
        header('Content-Type: application/json');
        $config = $this->modelo->getConfig($_SESSION['user_id']);
        echo json_encode(['success' => true, 'config' => $config]);
    }

    /**
     * POST /notificaciones/toggleConfig — activar/desactivar tipo
     */
    public function toggleConfig()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        $this->validateCsrf();

        $input = json_decode(file_get_contents('php://input'), true);
        $tipo = trim($input['tipo'] ?? '');
        $activo = !empty($input['activo']);

        if (empty($tipo)) {
            echo json_encode(['success' => false, 'message' => 'Tipo requerido']);
            return;
        }

        if ($this->modelo->toggleConfig($_SESSION['user_id'], $tipo, $activo)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error']);
        }
    }
}
