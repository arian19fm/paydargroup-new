<?php

namespace Tests\Unit;

use App\Support\Contact\GoogleMapsEmbed;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleMapsEmbedTest extends TestCase
{
    public function test_place_page_url_uses_its_viewport_coordinates_and_zoom(): void
    {
        $embed = GoogleMapsEmbed::fromUrl('https://www.google.com/maps/place/Paydar/@35.7219,51.3347,17z/data=!3m1!4b1');

        $this->assertSame('https://www.google.com/maps?q=35.7219%2C51.3347&hl=fa&output=embed&z=17', $embed);
    }

    public function test_search_and_query_urls_and_embed_snippets_are_accepted(): void
    {
        $this->assertSame('https://www.google.com/maps?q=Tehran+Office&hl=fa&output=embed', GoogleMapsEmbed::fromUrl('https://maps.google.com/?q=Tehran+Office'));
        $this->assertSame('https://www.google.com/maps?q=Paydar+Group&hl=fa&output=embed', GoogleMapsEmbed::fromUrl('https://www.google.com/maps/search/Paydar+Group/'));
        $this->assertSame('https://www.google.com/maps/embed?pb=!1m18!2d51.3', GoogleMapsEmbed::fromUrl('<iframe src="https://www.google.com/maps/embed?pb=!1m18!2d51.3" width="600" height="450"></iframe>'));
        $this->assertSame('https://www.google.com/maps?q=Cafe&hl=fa&output=embed', GoogleMapsEmbed::fromUrl('google.com/maps/place/Cafe'));
    }

    public function test_non_google_links_and_blank_values_yield_nothing(): void
    {
        $this->assertNull(GoogleMapsEmbed::fromUrl('https://neshan.org/maps/places/xyz'));
        $this->assertNull(GoogleMapsEmbed::fromUrl('https://www.google.com/'));
        $this->assertNull(GoogleMapsEmbed::fromUrl(''));
        $this->assertNull(GoogleMapsEmbed::fromUrl(null));
        $this->assertNull(GoogleMapsEmbed::fromUrl('not a url'));
    }

    public function test_short_share_links_are_resolved_once_and_cached(): void
    {
        Cache::flush();
        Http::fake(['https://maps.app.goo.gl/abc123' => Http::response('', 302, ['Location' => 'https://www.google.com/maps/place/X/@35.7,51.4,15z/'])]);

        $expected = 'https://www.google.com/maps?q=35.7%2C51.4&hl=fa&output=embed&z=15';
        $this->assertSame($expected, GoogleMapsEmbed::fromUrl('https://maps.app.goo.gl/abc123'));
        $this->assertSame($expected, GoogleMapsEmbed::fromUrl('https://maps.app.goo.gl/abc123'));
        Http::assertSentCount(1);
    }

    public function test_unresolvable_short_link_fails_quietly(): void
    {
        Cache::flush();
        Http::fake(['https://maps.app.goo.gl/dead' => Http::response('', 500)]);

        $this->assertNull(GoogleMapsEmbed::fromUrl('https://maps.app.goo.gl/dead'));
    }
}
