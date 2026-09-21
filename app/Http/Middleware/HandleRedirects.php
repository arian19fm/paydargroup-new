<?php

namespace App\Http\Middleware;

use App\Support\Redirects\RedirectResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies managed 301/302 redirects before routing. Only GET/HEAD requests
 * are considered, infrastructure paths are exempt, and lookups are cached
 * per path by the resolver.
 */
class HandleRedirects
{
    public function __construct(protected RedirectResolver $resolver) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethodSafe() || $request->isMethod('OPTIONS')) {
            return $next($request);
        }

        $match = $this->resolver->resolve('/'.$request->path());

        if ($match === null) {
            return $next($request);
        }

        $this->resolver->recordHit((int) $match['id']);

        $destination = $match['destination_url'];

        if ($query = $request->getQueryString()) {
            $destination .= (str_contains($destination, '?') ? '&' : '?').$query;
        }

        return redirect()->to($destination, (int) $match['http_status']);
    }
}
