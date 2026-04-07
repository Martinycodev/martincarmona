<?php

namespace App\Modules\Planner\Services;

/**
 * AnthropicClient
 * ---------------
 * Cliente HTTP minimalista para la Messages API de Anthropic.
 * Solo expone sendMessage() porque es la única operación que el módulo
 * Planner necesita. Si en el futuro hace falta streaming, batch o files,
 * se añade aquí (no se mete a Guzzle: una sola dep menos).
 *
 * Implementado con cURL nativo para mantener cero dependencias nuevas
 * en composer.json.
 *
 * Modo dry-run:
 *   Si $_ENV['PLANNER_AI_DRY_RUN'] = '1', sendMessage() NO llama a la
 *   API real. Devuelve una respuesta sintética que imita el formato de
 *   Anthropic con un horario válido. Útil para desarrollar el validator
 *   y el logging sin gastar tokens.
 */
class AnthropicClient
{
    private const ENDPOINT = 'https://api.anthropic.com/v1/messages';
    private const API_VERSION = '2023-06-01';
    private const TIMEOUT_SECONDS = 60;

    private string $apiKey;
    private string $model;
    private bool   $dryRun;

    public function __construct(?string $apiKey = null, string $model = 'claude-sonnet-4-6')
    {
        $this->apiKey = $apiKey ?? ($_ENV['ANTHROPIC_API_KEY'] ?? '');
        $this->model  = $model;
        $this->dryRun = ($_ENV['PLANNER_AI_DRY_RUN'] ?? '0') === '1';

        // En modo real exigimos API key. En dry-run no, para que se pueda
        // testear todo el flujo sin tener la clave configurada.
        if (!$this->dryRun && $this->apiKey === '') {
            throw new \RuntimeException(
                'AnthropicClient: ANTHROPIC_API_KEY no está definida en .env. '
                . 'Activa PLANNER_AI_DRY_RUN=1 si quieres trabajar sin API.'
            );
        }
    }

    public function isDryRun(): bool
    {
        return $this->dryRun;
    }

    public function model(): string
    {
        return $this->model;
    }

    /**
     * Envía un mensaje al modelo y devuelve la respuesta cruda decodificada.
     *
     * @param string $systemPrompt  Texto del system prompt.
     * @param string $userMessage   Contenido del mensaje user (string ya serializado).
     * @param int    $maxTokens     Límite superior de tokens de salida.
     *
     * @return array{
     *   raw: array,         // payload completo decodificado tal cual lo devolvió Anthropic (o el dry-run)
     *   text: string,       // primer bloque de texto extraído
     *   tokens_in: int,
     *   tokens_out: int,
     *   latency_ms: int,
     * }
     *
     * @throws \RuntimeException  En cualquier error HTTP, de red o parseo.
     */
    public function sendMessage(string $systemPrompt, string $userMessage, int $maxTokens = 2048): array
    {
        if ($this->dryRun) {
            return $this->fakeResponse($userMessage);
        }

        $payload = [
            'model'      => $this->model,
            'max_tokens' => $maxTokens,
            'system'     => $systemPrompt,
            'messages'   => [
                ['role' => 'user', 'content' => $userMessage],
            ],
        ];

        $startedAt = microtime(true);

        $ch = curl_init(self::ENDPOINT);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'x-api-key: ' . $this->apiKey,
                'anthropic-version: ' . self::API_VERSION,
            ],
            CURLOPT_TIMEOUT        => self::TIMEOUT_SECONDS,
        ]);

        $body       = curl_exec($ch);
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errno      = curl_errno($ch);
        $error      = curl_error($ch);
        curl_close($ch);

        $latencyMs = (int) round((microtime(true) - $startedAt) * 1000);

        if ($errno !== 0) {
            throw new \RuntimeException("AnthropicClient: error de red ({$errno}): {$error}");
        }

        if ($httpStatus < 200 || $httpStatus >= 300) {
            throw new \RuntimeException("AnthropicClient: HTTP {$httpStatus} — {$body}");
        }

        try {
            $decoded = json_decode((string) $body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \RuntimeException('AnthropicClient: respuesta no es JSON válido — ' . $e->getMessage());
        }

        return [
            'raw'        => $decoded,
            'text'       => $this->extractText($decoded),
            'tokens_in'  => (int) ($decoded['usage']['input_tokens']  ?? 0),
            'tokens_out' => (int) ($decoded['usage']['output_tokens'] ?? 0),
            'latency_ms' => $latencyMs,
        ];
    }

    /**
     * Extrae el primer bloque de texto de la respuesta de Anthropic.
     * El payload tiene forma: { content: [{ type: 'text', text: '...' }, ...] }
     */
    private function extractText(array $decoded): string
    {
        $blocks = $decoded['content'] ?? [];
        foreach ($blocks as $block) {
            if (($block['type'] ?? '') === 'text') {
                return (string) ($block['text'] ?? '');
            }
        }
        return '';
    }

    /**
     * Respuesta sintética para PLANNER_AI_DRY_RUN=1.
     * Devuelve un horario válido para "hoy" con 3 bloques. Sirve para
     * desarrollar y depurar todo el pipeline (validación + logging) sin
     * pegarle a la API real.
     */
    private function fakeResponse(string $userMessage): array
    {
        // Intentamos extraer la fecha objetivo del user_message para que
        // el dry-run sea más realista. Si no la encuentra, usa "hoy".
        $date = date('Y-m-d');
        if (preg_match('/"target_date"\s*:\s*"(\d{4}-\d{2}-\d{2})"/', $userMessage, $m)) {
            $date = $m[1];
        }

        $fakeJson = json_encode([
            'date'   => $date,
            'blocks' => [
                [
                    'title'       => 'Escribir borrador del case study principal',
                    'description' => 'Sesión enfocada en la narrativa del proyecto agrícola, sin distracciones.',
                    'block_type'  => 'deep_work',
                    'goal_id'     => 1,
                    'start_at'    => $date . 'T09:00:00',
                    'end_at'      => $date . 'T10:30:00',
                ],
                [
                    'title'       => 'Descansar y caminar',
                    'description' => 'Pausa activa antes del siguiente bloque de foco.',
                    'block_type'  => 'rest',
                    'goal_id'     => null,
                    'start_at'    => $date . 'T10:30:00',
                    'end_at'      => $date . 'T10:45:00',
                ],
                [
                    'title'       => 'Revisar emails y tareas administrativas',
                    'description' => 'Cerrar pendientes ligeros para liberar atención.',
                    'block_type'  => 'admin',
                    'goal_id'     => null,
                    'start_at'    => $date . 'T11:00:00',
                    'end_at'      => $date . 'T12:00:00',
                ],
            ],
            'rationale' => 'Dry-run sintético: 1 deep_work + descanso + admin ligero.',
        ], JSON_UNESCAPED_UNICODE);

        return [
            'raw' => [
                'id'      => 'dry-run',
                'model'   => $this->model,
                'content' => [['type' => 'text', 'text' => $fakeJson]],
                'usage'   => ['input_tokens' => 0, 'output_tokens' => 0],
            ],
            'text'       => $fakeJson,
            'tokens_in'  => 0,
            'tokens_out' => 0,
            'latency_ms' => 0,
        ];
    }
}
