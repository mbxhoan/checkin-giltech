<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Checkins\CheckinRequest;
use App\Http\Requests\Api\Checkins\MultiCheckinRequest;
use App\Http\Resources\Checkin as CheckinResource;
use App\Models\Checkin;
use App\Services\Api\CheckinService;
use Illuminate\Http\Request;

class CheckinController extends Controller
{
    public function __construct(CheckinService $service)
    {
        $this->service = $service;
    }

    public function checkin(CheckinRequest $request)
    {
        $this->service->attributes = $request->all();

        if ($result = $this->service->checkin()) {
            if (is_array($result)) {
                if ($result['checkin']) {
                    $model = $result['model'];
                    return $this->responseSuccess(new CheckinResource($model), $result['msg']);
                }

                return $this->responseError($result['msg'], 400, $result['client'] ?? null);
            }
        }

        return $this->responseError(__('responses.error'), 400);
    }

    public function multiCheckin(MultiCheckinRequest $request)
    {
        $this->service->attributes = $request->all();

        if ($result = $this->service->multiCheckin()) {
            if (is_array($result)) {
                if ($result['checkin']) {
                    return $this->responseSuccess(null, $result['msg']);
                }

                return $this->responseError($result['msg'], 400);
            }
        }

        return $this->responseError(__('responses.error'), 400);
    }
}
