@extends('layouts.admin')

@php $editing = $menu->exists; @endphp
@section('title', $editing ? __('admin.edit').': '.$menu->name : __('admin.create'))
@php $breadcrumbs = [['label' => __('admin.nav.menus'), 'url' => route('admin.menus.index')], ['label' => $editing ? $menu->name : __('admin.create')]]; @endphp

@section('content')
    <div class="row g-4">
        <div class="col-lg-4">
            <form method="POST" action="{{ $editing ? route('admin.menus.update', $menu) : route('admin.menus.store') }}" class="card" novalidate>
                <div class="card-body">
                    @csrf
                    @if ($editing) @method('PUT') @endif
                    <x-admin.form.input name="name" :label="__('admin.fields.name')" :value="$menu->name" required />
                    <x-admin.form.input name="location" :label="__('admin.fields.location')" :value="$menu->location" dir="ltr" required help="main, footer, …" />
                    <button type="submit" class="btn btn-primary">{{ __('admin.save') }}</button>
                </div>
            </form>
        </div>

        @if ($editing)
            @php
                $pageOptions = $pages->mapWithKeys(fn ($p) => [$p->id => $p->title.($p->isPublished() ? '' : ' ('.__('admin.status.draft').')')])->all();
                $parentOptions = $menu->items->mapWithKeys(fn ($i) => [$i->id => $i->label])->all();
                $targetOptions = ['_self' => __('admin.fields.target_self'), '_blank' => __('admin.fields.target_blank')];
            @endphp
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header h6 mb-0 py-2">{{ __('admin.menus.items') }}</div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead><tr>
                                <th scope="col">{{ __('admin.fields.label') }}</th>
                                <th scope="col">{{ __('admin.fields.page') }} / {{ __('admin.fields.url') }}</th>
                                <th scope="col">{{ __('admin.fields.parent') }}</th>
                                <th scope="col">{{ __('admin.fields.sort_order') }}</th>
                                <th scope="col">{{ __('admin.fields.is_active') }}</th>
                                <th scope="col"><span class="visually-hidden">{{ __('admin.actions') }}</span></th>
                            </tr></thead>
                            <tbody>
                                @forelse ($menu->items as $item)
                                    <tr>
                                        <td>{{ $item->label }}</td>
                                        <td class="small" dir="ltr">
                                            @if ($item->page)
                                                {{ $item->page->title }}
                                                @unless ($item->page->isPublished())<span class="badge text-bg-warning" title="{{ __('admin.menus.page_unpublished') }}">{{ __('admin.status.draft') }}</span>@endunless
                                            @else
                                                <code>{{ $item->url }}</code>
                                            @endif
                                        </td>
                                        <td class="small">{{ $item->parent?->label ?? __('admin.none') }}</td>
                                        <td>{{ $item->sort_order }}</td>
                                        <td>{{ $item->is_active ? __('admin.yes') : __('admin.no') }}</td>
                                        <td class="text-nowrap">
                                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#item-{{ $item->id }}" aria-expanded="false" aria-controls="item-{{ $item->id }}">{{ __('admin.edit') }}</button>
                                            <x-admin.delete-button :action="route('admin.menus.items.destroy', [$menu, $item])" />
                                        </td>
                                    </tr>
                                    <tr class="collapse" id="item-{{ $item->id }}">
                                        <td colspan="6" class="bg-body-tertiary">
                                            <form method="POST" action="{{ route('admin.menus.items.update', [$menu, $item]) }}" class="row g-2 align-items-end" novalidate>
                                                @csrf
                                                @method('PUT')
                                                <div class="col-md-4"><x-admin.form.input name="label" id="label-{{ $item->id }}" :label="__('admin.fields.label')" :value="$item->label" required /></div>
                                                <div class="col-md-4"><x-admin.form.select name="page_id" id="page-{{ $item->id }}" :label="__('admin.fields.page')" :options="$pageOptions" :selected="$item->page_id" :placeholder="__('admin.none')" /></div>
                                                <div class="col-md-4"><x-admin.form.input name="url" id="url-{{ $item->id }}" :label="__('admin.fields.url')" :value="$item->url" dir="ltr" /></div>
                                                <div class="col-md-4"><x-admin.form.select name="parent_id" id="parent-{{ $item->id }}" :label="__('admin.fields.parent')" :options="array_diff_key($parentOptions, [$item->id => null])" :selected="$item->parent_id" :placeholder="__('admin.none')" /></div>
                                                <div class="col-md-3"><x-admin.form.select name="target" id="target-{{ $item->id }}" :label="__('admin.fields.target')" :options="$targetOptions" :selected="$item->target" /></div>
                                                <div class="col-md-2"><x-admin.form.input name="sort_order" id="sort-{{ $item->id }}" type="number" min="0" :label="__('admin.fields.sort_order')" :value="$item->sort_order" /></div>
                                                <div class="col-md-2"><x-admin.form.checkbox name="is_active" id="active-{{ $item->id }}" :label="__('admin.fields.is_active')" :checked="$item->is_active" /></div>
                                                <div class="col-md-1 mb-3"><button type="submit" class="btn btn-primary btn-sm">{{ __('admin.save') }}</button></div>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-body-secondary py-4">{{ __('admin.menus.no_items') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.menus.items.store', $menu) }}" class="card" novalidate>
                    <div class="card-header h6 mb-0 py-2">{{ __('admin.menus.add_item') }}</div>
                    <div class="card-body row g-2 align-items-end">
                        @csrf
                        <div class="col-md-4"><x-admin.form.input name="label" :label="__('admin.fields.label')" required /></div>
                        <div class="col-md-4"><x-admin.form.select name="page_id" :label="__('admin.fields.page')" :options="$pageOptions" :placeholder="__('admin.none')" /></div>
                        <div class="col-md-4"><x-admin.form.input name="url" :label="__('admin.fields.url')" dir="ltr" /></div>
                        <div class="col-md-4"><x-admin.form.select name="parent_id" :label="__('admin.fields.parent')" :options="$parentOptions" :placeholder="__('admin.none')" /></div>
                        <div class="col-md-3"><x-admin.form.select name="target" :label="__('admin.fields.target')" :options="$targetOptions" selected="_self" /></div>
                        <div class="col-md-2"><x-admin.form.input name="sort_order" type="number" min="0" :label="__('admin.fields.sort_order')" value="0" /></div>
                        <div class="col-md-2"><x-admin.form.checkbox name="is_active" :label="__('admin.fields.is_active')" :checked="true" /></div>
                        <div class="col-md-1 mb-3"><button type="submit" class="btn btn-primary btn-sm">{{ __('admin.create') }}</button></div>
                    </div>
                </form>
            </div>
        @endif
    </div>
@endsection
