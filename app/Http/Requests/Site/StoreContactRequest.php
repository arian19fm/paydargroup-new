<?php

namespace App\Http\Requests\Site;

use App\Support\Localization\PersianNumbers;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation for the public contact form. Phone digits typed in Persian or
 * Arabic-Indic are normalised to ASCII before validation and storage. The
 * `website` field is a honeypot: real users never see it, so any value
 * means a bot and the request is rejected as invalid.
 */
class StoreContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => preg_replace('/[\s\-()]+/u', '', PersianNumbers::toLatin((string) $this->input('phone', ''))),
        ]);
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:120'],
            'phone' => ['required', 'string', 'regex:/^\+?\d{8,15}$/'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
            'website' => ['prohibited'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'name' => __('home.contact.name'),
            'phone' => __('home.contact.phone'),
            'message' => __('home.contact.message'),
        ];
    }
}
