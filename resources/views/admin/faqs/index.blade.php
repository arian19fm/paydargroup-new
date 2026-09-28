@extends('layouts.admin')

@section('title', __('admin.faqs.title'))
@php $breadcrumbs = [['label' => __('admin.nav.faqs')]]; @endphp

@section('actions')
    @can('create', App\Models\Faq::class)
        <a href="{{ route('admin.faqs.create') }}" class="btn btn-primary btn-sm">{{ __('admin.faqs.add') }}</a>
    @endcan
@endsection

@section('content')
    <p class="text-body-secondary small">{{ __('admin.faqs.help') }}</p>
    <x-admin.search-form />
    <div class="table-responsive bg-body rounded border">
        <table class="table table-hover align-middle mb-0">
            <thead><tr>
                <th scope="col">{{ __('admin.fields.question') }}</th>
                <th scope="col">{{ __('admin.fields.sort_order') }}</th>
                <th scope="col">{{ __('admin.fields.is_active') }}</th>
                <th scope="col"><span class="visually-hidden">{{ __('admin.actions') }}</span></th>
            </tr></thead>
            <tbody>
                @forelse ($faqs as $faq)
                    <tr>
                        <td><a href="{{ route('admin.faqs.edit', $faq) }}">{{ $faq->question }}</a></td>
                        <td>{{ fa_digits($faq->sort_order) }}</td>
                        <td>{{ $faq->is_active ? __('admin.yes') : __('admin.no') }}</td>
                        <td class="text-end">@can('delete', $faq)<x-admin.delete-button :action="route('admin.faqs.destroy', $faq)" />@endcan</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-body-secondary py-4">{{ __('admin.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $faqs->links() }}</div>
@endsection
