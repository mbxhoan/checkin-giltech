<?php
namespace App\Services\Admin;

use App\Models\ImpexpFile;
use App\Services\BaseService;

class ImpexpFileService extends BaseService
{
    public $fileName;
    public $dataFile;
    public $table;
    public $folderName = 'impexp';

    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->model = resolve(ImpexpFile::class);
    }

    public function event()
    {
        return app(EventService::class);
    }

    public function company()
    {
        return app(CompanyService::class);
    }

    public function uploadFile()
    {
        if (!empty($this->dataFile)) {
            $filePath = $this->saveFile();

            $this->attributes = [
                'event_id'      => $this->attributes['event_id'] ?? null,
                'name'          => $this->fileName,
                'table'         => $this->table,
                'file_path'     => $filePath,
                'type'          => $this->attributes['type'] ?? ImpExpFile::TYPE_IMPORT,
                'status'        => ImpExpFile::STATUS_NEW,
            ];

            if ($model = $this->create($this->attributes)) {
                return $model;
            }
        }

        return null;
    }

    /**
     * Save file to folder and database
     *
     * @return user
     */
    public function saveFile()
    {
        /* Save folder */
        $uploadPath = $this->getUploadPath();
        $fileName = $this->getUploadFile();
        $file = $this->dataFile->storeAs($uploadPath, $fileName);
        return $file;
    }

     /**
     * Path upload
     *
     * @return string uploadPath
     */
    protected function getUploadPath()
    {
        $y = date("Y");
        $m = date("m");
        $uploadPath = "{$this->folderName}/{$y}/{$m}";
        return $uploadPath;
    }

    protected function getUploadFile()
    {
        $fileNameWithExtension = $this->dataFile->getClientOriginalName();
        $fileName = pathinfo($fileNameWithExtension, PATHINFO_FILENAME);
        $extension = $this->dataFile->getClientOriginalExtension();
        $this->fileName = $fileName.'_'.time().'.'.$extension;
        return $this->fileName;
    }
}
