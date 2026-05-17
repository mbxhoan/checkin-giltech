<?php

namespace App\Services\Videc;

use App\Models\Event;
use App\Models\PromoCode;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PromoCodeCrudService
{
    public function listForEvent(Event $event): Collection
    {
        return PromoCode::query()
            ->where('event_id', $event->getKey())
            ->orderBy('id')
            ->get();
    }

    public function upsert(Event $event, array $input, ?PromoCode $promoCode = null): PromoCode
    {
        return DB::transaction(function () use ($event, $input, $promoCode) {
            $payload = [
                'event_id' => $event->getKey(),
                'code' => trim((string) $input['code']),
                'discount_type' => 'percentage',
                'discount_value' => $this->normalizeDecimal($input['discount_value'] ?? 0),
                'max_discount_amount' => $this->normalizeNullableDecimal($input['max_discount_amount'] ?? null),
                'min_order_amount' => $this->normalizeNullableDecimal($input['min_order_amount'] ?? null),
                'usage_limit' => $this->normalizeNullableInteger($input['usage_limit'] ?? null),
                'starts_at' => $this->normalizeNullableDateTime($input['starts_at'] ?? null),
                'ends_at' => $this->normalizeNullableDateTime($input['ends_at'] ?? null),
                'status' => $input['status'] ?? 'ACTIVE',
                'metadata' => $this->decodeJson($input['metadata_json'] ?? null) ?? [],
            ];

            if ($promoCode) {
                if ((int) $promoCode->event_id !== (int) $event->getKey()) {
                    throw ValidationException::withMessages([
                        'promo_code' => 'Promo code does not belong to this event',
                    ]);
                }

                $promoCode->fill($payload)->save();

                return $promoCode->refresh();
            }

            return PromoCode::query()->create($payload);
        });
    }

    public function delete(Event $event, PromoCode $promoCode): void
    {
        if ((int) $promoCode->event_id !== (int) $event->getKey()) {
            throw ValidationException::withMessages([
                'promo_code' => 'Promo code does not belong to this event',
            ]);
        }

        $promoCode->delete();
    }

    public function formValues(?PromoCode $promoCode = null): array
    {
        return [
            'event_id' => $promoCode?->event_id,
            'code' => $promoCode?->code,
            'discount_type' => $promoCode?->discount_type ?? 'percentage',
            'discount_value' => $promoCode ? (string) $promoCode->discount_value : null,
            'max_discount_amount' => $promoCode ? (string) $promoCode->max_discount_amount : null,
            'min_order_amount' => $promoCode ? (string) $promoCode->min_order_amount : null,
            'usage_limit' => $promoCode?->usage_limit,
            'starts_at' => $promoCode?->starts_at?->format('Y-m-d\TH:i'),
            'ends_at' => $promoCode?->ends_at?->format('Y-m-d\TH:i'),
            'status' => $promoCode?->status ?? 'ACTIVE',
            'metadata_json' => $promoCode
                ? json_encode((array) ($promoCode->metadata ?? []), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                : null,
        ];
    }

    private function normalizeDecimal(mixed $value): string
    {
        return (string) ((float) ($value === null || $value === '' ? 0 : $value));
    }

    private function normalizeNullableDecimal(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (string) ((float) $value);
    }

    private function normalizeNullableInteger(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private function normalizeNullableDateTime(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Carbon::parse((string) $value)->format('Y-m-d H:i:s');
    }

    private function decodeJson(mixed $value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode((string) $value, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            throw ValidationException::withMessages([
                'metadata_json' => 'JSON không hợp lệ',
            ]);
        }

        return $decoded;
    }
}
