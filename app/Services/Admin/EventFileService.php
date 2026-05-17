<?php
namespace App\Services\Admin;

use App\Models\EventFile;
use App\Services\BaseService;

class EventFileService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(EventFile::class);
    }

    public function event()
    {
        return app(EventService::class);
    }

    public function mediaLibraryService()
    {
        return new MediaLibraryService($this->attributes);
    }
}
