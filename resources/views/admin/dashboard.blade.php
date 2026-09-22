@extends('layouts.admin')

@section('title', __('admin.dashboard'))

@section('content')
    <section class="pg-admin__welcome">
        <div>
            <p class="pg-admin__welcome-eyebrow">{{ \App\Support\Localization\Jalali::format(now()) }}</p>
            <h2 class="pg-admin__welcome-title">{{ __('admin.welcome', ['name' => auth()->user()->name]) }}</h2>
            <p class="pg-admin__welcome-text">{{ __('admin.welcome_text') }}</p>
        </div>
        @if ($quick)
            <div class="pg-admin__quick">
                @foreach ($quick as $action)
                    <a class="pg-admin__quick-link" href="{{ route($action['route'], $action['params'] ?? []) }}"><x-admin.icon :name="$action['icon']" size="18" /><span>{{ $action['label'] }}</span></a>
                @endforeach
            </div>
        @endif
    </section>

    @if ($counts)
        <div class="row g-3 mb-4">
            @foreach ($counts as $key => $numbers)
                <div class="col-sm-6 col-xl-3">
                    <a class="pg-stat pg-stat--{{ $key }}" href="{{ route($numbers['route']) }}">
                        <span class="pg-stat__icon"><x-admin.icon :name="$numbers['icon']" size="22" /></span>
                        <span class="pg-stat__body">
                            <span class="pg-stat__label">{{ __('admin.dashboard_cards.'.$key) }}</span>
                            <span class="pg-stat__value">{{ fa_digits($numbers['total']) }}</span>
                            @isset($numbers['published'])
                                <span class="pg-stat__meta">{{ __('admin.dashboard_cards.published') }}: {{ fa_digits($numbers['published']) }}</span>
                            @endisset
                            @isset($numbers['active'])
                                <span class="pg-stat__meta">{{ __('admin.dashboard_cards.active') }}: {{ fa_digits($numbers['active']) }}</span>
                            @endisset
                        </span>
                    </a>
                </div>
            @endforeach
        </div>
    @endif

    @if ($recent)
        <div class="row g-3">
            @foreach ($recent as $type => $items)
                <div class="col-lg-6">
                    <div class="card pg-admin__card h-100">
                        <div class="card-header">
                            <h2 class="h6 mb-0">{{ __('admin.recent.'.$type) }}</h2>
                            <a class="small" href="{{ route('admin.'.$type.'.index') }}">{{ __('admin.recent.all') }}</a>
                        </div>
                        @if ($items->isEmpty())
                            <div class="card-body text-body-secondary small">{{ __('admin.empty') }}</div>
                        @else
                            <ul class="list-group list-group-flush">
                                @foreach ($items as $item)
                                    <li class="list-group-item pg-admin__recent-item">
                                        <a href="{{ route('admin.'.$type.'.edit', $item) }}">{{ $item->title }}</a>
                                        <span class="pg-admin__recent-meta">
                                            <x-admin.status-badge :model="$item" />
                                            <time datetime="{{ $item->updated_at->toIso8601String() }}">{{ $item->updated_at->diffForHumans() }}</time>
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
