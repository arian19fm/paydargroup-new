<?php

namespace App\Http\Requests\Admin;

use App\Models\JobOpening;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JobOpeningRequest extends FormRequest
{
    public function authorize(): bool
    {
        $job = $this->route('job');

        return $job ? $this->user()->can('update', $job) : $this->user()->can('create', JobOpening::class);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'apply_url' => trim((string) $this->input('apply_url')) ?: null,
            'specs' => JobOpening::parseSpecs((string) $this->input('specs_text', '')),
        ]);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'tone' => ['required', Rule::in(array_keys(JobOpening::TONES))],
            'description' => ['nullable', 'string', 'max:500'],
            'body' => ['nullable', 'string', 'max:20000'],
            'specs' => ['nullable', 'array', 'max:12'],
            'specs.*.label' => ['required', 'string', 'max:100'],
            'specs.*.value' => ['nullable', 'string', 'max:255'],
            'employment_type' => ['nullable', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:100'],
            // Optional: a page to apply on, or an e-mail address, instead of the built-in form.
            'apply_url' => ['nullable', 'string', 'max:500', function (string $attribute, mixed $value, \Closure $fail) {
                if (! filter_var($value, FILTER_VALIDATE_EMAIL) && ! (filter_var($value, FILTER_VALIDATE_URL) && preg_match('#^https?://#i', $value))) {
                    $fail(__('admin.jobs.apply_url_invalid'));
                }
            }],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['boolean'],
        ];
    }

    public function jobData(): array
    {
        $data = $this->validated();
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['specs'] = array_values($data['specs'] ?? []);

        return $data;
    }
}
