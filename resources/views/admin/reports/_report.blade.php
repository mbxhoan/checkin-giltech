<input type="hidden" id="event_id" value="{{ $event->id }}">
@unless ($showTicketSummary ?? false)
<div class="row g-2 mb-1">
    <div id="col-checked-chart" class="col-md-4"
        data-url=""
    >
        <div style="width: 100%; height: 100%;" class="border shadow-sm bg-white rounded p-2">
            <div id="col-checked-chart-loading" style="position: relative; ">
                {{-- <div class="" style="position: absolute; top: 10px; left: 0;">
                    <i class="bx bx-loader bx-spin text-muted" style="font-size: 50px;"></i>
                </div> --}}
            </div>
            <div id="checked-chart" class="container-fluid"
                {{-- data-checked="{{ $totalCheckedIn->count() }}" --}}
                data-checked="{{ $totalCheckedIn }}"
                data-total="{{ $clients->total() - $totalCheckedIn }}"
                style="position: relative;"
            >
                <a id="btn-refresh-chart" href="" class="text-gray" style="position: absolute; top: 5px; right: 5px;">
                    <i class="bx bx-refresh bx-sm"></i>
                </a>
                <h5>
                    Đã checkin/Tổng số khách mời
                </h5>
                {{-- thêm ngày tại đây --}}
                <canvas
                    id="pieChart"
                    style="height: 90% !important; max-height: 90% !important;"
                >
                </canvas>
                {{-- <canvas id="checkinChart" style=""></canvas> --}}
            </div>
        </div>
    </div>
    {{-- customize --}}
    {{-- sunhouse --}}
    <div id="col-checkin-chart" class="col-md-8"
        data-url=""
    >
        @if ($event->code != 'sunhouse')
            <div style="width: 100%; height: 400px;" class="border shadow-sm bg-white rounded p-2">
                <div id="col-checkin-chart-loading" style="position: relative; ">
                    {{-- <div class="" style="position: absolute; top: 10px; left: 0;">
                        <i class="bx bx-loader bx-spin text-muted" style="font-size: 50px;"></i>
                    </div> --}}
                </div>
                <div id="checkin-chart" class="container-fluid"
                    data-x="{{ json_encode($dateTimes) }}"
                    data-y="{{ json_encode($checkins) }}"
                    style="position: relative;"
                >
                    <a id="btn-refresh-chart" href="" class="text-gray" style="position: absolute; top: 5px; right: 5px;">
                        <i class="bx bx-refresh bx-sm"></i>
                    </a>
                    <h5>
                        Theo dõi checkin
                    </h5>
                    <span class="text-gray text-xs d-lg-block d-md-block d-none">
                        Báo cáo theo thời gian thực trong thời gian diễn ra sự kiện
                    </span>
                    <canvas id="checkinChart"
                        style="height: 85% !important; max-height: 85% !important;"></canvas>
                    {{-- <canvas id="checkinChart" style=""></canvas> --}}
                </div>
            </div>
        @else
            <div class="border shadow-sm bg-white rounded p-2">
                <h6>
                    Checkin theo Hàng:
                </h6>
                <div class="row">
                    @foreach ($sunhouse['hang'] as $key => $detail)
                        <div class="col-md-3 col-4 text-sm">
                            {{ $key }}: <span class="fw-bold {{ ($detail['count'] ?? 0) ? "" : "text-danger" }}">{{ $detail['count'] ?? 0 }}</span>/{{ $detail['total'] }}
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="border shadow-sm bg-white rounded p-2 mt-2">
                <h6>
                    Checkin theo Tầng:
                </h6>
                <div class="row">
                    @foreach ($sunhouse['tang'] as $key => $detail)
                        <div class="col-6 text-sm">
                            Tầng {{ $key }}:
                            <span class="fw-bold {{ ($detail['count'] ?? 0) ? "" : "text-danger" }}">{{ $detail['count'] ?? 0 }}</span>/{{ $detail['total'] }}
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

