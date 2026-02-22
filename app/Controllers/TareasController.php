<?php

namespace App\Controllers;

require_once BASE_PATH . '/app/Models/Tarea.php';
require_once BASE_PATH . '/config/database.php';

class TareasController extends BaseController
{
    private $tareaModel;
    private $db;

    public function __construct()
    {
        // Verificar autenticación
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/');
            return;
        }

        $this->tareaModel = new \App\Models\Tarea();
        $this->db = \Database::connect();
    }

    public function index()
    {
        $userId = $_SESSION['user_id'];

        // Obtener página actual y límite
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $limit = 20; // 20 tareas por página

        // Obtener tareas paginadas y total
        $tareas = $this->tareaModel->getAllPaginated($userId, $page, $limit);
        $totalTareas = $this->tareaModel->getTotalCount($userId);
        $totalPaginas = ceil($totalTareas / $limit);

        // Incluir la información del usuario desde la sesión
        $user = [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'] ?? $_SESSION['username'] ?? 'Usuario'
        ];

        $data = [
            'titulo' => 'Gestión de Tareas',
            'mensaje' => 'Sistema de gestión de tareas del campo',
            'tareas' => $tareas,
            'user' => $user,
            'pagination' => [
                'currentPage' => $page,
                'totalPages' => $totalPaginas,
                'totalItems' => $totalTareas,
                'itemsPerPage' => $limit,
                'hasNext' => $page < $totalPaginas,
                'hasPrev' => $page > 1
            ]
        ];

        $this->render('tareas/index', $data);
    }

    public function crear()
    {
        $userId = $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();

            // Verificar si es una petición AJAX
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

            if ($isAjax) {
                header('Content-Type: application/json');

                // Obtener datos JSON
                $input = json_decode(file_get_contents('php://input'), true);
                error_log('Datos recibidos: ' . print_r($input, true));

                if ($input === null) {
                    echo json_encode(['success' => false, 'message' => 'Error al procesar los datos JSON']);
                    return;
                }

                // Extraer user_id de la sesión o del input
                $userId = $_SESSION['user_id'] ?? ($input['user_id'] ?? 0);

                $tareaData = [
                    'fecha' => $input['fecha'] ?? date('Y-m-d'),
                    'titulo' => $input['titulo'] ?? '',
                    'descripcion' => $input['descripcion'] ?? '',
                    'trabajo' => intval($input['trabajo'] ?? 0),
                    'horas' => floatval($input['horas'] ?? 0)
                ];

                // Manejar trabajadores múltiples
                if (isset($input['trabajadores']) && is_array($input['trabajadores'])) {
                    // Modo múltiple: array de IDs
                    $tareaData['trabajadores'] = array_map('intval', $input['trabajadores']);
                } elseif (isset($input['trabajador']) && $input['trabajador'] > 0) {
                    // Modo único: compatible con formato anterior
                    $tareaData['trabajador'] = intval($input['trabajador']);
                }

                // Manejar parcelas múltiples
                if (isset($input['parcelas']) && is_array($input['parcelas'])) {
                    // Modo múltiple: array de IDs
                    $tareaData['parcelas'] = array_map('intval', $input['parcelas']);
                } elseif (isset($input['parcela']) && $input['parcela'] > 0) {
                    // Modo único: compatible con formato anterior
                    $tareaData['parcela'] = intval($input['parcela']);
                }

                error_log('Intentando crear tarea con datos: ' . print_r($tareaData, true));
                error_log('User ID: ' . $userId);

                // Validar tipos de datos
                $tareaData['fecha'] = (string) $tareaData['fecha'];
                $tareaData['titulo'] = (string) $tareaData['titulo'];
                $tareaData['descripcion'] = (string) $tareaData['descripcion'];
                $tareaData['trabajo'] = (int) $tareaData['trabajo'];
                $tareaData['horas'] = (float) $tareaData['horas'];
                $userId = (int) $userId;

                // No convertir trabajadores si es array (ya están convertidos)
                if (isset($tareaData['trabajador'])) {
                    $tareaData['trabajador'] = (int) $tareaData['trabajador'];
                }

                // No convertir parcelas si es array (ya están convertidas)
                if (isset($tareaData['parcela'])) {
                    $tareaData['parcela'] = (int) $tareaData['parcela'];
                }

                error_log('Datos después de la conversión: ' . print_r($tareaData, true));

                $result = $this->tareaModel->create($tareaData, $userId);
                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Tarea creada exitosamente', 'id' => $result]);
                } else {
                    $error = error_get_last();
                    $errorMessage = $error ? $error['message'] : 'Error desconocido';
                    error_log('Error al crear tarea: ' . $errorMessage);
                    echo json_encode(['success' => false, 'message' => 'Error al crear la tarea: ' . $errorMessage]);
                }
                return;
            }

            // Si no es AJAX, redirigir al dashboard
            $this->redirect('/dashboard');
            return;
        }

        $data = [
            'titulo' => 'Crear Nueva Tarea'
        ];

        $this->render('tareas/crear', $data);
    }

    /**
     * Crea una tarea vacía para el flujo "crear y editar en sidebar".
     * Devuelve el ID para que el sidebar la abra inmediatamente.
     */
    public function crearVacio()
    {
        header('Content-Type: application/json');
        $this->validateCsrf();

        $userId = $_SESSION['user_id'];
        $tareaData = [
            'fecha'       => date('Y-m-d'),
            'titulo'      => '',
            'descripcion' => '',
            'trabajo'     => 0,
            'horas'       => 0,
        ];

        $result = $this->tareaModel->create($tareaData, $userId);
        if ($result) {
            echo json_encode(['success' => true, 'id' => $result]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear la tarea']);
        }
    }

    public function actualizar()
    {
        $userId = $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();

            // Verificar si es una petición AJAX
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

            if ($isAjax) {
                header('Content-Type: application/json');

                // Obtener datos JSON
                $input = json_decode(file_get_contents('php://input'), true);

                if ($input === null) {
                    echo json_encode(['success' => false, 'message' => 'Error al procesar los datos JSON']);
                    return;
                }

                $tareaData = [
                    'id' => $input['id'] ?? 0,
                    'fecha' => $input['fecha'] ?? date('Y-m-d'),
                    'titulo' => $input['titulo'] ?? '',
                    'descripcion' => $input['descripcion'] ?? '',
                    'horas' => $input['horas'] ?? 0
                ];

                // Manejar trabajadores múltiples en actualización
                if (isset($input['trabajadores']) && is_array($input['trabajadores'])) {
                    // Modo múltiple: array de IDs
                    $tareaData['trabajadores'] = array_map('intval', $input['trabajadores']);
                } elseif (isset($input['trabajador']) && $input['trabajador'] > 0) {
                    // Modo único: compatible con formato anterior
                    $tareaData['trabajador'] = intval($input['trabajador']);
                }

                // Manejar parcelas múltiples en actualización
                if (isset($input['parcelas']) && is_array($input['parcelas'])) {
                    // Modo múltiple: array de IDs
                    $tareaData['parcelas'] = array_map('intval', $input['parcelas']);
                } elseif (isset($input['parcela']) && $input['parcela'] > 0) {
                    // Modo único: compatible con formato anterior
                    $tareaData['parcela'] = intval($input['parcela']);
                }

                // Manejar trabajo
                if (isset($input['trabajo']) && $input['trabajo'] > 0) {
                    $tareaData['trabajo'] = intval($input['trabajo']);
                }

                if ($this->tareaModel->update($tareaData, $userId)) {
                    echo json_encode(['success' => true, 'message' => 'Tarea actualizada exitosamente']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al actualizar la tarea']);
                }
                return;
            }

            // Si no es AJAX, redirigir al dashboard
            $this->redirect('/dashboard');
            return;
        }

        // Si no es POST, mostrar error
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    }

    public function actualizarCampo()
    {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }

        $this->validateCsrf();

        $userId = $_SESSION['user_id'];
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input || !isset($input['id']) || !isset($input['campo']) || !isset($input['valor'])) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }

        $taskId = intval($input['id']);
        $campo = $input['campo'];
        $valor = $input['valor'];

        if ($this->tareaModel->updateSingleField($taskId, $campo, $valor, $userId)) {
            echo json_encode(['success' => true, 'message' => 'Campo actualizado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar el campo']);
        }
    }

    public function eliminar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $this->validateCsrf();

        $userId = $_SESSION['user_id'];
        $input = json_decode(file_get_contents('php://input'), true);
        $taskId = $input['id'] ?? 0;

        if ($this->tareaModel->delete($taskId, $userId)) {
            echo json_encode(['success' => true, 'message' => 'Tarea eliminada exitosamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar la tarea']);
        }
    }

    /**
     * Obtener una tarea individual con todos sus detalles
     */
    public function obtener()
    {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }

        // Obtener ID del parámetro GET
        $id = $_GET['id'] ?? null;

        if (!$id || !is_numeric($id)) {
            echo json_encode(['success' => false, 'message' => 'ID no válido']);
            return;
        }

        $userId = $_SESSION['user_id'];

        try {
            // Obtener la tarea principal
            $stmt = $this->db->prepare("
                SELECT
                    id,
                    fecha,
                    titulo,
                    descripcion,
                    horas,
                    created_at,
                    updated_at
                FROM tareas
                WHERE id = ? AND id_user = ?
            ");
            $stmt->bind_param("ii", $id, $userId);
            $stmt->execute();
            $result = $stmt->get_result();

            if (!($tarea = $result->fetch_assoc())) {
                echo json_encode(['success' => false, 'message' => 'Tarea no encontrada']);
                return;
            }
            $stmt->close();

            // Obtener trabajadores asignados
            $stmt = $this->db->prepare("
                SELECT 
                    t.id,
                    t.nombre,
                    tt.horas_asignadas
                FROM tarea_trabajadores tt
                JOIN trabajadores t ON tt.trabajador_id = t.id
                WHERE tt.tarea_id = ?
                ORDER BY t.nombre
            ");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();

            $trabajadores = [];
            while ($row = $result->fetch_assoc()) {
                $trabajadores[] = $row;
            }
            $stmt->close();

            // Obtener parcelas asignadas
            $stmt = $this->db->prepare("
                SELECT 
                    p.id,
                    p.nombre,
                    p.ubicacion,
                    tp.superficie_trabajada
                FROM tarea_parcelas tp
                JOIN parcelas p ON tp.parcela_id = p.id
                WHERE tp.tarea_id = ?
                ORDER BY p.nombre
            ");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();

            $parcelas = [];
            while ($row = $result->fetch_assoc()) {
                $parcelas[] = $row;
            }
            $stmt->close();

            // Obtener trabajos asignados
            $stmt = $this->db->prepare("
                SELECT 
                    tr.id,
                    tr.nombre,
                    tt.horas_trabajo,
                    tt.precio_hora
                FROM tarea_trabajos tt
                JOIN trabajos tr ON tt.trabajo_id = tr.id
                WHERE tt.tarea_id = ?
                ORDER BY tr.nombre
            ");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();

            $trabajos = [];
            while ($row = $result->fetch_assoc()) {
                $trabajos[] = $row;
            }
            $stmt->close();

            // Construir respuesta completa
            $tarea['trabajadores'] = $trabajadores;
            $tarea['parcelas'] = $parcelas;
            $tarea['trabajos'] = $trabajos;
            $tarea['imagenes'] = $this->tareaModel->getImages($id);

            echo json_encode(['success' => true, 'tarea' => $tarea]);

        } catch (\Exception $e) {
            error_log("Error obteniendo tarea: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
        }
    }

    /**
     * Obtener tareas de un mes específico (OPTIMIZADO para calendario)
     */
    public function obtenerPorMes()
    {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }

        $userId = $_SESSION['user_id'];
        $year = $_GET['year'] ?? date('Y');
        $month = $_GET['month'] ?? date('m');

        // Validar parámetros
        $year = (int) $year;
        $month = (int) $month;

        if ($year < 2020 || $year > 2050) {
            echo json_encode(['success' => false, 'message' => 'Año inválido']);
            return;
        }

        if ($month < 1 || $month > 12) {
            echo json_encode(['success' => false, 'message' => 'Mes inválido']);
            return;
        }

        try {
            $tareas = $this->tareaModel->getTareasByMonth($userId, $year, $month);
            echo json_encode([
                'success' => true,
                'tareas' => $tareas,
                'year' => $year,
                'month' => $month,
                'total' => count($tareas)
            ]);

        } catch (\Exception $e) {
            error_log("Error obteniendo tareas del mes: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error al obtener tareas']);
        }
    }

    /**
     * Subir imágenes a una tarea
     */
    public function subirImagen()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $this->validateCsrf();

        $userId = $_SESSION['user_id'];
        $tareaId = $_POST['tarea_id'] ?? 0;

        if (!$tareaId || !is_numeric($tareaId)) {
            echo json_encode(['success' => false, 'message' => 'ID de tarea inválido']);
            return;
        }

        // Verificar que hay archivos
        if (!isset($_FILES['imagenes']) || empty($_FILES['imagenes']['name'][0])) {
            echo json_encode(['success' => false, 'message' => 'No se han enviado imágenes']);
            return;
        }

        $uploadDir = BASE_PATH . '/public/uploads/tareas/' . $tareaId . '/';

        // Crear directorio si no existe
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $uploadedImages = [];
        $errors = [];

        // Procesar cada archivo
        $files = $_FILES['imagenes'];
        $count = count($files['name']);

        for ($i = 0; $i < $count; $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $tmpName = $files['tmp_name'][$i];
                $originalName = basename($files['name'][$i]);
                $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

                // Validar extensión
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if (!in_array($extension, $allowedExtensions)) {
                    $errors[] = "Archivo $originalName tiene una extensión no permitida";
                    continue;
                }

                // Generar nombre único
                $filename = uniqid() . '.' . $extension;
                $targetFile = $uploadDir . $filename;

                if (move_uploaded_file($tmpName, $targetFile)) {
                    $imageData = [
                        'filename' => $filename,
                        'original_filename' => $originalName,
                        'file_path' => '/public/uploads/tareas/' . $tareaId . '/' . $filename,
                        'file_size' => $files['size'][$i],
                        'mime_type' => $files['type'][$i]
                    ];

                    $imageId = $this->tareaModel->addImage($tareaId, $imageData);

                    if ($imageId) {
                        $imageData['id'] = $imageId;
                        $uploadedImages[] = $imageData;
                    } else {
                        $errors[] = "Error al guardar información de $originalName en base de datos";
                        unlink($targetFile); // Borrar archivo si falla BD
                    }
                } else {
                    $errors[] = "Error al mover el archivo $originalName";
                }
            } else {
                $errors[] = "Error en la subida del archivo " . $files['name'][$i];
            }
        }

        if (!empty($uploadedImages)) {
            echo json_encode([
                'success' => true,
                'message' => 'Imágenes subidas correctamente',
                'images' => $uploadedImages,
                'errors' => $errors
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No se pudo subir ninguna imagen',
                'errors' => $errors
            ]);
        }
    }

    /**
     * Eliminar una imagen
     */
    public function eliminarImagen()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $this->validateCsrf();

        $input = json_decode(file_get_contents('php://input'), true);
        $imageId = $input['id'] ?? 0;

        if (!$imageId) {
            echo json_encode(['success' => false, 'message' => 'ID de imagen inválido']);
            return;
        }

        // Obtener info de la imagen para borrar el archivo físico
        $image = $this->tareaModel->getImageById($imageId);

        if (!$image) {
            echo json_encode(['success' => false, 'message' => 'Imagen no encontrada']);
            return;
        }

        // Eliminar de base de datos
        if ($this->tareaModel->deleteImage($imageId)) {
            // Eliminar archivo físico
            $filePath = BASE_PATH . $image['file_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            echo json_encode(['success' => true, 'message' => 'Imagen eliminada correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar la imagen de la base de datos']);
        }
    }

    /**
     * Añade un trabajador a una tarea (desde el modal de detalle)
     * Recibe: { tarea_id, trabajador_id }
     */
    public function agregarTrabajador()
    {
        header('Content-Type: application/json');
        $this->validateCsrf();
        $userId = $_SESSION['user_id'];
        $input = json_decode(file_get_contents('php://input'), true);

        $tareaId    = intval($input['tarea_id'] ?? 0);
        $trabajadorId = intval($input['trabajador_id'] ?? 0);

        if (!$tareaId || !$trabajadorId) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }

        // Obtener horas de la tarea para asignarlas al trabajador
        $stmt = $this->db->prepare("SELECT horas FROM tareas WHERE id = ? AND id_user = ?");
        $stmt->bind_param("ii", $tareaId, $userId);
        $stmt->execute();
        $tarea = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$tarea) {
            echo json_encode(['success' => false, 'message' => 'Tarea no encontrada']);
            return;
        }

        $result = $this->tareaModel->agregarTrabajador($tareaId, $trabajadorId, $tarea['horas']);
        echo json_encode(['success' => $result, 'message' => $result ? 'Trabajador añadido' : 'Error al añadir trabajador']);
    }

    /**
     * Quita un trabajador de una tarea (desde el modal de detalle)
     * Recibe: { tarea_id, trabajador_id }
     */
    public function quitarTrabajador()
    {
        header('Content-Type: application/json');
        $this->validateCsrf();
        $userId = $_SESSION['user_id'];
        $input = json_decode(file_get_contents('php://input'), true);

        $tareaId      = intval($input['tarea_id'] ?? 0);
        $trabajadorId = intval($input['trabajador_id'] ?? 0);

        if (!$tareaId || !$trabajadorId) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }

        $result = $this->tareaModel->quitarTrabajador($tareaId, $trabajadorId);
        echo json_encode(['success' => $result, 'message' => $result ? 'Trabajador quitado' : 'Error al quitar trabajador']);
    }

    /**
     * Asigna todos los trabajadores de la cuadrilla a una tarea de una vez
     * Recibe: { tarea_id }
     */
    public function asignarCuadrilla()
    {
        header('Content-Type: application/json');
        $this->validateCsrf();
        $userId = $_SESSION['user_id'];
        $input  = json_decode(file_get_contents('php://input'), true);

        $tareaId = intval($input['tarea_id'] ?? 0);
        if (!$tareaId) {
            echo json_encode(['success' => false, 'message' => 'ID de tarea no válido']);
            return;
        }

        // Verificar que la tarea pertenece al usuario
        $stmt = $this->db->prepare("SELECT horas FROM tareas WHERE id = ? AND id_user = ?");
        $stmt->bind_param("ii", $tareaId, $userId);
        $stmt->execute();
        $tarea = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$tarea) {
            echo json_encode(['success' => false, 'message' => 'Tarea no encontrada']);
            return;
        }

        // Obtener todos los trabajadores de la cuadrilla
        $stmt = $this->db->prepare("SELECT id, nombre FROM trabajadores WHERE cuadrilla = 1 AND id_user = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result       = $stmt->get_result();
        $cuadrilla    = [];
        while ($row = $result->fetch_assoc()) {
            $cuadrilla[] = $row;
        }
        $stmt->close();

        if (empty($cuadrilla)) {
            echo json_encode(['success' => false, 'message' => 'No hay trabajadores en la cuadrilla']);
            return;
        }

        $added   = 0;
        $skipped = 0;
        foreach ($cuadrilla as $trabajador) {
            $ok = $this->tareaModel->agregarTrabajador($tareaId, $trabajador['id'], $tarea['horas']);
            $ok ? $added++ : $skipped++;
        }

        $nombres = array_column($cuadrilla, 'nombre');
        echo json_encode([
            'success'  => true,
            'message'  => "$added trabajador(es) añadido(s)" . ($skipped ? ", $skipped ya estaban asignados" : ''),
            'nombres'  => $nombres,
            'added'    => $added,
        ]);
    }

    /**
     * Añade una parcela a una tarea (desde el modal de detalle)
     * Recibe: { tarea_id, parcela_id }
     */
    public function agregarParcela()
    {
        header('Content-Type: application/json');
        $this->validateCsrf();
        $userId = $_SESSION['user_id'];
        $input = json_decode(file_get_contents('php://input'), true);

        $tareaId  = intval($input['tarea_id'] ?? 0);
        $parcelaId = intval($input['parcela_id'] ?? 0);

        if (!$tareaId || !$parcelaId) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }

        $result = $this->tareaModel->agregarParcela($tareaId, $parcelaId);
        echo json_encode(['success' => $result, 'message' => $result ? 'Parcela añadida' : 'Error al añadir parcela']);
    }

    /**
     * Quita una parcela de una tarea (desde el modal de detalle)
     * Recibe: { tarea_id, parcela_id }
     */
    public function quitarParcela()
    {
        header('Content-Type: application/json');
        $this->validateCsrf();
        $userId = $_SESSION['user_id'];
        $input = json_decode(file_get_contents('php://input'), true);

        $tareaId  = intval($input['tarea_id'] ?? 0);
        $parcelaId = intval($input['parcela_id'] ?? 0);

        if (!$tareaId || !$parcelaId) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }

        $result = $this->tareaModel->quitarParcela($tareaId, $parcelaId);
        echo json_encode(['success' => $result, 'message' => $result ? 'Parcela quitada' : 'Error al quitar parcela']);
    }

    /**
     * Cambia el tipo de trabajo de una tarea (desde el modal de detalle)
     * Recibe: { tarea_id, trabajo_id }
     */
    public function cambiarTrabajo()
    {
        header('Content-Type: application/json');
        $this->validateCsrf();
        $userId = $_SESSION['user_id'];
        $input = json_decode(file_get_contents('php://input'), true);

        $tareaId  = intval($input['tarea_id'] ?? 0);
        $trabajoId = intval($input['trabajo_id'] ?? 0);

        if (!$tareaId || !$trabajoId) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }

        // Obtener horas de la tarea
        $stmt = $this->db->prepare("SELECT horas FROM tareas WHERE id = ? AND id_user = ?");
        $stmt->bind_param("ii", $tareaId, $userId);
        $stmt->execute();
        $tarea = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$tarea) {
            echo json_encode(['success' => false, 'message' => 'Tarea no encontrada']);
            return;
        }

        $result = $this->tareaModel->cambiarTrabajo($tareaId, $trabajoId, $tarea['horas']);
        echo json_encode(['success' => $result, 'message' => $result ? 'Trabajo actualizado' : 'Error al cambiar trabajo']);
    }

    /**
     * Devuelve todos los trabajadores, parcelas y trabajos disponibles para
     * llenar los selects de edición inline del modal de detalle de tarea.
     */
    public function opcionesModal()
    {
        header('Content-Type: application/json');

        $trabajadores = [];
        $res = $this->db->query("SELECT id, nombre FROM trabajadores ORDER BY nombre");
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $trabajadores[] = $row;
            }
        }

        $parcelas = [];
        $res = $this->db->query("SELECT id, nombre FROM parcelas ORDER BY nombre");
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $parcelas[] = $row;
            }
        }

        $trabajos = [];
        $res = $this->db->query("SELECT id, nombre FROM trabajos ORDER BY nombre");
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $trabajos[] = $row;
            }
        }

        echo json_encode([
            'trabajadores' => $trabajadores,
            'parcelas'     => $parcelas,
            'trabajos'     => $trabajos,
        ]);
    }
}
