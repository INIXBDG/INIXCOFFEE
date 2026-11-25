<?php

namespace App\Services;

class ExcelFormulaEvaluator
{
    /**
     * Parse nilai nominal atau formula aritmatika sederhana dari sel Excel tanpa eval().
     */
    public function parseAmount(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $value = trim((string) $value);

        if (str_starts_with($value, '=')) {
            $formula = substr($value, 1);

            if (preg_match('/^[0-9+\-*\/().\s]+$/', $formula)) {
                try {
                    return $this->evaluateSimpleArithmetic($formula);
                } catch (\Throwable) {
                    return 0.0;
                }
            }
        }

        $clean = preg_replace('/[^0-9.,-]/', '', $value);

        $lastComma = strrpos($clean, ',');
        $lastDot = strrpos($clean, '.');

        if ($lastComma !== false && $lastDot !== false) {
            if ($lastComma > $lastDot) {
                $clean = str_replace('.', '', $clean);
                $clean = str_replace(',', '.', $clean);
            } else {
                $clean = str_replace(',', '', $clean);
            }
        } elseif ($lastComma !== false) {
            if (preg_match('/,[0-9]{3}$/', $clean)) {
                $clean = str_replace(',', '', $clean);
            } else {
                $clean = str_replace(',', '.', $clean);
            }
        } elseif ($lastDot !== false) {
            if (preg_match('/\.[0-9]{3}$/', $clean)) {
                $clean = str_replace('.', '', $clean);
            }
        }

        return $clean !== '' ? abs((float) $clean) : 0.0;
    }

    /**
     * Recursive-descent arithmetic parser untuk ekspresi angka, +, -, *, /, dan tanda kurung.
     */
    public function evaluateSimpleArithmetic(string $expression): float
    {
        preg_match_all('/\d+(?:\.\d+)?|\.\d+|[+\-*\/()]|\S/', $expression, $matches);
        $tokens = $matches[0] ?? [];
        $pos = 0;
        $depth = 0;

        if (empty($tokens)) {
            return 0.0;
        }

        $parseExpression = null;
        $parseTerm = null;
        $parseUnary = null;
        $parsePrimary = null;

        $parseExpression = function () use (&$tokens, &$pos, &$parseTerm): float {
            $value = $parseTerm();
            while (isset($tokens[$pos]) && ($tokens[$pos] === '+' || $tokens[$pos] === '-')) {
                $op = $tokens[$pos++];
                $rhs = $parseTerm();
                $value = $op === '+' ? $value + $rhs : $value - $rhs;
            }
            return $value;
        };

        $parseTerm = function () use (&$tokens, &$pos, &$parseUnary): float {
            $value = $parseUnary();
            while (isset($tokens[$pos]) && ($tokens[$pos] === '*' || $tokens[$pos] === '/')) {
                $op = $tokens[$pos++];
                $rhs = $parseUnary();
                if ($op === '/') {
                    if ($rhs == 0.0) {
                        throw new \RuntimeException('Division by zero');
                    }
                    $value /= $rhs;
                } else {
                    $value *= $rhs;
                }
            }
            return $value;
        };

        $parseUnary = function () use (&$tokens, &$pos, &$parseUnary, &$parsePrimary, &$depth): float {
            if (++$depth > 64) {
                throw new \RuntimeException('Expression too deeply nested');
            }
            try {
                if (isset($tokens[$pos]) && ($tokens[$pos] === '+' || $tokens[$pos] === '-')) {
                    $op = $tokens[$pos++];
                    $val = $parseUnary();
                    return $op === '-' ? -$val : $val;
                }
                return $parsePrimary();
            } finally {
                $depth--;
            }
        };

        $parsePrimary = function () use (&$tokens, &$pos, &$parseExpression): float {
            if (!isset($tokens[$pos])) {
                throw new \RuntimeException('Unexpected end of expression');
            }

            $token = $tokens[$pos];
            if ($token === '(') {
                $pos++;
                $value = $parseExpression();
                if (!isset($tokens[$pos]) || $tokens[$pos] !== ')') {
                    throw new \RuntimeException('Unmatched parenthesis');
                }
                $pos++;
                return $value;
            }

            if (is_numeric($token)) {
                $pos++;
                return (float) $token;
            }

            throw new \RuntimeException('Unexpected token in expression');
        };

        $result = $parseExpression();
        if ($pos !== count($tokens)) {
            throw new \RuntimeException('Unexpected trailing tokens in expression');
        }

        return (float) $result;
    }
}
