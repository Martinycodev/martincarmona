<?php

namespace App\Modules\Planner\Controllers;

use App\Modules\Planner\Models\AiLog;
use App\Modules\Planner\Models\Checkin;
use App\Modules\Planner\Models\Constraint;
use App\Modules\Planner\Models\Goal;
use App\Modules\Planner\Models\PostponeLog;
use App\Modules\Planner\Models\ScheduleBlock;
use App\Modules\Planner\Services\PlannerAIService;

/**
 * DevController
 * -------------
 * Endpoints temporales SOLO para verificar el wiring del módulo en
 * desarrollo. Borrar este archivo (y su ruta) cuando termine la
 * Fase 7 o cuando los endpoints reales estén operativos.
 *
 * Cada acción intenta hacer una query trivial contra cada tabla
 * planner_* y devuelve un resumen JSON. Si una tabla falta, el error
 * de PDO te dirá exactamente cuál.
 */
class DevController extends PlannerController
{
    /**
     * GET /planner/dev/test-models
     * Verifica que las 6 tablas existen y los modelos pueden leerlas.
     */
    public function testModels(): string
    {
        $report = [];

        // Cada bloque captura su propio error para que un fallo en una
        // tabla no impida ver el estado del resto.
        $report['goals']           = $this->safeCount(fn() => Goal::all());
        $report['goals_active']    = $this->safeCount(fn() => Goal::active());
        $report['constraints']     = $this->safeCount(fn() => Constraint::all());
        $report['schedule_blocks'] = $this->safeCount(fn() => ScheduleBlock::all());
        $report['postpone_log']    = $this->safeCount(fn() => PostponeLog::all());
        $report['checkins']        = $this->safeCount(fn() => Checkin::all());
        $report['ai_logs']         = $this->safeCount(fn() => AiLog::all());

        // Bonus: muestro el primer goal activo si lo hay, así verificas
        // que el seed se aplicó correctamente.
        $firstGoal = null;
        try {
            $active = Goal::active();
            $firstGoal = $active[0] ?? null;
        } catch (\Throwable $e) {
            $firstGoal = ['error' => $e->getMessage()];
        }

        return $this->json([
            'module'           => 'planner',
            'status'           => 'ok',
            'tables'           => $report,
            'first_active_goal' => $firstGoal,
        ]);
    }

    /**
     * GET /planner/dev/test-ai
     * Genera un horario de prueba para HOY usando PlannerAIService.
     * Recomendado: poner PLANNER_AI_DRY_RUN=1 en .env para no gastar
     * tokens reales mientras desarrollas.
     */
    public function testAi(): string
    {
        try {
            $service  = new PlannerAIService();
            $schedule = $service->generateDailySchedule(date('Y-m-d'));

            if ($schedule === null) {
                return $this->json([
                    'ok'   => false,
                    'note' => 'PlannerAIService devolvió null. Revisa la última fila de planner_ai_logs para ver por qué.',
                ], 500);
            }

            return $this->json([
                'ok'       => true,
                'schedule' => $schedule,
            ]);
        } catch (\Throwable $e) {
            return $this->json([
                'ok'    => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ejecuta una query y devuelve el conteo, o el mensaje de error
     * si la tabla no existe / la query falla.
     */
    private function safeCount(callable $query): array
    {
        try {
            $rows = $query();
            return ['ok' => true, 'count' => count($rows)];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
