<?php

namespace App\Http\Requests\Admin;

use App\Models\Redirect;
use App\Support\Redirects\RedirectResolver;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class RedirectRequest extends FormRequest
{
    public function authorize(): bool
    {
        $redirect = $this->route('redirect');

        return $redirect ? $this->user()->can('update', $redirect) : $this->user()->can('create', Redirect::class);
    }

    protected function prepareForValidation(): void
    {
        $source = trim((string) $this->input('source_path'));

        $this->merge([
            'source_path' => $source === '' ? '' : RedirectResolver::normalizePath($source),
            'destination_url' => trim((string) $this->input('destination_url')),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        return [
            // A site-relative path: starts with "/", no scheme, no query/fragment, no whitespace.
            'source_path' => [
                'required', 'string', 'max:700', 'regex:#^/[^\s?\#]*$#',
                Rule::unique('redirects', 'source_path')->ignore($this->route('redirect')),
            ],
            // Either a site-relative path or an absolute http(s) URL.
            'destination_url' => ['required', 'string', 'max:2048', 'regex:#^(/[^\s]*|https?://[^\s]+)$#i'],
            'http_status' => ['required', 'integer', Rule::in(Redirect::STATUS_CODES)],
            'is_active' => ['boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $source = $this->input('source_path');
            $destination = $this->input('destination_url');

            if ($validator->errors()->hasAny(['source_path', 'destination_url'])) {
                return;
            }

            if (RedirectResolver::isProtected($source)) {
                $validator->errors()->add('source_path', __('validation.custom.redirect.protected'));
            }

            if ($source === '/') {
                $validator->errors()->add('source_path', __('validation.custom.redirect.root'));
            }

            if (app(RedirectResolver::class)->wouldLoop($source, $destination, $this->route('redirect')?->id)) {
                $validator->errors()->add('destination_url', __('validation.custom.redirect.loop'));
            }
        });
    }

    public function redirectData(): array
    {
        return $this->safe()->only(['source_path', 'destination_url', 'http_status', 'is_active']);
    }
}
