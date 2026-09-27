<?php

namespace App\Models;

use App\Models\Concerns\HasAuditFields;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** One opening on the careers page: title, category badge, blurb, type, location and where to apply. */
class JobOpening extends Model
{
    use HasAuditFields, HasFactory;

    /** Category badge tones (Figma job post badges): key => [text, background]. */
    public const TONES = [
        'blue' => ['#3c8afd', '#e4e7ed'],
        'green' => ['#68a62b', '#eaede4'],
        'orange' => ['#f97316', '#fcf2eb'],
        'emerald' => ['#067647', '#ecfdf3'],
        'amber' => ['#d98b06', '#fcf6ea'],
        'red' => ['#ef4444', '#fbeeee'],
        'dark' => ['#172617', '#ecefed'],
    ];

    protected $fillable = [
        'title', 'category', 'tone', 'description', 'body', 'specs', 'employment_type', 'location', 'apply_url', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return ['sort_order' => 'integer', 'is_active' => 'boolean', 'specs' => 'array'];
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    /**
     * Spec rows shown under the description (label/value pairs; rows
     * without a label are dropped).
     *
     * @return list<array{label: string, value: string}>
     */
    public function specList(): array
    {
        $rows = [];

        foreach ($this->specs ?? [] as $row) {
            $label = trim((string) ($row['label'] ?? ''));

            if ($label !== '') {
                $rows[] = ['label' => $label, 'value' => trim((string) ($row['value'] ?? ''))];
            }
        }

        return $rows;
    }

    /** Specs as form text: one per line, "label | value". */
    public function specsAsText(): string
    {
        return implode(PHP_EOL, array_map(fn (array $s) => $s['label'].' | '.$s['value'], $this->specList()));
    }

    /** Parse the form text back into rows — the inverse of specsAsText(). */
    public static function parseSpecs(string $text): array
    {
        $rows = [];

        foreach (preg_split('/\R/u', $text) as $line) {
            [$label, $value] = array_pad(array_map('trim', explode('|', $line, 2)), 2, '');

            if ($label !== '') {
                $rows[] = ['label' => $label, 'value' => $value];
            }
        }

        return $rows;
    }

    /** Whether the detail modal has anything beyond the card fields. */
    public function hasDetails(): bool
    {
        return trim((string) $this->body) !== '' || $this->specList() !== [];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderByDesc('id');
    }

    public function toneKey(): string
    {
        return array_key_exists($this->tone, self::TONES) ? $this->tone : 'blue';
    }

    /** The apply link as an href: mailto for e-mail addresses, the URL otherwise. */
    public function applyHref(): ?string
    {
        $value = trim((string) $this->apply_url);

        if ($value === '') {
            return null;
        }

        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return 'mailto:'.$value;
        }

        return $value;
    }
}
