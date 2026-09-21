{{--
    Session flash messages. Controllers flash one of: success, error,
    warning, info, status (treated as success). Rendered as dismissible
    Bootstrap alerts inside a polite live region.
--}}
@php
    $messages = collect([
        'success' => 'success',
        'status' => 'success',
        'error' => 'danger',
        'warning' => 'warning',
        'info' => 'info',
    ])->map(fn ($variant, $key) => session()->has($key) ? ['variant' => $variant, 'text' => session($key)] : null)->filter();
@endphp
@if ($messages->isNotEmpty())
<div {{ $attributes->class(['pg-flash']) }} role="status" aria-live="polite">
    @foreach ($messages as $message)
        <div class="alert alert-{{ $message['variant'] }} alert-dismissible fade show" role="alert">
            {{ $message['text'] }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('ui.close') }}"></button>
        </div>
    @endforeach
</div>
@endif
