<?php

namespace App\Controllers;

class DatosController extends BaseController
{
    public function index()
    {
        // Verificar si el usuario está autenticado
        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            // Para debugging, mostrar información de la sesión
            /*echo "Sesión no iniciada. Contenido de \$_SESSION:<br>";
            var_dump($_SESSION);
            echo "<br><a href='/'>Volver al inicio</a>";
            return*/

            $this->redirect('/');
        }
        
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
