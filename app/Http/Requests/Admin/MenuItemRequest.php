<?php

namespace App\Http\Requests\Admin;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * A menu item must point at exactly one target: an internal page OR an
 * explicit URL — unless it has a `source` (automatic children), which
 * provides a default link, so the target may then be left empty. Parents
 * must belong to the same menu and an item can never be its own ancestor.
 */
class MenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('menu'));
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'url' => trim((string) $this->input('url')) ?: null,
            'page_id' => $this->input('page_id') ?: null,
            'source' => $this->input('source') ?: null,
            'parent_id' => $this->input('parent_id') ?: null,
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        /** @var Menu $menu */
        $menu = $this->route('menu');
        $item = $this->route('item');

        return [
            'label' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'string', 'max:2048', 'regex:#^(/[^\s]*|https?://[^\s]+|mailto:[^\s]+|tel:[^\s]+|\#[^\s]*)$#i'],
            'page_id' => ['nullable', 'integer', Rule::exists('pages', 'id')->whereNull('deleted_at')],
            'source' => ['nullable', Rule::in(MenuItem::SOURCES)],
            'parent_id' => [
                'nullable', 'integer',
                Rule::exists('menu_items', 'id')->where('menu_id', $menu->id),
                $item ? Rule::notIn([$item->id]) : 'nullable',
            ],
            'target' => ['required', Rule::in(MenuItem::TARGETS)],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $hasUrl = filled($this->input('url'));
            $hasPage = filled($this->input('page_id'));
            $hasSource = filled($this->input('source'));

            if (($hasUrl && $hasPage) || (! $hasUrl && ! $hasPage && ! $hasSource)) {
                $validator->errors()->add('url', __('validation.custom.menu_item.target'));
            }

            // Prevent cycles: the chosen parent may not be a descendant of this item.
            $item = $this->route('item');
            $parentId = $this->input('parent_id');

            if ($item && $parentId) {
                $ancestor = MenuItem::find($parentId);

                while ($ancestor) {
                    if ($ancestor->id === $item->id) {
                        $validator->errors()->add('parent_id', __('validation.custom.menu_item.cycle'));
                        break;
                    }

                    $ancestor = $ancestor->parent;
                }
            }
        });
    }

    public function itemData(): array
    {
        $data = $this->safe()->only(['label', 'url', 'page_id', 'source', 'parent_id', 'target', 'sort_order', 'is_active']);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }
}
