<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Cpf implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! self::valido($value)) {
            $fail('Informe um CPF válido.');
        }
    }

    public static function valido(string $valor): bool
    {
        if (! preg_match('/^\d{3}\.?\d{3}\.?\d{3}-?\d{2}$/', $valor)) {
            return false;
        }

        $cpf = preg_replace('/\D/', '', $valor);

        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            $soma = 0;
            for ($i = 0; $i < $t; $i++) {
                $soma += (int) $cpf[$i] * (($t + 1) - $i);
            }
            if ((int) $cpf[$t] !== ((10 * $soma) % 11) % 10) {
                return false;
            }
        }

        return true;
    }
}
