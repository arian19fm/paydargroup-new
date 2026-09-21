<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MenuRequest;
use App\Models\Menu;
use App\Models\Page;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class MenuController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Menu::class, 'menu');
    }

    public function index(): View
    {
        $menus = Menu::query()->withCount('items')->orderBy('name')->get();

        return view('admin.menus.index', compact('menus'));
    }

    public function create(): View
    {
        return view('admin.menus.form', ['menu' => new Menu]);
    }

    public function store(MenuRequest $request): RedirectResponse
    {
        $menu = Menu::create($request->validated());

        return redirect()->route('admin.menus.edit', $menu)->with('success', __('admin.saved'));
    }

    /** Menu details plus its item tree and the item editor. */
    public function edit(Menu $menu): View
    {
        $menu->load(['items.page:id,title,status,published_at', 'items.parent:id,label']);

        return view('admin.menus.form', [
            'menu' => $menu,
            'pages' => Page::query()->orderBy('title')->get(['id', 'title', 'status', 'published_at']),
        ]);
    }

    public function update(MenuRequest $request, Menu $menu): RedirectResponse
    {
        $menu->update($request->validated());

        return redirect()->route('admin.menus.edit', $menu)->with('success', __('admin.saved'));
    }

    public function destroy(Menu $menu): RedirectResponse
    {
        $menu->delete();

        return redirect()->route('admin.menus.index')->with('success', __('admin.deleted'));
    }
}
