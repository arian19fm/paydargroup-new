@extends('layouts.admin')

@section('title', __('admin.nav.menus'))
@php $breadcrumbs = [['label' => __('admin.nav.menus')]]; @endphp

@section('actions')
    @can('create', App\Models\Menu::class)
        <a href="{{ route('admin.menus.create') }}" class="btn btn-primary btn-sm">{{ __('admin.create') }}</a>
    @endcan
@endsection

@section('content')
    <div class="table-responsive bg-body rounded border">
        <table class="table table-hover align-middle mb-0">
            <thead><tr>
                <th scope="col">{{ __('admin.fields.name') }}</th>
                <th scope="col">{{ __('admin.fields.location') }}</th>
                <th scope="col">{{ __('admin.menus.items') }}</th>
                <th scope="col"><span class="visually-hidden">{{ __('admin.actions') }}</span></th>
            </tr></thead>
            <tbody>
                @forelse ($menus as $menu)
                    <tr>
                        <td><a href="{{ route('admin.menus.edit', $menu) }}">{{ $menu->name }}</a></td>
                        <td><code>{{ $menu->location }}</code></td>
                        <td>{{ $menu->items_count }}</td>
                        <td>@can('delete', $menu)<x-admin.delete-button :action="route('admin.menus.destroy', $menu)" />@endcan</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-body-secondary py-4">{{ __('admin.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
