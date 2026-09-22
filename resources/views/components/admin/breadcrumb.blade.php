{{-- Admin breadcrumb: [['label' => ..., 'url' => ...], ...]; dashboard is prepended. --}}
@props(['items' => []])
<nav aria-label="{{ __('nav.breadcrumb') }}" class="pg-admin__breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item @if (! $items) active @endif"><a href="{{ route('admin.dashboard') }}">{{ __('admin.dashboard') }}</a></li>
        @foreach ($items as $item)
            @if ($loop->last || empty($item['url']))
                <li class="breadcrumb-item active" aria-current="page">{{ $item['label'] }}</li>
            @else
                <li class="breadcrumb-item"><a href="{{ $item['url'] }}">{{ $item['label'] }}</a></li>
            @endif
        @endforeach
    </ol>
</nav>
