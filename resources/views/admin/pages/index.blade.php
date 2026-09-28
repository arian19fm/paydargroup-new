@extends('layouts.admin')

@section('title', __('admin.nav.pages'))
@php $breadcrumbs = [['label' => __('admin.nav.pages')]]; @endphp

@section('content')
    <p class="text-body-secondary small">{{ __('admin.pages.help') }}</p>
    <x-admin.search-form>
        <label for="status" class="visually-hidden">{{ __('admin.status.label') }}</label>
        <select id="status" name="status" class="form-select form-select-sm w-auto">
            <option value="">{{ __('admin.status.label') }}: {{ __('admin.all') }}</option>
            @foreach (App\Enums\ContentStatus::cases() as $status)
                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
    </x-admin.search-form>

    <div class="table-responsive bg-body rounded border">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th scope="col">{{ __('admin.fields.title') }}</th>
                    <th scope="col">{{ __('admin.fields.slug') }}</th>
                    <th scope="col">{{ __('admin.status.label') }}</th>
                    <th scope="col">{{ __('admin.updated_at') }}</th>
                    <th scope="col"><span class="visually-hidden">{{ __('admin.actions') }}</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pages as $page)
                    <tr>
                        <td><a href="{{ route('admin.pages.edit', $page) }}">{{ $page->title }}</a>@if ($page->is_featured) <span class="badge text-bg-warning">{{ __('admin.fields.is_featured') }}</span>@endif</td>
                        <td dir="ltr" class="text-end small"><code>/{{ $page->slug }}</code></td>
                        <td><x-admin.status-badge :model="$page" /></td>
                        <td class="small text-body-secondary">{{ $page->updated_at->diffForHumans() }} @if ($page->editor) {{ __('admin.by') }} {{ $page->editor->name }} @endif</td>
                        <td class="text-nowrap">
                            @if ($page->isPublished())<a class="btn btn-sm btn-outline-secondary" href="{{ $page->publicUrl() }}" target="_blank" rel="noopener">{{ __('admin.view_site') }}</a>@endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-body-secondary py-4">{{ __('admin.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $pages->links() }}</div>
@endsection
