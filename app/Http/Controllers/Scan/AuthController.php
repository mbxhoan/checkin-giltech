<?php

namespace App\Http\Controllers\Scan;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function viewLogin()
    {
        return view('scan.auth.login');
    }

    public function loginByQrcode(Request $request)
    {
        if ($request->ajax()) {
            $request->validate([
                'code'      => ['required', 'regex:/^[a-zA-Z0-9-+_#$*%]+$/'],
            ]);

            // check user
            $user = User::where('login_code', trim($request->code))
                ->first();

            if (!$user) {
                return $this->responseError('Thông tin đăng nhập không chính xác.', 400);
            }

            // check scanner
            if ($user->status !== User::STATUS_ACTIVE ||
                // $user->type !== User::TYPE_SCANNER ||
                !$user->hasRole(Role::ROLE_SCANNER)) {
                return $this->responseError('Tài khoản của bạn không có quyền truy cập trang này.', 400);
            }

            Auth::login($user);
            $attributes = [
                'last_login_at' => Carbon::now()->toDateTimeString(),
                'session_id'    => Session::getId(),
            ];

            $user->update($attributes);
            $request->session()->regenerate();
            return $this->responseSuccess([
                'redirectTo' => route('scan.index')
            ], 'Tài khoản của bạn không có quyền truy cập trang này.');
        }

        return $this->responseError('Đăng nhập không thành công.', 400);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username'  => ['required', 'regex:/^[a-zA-Z0-9\-_.]+$/',],
            'password'  => ['required'],
        ]);

        // check user
        $user = User::where('username', strtolower($request->username))
            ->first();

        if (!$user) {
            return back()->withErrors([
                'username' => 'Thông tin đăng nhập không chính xác.',
            ])->onlyInput('username');
        }

        /* avoid sysadmin */
        if ($user->isSysAdmin()) {
            return back()->withErrors([
                'username' => 'Tài khoản của bạn không có quyền truy cập trang này.',
            ])->onlyInput('username');
        }

        // check scanner
        if ($user->status !== User::STATUS_ACTIVE ||
            // $user->type !== User::TYPE_SCANNER ||
            !$user->hasRole(Role::ROLE_SCANNER)) {
            return back()->withErrors([
                'username' => 'Tài khoản của bạn không có quyền truy cập trang này.',
            ])->onlyInput('username');
        }

        // try login
        if (Auth::attempt($credentials)) {
            $attributes = [
                'last_login_at' => Carbon::now()->toDateTimeString(),
                'session_id'    => Session::getId(),
            ];

            $user->update($attributes);
            $request->session()->regenerate();
            return redirect()->intended(route('scan.index'));
        }

        return back()->withErrors([
            'username' => 'Thông tin đăng nhập không chính xác.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('scan.login');
    }
}
