@extends('layouts.admin')

@php $editing = $group->exists; @endphp
@section('title', $editing ? __('admin.edit').': '.$group->name : __('admin.team.add_group'))
@php $breadcrumbs = [['label' => __('admin.nav.team'), 'url' => route('admin.team.members.index')], ['label' => __('admin.team.groups'), 'url' => route('admin.team.groups.index')], ['label' => $editing ? $group->name : __('admin.create')]]; @endphp

@section('content')
    <form method="POST" action="{{ $editing ? route('admin.team.groups.update', $group) : route('admin.team.groups.store') }}" class="card" novalidate>
        <div class="card-body" style="max-width: 40rem;">
            @csrf
            @if ($editing) @method('PUT') @endif
            <x-admin.form.input name="name" :label="__('admin.fields.name')" :value="$group->name" required />
            <x-admin.form.input name="sort_order" type="number" min="0" :label="__('admin.fields.sort_order')" :value="$group->sort_order ?? 0" />
            <x-admin.form.checkbox name="is_active" :label="__('admin.fields.is_active')" :checked="$group->is_active ?? true" />
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">{{ __('admin.save') }}</button>
                <a href="{{ route('admin.team.groups.index') }}" class="btn btn-outline-secondary">{{ __('admin.cancel') }}</a>
            </div>
        </div>
    </form>
@endsection
