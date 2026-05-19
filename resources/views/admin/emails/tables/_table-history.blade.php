<table class="table">
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
            <tr id="{{ $email->id }}"
                class="text-sm {{ $email->status == $email::STATUS_CLOSED ? 'table-secondary' : ($email->message_id ? 'table-light' : '') }}"
                data-bs-toggle="collapse" data-bs-target="#collapseWebhook{{ $email->message_id }}"
                aria-expanded="false" aria-controls="collapseWebhook{{ $email->message_id }}">
                <th scope="row" class="text-xs" style="vertical-align: middle;">
                    {{ $email->id }}
                </th>
                <td class="text-xs" style="vertical-align: middle;">
                    {{ $email->message_id }}
                </td>
                <td>
                    {{ $email->to_email }}
                </td>
                <td>
                    {{ $email->to_name }}
                    <a href="#" onclick="return false" data-bs-toggle="modal" data-bs-target="#{{ $email->id }}Modal">
                        <x-icon name="circle-info" />
                    </a>
                    @include('admin.emails._modal-info', [
                        'modalId' => "{$email->id}Modal",
                        'data' => $email->param,
                    ])
                </td>
                <td id="email-sent_at-{{ $email->id }}">
                    {{ $email->sent_at ? humanize_date($email->sent_at, 'H:i:s d-m-Y') : null }}
                </td>
                <td>
                    @if ($email->error_log)
                        <a href="#" onclick="return false" data-bs-toggle="modal" data-bs-target="#error_log-{{ $email->id }}Modal">
                            <x-icon name="circle-info" />
                        </a>
                        @include('admin.emails._modal-info', [
                            'modalId'   => "error_log-{$email->id}Modal",
                            'data'      => $email->error_log,
                        ])
                    @endif
                </td>
                <td id="email-status-{{ $email->id }}">
                    @include('admin.emails._status', [
                        'email' => $email,
                        'class' => 'btn btn-sm',
                    ])
                </td>
                <td id="btns-status-{{ $email->id }}">
                    @include('admin.emails._btn-status', [
                        'email' => $email,
                    ])
                </td>
            </tr>
            @if (!empty($email->webhookPostmarks) && $email->webhookPostmarks->count())
                @include('admin.emails.tables._webhook-postmark', [
                    'colspan' => 8,
                    'email' => $email,
                    'email' => $email,
                ])
            @endif
        @endforeach
    </tbody>
</table>
