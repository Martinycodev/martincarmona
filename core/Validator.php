<?php

namespace Core;

/**
 * Validador de inputs para formularios POST.
 *
 * Uso básico:
 *   $v = Validator::make($_POST, [
 *       'nombre' => 'required|max_length:100',
 *       'fecha'  => 'required|date',
 *       'horas'  => 'required|numeric|min:0|max:24',
 *       'id'     => 'required|integer',
 *   ]);
 *
 *   if (!$v->passes()) {
 *       // $v->errors()       → array con todos los errores por campo
 *       // $v->firstError('nombre') → primer error de un campo
 *   }
 */
class Validator
{
    private array $errors = [];

    // -------------------------------------------------------------------------
    // API pública
    // -------------------------------------------------------------------------

    /**
     * Factory estático para uso en una sola línea.
     */
    public static function make(array $data, array $rules): self
    {
        $instance = new self();
        $instance->validate($data, $rules);
        return $instance;
    }

    /**
     * Valida $data contra $rules.
     * Devuelve true si pasa todas las reglas, false si hay errores.
     */
    public function validate(array $data, array $rules): bool
    {
        $this->errors = [];

        foreach ($rules as $field => $ruleString) {
            $value     = $data[$field] ?? null;
            $ruleList  = explode('|', $ruleString);

            foreach ($ruleList as $rule) {
                [$ruleName, $param] = array_pad(explode(':', $rule, 2), 2, null);

                // Si el campo está vacío y la regla no es 'required', la saltamos
                // (evita errores en cascada para campos opcionales vacíos)
                if ($ruleName !== 'required' && ($value === null || $value === '')) {
                    continue;
                }

                $error = $this->applyRule($field, $value, $ruleName, $param);
                if ($error !== null) {
                    $this->errors[$field][] = $error;
                }
            }
        }

        return empty($this->errors);
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    /** Todos los errores indexados por campo. */
    public function errors(): array
    {
        return $this->errors;
    }

    /** Primer error de un campo concreto, o null si no hay. */
    public function firstError(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    /** Todos los errores en un array plano (útil para respuestas JSON). */
    public function allErrors(): array
    {
        $flat = [];
        foreach ($this->errors as $messages) {
            foreach ($messages as $msg) {
                $flat[] = $msg;
            }
        }
        return $flat;
    }

    // -------------------------------------------------------------------------
    // Reglas
    // -------------------------------------------------------------------------

    private function applyRule(string $field, mixed $value, string $rule, ?string $param): ?string
    {
        $label = $this->label($field);

        switch ($rule) {

            case 'required':
                if ($value === null || trim((string) $value) === '') {
                    return "El campo {$label} es obligatorio.";
                }
                break;

            case 'date':
                // Acepta YYYY-MM-DD
                $d = \DateTime::createFromFormat('Y-m-d', $value);
                if (!$d || $d->format('Y-m-d') !== $value) {
                    return "El campo {$label} debe ser una fecha válida (AAAA-MM-DD).";
                }
                break;

            case 'numeric':
                if (!is_numeric($value)) {
                    return "El campo {$label} debe ser un número.";
                }
                break;

            case 'integer':
                if (filter_var($value, FILTER_VALIDATE_INT) === false) {
                    return "El campo {$label} debe ser un número entero.";
                }
                break;

            case 'min':
                if ($param === null) break;
                if (!is_numeric($value) || (float) $value < (float) $param) {
                    return "El campo {$label} debe ser mayor o igual a {$param}.";
                }
                break;

            case 'max':
                if ($param === null) break;
                if (!is_numeric($value) || (float) $value > (float) $param) {
                    return "El campo {$label} debe ser menor o igual a {$param}.";
                }
                break;

            case 'max_length':
                if ($param === null) break;
                if (mb_strlen((string) $value) > (int) $param) {
                    return "El campo {$label} no puede superar los {$param} caracteres.";
                }
                break;

            case 'email':
                if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
                    return "El campo {$label} debe ser un email válido.";
                }
                break;

            default:
                // Regla desconocida — ignorar silenciosamente
                break;
        }

        return null;
    }

    /**
     * Convierte snake_case/camelCase a texto legible para los mensajes de error.
     * Ej: "fecha_inicio" → "fecha inicio", "nombreCompleto" → "nombre completo"
     */
    private function label(string $field): string
    {
        // camelCase → snake_case
        $label = preg_replace('/([A-Z])/', ' $1', $field);
        // snake_case → espacios
        $label = str_replace('_', ' ', $label ?? $field);
        return mb_strtolower(trim($label));
    }
}
