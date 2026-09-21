<?php

namespace App\Http\Requests\Admin\Concerns;

use App\Enums\ContentStatus;
use Illuminate\Validation\Rule;

/**
 * Status/publication rules and the "may this user publish?" check shared by
 * Page and Article requests.
 */
trait ValidatesPublishing
{
    protected function publishingRules(): array
    {
        return [
            'status' => ['required', Rule::enum(ContentStatus::class)],
            'published_at' => ['nullable', 'date'],
        ];
    }

    protected function wantsPublished(): bool
    {
        return $this->input('status') === ContentStatus::Published->value;
    }

    /**
     * Publication payload: published items get a date (defaulting to now);
     * drafts keep whatever date was entered for later scheduling.
     */
    protected function publishingData(): array
    {
        $publishedAt = $this->validated('published_at');

        if ($this->wantsPublished() && ! $publishedAt) {
            $publishedAt = now();
        }

        return [
            'status' => $this->validated('status'),
            'published_at' => $publishedAt,
        ];
    }
}
