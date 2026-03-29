<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Project;

class PortfolioController extends Controller
{
    public function index(Request $request, Response $response): string
    {
        $category = $request->get('category', 'all');
        $projects = Project::all($category !== 'all' ? $category : null);

        return $this->render('portfolio/index', [
            'title'    => 'Portfolio — Martín Carmona',
            'projects' => $projects,
            'category' => $category,
        ]);
    }

    public function show(Request $request, Response $response): string
    {
        // Slug viene por query param por ahora; mejorar con routing dinámico
        $slug    = $request->get('slug');
        $project = Project::findBySlug($slug);

        if (!$project) {
            throw new \Exception("Proyecto no encontrado", 404);
        }

        return $this->render('portfolio/show', [
            'title'   => $project['title'] . ' — Portfolio',
            'project' => $project,
        ]);
    }
}
