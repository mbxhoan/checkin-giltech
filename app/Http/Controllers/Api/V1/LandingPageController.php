<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Api\LandingPageService;
use App\Http\Resources\LandingPage as LandingPageResource;

class LandingPageController extends Controller
{
    public function __construct(LandingPageService $service)
    {
        $this->service = $service;
    }

    public function getBySlug(string $slug, string $lang)
    {
        $landingPage = $this->service->findByAttributes([
            'slug' => $slug
        ]);

        if ($landingPage && $landingPage->checkIfLandingPageIsValid()) {
            return $this->responseSuccess(new LandingPageResource($landingPage, $lang), "Landing page");
        }

        return $this->responseError("Không tìm thấy landing page", 404);
    }
}
