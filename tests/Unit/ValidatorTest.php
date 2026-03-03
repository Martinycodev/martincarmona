<?php

namespace Tests\Unit;

use Core\Validator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    // -------------------------------------------------------------------------
    // required
    // -------------------------------------------------------------------------

    public function test_required_passes_with_value(): void
    {
        $v = Validator::make(['nombre' => 'Juan'], ['nombre' => 'required']);
        $this->assertTrue($v->passes());
    }

    public function test_required_fails_with_empty_string(): void
    {
        $v = Validator::make(['nombre' => ''], ['nombre' => 'required']);
        $this->assertTrue($v->fails());
        $this->assertNotEmpty($v->firstError('nombre'));
    }

    public function test_required_fails_with_missing_field(): void
    {
        $v = Validator::make([], ['nombre' => 'required']);
        $this->assertTrue($v->fails());
    }

    public function test_required_fails_with_whitespace_only(): void
    {
        $v = Validator::make(['nombre' => '   '], ['nombre' => 'required']);
        $this->assertTrue($v->fails());
    }

    public function test_required_passes_with_zero_string(): void
    {
        $v = Validator::make(['cantidad' => '0'], ['cantidad' => 'required']);
        $this->assertTrue($v->passes());
    }

    // -------------------------------------------------------------------------
    // date
    // -------------------------------------------------------------------------

    public function test_date_passes_with_valid_date(): void
    {
        $v = Validator::make(['fecha' => '2026-03-01'], ['fecha' => 'date']);
        $this->assertTrue($v->passes());
    }

    public function test_date_fails_with_invalid_format(): void
    {
        $v = Validator::make(['fecha' => '01/03/2026'], ['fecha' => 'date']);
        $this->assertTrue($v->fails());
    }

    public function test_date_fails_with_impossible_date(): void
    {
        $v = Validator::make(['fecha' => '2026-02-30'], ['fecha' => 'date']);
        $this->assertTrue($v->fails());
    }

    public function test_date_fails_with_text(): void
    {
        $v = Validator::make(['fecha' => 'hoy'], ['fecha' => 'date']);
        $this->assertTrue($v->fails());
    }

    public function test_date_skipped_when_field_empty(): void
    {
        // Campo no requerido y vacío → la regla date se salta
        $v = Validator::make(['fecha' => ''], ['fecha' => 'date']);
        $this->assertTrue($v->passes());
    }

    // -------------------------------------------------------------------------
    // numeric
    // -------------------------------------------------------------------------

    public function test_numeric_passes_with_integer(): void
    {
        $v = Validator::make(['horas' => '8'], ['horas' => 'numeric']);
        $this->assertTrue($v->passes());
    }

    public function test_numeric_passes_with_float(): void
    {
        $v = Validator::make(['horas' => '7.5'], ['horas' => 'numeric']);
        $this->assertTrue($v->passes());
    }

    public function test_numeric_fails_with_text(): void
    {
        $v = Validator::make(['horas' => 'ocho'], ['horas' => 'numeric']);
        $this->assertTrue($v->fails());
    }

    // -------------------------------------------------------------------------
    // integer
    // -------------------------------------------------------------------------

    public function test_integer_passes_with_whole_number(): void
    {
        $v = Validator::make(['id' => '42'], ['id' => 'integer']);
        $this->assertTrue($v->passes());
    }

    public function test_integer_fails_with_float(): void
    {
        $v = Validator::make(['id' => '3.14'], ['id' => 'integer']);
        $this->assertTrue($v->fails());
    }

    public function test_integer_fails_with_text(): void
    {
        $v = Validator::make(['id' => 'abc'], ['id' => 'integer']);
        $this->assertTrue($v->fails());
    }

    // -------------------------------------------------------------------------
    // min
    // -------------------------------------------------------------------------

    public function test_min_passes_when_value_equals_min(): void
    {
        $v = Validator::make(['horas' => '0'], ['horas' => 'numeric|min:0']);
        $this->assertTrue($v->passes());
    }

    public function test_min_passes_when_value_above_min(): void
    {
        $v = Validator::make(['horas' => '5'], ['horas' => 'numeric|min:0']);
        $this->assertTrue($v->passes());
    }

    public function test_min_fails_when_value_below_min(): void
    {
        $v = Validator::make(['horas' => '-1'], ['horas' => 'numeric|min:0']);
        $this->assertTrue($v->fails());
    }

    // -------------------------------------------------------------------------
    // max
    // -------------------------------------------------------------------------

    public function test_max_passes_when_value_equals_max(): void
    {
        $v = Validator::make(['horas' => '24'], ['horas' => 'numeric|max:24']);
        $this->assertTrue($v->passes());
    }

    public function test_max_fails_when_value_exceeds_max(): void
    {
        $v = Validator::make(['horas' => '25'], ['horas' => 'numeric|max:24']);
        $this->assertTrue($v->fails());
    }

    // -------------------------------------------------------------------------
    // max_length
    // -------------------------------------------------------------------------

    public function test_max_length_passes_within_limit(): void
    {
        $v = Validator::make(['nombre' => 'Juan'], ['nombre' => 'max_length:100']);
        $this->assertTrue($v->passes());
    }

    public function test_max_length_fails_exceeding_limit(): void
    {
        $v = Validator::make(['nombre' => str_repeat('a', 101)], ['nombre' => 'max_length:100']);
        $this->assertTrue($v->fails());
    }

    public function test_max_length_passes_exactly_at_limit(): void
    {
        $v = Validator::make(['nombre' => str_repeat('a', 100)], ['nombre' => 'max_length:100']);
        $this->assertTrue($v->passes());
    }

    public function test_max_length_handles_multibyte_correctly(): void
    {
        // "ñ" ocupa 1 carácter multibyte, no 2 bytes
        $v = Validator::make(['nombre' => 'Ñoño'], ['nombre' => 'max_length:5']);
        $this->assertTrue($v->passes());
    }

    // -------------------------------------------------------------------------
    // email
    // -------------------------------------------------------------------------

    public function test_email_passes_with_valid_address(): void
    {
        $v = Validator::make(['email' => 'user@example.com'], ['email' => 'email']);
        $this->assertTrue($v->passes());
    }

    public function test_email_fails_without_at_sign(): void
    {
        $v = Validator::make(['email' => 'userexample.com'], ['email' => 'email']);
        $this->assertTrue($v->fails());
    }

    public function test_email_fails_with_plain_text(): void
    {
        $v = Validator::make(['email' => 'no-es-email'], ['email' => 'email']);
        $this->assertTrue($v->fails());
    }

    // -------------------------------------------------------------------------
    // Combinaciones y comportamientos globales
    // -------------------------------------------------------------------------

    public function test_multiple_rules_all_pass(): void
    {
        $v = Validator::make(
            ['fecha' => '2026-01-15', 'horas' => '8', 'titulo' => 'Poda'],
            [
                'fecha'  => 'required|date',
                'horas'  => 'required|numeric|min:0|max:24',
                'titulo' => 'required|max_length:200',
            ]
        );
        $this->assertTrue($v->passes());
        $this->assertEmpty($v->errors());
    }

    public function test_multiple_rules_collects_all_errors(): void
    {
        $v = Validator::make(
            ['fecha' => 'no-es-fecha', 'horas' => 'mucho'],
            ['fecha' => 'required|date', 'horas' => 'required|numeric|min:0|max:24']
        );
        $this->assertTrue($v->fails());
        $this->assertArrayHasKey('fecha', $v->errors());
        $this->assertArrayHasKey('horas', $v->errors());
    }

    public function test_optional_empty_field_skips_other_rules(): void
    {
        // 'baja_ss' no es required, viene vacío → la regla 'date' no aplica
        $v = Validator::make(['baja_ss' => ''], ['baja_ss' => 'date']);
        $this->assertTrue($v->passes());
    }

    public function test_all_errors_returns_flat_array(): void
    {
        $v = Validator::make(
            [],
            ['nombre' => 'required', 'fecha' => 'required']
        );
        $flat = $v->allErrors();
        $this->assertCount(2, $flat);
        $this->assertIsString($flat[0]);
    }

    public function test_first_error_returns_null_when_field_valid(): void
    {
        $v = Validator::make(['nombre' => 'Juan'], ['nombre' => 'required']);
        $this->assertNull($v->firstError('nombre'));
    }

    public function test_make_static_factory_returns_validator(): void
    {
        $v = Validator::make([], []);
        $this->assertInstanceOf(Validator::class, $v);
    }

    public function test_validate_method_returns_bool(): void
    {
        $v = new Validator();
        $result = $v->validate(['x' => 'ok'], ['x' => 'required']);
        $this->assertTrue($result);
    }
}
