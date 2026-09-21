@extends('layouts.admin')

@section('title', __('admin.nav.users'))
@php $breadcrumbs = [['label' => __('admin.nav.users')]]; @endphp

@section('actions')
    @can('create', App\Models\User::class)
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">{{ __('admin.create') }}</a>
    @endcan
@endsection

@section('content')
    <x-admin.search-form />
    <div class="table-responsive bg-body rounded border">
        <table class="table table-hover align-middle mb-0">
            <thead><tr>
                <th scope="col">{{ __('admin.fields.name') }}</th>
                <th scope="col">{{ __('admin.fields.email') }}</th>
                <th scope="col">{{ __('admin.fields.roles') }}</th>
                <th scope="col">{{ __('admin.fields.is_active') }}</th>
                <th scope="col">{{ __('admin.fields.last_login_at') }}</th>
                <th scope="col"><span class="visually-hidden">{{ __('admin.actions') }}</span></th>
            </tr></thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>@can('update', $user)<a href="{{ route('admin.users.edit', $user) }}">{{ $user->name }}</a>@else{{ $user->name }}@endcan</td>
                        <td dir="ltr" class="text-end small">{{ $user->email }}</td>
                        <td class="small">{{ $user->roles->pluck('name')->join(', ') ?: __('admin.none') }}</td>
                        <td>
                            <span class="badge {{ $user->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $user->is_active ? __('admin.users.active') : __('admin.users.inactive') }}</span>
                        </td>
                        <td class="small text-body-secondary">{{ $user->last_login_at?->diffForHumans() ?? __('admin.none') }}</td>
                        <td>
                            @can('toggleActive', $user)
                                <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">{{ $user->is_active ? __('admin.users.deactivate') : __('admin.users.activate') }}</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-body-secondary py-4">{{ __('admin.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $users->links() }}</div>
@endsection
