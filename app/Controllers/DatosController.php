<?php

namespace App\Controllers;

class DatosController extends BaseController
{
    public function index()
    {
        $this->requireEmpresa();

        // Obtener información del usuario
        $userId = $_SESSION['user_id'];
        $userName = $_SESSION['user_name'];
        $userEmail = $_SESSION['user_email'];
        
        // Preparar datos para la vista
        $data = [
            'user' => [
                'id' => $userId,
                'name' => $userName,
                'email' => $userEmail
            ]
        ];
        
        // Obtener estadísticas y tareas usando el model
        
        $this->render('datos/index', $data);
    }
}
