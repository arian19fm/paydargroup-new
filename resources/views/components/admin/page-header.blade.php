@props(['title'])
<div class="pg-admin__page-header">
    <h1 class="pg-admin__title">{{ $title }}</h1>
    @if (trim($slot))
        <div class="pg-admin__actions">{{ $slot }}</div>
    @endif
</div>
