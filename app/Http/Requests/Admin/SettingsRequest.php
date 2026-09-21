<?php

namespace App\Http\Requests\Admin;

use App\Support\Settings\Settings;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Rules are derived from the group's schema in config/settings.php. */
class SettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('settings.update');
    }

    public function rules(): array
    {
        $schema = app(Settings::class)->schema($this->route('group'));
        $rules = ['values' => ['nullable', 'array']];

        foreach ($schema['keys'] as $key => $definition) {
            $rules['values.'.$key] = match ($definition['type']) {
                'boolean' => ['nullable', 'boolean'],
                'integer' => ['nullable', 'integer'],
                'media' => ['nullable', 'integer', Rule::exists('media', 'id')],
                'url' => ['nullable', 'string', 'url:http,https', 'max:2048'],
                'email' => ['nullable', 'string', 'email', 'max:255'],
                'text' => ['nullable', 'string', 'max:5000'],
                'json' => ['nullable', 'json'],
                default => ['nullable', 'string', 'max:500'],
            };
        }

        return $rules;
    }

    /** @return array<string, mixed> short key => value (blank → null) */
    public function values(): array
    {
        $schema = app(Settings::class)->schema($this->route('group'));
        $values = [];

        foreach (array_keys($schema['keys']) as $key) {
            $value = $this->input('values.'.$key);
            $values[$key] = ($value === '' || $value === null) ? null : $value;
        }

        return $values;
    }
}
