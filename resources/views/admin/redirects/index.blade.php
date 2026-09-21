@extends('layouts.admin')

@section('title', __('admin.nav.redirects'))
@php $breadcrumbs = [['label' => __('admin.nav.redirects')]]; @endphp

@section('actions')
    @can('create', App\Models\Redirect::class)
        <a href="{{ route('admin.redirects.create') }}" class="btn btn-primary btn-sm">{{ __('admin.create') }}</a>
    @endcan
@endsection

@section('content')
    <x-admin.search-form />
    <div class="table-responsive bg-body rounded border">
        <table class="table table-hover align-middle mb-0">
            <thead><tr>
                <th scope="col">{{ __('admin.fields.source_path') }}</th>
                <th scope="col">{{ __('admin.fields.destination_url') }}</th>
                <th scope="col">{{ __('admin.fields.http_status') }}</th>
                <th scope="col">{{ __('admin.fields.is_active') }}</th>
                <th scope="col">{{ __('admin.fields.hit_count') }}</th>
                <th scope="col">{{ __('admin.fields.last_hit_at') }}</th>
                <th scope="col"><span class="visually-hidden">{{ __('admin.actions') }}</span></th>
            </tr></thead>
            <tbody>
                @forelse ($redirects as $redirect)
                    <tr>
                        <td dir="ltr" class="text-end"><a href="{{ route('admin.redirects.edit', $redirect) }}"><code>{{ $redirect->source_path }}</code></a></td>
                        <td dir="ltr" class="text-end small"><code>{{ $redirect->destination_url }}</code></td>
                        <td>{{ $redirect->http_status }}</td>
                        <td>{{ $redirect->is_active ? __('admin.yes') : __('admin.no') }}</td>
                        <td>{{ $redirect->hit_count }}</td>
                        <td class="small text-body-secondary">{{ $redirect->last_hit_at?->diffForHumans() ?? __('admin.none') }}</td>
                        <td>@can('delete', $redirect)<x-admin.delete-button :action="route('admin.redirects.destroy', $redirect)" />@endcan</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-body-secondary py-4">{{ __('admin.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $redirects->links() }}</div>
@endsection
