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

        // Obtener estadísticas reales para la landing page
        $stats = $this->getLandingStats();

        $error = $_GET['error'] ?? null;
        $data = [
            'error' => $error,
            'stats' => $stats,
        ];
        $this->render('home', $data);
    }

    /**
     * Consulta contadores reales de la BD para la barra de stats de la landing.
     * Si la BD no está disponible, devuelve valores por defecto.
     */
    private function getLandingStats(): array
    {
        $defaults = [
            'parcelas'     => 0,
            'empresas'     => 0,
            'trabajadores' => 0,
            'tareas'       => 0,
        ];

        try {
            $db = \Database::connect();

            // Total de parcelas registradas
            $r = $db->query("SELECT COUNT(*) AS total FROM parcelas");
            $defaults['parcelas'] = (int) ($r->fetch_assoc()['total'] ?? 0);

            // Empresas = usuarios con rol 'empresa'
            $r = $db->query("SELECT COUNT(*) AS total FROM usuarios WHERE rol = 'empresa'");
            $defaults['empresas'] = (int) ($r->fetch_assoc()['total'] ?? 0);

            // Trabajadores activos (no dados de baja)
            $r = $db->query("SELECT COUNT(*) AS total FROM trabajadores WHERE baja_ss IS NULL OR baja_ss = ''");
            $defaults['trabajadores'] = (int) ($r->fetch_assoc()['total'] ?? 0);

            // Tareas completadas
            $r = $db->query("SELECT COUNT(*) AS total FROM tareas WHERE estado = 'completada'");
            $defaults['tareas'] = (int) ($r->fetch_assoc()['total'] ?? 0);

        } catch (\Throwable $e) {
            // Si la BD falla, usamos los defaults (0)
            \Core\Logger::app()->warning("Landing stats query failed: " . $e->getMessage());
        }

        return $defaults;
    }
}
