@php
    $formatMoney = fn ($value) => $value !== null && $value !== '' ? number_format((float) $value, 0, ',', '.') : '-';
@endphp

<div class="table-responsive">
    <table class="table table-sm table-striped table-hover align-middle mb-0">
        <thead class="table-dark sticky-top">
            <tr>
                <th class="text-center" style="width: 55px;">#</th>
                <th style="width: 180px;">Mã</th>
                <th>Giảm giá</th>
                <th>Giới hạn</th>
                <th style="width: 180px;">Hiệu lực</th>
                <th class="text-center" style="width: 120px;">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($promoCodes as $index => $promoCode)
                @php
                    $isSelected = (int) request()->integer('promo_code') === (int) $promoCode->id;
                @endphp
                <tr @class(['table-warning' => $isSelected])>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <div class="fw-semibold">
                            <code>{{ $promoCode->code }}</code>
                        </div>
                        <div class="text-muted small">
                            ID: {{ $promoCode->id }}
                        </div>
                    </td>
                    <td>
                        <div class="fw-semibold">{{ (float) $promoCode->discount_value }}%</div>
                        <div class="text-muted small">
                            Loại: {{ $promoCode->discount_type }}
                        </div>
                        <div class="text-muted small">
                            Giảm tối đa: {{ $formatMoney($promoCode->max_discount_amount) }}
                        </div>
                    </td>
                    <td>
                        <div class="text-muted small">
                            Đơn tối thiểu: {{ $formatMoney($promoCode->min_order_amount) }}
                        </div>
                        <div class="text-muted small">
                            Lượt dùng: {{ $promoCode->usage_count }}{{ $promoCode->usage_limit ? " / {$promoCode->usage_limit}" : '' }}
                        </div>
                        <div class="text-muted small">
                            Trạng thái: {{ $promoCode->status }}
                        </div>
                    </td>
                    <td>
                        <div>{{ $promoCode->starts_at?->format('d/m/Y H:i') ?? '-' }}</div>
                        <div class="text-muted small">{{ $promoCode->ends_at?->format('d/m/Y H:i') ?? '-' }}</div>
                    </td>
                    <td class="text-center">
                        <div class="d-inline-flex gap-1">
                            <a href="{{ url("/admin/events/{$event->id}/promo-codes?promo_code={$promoCode->id}") }}" class="btn btn-xs btn-outline-primary">
                                <x-icon name="edit" />
                            </a>
                            <form action="{{ url("/admin/events/{$event->id}/promo-codes/{$promoCode->id}") }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xoá promo code này?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-xs btn-outline-danger">
                                    <x-icon name="trash" />
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        Chưa có promo code nào. Hãy tạo promo code đầu tiên ở form bên phải.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
