<div id="report-email">
    <div class="d-flex mb-2">
        @if (count($dataStatuses))
            @foreach ($dataStatuses as $status => $count)
                @if (!in_array($status, ['Bounce']))
                    <div class="bg-light border rounded shadown-sm px-4 py-3 ms-2" style="width: 150px;">
                        <div class="text-sm fw-bold">
                            @switch($status)
                                @case('Delivery')
                                    DELIVERED
                                @break

                                @case('Open')
                                    ĐÃ MỞ
                                @break

                                @case('Click')
                                    ĐÃ CLICK
                                @break

                                @case('Bounce')
                                    BỊ CHẶN
                                @break

                                @default
                                    {{ $status }}
                            @endswitch
                        </div>
                        <div class="text-danger text-sm">
                            {{ $count }}
                        </div>
                    </div>
                @endif
            @endforeach
        @endif
        {{-- <a href="" class="bg-light border rounded shadown-sm px-4 py-3 ms-2 text-decoration-none text-dark" style="width: 150px;"
            data-bs-toggle="modal"
            data-bs-target="#dupEmailsModal"
        >
            <div class="text-sm fw-bold">
                LẶP LẠI
            </div>
            <div class="text-danger text-sm">
                {{ $hooks->where('total_webhook', '>', 1)->count() }}
            </div>
        </a> --}}
        {{-- <div class="modal fade" id="dupEmailsModal" tabindex="-1" aria-labelledby="dupEmailsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="dupEmailsLabel">
                            Danh sách email gửi nhiều lần
                            <span class="text-danger">
                                ({{ $hooks->where('total_webhook', '>', 1)->count() }})
                            </span>
                        </h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table table-responsive">
                            <table class="table table-striped table-hover table-sm">
                                <tbody>
                                    <tr>
                                        <th scope="col" class="fw-bold">
                                            Email
                                        </th>
                                        <th scope="col" class="fw-bold">
                                            Số lần nhận
                                        </th>
                                    </tr>
                                    @foreach ($hooks->where('total_webhook', '>', 1) as $hook)
                                        <tr>
                                            <td>
                                                {{ $hook->email }}
                                            </td>
                                            <td>
                                                {{ $hook->total_webhook }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            @lang('common.close')
                        </button>
                    </div>
                </div>
            </div>
        </div> --}}
    </div>
    <div class="d-flex align-items-center gap-2">
        @foreach ($clientTypes as $type => $label)
            @php
                $params = request()->all(); // keep all current params
                $types = (array) ($params['types'] ?? []);

                // Toggle key on/off
                if (in_array($type, $types)) {
                    $types = array_diff($types, [$type]);
                } else {
                    $types[] = $type;
                }

                $params['types'] = $types; // update types
                $params['event'] = $event;
            @endphp
            <a href="{{ route('admin.reports.report', $params) }}"
                class="btn btn-xs align-self-center mb-lg-0 mb-2 h-100
                            {{ in_array($type, (array) request()->input('types', [])) ? 'btn-primary' : 'btn-outline-primary' }}">
                {{ $label }}
            </a>
        @endforeach
        {{-- @dd($hooks->where('total_webhook', '>', 1), $hooks, $hooks->where('webhook_postmarks.status', 'Delivery')) --}}
    </div>
    <div class="d-flex align-items-center justify-content-start my-2">
        <h6>
            {{-- Số mail đã gửi:
            <span class="text-success">
                {{ count($emailsSent) }}
            </span>
            /
            <span class="">
                {{ count($emails) }}
            </span> --}}
        </h6>
        <div class="w-25">
            <input type="text" id="searchBox" placeholder="Tìm kiếm..." class="form-control mb-2 w-100" autofocus>
        </div>
        {{-- <div class="">
            <a href="{{ route('admin.emails.export-report', [
                'event' => $event,
            ]) }}"
                class="btn btn-sm btn-success">
                <x-icon name="file-excel" prefix="fa-solid" />
                @lang('imports.export')
            </a>
        </div> --}}
    </div>
    <p class="text-xs text-secondary">
        Chọn vào email để xem chi tiết lịch sử gửi
        <x-icon name="arrow-down" />
    </p>
    <div id="history-send-mail" class="table table-responsive" data-time="7">
        @include('admin.reports.emails._table-history', [
            'emails' => $emails,
        ])
    </div>
    <div class="d-flex justify-content-center" id="email-pagination-links">
        {!! $emails->links() !!}
    </div>
</div>
