<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Exceptions\InvalidHeaderException;

class EnsureHeaderIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if ($this->validateHeader($request)) {
            return $next($request);
        }

        throw new InvalidHeaderException;
    }

    protected function validateHeader($request)
    {
        $excludedRoutes = [
            'api/registrations/*',
            'api/payments/create',
            'api/payments/status/*',
            'api/orders/*/apply-promo',
            'api/orders/*/remove-promo',
            'api/orders/*/payment-attempts',
            'api/orders/*/cancel',
            'api/orders/*/refund',
            'api/orders/*/change-ticket',
            'api/events/*/tickets',
            'api/payments/onepay/*',
            'api/onepay/*',
            'api/portal/*',
        ];

        if ($request->is($excludedRoutes)) {
            return true;
        }

        $userAgents = config('app.user-agents', ['PDA', 'WebPortal', 'MobileApp', 'ApiPortal', 'Postmark']);

        if ($request->expectsJson()) {
            if ($request->hasHeader('User-Agent')) {
                if (\in_array($request->header('User-Agent'), [
                    'ApiPortal',
                    'Postmark'
                ])) {
                    if ($request->accepts(['application/json'])) {
                        return true;
                    }
                }

                if ($request->hasHeader('App-Key')) {
                    if (\in_array($request->header('User-Agent'), $userAgents)) {
                        if ($request->accepts(['application/json'])) {
                            $tmpAppKey = $request->header('App-Key');
                            $appKey = "base64:{$tmpAppKey}=";

                            if ($appKey === config('app.key')) {
                                return true;
                            }
                        }
                    }

                    if ($request->accepts(['application/json'])) {
                        $tmpAppKey = $request->header('App-Key');
                        $appKey = "base64:{$tmpAppKey}=";

                        if ($appKey === config('app.key')) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }
}
