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

        // Estadísticas fijas para la landing (marketing)
        // TODO: Descomentar getLandingStats() cuando se quiera usar datos reales de BD
        $stats = [
            'parcelas'     => 79,
            'empresas'     => 3,
            'trabajadores' => 58,
        ];

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
     *
     * TODO: Activar cuando se quiera mostrar datos reales en vez de los fijos.
     * Uso: $stats = $this->getLandingStats();
     */
    /*
    private function getLandingStats(): array
    {
        $defaults = [
            'parcelas'     => 0,
            'empresas'     => 0,
            'trabajadores' => 0,
        ];

        try {
            $db = \Database::connect();

            $r = $db->query("SELECT COUNT(*) AS total FROM parcelas");
            $defaults['parcelas'] = (int) ($r->fetch_assoc()['total'] ?? 0);

            $r = $db->query("SELECT COUNT(*) AS total FROM usuarios WHERE rol = 'empresa'");
            $defaults['empresas'] = (int) ($r->fetch_assoc()['total'] ?? 0);

            $r = $db->query("SELECT COUNT(*) AS total FROM trabajadores WHERE baja_ss IS NULL OR baja_ss = ''");
            $defaults['trabajadores'] = (int) ($r->fetch_assoc()['total'] ?? 0);

        } catch (\Throwable $e) {
            \Core\Logger::app()->warning("Landing stats query failed: " . $e->getMessage());
        }

        return $defaults;
    }
    */
}
