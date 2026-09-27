@extends('layouts.admin')

@section('title', __('admin.jobs.title'))
@php $breadcrumbs = [['label' => __('admin.nav.jobs')]]; @endphp

@section('actions')
    @can('settings.view')
        <a href="{{ route('admin.settings.edit', 'careers') }}" class="btn btn-outline-secondary btn-sm">{{ __('admin.jobs.settings_link') }}</a>
    @endcan
    @can('create', App\Models\JobOpening::class)
        <a href="{{ route('admin.jobs.create') }}" class="btn btn-primary btn-sm">{{ __('admin.jobs.add') }}</a>
    @endcan
@endsection

@section('content')
    <p class="text-body-secondary small">{{ __('admin.jobs.help') }}</p>
    <x-admin.search-form />
    <div class="table-responsive bg-body rounded border">
        <table class="table table-hover align-middle mb-0">
            <thead><tr>
                <th scope="col">{{ __('admin.fields.title') }}</th>
                <th scope="col">{{ __('admin.fields.job_category') }}</th>
                <th scope="col">{{ __('admin.fields.employment_type') }}</th>
                <th scope="col">{{ __('admin.fields.job_location') }}</th>
                <th scope="col">{{ __('admin.fields.sort_order') }}</th>
                <th scope="col">{{ __('admin.fields.is_active') }}</th>
                <th scope="col"><span class="visually-hidden">{{ __('admin.actions') }}</span></th>
            </tr></thead>
            <tbody>
                @forelse ($jobs as $job)
                    <tr>
                        <td><a href="{{ route('admin.jobs.edit', $job) }}">{{ $job->title }}</a></td>
                        <td class="small">
                            @if ($job->category)
                                <span class="badge rounded-pill" style="color: {{ App\Models\JobOpening::TONES[$job->toneKey()][0] }}; background: {{ App\Models\JobOpening::TONES[$job->toneKey()][1] }};">{{ $job->category }}</span>
                            @else
                                {{ __('admin.none') }}
                            @endif
                        </td>
                        <td class="small">{{ $job->employment_type ?: __('admin.none') }}</td>
                        <td class="small">{{ $job->location ?: __('admin.none') }}</td>
                        <td>{{ fa_digits($job->sort_order) }}</td>
                        <td>{{ $job->is_active ? __('admin.yes') : __('admin.no') }}</td>
                        <td class="text-end">@can('delete', $job)<x-admin.delete-button :action="route('admin.jobs.destroy', $job)" />@endcan</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-body-secondary py-4">{{ __('admin.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $jobs->links() }}</div>
@endsection
