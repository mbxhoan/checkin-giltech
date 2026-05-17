<?php

namespace App\Services\Videc;

use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TicketCrudService
{
    public function listForEvent(Event $event): Collection
    {
        $eventCode = $this->resolveEventCode($event);

        return Ticket::query()
            ->where('event_code', $eventCode)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function upsert(Event $event, array $input, ?Ticket $ticket = null): Ticket
    {
        $eventCode = $this->resolveEventCode($event, $input['event_code'] ?? null);
        $metadata = $this->buildMetadata($input, (array) ($ticket?->metadata ?? []));
        $datesValid = $this->buildDatesValid($input, (array) ($ticket?->dates_valid ?? []));

        return DB::transaction(function () use ($eventCode, $input, $ticket, $metadata, $datesValid) {
            $payload = [
                'event_code' => $eventCode,
                'code' => trim((string) $input['code']),
                'name' => trim((string) $input['name']),
                'type' => $this->normalizeNullableString($input['type'] ?? null),
                'price' => $this->normalizePrice($input['price'] ?? 0),
                'sort_order' => (int) ($input['sort_order'] ?? 0),
                'dates_string' => $this->normalizeNullableString($input['dates_string'] ?? null),
                'dates_valid' => $datesValid,
                'metadata' => $metadata,
            ];

            if ($ticket) {
                if ($ticket->event_code !== $eventCode) {
                    throw ValidationException::withMessages([
                        'ticket' => 'Ticket does not belong to this event',
                    ]);
                }

                $ticket->fill($payload)->save();
                return $ticket->refresh();
            }

            return Ticket::query()->create($payload);
        });
    }

    public function delete(Event $event, Ticket $ticket): void
    {
        if ($ticket->event_code !== $this->resolveEventCode($event)) {
            throw ValidationException::withMessages([
                'ticket' => 'Ticket does not belong to this event',
            ]);
        }

        $ticket->delete();
    }

    public function formValues(?Ticket $ticket = null): array
    {
        $metadata = (array) ($ticket?->metadata ?? []);
        $group = (array) data_get($metadata, 'group', []);
        $display = (array) data_get($metadata, 'display', []);
        $rules = (array) data_get($metadata, 'rules', []);

        return [
            'code' => $ticket?->code,
            'name' => $ticket?->name,
            'type' => $ticket?->type,
            'sort_order' => $ticket?->sort_order,
            'price' => $ticket ? (string) $ticket->price : null,
            'dates_string' => $ticket?->dates_string,
            'dates_valid_from' => data_get($ticket?->dates_valid, 'starts_at'),
            'dates_valid_to' => data_get($ticket?->dates_valid, 'ends_at'),
            'group_code' => data_get($group, 'code'),
            'group_label_vi' => data_get($group, 'label_vi'),
            'group_label_en' => data_get($group, 'label_en'),
            'group_allow_none' => (bool) data_get($group, 'allow_none', true),
            'group_none_label_vi' => data_get($group, 'none_label_vi'),
            'group_none_label_en' => data_get($group, 'none_label_en'),
            'display_name_vi' => data_get($display, 'name_vi'),
            'display_name_en' => data_get($display, 'name_en'),
            'display_price_usd' => data_get($display, 'price_usd'),
            'max_quantity' => data_get($rules, 'max_quantity', 1),
            'metadata_json' => $ticket
                ? json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                : null,
        ];
    }

    public function nextSortOrder(Event $event): int
    {
        $eventCode = $this->resolveEventCode($event);
        $maxSortOrder = Ticket::query()
            ->where('event_code', $eventCode)
            ->max('sort_order');

        return $maxSortOrder ? ((int) $maxSortOrder + 10) : 10;
    }

    private function resolveEventCode(Event $event, ?string $eventCode = null): string
    {
        $eventCode = trim((string) ($eventCode ?? ''));

        if ($eventCode !== '') {
            return $eventCode;
        }

        $eventCode = trim((string) ($event->code ?? ''));

        if ($eventCode !== '') {
            return $eventCode;
        }

        $resolvedEventCode = Event::query()
            ->whereKey($event->getKey())
            ->value('code');

        if (is_string($resolvedEventCode) && trim($resolvedEventCode) !== '') {
            return trim($resolvedEventCode);
        }

        if (str_contains(strtolower((string) $event->name), 'videc')) {
            return 'videc-2026';
        }

        throw ValidationException::withMessages([
            'event' => 'Không xác định được mã sự kiện',
        ]);
    }

    private function buildMetadata(array $input, array $existingMetadata): array
    {
        $metadata = $this->decodeJson($input['metadata_json'] ?? null) ?? $existingMetadata;

        $group = (array) data_get($metadata, 'group', []);
        $group = $this->setStringField($group, 'code', $input, 'group_code');
        $group = $this->setStringField($group, 'label_vi', $input, 'group_label_vi');
        $group = $this->setStringField($group, 'label_en', $input, 'group_label_en');
        if (array_key_exists('group_allow_none', $input)) {
            $group['allow_none'] = filter_var($input['group_allow_none'], FILTER_VALIDATE_BOOLEAN);
        }
        $group = $this->setStringField($group, 'none_label_vi', $input, 'group_none_label_vi');
        $group = $this->setStringField($group, 'none_label_en', $input, 'group_none_label_en');

        $display = (array) data_get($metadata, 'display', []);
        $display = $this->setStringField($display, 'name_vi', $input, 'display_name_vi');
        $display = $this->setStringField($display, 'name_en', $input, 'display_name_en');
        if (array_key_exists('display_price_usd', $input)) {
            $display['price_usd'] = $this->normalizeNullableNumber($input['display_price_usd']);
        }

        $rules = (array) data_get($metadata, 'rules', []);
        if (array_key_exists('max_quantity', $input)) {
            $maxQuantity = $this->normalizeNullableInteger($input['max_quantity']);
            if ($maxQuantity !== null) {
                $rules['max_quantity'] = $maxQuantity;
            } else {
                unset($rules['max_quantity']);
            }
        }

        if ($group) {
            $metadata['group'] = $group;
        } else {
            unset($metadata['group']);
        }

        if ($display) {
            $metadata['display'] = $display;
        } else {
            unset($metadata['display']);
        }

        if ($rules) {
            $metadata['rules'] = $rules;
        } else {
            unset($metadata['rules']);
        }

        return $metadata;
    }

    private function buildDatesValid(array $input, array $existingDatesValid): ?array
    {
        $startsAt = array_key_exists('dates_valid_from', $input)
            ? $this->normalizeNullableString($input['dates_valid_from'])
            : data_get($existingDatesValid, 'starts_at');

        $endsAt = array_key_exists('dates_valid_to', $input)
            ? $this->normalizeNullableString($input['dates_valid_to'])
            : data_get($existingDatesValid, 'ends_at');

        if (!$startsAt && !$endsAt) {
            return null;
        }

        return [
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ];
    }

    private function setStringField(array $target, string $key, array $input, string $inputKey): array
    {
        if (array_key_exists($inputKey, $input)) {
            $target[$key] = $this->normalizeNullableString($input[$inputKey]);
        }

        return $target;
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        $value = is_string($value) ? trim($value) : $value;

        if ($value === '' || $value === null) {
            return null;
        }

        return (string) $value;
    }

    private function normalizePrice(mixed $value): string
    {
        $numeric = $this->normalizeNullableNumber($value);

        return $numeric === null
            ? '0'
            : (string) (int) round($numeric);
    }

    private function normalizeNullableNumber(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }

    private function normalizeNullableInteger(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
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
