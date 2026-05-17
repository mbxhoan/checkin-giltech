<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Audios\SetToEventRequest;
use App\Http\Requests\Admin\AudiosRequest;
use App\Models\Audio;
use App\Models\Event;
use App\Services\Admin\AudioService;

class AudioController extends Controller
{
    public function __construct(AudioService $service)
    {
        $this->service = $service;
    }

    /**
     * Show the application clients index.
     */
    public function store(AudiosRequest $request)
    {
        $attributes = $request->only(['text', 'voice']);
        $attributes['code'] = Audio::generateUniqueCode();
        $attributes['company_id'] = auth()->user()->company_id;
        $attributes['created_by'] = auth()->user()->id;
        $attributes['updated_by'] = auth()->user()->id;
        $audio = $this->service->create($attributes);
        $result = $this->service->generateSpeech($audio);

        if ($result['success']) {
            return back()->withSuccess("Tạo mới audio thành công");
        }

        return back()->withErrors("Không thể tạo audio. Lỗi: {$result['error']}");
    }

    public function update(AudiosRequest $request, Audio $audio)
    {
        $attributes = $request->only(['text', 'voice']);
        $attributes['updated_by'] = auth()->user()->id;
        $this->service->update($audio->id, $attributes);

        if ($attributes['text'] != $audio->text || $attributes['voice'] != $audio->voice) {
            $result = $this->service->generateSpeech($audio);

            if ($result['success']) {
                return back()->withSuccess("Cập nhật audio thành công");
            }

            return back()->withErrors("Không thể tạo audio. Lỗi: {$result['error']}");
        }

        return back()->withSuccess("Cập nhật audio thành công");
    }

    public function setToEvent(Event $event, SetToEventRequest $request)
    {
        $this->authorize('set_to_event_audio', $event);

        $attributes = array_filter($request->only(['sound_success', 'sound_fail']));

        if (count($attributes)) {
            $this->service->event()->update($event->id, $attributes);
            return back()->withSuccess("Cập nhật audio checkin thành công");
        }

        return back()->withErrors("Không cập nhật audio checkin");
    }
}
