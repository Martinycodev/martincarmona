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
        $stmt = $db->prepare("SELECT id, nombre, apellidos, id_user FROM propietarios WHERE id = ?");
        $stmt->bind_param("i", $propietarioId);
        $stmt->execute();
        $propietario = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$propietario) { $db->close(); $this->redirect('/'); return; }

        // Nombre de la empresa (usuario propietario del propietario)
        $nombreEmpresa = '';
        if (!empty($propietario['id_user'])) {
            $stmt = $db->prepare("SELECT name FROM usuarios WHERE id = ? AND rol IN ('empresa','admin')");
            $stmt->bind_param("i", $propietario['id_user']);
            $stmt->execute();
            $empresa = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if ($empresa) {
                $nombreEmpresa = $empresa['name'];
            }
        }

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

        // Filtro por año y paginación
        $yearFilter  = intval($_GET['year'] ?? date('Y'));
        $page        = max(1, intval($_GET['page'] ?? 1));
        $porPagina   = 15;
        $offset      = ($page - 1) * $porPagina;

        // Años disponibles para el selector (años con tareas realizadas en sus parcelas)
        $anosDisponibles = [];

        // Tareas realizadas en sus parcelas — paginadas y filtradas por año
        $tareas      = [];
        $totalTareas = 0;

        if (!empty($parcelas)) {
            $parcelaIds   = array_column($parcelas, 'id');
            $placeholders = implode(',', array_fill(0, count($parcelaIds), '?'));
            $types        = str_repeat('i', count($parcelaIds));

            // Años disponibles
            $stmt = $db->prepare("
                SELECT DISTINCT YEAR(t.fecha) AS ano
                FROM tareas t
                JOIN tarea_parcelas tp ON t.id = tp.tarea_id
                WHERE tp.parcela_id IN ($placeholders) AND t.estado = 'realizada' AND t.fecha IS NOT NULL
                ORDER BY ano DESC
            ");
            $stmt->bind_param($types, ...$parcelaIds);
            $stmt->execute();
            $anosDisponibles = array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'ano');
            $stmt->close();

            // Si el año seleccionado no tiene tareas, usar el año actual
            if (!in_array($yearFilter, $anosDisponibles) && !empty($anosDisponibles)) {
                $yearFilter = $anosDisponibles[0];
            }

            // Total de tareas para la paginación
            $stmt = $db->prepare("
                SELECT COUNT(DISTINCT t.id) AS total
                FROM tareas t
                JOIN tarea_parcelas tp ON t.id = tp.tarea_id
                WHERE tp.parcela_id IN ($placeholders) AND t.estado = 'realizada'
                  AND YEAR(t.fecha) = ?
            ");
            $typesCount = $types . 'i';
            $stmt->bind_param($typesCount, ...[...$parcelaIds, $yearFilter]);
            $stmt->execute();
            $totalTareas = intval($stmt->get_result()->fetch_assoc()['total']);
            $stmt->close();

            // Tareas paginadas
            $stmt = $db->prepare("
                SELECT DISTINCT t.id, t.fecha, t.titulo, t.descripcion,
                       GROUP_CONCAT(DISTINCT p.nombre ORDER BY p.nombre SEPARATOR ', ') AS parcelas_nombres
                FROM tareas t
                JOIN tarea_parcelas tp ON t.id = tp.tarea_id
                JOIN parcelas p ON tp.parcela_id = p.id
                WHERE tp.parcela_id IN ($placeholders) AND t.estado = 'realizada'
                  AND YEAR(t.fecha) = ?
                GROUP BY t.id
                ORDER BY t.fecha DESC
                LIMIT ? OFFSET ?
            ");
            $typesPag = $types . 'iii';
            $stmt->bind_param($typesPag, ...[...$parcelaIds, $yearFilter, $porPagina, $offset]);
            $stmt->execute();
            $tareas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        }

        $totalPaginas = ceil($totalTareas / $porPagina);

        $db->close();

        $nombreCompleto = $propietario['nombre'] . ($propietario['apellidos'] ? ' ' . $propietario['apellidos'] : '');

        $this->render('propietario/index', [
            'propietario'     => $propietario,
            'nombreEmpresa'   => $nombreEmpresa,
            'parcelas'        => $parcelas,
            'tareas'          => $tareas,
            'yearFilter'      => $yearFilter,
            'anosDisponibles' => $anosDisponibles,
            'page'            => $page,
            'totalPaginas'    => $totalPaginas,
            'totalTareas'     => $totalTareas,
            'user'            => ['name' => $nombreCompleto],
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

        // Filtro por año y paginación
        $yearFilter = intval($_GET['year'] ?? date('Y'));
        $page       = max(1, intval($_GET['page'] ?? 1));
        $porPagina  = 15;
        $offset     = ($page - 1) * $porPagina;

        // Años disponibles
        $stmt = $db->prepare("
            SELECT DISTINCT YEAR(t.fecha) AS ano
            FROM tareas t
            JOIN tarea_parcelas tp ON t.id = tp.tarea_id
            WHERE tp.parcela_id = ? AND t.estado = 'realizada' AND t.fecha IS NOT NULL
            ORDER BY ano DESC
        ");
        $stmt->bind_param("i", $parcelaId);
        $stmt->execute();
        $anosDisponibles = array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'ano');
        $stmt->close();

        if (!in_array($yearFilter, $anosDisponibles) && !empty($anosDisponibles)) {
            $yearFilter = $anosDisponibles[0];
        }

        // Total de tareas para paginación
        $stmt = $db->prepare("
            SELECT COUNT(*) AS total
            FROM tareas t
            JOIN tarea_parcelas tp ON t.id = tp.tarea_id
            WHERE tp.parcela_id = ? AND t.estado = 'realizada' AND YEAR(t.fecha) = ?
        ");
        $stmt->bind_param("ii", $parcelaId, $yearFilter);
        $stmt->execute();
        $totalTareas = intval($stmt->get_result()->fetch_assoc()['total']);
        $stmt->close();

        // Tareas paginadas
        $stmt = $db->prepare("
            SELECT t.fecha, t.titulo, t.descripcion
            FROM tareas t
            JOIN tarea_parcelas tp ON t.id = tp.tarea_id
            WHERE tp.parcela_id = ? AND t.estado = 'realizada' AND YEAR(t.fecha) = ?
            ORDER BY t.fecha DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->bind_param("iiii", $parcelaId, $yearFilter, $porPagina, $offset);
        $stmt->execute();
        $tareas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $totalPaginas = ceil($totalTareas / $porPagina);

        $nombreCompleto = $parcela['prop_nombre'] . ($parcela['prop_apellidos'] ? ' ' . $parcela['prop_apellidos'] : '');

        $this->render('propietario/parcela_detalle', [
            'parcela'         => $parcela,
            'tareas'          => $tareas,
            'yearFilter'      => $yearFilter,
            'anosDisponibles' => $anosDisponibles,
            'page'            => $page,
            'totalPaginas'    => $totalPaginas,
            'totalTareas'     => $totalTareas,
            'user'            => ['name' => $nombreCompleto],
        ]);
    }
}
