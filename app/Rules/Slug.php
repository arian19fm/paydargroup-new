<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/** Lowercase-hyphen slug that is not reserved by the application. */
class Slug implements ValidationRule
{
    public function __construct(protected bool $checkReserved = true) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! preg_match(config('cms.slug_pattern'), $value)) {
            $fail(__('validation.custom.slug.format'));

            return;
        }

        if ($this->checkReserved && in_array($value, config('cms.reserved_slugs'), true)) {
            $fail(__('validation.custom.slug.reserved'));
        }
    }
}
