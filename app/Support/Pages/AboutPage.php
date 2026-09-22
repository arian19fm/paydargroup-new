<?php

namespace App\Support\Pages;

use App\Models\Media;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;

/**
 * Extras of the "about" page template, read from Settings → about: the two
 * intro photos, the partner logo strip, the history block and the three
 * statistics. Media IDs that do not resolve to an image are dropped, so a
 * stale ID can never break the page.
 */
class AboutPage
{
    /** @return array<string, mixed> */
    public function data(): array
    {
        $partnerIds = array_values(array_filter(array_map(
            'intval',
            preg_split('/[\s,،]+/u', (string) settings('about.partner_media_ids'), -1, PREG_SPLIT_NO_EMPTY)
        )));

        $ids = array_filter([
            'image_1' => (int) settings('about.image_1_media_id'),
            'image_2' => (int) settings('about.image_2_media_id'),
            'history' => (int) settings('about.history_image_media_id'),
        ]);

        $media = $this->images([...array_values($ids), ...$partnerIds]);

        return [
            'image1' => $media->get($ids['image_1'] ?? 0),
            'image2' => $media->get($ids['image_2'] ?? 0),
            'partners' => collect($partnerIds)->map(fn (int $id) => $media->get($id))->filter()->values(),
            'history' => [
                'title' => settings('about.history_title'),
                'text' => settings('about.history_text'),
                'image' => $media->get($ids['history'] ?? 0),
            ],
            'stats' => collect([
                'clients' => settings('about.stat_clients'),
                'years' => settings('about.stat_years'),
                'companies' => settings('about.stat_companies'),
            ])->filter(fn ($v) => $v !== null && $v !== '')->map(fn ($v) => (int) $v),
        ];
    }

    /** @return Collection<int, Media> image media keyed by id */
    protected function images(array $ids): Collection
    {
        $ids = array_values(array_unique(array_filter($ids)));

        if ($ids === []) {
            return new Collection;
        }

        try {
            return Media::query()->whereIn('id', $ids)->get()
                ->filter(fn (Media $m) => $m->isImage())
                ->keyBy('id');
        } catch (QueryException) {
            return new Collection;
        }
    }
}
