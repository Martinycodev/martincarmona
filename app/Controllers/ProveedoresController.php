<?php
namespace App\Controllers;
require_once BASE_PATH . '/config/database.php';
class ProveedoresController extends BaseController
{
    public function index()
    {
        $db = \Database::connect();
        $result = $db->query("SELECT * FROM proveedores");
        $proveedores = $result->fetch_all(MYSQLI_ASSOC);
        $db->close();
        $data = [
            'proveedores' => $proveedores,
            'user' => [
                'name' => $_SESSION['user_name'] ?? 'Usuario'
            ]
        ];
        $this->render('proveedores/index', $data);
    }
}
