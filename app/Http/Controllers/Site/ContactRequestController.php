<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\Site\StoreContactRequest;
use App\Models\ContactRequest;
use Illuminate\Http\RedirectResponse;

/**
 * Receives the home page contact form. Stores the request and returns the
 * visitor to the form with a confirmation; nothing is e-mailed.
 */
class ContactRequestController extends Controller
{
    public function store(StoreContactRequest $request): RedirectResponse
    {
        ContactRequest::create([
            'name' => $request->validated('name'),
            'phone' => $request->validated('phone'),
            'message' => $request->validated('message'),
            'ip' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 255),
        ]);

        return redirect()->to(route('home').'#contact')->with('contact_sent', true);
    }
}
