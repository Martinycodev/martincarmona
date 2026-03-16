<?php

namespace App\Controllers;

/**
 * ReportesController — Panel de reportes y estadísticas.
 *
 * Incluye:
 *  - Dashboard principal con KPIs, trends reales y selector de periodo
 *  - Sub-páginas: personal, parcelas, trabajos, economía, recursos, proveedores
 *  - Datos históricos del seed de Notion (tareas estado='realizada')
 */
class ReportesController extends BaseController {

    // =====================================================================
    // HELPERS PRIVADOS
    // =====================================================================

    /**
     * Obtiene mes/año desde GET params. Por defecto: mes y año actuales.
     */
    private function getPeriodo(): array {
        $mes  = intval($_GET['mes']  ?? date('m'));
        $anio = intval($_GET['anio'] ?? date('Y'));
        if ($mes < 1 || $mes > 12) $mes = intval(date('m'));
        if ($anio < 2020 || $anio > 2030) $anio = intval(date('Y'));
        return ['mes' => $mes, 'anio' => $anio];
    }

    /**
     * Calcula el periodo anterior (mes-1, o dic del año anterior).
     */
    private function getPeriodoAnterior(int $mes, int $anio): array {
        return ($mes === 1)
            ? ['mes' => 12, 'anio' => $anio - 1]
            : ['mes' => $mes - 1, 'anio' => $anio];
    }

    /**
     * Calcula el % de cambio entre dos valores y devuelve clase CSS + texto.
     * $invertir = true invierte la lógica (menos es mejor, ej: costos).
     */
    private function calcularTrend(float $actual, float $anterior, bool $invertir = false): array {
        if ($anterior == 0 && $actual == 0) {
            return ['clase' => 'neutral', 'texto' => 'Sin datos previos'];
        }
        if ($anterior == 0) {
            return ['clase' => 'positive', 'texto' => 'Nuevo'];
        }
        $cambio = round(($actual - $anterior) / $anterior * 100, 1);
        if ($cambio == 0) {
            return ['clase' => 'neutral', 'texto' => 'Sin cambios'];
        }
        $positivo = $cambio > 0;
        if ($invertir) $positivo = !$positivo;
        $signo = $cambio > 0 ? '+' : '';
        return [
            'clase' => $positivo ? 'positive' : 'negative',
            'texto' => "{$signo}{$cambio}% vs mes anterior",
        ];
    }

