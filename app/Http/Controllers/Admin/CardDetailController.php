<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CardDetails\StoreRequest;
use App\Http\Requests\Admin\CardDetails\UpdateRequest;
use App\Services\Admin\CardDetailService;
use App\Models\CardDetail;

class CardDetailController extends Controller
{
    public function __construct(CardDetailService $service)
    {
        $this->service = $service;
    }

    public function store(StoreRequest $request)
    {
        $attributes = $request->only([
            'card_id',
            'card_code',
            'type',
            'field',
        ]);

        if ($attributes['type'] == CardDetail::TYPE_IMG) {
            if (!in_array($attributes['field'], [
                "qrcode",
            ])) {
                return back()->withErrors("Loại Ảnh chỉ có thể dùng cho trường Qrcode");
            }
        }

        $attributes['font_size'] = $attributes['font_size'] ?? 5; // em
        $cardDetail = $this->service->create($attributes);

        if ($cardDetail) {
            return back()->withSuccess("Tạo mới thành phần cho thư/thiệp mời thành công");
            return redirect()->route('admin.cards.edit', [
                'card'          => $cardDetail->card,
            ])->withSuccess("Tạo mới thành phần cho thư/thiệp mời thành công");
        }

        return back()->withErrors("Đã có lỗi trong quá trình tạo thành phần cho thư/thiệp mời");
    }

    public function update(UpdateRequest $request, CardDetail $card_detail)
    {
        $attributes = $request->only([
            'name',
            'is_show',
            'color',
            'font',
            'font_size',
            'h_align',
            'pos_x',
            'pos_y',
            'width',
            'height',
        ]);

        $attributes['width'] = $attributes['width'] ?? 80;
        $attributes['height'] = $attributes['height'] ?? 80;

        /* set for boolean columns */
        foreach ([
            'is_show',
        ] as $field) {
            if (isset($attributes[$field])) {
                $attributes[$field] = (($attributes[$field] == "true" || $attributes[$field] == "1") ? 1 : 0);
            } else {
                $attributes[$field] = 0;
            }
        }

        if ($attributes['is_show']) {
            $attributes['status'] = CardDetail::STATUS_ACTIVE;
        } else {
            $attributes['status'] = CardDetail::STATUS_DELETED;
        }

        $this->service->update($card_detail->id, $attributes);
        return $this->responseSuccess(null, "Đã cập nhật giá trị cho thành phần {$card_detail->name}");
    }
}
