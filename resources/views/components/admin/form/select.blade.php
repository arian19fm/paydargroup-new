{{-- options: [value => label]; `selected` may be a scalar or array (multiple). --}}
@props(['name', 'label', 'options' => [], 'selected' => null, 'help' => null, 'required' => false, 'placeholder' => null, 'multiple' => false])
@php
    $id = $attributes->get('id', 'f-'.str_replace(['.', '[', ']'], '-', $name));
    $dotName = rtrim(str_replace(['[', ']'], ['.', ''], $name), '.');
    $current = old($dotName, $selected);
    $current = is_array($current) ? array_map('strval', $current) : ($current === null ? [] : [(string) $current]);
@endphp
<div class="mb-3">
    <label for="{{ $id }}" class="form-label">{{ $label }}@if ($required) <span class="text-danger" aria-hidden="true">*</span>@endif</label>
    <select id="{{ $id }}" name="{{ $name }}" {{ $attributes->except('id')->class(['form-select', 'is-invalid' => $errors->has($dotName)]) }}
            @if ($multiple) multiple @endif @if ($required) required @endif @if ($help) aria-describedby="{{ $id }}-help" @endif>
        @if ($placeholder !== null)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $value => $text)
            <option value="{{ $value }}" @selected(in_array((string) $value, $current, true))>{{ $text }}</option>
        @endforeach
    </select>
    @if ($help)<div id="{{ $id }}-help" class="form-text">{{ $help }}</div>@endif
    @error($dotName)<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
