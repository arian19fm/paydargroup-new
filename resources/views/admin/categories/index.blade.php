@extends('layouts.admin')

@section('title', __('admin.nav.categories'))
@php $breadcrumbs = [['label' => __('admin.nav.categories')]]; @endphp

@section('actions')
    @can('create', App\Models\ArticleCategory::class)
        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm">{{ __('admin.create') }}</a>
    @endcan
@endsection

@section('content')
    <div class="table-responsive bg-body rounded border">
        <table class="table table-hover align-middle mb-0">
            <thead><tr>
                <th scope="col">{{ __('admin.fields.name') }}</th>
                <th scope="col">{{ __('admin.fields.slug') }}</th>
                <th scope="col">{{ __('admin.nav.articles') }}</th>
                <th scope="col">{{ __('admin.fields.sort_order') }}</th>
                <th scope="col">{{ __('admin.fields.is_active') }}</th>
                <th scope="col"><span class="visually-hidden">{{ __('admin.actions') }}</span></th>
            </tr></thead>
            <tbody>
                @forelse ($categories as $category)
                    <tr>
                        <td><a href="{{ route('admin.categories.edit', $category) }}">{{ $category->name }}</a></td>
                        <td dir="ltr" class="text-end small"><code>{{ $category->slug }}</code></td>
                        <td>{{ $category->articles_count }}</td>
                        <td>{{ $category->sort_order }}</td>
                        <td>{{ $category->is_active ? __('admin.yes') : __('admin.no') }}</td>
                        <td>@can('delete', $category)<x-admin.delete-button :action="route('admin.categories.destroy', $category)" />@endcan</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-body-secondary py-4">{{ __('admin.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $categories->links() }}</div>
@endsection
