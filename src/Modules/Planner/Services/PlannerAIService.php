<?php

namespace App\Modules\Planner\Services;

use App\Modules\Planner\Models\AiLog;
use App\Modules\Planner\Models\Checkin;
use App\Modules\Planner\Models\Constraint;
use App\Modules\Planner\Models\Goal;
use App\Modules\Planner\Models\PostponeLog;

/**
 * PlannerAIService
 * ----------------
 * Orquesta la generación del horario diario por la IA.
 *
 * Flujo de generateDailySchedule():
 *   1. Recolecta el contexto desde la BD (goals, constraints, último checkin, postponements).
 *   2. Construye el user_message con ese contexto.
 *   3. Carga el system prompt fijo desde Prompts/scheduler.system.txt.
 *   4. Llama a AnthropicClient::sendMessage().
 *   5. Valida el JSON con ScheduleValidator.
 *   6. Si falla → reintenta UNA vez con un mensaje de corrección.
 *   7. Loguea SIEMPRE en planner_ai_logs (ok | validation_failed | api_error | retry).
 *   8. Devuelve el horario decodificado o null si la generación falló.
 *
 * Decisión: en error irrecuperable devuelve null en lugar de lanzar.
 * El caller (futuro ScheduleRecalculator) decide si caer al fallback
 * del día anterior o avisar al usuario.
 */
class PlannerAIService
{
    private const SYSTEM_PROMPT_FILE = __DIR__ . '/../Prompts/scheduler.system.txt';
    private const ENDPOINT_NAME      = 'messages';

    private AnthropicClient $client;

    public function __construct(?AnthropicClient $client = null)
    {
        $this->client = $client ?? new AnthropicClient();
    }

    /**
     * Genera el horario para una fecha. Devuelve el horario decodificado
     * (con claves date, blocks, rationale) o null si la IA falló.
     *
     * @param string $targetDate  Formato YYYY-MM-DD.
     */
    public function generateDailySchedule(string $targetDate): ?array
    {
        // 1) Recolectar contexto. Si la BD falla, lo dejamos burbujear:
        //    no es un problema de la IA y queremos verlo en producción.
        $context = $this->collectContext($targetDate);

        // 2) Cargar prompts.
        $systemPrompt = $this->loadSystemPrompt();
        $userMessage  = $this->buildUserMessage($targetDate, $context);

        // 3) Primer intento.
        $result = $this->attempt($systemPrompt, $userMessage, $isRetry = false);
        if ($result !== null) {
            return $result;
        }

        // 4) Reintento con mensaje de corrección. Solo se ejecuta si el
        //    primer intento falló por validación (no por error de red).
        $correctionMessage = $userMessage
            . "\n\nIMPORTANTE: tu respuesta anterior no cumplió el esquema JSON o las reglas de negocio. "
            . "Devuelve EXCLUSIVAMENTE un objeto JSON válido siguiendo TODAS las reglas del system prompt.";

        return $this->attempt($systemPrompt, $correctionMessage, $isRetry = true);
    }

    // ─────────────────────────────────────────────────────────────
    //  Pasos internos del pipeline
    // ─────────────────────────────────────────────────────────────

    /**
     * Un único intento: llama, valida, loguea y devuelve el array
     * decodificado (o null en cualquier error).
     */
    private function attempt(string $systemPrompt, string $userMessage, bool $isRetry): ?array
    {
        $promptHash = hash('sha256', $systemPrompt . "\n---\n" . $userMessage);
        $logBase = [
            'endpoint'    => self::ENDPOINT_NAME,
            'model'       => $this->client->model(),
            'prompt_hash' => $promptHash,
            'request_json' => json_encode([
                'system' => $systemPrompt,
                'user'   => $userMessage,
            ], JSON_UNESCAPED_UNICODE),
        ];

        // ── A. Llamada a la API ────────────────────────────────
        try {
            $apiResult = $this->client->sendMessage($systemPrompt, $userMessage);
        } catch (\Throwable $e) {
            // Error de red, HTTP 5xx, JSON malformado en envoltorio…
            // Logueamos como api_error y devolvemos null.
            $this->logSafely(array_merge($logBase, [
                'response_json' => json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE),
                'tokens_in'     => 0,
                'tokens_out'    => 0,
                'latency_ms'    => 0,
                'status'        => 'api_error',
            ]));
            return null;
        }

