<?php

namespace App\Rules;

use App\Support\SriLankaFormat;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SriLankanNic implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! SriLankaFormat::isValidNic($value)) {
            $fail('Enter a valid Sri Lankan NIC (old: 123456789V or new: 199012345678).');
        }
    }
}
