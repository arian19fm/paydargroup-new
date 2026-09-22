@extends('layouts.admin')

@section('title', __('admin.profile.title'))
@php $breadcrumbs = [['label' => __('admin.profile.title')]]; @endphp

@section('content')
    <form method="POST" action="{{ route('admin.profile.update') }}" class="card" novalidate autocomplete="off">
        <div class="card-body" style="max-width: 40rem;">
            @csrf
            @method('PUT')
            <x-admin.form.input name="name" :label="__('admin.fields.name')" :value="$user->name" required />
            <x-admin.form.input name="email" type="email" :label="__('admin.fields.email')" :value="$user->email" dir="ltr" required autocomplete="username" />
            <p class="small text-body-secondary">{{ __('admin.fields.roles') }}: {{ $user->getRoleNames()->join('، ') }}</p>

            <hr>
            <h2 class="h6">{{ __('admin.profile.change_password') }}</h2>
            <x-admin.form.input name="current_password" type="password" :label="__('admin.fields.current_password')" :help="__('admin.profile.current_password_help')" autocomplete="current-password" />
            <x-admin.form.input name="password" type="password" :label="__('admin.fields.new_password')" :help="__('admin.fields.password_help')" autocomplete="new-password" />
            <x-admin.form.input name="password_confirmation" type="password" :label="__('admin.fields.password_confirmation')" autocomplete="new-password" />

            <button type="submit" class="btn btn-primary">{{ __('admin.save') }}</button>
        </div>
    </form>
@endsection
