<tr class="collapse email-webhook-collapse" id="collapseWebhook{{ $email['message_id'] }}" style="display: none; width: 100%;">
    <td colspan="{{ $colspan ?? 8 }}" class="p-0">
        <table class="table table-responsive table-borderless mb-0 w-100 text-xs">
            <thead>
                <tr>
                    <th scope="col"></th>
                    <th scope="col" class="col-1">Chi tiết</th>
                    <th scope="col" class="col-2">Email</th>
                    <th scope="col" class="col-2">Thời gian</th>
                    <th scope="col" class="col-1">Trạng thái</th>
                    <th scope="col">Stream & Tag</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($webhookPostmarks as $webhook)
                    <tr>
                        <th scope="row" style="vertical-align: middle;">
                            {{-- {{ $webhook['id'] }} --}}
                        </th>
                        <td style="
                                word-wrap: break-word;
                                white-space: normal;
                                max-width: 100%;
                            "
                        >
                            {{ $webhook['details'] ?? "-" }}
                        </td>
                        <td style="vertical-align: middle;">
                            {{ $webhook['email'] }}
                        </td>
                        <td style="vertical-align: middle;">
                            @if ($webhook['status'] === 'Delivery')
                                {{ \Carbon\Carbon::parse($webhook['record_time'])->addHours(7)->format('Y-m-d H:i:s') }}
                            @elseif (in_array($webhook['status'], ['Click', 'Open']))
                                {{ \Carbon\Carbon::parse($webhook['record_time'])->subHours(7)->format('Y-m-d H:i:s') }}
                            @else
                                {{ \Carbon\Carbon::parse($webhook['record_time'])->addHours(7)->toDateTimeString(); }}
                            @endif
                            {{-- {{ \Carbon\Carbon::parse($webhook['record_time'])->addHours(7)->toDateTimeString(); }} --}}
                        </td>
                        <td style="vertical-align: middle;">
                            <span class="btn btn-xs btn-{{ $webhook->getStatusClass() }} btn-sm">
                                {{ $webhook['status'] }}
                            </span>
                        </td>
                        <td style="vertical-align: middle;">
                            {{ $webhook['message_stream'] }} - {{ $webhook['tag'] ?? "-" }}
                            @if ($webhook['response'])
                                <a href="#" onclick="return false" data-bs-toggle="modal" data-bs-target="#webhook-response-{{ $webhook['id'] }}Modal">
                                    <x-icon name="circle-info" />
                                </a>
                                @include('admin.emails._modal-info', [
                                    'modalId'   => "webhook-response-{$webhook['id']}Modal",
                                    'data'      => json_encode($webhook['response']),
                                ])
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </td>
</tr>
