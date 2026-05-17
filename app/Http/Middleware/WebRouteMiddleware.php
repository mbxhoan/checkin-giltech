<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class WebRouteMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->package_id) {
                $exceptRoutes = config("info.packages.{$user->package->code}.excepts.routes") ?? [];

                /* Chùa Minh Hiệp */
                if ($user->email == "cmh01@gmail.com") {
                    $exceptRoutes = array_filter($exceptRoutes, function ($item) {
                        return !in_array($item, [
                            'admin.landing_pages.*',
                            'admin.landing_page_campaigns.*',
                            'admin.language_defines.*',
                            'admin.campaigns.*',
                            'admin.campaign_details.*',
                            'admin.emails.*',
                            'admin.email_templates.*',
                            'admin.email_senders.*',
                        ]);
                    });

                    $exceptRoutes = array_values($exceptRoutes);
                }

                /* Thành - MKT */
                if ($user->email == "thanh.nv@giltech.com.vn") {
                    $exceptRoutes = array_filter($exceptRoutes, function ($item) {
                        return !in_array($item, [
                            'admin.campaigns.*',
                            'admin.campaign_details.*',
                            'admin.emails.*',
                            'admin.email_templates.*',
                            'admin.email_senders.*',
                        ]);
                    });

                    $exceptRoutes = array_values($exceptRoutes);
                }

                if (!empty($exceptRoutes) && count($exceptRoutes)) {
                    foreach ($exceptRoutes as $routePattern) {
                        if ($request->routeIs($routePattern)) {
                            return redirect()->route('home')->withErrors('Bạn không có quyền truy cập.');
                        }
                    }
                }
            }
        }

        return $next($request);
    }
}
