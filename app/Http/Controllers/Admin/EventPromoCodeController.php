<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PromoCodes\PromoCodeUpsertRequest;
use App\Models\Event;
use App\Models\PromoCode;
use App\Services\Videc\PromoCodeCrudService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventPromoCodeController extends Controller
{
    public function __construct(private readonly PromoCodeCrudService $promoCodeCrudService)
    {
    }

    public function index(string $event, Request $request): View
    {
        $event = $this->resolveEvent($event);
        $this->authorize('edit', $event);

        $promoCodes = $this->promoCodeCrudService->listForEvent($event);
        $selectedPromoCode = $this->resolveSelectedPromoCode($promoCodes, $request->integer('promo_code'));
        $formPromoCode = $selectedPromoCode ?: new PromoCode([
            'event_id' => $event->getKey(),
            'discount_type' => 'percentage',
            'status' => 'ACTIVE',
        ]);

        return view('admin.events.promo-codes.index', [
            'event' => $event,
            'promoCodes' => $promoCodes,
            'selectedPromoCode' => $selectedPromoCode,
            'formValues' => array_merge(
                $this->promoCodeCrudService->formValues($formPromoCode),
                $this->oldPromoCodeValues($formPromoCode)
            ),
        ]);
    }

    public function store(PromoCodeUpsertRequest $request, string $event): RedirectResponse
    {
        $event = $this->resolveEvent($event);
        $this->authorize('edit', $event);

        $promoCode = $this->promoCodeCrudService->upsert($event, $request->validated());

        return redirect()
            ->route('admin.events.promo-codes.index', [
                'event' => $event,
                'promo_code' => $promoCode->id,
            ])
            ->withSuccess('Tạo promo code thành công');
    }

    public function update(PromoCodeUpsertRequest $request, string $event, string $promoCode): RedirectResponse
    {
        $event = $this->resolveEvent($event);
        $promoCode = $this->resolvePromoCode($promoCode);
        $this->authorize('edit', $event);

        $promoCode = $this->promoCodeCrudService->upsert($event, $request->validated(), $promoCode);

        return redirect()
            ->route('admin.events.promo-codes.index', [
                'event' => $event,
                'promo_code' => $promoCode->id,
            ])
            ->withSuccess('Cập nhật promo code thành công');
    }

    public function destroy(string $event, string $promoCode): RedirectResponse
    {
        $event = $this->resolveEvent($event);
        $promoCode = $this->resolvePromoCode($promoCode);
        $this->authorize('edit', $event);

        $this->promoCodeCrudService->delete($event, $promoCode);

        return redirect()
            ->route('admin.events.promo-codes.index', $event)
            ->withSuccess('Xoá promo code thành công');
    }

    private function resolveSelectedPromoCode($promoCodes, ?int $promoCodeId): ?PromoCode
    {
        if (!$promoCodeId) {
            return null;
        }

        return $promoCodes->firstWhere('id', $promoCodeId);
    }

    private function oldPromoCodeValues(PromoCode $formPromoCode): array
    {
        return [
            'event_id' => old('event_id', $formPromoCode->event_id),
            'code' => old('code', $formPromoCode->code),
            'discount_type' => old('discount_type', $formPromoCode->discount_type),
            'discount_value' => old('discount_value', $formPromoCode->discount_value),
            'max_discount_amount' => old('max_discount_amount', $formPromoCode->max_discount_amount),
            'min_order_amount' => old('min_order_amount', $formPromoCode->min_order_amount),
            'usage_limit' => old('usage_limit', $formPromoCode->usage_limit),
            'starts_at' => old('starts_at', $formPromoCode->starts_at?->format('Y-m-d\TH:i')),
            'ends_at' => old('ends_at', $formPromoCode->ends_at?->format('Y-m-d\TH:i')),
            'status' => old('status', $formPromoCode->status),
            'metadata_json' => old(
                'metadata_json',
                empty($formPromoCode->id)
                    ? null
                    : json_encode((array) ($formPromoCode->metadata ?? []), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            ),
        ];
    }

    private function resolveEvent(string $event): Event
    {
        return Event::query()->findOrFail($event);
    }

    private function resolvePromoCode(string $promoCode): PromoCode
    {
        return PromoCode::query()->findOrFail($promoCode);
    }
}
