<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PageAccessLogRequest;
use App\Http\Resources\Checkin as CheckinResource;
use App\Http\Resources\PageAccessLog;
use App\Services\Api\PageAccessLogService;

class PageAccessLogController extends Controller
{
    public function __construct(PageAccessLogService $service)
    {
        $this->service = $service;
    }

    public function store(PageAccessLogRequest $request)
    {
        $attributes = $request->only([
            'lp_id',
            'page',
            'ip_address',
        ]);

        $model = $this->service->create($attributes);

        if ($model) {
            return $this->responseSuccess(new PageAccessLog($model), "Log thành công");
        }

        return $this->responseError(__('responses.error'), 400);
    }
}