    /**
     * Calcula los 6 KPIs principales para un mes/año dado.
     * Optimizado: 3 queries en vez de 6 (agrupa por tipo de JOIN).
     * Incluye tareas con estado 'completada' y 'realizada' (seed histórico).
     */
    private function calcularKPIs($db, int $userId, int $mes, int $anio): array {
        // Query 1: tareas + trabajadores + parcelas + horas (un solo scan de tareas)
        $stmt = $db->prepare("
            SELECT
                COUNT(DISTINCT t.id) AS total_tareas,
                COUNT(DISTINCT CASE WHEN t.estado IN ('completada','realizada') THEN t.id END) AS completadas,
                COUNT(DISTINCT tt.trabajador_id) AS trabajadores,
                COUNT(DISTINCT tp.parcela_id) AS parcelas,
                COALESCE(SUM(tt.horas_asignadas), 0) AS horas
            FROM tareas t
            LEFT JOIN tarea_trabajadores tt ON tt.tarea_id = t.id
            LEFT JOIN tarea_parcelas tp ON tp.tarea_id = t.id
            WHERE t.id_user = ? AND MONTH(t.fecha) = ? AND YEAR(t.fecha) = ?
        ");
        $stmt->bind_param("iii", $userId, $mes, $anio);
        $stmt->execute();
        $r1 = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // Query 2: eficiencia + costo (scan de tarea_trabajos)
        $stmt = $db->prepare("
            SELECT
                COUNT(DISTINCT twj.tarea_id) AS tareas_con_trabajo,
                COALESCE(SUM(twj.horas_trabajo * twj.precio_hora), 0) AS costo
            FROM tarea_trabajos twj
            JOIN tareas t ON twj.tarea_id = t.id
            WHERE t.id_user = ? AND MONTH(t.fecha) = ? AND YEAR(t.fecha) = ?
        ");
        $stmt->bind_param("iii", $userId, $mes, $anio);
        $stmt->execute();
        $r2 = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $totalTareas = intval($r1['total_tareas']);
        $eficiencia = ($totalTareas > 0)
            ? round(intval($r2['tareas_con_trabajo']) / $totalTareas * 100, 1)
            : 0;

        return [
            'total_horas_mes'      => round(floatval($r1['horas']), 1),
            'tareas_completadas'   => intval($r1['completadas']),
            'trabajadores_activos' => intval($r1['trabajadores']),
            'parcelas_trabajadas'  => intval($r1['parcelas']),
            'eficiencia_promedio'  => $eficiencia,
            'costo_total_mes'      => round(floatval($r2['costo']), 2),
        ];
    }

    /**
     * Devuelve los años con datos de tareas, para el selector de periodo.
     */
    private function getAniosDisponibles($db, int $userId): array {
        $stmt = $db->prepare("
            SELECT DISTINCT YEAR(fecha) AS anio FROM tareas
            WHERE id_user = ? AND fecha IS NOT NULL
            ORDER BY anio DESC
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $anios = array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'anio');
        $stmt->close();
        $currentYear = intval(date('Y'));
        if (!in_array($currentYear, $anios)) {
            $anios[] = $currentYear;
            rsort($anios);
        }
        return $anios;
    }

    /**
     * Nombre del mes en español.
     */
    private function nombreMes(int $mes): string {
        $meses = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio',
                   'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
        return $meses[$mes] ?? '';
    }

    /**
     * Abreviatura del mes en español (3 letras).
     */
    private function mesCort(int $mes): string {
        $m = ['','Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
        return $m[$mes] ?? '';
    }

    // =====================================================================
    // PÁGINA PRINCIPAL — Dashboard de reportes
    // =====================================================================

    public function index() {
        $userId  = intval($_SESSION['user_id'] ?? 0);
        $periodo = $this->getPeriodo();
        $mes     = $periodo['mes'];
        $anio    = $periodo['anio'];
        $db      = \Database::connect();

        // ── KPIs del periodo seleccionado ────────────────────────────────
        $kpis = $this->calcularKPIs($db, $userId, $mes, $anio);

        // ── KPIs del periodo anterior → trends reales ────────────────────
        $ant     = $this->getPeriodoAnterior($mes, $anio);
        $kpisAnt = $this->calcularKPIs($db, $userId, $ant['mes'], $ant['anio']);

        $trends = [
            'horas'        => $this->calcularTrend($kpis['total_horas_mes'],      $kpisAnt['total_horas_mes']),
            'tareas'       => $this->calcularTrend($kpis['tareas_completadas'],    $kpisAnt['tareas_completadas']),
            'trabajadores' => $this->calcularTrend($kpis['trabajadores_activos'],  $kpisAnt['trabajadores_activos']),
            'parcelas'     => $this->calcularTrend($kpis['parcelas_trabajadas'],   $kpisAnt['parcelas_trabajadas']),
            'eficiencia'   => $this->calcularTrend($kpis['eficiencia_promedio'],   $kpisAnt['eficiencia_promedio']),
            'costo'        => $this->calcularTrend($kpis['costo_total_mes'],       $kpisAnt['costo_total_mes'], true),
        ];

        // ── Conteos reales para nav-cards ────────────────────────────────
        $stmt = $db->prepare("SELECT COUNT(*) AS t FROM vehiculos WHERE id_user = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $nVehiculos = intval($stmt->get_result()->fetch_assoc()['t']);
        $stmt->close();

        $stmt = $db->prepare("SELECT COUNT(*) AS t FROM herramientas WHERE id_user = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $nHerramientas = intval($stmt->get_result()->fetch_assoc()['t']);
        $stmt->close();

        $stmt = $db->prepare("SELECT COUNT(*) AS t FROM proveedores WHERE id_user = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $nProveedores = intval($stmt->get_result()->fetch_assoc()['t']);
        $stmt->close();

        // ── Años disponibles para el selector ────────────────────────────
        $aniosDisponibles = $this->getAniosDisponibles($db, $userId);

        $db->close();

        $this->render('reportes/index', [
            'title'             => 'Reportes - MartinCarmona.com',
            'kpis'              => $kpis,
            'trends'            => $trends,
            'mes'               => $mes,
            'anio'              => $anio,
            'nombre_mes'        => $this->nombreMes($mes),
            'anios_disponibles' => $aniosDisponibles,
            'total_recursos'    => $nVehiculos + $nHerramientas,
            'total_proveedores' => $nProveedores,
        ]);
    }

    // =====================================================================
    // SUB-PÁGINA: Gestión de Personal
    // =====================================================================

    public function personal() {
        $userId = intval($_SESSION['user_id'] ?? 0);
        $anio   = intval($_GET['anio'] ?? date('Y'));
        $db     = \Database::connect();
        $aniosDisponibles = $this->getAniosDisponibles($db, $userId);

        // Todos los trabajadores con estadísticas del año seleccionado
        $stmt = $db->prepare("
            SELECT
                tr.id, tr.nombre, tr.apellidos, tr.estado,
                COUNT(DISTINCT CASE WHEN YEAR(t.fecha) = ? THEN t.id END) AS tareas_anio,
                COALESCE(SUM(CASE WHEN YEAR(t.fecha) = ? THEN tt.horas_asignadas ELSE 0 END), 0) AS horas_anio,
                COUNT(DISTINCT CASE WHEN YEAR(t.fecha) = ? THEN MONTH(t.fecha) END) AS meses_activo,
                COUNT(DISTINCT tp.parcela_id) AS parcelas_distintas,
                MAX(t.fecha) AS ultima_actividad
            FROM trabajadores tr
            LEFT JOIN tarea_trabajadores tt ON tt.trabajador_id = tr.id
            LEFT JOIN tareas t ON tt.tarea_id = t.id
            LEFT JOIN tarea_parcelas tp ON tp.tarea_id = t.id
            WHERE tr.id_user = ?
            GROUP BY tr.id, tr.nombre, tr.apellidos, tr.estado
            ORDER BY tareas_anio DESC
        ");
        $stmt->bind_param("iiii", $anio, $anio, $anio, $userId);
        $stmt->execute();
        $trabajadores = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Actividad mensual del año (para gráfico)
        $stmt = $db->prepare("
            SELECT
                MONTH(t.fecha) AS mes,
                COUNT(DISTINCT t.id) AS tareas,
                COALESCE(SUM(tt.horas_asignadas), 0) AS horas,
                COUNT(DISTINCT tt.trabajador_id) AS trabajadores
            FROM tareas t
            LEFT JOIN tarea_trabajadores tt ON tt.tarea_id = t.id
            WHERE t.id_user = ? AND YEAR(t.fecha) = ? AND t.fecha IS NOT NULL
            GROUP BY MONTH(t.fecha)
            ORDER BY mes
        ");
        $stmt->bind_param("ii", $userId, $anio);
        $stmt->execute();
        $actMap = [];
        foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $r) {
            $actMap[intval($r['mes'])] = $r;
        }
        $stmt->close();

        $chartLabels = [];
        $chartTareas = [];
        $chartHoras  = [];
        for ($m = 1; $m <= 12; $m++) {
            $chartLabels[] = $this->mesCort($m);
            $chartTareas[] = intval($actMap[$m]['tareas'] ?? 0);
            $chartHoras[]  = round(floatval($actMap[$m]['horas'] ?? 0), 1);
        }

        // Resumen
        $totalTareas = array_sum($chartTareas);
        $totalHoras  = array_sum($chartHoras);
        $activos     = count(array_filter($trabajadores, fn($t) => $t['tareas_anio'] > 0));

        $db->close();

        $this->render('reportes/personal', [
            'title'             => 'Personal - Reportes',
            'anio'              => $anio,
            'anios_disponibles' => $aniosDisponibles,
            'trabajadores'      => $trabajadores,
            'chart_labels'      => $chartLabels,
            'chart_tareas'      => $chartTareas,
            'chart_horas'       => $chartHoras,
            'total_tareas'      => $totalTareas,
            'total_horas'       => $totalHoras,
            'activos'           => $activos,
        ]);
    }

    // =====================================================================
    // SUB-PÁGINA: Análisis de Parcelas
    // =====================================================================

    public function parcelas() {
        $userId = intval($_SESSION['user_id'] ?? 0);
        $anio   = intval($_GET['anio'] ?? date('Y'));
        $db     = \Database::connect();
        $aniosDisponibles = $this->getAniosDisponibles($db, $userId);

        // Todas las parcelas con stats del año + riego total
        $stmt = $db->prepare("
            SELECT
                p.id, p.nombre, p.olivos, p.ubicacion,
                COUNT(DISTINCT CASE WHEN YEAR(t.fecha) = ? THEN t.id END) AS tareas_anio,
                COALESCE(SUM(CASE WHEN YEAR(t.fecha) = ? THEN tt.horas_asignadas ELSE 0 END), 0) AS horas_anio,
                MAX(t.fecha) AS ultima_actividad
            FROM parcelas p
            LEFT JOIN tarea_parcelas tp ON tp.parcela_id = p.id
            LEFT JOIN tareas t ON tp.tarea_id = t.id
            LEFT JOIN tarea_trabajadores tt ON tt.tarea_id = t.id
            WHERE p.id_user = ?
            GROUP BY p.id, p.nombre, p.olivos, p.ubicacion
            ORDER BY tareas_anio DESC
        ");
        $stmt->bind_param("iii", $anio, $anio, $userId);
        $stmt->execute();
        $parcelas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Riego por parcela (total m3 del año)
        $stmt = $db->prepare("
            SELECT parcela_id, SUM(total_m3) AS m3_total, COUNT(*) AS riegos
            FROM riegos
            WHERE id_user = ? AND YEAR(fecha_ini) = ? AND parcela_id IS NOT NULL
            GROUP BY parcela_id
        ");
        $stmt->bind_param("ii", $userId, $anio);
        $stmt->execute();
        $riegoMap = [];
        foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $r) {
            $riegoMap[$r['parcela_id']] = $r;
        }
        $stmt->close();

        // Añadir datos de riego a cada parcela
        foreach ($parcelas as &$p) {
            $rid = $riegoMap[$p['id']] ?? null;
            $p['m3_riego']     = $rid ? round($rid['m3_total'], 1) : 0;
            $p['num_riegos']   = $rid ? intval($rid['riegos']) : 0;
        }
        unset($p);

        // Actividad mensual por parcela (top 5 para gráfico)
        $stmt = $db->prepare("
            SELECT
                p.nombre,
                MONTH(t.fecha) AS mes,
                COUNT(DISTINCT t.id) AS tareas
            FROM tarea_parcelas tp
            JOIN parcelas p ON tp.parcela_id = p.id
            JOIN tareas t ON tp.tarea_id = t.id
            WHERE t.id_user = ? AND YEAR(t.fecha) = ?
            GROUP BY p.id, p.nombre, MONTH(t.fecha)
            ORDER BY p.nombre, mes
        ");
        $stmt->bind_param("ii", $userId, $anio);
        $stmt->execute();
        $parcelasMes = [];
        foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $r) {
            $nombre = htmlspecialchars($r['nombre']);
            if (!isset($parcelasMes[$nombre])) {
                $parcelasMes[$nombre] = array_fill(1, 12, 0);
            }
            $parcelasMes[$nombre][intval($r['mes'])] = intval($r['tareas']);
        }
        $stmt->close();

        // Solo top 6 parcelas para el gráfico
        $parcelasMes = array_slice($parcelasMes, 0, 6, true);

        // Resumen
        $totalParcelas = count($parcelas);
        $parcelasActivas = count(array_filter($parcelas, fn($p) => $p['tareas_anio'] > 0));
        $totalM3 = array_sum(array_column($parcelas, 'm3_riego'));

        $chartLabels = [];
        for ($m = 1; $m <= 12; $m++) $chartLabels[] = $this->mesCort($m);

        $db->close();

        $this->render('reportes/parcelas', [
            'title'             => 'Parcelas - Reportes',
            'anio'              => $anio,
            'anios_disponibles' => $aniosDisponibles,
            'parcelas'          => $parcelas,
            'parcelas_mes'      => $parcelasMes,
            'chart_labels'      => $chartLabels,
            'total_parcelas'    => $totalParcelas,
            'parcelas_activas'  => $parcelasActivas,
            'total_m3'          => round($totalM3, 1),
        ]);
    }

    // =====================================================================
    // SUB-PÁGINA: Análisis de Trabajos y Estacionalidad
    // =====================================================================

    public function trabajos() {
        $userId = intval($_SESSION['user_id'] ?? 0);
        $db     = \Database::connect();
        $aniosDisponibles = $this->getAniosDisponibles($db, $userId);

        // Estacionalidad: tipo de trabajo × mes (todos los años acumulados)
        $stmt = $db->prepare("
            SELECT
                w.nombre AS trabajo,
                MONTH(t.fecha) AS mes,
                COUNT(*) AS cantidad
            FROM tarea_trabajos twj
            JOIN trabajos w ON twj.trabajo_id = w.id
            JOIN tareas t ON twj.tarea_id = t.id
            WHERE t.id_user = ? AND t.fecha IS NOT NULL
            GROUP BY w.id, w.nombre, MONTH(t.fecha)
            ORDER BY w.nombre, mes
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $estacionalidad = [];
        foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $r) {
            $nombre = htmlspecialchars($r['trabajo']);
            if (!isset($estacionalidad[$nombre])) {
                $estacionalidad[$nombre] = array_fill(1, 12, 0);
            }
            $estacionalidad[$nombre][intval($r['mes'])] = intval($r['cantidad']);
        }
        $stmt->close();

        // Frecuencia histórica total
        $stmt = $db->prepare("
            SELECT
                w.nombre,
                COUNT(*) AS total,
                COALESCE(SUM(twj.horas_trabajo), 0) AS horas,
                COUNT(DISTINCT YEAR(t.fecha)) AS anios_con_datos,
                MIN(t.fecha) AS primera_vez,
                MAX(t.fecha) AS ultima_vez
            FROM tarea_trabajos twj
            JOIN trabajos w ON twj.trabajo_id = w.id
            JOIN tareas t ON twj.tarea_id = t.id
            WHERE t.id_user = ?
            GROUP BY w.id, w.nombre
            ORDER BY total DESC
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $frecuencia = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Resumen
        $totalRegistros = array_sum(array_column($frecuencia, 'total'));
        $tiposDistintos = count($frecuencia);

        $chartLabels = [];
        for ($m = 1; $m <= 12; $m++) $chartLabels[] = $this->mesCort($m);

        $db->close();

        $this->render('reportes/trabajos', [
            'title'             => 'Trabajos - Reportes',
            'anios_disponibles' => $aniosDisponibles,
            'estacionalidad'    => $estacionalidad,
            'frecuencia'        => $frecuencia,
            'chart_labels'      => $chartLabels,
            'total_registros'   => $totalRegistros,
            'tipos_distintos'   => $tiposDistintos,
        ]);
    }

    // =====================================================================
    // SUB-PÁGINA: Análisis Económico
    // =====================================================================

    public function economia() {
        $anio = intval($_GET['anio'] ?? date('Y'));
        $userId = intval($_SESSION['user_id'] ?? 0);
        $db   = \Database::connect();
        $aniosDisponibles = $this->getAniosDisponibles($db, $userId);

        // Evolución mensual: ingresos vs gastos del año seleccionado
        $stmt = $db->prepare("
            SELECT
                MONTH(fecha) AS mes,
                SUM(CASE WHEN tipo = 'ingreso' THEN importe ELSE 0 END) AS ingresos,
                SUM(CASE WHEN tipo = 'gasto'   THEN importe ELSE 0 END) AS gastos
            FROM movimientos
            WHERE YEAR(fecha) = ?
            GROUP BY MONTH(fecha)
            ORDER BY mes
        ");
        $stmt->bind_param("i", $anio);
        $stmt->execute();
        $evMap = [];
        foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $r) {
            $evMap[intval($r['mes'])] = $r;
        }
        $stmt->close();

        $chartLabels   = [];
        $chartIngresos = [];
        $chartGastos   = [];
        $chartBalance  = [];
        $balanceAcum   = 0;
        for ($m = 1; $m <= 12; $m++) {
            $chartLabels[]   = $this->mesCort($m);
            $ing = round(floatval($evMap[$m]['ingresos'] ?? 0), 2);
            $gas = round(floatval($evMap[$m]['gastos']   ?? 0), 2);
            $chartIngresos[] = $ing;
            $chartGastos[]   = $gas;
            $balanceAcum    += ($ing - $gas);
            $chartBalance[]  = round($balanceAcum, 2);
        }

        $totalIngresos = array_sum($chartIngresos);
        $totalGastos   = array_sum($chartGastos);
        $balanceAnual  = round($totalIngresos - $totalGastos, 2);

        // Desglose por categoría del año
        $labels = \App\Models\Movimiento::LABELS_CATEGORIA;

        $stmt = $db->prepare("
            SELECT categoria, tipo, SUM(importe) AS total
            FROM movimientos
            WHERE YEAR(fecha) = ?
            GROUP BY categoria, tipo
            ORDER BY total DESC
        ");
        $stmt->bind_param("i", $anio);
        $stmt->execute();
        $categoriasGasto   = [];
        $categoriasIngreso = [];
        foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $r) {
            $item = [
                'categoria' => $labels[$r['categoria']] ?? ucfirst($r['categoria']),
                'total'     => round($r['total'], 2),
            ];
            if ($r['tipo'] === 'gasto') {
                $categoriasGasto[] = $item;
            } else {
                $categoriasIngreso[] = $item;
            }
        }
        $stmt->close();

        // Top 5 movimientos más grandes del año
        $stmt = $db->prepare("
            SELECT fecha, concepto, tipo, categoria, importe, cuenta
            FROM movimientos
            WHERE YEAR(fecha) = ?
            ORDER BY importe DESC
            LIMIT 10
        ");
        $stmt->bind_param("i", $anio);
        $stmt->execute();
        $topMovimientos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $db->close();

        $this->render('reportes/economia', [
            'title'              => 'Economía - Reportes',
            'anio'               => $anio,
            'anios_disponibles'  => $aniosDisponibles,
            'chart_labels'       => $chartLabels,
            'chart_ingresos'     => $chartIngresos,
            'chart_gastos'       => $chartGastos,
            'chart_balance'      => $chartBalance,
            'total_ingresos'     => $totalIngresos,
            'total_gastos'       => $totalGastos,
            'balance_anual'      => $balanceAnual,
            'categorias_gasto'   => $categoriasGasto,
            'categorias_ingreso' => $categoriasIngreso,
            'top_movimientos'    => $topMovimientos,
            'labels_categoria'   => $labels,
        ]);
    }

    // =====================================================================
    // SUB-PÁGINA: Gestión de Recursos (Vehículos + Herramientas)
    // =====================================================================

    public function recursos() {
        $userId = intval($_SESSION['user_id'] ?? 0);
        $db     = \Database::connect();

        // Vehículos con alertas
        $stmt = $db->prepare("SELECT * FROM vehiculos WHERE id_user = ? ORDER BY nombre");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $vehiculos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Añadir alertas a vehículos
        $hoy = new \DateTime();
        foreach ($vehiculos as &$v) {
            $v['alertas'] = [];
            // Alerta ITV
            if (!empty($v['pasa_itv'])) {
                $itv = new \DateTime($v['pasa_itv']);
                $diff = $hoy->diff($itv);
                $dias = intval($diff->format('%r%a'));
                if ($dias < 0) {
                    $v['alertas'][] = ['tipo' => 'error', 'msg' => 'ITV caducada hace ' . abs($dias) . ' días'];
                } elseif ($dias <= 30) {
                    $v['alertas'][] = ['tipo' => 'warning', 'msg' => "ITV caduca en {$dias} días"];
                } elseif ($dias <= 90) {
                    $v['alertas'][] = ['tipo' => 'info', 'msg' => "ITV caduca en {$dias} días"];
                }
            }
        }
        unset($v);

        // Herramientas
        $stmt = $db->prepare("SELECT * FROM herramientas WHERE id_user = ? ORDER BY nombre");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $herramientas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Resumen
        $alertasVehiculos = 0;
        foreach ($vehiculos as $v) {
            $alertasVehiculos += count($v['alertas']);
        }

        $db->close();

        $this->render('reportes/recursos', [
            'title'             => 'Recursos - Reportes',
            'vehiculos'         => $vehiculos,
            'herramientas'      => $herramientas,
            'total_vehiculos'   => count($vehiculos),
            'total_herramientas'=> count($herramientas),
            'alertas_vehiculos' => $alertasVehiculos,
        ]);
    }

    // =====================================================================
    // SUB-PÁGINA: Análisis de Proveedores
    // =====================================================================

    public function proveedores() {
        $userId = intval($_SESSION['user_id'] ?? 0);
        $anio   = intval($_GET['anio'] ?? date('Y'));
        $db     = \Database::connect();
        $aniosDisponibles = $this->getAniosDisponibles($db, $userId);

        // Proveedores con gasto total del año (desde movimientos.proveedor_id)
        $stmt = $db->prepare("
            SELECT
                p.id, p.nombre, p.telefono, p.ubicacion, p.descripcion,
                COALESCE(SUM(CASE WHEN YEAR(m.fecha) = ? THEN m.importe ELSE 0 END), 0) AS gasto_anio,
                COALESCE(SUM(m.importe), 0) AS gasto_total,
                COUNT(CASE WHEN YEAR(m.fecha) = ? THEN m.id END) AS movimientos_anio,
                COUNT(m.id) AS movimientos_total,
                MAX(m.fecha) AS ultima_compra
            FROM proveedores p
            LEFT JOIN movimientos m ON m.proveedor_id = p.id AND m.tipo = 'gasto'
            WHERE p.id_user = ?
            GROUP BY p.id, p.nombre, p.telefono, p.ubicacion, p.descripcion
            ORDER BY gasto_anio DESC
        ");
        $stmt->bind_param("iii", $anio, $anio, $userId);
        $stmt->execute();
        $proveedores = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Resumen
        $totalGastoAnio = array_sum(array_column($proveedores, 'gasto_anio'));
        $proveedoresActivos = count(array_filter($proveedores, fn($p) => $p['movimientos_anio'] > 0));

        $db->close();

        $this->render('reportes/proveedores', [
            'title'               => 'Proveedores - Reportes',
            'anio'                => $anio,
            'anios_disponibles'   => $aniosDisponibles,
            'proveedores'         => $proveedores,
            'total_gasto_anio'    => round($totalGastoAnio, 2),
            'proveedores_activos' => $proveedoresActivos,
        ]);
    }
}
