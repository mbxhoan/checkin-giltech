<?php
namespace App\Services;

use App\Traits\RedisCacher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;
use ZipArchive;

abstract class BaseService
{
    public $attributes;
    public $httpClient;
    protected Model $model;

    use RedisCacher;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function setAttributes($attributes)
    {
        return $this->attributes = $attributes;
    }

    public function init()
    {
        $model = $this->model->getModel();
        return new $model;
    }

    public function getQuery()
    {
        return $this->model->newQuery();
    }

    /**
     * Get all records.
     */
    public function getAll(int $paginate = 0): Collection
    {
        $query = $this->model->latest(); // Order by latest
        return $paginate > 0 ? $query->paginate($paginate) : $query->get();
    }

    /**
     * Get list of records fit the array of attributes.
     */
    public function getQueryByAttributes(
        array $attributes = [],
        array $excludeAttributes = [],
        array $likes = [],
        array $orderBy = [],
        bool $includeDeleted = false,
        int $limit = 0,
    ): Builder
    {
        $table = $this->model->getTable();
        $query = $this->model->newQuery();

        if (count($attributes)) {
            foreach ($attributes as $attrCol => $attrValue) {
                if (Schema::hasColumn($table, $attrCol)) {
                    if (is_array($attrValue)) {
                       $query->whereIn($attrCol, $attrValue);
                    } else {
                       $query->where($attrCol, $attrValue);
                    }
                }
            }
        }

        if (count($excludeAttributes)) {
            foreach ($excludeAttributes as $attrCol => $attrValue) {
                if (Schema::hasColumn($table, $attrCol)) {
                    if (is_null($attrValue)) {
                        $query->whereNotNull($attrCol);
                    } elseif (is_array($attrValue)) {
                        $query->whereNotIn($attrCol, $attrValue);
                    } else {
                        $query->where($attrCol, '!=', $attrValue);
                    }
                }
            }
        }

        if (count($likes)) {
            foreach ($likes as $attrCol => $attrValue) {
                if (Schema::hasColumn($table, $attrCol)) {
                    if (is_string($attrValue)) {
                        $query->where($attrCol, 'like', "%{$attrValue}%");
                    }
                }
            }
        }

        if (count($orderBy)) {
            foreach ($orderBy as $orderCol => $order) {
                $query->orderBy($orderCol, $order);
            }
        } else {
            $query->orderBy('updated_at', 'DESC');
        }

        if (Schema::hasColumn($table, 'status')) {
            if (!$includeDeleted) {
                $query->where('status', '!=', "DELETED");
            }
        }

        if ((int)$limit) {
            $query = $query->limit($limit);
        }

        return $query;
    }

    public function getListByAttributes(
        array $attributes = [],
        array $excludeAttributes = [],
        array $likes = [],
        int $paginate = 0,
        array $orderBy = [],
        bool $includeDeleted = false,
        int $limit = 0,
    )
    {
        $query = $this->getQueryByAttributes($attributes, $excludeAttributes, $likes, $orderBy, $includeDeleted, $limit);
        return $paginate > 0 ? $query->paginate($paginate) : ($query->get() ?? collect());
    }

    /* public function getList($status = null): Collection
    {
        $table = $this->model->getTable();

        if ($status) {
            if (Schema::hasColumn($table, 'status')) {
                if (is_array($status)) {
                    return $this->model->whereIn('status', $status)->get();
                } else {
                    return $this->model->where('status', $status)->get();
                }
            }
        }

        return $this->model->all();
    } */

    /**
     * Find a record by ID.
     */
    public function findById(int $id): ?Model
    {
        return $this->model->find($id);
    }

    /**
     * Find a record by array of attributes.
     */
    public function findByAttributes(
        array $attributes = [],
        array $excludeAttributes = [],
        array $likes = [],
        array $orderBy = []
    ): ?Model
    {
        $table = $this->model->getTable();
        $query = $this->model->newQuery(); // Use newQuery() to avoid modifying the model instance.

        if (!empty($attributes)) {
            foreach ($attributes as $attrName => $attrValue) {
                if (Schema::hasColumn($table, $attrName)) { // Ensure column exists before applying condition
                    if (is_array($attrValue)) {
                        $query->whereIn($attrName, $attrValue);
                    } else {
                        $query->where($attrName, $attrValue);
                    }
                }
            }
        }

        if (!empty($excludeAttributes)) {
            foreach ($excludeAttributes as $attrName => $attrValue) {
                if (Schema::hasColumn($table, $attrName)) { // Ensure column exists before applying condition
                    if (is_null($attrValue)) {
                        $query->whereNotNull($attrName);
                    } elseif (is_array($attrValue)) {
                        $query->whereNotIn($attrName, $attrValue);
                    } else {
                        $query->where($attrName, '!=', $attrValue);
                    }
                }
            }
        }

        if (count($likes)) {
            foreach ($likes as $attrCol => $attrValue) {
                if (Schema::hasColumn($table, $attrCol)) { // Ensure column exists before applying condition
                    if (is_string($attrValue)) {
                        $query->where($attrCol, 'like', "%{$attrValue}%");

                    }
                }
            }
        }

        if (count($orderBy)) {
            foreach ($orderBy as $orderCol => $order) {
                $query->orderBy($orderCol, $order);
            }
        } else {
            $query->orderBy('updated_at', 'DESC');
        }

        if (Schema::hasColumn($table, 'status')) {
            $query->where('status', '!=', "DELETED");
        }

        return $query->first(); // Return first matching record or null
    }

