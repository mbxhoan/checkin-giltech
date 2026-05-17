<?php

namespace App\Imports;

use App\Models\Client;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use App\Services\Admin\ClientService;
use App\Services\Api\CheckinService;
use App\Services\Api\CountryService;
use Maatwebsite\Excel\Validators\Failure;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class _ClientImport implements ToModel, WithChunkReading, WithStartRow, WithHeadingRow, SkipsOnFailure, WithValidation
{
    use RemembersRowNumber;
    private $rows = 0;
    private $modelEvent = null;
    private $clientService;
    private $checkinService;
    private $countryService;
    private $errorLog;

    public function __construct($modelEvent)
    {
        $this->modelEvent = $modelEvent;
        $this->clientService = new ClientService;
        $this->checkinService = new CheckinService;
        $this->countryService = new CountryService;
    }

    public function model(array $row)
    {
        ++$this->rows;

        $cFields = [];
        $customFields = [];

        if (!empty($this->modelEvent)) {
            if ($cFields = $this->modelEvent->getCustomFieldTemplate()) {
                if (count($cFields)) {
                    foreach ($cFields as $field) {
                        $fieldValue = isset($row[strtolower($field['fieldName'])]) ? $row[strtolower($field['fieldName'])] : '';

                        if ($field['fieldType'] == config('event.type.multichoice')) {
                            $fieldValue = explode(',', $fieldValue);
                            $customFields[$field['fieldName']] = $fieldValue;
                        } else {
                            $customFields[$field['fieldName']] = $fieldValue;
                        }
                    }
                }
            }

            $modelClient = [];
            $qrcode = null;
            $name = trim($row['name']);
            $phone = !empty($row['phone']) ? trim($row['phone']) : null;
            $email = !empty($row['email']) ? trim($row['email']) : null;
            $status = !empty($row['status']) && array_key_exists(trim($row['status']), Client::STATUES) ? trim($row['status']) : Client::STATUS_ACTIVE;

            if (!isset($row['qr'])) {
                $qrcode = $this->modelEvent->generateQrcodeOnSetting($this->modelEvent->code, $phone, $email, $name, $customFields);
            } else {
                $qrcode = trim($row['qr']);
            }

            if ($this->modelEvent->code == "VietjetConference2025") {
                for ($i = 1; $i <= 12; $i++) {
                    $index = str_pad($i, 2, '0', STR_PAD_LEFT);
                    $kpi2024[] = $row["kpi2024{$index}"] ?? null;
                }

                if (count($kpi2024)) {
                    $customFields["kpi2024"] = $kpi2024;
                    $customFields["kpi2024total"] = $row["kpi2024total"] ?? null;
                }
            }

            $modelCountry = $this->getCountry($row['country_code'] ?? null, $row['country_name'] ?? null);
            // $modelClient = $this->clientService->repo->getClientByQrCode($qrcode);
            $modelClient = Client::where('qrcode', $qrcode)->first();

            $attributes = [
                'country_id'            => $modelCountry ? $modelCountry->id : null,
                'event_id'              => $this->modelEvent->id,
                'event_code'            => $this->modelEvent->code,
                'qrcode'                => $qrcode,
                'name'                  => $name,
                'email'                 => $email,
                'phone'                 => $phone,
                'type'                  => !empty($row['type']) ? $row['type'] : (!empty($modelClient) ? $modelClient->type : "NONE"),
                'register_source'       => Client::REGISTER_IMPORT,
                'custom_fields'         => $customFields,
                // 'status'                => !empty($modelClient) ? $modelClient->status : $status,
                'status'                => $status,
            ];

            /* CHECKIN */
            if (isset($row['checkin'])) {
                $checkin = $row['checkin'];
                $scanTime = isset($row['scan_time']) ? Carbon::parse($row['scan_time']) : null;

                if ((int)$checkin) {
                    $this->checkinService->check($this->modelEvent->code, $qrcode, $scanTime);
                }
            }

            if (!empty($modelClient)) {
                if ($modelClient->event_code == $this->modelEvent->code) {
                    if (isset($modelClient->custom_fields['lang'])) {
                        $attributes['custom_fields']['lang'] = $modelClient->custom_fields['lang'];
                    }

                    $attributes['register_source'] = $modelClient->register_source;
                    $modelClient->update($attributes);
                } else {
                    $this->errorLog[$this->rowNumber][] = "Mã QR {$qrcode} đã tồn tại";
                }

                return;
            }

            return new Client($attributes);
        }
    }

    private function getCountry($countryCode = null, $countryName = null)
    {
        if (!$countryCode || !$countryName) return null;
        return $this->countryService->repo->getItemByCodeAndName($countryCode, $countryName);
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function startRow(): int
    {
        return 2;
    }

    public function rules(): array
    {
        return [
            '*.email' => [
                'nullable',
                'string',
                // 'email'
            ],
            // '*.phone' => [
            //     'nullable',
            //     'string',
            //     'regex:/^(\+?[0-9]{1,3})?([ .-]?[0-9]{1,4}){2,4}$/',
            // ],
            'name' => [
                'required',
                'string',
                'max:255'
            ],
            'qr' => [
                'nullable',
                // 'string',
                'max:200'
            ]
        ];
    }

    public function customValidationMessages()
    {
        return [
            'email.email'   => 'Email chưa đúng định dạng',
            'phone.regex'   => 'Số điện thoại chưa đúng định dạng',
            'phone.string'  => 'Số điện thoại chưa đúng định dạng',
            'name.required' => 'Tên không được để trống',
            'name.string'   => 'Tên chưa đúng định dạng',
            'name.max'      => 'Tên đã vượt quá giới hạn ký tự',
            // 'qr.string'     => 'Mã QR chưa đúng định dạng',
            'qr.max'        => 'Mã QR vượt quá số lượng',
        ];
    }

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            foreach ($failure->errors() as $error) {
                $this->errorLog[$failure->row()][] = $error;
            }
        }
    }

    public function onError(\Throwable $e)
    {
        Log::info("*** CLIENT IMPORT ERROR {$this->modelEvent->code} ***");
        Log::alert($e);
    }

    public function getErrors(): array
    {
        return $this->errorLog ?? [];
    }
}
