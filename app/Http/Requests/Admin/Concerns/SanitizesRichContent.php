<?php

namespace App\Http\Requests\Admin\Concerns;

use App\Support\Content\HtmlContent;

/**
 * Rich-editor fields (docs/CMS.md §3): the submitted HTML is sanitised
 * before validation, so what is validated and stored is exactly what the
 * site renders, and an editor holding only empty markup counts as empty.
 */
trait SanitizesRichContent
{
    protected function sanitizeRichContent(string ...$fields): void
    {
        foreach ($fields as $field) {
            $value = $this->input($field);

            // Oversized input is left as is for richContentRules() to reject.
            if (is_string($value) && strlen($value) <= HtmlContent::MAX_BYTES) {
                $this->merge([$field => HtmlContent::sanitize($value) ?: null]);
            }
        }
    }

    /** @param  int  $maxBytes  the column's capacity (TEXT = 65 535, LONGTEXT capped by the sanitiser) */
    protected function richContentRules(bool $required = false, int $maxBytes = HtmlContent::MAX_BYTES): array
    {
        return [
            $required ? 'required' : 'nullable',
            'string',
            function (string $attribute, mixed $value, \Closure $fail) use ($maxBytes) {
                if (is_string($value) && strlen($value) > $maxBytes) {
                    $fail(__('admin.editor.too_long'));
                }
            },
        ];
    }
}
