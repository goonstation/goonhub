<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Cidr implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('The :attribute must be a string.');

            return;
        }

        if (! preg_match('/^(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})\/?(\d{1,2})?$/', $value, $matches)) {
            $fail('The :attribute must be a valid IPv4 CIDR.');

            return;
        }

        $ip = $matches[1];
        $mask = isset($matches[2]) ? (int) $matches[2] : 0;

        if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $fail('The :attribute must contain a valid IPv4 address.');

            return;
        }

        if ($mask < 0 || $mask > 32) {
            $fail('The :attribute CIDR mask must be between 0 and 32.');
        }
    }
}
