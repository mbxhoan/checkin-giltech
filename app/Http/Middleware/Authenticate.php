<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if (!$request->expectsJson()) {
            $httpHost = $request->getHost();

            if ($httpHost == config('app.admin_domain')) {
                return route('admin.login');
            } else if ($httpHost == config('app.profile_domain')) {
                return route('profile.login');
            } else if ($httpHost == config('app.scan_domain')) {
                return route('scan.login');
            } else {
                return route('home');
            }
        }

        return $request->expectsJson() ? null : route('login');
    }
}
