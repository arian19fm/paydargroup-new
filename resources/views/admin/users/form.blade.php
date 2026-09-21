@extends('layouts.admin')

@php $editing = $user->exists; @endphp
@section('title', $editing ? __('admin.edit').': '.$user->name : __('admin.create'))
@php $breadcrumbs = [['label' => __('admin.nav.users'), 'url' => route('admin.users.index')], ['label' => $editing ? $user->name : __('admin.create')]]; @endphp

@section('content')
    <form method="POST" action="{{ $editing ? route('admin.users.update', $user) : route('admin.users.store') }}" class="card" novalidate autocomplete="off">
        <div class="card-body" style="max-width: 40rem;">
            @csrf
            @if ($editing) @method('PUT') @endif
            <x-admin.form.input name="name" :label="__('admin.fields.name')" :value="$user->name" required />
            <x-admin.form.input name="email" type="email" :label="__('admin.fields.email')" :value="$user->email" dir="ltr" required autocomplete="off" />
            <x-admin.form.input name="password" type="password" :label="__('admin.fields.password')" :help="__('admin.fields.password_help')" :required="! $editing" autocomplete="new-password" />
            <x-admin.form.input name="password_confirmation" type="password" :label="__('admin.fields.password_confirmation')" :required="! $editing" autocomplete="new-password" />
            <x-admin.form.select name="roles[]" :label="__('admin.fields.roles')" :options="$roles->pluck('name', 'name')->all()" :selected="$user->roles->pluck('name')->all()" multiple size="3" required />
            <x-admin.form.checkbox name="is_active" :label="__('admin.fields.is_active')" :checked="$user->is_active ?? true" />
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">{{ __('admin.save') }}</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">{{ __('admin.cancel') }}</a>
            </div>
        </div>
    </form>
@endsection
