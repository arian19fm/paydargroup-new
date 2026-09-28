<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FaqRequest;
use App\Models\Faq;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Faq::class, 'faq');
    }

    public function index(Request $request): View
    {
        $faqs = Faq::query()
            ->when($request->string('q')->toString(), fn ($q, $term) => $q->where(fn ($w) => $w
                ->where('question', 'like', "%{$term}%")
                ->orWhere('answer', 'like', "%{$term}%")))
            ->ordered()
            ->paginate(config('cms.per_page'))
            ->withQueryString();

        return view('admin.faqs.index', compact('faqs'));
    }

    public function create(): View
    {
        return view('admin.faqs.form', ['faq' => new Faq(['is_active' => true])]);
    }

    public function store(FaqRequest $request): RedirectResponse
    {
        Faq::create($request->faqData());

        return redirect()->route('admin.faqs.index')->with('success', __('admin.saved'));
    }

    public function edit(Faq $faq): View
    {
        return view('admin.faqs.form', compact('faq'));
    }

    public function update(FaqRequest $request, Faq $faq): RedirectResponse
    {
        $faq->update($request->faqData());

        return redirect()->route('admin.faqs.index')->with('success', __('admin.saved'));
    }

    public function destroy(Faq $faq): RedirectResponse
    {
        $faq->delete();

        return redirect()->route('admin.faqs.index')->with('success', __('admin.deleted'));
    }
}
