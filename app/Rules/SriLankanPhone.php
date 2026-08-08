<?php

namespace App\Rules;

use App\Support\SriLankaFormat;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SriLankanPhone implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (! is_string($value) || ! SriLankaFormat::isValidPhone($value)) {
            $fail('Enter a valid Sri Lankan phone number (e.g. 0112345678 or 0771234567).');
        }
    }
}
