<?php

namespace App\Http\Requests\Admin;

use App\Models\Faq;
use Illuminate\Foundation\Http\FormRequest;

class FaqRequest extends FormRequest
{
    public function authorize(): bool
    {
        $faq = $this->route('faq');

        return $faq ? $this->user()->can('update', $faq) : $this->user()->can('create', Faq::class);
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }

    public function rules(): array
    {
        return [
            'question' => ['required', 'string', 'max:500'],
            'answer' => ['nullable', 'string', 'max:5000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['boolean'],
        ];
    }

    public function faqData(): array
    {
        $data = $this->validated();
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }
}
