@props(['name', 'label', 'checked' => false, 'help' => null])
@php $id = $attributes->get('id', 'f-'.str_replace(['.', '[', ']'], '-', $name)); $dotName = str_replace(['[', ']'], ['.', ''], $name); @endphp
<div class="form-check mb-3">
    <input type="hidden" name="{{ $name }}" value="0">
    <input type="checkbox" id="{{ $id }}" name="{{ $name }}" value="1" {{ $attributes->except('id')->class(['form-check-input', 'is-invalid' => $errors->has($dotName)]) }}
           @checked(old($dotName, $checked)) @if ($help) aria-describedby="{{ $id }}-help" @endif>
    <label for="{{ $id }}" class="form-check-label">{{ $label }}</label>
    @if ($help)<div id="{{ $id }}-help" class="form-text">{{ $help }}</div>@endif
    @error($dotName)<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