{{-- customize --}}
{{-- sunhouse --}}
@if ($event->code === 'sunhouse')
    <div class="row g-2 mb-4">
        {{-- <div class="col-md-4">
            <div class="border shadow-sm bg-white rounded p-2">
                <h6>
                    Checkin theo Tầng:
                </h6>
                <div class="row">
                    @foreach ($sunhouse['tang'] as $key => $detail)
                        <div class="col-md-6 text-sm">
                            Tầng {{ $key }}:
                            <br>
                            <span class="fw-bold {{ ($detail['count'] ?? 0) ? "" : "text-danger" }}">{{ $detail['count'] ?? 0 }}</span>/{{ $detail['total'] }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div> --}}
        <div class="col-md-4">
            <div class="border shadow-sm bg-white rounded p-2 mt-2">
                <h6>
                    Checkin theo Miền:
                </h6>
                <div class="row">
                    @foreach ($sunhouse['mien'] as $key => $detail)
                        <div class="col-md-4 col-6 text-sm">
                            {{ $key }}:
                            <span class="fw-bold {{ ($detail['count'] ?? 0) ? "" : "text-danger" }}">{{ $detail['count'] ?? 0 }}</span>/{{ $detail['total'] }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="border shadow-sm bg-white rounded p-2 mt-2">
                <h6>
                    Checkin theo Kênh:
                </h6>
                <div class="row">
                    @foreach ($sunhouse['mien'] as $key => $detail)
                        <div class="col-md-4 col-6 text-sm">
                            {{ $key }}:
                            <span class="fw-bold {{ ($detail['count'] ?? 0) ? "" : "text-danger" }}">{{ $detail['count'] ?? 0 }}</span>/{{ $detail['total'] }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="border shadow-sm bg-white rounded p-2 mt-2">
                <h6>
                    Checkin theo Thiệp:
                </h6>
                <div class="row">
                    @foreach ($sunhouse['type'] as $key => $detail)
                        <div class="col-md-4 col-4 text-sm">
                            {{ $key }}:
                            <br>
                            <span class="fw-bold {{ ($detail['count'] ?? 0) ? "" : "text-danger" }}">{{ $detail['count'] ?? 0 }}</span>/{{ $detail['total'] }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endif

{{-- galaxy-holding --}}
<div class="row g-2 mb-4">
    <div class="col-md-4">
        <div class="border shadow-sm bg-white rounded p-2 mt-2">
            <h6>
                Checkin theo khu vực
            </h6>
            <div class="row">
                @foreach ($event->areas as $area)
                    <div class="col-md-4 col-6 text-sm">
                        {{ $area->name }}:
                        <span class="fw-bold {{ ($galaxy[$area->name]['count'] ?? 0) ? "" : "text-danger" }}">{{ $galaxy[$area->name]['count'] ?? 0 }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="border shadow-sm bg-white rounded p-2 mt-2">
            <h6>
                Checkin theo Nhóm khách:
            </h6>
            <div class="row g-3">
                @foreach ($galaxy['type'] as $key => $detail)
                @php
                    $count = $detail['count'] ?? 0;
                    $total = $detail['total'] ?? 0;
                    $percent = $total > 0 ? round(($count / $total) * 100, 1) : 0;
                @endphp
                    <div class="col-md-4 col-6">
                        <div class="border rounded p-2 h-100 bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted small">{{ $key }}</span>
                                <span class="fw-bold {{ $count ? 'text-success' : 'text-danger' }}">
                                    {{ $count }}/{{ $total }}
                                </span>
                            </div>
                            <div class="progress mb-1" style="height:6px;">
                                <div 
                                    class="progress-bar bg-success" 
                                    style="width: {{ $percent }}%">
                                </div>
                            </div>
                            <div class="text-end small text-muted">
                                {{ $percent }}%
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endunless

@if (!empty($videcAnalytics))
    @include('admin.videc._ticket-analytics', [
        'analytics' => $videcAnalytics,
    ])
@endif

{{-- buttons --}}
<div class="mb-2 d-lg-flex justify-content-between">
    <div class="">
        <a href=""
            class="btn {{ request()->hasAny([
                'customer_id',
                'status',
                'type',
                'register_source',
                'field_date',
                'from_date',
                'to_date'
            ]) ? 'btn-outline-warning' : 'btn-warning' }}
            btn-sm align-self-center mb-lg-0 mb-2"
            data-bs-toggle="modal"
            data-bs-target="#filterModal"
        >
            Bộ lọc
            <x-icon name="filter"/>
        </a>
        @include('admin.reports._modal-filter', [
            'modalId'       => 'filterModal',
            'title'         => "Bộ lọc",
            'submitBtn'     => "Lọc",
            'model'         => \App\Models\Client::getModel(),
            'route'         => route('admin.reports.report', [
                'event'     => $event
            ]),
        ])
        {{-- <a href="{{ route('admin.clients.export-list', ['event' => $event]) }}?{{ http_build_query(request()->all()) }}" class="btn btn-success btn-sm align-self-center mb-lg-0 mb-2">
            <x-icon name="file-excel" prefix="fa-solid"/>
            @lang('imports.export')
        </a> --}}
        <a href="{{ route('admin.clients.download-qrcodes', [
                'event' => $event
            ]) }}?{{ http_build_query(request()->all()) }}" title="Tải xuống" class="btn btn-primary btn-sm mb-lg-0 mb-2"
        >
            <x-icon name="download" />
            Tải Qrcodes
        </a>
        <a href="{{ route('admin.checkins.index', $event) }}" class="btn btn-sm btn-secondary mb-lg-0 mb-2">
            <x-icon name="arrow-circle-right" />
            Đã checkin: <span class="fw-bold">{{ $totalCheckedIn ?? 0 }}</span>
        </a>
        @include('admin.clients._btn-export-list', [
            'event'         => $event,
            'text'          => 'Tổng hợp khách mời',
            // 'fields'        => request()->all()
        ])
        @include('admin.checkins._btn-export-list', [
            'event'     => $event,
            // 'fields'    => request()->all(),
            'route'     => route('admin.checkins.export-check-in-out', [
                'event' => $event
            ]),
            'text'      => 'Chi tiết checkin'
        ])
        @include('admin.checkins._btn-export-list', [
            'event'     => $event,
            // 'fields'    => request()->all(),
            'route'     => route('admin.checkins.export-checkin_count', [
                'event' => $event
            ]),
            'text'      => 'Tổng hợp checkin',
        ])
    </div>
</div>
<div class="table table-responsive">
    @include('admin.reports.clients.table', [
        'event'     => $event,
        'clients'   => $clients
    ])
</div>
