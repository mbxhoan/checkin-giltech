<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsValid
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
        $currentHost = $request->getHost();
        $driver = Auth::getDefaultDriver();

        if ($currentHost == "web") {
            $types = [
                User::TYPE_WEB
            ];
        }

        if ($this->validateHeader($driver, $types ?? [])) {
            return $next($request);
        }

        Auth::guard($driver)->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        // toastr()->error("Tài khoản đã quá thời hạn sử dụng! Vui lòng thử lại sau.");

        $response = redirect('/')
            ->withErrors('Tài khoản của bạn chưa được kích hoạt hoặc đã hết hạn sử dụng.');

        return $response;

        // return redirect('/');
    }

    protected function validateHeader($driver, $types)
    {
        // dd(Auth::user(), Auth::check(), auth()->user(), $driver, session()->all());
        if (Auth::check()) {
            if (!empty(auth($driver)->user())) {
                return auth($driver)->user()->checkIfValidUser($types);

                // if (!empty(auth($driver)->user()->expire_date)) {
                //     if (Helper::compareDateToToday(auth($driver)->user()->expire_date) == -1) {
                //         return false;
                //     }
                // }
            }
        }

        return true;
    }
}
