<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\HttpFoundation\Response;

class ThrottleRequestsByIp
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->is('api/*') || !config('api_security.global_ip_throttle.enabled', false)) {
            return $next($request);
        }

        $ip = $request->ip();
        $key = "api:global-ip-rate-limit:{$ip}";
        $maxAttempts = max(60, (int) config('api_security.global_ip_throttle.per_minute', 2000));
        $decaySeconds = 60;

        cache()->add($key, 0, $decaySeconds);
        $attempts = (int) cache()->increment($key);

        if ($attempts > $maxAttempts) {
            throw new TooManyRequestsHttpException(60, 'Too Many Requests');
        }

        return $next($request);
    }
}
