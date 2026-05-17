<?php

namespace Database\Seeders\Videc;

use App\Models\Company;
use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Videc2026Seeder extends Seeder
{
    public function run(): void
    {
        $company = Company::query()->updateOrCreate(
            ['code' => 'CMP-L3IP2V1P'],
            [
                'name' => 'FOREST MEDIA',
                'status' => Company::STATUS_ACTIVE,
                'is_default' => false,
            ]
        );

        DB::table('events')->updateOrInsert(
            ['id' => 106],
            [
                'company_id' => $company->id,
                'code' => 'videc-2026',
                'name' => 'VIDEC 2026',
                'status' => Event::STATUS_ACTIVE,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $event = Event::query()->findOrFail(106);

        $validFrom = now()->addDay()->toDateString();
        $validTo = now()->addDays(8)->toDateString();
        $datesString = sprintf('%s - %s', $validFrom, $validTo);

        foreach ($this->tickets($validFrom, $validTo, $datesString) as $ticket) {
            Ticket::query()->updateOrCreate(
                [
                    'event_code' => $event->code,
                    'code' => $ticket['code'],
                ],
                [
                    'sort_order' => $ticket['sort_order'],
                    'name' => $ticket['name'],
                    'type' => $ticket['type'],
                    'price' => (string) $ticket['price_vnd'],
                    'dates_string' => $ticket['dates_string'],
                    'dates_valid' => $ticket['dates_valid'],
                    'metadata' => $ticket['metadata'],
                ]
            );
        }
    }

    private function tickets(string $validFrom, string $validTo, string $datesString): array
    {
        return [
            [
                'code' => 'VC26-PC-IMP',
                'sort_order' => 10,
                'type' => 'pre_congress',
                'name' => 'Phiên Cấy ghép Implant / Session Implantology',
                'price_vnd' => 2000000,
                'dates_string' => $datesString,
                'dates_valid' => [
                    'starts_at' => $validFrom,
                    'ends_at' => $validTo,
                ],
                'metadata' => $this->metadata(
                    'pre_congress',
                    'Tiền Hội nghị',
                    'Pre-Congress Ticket',
                    'Phiên Cấy ghép Implant',
                    'Session Implantology',
                    80
                ),
            ],
            [
                'code' => 'VC26-PC-ORTH',
                'sort_order' => 20,
                'type' => 'pre_congress',
                'name' => 'Phiên Chỉnh nha / Session Orthodontics',
                'price_vnd' => 2000000,
                'dates_string' => $datesString,
                'dates_valid' => [
                    'starts_at' => $validFrom,
                    'ends_at' => $validTo,
                ],
                'metadata' => $this->metadata(
                    'pre_congress',
                    'Tiền Hội nghị',
                    'Pre-Congress Ticket',
                    'Phiên Chỉnh nha',
                    'Session Orthodontics',
                    80
                ),
            ],
            [
                'code' => 'VC26-CF-VOSA',
                'sort_order' => 30,
                'type' => 'conference',
                'name' => 'Hội viên VOSA / VOSA Member',
                'price_vnd' => 1500000,
                'dates_string' => $datesString,
                'dates_valid' => [
                    'starts_at' => $validFrom,
                    'ends_at' => $validTo,
                ],
                'metadata' => $this->metadata(
                    'conference',
                    'Hội nghị',
                    'Conference Ticket',
                    'Hội viên VOSA',
                    'VOSA Member',
                    null
                ),
            ],
            [
                'code' => 'VC26-CF-VN-DENTIST',
                'sort_order' => 40,
                'type' => 'conference',
                'name' => 'Nha sĩ Việt Nam / Vietnamese Dentist',
                'price_vnd' => 2000000,
                'dates_string' => $datesString,
                'dates_valid' => [
                    'starts_at' => $validFrom,
                    'ends_at' => $validTo,
                ],
                'metadata' => $this->metadata(
                    'conference',
                    'Hội nghị',
                    'Conference Ticket',
                    'Nha sĩ Việt Nam',
                    'Vietnamese Dentist',
                    null
                ),
            ],
            [
                'code' => 'VC26-CF-INT-DENTIST',
                'sort_order' => 50,
                'type' => 'conference',
                'name' => 'Nha sĩ quốc tế / International Dentist',
                'price_vnd' => 5300000,
                'dates_string' => $datesString,
                'dates_valid' => [
                    'starts_at' => $validFrom,
                    'ends_at' => $validTo,
                ],
                'metadata' => $this->metadata(
                    'conference',
                    'Hội nghị',
                    'Conference Ticket',
                    'Nha sĩ quốc tế',
                    'International Dentist',
                    200
                ),
            ],
            [
                'code' => 'VC26-CF-TECH',
                'sort_order' => 60,
                'type' => 'conference',
                'name' => 'Kỹ thuật viên / Trợ thủ / Sinh viên / Technician / Assistant / Student',
                'price_vnd' => 1500000,
                'dates_string' => $datesString,
                'dates_valid' => [
                    'starts_at' => $validFrom,
                    'ends_at' => $validTo,
                ],
                'metadata' => $this->metadata(
                    'conference',
                    'Hội nghị',
                    'Conference Ticket',
                    'Kỹ thuật viên / Trợ thủ / Sinh viên',
                    'Technician / Assistant / Student',
                    null
                ),
            ],
            [
                'code' => 'VC26-EXH-VISITOR',
                'sort_order' => 70,
                'type' => 'exhibition',
                'name' => 'Tham quan / Visitor',
                'price_vnd' => 300000,
                'dates_string' => $datesString,
                'dates_valid' => [
                    'starts_at' => $validFrom,
                    'ends_at' => $validTo,
                ],
                'metadata' => $this->metadata(
                    'exhibition',
                    'Triển lãm',
                    'Exhibition',
                    'Tham quan',
                    'Visitor',
                    20
                ),
            ],
        ];
    }

    private function metadata(
        string $groupCode,
        string $groupLabelVi,
        string $groupLabelEn,
        string $nameVi,
        string $nameEn,
        ?int $priceUsd
    ): array {
        return [
            'group' => [
                'code' => $groupCode,
                'label_vi' => $groupLabelVi,
                'label_en' => $groupLabelEn,
                'allow_none' => true,
                'none_label_vi' => 'Không tham gia',
                'none_label_en' => 'None',
            ],
            'display' => [
                'name_vi' => $nameVi,
                'name_en' => $nameEn,
                'price_usd' => $priceUsd,
            ],
            'rules' => [
                'max_quantity' => 1,
                'one_per_group' => true,
            ],
            'event' => [
                'code' => 'videc-2026',
                'id' => 106,
            ],
        ];
    }
}
