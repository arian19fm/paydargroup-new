<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SettingsRequest;
use App\Support\Settings\Settings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Settings are edited per group; the form is generated from the schema in
 * config/settings.php so no arbitrary keys can be created.
 */
class SettingsController extends Controller
{
    public function __construct(protected Settings $settings) {}

    public function edit(string $group): View
    {
        $groups = config('settings.groups');

        abort_unless(isset($groups[$group]), 404);

        return view('admin.settings.edit', [
            'group' => $group,
            'groups' => $groups,
            'schema' => $groups[$group],
            'values' => $this->settings->group($group),
        ]);
    }

    public function update(SettingsRequest $request, string $group): RedirectResponse
    {
        $this->settings->setGroup($group, $request->values());

        return redirect()->route('admin.settings.edit', $group)->with('success', __('admin.saved'));
    }
}
