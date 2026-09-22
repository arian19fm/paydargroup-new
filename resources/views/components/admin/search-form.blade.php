@props(['placeholder' => null])
<form method="GET" class="pg-admin__toolbar" role="search">
    <label for="q" class="visually-hidden">{{ __('admin.search') }}</label>
    <div class="pg-admin__search">
        <x-admin.icon name="search" size="16" />
        <input type="search" id="q" name="q" class="form-control form-control-sm" value="{{ request('q') }}" placeholder="{{ $placeholder ?? __('admin.search') }}">
    </div>
    {{ $slot }}
    <button type="submit" class="btn btn-sm btn-outline-secondary">{{ __('admin.search') }}</button>
</form>
