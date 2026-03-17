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
        
        // Reset mensual de trabajadores: día 1 de cada mes todos pasan a inactivo
        // Si hacen una tarea durante el mes, se reactivan automáticamente
        $mesActual = date('Y-m');
        if (($mesActual !== ($_SESSION['activo_reset_month'] ?? ''))) {
            $trabajadorModel = new \App\Models\Trabajador();
            $trabajadorModel->resetearActivoMensual($userId);
            $_SESSION['activo_reset_month'] = $mesActual;
        }

        // Obtener estadísticas y tareas usando el modelo
        $tareaModel = new \App\Models\Tarea();
        $stats = $tareaModel->getStats($userId);
        $tareas = $tareaModel->getAll($userId);

        // Obtener campaña activa para quick button
        $campanaModel = new \App\Models\Campana();
        $campanaActiva = $campanaModel->getActiva($userId);

        $data = [
            'user' => [
                'id' => $userId,
                'name' => $userName,
                'email' => $userEmail
            ],
            'stats' => $stats,
            'tareas' => $tareas,
            'campanaActiva' => $campanaActiva
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
