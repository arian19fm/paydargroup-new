@extends('layouts.admin')

@section('title', __('admin.nav.articles'))
@php $breadcrumbs = [['label' => __('admin.nav.articles')]]; @endphp

@section('actions')
    @can('create', App\Models\Article::class)
        <a href="{{ route('admin.articles.create') }}" class="btn btn-primary btn-sm">{{ __('admin.create') }}</a>
    @endcan
@endsection

@section('content')
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
                    <th scope="col">{{ __('admin.fields.categories') }}</th>
                    <th scope="col">{{ __('admin.fields.author') }}</th>
                    <th scope="col">{{ __('admin.status.label') }}</th>
                    <th scope="col">{{ __('admin.fields.published_at') }}</th>
                    <th scope="col"><span class="visually-hidden">{{ __('admin.actions') }}</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($articles as $article)
                    <tr>
                        <td><a href="{{ route('admin.articles.edit', $article) }}">{{ $article->title }}</a><br><code class="small" dir="ltr">/articles/{{ $article->slug }}</code></td>
                        <td class="small">{{ $article->categories->pluck('name')->join('، ') ?: __('admin.none') }}</td>
                        <td class="small">{{ $article->author?->name ?? __('admin.none') }}</td>
                        <td><x-admin.status-badge :model="$article" /></td>
                        <td class="small text-body-secondary">{{ $article->published_at?->format('Y-m-d H:i') ?? __('admin.none') }}</td>
                        <td class="text-nowrap">
                            @if ($article->isPublished())<a class="btn btn-sm btn-outline-secondary" href="{{ $article->publicUrl() }}" target="_blank" rel="noopener">{{ __('admin.view_site') }}</a>@endif
                            @can('delete', $article)<x-admin.delete-button :action="route('admin.articles.destroy', $article)" />@endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-body-secondary py-4">{{ __('admin.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $articles->links() }}</div>
@endsection
