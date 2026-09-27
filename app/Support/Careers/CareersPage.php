<?php

namespace App\Support\Careers;

use App\Models\JobOpening;
use App\Models\Media;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\QueryException;

/**
 * Assembles the careers page: copy from Settings → careers with the
 * designed text (lang/{locale}/careers.php) as fallback, the four benefit
 * cards (settings text, designed icons), and the active job openings.
 */
class CareersPage
{
    public const PER_PAGE = 7;

    /** Benefit card icon per slot, in display order (Figma 341:52). */
    public const BENEFIT_ICONS = [
        1 => ['careers-book.svg'],
        2 => ['careers-tick.svg', 'careers-tick-ring.svg'],
        3 => ['careers-chart.svg'],
        4 => ['careers-moneys.svg'],
    ];

    /** @return array<string, mixed> */
    public function copy(): array
    {
        $text = fn (string $key) => trim((string) settings('careers.'.$key)) ?: __('careers.'.$key);

        return [
            'hero_eyebrow' => $text('hero_eyebrow'),
            'hero_title' => $text('hero_title'),
            'benefits_eyebrow' => $text('benefits_eyebrow'),
            'benefits_title_highlight' => $text('benefits_title_highlight'),
            'benefits_title' => $text('benefits_title'),
            'benefits_text' => $text('benefits_text'),
            'benefits_cta_label' => $text('benefits_cta_label'),
            'benefits_cta_url' => trim((string) settings('careers.benefits_cta_url')) ?: route('contact'),
            'jobs_eyebrow' => $text('jobs_eyebrow'),
            'jobs_title_highlight' => $text('jobs_title_highlight'),
            'jobs_title' => $text('jobs_title'),
            'jobs_text' => $text('jobs_text'),
            'jobs_empty' => $text('jobs_empty'),
            'apply' => __('careers.apply'),
        ];
    }

    /**
     * The four benefit cards; a slot whose title is blank in both settings
     * and the designed copy is skipped.
     *
     * @return list<array{title: string, text: string, icons: list<string>}>
     */
    public function benefits(): array
    {
        $cards = [];

        foreach (self::BENEFIT_ICONS as $slot => $icons) {
            $title = trim((string) settings("careers.benefit_{$slot}_title")) ?: __("careers.benefits.{$slot}.title");
            $text = trim((string) settings("careers.benefit_{$slot}_text")) ?: __("careers.benefits.{$slot}.text");

            if ($title !== '') {
                $cards[] = ['title' => $title, 'text' => $text, 'icons' => $icons];
            }
        }

        return $cards;
    }

    /** Hero photo from settings, else null (the designed photo is used). */
    public function heroImage(): ?Media
    {
        $id = (int) settings('careers.hero_image_media_id');

        if ($id <= 0) {
            return null;
        }

        try {
            $media = Media::query()->find($id);
        } catch (QueryException) {
            return null;
        }

        return $media?->isImage() ? $media : null;
    }

    public function jobs(): LengthAwarePaginator
    {
        return JobOpening::query()->active()->ordered()->paginate(self::PER_PAGE)->withQueryString();
    }
}
