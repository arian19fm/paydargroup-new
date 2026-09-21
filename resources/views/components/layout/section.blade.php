{{--
    Semantic page section with an optional heading. `level` sets the heading
    element (2 by default — the page's single H1 belongs to the page itself).
    Pass `id` to make the section linkable; the heading is then used as the
    section's accessible name.
--}}
@props(['heading' => null, 'level' => 2, 'id' => null])
@php
    $tag = 'h'.max(2, min(6, (int) $level));
    $headingId = $id ? $id.'-heading' : null;
@endphp
<section {{ $attributes->class(['pg-section']) }} @if ($id) id="{{ $id }}" aria-labelledby="{{ $headingId }}" @endif>
    <x-layout.container>
        @if ($heading)
            <{{ $tag }} @if ($headingId) id="{{ $headingId }}" @endif class="pg-section__heading">{{ $heading }}</{{ $tag }}>
        @endif
        {{ $slot }}
    </x-layout.container>
</section>
