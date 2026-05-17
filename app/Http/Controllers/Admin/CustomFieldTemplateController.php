<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CustomFieldTemplates\CreateRequest;
use App\Http\Requests\Admin\CustomFieldTemplates\CustomFieldTemplatesRequest;
use Illuminate\Http\Request;
use App\Models\CustomFieldTemplate;
use App\Models\Event;
use App\Services\Admin\CustomFieldTemplateService;
use Illuminate\Support\Facades\Log;

class CustomFieldTemplateController extends Controller
{
    public function __construct(CustomFieldTemplateService $service)
    {
        $this->service = $service;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateRequest $request): RedirectResponse
    {
        $attributes = $request->only(['new'])['new'];
        Log::info("Saving custom_field_templates: ".json_encode($attributes));
        $this->service->create([
            'event_id'      => (int)$attributes['event_id'],
            'order'         => (int)$attributes['order'],
            'name'          => $attributes['name'],
            'description'   => $attributes['description'],
            'type'          => $attributes['type'],
        ]);
        return back()->withSuccess('Tạo mới trường thông tin thành công');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CustomFieldTemplatesRequest $request, CustomFieldTemplate $custom_field_template)
    {
        $attributes = array_merge(($request->only([
            // 'order',
            'name',
            'description',
            'type',
            'required',
            'unique',
            'is_lp',
            'is_checkin_mobile',
            'is_checkin_desktop',
            'show_prefix',
            'options',
            'accepts'
        ])), [

        ]);

        Log::info("Requesting custom_field_templates #{$custom_field_template->id}: ".json_encode($request->all()));
        Log::info("Updating custom_field_templates #{$custom_field_template->id}: ".json_encode($attributes));

        /* set for boolean columns */
        foreach ([
            'required',
            'unique',
            'is_lp',
            'show_prefix',
            'is_checkin_mobile',
            'is_checkin_desktop'
        ] as $field) {
            if (isset($attributes[$field])) {
                $attributes[$field] = (($attributes[$field] == "true" || $attributes[$field] == "1") ? 1 : 0);
            } else {
                // $attributes[$field] = 0;
                $attributes[$field] = $custom_field_template->$field;
            }
        }

        /* nếu set bắt buộc mà có landing page thì phải nhập trên landing page */
        if (isset($attributes['required']) && $attributes['required']) {
            $attributes['is_lp'] = 1;
        }

        /* avoid changing name of default fields */
        /* nếu là trường mặc định thì luôn bắt buộc nhập, kiểu dữ liệu cố định */
        if ($custom_field_template->is_default) {
            unset($attributes["type"]);

            if (in_array($custom_field_template->name, ['qrcode', 'name'])) {
                unset($attributes['name']);
                unset($attributes["required"]);
            }

            /* nếu là trường "qrcode" thì luôn là duy nhất */
            if (in_array($custom_field_template->name, ['qrcode'])) {
                unset($attributes["unique"]);
            }

            /* nếu là trường "name" thì luôn là duy nhất */
            if (in_array($custom_field_template->name, ['name'])) {
                unset($attributes["is_lp"]);
            }
        }

        /* nếu option đẩy lên không có giá trị thì remove ra */
        if (isset($attributes['options']) && count($attributes['options'])) {
            foreach ($attributes['options'] as $index => $option) {
                // if (empty($option['key']) || empty($option['value'])) {
                if (empty($option['value'])) {
                    unset($attributes['options'][$index]);
                }
            }
        }

        /* cài đặt checkins */
        $this->service->updateCheckinsField($custom_field_template->id, $request->only(['checkins'])['checkins'] ?? []);
        $custom_field_template->update($attributes);
        Log::info("UPDATED custom_field_templates #{$custom_field_template->id}: ".json_encode($attributes));
        return $this->responseSuccess(null, "Đã cập nhật giá trị cho {$custom_field_template->name}");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CustomFieldTemplate $custom_field_template)
    {
        $custom_field_template->event->touch();

        if ($custom_field_template->is_default) {
            return $this->responseError("Không thể xoá các trường thông tin mặc định");
        }

        $this->service->delete($custom_field_template->id);
        return $this->responseSuccess(null, "Đã xoá trường {$custom_field_template->name}");
    }

    public function delete(Request $request)
    {
        var_dump($request->all());
        die();
        $this->service->delete($template->id);
        return redirect()->route('admin.clients.index')->withSuccess(__('clients.deleted'));
    }

    public function updateOrders(Request $request)
    {
        $request->validate([
            'items' => [
                'required',
                'array'
            ]
        ]);

        $attributes = $request->only([
            'items',
        ]);

        foreach ($attributes['items'] as $item) {
            $this->service->update((int)$item['id'], [
                'order' => (int)$item['order'],
            ]);
        }

        return $this->responseSuccess(null, "Cập nhật thứ tự thành công");
    }
}
