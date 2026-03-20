<?php

namespace App\Controllers;


class TareasController extends BaseController
{
    private $tareaModel;
    private $db;

    public function __construct()
    {
        $this->requireEmpresa();

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
                \Core\Logger::app()->error('Datos recibidos: ' . print_r($input, true));

                if ($input === null) {
                    echo json_encode(['success' => false, 'message' => 'Error al procesar los datos JSON']);
                    return;
                }

                $v = \Core\Validator::make($input, [
                    'fecha'  => 'date',
                    'horas'  => 'numeric|min:0|max:24',
                    'titulo' => 'max_length:200',
                ]);
                if ($v->fails()) {
                    echo json_encode(['success' => false, 'message' => implode(' ', $v->allErrors())]);
                    return;
                }

                // Extraer user_id de la sesión o del input
                $userId = $_SESSION['user_id'] ?? ($input['user_id'] ?? 0);

                $tareaData = [
                    'fecha'       => $input['fecha'] ?? date('Y-m-d'),
                    'titulo'      => strip_tags(trim($input['titulo'] ?? '')),
                    'descripcion' => strip_tags(trim($input['descripcion'] ?? '')),
                    'trabajo'     => intval($input['trabajo'] ?? 0),
                    'horas'       => floatval($input['horas'] ?? 0),
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

                \Core\Logger::app()->error('Intentando crear tarea con datos: ' . print_r($tareaData, true));
                \Core\Logger::app()->error('User ID: ' . $userId);

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

                \Core\Logger::app()->error('Datos después de la conversión: ' . print_r($tareaData, true));

                $result = $this->tareaModel->create($tareaData, $userId);
                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Tarea creada exitosamente', 'id' => $result]);
                } else {
                    $error = error_get_last();
                    $errorMessage = $error ? $error['message'] : 'Error desconocido';
                    \Core\Logger::app()->error('Error al crear tarea: ' . $errorMessage);
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

        $input = json_decode(file_get_contents('php://input'), true);

        // Si viene titulo sin fecha → tarea pendiente (fecha null)
        // Si viene fecha → tarea con fecha
        // Si no viene nada → fecha de hoy por defecto
        $fecha = null;
        if (!empty($input['fecha']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $input['fecha'])) {
            $fecha = $input['fecha'];
        } elseif (empty($input['titulo'])) {
            $fecha = date('Y-m-d');
        }

        $titulo = trim($input['titulo'] ?? '');

        $tareaData = [
            'fecha'       => $fecha,
            'titulo'      => $titulo,
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

                $v = \Core\Validator::make($input, [
                    'id'     => 'required|integer',
                    'fecha'  => 'date',
                    'horas'  => 'numeric|min:0|max:24',
                    'titulo' => 'max_length:200',
                ]);
                if ($v->fails()) {
                    echo json_encode(['success' => false, 'message' => implode(' ', $v->allErrors())]);
                    return;
                }

                $tareaData = [
                    'id'          => intval($input['id']),
                    'fecha'       => $input['fecha'] ?? date('Y-m-d'),
                    'titulo'      => strip_tags(trim($input['titulo'] ?? '')),
                    'descripcion' => strip_tags(trim($input['descripcion'] ?? '')),
                    'horas'       => $input['horas'] ?? 0,
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
            \Core\Logger::app()->error("Error obteniendo tarea: " . $e->getMessage());
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
            \Core\Logger::app()->error("Error obteniendo tareas del mes: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error al obtener tareas']);
        }
    }

    /**
     * Subir imágenes a una tarea
     */
    public function subirImagen()
    {
        // Limpiar cualquier output previo y forzar JSON
        if (ob_get_level()) ob_clean();
        header('Content-Type: application/json');

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

        try {
            $uploadedImages = [];
            $errors = [];

            // Límite: 10MB por imagen (las fotos de móvil se comprimen en cliente antes de subir)
            $maxFileSize = 10 * 1024 * 1024;
            $maxFiles = 10;

            $files = $_FILES['imagenes'];
            $count = min(count($files['name']), $maxFiles);

            for ($i = 0; $i < $count; $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    $tmpName = $files['tmp_name'][$i];
                    $originalName = basename($files['name'][$i]);
                    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

                    // Validar extensión
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    if (!in_array($extension, $allowedExtensions)) {
                        $errors[] = "$originalName: extensión no permitida";
                        continue;
                    }

                    // Validar tamaño
                    if ($files['size'][$i] > $maxFileSize) {
                        $errors[] = "$originalName: supera 10MB";
                        continue;
                    }

                    // Validar tipo MIME real
                    $mimeType = mime_content_type($tmpName);
                    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    if (!in_array($mimeType, $allowedMimes)) {
                        $errors[] = "$originalName: tipo de archivo no válido";
                        continue;
                    }

                    // Generar nombre único
                    $filename = uniqid() . '_' . time();
                    $targetFile = $uploadDir . $filename;

                    // Intentar redimensionar y comprimir la imagen
                    $saved = $this->optimizarImagen($tmpName, $mimeType, $targetFile, $extension);

                    if ($saved) {
                        $finalFile = $saved['path'];
                        $finalFilename = basename($finalFile);

                        $imageData = [
                            'filename' => $finalFilename,
                            'original_filename' => $originalName,
                            'file_path' => '/public/uploads/tareas/' . $tareaId . '/' . $finalFilename,
                            'file_size' => filesize($finalFile),
                            'mime_type' => $saved['mime']
                        ];

                        $imageId = $this->tareaModel->addImage($tareaId, $imageData);

                        if ($imageId) {
                            $imageData['id'] = $imageId;
                            $uploadedImages[] = $imageData;
                        } else {
                            $errors[] = "$originalName: error al guardar en BD";
                            @unlink($finalFile);
                        }
                    } else {
                        $errors[] = "$originalName: error al procesar imagen";
                    }
                } else if ($files['error'][$i] === UPLOAD_ERR_INI_SIZE || $files['error'][$i] === UPLOAD_ERR_FORM_SIZE) {
                    $errors[] = $files['name'][$i] . ": archivo demasiado grande";
                } else {
                    $errors[] = "Error en la subida de " . $files['name'][$i];
                }
            }

            if (!empty($uploadedImages)) {
                echo json_encode([
                    'success' => true,
                    'message' => count($uploadedImages) . ' imagen(es) subida(s)',
                    'images' => $uploadedImages,
                    'errors' => $errors
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => !empty($errors) ? implode('. ', $errors) : 'No se pudo subir ninguna imagen',
                    'errors' => $errors
                ]);
            }
        } catch (\Throwable $e) {
            \Core\Logger::app()->error("Error en subirImagen: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error del servidor: ' . $e->getMessage()
            ]);
        }
    }

    // optimizarImagen() se hereda de BaseController

    /**
     * Eliminar una imagen
     */
    public function eliminarImagen()
    {
        header('Content-Type: application/json');
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

        // Marcar trabajador como activo este mes
        if ($result) {
            $trabajadorModel = new \App\Models\Trabajador();
            $trabajadorModel->marcarActivo($trabajadorId);
        }

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
        $trabajadorModel = new \App\Models\Trabajador();
        foreach ($cuadrilla as $trabajador) {
            $ok = $this->tareaModel->agregarTrabajador($tareaId, $trabajador['id'], $tarea['horas']);
            if ($ok) {
                $added++;
                // Marcar trabajador como activo este mes
                $trabajadorModel->marcarActivo($trabajador['id']);
            } else {
                $skipped++;
            }
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

        $tareaId   = intval($input['tarea_id'] ?? 0);
        $trabajoId = intval($input['trabajo_id'] ?? 0);

        if (!$tareaId) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }

        // Obtener datos de la tarea
        $stmt = $this->db->prepare("SELECT horas, fecha FROM tareas WHERE id = ? AND id_user = ?");
        $stmt->bind_param("ii", $tareaId, $userId);
        $stmt->execute();
        $tarea = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$tarea) {
            echo json_encode(['success' => false, 'message' => 'Tarea no encontrada']);
            return;
        }

        // Obtener la categoría del trabajo anterior (para borrar registros reactivos)
        $categoriaAnterior = $this->_getCategoriaTrabajoActual($tareaId);

        // Si trabajo_id=0 es que se quita el trabajo
        $result = $this->tareaModel->cambiarTrabajo($tareaId, $trabajoId, $tarea['horas']);

        if ($result) {
            // Borrar registros reactivos del trabajo anterior
            if ($categoriaAnterior) {
                $this->_borrarRegistroReactivo($categoriaAnterior, $tareaId, $userId);
            }

            // Crear registro reactivo del nuevo trabajo (si aplica)
            if ($trabajoId > 0) {
                $stmtTr = $this->db->prepare("SELECT nombre, categoria FROM trabajos WHERE id = ? AND id_user = ?");
                $stmtTr->bind_param("ii", $trabajoId, $userId);
                $stmtTr->execute();
                $trabajoRow = $stmtTr->get_result()->fetch_assoc();
                $stmtTr->close();

                if ($trabajoRow) {
                    $this->_crearRegistroReactivo(
                        $trabajoRow['categoria'],
                        $tareaId,
                        $tarea['fecha'] ?? date('Y-m-d'),
                        $userId,
                        $trabajoRow['nombre']
                    );
                }
            }
        }

        echo json_encode(['success' => $result, 'message' => $result ? 'Trabajo actualizado' : 'Error al cambiar trabajo']);
    }

