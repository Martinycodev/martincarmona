<?php

namespace App\Modules\Planner\Services;

/**
 * ScheduleValidator
 * -----------------
 * Valida que el JSON devuelto por la IA cumple el esquema y las reglas
 * de negocio del horario diario.
 *
 * Uso:
 *   $result = ScheduleValidator::validate($parsedJson);
 *   if (!$result['ok']) {
 *       // $result['errors'] es un array de strings legibles.
 *   }
 *
 * Decisión: validador manual sin librerías externas. Las reglas son
 * pocas, fijas y muy específicas del dominio; no merece la pena cargar
 * justinrainbow/json-schema solo para esto.
 */
class ScheduleValidator
{
    /** Tipos de bloque permitidos. Debe coincidir con el ENUM SQL. */
    private const BLOCK_TYPES = ['deep_work', 'admin', 'rest', 'meal', 'exercise', 'review'];

    /** Máximo de bloques deep_work por día (regla 2 del system prompt). */
    private const MAX_DEEP_WORK = 6;

    /** Duración mínima/máxima de un deep_work en minutos. */
    private const DEEP_WORK_MIN_MINUTES = 45;
    private const DEEP_WORK_MAX_MINUTES = 90;

    /**
     * @param mixed $data  Resultado de json_decode(..., true).
     * @return array{ok: bool, errors: string[]}
     */
    public static function validate(mixed $data): array
    {
        $errors = [];

        // ── Estructura raíz ────────────────────────────────────
        if (!is_array($data)) {
            return ['ok' => false, 'errors' => ['La raíz debe ser un objeto JSON.']];
        }

        if (!isset($data['date']) || !self::isValidDate($data['date'])) {
            $errors[] = 'Campo "date" ausente o con formato inválido (esperado YYYY-MM-DD).';
        }

        if (!isset($data['blocks']) || !is_array($data['blocks'])) {
            return ['ok' => false, 'errors' => array_merge($errors, ['Campo "blocks" ausente o no es un array.'])];
        }

        if (!isset($data['rationale']) || !is_string($data['rationale'])) {
            $errors[] = 'Campo "rationale" ausente o no es string.';
        } elseif (mb_strlen($data['rationale']) > 200) {
            $errors[] = 'Campo "rationale" excede 200 caracteres.';
        }

        // ── Cada bloque individual ─────────────────────────────
        $deepWorkCount = 0;
        $parsedBlocks  = [];

        foreach ($data['blocks'] as $i => $block) {
            $blockErrors = self::validateBlock($block, $i);
            if ($blockErrors !== []) {
                $errors = array_merge($errors, $blockErrors);
                continue;
            }

            // Si el bloque es válido, lo guardamos para la comprobación
            // de solapes (necesita timestamps).
            $parsedBlocks[] = [
                'index' => $i,
                'type'  => $block['block_type'],
                'start' => strtotime($block['start_at']),
                'end'   => strtotime($block['end_at']),
            ];

            if ($block['block_type'] === 'deep_work') {
                $deepWorkCount++;
                $minutes = (strtotime($block['end_at']) - strtotime($block['start_at'])) / 60;
                if ($minutes < self::DEEP_WORK_MIN_MINUTES || $minutes > self::DEEP_WORK_MAX_MINUTES) {
                    $errors[] = sprintf(
                        'Bloque #%d (deep_work) dura %d min, fuera del rango [%d-%d].',
                        $i, $minutes, self::DEEP_WORK_MIN_MINUTES, self::DEEP_WORK_MAX_MINUTES
                    );
                }
            }
        }

        // ── Reglas globales ────────────────────────────────────
        if ($deepWorkCount > self::MAX_DEEP_WORK) {
            $errors[] = sprintf(
                'Hay %d bloques deep_work, excede el máximo de %d.',
                $deepWorkCount, self::MAX_DEEP_WORK
            );
        }

        // Comprobar solapes ordenando por start.
        usort($parsedBlocks, fn(array $a, array $b): int => $a['start'] <=> $b['start']);
        for ($i = 1, $n = count($parsedBlocks); $i < $n; $i++) {
            $prev = $parsedBlocks[$i - 1];
            $curr = $parsedBlocks[$i];
            if ($curr['start'] < $prev['end']) {
                $errors[] = sprintf(
                    'Solape entre bloques #%d y #%d.',
                    $prev['index'], $curr['index']
                );
            }
        }

        return ['ok' => $errors === [], 'errors' => $errors];
    }

    /**
     * Validación de un único bloque. Devuelve la lista de errores
     * encontrados (vacía si todo OK).
     */
    private static function validateBlock(mixed $block, int $i): array
    {
        if (!is_array($block)) {
            return ["Bloque #{$i} no es un objeto."];
        }

        $errors = [];
        $required = ['title', 'description', 'block_type', 'goal_id', 'start_at', 'end_at'];
        foreach ($required as $key) {
            if (!array_key_exists($key, $block)) {
                $errors[] = "Bloque #{$i}: falta el campo \"{$key}\".";
            }
        }
        if ($errors !== []) {
            return $errors;
        }

        if (!is_string($block['title']) || $block['title'] === '') {
            $errors[] = "Bloque #{$i}: \"title\" debe ser string no vacío.";
        }
        if (!is_string($block['description'])) {
            $errors[] = "Bloque #{$i}: \"description\" debe ser string.";
        }
        if (!in_array($block['block_type'], self::BLOCK_TYPES, true)) {
            $errors[] = "Bloque #{$i}: \"block_type\" inválido (\"{$block['block_type']}\").";
        }
        if ($block['goal_id'] !== null && !is_int($block['goal_id'])) {
            $errors[] = "Bloque #{$i}: \"goal_id\" debe ser entero o null.";
        }
        if (!self::isValidDateTime($block['start_at'])) {
            $errors[] = "Bloque #{$i}: \"start_at\" con formato inválido (esperado YYYY-MM-DDTHH:MM:00).";
        }
        if (!self::isValidDateTime($block['end_at'])) {
            $errors[] = "Bloque #{$i}: \"end_at\" con formato inválido.";
        }

        // Solo comprobamos start < end si ambos son válidos.
        if ($errors === [] && strtotime($block['end_at']) <= strtotime($block['start_at'])) {
            $errors[] = "Bloque #{$i}: \"end_at\" debe ser posterior a \"start_at\".";
        }

        return $errors;
    }

    private static function isValidDate(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }
        $d = \DateTime::createFromFormat('Y-m-d', $value);
        return $d !== false && $d->format('Y-m-d') === $value;
    }

    private static function isValidDateTime(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }
        $d = \DateTime::createFromFormat('Y-m-d\TH:i:s', $value);
        return $d !== false && $d->format('Y-m-d\TH:i:s') === $value;
    }
}
