@props(['title'])
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <h1 class="h3 mb-0">{{ $title }}</h1>
    @if (trim($slot))
        <div class="d-flex gap-2">{{ $slot }}</div>
    @endif
</div>
