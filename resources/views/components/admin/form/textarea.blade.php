@props(['name', 'label', 'value' => null, 'help' => null, 'required' => false, 'rows' => 4, 'counter' => null, 'preview' => false])
@php $id = $attributes->get('id', 'f-'.str_replace(['.', '[', ']'], '-', $name)); $dotName = str_replace(['[', ']'], ['.', ''], $name); @endphp
<div class="mb-3">
    <label for="{{ $id }}" class="form-label">{{ $label }}@if ($required) <span class="text-danger" aria-hidden="true">*</span>@endif</label>
    <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}"
              {{ $attributes->except('id')->class(['form-control', 'is-invalid' => $errors->has($dotName)]) }}
              @if ($required) required @endif
              @if ($counter) data-char-count="{{ $counter }}" @endif
              @if ($preview) data-text-preview="{{ $id }}-preview" @endif
              @if ($help || $counter) aria-describedby="{{ $id }}-help" @endif>{{ old($dotName, $value) }}</textarea>
    @if ($help || $counter)
        <div id="{{ $id }}-help" class="form-text d-flex justify-content-between">
            <span>{{ $help }}</span>
            @if ($counter)<span data-char-count-for="{{ $id }}"></span>@endif
        </div>
    @endif
    @error($dotName)<div class="invalid-feedback">{{ $message }}</div>@enderror
    @if ($preview)
        {{-- Mirrors App\Support\Content\PlainText::toHtml() client-side (admin/text-preview.js). --}}
        <div class="pg-admin-preview" hidden>
            <div class="pg-admin-preview__label">{{ __('admin.preview.label') }}</div>
            <div id="{{ $id }}-preview" class="pg-admin-preview__body pg-content" aria-live="polite">{!! \App\Support\Content\PlainText::toHtml(old($dotName, $value)) !!}</div>
        </div>
    @endif
</div>
