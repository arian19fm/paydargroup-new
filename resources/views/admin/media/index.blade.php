@extends('layouts.admin')

@section('title', __('admin.nav.media'))
@php $breadcrumbs = [['label' => __('admin.nav.media')]]; @endphp

@section('actions')
    @can('create', App\Models\Media::class)
        <a href="{{ route('admin.media.create') }}" class="btn btn-primary btn-sm">{{ __('admin.media.upload') }}</a>
    @endcan
@endsection

@section('content')
    <x-admin.search-form />
    <div class="table-responsive bg-body rounded border">
        <table class="table table-hover align-middle mb-0">
            <thead><tr>
                <th scope="col">ID</th>
                <th scope="col">{{ __('admin.media.preview') }}</th>
                <th scope="col">{{ __('admin.fields.file') }}</th>
                <th scope="col">{{ __('admin.fields.alt_text') }}</th>
                <th scope="col">{{ __('admin.fields.dimensions') }}</th>
                <th scope="col">{{ __('admin.fields.size') }}</th>
                <th scope="col">{{ __('admin.fields.uploaded_by') }}</th>
                <th scope="col"><span class="visually-hidden">{{ __('admin.actions') }}</span></th>
            </tr></thead>
            <tbody>
                @forelse ($media as $medium)
                    <tr>
                        <td><code>{{ $medium->id }}</code></td>
                        <td>
                            @if ($medium->isImage())
                                <img src="{{ $medium->url() }}" alt="" width="64" height="64" style="object-fit: cover;" loading="lazy" class="rounded border">
                            @else
                                <span class="badge text-bg-secondary">{{ strtoupper($medium->extension) }}</span>
                            @endif
                        </td>
                        <td><a href="{{ route('admin.media.edit', $medium) }}">{{ $medium->original_filename }}</a><br><span class="small text-body-secondary">{{ $medium->mime_type }}</span></td>
                        <td class="small">{{ $medium->alt_text ?: __('admin.none') }}</td>
                        <td class="small">{{ $medium->width ? $medium->width.'×'.$medium->height : __('admin.none') }}</td>
                        <td class="small">{{ number_format($medium->size / 1024) }} KB</td>
                        <td class="small">{{ $medium->uploader?->name ?? __('admin.none') }}</td>
                        <td>@can('delete', $medium)<x-admin.delete-button :action="route('admin.media.destroy', $medium)" />@endcan</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-body-secondary py-4">{{ __('admin.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $media->links() }}</div>
@endsection
