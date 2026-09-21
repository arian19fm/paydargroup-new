<?php

namespace App\Enums;

/**
 * Publication status for managed content. Deliberately minimal: a piece of
 * content is either a draft or published; scheduling is expressed through
 * published_at (published + future date = not yet public).
 */
enum ContentStatus: string
{
    case Draft = 'draft';
    case Published = 'published';

    public function label(): string
    {
        return __('admin.status.'.$this->value);
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
