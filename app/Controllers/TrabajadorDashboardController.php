<?php

namespace App\Controllers;


class TrabajadorDashboardController extends BaseController
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

        // Datos del trabajador (incluye alta/baja en Seguridad Social)
        $stmt = $db->prepare("SELECT id, nombre, alta_ss, baja_ss, id_user FROM trabajadores WHERE id = ?");
        $stmt->bind_param("i", $trabajadorId);
        $stmt->execute();
        $trabajador = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$trabajador) { $db->close(); $this->redirect('/'); return; }

        // Nombre de la empresa (usuario propietario del trabajador)
        $nombreEmpresa = '';
        if (!empty($trabajador['id_user'])) {
            $stmt = $db->prepare("SELECT name FROM usuarios WHERE id = ? AND rol IN ('empresa','admin')");
            $stmt->bind_param("i", $trabajador['id_user']);
            $stmt->execute();
            $empresa = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if ($empresa) {
                $nombreEmpresa = $empresa['name'];
            }
        }

        // Deuda estimada: valor total generado en tareas
        $stmt = $db->prepare("
            SELECT COALESCE(
                SUM(tt.horas_asignadas * COALESCE(ttrab.precio_hora, trab.precio_hora, 0)), 0
            ) AS total_generado
            FROM tarea_trabajadores tt
            JOIN tareas ta ON tt.tarea_id = ta.id
            LEFT JOIN tarea_trabajos ttrab ON ta.id = ttrab.tarea_id
            LEFT JOIN trabajos trab ON ttrab.trabajo_id = trab.id
            WHERE tt.trabajador_id = ?
        ");
        $stmt->bind_param("i", $trabajadorId);
        $stmt->execute();
        $totalGenerado = floatval($stmt->get_result()->fetch_assoc()['total_generado']);
        $stmt->close();

        // Total ya pagado formalmente
        $stmt = $db->prepare("
            SELECT COALESCE(SUM(importe_total), 0) AS total_pagado
            FROM pagos_mensuales_trabajadores
            WHERE trabajador_id = ? AND pagado = 1
        ");
        $stmt->bind_param("i", $trabajadorId);
        $stmt->execute();
        $totalPagado = floatval($stmt->get_result()->fetch_assoc()['total_pagado']);
        $stmt->close();

        $deuda = max(0.0, $totalGenerado - $totalPagado);

        // Meses formales pendientes de liquidar (cerrados pero no pagados)
        $stmt = $db->prepare("
            SELECT COUNT(*) AS meses_pendientes
            FROM pagos_mensuales_trabajadores
            WHERE trabajador_id = ? AND pagado = 0
        ");
        $stmt->bind_param("i", $trabajadorId);
        $stmt->execute();
        $mesesPendientes = intval($stmt->get_result()->fetch_assoc()['meses_pendientes']);
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

        // Filtro por año y paginación del historial
        $yearFilter = intval($_GET['year'] ?? date('Y'));
        $page       = max(1, intval($_GET['page'] ?? 1));
        $porPagina  = 15;
        $offset     = ($page - 1) * $porPagina;

        // Años disponibles con tareas realizadas
        $stmt = $db->prepare("
            SELECT DISTINCT YEAR(t.fecha) AS ano
            FROM tarea_trabajadores tt
            JOIN tareas t ON tt.tarea_id = t.id
            WHERE tt.trabajador_id = ? AND t.estado = 'realizada' AND t.fecha IS NOT NULL
            ORDER BY ano DESC
        ");
        $stmt->bind_param("i", $trabajadorId);
        $stmt->execute();
        $anosDisponibles = array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'ano');
        $stmt->close();

        if (!in_array($yearFilter, $anosDisponibles) && !empty($anosDisponibles)) {
            $yearFilter = $anosDisponibles[0];
        }

        // Total de tareas para paginación
        $stmt = $db->prepare("
            SELECT COUNT(DISTINCT t.id) AS total
            FROM tarea_trabajadores tt
            JOIN tareas t ON tt.tarea_id = t.id
            WHERE tt.trabajador_id = ? AND t.estado = 'realizada' AND YEAR(t.fecha) = ?
        ");
        $stmt->bind_param("ii", $trabajadorId, $yearFilter);
        $stmt->execute();
        $totalTareas = intval($stmt->get_result()->fetch_assoc()['total']);
        $stmt->close();

        // Historial paginado y filtrado por año
        $stmt = $db->prepare("
            SELECT t.id, t.fecha, t.titulo, tt.horas_asignadas,
                   GROUP_CONCAT(DISTINCT p.nombre ORDER BY p.nombre SEPARATOR ', ') AS parcelas
            FROM tarea_trabajadores tt
            JOIN tareas t ON tt.tarea_id = t.id
            LEFT JOIN tarea_parcelas tp ON t.id = tp.tarea_id
            LEFT JOIN parcelas p ON tp.parcela_id = p.id
            WHERE tt.trabajador_id = ? AND t.estado = 'realizada' AND YEAR(t.fecha) = ?
            GROUP BY t.id, tt.horas_asignadas
            ORDER BY t.fecha DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->bind_param("iiii", $trabajadorId, $yearFilter, $porPagina, $offset);
        $stmt->execute();
        $historial = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $totalPaginas = ceil($totalTareas / $porPagina);

        $db->close();

        $this->render('trabajador/index', [
            'trabajador'       => $trabajador,
            'nombreEmpresa'    => $nombreEmpresa,
            'deuda'            => $deuda,
            'mesesPendientes'  => $mesesPendientes,
            'tareasPendientes' => $tareasPendientes,
            'historial'        => $historial,
            'yearFilter'       => $yearFilter,
            'anosDisponibles'  => $anosDisponibles,
            'page'             => $page,
            'totalPaginas'     => $totalPaginas,
            'totalTareas'      => $totalTareas,
            'user'             => ['name' => $trabajador['nombre']],
        ]);
    }
}