        // ── B. Parseo del texto a JSON ─────────────────────────
        $rawText = trim($apiResult['text']);
        try {
            $decoded = json_decode($rawText, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $this->logSafely(array_merge($logBase, [
                'response_json' => json_encode([
                    'parse_error' => $e->getMessage(),
                    'raw_text'    => $rawText,
                ], JSON_UNESCAPED_UNICODE),
                'tokens_in'     => $apiResult['tokens_in'],
                'tokens_out'    => $apiResult['tokens_out'],
                'latency_ms'    => $apiResult['latency_ms'],
                'status'        => 'validation_failed',
            ]));
            return null;
        }

        // ── C. Validación de esquema y reglas de negocio ───────
        $validation = ScheduleValidator::validate($decoded);
        if (!$validation['ok']) {
            $this->logSafely(array_merge($logBase, [
                'response_json' => json_encode([
                    'validation_errors' => $validation['errors'],
                    'raw_response'      => $decoded,
                ], JSON_UNESCAPED_UNICODE),
                'tokens_in'     => $apiResult['tokens_in'],
                'tokens_out'    => $apiResult['tokens_out'],
                'latency_ms'    => $apiResult['latency_ms'],
                // Marcamos como 'retry' si estamos en el primer intento,
                // 'validation_failed' si ya es el reintento.
                'status'        => $isRetry ? 'validation_failed' : 'retry',
            ]));
            return null;
        }

        // ── D. Éxito ───────────────────────────────────────────
        $this->logSafely(array_merge($logBase, [
            'response_json' => json_encode($decoded, JSON_UNESCAPED_UNICODE),
            'tokens_in'     => $apiResult['tokens_in'],
            'tokens_out'    => $apiResult['tokens_out'],
            'latency_ms'    => $apiResult['latency_ms'],
            'status'        => 'ok',
        ]));

        return $decoded;
    }

    /**
     * Inserta una entrada en planner_ai_logs sin propagar errores:
     * un fallo al loguear NUNCA debe romper el flujo principal.
     */
    private function logSafely(array $row): void
    {
        try {
            AiLog::create($row);
        } catch (\Throwable $e) {
            // Última línea de defensa: error_log de PHP. Si esto también
            // falla, no hay nada más que podamos hacer.
            error_log('PlannerAIService: fallo al guardar AiLog — ' . $e->getMessage());
        }
    }

    /**
     * Recolecta el contexto que la IA necesita para razonar sobre el día.
     */
    private function collectContext(string $targetDate): array
    {
        // Índice del día de la semana (0 = lunes, 6 = domingo) — coincide
        // con el bitmask de planner_constraints (L=bit0, M=bit1, ...).
        $weekday = (int) (new \DateTime($targetDate))->format('N') - 1;

        $latestCheckin = Checkin::latest();

        return [
            'goals'                 => Goal::active(),
            'constraints'           => Constraint::activeForWeekday($weekday),
            'recent_postponements'  => PostponeLog::forDate($targetDate),
            'load_factor'           => $latestCheckin['load_factor']  ?? 1.00,
            'last_checkin_summary'  => $latestCheckin['ai_summary']   ?? null,
        ];
    }

    /**
     * Carga el system prompt del archivo plano. Lo cacheo en static para
     * no leer del disco en cada llamada (irrelevante para 1 req/min, pero
     * gratis y mejor higiene).
     */
    private function loadSystemPrompt(): string
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }
        if (!is_readable(self::SYSTEM_PROMPT_FILE)) {
            throw new \RuntimeException('PlannerAIService: scheduler.system.txt no encontrado o no legible.');
        }
        $cache = (string) file_get_contents(self::SYSTEM_PROMPT_FILE);
        return $cache;
    }

    /**
     * Construye el user_message como JSON serializado. Mantenemos un
     * formato simple y plano para que el modelo lo procese sin ambigüedad.
     */
    private function buildUserMessage(string $targetDate, array $context): string
    {
        // Aplanamos los goals al subset relevante para no enviar timestamps
        // de creación ni columnas internas.
        $goals = array_map(fn(array $g): array => [
            'id'            => (int) $g['id'],
            'title'         => $g['title'],
            'priority'      => (int) $g['priority'],
            'horizon_weeks' => (int) $g['horizon_weeks'],
        ], $context['goals']);

        $constraints = array_map(fn(array $c): array => [
            'type'  => $c['type'],
            'label' => $c['label'],
            'start' => substr($c['start_time'], 0, 5),
            'end'   => substr($c['end_time'],   0, 5),
        ], $context['constraints']);

        $postponements = array_map(fn(array $p): array => [
            'block_title'  => $p['block_title'] ?? null,
            'postponed_at' => $p['postponed_at'],
        ], $context['recent_postponements']);

        $payload = [
            'target_date'           => $targetDate,
            'active_goals'          => $goals,
            'constraints'           => $constraints,
            'recent_postponements'  => $postponements,
            'load_factor'           => (float) $context['load_factor'],
            'last_checkin_summary'  => $context['last_checkin_summary'],
        ];

        return json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
