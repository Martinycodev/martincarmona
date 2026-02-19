<?php

namespace App\Controllers;

require_once BASE_PATH . '/app/Models/Tarea.php';

class DashboardController extends BaseController
{
    public function index()
    {
        // Verificar si el usuario está autenticado
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/');
            return;
        }
        
        // Obtener información del usuario
        $userId = $_SESSION['user_id'];
        $userName = $_SESSION['user_name'];
        $userEmail = $_SESSION['user_email'];
        
        // Obtener estadísticas y tareas usando el modelo
        $tareaModel = new \App\Models\Tarea();
        $stats = $tareaModel->getStats($userId);
        $tareas = $tareaModel->getAll($userId);
        
        $data = [
            'user' => [
                'id' => $userId,
                'name' => $userName,
                'email' => $userEmail
            ],
            'stats' => $stats,
            'tareas' => $tareas
        ];
        
        $this->render('dashboard/index', $data);
    }
}
