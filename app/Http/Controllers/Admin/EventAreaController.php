<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventArea;
use App\Services\Admin\EventAreaService;
use Illuminate\Http\Request;

class EventAreaController extends Controller
{
    public function __construct(EventAreaService $service)
    {
        $this->service = $service;
    }

    public function store(Request $request)
    {
        $attributes = $request->only(['event_id', 'name']);
        $area = $this->service->create($attributes);
        return redirect()->route('admin.events.edit', $area->event)->withSuccess("Tạo mới khu vực thành công");
    }

    public function update(Request $request, EventArea $event_area)
    {
        $attributes = $request->only(['description', 'client_types']);
        $this->service->update($event_area->id, $attributes);
        return redirect()->route('admin.events.edit', $event_area->event)->withSuccess("Tạo mới khu vực thành công");
    }
}
