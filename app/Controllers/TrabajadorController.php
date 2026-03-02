<?php

namespace App\Controllers;

require_once BASE_PATH . '/config/database.php';

class TrabajadorController extends BaseController
{
    public function __construct()
    {
        if (!isset($_SESSION['user_id'])) { $this->redirect('/'); exit; }
        if (($_SESSION['user_rol'] ?? '') !== 'trabajador') {
            $this->redirectByRole($_SESSION['user_rol'] ?? ''); exit;
        }
    }

    public function index()
    {
        $trabajadorId = intval($_SESSION['user_trabajador_id'] ?? 0);
        if (!$trabajadorId) { $this->redirect('/'); return; }

        $db = \Database::connect();

        // Datos del trabajador
        $stmt = $db->prepare("SELECT id, nombre FROM trabajadores WHERE id = ?");
        $stmt->bind_param("i", $trabajadorId);
        $stmt->execute();
        $trabajador = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$trabajador) { $db->close(); $this->redirect('/'); return; }

        // Deuda pendiente (meses no pagados)
        $stmt = $db->prepare("
            SELECT COALESCE(SUM(importe_total), 0) AS deuda_pendiente,
                   COUNT(*) AS meses_pendientes
            FROM pagos_mensuales_trabajadores
            WHERE trabajador_id = ? AND pagado = 0
        ");
        $stmt->bind_param("i", $trabajadorId);
        $stmt->execute();
        $deudaRow         = $stmt->get_result()->fetch_assoc();
        $deuda            = floatval($deudaRow['deuda_pendiente']);
        $mesesPendientes  = intval($deudaRow['meses_pendientes']);
        $stmt->close();

        // Tareas pendientes asignadas a este trabajador
        $stmt = $db->prepare("
            SELECT t.id, t.titulo, t.descripcion,
                   GROUP_CONCAT(DISTINCT p.nombre ORDER BY p.nombre SEPARATOR ', ') AS parcelas
            FROM tarea_trabajadores tt
            JOIN tareas t ON tt.tarea_id = t.id
            LEFT JOIN tarea_parcelas tp ON t.id = tp.tarea_id
            LEFT JOIN parcelas p ON tp.parcela_id = p.id
            WHERE tt.trabajador_id = ? AND t.estado = 'pendiente'
            GROUP BY t.id
            ORDER BY t.id DESC
        ");
        $stmt->bind_param("i", $trabajadorId);
        $stmt->execute();
        $tareasPendientes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Historial: últimas 20 tareas realizadas
        $stmt = $db->prepare("
            SELECT t.id, t.fecha, t.titulo, tt.horas_asignadas,
                   GROUP_CONCAT(DISTINCT p.nombre ORDER BY p.nombre SEPARATOR ', ') AS parcelas
            FROM tarea_trabajadores tt
            JOIN tareas t ON tt.tarea_id = t.id
            LEFT JOIN tarea_parcelas tp ON t.id = tp.tarea_id
            LEFT JOIN parcelas p ON tp.parcela_id = p.id
            WHERE tt.trabajador_id = ? AND t.estado = 'realizada'
            GROUP BY t.id, tt.horas_asignadas
            ORDER BY t.fecha DESC
            LIMIT 20
        ");
        $stmt->bind_param("i", $trabajadorId);
        $stmt->execute();
        $historial = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $db->close();

        $this->render('trabajador/index', [
            'trabajador'       => $trabajador,
            'deuda'            => $deuda,
            'mesesPendientes'  => $mesesPendientes,
            'tareasPendientes' => $tareasPendientes,
            'historial'        => $historial,
            'user'             => ['name' => $trabajador['nombre']],
        ]);
    }
}
