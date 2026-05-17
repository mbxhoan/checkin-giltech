<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ScanDomainMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $scanDomain = config('app.scan_domain');
        $currentHost = $request->getHost();

        if ($currentHost !== $scanDomain) {
            return redirect()->route('scan.index');
        }

        return $next($request);
    }
}
