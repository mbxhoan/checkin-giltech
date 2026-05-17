<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LabelDetails\StoreRequest;
use App\Http\Requests\Admin\LabelDetails\UpdateRequest;
use App\Services\Admin\LabelDetailService;
use App\Models\LabelDetail;

class LabelDetailController extends Controller
{
    public function __construct(LabelDetailService $service)
    {
        $this->service = $service;
    }

    public function store(StoreRequest $request)
    {
        $attributes = $request->only([
            'label_id',
            'label_code',
            'type',
            'field',
        ]);

        if ($attributes['type'] == LabelDetail::TYPE_IMG) {
            if (!in_array($attributes['field'], [
                "qrcode",
            ])) {
                return back()->withErrors("Loại Ảnh chỉ có thể dùng cho trường Qrcode");
            }
        }

        $attributes['unit'] = "%";
        $labelDetail = $this->service->create($attributes);

        if ($labelDetail) {
            return redirect()->route('admin.labels.edit', [
                'event'         => $labelDetail->label->event,
                'label'          => $labelDetail->label,
            ])->withSuccess("Tạo mới thành phần cho thư/thiệp mời thành công");
        }

        return back()->withErrors("Đã có lỗi trong quá trình tạo thành phần cho thư/thiệp mời");
    }

    public function update(UpdateRequest $request, LabelDetail $label_detail)
    {
        $attributes = $request->only([
            'name',
            'is_show',
            'color',
            'font',
            'size',
            'v_align',
            'h_align',
            'pos_x',
            'pos_y',
            'width',
            'height',
            'bold',
            'italic',
            'uppercase',
        ]);

        $attributes['width'] = $attributes['width'] ?? 50;
        $attributes['height'] = $attributes['height'] ?? 50;

        /* set for boolean columns */
        foreach ([
            'is_show',
            'bold',
            'italic',
            'uppercase',
        ] as $field) {
            if (isset($attributes[$field])) {
                $attributes[$field] = (($attributes[$field] == "true" || $attributes[$field] == "1") ? 1 : 0);
            } else {
                $attributes[$field] = 0;
            }
        }

        if ($attributes['is_show']) {
            $attributes['status'] = LabelDetail::STATUS_ACTIVE;
        } else {
            $attributes['status'] = LabelDetail::STATUS_DELETED;
        }

        $this->service->update($label_detail->id, $attributes);
        return $this->responseSuccess(null, "Đã cập nhật giá trị cho thành phần {$label_detail->name}");
    }
}
