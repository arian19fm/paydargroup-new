@extends('layouts.admin')

@section('title', __('admin.nav.roles'))
@php $breadcrumbs = [['label' => __('admin.nav.roles')]]; @endphp

@section('actions')
    @can('create', Spatie\Permission\Models\Role::class)
        <a href="{{ route('admin.roles.create') }}" class="btn btn-primary btn-sm">{{ __('admin.create') }}</a>
    @endcan
@endsection

@section('content')
    <div class="table-responsive bg-body rounded border">
        <table class="table table-hover align-middle mb-0">
            <thead><tr>
                <th scope="col">{{ __('admin.fields.role_name') }}</th>
                <th scope="col">{{ __('admin.fields.permissions') }}</th>
                <th scope="col">{{ __('admin.nav.users') }}</th>
                <th scope="col"><span class="visually-hidden">{{ __('admin.actions') }}</span></th>
            </tr></thead>
            <tbody>
                @forelse ($roles as $role)
                    <tr>
                        <td>
                            @php $label = trans()->has('admin.roles.names.'.$role->name) ? __('admin.roles.names.'.$role->name) : $role->name; @endphp
                            @can('update', $role)<a href="{{ route('admin.roles.edit', $role) }}">{{ $label }}</a>@else{{ $label }}@endcan
                            @if ($role->name === App\Models\User::ROLE_SUPER_ADMIN)
                                <span class="badge text-bg-dark ms-1">{{ __('admin.roles.system') }}</span>
                            @endif
                        </td>
                        <td class="small">{{ $role->name === App\Models\User::ROLE_SUPER_ADMIN ? __('admin.roles.all_permissions') : $role->permissions_count }}</td>
                        <td class="small">{{ $role->users_count }}</td>
                        <td class="text-end">
                            @can('delete', $role)
                                <x-admin.delete-button :action="route('admin.roles.destroy', $role)" />
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-body-secondary py-4">{{ __('admin.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <p class="form-text mt-3">{{ __('admin.roles.help') }}</p>
@endsection
