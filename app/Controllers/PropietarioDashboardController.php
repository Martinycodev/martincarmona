<?php

namespace App\Controllers;


class PropietarioDashboardController extends BaseController
{
    public function __construct()
    {
        if (!isset($_SESSION['user_id'])) { $this->redirect('/'); exit; }
        if (($_SESSION['user_rol'] ?? '') !== 'propietario') {
            $this->redirectByRole($_SESSION['user_rol'] ?? ''); exit;
        }
    }

    public function index()
    {
        $propietarioId = intval($_SESSION['user_propietario_id'] ?? 0);
        if (!$propietarioId) { $this->redirect('/'); return; }

        $db = \Database::connect();

        // Datos del propietario
        $stmt = $db->prepare("SELECT id, nombre, apellidos FROM propietarios WHERE id = ?");
        $stmt->bind_param("i", $propietarioId);
        $stmt->execute();
        $propietario = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$propietario) { $db->close(); $this->redirect('/'); return; }

        // Sus parcelas
        $stmt = $db->prepare("
            SELECT id, nombre, ubicacion, olivos, riego_secano, tipo_plantacion
            FROM parcelas
            WHERE propietario_id = ?
            ORDER BY nombre ASC
        ");
        $stmt->bind_param("i", $propietarioId);
        $stmt->execute();
        $parcelas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Tareas realizadas en sus parcelas (últimas 50, sin horas ni precios)
        $tareas = [];
        if (!empty($parcelas)) {
            $parcelaIds   = array_column($parcelas, 'id');
            $placeholders = implode(',', array_fill(0, count($parcelaIds), '?'));
            $types        = str_repeat('i', count($parcelaIds));

            $stmt = $db->prepare("
                SELECT DISTINCT t.id, t.fecha, t.titulo, t.descripcion,
                       GROUP_CONCAT(DISTINCT p.nombre ORDER BY p.nombre SEPARATOR ', ') AS parcelas_nombres
                FROM tareas t
                JOIN tarea_parcelas tp ON t.id = tp.tarea_id
                JOIN parcelas p ON tp.parcela_id = p.id
                WHERE tp.parcela_id IN ($placeholders) AND t.estado = 'realizada'
                GROUP BY t.id
                ORDER BY t.fecha DESC
                LIMIT 50
            ");
            $stmt->bind_param($types, ...$parcelaIds);
            $stmt->execute();
            $tareas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        }

        $db->close();

        $nombreCompleto = $propietario['nombre'] . ($propietario['apellidos'] ? ' ' . $propietario['apellidos'] : '');

        $this->render('propietario/index', [
            'propietario' => $propietario,
            'parcelas'    => $parcelas,
            'tareas'      => $tareas,
            'user'        => ['name' => $nombreCompleto],
        ]);
    }

    public function parcelaDetalle()
    {
        $propietarioId = intval($_SESSION['user_propietario_id'] ?? 0);
        $parcelaId     = intval($_GET['id'] ?? 0);

        if (!$propietarioId || !$parcelaId) {
            $this->redirect('/propietario');
            return;
        }

        $db = \Database::connect();

        // Verificar que la parcela pertenezca a este propietario
        $stmt = $db->prepare("
            SELECT p.id, p.nombre, p.ubicacion, p.olivos, p.hidrante,
                   p.tipo_plantacion, p.riego_secano, p.descripcion,
                   pr.nombre AS prop_nombre, pr.apellidos AS prop_apellidos
            FROM parcelas p
            JOIN propietarios pr ON p.propietario_id = pr.id
            WHERE p.id = ? AND p.propietario_id = ?
        ");
        $stmt->bind_param("ii", $parcelaId, $propietarioId);
        $stmt->execute();
        $parcela = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$parcela) {
            $this->redirect('/propietario');
            return;
        }

        // Tareas realizadas en esta parcela (últimas 30)
        $stmt = $db->prepare("
            SELECT t.fecha, t.titulo, t.descripcion
            FROM tareas t
            JOIN tarea_parcelas tp ON t.id = tp.tarea_id
            WHERE tp.parcela_id = ? AND t.estado = 'realizada'
            ORDER BY t.fecha DESC
            LIMIT 30
        ");
        $stmt->bind_param("i", $parcelaId);
        $stmt->execute();
        $tareas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $nombreCompleto = $parcela['prop_nombre'] . ($parcela['prop_apellidos'] ? ' ' . $parcela['prop_apellidos'] : '');

        $this->render('propietario/parcela_detalle', [
            'parcela' => $parcela,
            'tareas'  => $tareas,
            'user'    => ['name' => $nombreCompleto],
        ]);
    }
}
