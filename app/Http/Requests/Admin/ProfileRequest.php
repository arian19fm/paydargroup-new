<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * A signed-in staff member editing their own account. Changing the e-mail
 * or the password requires the current password; roles and the active flag
 * are not editable here (users.manage covers those).
 */
class ProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['email' => strtolower(trim((string) $this->input('email')))]);
    }

    public function rules(): array
    {
        $sensitive = $this->filled('password') || $this->input('email') !== $this->user()->email;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user())],
            'current_password' => [$sensitive ? 'required' : 'nullable', 'current_password'],
            'password' => ['nullable', 'string', 'confirmed', UserRequest::passwordRule()],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => __('admin.fields.name'),
            'email' => __('admin.fields.email'),
            'current_password' => __('admin.fields.current_password'),
            'password' => __('admin.fields.password'),
        ];
    }

    public function userData(): array
    {
        $data = $this->safe()->only(['name', 'email']);

        if ($this->filled('password')) {
            $data['password'] = $this->validated('password');
        }

        return $data;
    }
}
