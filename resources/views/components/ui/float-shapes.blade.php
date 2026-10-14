{{--
    Floating geometry: a few faint, slowly drifting squares behind a
    section's content (components/_float.scss, site/float-shapes.js).
    Purely decorative — aria-hidden, pointer-events none, clipped to the
    section. The host section needs the `pg-float-host` class and the
    component goes in as its first child.

    Props
      shapes   list of shapes; each: top|bottom and start|end (CSS lengths,
               logical so RTL mirrors them), size (px, scaled down below
               lg), dur (s, 20–40), delay (s, negative to desynchronise),
               rot (deg), reverse (bool, drifts the other way), o
               (per-shape opacity override)
      mobile   how many of the shapes stay below lg (the first N); the
               rest are desktop-only
      variant  light (navy on light surfaces; blue in the dark theme) or
               dark (white on dark surfaces)
      opacity  base opacity for the whole layer (default in CSS: .1)
      speed    multiplier for every duration (.5 = twice as slow)
--}}
@props([
    'shapes' => [],
    'mobile' => 1,
    'variant' => 'light',
    'opacity' => null,
    'speed' => 1,
])
@php
    $sides = ['top', 'bottom'];
    $inline = ['start' => 'inset-inline-start', 'end' => 'inset-inline-end'];
@endphp
<div {{ $attributes->class(['pg-float', 'pg-float--'.$variant]) }} aria-hidden="true" @if ($opacity !== null) style="--pg-float-opacity: {{ $opacity }}" @endif data-float>
    @foreach ($shapes as $i => $shape)
        @php
            $style = [];
            foreach ($sides as $side) {
                if (isset($shape[$side])) {
                    $style[] = $side.': '.$shape[$side];
                }
            }
            foreach ($inline as $key => $property) {
                if (isset($shape[$key])) {
                    $style[] = $property.': '.$shape[$key];
                }
            }
            $style[] = '--size: '.(int) ($shape['size'] ?? 96).'px';
            $style[] = '--dur: '.round(($shape['dur'] ?? 30) / max($speed, .1), 1).'s';
            $style[] = '--delay: '.($shape['delay'] ?? 0).'s';
            $style[] = '--rot: '.($shape['rot'] ?? 0).'deg';
            if (isset($shape['o'])) {
                $style[] = '--pg-float-opacity: '.$shape['o'];
            }
        @endphp
        <span class="pg-float__shape @if ($i >= $mobile) pg-float__shape--wide @endif @if (! empty($shape['reverse'])) pg-float__shape--reverse @endif" style="{{ implode('; ', $style) }}"></span>
    @endforeach
</div>
