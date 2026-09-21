<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $target = $this->route('user');

        if (! $target) {
            return $this->user()->can('create', User::class);
        }

        return $this->user()->can('update', $target) && $this->user()->can('assignRoles', $target);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim((string) $this->input('email'))),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        $target = $this->route('user');
        $assignable = $this->user()->isSuperAdmin()
            ? ['super_admin', 'admin', 'editor']
            : ['admin', 'editor'];

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($target)],
            'password' => [$target ? 'nullable' : 'required', 'string', 'confirmed', self::passwordRule()],
            'is_active' => ['boolean'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['string', Rule::in($assignable)],
        ];
    }

    public static function passwordRule(): Password
    {
        // No "uncompromised" check: it calls an external API, which may be
        // unreachable and would make account creation depend on it.
        return Password::min(12)->letters()->numbers();
    }

    public function userData(): array
    {
        $data = $this->safe()->only(['name', 'email', 'is_active']);

        if ($this->filled('password')) {
            $data['password'] = $this->validated('password');
        }

        return $data;
    }

    /** @return list<string> */
    public function roles(): array
    {
        return array_values($this->validated('roles'));
    }
}
