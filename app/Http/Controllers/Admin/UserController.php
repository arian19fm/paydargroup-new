<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(User::class, 'user', ['except' => ['destroy']]);
    }

    public function index(Request $request): View
    {
        $users = User::query()
            ->with('roles:id,name')
            ->when($request->string('q')->toString(), fn ($q, $term) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")))
            ->orderBy('name')
            ->paginate(config('cms.per_page'))
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.users.form', ['user' => new User(['is_active' => true]), 'roles' => $this->assignableRoles()]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $user = User::create($request->userData());
        $user->syncRoles($request->roles());

        return redirect()->route('admin.users.index')->with('success', __('admin.saved'));
    }

    public function edit(User $user): View
    {
        $user->load('roles:id,name');

        return view('admin.users.form', ['user' => $user, 'roles' => $this->assignableRoles()]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $user->update($request->userData());
        $user->syncRoles($request->roles());

        return redirect()->route('admin.users.index')->with('success', __('admin.saved'));
    }

    /** Activate / deactivate an account (no hard deletes). */
    public function toggleActive(User $user): RedirectResponse
    {
        $this->authorize('toggleActive', $user);

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', __('admin.saved'));
    }

    protected function assignableRoles()
    {
        $names = auth()->user()->isSuperAdmin() ? ['super_admin', 'admin', 'editor'] : ['admin', 'editor'];

        return Role::query()->whereIn('name', $names)->orderBy('name')->get(['id', 'name']);
    }
}