    /**
     * Find a record by column name if the column exists.
     */
    /* public function findByColumnName(string $colName, string $value): ?Model
    {
        $table = $this->model->getTable();

        if (Schema::hasColumn($table, $colName)) {
            return $this->model->where($colName, $value)->first();
        }

        throw new \Exception("The {$colName} column does not exist in the ".get_class($this->model)." model.");

        // if (!in_array('code', $this->model->getFillable())) {
        //     throw new \Exception("The 'code' column does not exist in the " . get_class($this->model) . " model.");
        // }
    } */

    /**
     * Create a new record.
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * Update a record by ID.
     */
    public function update(int $id, array $data): bool
    {
        $record = $this->findById($id);
        return $record ? $record->update($data) : false;
    }

    /**
     * Delete a record by ID.
     */
    public function delete(int $id): bool
    {
        $record = $this->findById($id);
        return $record ? $record->delete() : false;
    }

    public function getFillable()
    {
        return $this->model->getFillable();
    }

     /**
     * Create or Update object
     */
    public function store()
    {
        $id = $this->attributes['id'] ? (int)($this->attributes['id']) : null;

        if ($id) {
            $model = $this->findById($id);

            if ($model) {
                return $model->update($this->attributes);
            }
        } else {
            return $this->create($this->attributes);
        }

        return null;
    }

    public function getStoreAsAttribute($attrForms, $attrMores = [])
    {
        $attrPermits = $this->getFillable();
        $attributes = [];

        /* Filter field is saved */
        foreach ($attrPermits as $val) {
            if (isset($attrForms[$val])) {
                $attributes[$val] = $attrForms[$val];
            }
        }

        if (count($attrMores)) {
            $attributes = array_merge($attributes, $attrMores);
        }

        return $attributes;
    }

    public function storeAs($attrForms, $attrMores = [])
    {
        $this->attributes = $this->getStoreAsAttribute($attrForms, $attrMores);

        if ($model = $this->store()) {
            return $model;
        }

        return null;
    }

    public function exportCmd($exportCommand, $agruments)
    {
        ob_start();
        Artisan::call($exportCommand, $agruments);
        $checkCmd = (int)ob_get_contents();
        ob_end_clean();
        return $checkCmd;
    }

    public function deleteOnStatus($id)
    {
        $this->attributes = [
            'status' => "DELETED"
        ];

        if ($this->update($id, $this->attributes)) {
            return true;
        }

        throw new \Exception("Error trying deleting on status #{$id} in the ".get_class($this->model)." model.");
        return false;
    }

    public function deleteList($ids, $deleteOnStatus = true)
    {
        if (count($ids)) {
            foreach ($ids as $id) {
                if ($deleteOnStatus) {
                    if (!$this->deleteOnStatus((int)$id)) {
                        return false;
                    }
                } else {
                    $this->delete($id);
                }
            }

            return true;
        }
    }

    public function checkFileExistInStorage($filePath, $withPublic = true)
    {
        $fullPath = $filePath;

        if ($withPublic) {
            $fullPath = "public/{$filePath}";
        }

        if (!Storage::exists($fullPath)) {
            return false;
        }

        return true;
    }

    public function cloneModel($oldModel, $customizeAttributes = [])
    {
        $newModel = $oldModel->replicate();

        if (count($customizeAttributes)) {
            foreach ($customizeAttributes as $key => $value) {
                $newModel->$key = $value;
            }
        }

        $newModel->save();
        return $newModel;
    }

    public function zipFolder($zipFileName, $folderPath)
    {
        $zip = new ZipArchive();

        if ($zip->open(public_path($zipFileName), ZipArchive::CREATE) !== true) {
            return false;
        }

        try {
            if (!is_dir($folderPath)) {
                return false;
            }

            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($folderPath));
            foreach ($files as $name => $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($folderPath) + 1);
                    $zip->addFile($filePath, $relativePath);
                }
            }
        } catch (Exception $e) {
            $msgError = "Lỗi nén file zip: {$zipFileName}";

            if (auth()->user()->isSysAdmin()) {
                $msgError = $e->getMessage();
            }

            return response()->json(['message' => $msgError], 500);
        }

        $zip->close();
        return true;
    }

    public function removeEmptyElementInArray($array)
    {
        $filteredArray = array_filter($array, function ($value) {
            return $value !== "" && $value !== null;
        });

        return $filteredArray;
    }

    public function deleteAssets($folderPath, $file = null)
    {
        try {
            if (Storage::exists($folderPath)) {
                if ($file) {
                    if (Storage::exists("{$folderPath}/{$file}")) {
                        Storage::delete("{$folderPath}/{$file}");
                        return true;
                    }

                    return false;
                } else {
                    // Notice: "public/{$folderPath}"
                    Storage::deleteDirectory($folderPath);
                }

                return true;
            }
        } catch (\Exception $e) {
            Log::info("Error occured while delete assets");
            Log::alert($e);
        }

        return false;
    }

    public function resetFieldToNull($model, $fieldName)
    {
        try {
            $model->update([
                $fieldName => null
            ]);

            return true;
        } catch (\Exception $e) {
            Log::info("Error occured while reset field {$fieldName} on table {$model->getTable()}");
            Log::alert($e);
        }

        return false;
    }

    public function getConfigEventSettings()
    {
        return config("event-settings");
    }

    public function getListDistinctField(array $attributes = [], $field = 'type')
    {
        $query = $this->model->newQuery();
        $query->distinct()->where($attributes);
        return $query->get([$field]);
    }

    protected function convertToJsonFile(array $data = [], string $folderName = "backup", ?string $filename = null)
    {
        $date = now()->format('Ymd_His');
        $filename = $filename ?? "backup_{$date}.json";
        $path = "{$folderName}/{$filename}";
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        Storage::put($path, $json);
        return $path;
    }
}
