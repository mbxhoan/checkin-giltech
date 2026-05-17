<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ScanMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // check domain
        $scanDomain = config('app.scan_domain');
        $currentHost = $request->getHost();

        if ($currentHost !== $scanDomain) {
            // redirect to login page
            // show error message
            return redirect()->route('scan.login')->with('error', 'Domain không hợp lệ.');
        }

        // if access login page or logout page
        if ($request->routeIs('scan.login') ||
            // $request->routeIs('scan.login-by-qrcode') ||
            $request->routeIs('scan.login.post') ||
            $request->routeIs('scan.logout')
        ) {
            return $next($request);
        }

        // check login
        if (!Auth::check()) {
            return redirect()->route('scan.login');
        }

        // get latest user info from DB and check again
        $user = Auth::user();
        $user->refresh(); // get latest data

        if ($user->status !== User::STATUS_ACTIVE ||
            // $user->type !== User::TYPE_SCANNER ||
            !$user->hasRole('scanner')) {

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('scan.login')->with('error', 'Tài khoản của bạn không còn hợp lệ hoặc đã bị thay đổi. Vui lòng đăng nhập lại.');
        }

        return $next($request);
    }
}
