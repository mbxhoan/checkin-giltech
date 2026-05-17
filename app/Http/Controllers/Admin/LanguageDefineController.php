<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LanguageDefines\EditValueRequest;
use App\Models\Event;
use Illuminate\Http\Request;
use App\Services\Admin\LanguageDefineService;

class LanguageDefineController extends Controller
{
    public function __construct(LanguageDefineService $service)
    {
        $this->service = $service;
    }

    /**
     * Display the specified resource edit form.
     */
    public function editValue(EditValueRequest $request)
    // public function editValue(Request $request)
    {
        $attributes = $request->only([
            'language_id',
            'event_id',
            'name',
            'value',
            'customs',
        ]);

        $language = $this->service->language()->findByAttributes([
            'id' => $attributes['language_id']
        ]);

        if (!$language) {
            if ($request->ajax()) {
                return $this->responseError("Không tìm thấy ngôn ngữ đã chọn");
            }

            return back()->withErrors("Không tìm thấy ngôn ngữ đã chọn");
        }

        $model = $this->service->findByAttributes([
            'event_id'      => $attributes['event_id'],
            'language_id'   => $language->id,
            'keyword'       => $attributes['name'],
        ]);

        /* xử lý customs */
        if (isset($attributes['customs']) && is_array($attributes['customs'])) {
            $this->service->landing_page()->handleCustoms($attributes['customs']);
        }

        if ($model) {
            $this->service->update($model->id, [
                'translate' => $attributes['value'],
            ]);

            $this->service->generateLang($model->event->code);

            if ($request->ajax()) {
                return $this->responseSuccess(null, "Cập nhật thành công");
            }

            return back()->withSuccess("Cập nhật thành công");
        } else {
            $model = $this->service->create([
                'event_id'      => $attributes['event_id'],
                'language_id'   => $language->id,
                'keyword'       => $attributes['name'],
                'translate'     => $attributes['value']
            ]);

            $this->service->generateLang($model->event->code);
        }

        if ($request->ajax()) {
            return $this->responseSuccess(null, "Thêm mới thành công");
        }

        return back()->withSuccess("Thêm mới thành công");
    }

    public function generateLang(Event $event)
    {
        $this->authorize('generate_lang', $event);

        $this->service->generateLanguageDefinesByCmd($event->code);
        return back()->withSuccess("Ngôn ngữ đang được thay đổi");
    }
}
