@props(['model'])
@php
    [$class, $label] = $model->isPublished()
        ? ['text-bg-success', __('admin.status.published')]
        : ($model->isScheduled() ? ['text-bg-info', __('admin.status.scheduled')] : ['text-bg-secondary', __('admin.status.draft')]);
@endphp
<span class="badge {{ $class }}">{{ $label }}</span>
