<?php

namespace App\Rules;

use App\Support\SriLankaFormat;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SriLankanMobile implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (! is_string($value) || ! SriLankaFormat::isValidMobile($value)) {
            $fail('Enter a valid Sri Lankan mobile number (e.g. 0771234567 or +94771234567).');
        }
    }
}
