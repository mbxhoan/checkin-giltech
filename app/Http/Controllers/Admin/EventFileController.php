<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Admin\EventFileService;
use App\Models\Event;
use App\Models\EventFile;

class EventFileController extends Controller
{
    public function __construct(EventFileService $service)
    {
        $this->service = $service;
    }

    /**
     * Update the specified resource in storage.
     */
    public function upload(Request $request, Event $event)
    {
        $request->validate([
            'medias'             => "required|array",
            'medias.*'           => "required|file|max:".config('app.upload_media_size_max')."|mimes:".config('app.upload_media_allow_types'),
        ],[
            'medias.required'    => 'Vui lòng chọn ít nhất một tệp.',
            'medias.array'       => 'Định dạng tệp không hợp lệ.',

            'medias.*.required'  => 'Mỗi tệp đều bắt buộc.',
            'medias.*.file'      => 'Tệp không hợp lệ.',
            'medias.*.max'       => 'Dung lượng tối đa cho mỗi tệp là :max kilobytes.',
            'medias.*.mimes'     => 'Định dạng tệp không được hỗ trợ. Chỉ chấp nhận: :values.',
        ]);

        $medias = $request->medias;

        foreach ($medias as $file) {
            if ($file) {
                $this->service->attributes['image'] = $file;
                $this->service->attributes['name'] = $file->getClientOriginalName();

                if ($result = $this->service->mediaLibraryService()->store()) {
                    if (!empty($result['media'])) {
                        $eventFile = $this->service->create([
                            'event_id'  => $event->id,
                            'media_id'  => $result['media']->id,
                            'name'      => $result['media']->getDownloadFilename(),
                            'type'      => $result['media']->mime_type,
                            'file_path' => $result['media']->getPathRelativeToRoot(),
                            'is_public' => true,
                            'status'    => EventFile::STATUS_ACTIVE,
                            // 'file_size' => $media->size,
                        ]);

                        // if ($eventFile) {
                        //     return back()->withSuccess('Nạp file thành công');
                        // }
                    } else {
                        // return back()->withErrors($result['msg']);
                    }
                }

                //     $media->getDownloadFilename(),              // "bg.png"
                //     $media->getPath(),                          // "/Users/leviackerman/Codes/checkin-v3/storage/app/public/medias/28/bg.png"
                //     $media->getFullUrl(),                       // "http://localhost:8000/storage/medias/28/bg.png"
                //     $media->getPathRelativeToRoot(),            // "28/bg.png"
                //     $media->getUrl('thumb'),                    // "http://localhost:8000/storage/medias/28/conversions/bg-thumb.jpg"
                //     config("filesystems.disks.public.root"),    // "/Users/leviackerman/Codes/checkin-v3/storage/app/public/medias"
                //     config("filesystems.disks.public.url"),     // "http://localhost:8000/storage/medias"
            }
        }

        return back()->withSuccess('Nạp file thành công');

        // return back()->withErrors('Không thể xử lý file');
    }
}
