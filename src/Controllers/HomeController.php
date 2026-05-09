<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;

class HomeController extends Controller
{
    public function index(Request $request, Response $response): string
    {
        // Landing portfolio personal (sin CTAs comerciales).
        // El layout 'landing' usa la paleta del personaje + tipografía
        // Fraunces/Switzer, totalmente independiente de Tailwind/Vite.
        $this->setLayout('landing');

        return $this->render('home/landing', [
            'title' => 'Martín Carmona — Una década convirtiendo ideas en cosas reales',
        ]);
    }
}
