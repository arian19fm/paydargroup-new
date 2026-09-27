<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Support\Contact\GoogleMapsEmbed;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;

/**
 * Contact page (Figma 258:460 / 258:622): the shared request form, the
 * contact channels from Settings → contact and the map — a live Google
 * map embedded from the configured link, else the static map image.
 * Values that are not configured are simply not rendered.
 */
class ContactPageController extends Controller
{
    public function __invoke(): View
    {
        seo()->title(__('contact.title'))
            ->description(__('contact.description'))
            ->breadcrumbs([
                ['label' => __('nav.home'), 'url' => route('home')],
                ['label' => __('contact.title')],
            ]);

        $mapId = (int) settings('contact.map_image_media_id');
        $map = null;

        if ($mapId > 0) {
            try {
                $map = Media::query()->find($mapId);
            } catch (QueryException) {
                $map = null;
            }

            if ($map && ! $map->isImage()) {
                $map = null;
            }
        }

        $mapUrl = trim((string) settings('contact.map_url')) ?: null;

        return view('site.contact', [
            'address' => settings('contact.address'),
            'email' => settings('contact.email'),
            'phone' => settings('contact.phone'),
            'hours' => settings('contact.working_hours'),
            'map' => $map,
            'mapUrl' => $mapUrl,
            'mapEmbed' => $mapUrl ? GoogleMapsEmbed::fromUrl($mapUrl) : null,
        ]);
    }
}
