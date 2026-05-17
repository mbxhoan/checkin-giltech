<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();
        $types = [
            User::TYPE_WEB
        ];

        // Check if the user's expire_date is valid and status is active
        if (auth()->user()->checkIfValidUser($types) === false) {
            // Auth::guard('web')->logout();
            // $request->session()->invalidate();
            // $request->session()->regenerateToken();

            $this->destroy($request);
            throw ValidationException::withMessages([
                'email' => trans('Tài khoản của bạn chưa được kích hoạt, chưa được cấp quyền đăng nhập hoặc lý do khác...'),
            ]);

            /* sửa tạm chỗ này thì ko dính cái lỗi domain */
            return back()->withErrors('Tài khoản của bạn chưa được kích hoạt hoặc đã hết hạn sử dụng.');
            return redirect()->intended(RouteServiceProvider::HOME)->withErrors('Tài khoản của bạn chưa được kích hoạt hoặc đã hết hạn sử dụng.');
        }

        $attributes = [
            'last_login_at' => Carbon::now()->toDateTimeString(),
            'session_id'    => Session::getId(),
        ];

        auth()->user()->update($attributes);

        if (auth()->user()->isAdmin()) {
            return redirect()->intended(RouteServiceProvider::HOME);
        }

        return redirect()->route('admin.reports.report', [
            'event' => auth()->user()->event,
        ])->with('success', 'Đăng nhập thành công.');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
