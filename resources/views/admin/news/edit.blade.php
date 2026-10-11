@extends('layouts.admin')

@section('title', __('admin.news.title'))
@php $breadcrumbs = [['label' => __('admin.nav.news')]]; @endphp

@section('actions')
    <form method="POST" action="{{ route('admin.news.run') }}">
        @csrf
        <button type="submit" class="btn btn-primary btn-sm" @disabled($active || ! $hasApiKey)>{{ $active ? __('admin.news.running') : __('admin.news.run_now') }}</button>
    </form>
@endsection

@section('content')
    <p class="text-body-secondary small">{{ __('admin.news.help') }}</p>

    @unless ($hasApiKey)
        <div class="alert alert-warning small" role="alert">{{ __('admin.news.no_api_key') }}</div>
    @endunless
    @unless ($schedulerOk)
        <div class="alert alert-warning small" role="alert">{{ __('admin.news.no_scheduler') }} <code class="d-block mt-2 text-start text-break" dir="ltr">* * * * * cd {{ base_path() }} &amp;&amp; php artisan schedule:run &gt;&gt; /dev/null 2&gt;&amp;1</code></div>
    @endunless

    <div class="row g-4">
        <div class="col-xl-5">
            <form method="POST" action="{{ route('admin.news.update') }}" class="card" novalidate>
                <div class="card-body">
                    @csrf
                    @method('PUT')
                    <x-admin.form.checkbox name="enabled" :label="__('admin.news.fields.enabled')" :checked="$settings->enabled" />
                    <x-admin.form.input name="run_at" type="time" :label="__('admin.news.fields.run_at')" :value="$settings->runAt" :help="__('admin.news.fields.run_at_help', ['zone' => config('news.timezone')])" dir="ltr" required />
                    <x-admin.form.input name="articles_per_run" type="number" min="1" :max="config('news.max_articles_per_run')" :label="__('admin.news.fields.articles_per_run')" :value="$settings->articlesPerRun" required />
                    <x-admin.form.checkbox name="auto_publish" :label="__('admin.news.fields.auto_publish')" :checked="$settings->autoPublish" :help="__('admin.news.fields.auto_publish_help')" />
                    <x-admin.form.textarea name="topics" :label="__('admin.news.fields.topics')" :value="$stored['topics']" rows="4" :help="__('admin.news.fields.topics_help')" :placeholder="implode(PHP_EOL, config('news.default_topics'))" />
                    <x-admin.form.select name="category_id" :label="__('admin.news.fields.category')" :options="$categories" :selected="$settings->categoryId" :placeholder="__('admin.none')" />
                    <x-admin.form.select name="author_id" :label="__('admin.news.fields.author')" :options="$authors" :selected="$settings->authorId" :placeholder="__('admin.none')" />
                    <x-admin.form.input name="cover_media_id" type="number" min="1" :label="__('admin.news.fields.cover')" :value="$settings->coverMediaId" :help="__('admin.news.fields.cover_help')" />
                    <button type="submit" class="btn btn-primary">{{ __('admin.save') }}</button>
                </div>
            </form>
        </div>

        <div class="col-xl-7">
            <h2 class="h6 fw-bold mb-3">{{ __('admin.news.runs') }}</h2>
            <div class="table-responsive bg-body rounded border">
                <table class="table align-middle mb-0 small">
                    <thead><tr>
                        <th scope="col">{{ __('admin.news.run_time') }}</th>
                        <th scope="col">{{ __('admin.news.run_status') }}</th>
                        <th scope="col">{{ __('admin.news.run_articles') }}</th>
                        <th scope="col">{{ __('admin.news.run_cost') }}</th>
                    </tr></thead>
                    <tbody>
                        @forelse ($runs as $run)
                            <tr>
                                <td class="text-nowrap">
                                    {{ \App\Support\Localization\Jalali::format($run->created_at).' – '.fa_digits($run->created_at->timezone(config('news.timezone'))->format('H:i')) }}
                                    <div class="text-body-secondary">{{ __('admin.news.triggers.'.$run->trigger) }}@if ($run->requester) — {{ $run->requester->name }}@endif</div>
                                </td>
                                <td>
                                    <span @class(['badge', 'text-bg-success' => $run->status === 'succeeded', 'text-bg-danger' => $run->status === 'failed', 'text-bg-secondary' => in_array($run->status, ['queued', 'running'], true)])>{{ __('admin.news.statuses.'.$run->status) }}</span>
                                    @if ($run->error)<div class="text-danger mt-1" dir="auto">{{ \Illuminate\Support\Str::limit($run->error, 300) }}</div>@endif
                                </td>
                                <td>
                                    @foreach ($run->articles as $article)
                                        <div><a href="{{ route('admin.articles.edit', $article) }}">{{ $article->title }}</a> <x-admin.status-badge :model="$article" /></div>
                                    @endforeach
                                </td>
                                <td class="text-nowrap" dir="ltr">@if ($run->input_tokens)~${{ number_format($run->estimatedCost(), 2) }}<div class="text-body-secondary">{{ number_format($run->input_tokens + $run->output_tokens) }} tok · {{ $run->web_searches }} search</div>@endif</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-body-secondary py-4">{{ __('admin.empty') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $runs->links() }}</div>
        </div>
    </div>
@endsection
