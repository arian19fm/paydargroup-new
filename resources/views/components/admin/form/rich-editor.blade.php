@props(['name', 'label', 'value' => null, 'help' => null, 'required' => false, 'rows' => 14])
{{--
    Rich content field (docs/CMS.md §3). The textarea holds the HTML and is
    what the form submits; admin/rich-editor enhances it with a visual
    editor and an "HTML" tab for hand-written markup. Without JavaScript it
    stays a plain HTML source field. The server sanitises on save.
--}}
@php
    $id = $attributes->get('id', 'f-'.str_replace(['.', '[', ']'], '-', $name));
    $dotName = str_replace(['[', ']'], ['.', ''], $name);
    $i18n = collect(__('admin.editor'))->except('too_long')->all();
@endphp
<div class="mb-3 pg-editor" data-rich-editor
     data-i18n="{{ json_encode($i18n, JSON_UNESCAPED_UNICODE) }}"
     @can('create', \App\Models\Media::class) data-upload-url="{{ route('admin.media.editor-image') }}" @endcan>
    <label for="{{ $id }}" class="form-label">{{ $label }}@if ($required) <span class="text-danger" aria-hidden="true">*</span>@endif</label>
    <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}" dir="ltr" spellcheck="false"
              {{ $attributes->except('id')->class(['form-control pg-editor__source', 'is-invalid' => $errors->has($dotName)]) }}
              @if ($required) required @endif
              aria-describedby="{{ $id }}-help">{{ old($dotName, $value) }}</textarea>
    <div id="{{ $id }}-help" class="form-text">
        @if ($help)<span class="d-block">{{ $help }}</span>@endif
        <span class="d-block pg-editor__html-help">{{ __('admin.editor.html_help') }}</span>
    </div>
    @error($dotName)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
