<?php

namespace App\Controllers;

class HomeController extends BaseController
{
    public function index()
    {
        // Si el usuario tiene sesión activa, redirigir al dashboard
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/dashboard');
            return;
        }
        $error = $_GET['error'] ?? null;
        $data = ['error' => $error];
        $this->render('home', $data);
    }
}
