@extends('layouts.admin')

@php $editing = $member->exists; @endphp
@section('title', $editing ? __('admin.edit').': '.$member->name : __('admin.team.add_member'))
@php $breadcrumbs = [['label' => __('admin.nav.team'), 'url' => route('admin.team.members.index')], ['label' => $editing ? $member->name : __('admin.create')]]; @endphp

@section('content')
    @if (! $groups)
        <div class="alert alert-warning">{{ __('admin.team.no_groups') }} <a href="{{ route('admin.team.groups.create') }}">{{ __('admin.team.add_group') }}</a></div>
    @endif
    <form method="POST" action="{{ $editing ? route('admin.team.members.update', $member) : route('admin.team.members.store') }}" class="card" enctype="multipart/form-data" novalidate>
        <div class="card-body" style="max-width: 40rem;">
            @csrf
            @if ($editing) @method('PUT') @endif
            <x-admin.form.input name="name" :label="__('admin.fields.name')" :value="$member->name" required />
            <x-admin.form.input name="role" :label="__('admin.fields.job_role')" :value="$member->role" />
            <x-admin.form.select name="team_group_id" :label="__('admin.fields.team_group')" :options="$groups" :selected="$member->team_group_id" required />

            <fieldset class="mb-3">
                <legend class="form-label fs-6">{{ __('admin.fields.photo') }}</legend>
                @if ($member->photo)
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <img src="{{ $member->photo->url() }}" width="72" height="72" alt="" class="rounded border object-fit-contain bg-body-tertiary">
                        <div class="small">
                            <div>{{ __('admin.fields.photo_current') }}: <a href="{{ route('admin.media.edit', $member->photo) }}">{{ $member->photo->original_filename }}</a></div>
                            <x-admin.form.checkbox name="remove_photo" :label="__('admin.fields.remove_photo')" />
                        </div>
                    </div>
                @endif
                <x-admin.form.input name="photo" type="file" accept="image/png,image/jpeg,image/webp" :label="__('admin.fields.photo_upload')" :help="__('admin.fields.photo_help')" />
                <x-admin.form.input name="photo_media_id" type="number" min="0" :label="__('admin.fields.photo_media_id')" :value="$member->photo_media_id" />
            </fieldset>

            <x-admin.form.input name="linkedin_url" type="url" :label="__('admin.fields.linkedin_url')" :value="$member->linkedin_url" dir="ltr" />
            <x-admin.form.input name="sort_order" type="number" min="0" :label="__('admin.fields.sort_order')" :value="$member->sort_order ?? 0" />
            <x-admin.form.checkbox name="is_active" :label="__('admin.fields.is_active')" :checked="$member->is_active ?? true" />
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">{{ __('admin.save') }}</button>
                <a href="{{ route('admin.team.members.index') }}" class="btn btn-outline-secondary">{{ __('admin.cancel') }}</a>
            </div>
        </div>
    </form>
@endsection
