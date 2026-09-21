{{-- Page-width container. `fluid` removes the max width. --}}
@props(['fluid' => false])
<div {{ $attributes->class([$fluid ? 'container-fluid' : 'container']) }}>
    {{ $slot }}
</div>
