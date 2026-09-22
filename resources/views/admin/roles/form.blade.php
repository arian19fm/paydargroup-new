@extends('layouts.admin')

@php $editing = $role->exists; @endphp
@section('title', $editing ? __('admin.edit').': '.$role->name : __('admin.roles.create'))
@php $breadcrumbs = [['label' => __('admin.nav.roles'), 'url' => route('admin.roles.index')], ['label' => $editing ? $role->name : __('admin.roles.create')]]; @endphp

@section('content')
    <form method="POST" action="{{ $editing ? route('admin.roles.update', $role) : route('admin.roles.store') }}" class="card" novalidate>
        <div class="card-body" style="max-width: 48rem;">
            @csrf
            @if ($editing) @method('PUT') @endif
            <x-admin.form.input name="name" :label="__('admin.fields.role_name')" :value="$role->name" :help="__('admin.roles.name_help')" required />

            <fieldset class="mb-3">
                <legend class="fs-6 fw-semibold">{{ __('admin.fields.permissions') }}</legend>
                <p class="form-text mt-0">{{ __('admin.roles.permissions_help') }}</p>
                @error('permissions')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
                @php $chosen = old('permissions', $selected); @endphp
                <div class="row g-3">
                    @foreach ($groups as $resource => $names)
                        <div class="col-md-6 col-lg-4">
                            <div class="border rounded p-3 h-100">
                                <div class="fw-semibold mb-2">{{ __('admin.permissions.groups.'.$resource) }}</div>
                                @foreach ($names as $name)
                                    @php $id = 'perm-'.str_replace('.', '-', $name); @endphp
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="{{ $id }}" name="permissions[]" value="{{ $name }}" @checked(in_array($name, $chosen, true))>
                                        <label class="form-check-label" for="{{ $id }}">{{ __('admin.permissions.'.str_replace('.', '_', $name)) }} <code class="small text-body-tertiary" dir="ltr">{{ $name }}</code></label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </fieldset>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">{{ __('admin.save') }}</button>
                <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">{{ __('admin.cancel') }}</a>
            </div>
        </div>
    </form>
@endsection
