<?php
namespace App\Controllers;
require_once BASE_PATH . '/config/database.php';
class HerramientasController extends BaseController
{
    public function index()
    {
        $db = \Database::connect();
        $result = $db->query("SELECT * FROM herramientas");
        $herramientas = $result->fetch_all(MYSQLI_ASSOC);
        $db->close();
        $data = [
            'herramientas' => $herramientas,
            'user' => [
                'name' => $_SESSION['user_name'] ?? 'Usuario'
            ]
        ];
        $this->render('herramientas/index', $data);
    }
}
