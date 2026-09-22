<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoleRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Roles and the permissions they grant. Permissions themselves are code
 * (RolesAndPermissionsSeeder) — the panel only decides which role gets
 * which; super_admin is read-only here (see RolePolicy).
 */
class RoleController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Role::class, 'role');
    }

    public function index(): View
    {
        $roles = Role::query()->where('guard_name', 'web')
            ->withCount(['permissions', 'users'])
            ->orderByRaw("name = 'super_admin' desc")->orderBy('name')
            ->get();

        return view('admin.roles.index', compact('roles'));
    }

    public function create(): View
    {
        return view('admin.roles.form', [
            'role' => new Role,
            'groups' => RoleRequest::grouped(),
            'selected' => ['admin.access'],
        ]);
    }

    public function store(RoleRequest $request): RedirectResponse
    {
        $role = Role::create(['name' => $request->validated('name'), 'guard_name' => 'web']);
        $role->syncPermissions($request->permissions());
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('admin.roles.index')->with('success', __('admin.saved'));
    }

    public function edit(Role $role): View
    {
        return view('admin.roles.form', [
            'role' => $role,
            'groups' => RoleRequest::grouped(),
            'selected' => $role->permissions->pluck('name')->all(),
        ]);
    }

    public function update(RoleRequest $request, Role $role): RedirectResponse
    {
        $role->update(['name' => $request->validated('name')]);
        $role->syncPermissions($request->permissions());
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('admin.roles.index')->with('success', __('admin.saved'));
    }

    public function destroy(Role $role): RedirectResponse
    {
        $role->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('admin.roles.index')->with('success', __('admin.deleted'));
    }
}
