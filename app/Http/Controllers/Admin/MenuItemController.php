<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MenuItemRequest;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Http\RedirectResponse;

/** Items are managed inside the menu edit screen (nested resource). */
class MenuItemController extends Controller
{
    public function store(MenuItemRequest $request, Menu $menu): RedirectResponse
    {
        $menu->items()->create($request->itemData());

        return redirect()->route('admin.menus.edit', $menu)->with('success', __('admin.saved'));
    }

    public function update(MenuItemRequest $request, Menu $menu, MenuItem $item): RedirectResponse
    {
        abort_unless($item->menu_id === $menu->id, 404);

        $item->update($request->itemData());

        return redirect()->route('admin.menus.edit', $menu)->with('success', __('admin.saved'));
    }

    public function destroy(Menu $menu, MenuItem $item): RedirectResponse
    {
        $this->authorize('update', $menu);
        abort_unless($item->menu_id === $menu->id, 404);

        $item->delete();

        return redirect()->route('admin.menus.edit', $menu)->with('success', __('admin.deleted'));
    }
}
