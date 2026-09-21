<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    /**
     * Render the public home page.
     */
    public function __invoke(): View
    {
        return view('site.home');
    }
}
