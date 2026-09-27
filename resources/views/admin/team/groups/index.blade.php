@extends('layouts.admin')

@section('title', __('admin.team.groups'))
@php $breadcrumbs = [['label' => __('admin.nav.team'), 'url' => route('admin.team.members.index')], ['label' => __('admin.team.groups')]]; @endphp

@section('actions')
    <a href="{{ route('admin.team.members.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('admin.team.members') }}</a>
    @can('create', App\Models\TeamGroup::class)
        <a href="{{ route('admin.team.groups.create') }}" class="btn btn-primary btn-sm">{{ __('admin.team.add_group') }}</a>
    @endcan
@endsection

@section('content')
    <div class="table-responsive bg-body rounded border">
        <table class="table table-hover align-middle mb-0">
            <thead><tr>
                <th scope="col">{{ __('admin.fields.name') }}</th>
                <th scope="col">{{ __('admin.team.members_count') }}</th>
                <th scope="col">{{ __('admin.fields.sort_order') }}</th>
                <th scope="col">{{ __('admin.fields.is_active') }}</th>
                <th scope="col"><span class="visually-hidden">{{ __('admin.actions') }}</span></th>
            </tr></thead>
            <tbody>
                @forelse ($groups as $group)
                    <tr>
                        <td><a href="{{ route('admin.team.groups.edit', $group) }}">{{ $group->name }}</a></td>
                        <td>{{ fa_digits($group->members_count) }}</td>
                        <td>{{ fa_digits($group->sort_order) }}</td>
                        <td>{{ $group->is_active ? __('admin.yes') : __('admin.no') }}</td>
                        <td class="text-end">@can('delete', $group)<x-admin.delete-button :action="route('admin.team.groups.destroy', $group)" />@endcan</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-body-secondary py-4">{{ __('admin.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <p class="form-text mt-3">{{ __('admin.team.group_help') }}</p>
    <div class="mt-3">{{ $groups->links() }}</div>
@endsection
