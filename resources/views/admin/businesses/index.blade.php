@extends('layouts.admin')

@section('title', __('admin.businesses.title'))
@php $breadcrumbs = [['label' => __('admin.nav.businesses')]]; @endphp

@section('actions')
    @can('settings.view')
        <a href="{{ route('admin.settings.edit', 'home') }}" class="btn btn-outline-secondary btn-sm">{{ __('admin.businesses.settings_link') }}</a>
    @endcan
    @can('create', App\Models\Business::class)
        <a href="{{ route('admin.businesses.create') }}" class="btn btn-primary btn-sm">{{ __('admin.businesses.add') }}</a>
    @endcan
@endsection

@section('content')
    <p class="text-body-secondary small">{{ __('admin.businesses.help') }}</p>
    <x-admin.search-form />
    <div class="table-responsive bg-body rounded border">
        <table class="table table-hover align-middle mb-0">
            <thead><tr>
                <th scope="col">{{ __('admin.fields.image') }}</th>
                <th scope="col">{{ __('admin.fields.title') }}</th>
                <th scope="col">{{ __('admin.fields.slug') }}</th>
                <th scope="col">{{ __('admin.fields.accent') }}</th>
                <th scope="col">{{ __('admin.fields.sort_order') }}</th>
                <th scope="col">{{ __('admin.status.label') }}</th>
                <th scope="col"><span class="visually-hidden">{{ __('admin.actions') }}</span></th>
            </tr></thead>
            <tbody>
                @forelse ($businesses as $business)
                    <tr>
                        <td>
                            @if ($business->image)
                                <img src="{{ $business->image->url() }}" width="56" height="42" alt="" class="rounded border object-fit-cover bg-body-tertiary" loading="lazy">
                            @else
                                <span class="small text-body-secondary">{{ __('admin.none') }}</span>
                            @endif
                        </td>
                        <td><a href="{{ route('admin.businesses.edit', $business) }}">{{ $business->title }}</a></td>
                        <td class="small" dir="ltr">{{ $business->slug }}</td>
                        <td class="small">{{ __('admin.fields.accents.'.$business->accentKey()) }}</td>
                        <td>{{ fa_digits($business->sort_order) }}</td>
                        <td><x-admin.status-badge :model="$business" /></td>
                        <td class="text-end">
                            @can('update', $business)
                                <a href="{{ route('admin.businesses.edit', $business) }}" class="btn btn-outline-secondary btn-sm">{{ __('admin.edit') }}</a>
                            @endcan
                            @if ($business->isPublished())
                                <a href="{{ $business->publicUrl() }}" target="_blank" rel="noopener" class="btn btn-link btn-sm">{{ __('admin.view_site') }}</a>
                            @endif
                            @can('delete', $business)<x-admin.delete-button :action="route('admin.businesses.destroy', $business)" />@endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-body-secondary py-4">{{ __('admin.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $businesses->links() }}</div>
@endsection
