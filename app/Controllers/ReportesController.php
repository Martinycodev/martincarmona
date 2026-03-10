<?php

namespace App\Controllers;

class ReportesController extends BaseController {

    public function index() {
        $userId = intval($_SESSION['user_id'] ?? 0);
        $mes    = intval(date('m'));
        $anio   = intval(date('Y'));
        $db     = \Database::connect();

        // ── KPI: horas totales mes ────────────────────────────────────────
        $stmt = $db->prepare("
            SELECT COALESCE(SUM(tt.horas_asignadas), 0) AS total
            FROM tarea_trabajadores tt
            JOIN tareas t ON tt.tarea_id = t.id
            WHERE t.id_user = ? AND MONTH(t.fecha) = ? AND YEAR(t.fecha) = ?
        ");
        $stmt->bind_param("iii", $userId, $mes, $anio);
        $stmt->execute();
        $total_horas_mes = floatval($stmt->get_result()->fetch_assoc()['total']);
        $stmt->close();

        // ── KPI: tareas completadas mes ───────────────────────────────────
        $stmt = $db->prepare("
            SELECT COUNT(*) AS total FROM tareas
            WHERE id_user = ? AND estado = 'completada'
              AND MONTH(fecha) = ? AND YEAR(fecha) = ?
        ");
        $stmt->bind_param("iii", $userId, $mes, $anio);
        $stmt->execute();
        $tareas_completadas = intval($stmt->get_result()->fetch_assoc()['total']);
        $stmt->close();

        // ── KPI: trabajadores activos este mes ────────────────────────────
        $stmt = $db->prepare("
            SELECT COUNT(DISTINCT tt.trabajador_id) AS total
            FROM tarea_trabajadores tt
            JOIN tareas t ON tt.tarea_id = t.id
            WHERE t.id_user = ? AND MONTH(t.fecha) = ? AND YEAR(t.fecha) = ?
        ");
        $stmt->bind_param("iii", $userId, $mes, $anio);
        $stmt->execute();
        $trabajadores_activos = intval($stmt->get_result()->fetch_assoc()['total']);
        $stmt->close();

        // ── KPI: parcelas trabajadas este mes ─────────────────────────────
        $stmt = $db->prepare("
            SELECT COUNT(DISTINCT tp.parcela_id) AS total
            FROM tarea_parcelas tp
            JOIN tareas t ON tp.tarea_id = t.id
            WHERE t.id_user = ? AND MONTH(t.fecha) = ? AND YEAR(t.fecha) = ?
        ");
        $stmt->bind_param("iii", $userId, $mes, $anio);
        $stmt->execute();
        $parcelas_trabajadas = intval($stmt->get_result()->fetch_assoc()['total']);
        $stmt->close();

        // ── KPI: eficiencia (% tareas con trabajo asignado) ───────────────
        $stmt = $db->prepare("
            SELECT
                COUNT(DISTINCT t.id)       AS total_tareas,
                COUNT(DISTINCT twj.tarea_id) AS tareas_con_trabajo
            FROM tareas t
            LEFT JOIN tarea_trabajos twj ON twj.tarea_id = t.id
            WHERE t.id_user = ? AND MONTH(t.fecha) = ? AND YEAR(t.fecha) = ?
        ");
        $stmt->bind_param("iii", $userId, $mes, $anio);
        $stmt->execute();
        $efRow = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $eficiencia_promedio = ($efRow['total_tareas'] > 0)
            ? round($efRow['tareas_con_trabajo'] / $efRow['total_tareas'] * 100, 1)
            : 0;

        // ── KPI: costo total mes (horas × precio de tarea_trabajos) ───────
        $stmt = $db->prepare("
            SELECT COALESCE(SUM(twj.horas_trabajo * twj.precio_hora), 0) AS total
            FROM tarea_trabajos twj
            JOIN tareas t ON twj.tarea_id = t.id
            WHERE t.id_user = ? AND MONTH(t.fecha) = ? AND YEAR(t.fecha) = ?
        ");
        $stmt->bind_param("iii", $userId, $mes, $anio);
        $stmt->execute();
        $costo_total_mes = floatval($stmt->get_result()->fetch_assoc()['total']);
        $stmt->close();

        $kpis = [
            'total_horas_mes'     => round($total_horas_mes, 1),
            'tareas_completadas'  => $tareas_completadas,
            'trabajadores_activos'=> $trabajadores_activos,
            'parcelas_trabajadas' => $parcelas_trabajadas,
            'eficiencia_promedio' => $eficiencia_promedio,
            'costo_total_mes'     => round($costo_total_mes, 2),
        ];

        // ── Productividad semanal ──────────────────────────────────────────
        $stmt = $db->prepare("
            SELECT
                CASE
                    WHEN DAY(t.fecha) <= 7  THEN 'Sem 1'
                    WHEN DAY(t.fecha) <= 14 THEN 'Sem 2'
                    WHEN DAY(t.fecha) <= 21 THEN 'Sem 3'
                    ELSE 'Sem 4'
                END AS semana,
                COALESCE(SUM(tt.horas_asignadas), 0) AS horas,
                COUNT(DISTINCT t.id) AS tareas
            FROM tareas t
            LEFT JOIN tarea_trabajadores tt ON tt.tarea_id = t.id
            WHERE t.id_user = ? AND MONTH(t.fecha) = ? AND YEAR(t.fecha) = ?
            GROUP BY semana
            ORDER BY MIN(DAY(t.fecha))
        ");
        $stmt->bind_param("iii", $userId, $mes, $anio);
        $stmt->execute();
        $semMap = [];
        foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $r) {
            $semMap[$r['semana']] = $r;
        }
        $stmt->close();
        $productividad_semanal = [];
        foreach (['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4'] as $s) {
            $productividad_semanal[] = [
                'semana' => $s,
                'horas'  => isset($semMap[$s]) ? round($semMap[$s]['horas'], 1) : 0,
                'tareas' => isset($semMap[$s]) ? intval($semMap[$s]['tareas']) : 0,
            ];
        }

        // ── Top 5 trabajadores ────────────────────────────────────────────
        $stmt = $db->prepare("
            SELECT
                CONCAT(tr.nombre, IF(tr.apellidos <> '' AND tr.apellidos IS NOT NULL,
                       CONCAT(' ', tr.apellidos), '')) AS nombre,
                COALESCE(SUM(tt.horas_asignadas), 0) AS horas,
                COUNT(DISTINCT tt.tarea_id) AS tareas
            FROM tarea_trabajadores tt
            JOIN trabajadores tr ON tt.trabajador_id = tr.id
            JOIN tareas t ON tt.tarea_id = t.id
            WHERE t.id_user = ? AND MONTH(t.fecha) = ? AND YEAR(t.fecha) = ?
            GROUP BY tr.id, tr.nombre, tr.apellidos
            ORDER BY horas DESC
            LIMIT 5
        ");
        $stmt->bind_param("iii", $userId, $mes, $anio);
        $stmt->execute();
        $rawTop = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $maxHoras = floatval($rawTop[0]['horas'] ?? 1) ?: 1;
        $top_trabajadores = array_map(function ($r) use ($maxHoras) {
            return [
                'nombre'     => htmlspecialchars(trim($r['nombre'])),
                'horas'      => round($r['horas'], 1),
                'tareas'     => intval($r['tareas']),
                'eficiencia' => round($r['horas'] / $maxHoras * 100, 1),
            ];
        }, $rawTop);

        // ── Top 5 parcelas ────────────────────────────────────────────────
        $stmt = $db->prepare("
            SELECT
                p.nombre,
                COALESCE(p.olivos, 0) AS olivos,
                COALESCE(SUM(tt.horas_asignadas), 0) AS horas,
                COUNT(DISTINCT tp.tarea_id) AS tareas
            FROM tarea_parcelas tp
            JOIN parcelas p ON tp.parcela_id = p.id
            JOIN tareas t ON tp.tarea_id = t.id
            LEFT JOIN tarea_trabajadores tt ON tt.tarea_id = t.id
            WHERE t.id_user = ? AND MONTH(t.fecha) = ? AND YEAR(t.fecha) = ?
            GROUP BY p.id, p.nombre, p.olivos
            ORDER BY tareas DESC, horas DESC
            LIMIT 5
        ");
        $stmt->bind_param("iii", $userId, $mes, $anio);
        $stmt->execute();
        $top_parcelas = array_map(function ($r) {
            return [
                'nombre' => htmlspecialchars($r['nombre']),
                'horas'  => round($r['horas'], 1),
                'tareas' => intval($r['tareas']),
                'olivos' => intval($r['olivos']),
                'roi'    => 0,
            ];
        }, $stmt->get_result()->fetch_all(MYSQLI_ASSOC));
        $stmt->close();

        // ── Trabajos más frecuentes (histórico) ──────────────────────────
        $stmt = $db->prepare("
            SELECT
                w.nombre AS tipo,
                COUNT(*) AS cantidad,
                COALESCE(SUM(twj.horas_trabajo), 0) AS horas_total
            FROM tarea_trabajos twj
            JOIN trabajos w ON twj.trabajo_id = w.id
            JOIN tareas t ON twj.tarea_id = t.id
            WHERE t.id_user = ?
            GROUP BY w.id, w.nombre
            ORDER BY cantidad DESC
            LIMIT 6
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $trabajos_frecuentes = array_map(function ($r) {
            $cant = intval($r['cantidad']);
            return [
                'tipo'       => htmlspecialchars($r['tipo']),
                'cantidad'   => $cant,
                'horas_total'=> round($r['horas_total'], 1),
                'promedio'   => $cant > 0 ? round($r['horas_total'] / $cant, 1) : 0,
            ];
        }, $stmt->get_result()->fetch_all(MYSQLI_ASSOC));
        $stmt->close();

        // ── Distribución de costos por categoría (mes actual) ────────────
        $stmt = $db->prepare("
            SELECT categoria, SUM(importe) AS monto
            FROM movimientos
            WHERE tipo = 'gasto'
              AND MONTH(fecha) = ? AND YEAR(fecha) = ?
            GROUP BY categoria
            ORDER BY monto DESC
        ");
        $stmt->bind_param("ii", $mes, $anio);
        $stmt->execute();
        $rawCostos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $totalGastos = array_sum(array_column($rawCostos, 'monto')) ?: 1;
        $labels = \App\Models\Movimiento::LABELS_CATEGORIA;
        $costos_categorias = array_map(function ($r) use ($totalGastos, $labels) {
            return [
                'categoria'  => $labels[$r['categoria']] ?? ucfirst($r['categoria']),
                'monto'      => round($r['monto'], 2),
                'porcentaje' => round($r['monto'] / $totalGastos * 100, 1),
            ];
        }, $rawCostos);

        // ── Alertas dinámicas ─────────────────────────────────────────────
        $alertas = [];

        // Parcelas sin actividad hace más de 30 días
        $stmt = $db->prepare("
            SELECT p.nombre, MAX(t.fecha) AS ultima_fecha
            FROM parcelas p
            LEFT JOIN tarea_parcelas tp ON tp.parcela_id = p.id
            LEFT JOIN tareas t ON tp.tarea_id = t.id AND t.id_user = ?
            WHERE p.id_user = ?
            GROUP BY p.id, p.nombre
            HAVING ultima_fecha IS NULL
               OR ultima_fecha < DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            LIMIT 3
        ");
        $stmt->bind_param("ii", $userId, $userId);
        $stmt->execute();
        foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $row) {
            $nombre = htmlspecialchars($row['nombre']);
            if ($row['ultima_fecha']) {
                $dias = intval((strtotime('today') - strtotime($row['ultima_fecha'])) / 86400);
                $alertas[] = ['tipo' => 'info', 'mensaje' => "Parcela \"{$nombre}\" sin actividad hace {$dias} días"];
            } else {
                $alertas[] = ['tipo' => 'info', 'mensaje' => "Parcela \"{$nombre}\" nunca ha sido trabajada"];
            }
        }
        $stmt->close();

        // Pagos de trabajadores pendientes
        $stmt = $db->prepare("
            SELECT COUNT(*) AS total FROM pagos_mensuales_trabajadores
            WHERE id_user = ? AND pagado = 0
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $pagosPendientes = intval($stmt->get_result()->fetch_assoc()['total']);
        $stmt->close();
        if ($pagosPendientes > 0) {
            $s = $pagosPendientes > 1 ? 's' : '';
            $alertas[] = ['tipo' => 'warning', 'mensaje' => "{$pagosPendientes} pago{$s} de trabajadores pendiente{$s} de abonar"];
        }

        // Fitosanitarios sin stock
        $stmt = $db->prepare("SELECT COUNT(*) AS total FROM fitosanitarios_inventario WHERE id_user = ? AND cantidad <= 0");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $sinStock = intval($stmt->get_result()->fetch_assoc()['total']);
        $stmt->close();
        if ($sinStock > 0) {
            $s = $sinStock > 1 ? 's' : '';
            $alertas[] = ['tipo' => 'warning', 'mensaje' => "{$sinStock} producto{$s} fitosanitario{$s} sin stock"];
        }

        // Resumen positivo
        if ($tareas_completadas > 0) {
            $alertas[] = ['tipo' => 'success', 'mensaje' => "{$tareas_completadas} tareas completadas este mes"];
        }
        if (empty($alertas)) {
            $alertas[] = ['tipo' => 'success', 'mensaje' => 'Todo en orden. No hay alertas pendientes.'];
        }

        $db->close();

        $this->render('reportes/index', [
            'title'                => 'Reportes - MartinCarmona.com',
            'kpis'                 => $kpis,
            'productividad_semanal'=> $productividad_semanal,
            'top_trabajadores'     => $top_trabajadores,
            'top_parcelas'         => $top_parcelas,
            'trabajos_frecuentes'  => $trabajos_frecuentes,
            'costos_categorias'    => $costos_categorias,
            'alertas'              => $alertas,
        ]);
    }

    public function personal() {
        $this->render('reportes/personal');
    }

    public function parcelas() {
        $this->render('reportes/parcelas');
    }

    public function trabajos() {
        $this->render('reportes/trabajos');
    }

    public function economia() {
        $this->render('reportes/economia');
    }

    public function recursos() {
        $this->render('reportes/recursos');
    }

    public function proveedores() {
        $this->render('reportes/proveedores');
    }
}
