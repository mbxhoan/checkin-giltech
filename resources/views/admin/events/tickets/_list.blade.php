@php
    $formatPriceVnd = fn (?string $price) => $price !== null ? number_format((float) $price, 0, ',', '.') : '-';
    $formatPriceUsd = fn ($price) => $price !== null && $price !== '' ? 'USD ' . rtrim(rtrim(number_format((float) $price, 2, '.', ''), '0'), '.') : '-';
@endphp

<div class="table-responsive">
    <table class="table table-sm table-striped table-hover align-middle mb-0">
        <thead class="table-dark sticky-top">
            <tr class="text-sm">
                <th class="text-center" style="width: 55px;">#</th>
                <th style="width: 140px;">Mã</th>
                <th>Thông tin vé</th>
                <th>Nhóm / hiển thị</th>
                <th class="text-end" style="width: 160px;">Giá</th>
                <th style="width: 180px;">Checkin</th>
                <th class="text-center" style="width: 120px;">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($tickets as $index => $ticket)
                @php
                    $metadata = (array) ($ticket->metadata ?? []);
                    $group = (array) data_get($metadata, 'group', []);
                    $display = (array) data_get($metadata, 'display', []);
                    $isSelected = (int) request()->integer('ticket') === (int) $ticket->id;
                @endphp
                <tr @class(['table-warning' => $isSelected])>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <div class="fw-semibold">
                            <code>{{ $ticket->code }}</code>
                        </div>
                        <div class="text-muted small">
                            ID: {{ $ticket->id }}
                        </div>
                    </td>
                    <td>
                        <div class="fw-semibold">{{ $ticket->name }}</div>
                        <div class="text-muted small">
                            Loại: <span class="badge bg-secondary">{{ $ticket->type ?? '-' }}</span>
                            <span class="ms-2">STT: {{ $ticket->sort_order }}</span>
                        </div>
                        @if (!empty($display))
                            <div class="text-muted small mt-1">
                                VI: {{ data_get($display, 'name_vi', '-') }}
                                <br>
                                EN: {{ data_get($display, 'name_en', '-') }}
                            </div>
                        @endif
                    </td>
                    <td>
                        <div class="fw-semibold">
                            {{ data_get($group, 'label_vi', '-') }}
                        </div>
                        <div class="text-muted small">
                            <code>{{ data_get($group, 'code', '-') }}</code>
                            <span class="mx-1">/</span>
                            {{ data_get($group, 'label_en', '-') }}
                        </div>
                        <div class="text-muted small">
                            None: {{ data_get($group, 'none_label_vi', '-') }}
                        </div>
                    </td>
                    <td class="text-end">
                        <div class="fw-semibold">{{ $formatPriceVnd($ticket->price) }} VND</div>
                        <div class="text-muted small">{{ $formatPriceUsd(data_get($display, 'price_usd')) }}</div>
                    </td>
                    <td>
                        <div>{{ $ticket->dates_string ?? '-' }}</div>
                        <div class="text-muted small">
                            {{ data_get($ticket->dates_valid, 'starts_at', '-') }} - {{ data_get($ticket->dates_valid, 'ends_at', '-') }}
                        </div>
                    </td>
                    <td class="text-center">
                        <div class="d-inline-flex gap-1">
                            <a href="{{ url("/admin/events/{$event->id}/tickets?ticket={$ticket->id}") }}" class="btn btn-xs btn-outline-primary">
                                <x-icon name="edit" />
                            </a>
                            <form action="{{ url("/admin/events/{$event->id}/tickets/{$ticket->id}") }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xoá vé này?');">
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
                    <td colspan="7" class="text-center text-muted py-4">
                        Chưa có vé nào. Hãy tạo vé đầu tiên ở form bên phải.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
