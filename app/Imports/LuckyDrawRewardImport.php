<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use App\Models\LuckyDrawReward;

class LuckyDrawRewardImport implements ToModel, WithChunkReading, WithStartRow, WithHeadingRow
{
    use RemembersRowNumber;
    private $rows = 0;
    private $modelLuckDraw;

    public function __construct($modelLuckDraw)
    {
        $this->modelLuckDraw = $modelLuckDraw;
    }

    public function model(array $row)
    {
        ++$this->rows;

        $modelLuckyDrawReward = null;
        $code = $row['code'] ? trim($row['code']) : null;
        $name = $row['name'] ? trim($row['name']) : null;

        if (empty($code) || empty($name)) {
            return null;
        }

        $value = $row['value'] ? trim($row['value']) : null;
        $imgLink = $row['img_link'] ? trim($row['img_link']) : null;
        $order = $row['order'] ?? 1;
        $orderName = $row['order_name'] ?? "UNKNOWN";
        $probability = $row['probability'] ?? null;
        $time = $row['time'] ?? 0;

        $modelLuckyDrawReward = LuckyDrawReward::where([
            // 'lucky_draw_id'     => $this->modelLuckDraw->id,
            'code'              => $code
        ])->first();

        $attributes = [
            'lucky_draw_id' => $this->modelLuckDraw->id,
            'code'          => $code,
            'name'          => $name,
            'value'         => $value,
            'img_link'      => $imgLink,
            'order'         => $order,
            'order_name'    => $orderName,
            'probability'   => $probability,
            'time'          => $time,
            'status'        => LuckyDrawReward::STATUS_ACTIVE,
        ];

        if (!empty($modelLuckyDrawReward)) {
            if ($modelLuckyDrawReward->lucky_draw_id != $this->modelLuckDraw->id) {
                $attributes['code'] = "{$code}_NEW_".date('Ymd_His');
                $modelLuckyDrawReward = null;
            } else {
                $modelLuckyDrawReward->update($attributes);
                return;
            }
        }

        return new LuckyDrawReward($attributes);
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function startRow(): int
    {
        return 2;
    }
}
