@props(['name', 'label', 'type' => 'text', 'value' => null, 'help' => null, 'required' => false, 'counter' => null])
@php $id = $attributes->get('id', 'f-'.str_replace(['.', '[', ']'], '-', $name)); $dotName = str_replace(['[', ']'], ['.', ''], $name); @endphp
<div class="mb-3">
    <label for="{{ $id }}" class="form-label">{{ $label }}@if ($required) <span class="text-danger" aria-hidden="true">*</span>@endif</label>
    <input type="{{ $type }}" id="{{ $id }}" name="{{ $name }}"
           value="{{ $type === 'password' ? '' : old($dotName, $value) }}"
           {{ $attributes->except('id')->class(['form-control', 'is-invalid' => $errors->has($dotName)]) }}
           @if ($required) required @endif
           @if ($counter) data-char-count="{{ $counter }}" @endif
           @if ($help || $counter) aria-describedby="{{ $id }}-help" @endif>
    @if ($help || $counter)
        <div id="{{ $id }}-help" class="form-text d-flex justify-content-between">
            <span>{{ $help }}</span>
            @if ($counter)<span data-char-count-for="{{ $id }}"></span>@endif
        </div>
    @endif
    @error($dotName)<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
