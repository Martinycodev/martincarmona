<?php
namespace App\Controllers;

require_once BASE_PATH . '/app/Models/Empresa.php';

class EmpresasController extends BaseController
{
    public function __construct()
    {
    }

    public function index()
    {
        // Verificar si el usuario está autenticado
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/');
            return;
        }

        // Obtener empresas del usuario usando el modelo
        $empresaModel = new \App\Models\Empresa();
        $empresas = $empresaModel->getAll($_SESSION['user_id']);

        // Obtener información del usuario de la sesión
        $data = [
            'empresas' => $empresas,
            'user' => [
                'id' => $_SESSION['user_id'],
                'name' => $_SESSION['user_name'] ?? 'Usuario',
                'email' => $_SESSION['user_email'] ?? ''
            ]
        ];
        
        $this->render('empresas/index', $data);
    }
}
