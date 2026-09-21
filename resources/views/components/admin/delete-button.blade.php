{{-- Inline DELETE form. Uses the native confirm() as a minimal safeguard. --}}
@props(['action', 'label' => null])
<form method="POST" action="{{ $action }}" class="d-inline" onsubmit="return window.confirm(@js(__('admin.confirm_delete')));">
    @csrf
    @method('DELETE')
    <button type="submit" {{ $attributes->class(['btn btn-sm btn-outline-danger']) }}>{{ $label ?? __('admin.delete') }}</button>
</form>
