<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiBasicAuthClientMiddleware
{
    use ApiResponser;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $configuredUsers = collect(explode(',', (string) config('api_security.client_basic_auth.users', '')))
            ->map(fn (string $user) => trim($user))
            ->filter(fn (string $user) => $user !== '')
            ->values()
            ->all();

        $configuredPassword = (string) config('api_security.client_basic_auth.password', '');
        $requestUser = (string) $request->getUser();
        $requestPassword = (string) $request->getPassword();

        $isAllowedUser = in_array($requestUser, $configuredUsers, true);
        $isAllowedPassword = $configuredPassword !== '' && hash_equals($configuredPassword, $requestPassword);

        if (!$isAllowedUser || !$isAllowedPassword) {
            return $this->responseError('Unauthorized', 401);
        }

        return $next($request);
    }
}
