<?php
namespace App\Controllers;
require_once BASE_PATH . '/config/database.php';
class EmpresasController extends BaseController
{
    public function index()
    {
        $db = \Database::connect();
        $result = $db->query("SELECT * FROM empresas");
        $empresas = $result->fetch_all(MYSQLI_ASSOC);
        $db->close();
        // Obtener información del usuario de la sesión
        $data = [
            'empresas' => $empresas,
            'user' => [
                'name' => $_SESSION['user_name'] ?? 'Usuario'
            ]
        ];
        $this->render('empresas/index', $data);
    }
}
