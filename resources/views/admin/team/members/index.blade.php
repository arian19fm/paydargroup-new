@extends('layouts.admin')

@section('title', __('admin.team.members'))
@php $breadcrumbs = [['label' => __('admin.nav.team')]]; @endphp

@section('actions')
    @can('viewAny', App\Models\TeamGroup::class)
        <a href="{{ route('admin.team.groups.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('admin.team.groups') }}</a>
    @endcan
    @can('create', App\Models\TeamMember::class)
        <a href="{{ route('admin.team.members.create') }}" class="btn btn-primary btn-sm">{{ __('admin.team.add_member') }}</a>
    @endcan
@endsection

@section('content')
    <x-admin.search-form />
    <div class="table-responsive bg-body rounded border">
        <table class="table table-hover align-middle mb-0">
            <thead><tr>
                <th scope="col">{{ __('admin.fields.name') }}</th>
                <th scope="col">{{ __('admin.fields.job_role') }}</th>
                <th scope="col">{{ __('admin.fields.team_group') }}</th>
                <th scope="col">{{ __('admin.fields.photo') }}</th>
                <th scope="col">{{ __('admin.fields.sort_order') }}</th>
                <th scope="col">{{ __('admin.fields.is_active') }}</th>
                <th scope="col"><span class="visually-hidden">{{ __('admin.actions') }}</span></th>
            </tr></thead>
            <tbody>
                @forelse ($members as $member)
                    <tr>
                        <td><a href="{{ route('admin.team.members.edit', $member) }}">{{ $member->name }}</a></td>
                        <td class="small">{{ $member->role ?: __('admin.none') }}</td>
                        <td class="small">{{ $member->group->name }}</td>
                        <td>
                            @if ($member->photo)
                                <img src="{{ $member->photo->url() }}" width="40" height="40" alt="" class="rounded border object-fit-contain bg-body-tertiary" loading="lazy">
                            @else
                                <span class="small text-body-secondary">{{ __('admin.none') }}</span>
                            @endif
                        </td>
                        <td>{{ fa_digits($member->sort_order) }}</td>
                        <td>{{ $member->is_active ? __('admin.yes') : __('admin.no') }}</td>
                        <td class="text-end">@can('delete', $member)<x-admin.delete-button :action="route('admin.team.members.destroy', $member)" />@endcan</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-body-secondary py-4">{{ __('admin.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $members->links() }}</div>
@endsection
