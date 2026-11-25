<?php

namespace Tests\Unit;

use App\Services\ExcelFormulaEvaluator;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ExcelFormulaEvaluatorTest extends TestCase
{
    private ExcelFormulaEvaluator $evaluator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->evaluator = new ExcelFormulaEvaluator();
    }

    public function test_evaluates_excel_arithmetic_formulas_without_eval(): void
    {
        $this->assertSame(294300.0, $this->evaluator->parseAmount('=75000+10000+65306-6+140000+4000'));
        $this->assertSame(14.0, $this->evaluator->parseAmount('=2 + 3 * 4'));
        $this->assertSame(20.0, $this->evaluator->parseAmount('=(2 + 3) * 4'));
        $this->assertSame(50.0, $this->evaluator->parseAmount('=-10 + 60'));
        $this->assertSame(25.5, $this->evaluator->parseAmount('=51 / 2'));
    }

    public function test_parses_indonesian_and_international_currency_strings(): void
    {
        $this->assertSame(294300.0, $this->evaluator->parseAmount('Rp 294.300,00'));
        $this->assertSame(294300.0, $this->evaluator->parseAmount('294,300.00'));
        $this->assertSame(75000.0, $this->evaluator->parseAmount('Rp75.000'));
        $this->assertSame(75000.0, $this->evaluator->parseAmount('75,000'));
        $this->assertSame(0.0, $this->evaluator->parseAmount(''));
        $this->assertSame(0.0, $this->evaluator->parseAmount(null));
    }

    public function test_rejects_division_by_zero_and_malicious_payloads_safely(): void
    {
        $this->assertSame(0.0, $this->evaluator->parseAmount('=100/0'));
        $this->assertSame(0.0, $this->evaluator->parseAmount("=system('id')"));

        $this->expectException(RuntimeException::class);
        $this->evaluator->evaluateSimpleArithmetic('100 / 0');
    }
}
