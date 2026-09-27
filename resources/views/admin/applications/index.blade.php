@extends('layouts.admin')

@section('title', __('admin.applications.title'))
@php $breadcrumbs = [['label' => __('admin.nav.applications')]]; @endphp

@section('actions')
    @if (request('status') === 'unseen')
        <a href="{{ route('admin.applications.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('admin.applications.show_all') }}</a>
    @else
        <a href="{{ route('admin.applications.index', ['status' => 'unseen']) }}" class="btn btn-outline-secondary btn-sm">{{ __('admin.applications.show_unseen') }}</a>
    @endif
@endsection

@section('content')
    <p class="text-body-secondary small">{{ __('admin.applications.help') }}</p>
    <x-admin.search-form />
    <div class="table-responsive bg-body rounded border">
        <table class="table table-hover align-middle mb-0">
            <thead><tr>
                <th scope="col">{{ __('admin.fields.job_title') }}</th>
                <th scope="col">{{ __('admin.fields.phone') }}</th>
                <th scope="col">{{ __('admin.fields.resume') }}</th>
                <th scope="col">{{ __('admin.fields.received_at') }}</th>
                <th scope="col">{{ __('admin.status.label') }}</th>
                <th scope="col"><span class="visually-hidden">{{ __('admin.actions') }}</span></th>
            </tr></thead>
            <tbody>
                @forelse ($applications as $application)
                    <tr @unless ($application->isSeen()) class="fw-semibold" @endunless>
                        <td><a href="{{ route('admin.applications.show', $application) }}">{{ $application->job_title }}</a></td>
                        <td dir="ltr" class="text-end">{{ fa_digits($application->phone) }}</td>
                        <td class="small"><a href="{{ route('admin.applications.resume', $application) }}">{{ $application->resume_name }}</a> <span class="text-body-secondary">({{ fa_digits(round($application->resume_size / 1024)) }} KB)</span></td>
                        <td class="small">{{ \App\Support\Localization\Jalali::format($application->created_at).' – '.fa_digits($application->created_at->format('H:i')) }}</td>
                        <td>
                            @if ($application->isSeen())
                                <span class="badge text-bg-secondary">{{ __('admin.applications.seen') }}</span>
                            @else
                                <span class="badge text-bg-primary">{{ __('admin.applications.new') }}</span>
                            @endif
                        </td>
                        <td class="text-end">@can('delete', $application)<x-admin.delete-button :action="route('admin.applications.destroy', $application)" />@endcan</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-body-secondary py-4">{{ __('admin.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $applications->links() }}</div>
@endsection
