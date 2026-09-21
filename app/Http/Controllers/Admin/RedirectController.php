<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RedirectRequest;
use App\Models\Redirect;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RedirectController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Redirect::class, 'redirect');
    }

    public function index(Request $request): View
    {
        $redirects = Redirect::query()
            ->when($request->string('q')->toString(), fn ($q, $term) => $q->where(fn ($w) => $w
                ->where('source_path', 'like', "%{$term}%")
                ->orWhere('destination_url', 'like', "%{$term}%")))
            ->orderBy('source_path')
            ->paginate(config('cms.per_page'))
            ->withQueryString();

        return view('admin.redirects.index', compact('redirects'));
    }

    public function create(): View
    {
        return view('admin.redirects.form', ['redirect' => new Redirect(['http_status' => 301, 'is_active' => true])]);
    }

    public function store(RedirectRequest $request): RedirectResponse
    {
        Redirect::create($request->redirectData());

        return redirect()->route('admin.redirects.index')->with('success', __('admin.saved'));
    }

    public function edit(Redirect $redirect): View
    {
        return view('admin.redirects.form', compact('redirect'));
    }

    public function update(RedirectRequest $request, Redirect $redirect): RedirectResponse
    {
        $redirect->update($request->redirectData());

        return redirect()->route('admin.redirects.index')->with('success', __('admin.saved'));
    }

    public function destroy(Redirect $redirect): RedirectResponse
    {
        $redirect->delete();

        return redirect()->route('admin.redirects.index')->with('success', __('admin.deleted'));
    }
}
