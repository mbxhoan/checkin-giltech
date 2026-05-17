<?php
namespace App\Services\Admin;

use App\Models\Media;
use App\Models\MediaLibrary;
use App\Services\BaseService;
use Illuminate\Support\Facades\File;

class MediaLibraryService extends BaseService
{
    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->model = resolve(Media::class);
    }

    public function store()
    {
        try {
            $image = $this->attributes['image'];
            $name = $image->getClientOriginalName();

            if (isset($this->attributes['name'])) {
                $name = $this->attributes['name'];
            }

            $media = MediaLibrary::first()
                ->addMedia($image)
                ->usingName($name)
                ->toMediaCollection();

            return [
                'media' => $media,
                'msg'   => null,
            ];
        } catch (\Throwable $th) {
            $msgs = "Lỗi";

            if (auth()->user()->isSysAdmin()) {
                $msgs = $th->getMessage();
            }

            return [
                'media' => null,
                'msg'   => $msgs,
            ];
        }
    }

    public function deleteMedia(int $id)
    {
        try {
            $rootPath = config('filesystems.disks.public.root');
            $medium = $this->findById($id);
            $filePath = "{$rootPath}/{$medium->id}/{$medium->file_name}";
            File::delete($filePath);
            $medium->delete();

            return [
                'success'   => true,
                'msg'       => null,
            ];
        } catch (\Throwable $th) {
            $msgs = "Lỗi";

            if (auth()->user()->isSysAdmin()) {
                $msgs = $th->getMessage();
            }

            return [
                'success'   => false,
                'msg'       => $msgs,
            ];
        }
    }
}
