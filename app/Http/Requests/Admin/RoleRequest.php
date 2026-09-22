<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Role name + the set of permissions it grants. Names are free text (a
 * Persian label works) except the reserved root role; permissions must be
 * ones the seeder knows about (`web` guard).
 */
class RoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $role = $this->route('role');

        return $role ? $this->user()->can('update', $role) : $this->user()->can('create', Role::class);
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['name' => trim((string) $this->input('name'))]);
    }

    public function rules(): array
    {
        $role = $this->route('role');

        return [
            'name' => [
                'required', 'string', 'max:64',
                Rule::notIn([User::ROLE_SUPER_ADMIN]),
                Rule::unique('roles', 'name')->where('guard_name', 'web')->ignore($role),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')->where('guard_name', 'web')],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => __('admin.fields.role_name'),
            'permissions' => __('admin.fields.permissions'),
        ];
    }

    /** @return list<string> */
    public function permissions(): array
    {
        return array_values(array_unique($this->validated('permissions') ?? []));
    }

    /** All permissions grouped by resource prefix, for the form. */
    public static function grouped(): array
    {
        return Permission::query()->where('guard_name', 'web')->orderBy('name')->pluck('name')
            ->groupBy(fn (string $name) => explode('.', $name, 2)[0])
            ->map(fn ($names) => $names->values()->all())
            ->all();
    }
}
