@extends('layouts.admin')

@section('title', __('admin.dashboard'))

@section('content')
    @if ($counts)
        <div class="row g-3">
            @foreach ($counts as $key => $numbers)
                <div class="col-sm-6 col-xl-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <h2 class="h6 text-body-secondary">{{ __('admin.dashboard_cards.'.$key) }}</h2>
                            <p class="display-6 mb-1">{{ $numbers['total'] }}</p>
                            @isset($numbers['published'])
                                <p class="small text-body-secondary mb-0">{{ __('admin.dashboard_cards.published') }}: {{ $numbers['published'] }}</p>
                            @endisset
                            @isset($numbers['active'])
                                <p class="small text-body-secondary mb-0">{{ __('admin.dashboard_cards.active') }}: {{ $numbers['active'] }}</p>
                            @endisset
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-body-secondary">{{ __('admin.empty') }}</p>
    @endif
@endsection
