<?php

namespace App\Imports;

use App\Http\Requests\Admin\Clients\ImportRequest;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Support\Facades\Validator;
use App\Services\Admin\ClientService;
use App\Models\CustomFieldTemplate;
use App\Models\Client;
use App\Models\Event;
use Illuminate\Support\Facades\DB;

class ClientImportv2 implements ToModel, WithHeadingRow, WithEvents, WithChunkReading, WithStartRow, SkipsOnFailure
{
    use RemembersRowNumber;
    public $validAttributes = [];
    public $processedQrcodes = [];
    public $errorLog = [];
    public $currentRow = 0;
    public $modelImp;
    protected $service;
    protected $event;
    protected $customFieldTemplates;

    public $successRow = 0;

    public function __construct(Event $event)
    {
        $this->service = app(ClientService::class);
        $this->event = $event;
    }

    public function registerEvents(): array
    {
        if (!empty($this->modelImp)) {
            return [
                BeforeImport::class => function (BeforeImport $event) {
                    $totalRows = $event->getReader()->getTotalRows();
                    $importRows = 0;

                    if (count($totalRows)) {
                        foreach ($totalRows as $totalRow) {
                            // Subtract 1 to account for the heading row
                            $importRows = (int)$totalRow - 1;

                            $this->modelImp->update([
                                'total_record_before' => $importRows,
                            ]);
                        }
                    }
                }
            ];
        }

        return [];
    }

    public function model(array $row)
    {
        ++$this->currentRow;
        $customFields = [];
        $validator = Validator::make($row, (new ImportRequest($this->event))->rules());

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->logErrors($this->currentRow, $error);
            }

            return null;
        }

        $qrcode = $row['qrcode'] ?? null;
        $name = $row['name'];

        /* kiểm tra serial đã được nhập trong phiếu này chưa */
        // ** Step 1: Check for duplicate SNs in the same file **
        if (isset($this->processedQrcodes[$qrcode])) {
            $this->logErrors($this->currentRow, "Mã qrcode bị trùng trong file");
            return null;
        }

        // ** Mark SN as processed **
        $this->processedQrcodes[$qrcode] = true;

        /* ràng buộc custom fields templates */
        $this->customFieldTemplates = $this->event->getCustomFieldTemplates();

        if (count($this->customFieldTemplates)) {
            foreach ($this->customFieldTemplates as $fieldName => $customFieldTemplate) {
                $fieldValue = isset($row[strtolower($fieldName)]) ? $row[strtolower($fieldName)] : null;

                if ($customFieldTemplate['type'] == CustomFieldTemplate::TYPE_MULTICHOICE) {
                    $fieldValues = explode(',', $fieldValue);
                    $customFields[$fieldName] = $fieldValues;
                    continue;
                }

                $customFields[$fieldName] = $fieldValue;
            }
        }

        if (!$qrcode) {
            $qrcode = $this->event->generateQrcodeOnSetting($this->event->code, $customFields['phone'] ?? null, $row['email'] ?? null, $name, $customFields);
        }

        $client = $this->service->findByAttributes([
            'event_id'  => $this->event->id,
            'qrcode'    => $qrcode,
        ]);

        $attributes = [
            'event_id'              => $this->event->id,
            'event_code'            => $this->event->code,
            'qrcode'                => $qrcode,
            'name'                  => $row['name'],
            'email'                 => $row['email'],
            'type'                  => !empty($row['type']) ? $row['type'] : (!empty($client) ? $client->type : "NONE"),
            'register_source'       => Client::REGISTER_IMPORT,
            'custom_fields'         => $customFields,
            'created_by'            => !empty(auth()->user()) ? auth()->user()->id : null,
            'updated_by'            => !empty(auth()->user()) ? auth()->user()->id : null
        ];

        if (!empty($client)) {
            if ($client->event_code == $this->event->code) {
                $attributes['id'] = $client->id;
                $attributes['register_source'] = $client->register_source;
                unset($attributes['created_by']);
            } else {
                $this->logErrors($this->currentRow, "Mã QR {$qrcode} đã tồn tại, sự kiện {$this->event->code}");
            }
        } else {
            $attributes['id'] = null;
        }

        $this->validAttributes[] = $attributes;
        return null;
    }

    public function __destruct()
    {
        if (count($this->errorLog)) {
            DB::rollBack(); // Rollback if any errors
        } else {
            Client::upsert(
                $this->validAttributes,
                ['id'],
            );

            DB::commit(); // Commit transaction
        }
    }

    public function uniqueBy()
    {
        return 'qrcode';
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function startRow(): int
    {
        return 2;
    }

    public function getRowCount(): int
    {
        return $this->currentRow;
    }

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            foreach ($failure->errors() as $error) {
                $this->logErrors($failure->row(), $error);
            }
        }
    }

    public function getErrors()
    {
        return $this->errorLog;
    }

    public function logErrors(int $row, string $error)
    {
        $this->errorLog[$row][] = $error;
    }

    public function onError(\Throwable $e)
    {
        $this->logErrors($this->currentRow, $e->getMessage());
    }
}
