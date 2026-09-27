<?php

namespace App\Http\Requests\Site;

use App\Support\Localization\PersianNumbers;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

/**
 * Validation for the careers application form: a phone number (Persian
 * digits normalised) and a PDF/DOCX résumé of at most 5 MB. `website` is
 * the honeypot shared with the contact form.
 */
class StoreJobApplicationRequest extends FormRequest
{
    public const MAX_KILOBYTES = 5120;

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
            'phone' => ['required', 'string', 'regex:/^\+?\d{8,15}$/'],
            'resume' => ['required', File::types(['pdf', 'docx'])->max(self::MAX_KILOBYTES), 'mimetypes:application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'website' => ['prohibited'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'phone' => __('careers.form.phone'),
            'resume' => __('careers.form.resume'),
        ];
    }
}
