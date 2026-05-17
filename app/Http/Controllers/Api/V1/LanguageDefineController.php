<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Api\LanguageService;
use Illuminate\Support\Facades\File;

class LanguageDefineController extends Controller
{
    public function __construct(LanguageService $service)
    {
        $this->service = $service;
    }

    public function getLanguageFile(string $lang, string $file)
    {
        $path = lang_path("{$lang}/{$file}.php");

        if (!File::exists($path)) {
            return $this->responseError("Not found");
        }

        $data = include $path;
        return $this->responseSuccess($data, "File {$file}");
    }
}
