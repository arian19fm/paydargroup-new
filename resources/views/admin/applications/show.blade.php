@extends('layouts.admin')

@section('title', __('admin.applications.one').': '.$application->job_title)
@php $breadcrumbs = [['label' => __('admin.nav.applications'), 'url' => route('admin.applications.index')], ['label' => $application->job_title]]; @endphp

@section('content')
    <div class="card" style="max-width: 40rem;">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-4">{{ __('admin.fields.job_title') }}</dt>
                <dd class="col-sm-8">
                    {{ $application->job_title }}
                    @if ($application->job)
                        <a class="small ms-2" href="{{ route('admin.jobs.edit', $application->job) }}">{{ __('admin.edit') }}</a>
                    @endif
                </dd>
                <dt class="col-sm-4">{{ __('admin.fields.phone') }}</dt>
                <dd class="col-sm-8"><a href="tel:{{ $application->phone }}" dir="ltr">{{ fa_digits($application->phone) }}</a></dd>
                <dt class="col-sm-4">{{ __('admin.fields.resume') }}</dt>
                <dd class="col-sm-8"><a class="btn btn-primary btn-sm" href="{{ route('admin.applications.resume', $application) }}">{{ __('admin.applications.download') }}</a> <span class="small text-body-secondary">{{ $application->resume_name }} · {{ fa_digits(round($application->resume_size / 1024)) }} KB</span></dd>
                <dt class="col-sm-4">{{ __('admin.fields.received_at') }}</dt>
                <dd class="col-sm-8">{{ \App\Support\Localization\Jalali::format($application->created_at).' – '.fa_digits($application->created_at->format('H:i')) }}</dd>
                <dt class="col-sm-4">{{ __('admin.fields.seen_at') }}</dt>
                <dd class="col-sm-8">{{ $application->seen_at ? \App\Support\Localization\Jalali::format($application->seen_at).' – '.fa_digits($application->seen_at->format('H:i')) : __('admin.none') }}</dd>
                @if ($application->ip)
                    <dt class="col-sm-4">IP</dt>
                    <dd class="col-sm-8" dir="ltr">{{ $application->ip }}</dd>
                @endif
            </dl>
            <div class="d-flex gap-2 mt-3">
                <a href="{{ route('admin.applications.index') }}" class="btn btn-outline-secondary">{{ __('admin.cancel') }}</a>
                @can('delete', $application)<x-admin.delete-button :action="route('admin.applications.destroy', $application)" />@endcan
            </div>
        </div>
    </div>
@endsection
