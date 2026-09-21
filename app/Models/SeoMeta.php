<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Polymorphic SEO overrides. Every column is optional: a null means "use
 * the content's own value" (see SeoManager::fromModel). Fallback values are
 * never copied into this table.
 */
class SeoMeta extends Model
{
    use HasFactory;

    protected $table = 'seo_meta';

    public const FIELDS = [
        'title', 'description', 'canonical_url', 'robots_index', 'robots_follow',
        'og_title', 'og_description', 'og_image_id',
        'twitter_title', 'twitter_description', 'twitter_image_id', 'schema_type',
    ];

    protected $fillable = self::FIELDS;

    protected function casts(): array
    {
        return [
            'robots_index' => 'boolean',
            'robots_follow' => 'boolean',
        ];
    }

    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }

    public function ogImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'og_image_id');
    }

    public function twitterImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'twitter_image_id');
    }

    /**
     * Normalise a form payload: trims strings, blanks become null, booleans
     * default to true (index/follow) when absent.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function normalize(array $data): array
    {
        $out = [];

        foreach (self::FIELDS as $field) {
            $value = $data[$field] ?? null;

            if (in_array($field, ['robots_index', 'robots_follow'], true)) {
                $out[$field] = array_key_exists($field, $data) ? filter_var($value, FILTER_VALIDATE_BOOL) : true;

                continue;
            }

            if (is_string($value)) {
                $value = trim($value);
            }

            $out[$field] = ($value === '' || $value === null) ? null : $value;
        }

        return $out;
    }

    /** True when no override is set (indexing flags at their defaults). */
    public static function isEmpty(array $attributes): bool
    {
        foreach ($attributes as $field => $value) {
            if (in_array($field, ['robots_index', 'robots_follow'], true)) {
                if ($value === false) {
                    return false;
                }

                continue;
            }

            if ($value !== null) {
                return false;
            }
        }

        return true;
    }
}