    /**
     * Obtener la categoría del trabajo actualmente asignado a una tarea
     */
    private function _getCategoriaTrabajoActual($tareaId)
    {
        $stmt = $this->db->prepare("
            SELECT tj.categoria FROM tarea_trabajos tt
            JOIN trabajos tj ON tt.trabajo_id = tj.id
            WHERE tt.tarea_id = ? LIMIT 1
        ");
        $stmt->bind_param("i", $tareaId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ? $row['categoria'] : null;
    }

    /**
     * Crear registro reactivo según la categoría del trabajo:
     * - riego → registro en riegos
     * - recoleccion → registro en campana_registros (si hay campaña activa)
     * - tratamiento → registro en fitosanitarios_aplicaciones
     */
    private function _crearRegistroReactivo($categoria, $tareaId, $fecha, $userId, $trabajoNombre)
    {
        // Obtener parcelas asignadas a esta tarea
        $stmtP = $this->db->prepare("SELECT parcela_id FROM tarea_parcelas WHERE tarea_id = ?");
        $stmtP->bind_param("i", $tareaId);
        $stmtP->execute();
        $parcelas = $stmtP->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmtP->close();

        $parcelaId = !empty($parcelas) ? intval($parcelas[0]['parcela_id']) : null;

        switch ($categoria) {
            case 'riego':
                // Crear registro de riego con fecha_ini = fecha de la tarea
                $stmt = $this->db->prepare("
                    INSERT INTO riegos (parcela_id, tarea_id, fecha_ini, id_user)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->bind_param("iisi", $parcelaId, $tareaId, $fecha, $userId);
                $stmt->execute();
                $stmt->close();
                break;

            case 'recoleccion':
                // Crear registro en campaña activa (si existe)
                $campanaModel = new \App\Models\Campana();
                $campanaActiva = $campanaModel->getActiva($userId);
                if ($campanaActiva) {
                    foreach ($parcelas as $p) {
                        // Comprobar si ya existe un registro para esta tarea
                        $check = $this->db->prepare("
                            SELECT id FROM campana_registros WHERE tarea_id = ? AND parcela_id = ?
                        ");
                        $check->bind_param("ii", $tareaId, $p['parcela_id']);
                        $check->execute();
                        $existe = $check->get_result()->fetch_assoc();
                        $check->close();

                        if (!$existe) {
                            $stmt = $this->db->prepare("
                                INSERT INTO campana_registros (campana_id, parcela_id, tarea_id, fecha, kilos, rendimiento_pct, id_user)
                                VALUES (?, ?, ?, ?, 0, 0, ?)
                            ");
                            $campanaId = $campanaActiva['id'];
                            $stmt->bind_param("iiisi", $campanaId, $p['parcela_id'], $tareaId, $fecha, $userId);
                            $stmt->execute();
                            $stmt->close();
                        }
                    }
                }
                break;

            case 'tratamiento':
                // Crear registro de aplicación fitosanitaria
                // Usar el nombre del trabajo como producto por defecto
                $producto = $trabajoNombre;
                foreach ($parcelas as $p) {
                    // Comprobar si ya existe registro para esta tarea y parcela
                    $check = $this->db->prepare("
                        SELECT id FROM fitosanitarios_aplicaciones WHERE tarea_id = ? AND parcela_id = ?
                    ");
                    $check->bind_param("ii", $tareaId, $p['parcela_id']);
                    $check->execute();
                    $existe = $check->get_result()->fetch_assoc();
                    $check->close();

                    if (!$existe) {
                        $stmt = $this->db->prepare("
                            INSERT INTO fitosanitarios_aplicaciones (parcela_id, producto, fecha, tarea_id, id_user)
                            VALUES (?, ?, ?, ?, ?)
                        ");
                        $stmt->bind_param("issii", $p['parcela_id'], $producto, $fecha, $tareaId, $userId);
                        $stmt->execute();
                        $stmt->close();
                    }
                }
                break;
        }
    }

    /**
     * Borrar registros reactivos creados automáticamente al cambiar de trabajo
     */
    private function _borrarRegistroReactivo($categoria, $tareaId, $userId)
    {
        switch ($categoria) {
            case 'riego':
                $stmt = $this->db->prepare("DELETE FROM riegos WHERE tarea_id = ? AND id_user = ?");
                $stmt->bind_param("ii", $tareaId, $userId);
                $stmt->execute();
                $stmt->close();
                break;

            case 'recoleccion':
                $stmt = $this->db->prepare("DELETE FROM campana_registros WHERE tarea_id = ? AND id_user = ?");
                $stmt->bind_param("ii", $tareaId, $userId);
                $stmt->execute();
                $stmt->close();
                break;

            case 'tratamiento':
                $stmt = $this->db->prepare("DELETE FROM fitosanitarios_aplicaciones WHERE tarea_id = ? AND id_user = ?");
                $stmt->bind_param("ii", $tareaId, $userId);
                $stmt->execute();
                $stmt->close();
                break;
        }
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
        $res = $this->db->query("SELECT id, nombre, precio_hora FROM trabajos ORDER BY nombre");
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

    /**
     * Lista de tareas pendientes (sin fecha)
     */
    public function pendientes()
    {
        $db = \Database::connect();
        $stmt = $db->prepare("
            SELECT t.*,
                   GROUP_CONCAT(DISTINCT tr.nombre ORDER BY tr.nombre SEPARATOR ', ') as trabajadores_nombres,
                   GROUP_CONCAT(DISTINCT p.nombre  ORDER BY p.nombre  SEPARATOR ', ') as parcelas_nombres
            FROM tareas t
            LEFT JOIN tarea_trabajadores tt ON t.id = tt.tarea_id
            LEFT JOIN trabajadores tr ON tt.trabajador_id = tr.id
            LEFT JOIN tarea_parcelas tp ON t.id = tp.tarea_id
            LEFT JOIN parcelas p ON tp.parcela_id = p.id
            WHERE t.estado = 'pendiente' AND t.id_user = ?
            GROUP BY t.id
            ORDER BY t.created_at DESC
        ");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $tareas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $db->close();

        $this->render('tareas/pendientes', [
            'tareas' => $tareas,
            'user'   => ['name' => $_SESSION['user_name'] ?? 'Usuario']
        ]);
    }

    /**
     * Obtener tareas pendientes en formato JSON (para el panel del dashboard)
     */
    public function obtenerPendientes()
    {
        header('Content-Type: application/json');
        $db = \Database::connect();
        $stmt = $db->prepare("
            SELECT t.id, t.titulo, t.descripcion,
                   GROUP_CONCAT(DISTINCT tr.nombre ORDER BY tr.nombre SEPARATOR ', ') as trabajadores,
                   GROUP_CONCAT(DISTINCT trab.nombre ORDER BY trab.nombre SEPARATOR ', ') as trabajos
            FROM tareas t
            LEFT JOIN tarea_trabajadores tt ON t.id = tt.tarea_id
            LEFT JOIN trabajadores tr ON tt.trabajador_id = tr.id
            LEFT JOIN tarea_trabajos ttj ON t.id = ttj.tarea_id
            LEFT JOIN trabajos trab ON ttj.trabajo_id = trab.id
            WHERE t.estado = 'pendiente' AND t.id_user = ?
            GROUP BY t.id
            ORDER BY t.created_at DESC
        ");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $tareas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        echo json_encode(['success' => true, 'tareas' => $tareas]);
    }

    /**
     * Quitar fecha a una tarea (del calendario → pendiente)
     */
    public function desfechar()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        $this->validateCsrf();

        $input = json_decode(file_get_contents('php://input'), true);
        $id = intval($input['id'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID no válido']);
            return;
        }

        try {
            $db = \Database::connect();
            $stmt = $db->prepare("
                UPDATE tareas
                SET fecha = NULL, estado = 'pendiente', updated_at = NOW()
                WHERE id = ? AND id_user = ?
            ");
            $stmt->bind_param("ii", $id, $_SESSION['user_id']);

            if ($stmt->execute() && $stmt->affected_rows > 0) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se encontró la tarea']);
            }
            $stmt->close();
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error interno']);
        }
    }

    /**
     * Crear una tarea pendiente (sin fecha)
     */
    public function crearPendiente()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        $this->validateCsrf();

        $input = json_decode(file_get_contents('php://input'), true);

        $v = \Core\Validator::make($input ?? [], [
            'titulo'      => 'required|max_length:200',
            'descripcion' => 'max_length:1000',
        ]);
        if ($v->fails()) {
            echo json_encode(['success' => false, 'message' => implode(' ', $v->allErrors())]);
            return;
        }

        $titulo      = strip_tags(trim($input['titulo']));
        $descripcion = strip_tags(trim($input['descripcion'] ?? ''));

        try {
            $db   = \Database::connect();
            $stmt = $db->prepare("
                INSERT INTO tareas (titulo, descripcion, estado, horas, id_user, created_at, updated_at)
                VALUES (?, ?, 'pendiente', 0, ?, NOW(), NOW())
            ");
            $stmt->bind_param("ssi", $titulo, $descripcion, $_SESSION['user_id']);

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'id' => $db->insert_id]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al crear la tarea: ' . $stmt->error]);
            }
            $stmt->close();
            $db->close();

        } catch (\Exception $e) {
            \Core\Logger::app()->error("Error creando tarea pendiente: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
        }
    }

    /**
     * Asignar fecha a una tarea pendiente → pasa a 'realizada'
     */
    public function fechar()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        $this->validateCsrf();

        $input = json_decode(file_get_contents('php://input'), true);

        $v = \Core\Validator::make($input ?? [], [
            'id'    => 'required|integer',
            'fecha' => 'required|date',
        ]);
        if ($v->fails()) {
            echo json_encode(['success' => false, 'message' => implode(' ', $v->allErrors())]);
            return;
        }

        $id    = intval($input['id']);
        $fecha = $input['fecha'];

        try {
            $db   = \Database::connect();
            $stmt = $db->prepare("
                UPDATE tareas
                SET fecha = ?, estado = 'realizada', updated_at = NOW()
                WHERE id = ? AND id_user = ? AND estado = 'pendiente'
            ");
            $stmt->bind_param("sii", $fecha, $id, $_SESSION['user_id']);

            if ($stmt->execute() && $stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Tarea fechada correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo fechar la tarea']);
            }
            $stmt->close();
            $db->close();

        } catch (\Exception $e) {
            \Core\Logger::app()->error("Error fechando tarea: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
        }
    }
}
