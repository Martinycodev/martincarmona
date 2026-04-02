<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;

class HomeController extends Controller
{
    public function index(Request $request, Response $response): string
    {
        // Placeholder provisional mientras se desarrolla la web definitiva
        $this->setLayout('placeholder');

        return $this->render('home/placeholder', [
            'title' => 'Martín Carmona — Fotógrafo, Videógrafo, Diseñador y Desarrollador',
        ]);
    }
}
