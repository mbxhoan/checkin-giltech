<table id="email-history-table" class="table">
    <caption>Tổng hợp</caption>
    <thead>
        <tr>
            <th scope="col">ID</th>
            <th scope="col" class="col-2">Message ID</th>
            <th scope="col" class="col-2">Email</th>
            <th scope="col" class="col-2">Họ tên</th>
            <th scope="col">Thời gian xử lý</th>
            <th scope="col">Log</th>
            <th scope="col">Trạng thái</th>
            <th scope="col"></th>
        </tr>
    </thead>
    <tbody>
        @foreach ($emails as $email)
            @php
                $webhookPostmarks = $email->webhookPostmarks ?? collect();
            @endphp
            <tr id="{{ $email['id'] }}"
                class="text-sm table-light"
                style="{{ $webhookPostmarks->count() ? 'cursor: pointer;' : '' }}"
                data-email-history-row="true"
                data-bs-toggle="collapse"
                aria-expanded="false"
                data-bs-target="#collapseWebhook{{ $email['message_id'] }}">
                <th scope="row" class="text-xs" style="vertical-align: middle;">
                    {{ $email['id'] }}
                </th>
                <td class="text-xs" style="vertical-align: middle;">
                    {{ $email['message_id'] }}
                </td>
                <td>
                    {{ $email['to_email'] }}
                </td>
                <td>
                    {{ $email['to_name'] }}
                    {{-- <a href="#" onclick="return false" data-bs-toggle="modal" data-bs-target="#{{ $email['id'] }}Modal">
                        <x-icon name="circle-info" />
                    </a> --}}
                    {{-- @include('admin.emails._modal-info', [
                        'modalId' => "{$email['id']}Modal",
                        'data' => $email['param'],
                    ]) --}}
                </td>
                <td id="email-sent_at-{{ $email['id'] }}">
                    {{ $email['sent_at'] ? humanize_date($email['sent_at'], 'H:i:s d-m-Y') : null }}
                </td>
                <td>
                    @if ($email['error_log'])
                        <a href="#" onclick="return false" class="btn btn-xs btn-danger" data-bs-toggle="modal" data-bs-target="#error_log-{{ $email['id'] }}Modal">
                            <x-icon name="circle-info" />
                            LỖI
                        </a>
                        @include('admin.emails._modal-info', [
                            'modalId'   => "error_log-{$email['id']}Modal",
                            'data'      => $email['error_log'],
                        ])
                    @endif
                </td>
                <td id="email-status-{{ $email['id'] }}">
                    <span class="btn btn-xs btn-outline-secondary">
                        @switch($email['status'])
                            @case('SENT')
                                ĐÃ GỬI
                                @break
                            @case('WAITING')
                                ĐANG GỬI
                                @break
                            @case('CLOSED')
                                ĐÃ GỬI
                                @break
                            @default
                                {{ $email['status'] }}
                        @endswitch
                    </span>
                </td>
                <td class="text-end" style="vertical-align: middle;">
                    @if ($webhookPostmarks->count())
                        <button
                            type="button"
                            class="btn btn-link p-0 text-secondary email-history-toggle"
                            data-bs-toggle="collapse"
                            data-bs-target="#collapseWebhook{{ $email['message_id'] }}"
                            aria-expanded="false"
                            aria-controls="collapseWebhook{{ $email['message_id'] }}"
                            title="Xem chi tiết webhook"
                        >
                            <x-icon name="chevron-down" />
                        </button>
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
            </tr>
            @if ($webhookPostmarks->count())
                @include('admin.reports.emails._webhook-postmark', [
                    'colspan'   => 8,
                    'email'     => $email,
                    'webhookPostmarks' => $webhookPostmarks,
                ])
            @endif
        @endforeach
    </tbody>
</table>
