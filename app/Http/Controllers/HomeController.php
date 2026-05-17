<?php

namespace App\Http\Controllers;

use App\Services\Web\HomeService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct(HomeService $service)
    {
        $this->service = $service;
    }

    public function home()
    {
        return view('web.home');
    }

    // public function test()
    // {
    //     return view('web.test');
    // }

    public function changeLanguage(Request $request, $lang)
    {
        $session = $request->session();
        return $this->service->language()->changeLanguage($session, $lang);
    }

    public function getPlaceholderQrcode()
    {
        $info = config('info.placeholders');
        $path = $info['qrcode'];

        if (file_exists($path)) {
            return response()->file($path);
        }

        return redirect()->route('web.home')->withErrors('Không tìm thấy thông tin');
    }
}
