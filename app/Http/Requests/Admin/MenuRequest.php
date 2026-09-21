<?php

namespace App\Http\Requests\Admin;

use App\Models\Menu;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        $menu = $this->route('menu');

        return $menu ? $this->user()->can('update', $menu) : $this->user()->can('create', Menu::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:64', 'regex:/^[a-z0-9_-]+$/', Rule::unique('menus', 'location')->ignore($this->route('menu'))],
        ];
    }
}
