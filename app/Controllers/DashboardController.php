<?php

namespace App\Controllers;


class DashboardController extends BaseController
{
    public function index()
    {
        $this->requireEmpresa();

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

    /**
     * Hub de bases de datos — muestra links a todos los módulos de datos
     */
    public function datos()
    {
        $this->requireEmpresa();

        $data = [
            'user' => [
                'id' => $_SESSION['user_id'],
                'name' => $_SESSION['user_name'] ?? 'Usuario',
                'email' => $_SESSION['user_email'] ?? ''
            ]
        ];

        $this->render('datos/index', $data);
    }
}
