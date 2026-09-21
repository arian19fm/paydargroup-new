@props(['placeholder' => null])
<form method="GET" class="d-flex gap-2 mb-3" role="search">
    <label for="q" class="visually-hidden">{{ __('admin.search') }}</label>
    <input type="search" id="q" name="q" class="form-control form-control-sm w-auto" value="{{ request('q') }}" placeholder="{{ $placeholder ?? __('admin.search') }}">
    {{ $slot }}
    <button type="submit" class="btn btn-sm btn-outline-secondary">{{ __('admin.search') }}</button>
</form>
