<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Api\LanguageService;
use App\Http\Resources\Language as LanguageResource;

class LanguageController extends Controller
{
    public function __construct(LanguageService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $languages = $this->service->getListByAttributes();
        $languages = LanguageResource::collection($languages);
        return $this->responseSuccess($languages, "Danh sách ngôn ngữ");
    }
}
