{{-- First focusable element on the page; jumps keyboard users past the header. --}}
@props(['target' => '#main'])
<a href="{{ $target }}" class="pg-skip-link">{{ __('nav.skip_to_content') }}</a>
