@extends('admin.layouts.app', [
    'pageTitle' => "Dashboard"
])

@section('content')
    <div class="page-header">
        {{-- <h1>@lang('dashboard.this_week')</h1> --}}
    </div>
    <div class="row g-2">
        <div class="col-md-6">
            <div class="callout callout-success mb-2 shadow-sm">
                <h6 class="fw-bold">
                    Chào mừng bạn đã đến với Delfi Checkin, {{ auth()->user()->name }}!
                </h6>
                <p class="text-xs">
                    {{ config("info.quotes")[array_rand(config("info.quotes"))] }}
                </p>
            </div>
        </div>
        @if (auth()->user()->isSysAdmin() || auth()->user()->isAdmin())
            <div class="col-md-6">
                <div class="container">
                    <div class="step-wrapper">
                        <div class="line"></div>
                        <div class="step-point">
                            <a href="#" class="text-sm">
                                <div class="circle-sm bg-danger"></div>
                                <div>&nbsp;</div>
                            </a>
                        </div>
                        @foreach (config("info.dashboard.steps") as $index => $step)
                            <div class="step">
                                <a href="{{ isset($step['route']) ? route($step['route']) : "#" }}" class="text-sm">
                                    <div class="circle">{{ ++$index }}</div>
                                    <div class="text-xs">{!! $step['text'] !!}</div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
    <div class="row g-2">
        <div class="col-md-3">
            <div class="content-block bg-white px-4 py-3 rounded-lg shadow">
                <h6 class="text-gray-500">
                    @admin()
                        Tổng Số Sự kiện
                        <a href="{{ route('admin.events.create') }}" class="text-xs text-primary">
                            <x-icon name="plus-square" prefix="fa-regular"/>
                        </a>
                    @else
                        {{ $event->name }}
                    @endadmin
                </h6>
                <p class="text-l font-bold text-green-600"></p>
                @admin()
                    <p class="text-2xl font-bold text-blue-600">
                        {{ count($events ?? []) }}
                        <span class="text-xs text-secondary">
                            sự kiện
                        </span>
                    </p>
                @else
                    <p class="text-2xl font-bold text-blue-600">
                        {{ empty($totalCheckedIn) ? 0 : $totalCheckedIn->count() }}
                        <span class="text-xs text-secondary">
                            đã checkin
                        </span>
                    </p>
                @endadmin
                <p class="text-l font-bold text-green-600"></p>
                <p class="text-2xl font-bold text-blue-600">
                    {{ $clients ?? 0 }}
                    <span class="text-xs text-secondary">
                        khách
                    </span>
                </p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="content-block bg-white px-4 py-3 rounded-lg shadow">
                <h6 class="text-gray-500">
                    Landing page(s)
                    @admin()
                        <a href="{{ route('admin.events.create') }}" class="text-xs text-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#selectEventModal"
                        >
                            <x-icon name="plus-square" prefix="fa-regular"/>
                        </a>
                    @endadmin
                    <div class="modal fade" id="selectEventModal" data-bs-keyboard="true" tabindex="-1"
                        aria-labelledby="selectEventModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="selectEventModalLabel">
                                        Chọn sự kiện
                                        <a href="{{ route('admin.events.create') }}" class="text-xs text-primary">
                                            <x-icon name="plus-square" prefix="fa-regular"/>
                                        </a>
                                    </h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="{{ route('admin.landing_pages.select-event-to-create') }}" method="GET">
                                    <div class="modal-body text-sm">
                                        <div class="row">
                                            <div class="col-md-12">
                                                @include('components.select', [
                                                    'fieldName'     => 'event_id',
                                                    'id'            => 'event_id',
                                                    'options'       => !empty($events) ? $events
                                                                    ->pluck('name', 'id')
                                                                    ->toArray() : [],
                                                    'selected'      => null,
                                                ])
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                                            @lang('common.close')
                                        </button>
                                        <button type="submit" class="btn btn-sm btn-primary">
                                            Chọn
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </h6>
                <p class="text-l font-bold text-green-600"></p>
                <p class="text-2xl font-bold text-blue-600">
                    {{ $landingPages ?? 0 }}
                    <span class="text-xs text-secondary">
                        page(s)
                    </span>
                </p>
                <p class="text-l font-bold text-green-600"></p>
                <p class="text-2xl font-bold text-blue-600">
                    {{ $clientsRegisterLp ?? 0 }}
                    <span class="text-xs text-secondary">
                        đã đăng ký
                    </span>
                </p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="content-block bg-white px-4 py-3 rounded-lg shadow">
                <h6 class="text-gray-500">
                    Gửi mail
                    @admin()
                        <a href="{{ route('admin.events.create') }}" class="text-xs text-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#selectEventForCampaignModal"
                        >
                            <x-icon name="plus-square" prefix="fa-regular"/>
                        </a>
                    @endadmin
                    <div class="modal fade" id="selectEventForCampaignModal" data-bs-keyboard="true" tabindex="-1"
                        aria-labelledby="selectEventForCampaignModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="selectEventForCampaignModalLabel">
                                        Chọn sự kiện
                                        <a href="{{ route('admin.events.create') }}" class="text-xs text-primary">
                                            <x-icon name="plus-square" prefix="fa-regular"/>
                                        </a>
                                    </h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="{{ route('admin.campaigns.select-event-to-create') }}" method="GET">
                                    <div class="modal-body text-sm">
                                        <div class="row">
                                            <div class="col-md-12">
                                                @include('components.select', [
                                                    'fieldName'     => 'event_id',
                                                    'id'            => 'event_id',
                                                    'options'       => !empty($events) ? $events
                                                                    ->pluck('name', 'id')
                                                                    ->toArray() : [],
                                                    'selected'      => null,
                                                ])
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                                            @lang('common.close')
                                        </button>
                                        <button type="submit" class="btn btn-sm btn-primary">
                                            Chọn
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </h6>
                <p class="text-l font-bold text-green-600"></p>
                <p class="text-2xl font-bold text-blue-600">
                    {{ $campaigns ?? 0 }}
                    <span class="text-xs text-secondary">
                        campaign(s)
                    </span>
                </p>
                <p class="text-l font-bold text-green-600"></p>
                <p class="text-2xl font-bold text-blue-600">
                    {{ $emails ?? 0 }}
                    <span class="text-xs text-secondary">
                        email đã gửi
                    </span>
                </p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="content-block bg-white px-4 py-3 rounded-lg shadow">
                <h3 class="text-xl text-gray-500">Tháng {{ $month }}</h3>
                @admin
                    <p class="text-l font-bold text-{{ empty($eventsThisMonth) || $eventsThisMonth == 0 ? 'danger' : 'green' }}-600">
                        {{ $eventsThisMonth ?? 0 }} sự kiện
                    </p>
                @endadmin
                <p class="text-l font-bold text-{{ empty($clientsThisMonth) || $clientsThisMonth == 0 ? 'danger' : 'green' }}-600">
                    {{ $clientsThisMonth ?? 0 }} khách
                </p>
                <p class="text-l font-bold text-green-600">
                    {{ $emailsThisMonth ?? 0 }}
                </p>
            </div>
        </div>
    </div>
    <div class="row charts mt-2 g-2">
        @admin()
            <div class="col-xl-3">
                <div class="bg-white p-4 rounded-lg shadow" style="max height: 400px !important; height: 400px !important; overflow: hidden;">
                    <label class="text font-semibold mb-2">
                        Sự kiện có nhiều khách hàng
                    </label>
                    <div class="pb-2" style="max-height: 100%;">
                        @foreach ($clientEventData as $index => $event)
                            <a
                                href="{{ $index == 9 ? "#" : route('admin.events.edit', $event['id']) }}"
                                class="row mb-1 py-1 text-sm fw-bold align-items-center
                                    {{ $index == 0 ? "bg-success rounded shadow-sm" : "" }}
                                    {{ $index == 1 ? "bg-warning rounded shadow-sm" : "" }}
                                    {{ $index == 2 ? "bg-info rounded shadow-sm" : "" }}
                                    {{ $index == 3 ? "bg-light rounded shadow-sm" : "" }}
                                    bg-opacity-50 link-opacity-50-hover hover-card"
                            >
                                <div class="col-md-1 text-xs ps-1 pe-0">
                                    {{ ++$index }}
                                </div>
                                <div class="col-md-6 {{ $index == 10 ? "fst-italic text-secondary" : "" }}">
                                    <div class="w-100 text-truncate">
                                        {{ $event['name'] }}
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    {{ $event['quantity'] }}
                                    <x-icon name="users"/>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <div class="col-xl-6">
                <div style="width: 100%; height: 400px;" class="shadow bg-white rounded p-4">
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
                        <h6 class="text-lg font-semibold mb-2">
                            Theo dõi checkin
                        </h6>
                        <span class="text-gray text-xs d-lg-block d-md-block d-none">
                            Báo cáo theo thời gian thực trong thời gian diễn ra sự kiện
                        </span>
                        <canvas id="checkinChart"
                            style="height: 85% !important; max-height: 85% !important;"></canvas>
                        {{-- <canvas id="checkinChart" style=""></canvas> --}}
                    </div>
                </div>
            </div>
        @endadmin
        @admin()
            <div class="col-xl-3">
                <div class="bg-white p-4 rounded-lg shadow" style="max height: 400px !important; height: 400px !important;">
                    <h6 class="text font-semibold mb-2">
                        Sự kiện trong các tỉnh thành
                    </h6>
                    <canvas id="pieChartProviceEventData" class="h-64" style="height: 310px !important; max-height: 310px !important;"></canvas>
                </div>
            </div>
        @else
            <div class="col-xl-3">
                <div style="width: 100%; height: 100%;" class="shadow bg-white rounded p-4">
                    <div id="col-checked-chart-loading" style="position: relative; "></div>
                    <div id="checked-chart" class="container-fluid"
                        data-checked="{{ count($checked) }}"
                        data-total="{{ $clients ?? 0 }}"
                        style="position: relative;"
                    >
                        <a id="btn-refresh-chart" href="" class="text-gray" style="position: absolute; top: 5px; right: 5px;">
                            <i class="bx bx-refresh bx-sm"></i>
                        </a>
                        <h6 class="text-lg font-semibold mb-2">
                            Đã checkin/Tổng số khách mời
                        </h6>
                        <canvas
                            id="pieChart"
                            style="height: 92% !important; max-height: 92% !important;"
                        >
                        </canvas>
                    </div>
                </div>
            </div>
        @endadmin
        @admin()
            <div class="col-xl-6">
                <div class="bg-white p-4 rounded-lg shadow" style="max height: 400px !important; height: 400px !important;">
                    <h6 class="text-lg font-semibold mb-2">
                        Xu hướng đầu vào của các sự kiện (Tháng {{ $month }})
                    </h6>
                    <canvas id="barChart" class="h-64"
                        style="height: 310px !important; max-height: 310px !important;"></canvas>
                </div>
            </div>
        @else
            <div class="col-xl-3">
                <div class="bg-white p-4 rounded-lg shadow" style="width: 100%; height: 100%;">
                    <h6 class="text-lg font-semibold mb-2">
                        Đầu vào sự kiện
                    </h6>
                    <canvas id="barChart"
                        style="height: 400px !important; max-height: 100% !important;"
                        {{-- height="100" --}}
                    >
                    </canvas>
                </div>
            </div>
        @endadmin
    </div>
    @if (!empty($videcAnalytics))
        <div class="row g-2 mt-2">
            <div class="col-12">
                @include('admin.videc._ticket-analytics', [
                    'analytics' => $videcAnalytics,
                ])
            </div>
        </div>
    @endif
    <div class="row mt-2 g-2">
        <div class="col-md-6">
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-4">Khách mời được đăng ký/nhập Gần Đây</h3>
                <div class="table table-responsive">
                    <table class="w-100">
                        <thead class="bg-gray-100 text-xs">
                            <tr>
                                <th class="p-2 text-left">Qrcode</th>
                                <th class="p-2 text-left">Thông tin</th>
                                <th class="p-2 text-left">Ngày tạo</th>
                                <th class="p-2 text-left">Trạng Thái</th>
                                <th class="p-2 text-left">Cập Nhật</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($clientsx5 as $client)
                                <tr class="text-xs" data-href="{{ route('admin.clients.edit', [
                                        'client'    => $client,
                                    ]) }}"
                                >
                                    <td class="p-2">{{ $client->qrcode }}</td>
                                    <td class="p-2">{{ $client->name }}</td>
                                    <td class="p-2">@humanize_date($client->created_at, 'd/m/Y H:i:s')</td>
                                    <td class="p-2">
                                        <label class="btn btn-sm {{ $client->getStatusClass() }}">{{ $client->getStatusText() }}</label>
                                    </td>
                                    <td class="p-2">
                                        {!! $client->updated_by ? $client->user->name : "<em>Không có</em>" !!}
                                        <br>
                                        @humanize_date($client->updated_at, 'd/m/Y H:i:s')
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-4">Bảng số lượng checkin theo loại khách mời</h3>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="text-nowrap">Loại khách mời</th>
                                <th class="text-nowrap text-center">Số lượng đăng ký</th>
                                <th class="text-nowrap text-center">Số lượng checkin</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $types = array_unique(array_merge(
                                    array_keys($registerByType ?? []),
                                    array_keys($checkinByType ?? [])
                                ));
                            @endphp

                            @forelse($types as $type)
                                <tr>
                                    <td>{{ $type }}</td>
                                    <td class="text-center">{{ $registerByType[$type] ?? 0 }}</td>
                                    <td class="text-center">{{ $checkinByType[$type] ?? 0 }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Không có dữ liệu</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow mt-2">
                <h3 class="text-lg font-semibold mb-4">Bảng số lượng checkout theo loại khách mời</h3>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="text-nowrap">Loại khách mời</th>
                                <th class="text-nowrap text-center">Số lượng đăng ký</th>
                                <th class="text-nowrap text-center">Số lượng checkout</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $types = array_unique(array_merge(
                                    array_keys($registerByType ?? []),
                                    array_keys($checkoutByType ?? [])
                                ));
                            @endphp

                            @forelse($types as $type)
                                <tr>
                                    <td>{{ $type }}</td>
                                    <td class="text-center">{{ $registerByType[$type] ?? 0 }}</td>
                                    <td class="text-center">{{ $checkoutByType[$type] ?? 0 }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Không có dữ liệu</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            @admin()
                <div class="bg-white p-4 rounded-lg shadow">
                    <h3 class="text-lg font-semibold mb-4">
                        Sự kiện đang triển khai
                        <span class="text-danger">
                            {{ !empty($eventsOnGoing) && $eventsOnGoing->count() ? $eventsOnGoing->count() : 0 }}
                        </span>
                    </h3>
                    <div class="table table-responsive">
                        <table class="w-100">
                            <thead class="bg-gray-100 text-xs">
                                <tr>
                                    <th class="p-2 text-left">Thông tin</th>
                                    <th class="p-2 text-left col-3">Tiến độ</th>
                                    <th class="p-2 text-left">Diễn ra</th>
                                    <th class="p-2 text-left">Trạng Thái</th>
                                    <th class="p-2 text-left">Cập Nhật</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($eventsOnGoing as $event)
                                    <tr class="text-xs" data-href="{{ route('admin.events.edit', $event) }}">
                                        <td class="p-2">
                                            {{ $event->name }}
                                        </td>
                                        <td class="p-2">
                                            @include('components._progress', [
                                                'completed' => $event->progress,
                                                'total'     => $event->total,
                                            ])
                                        </td>
                                        <td class="p-2">
                                            @if ($event->from_date == $event->to_date)
                                                @humanize_date($event->from_date, 'd/m/Y')
                                            @else
                                                @humanize_date($event->from_date, 'd/m/Y')
                                                <br>
                                                @humanize_date($event->to_date, 'd/m/Y')
                                            @endif
                                        </td>
                                        <td class="p-2">
                                            <label class="btn btn-xs {{ $event->getStatusClass() }}">
                                                {{ $event->getStatusText() }}
                                            </label>
                                        </td>
                                        <td class="p-2">
                                            {{ $event->user->name }}
                                            <br>
                                            @humanize_date($event->updated_at, 'd/m/Y H:i')
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endadmin
        </div>
    </div>
@endsection

@push('admin_js')
    {{-- chart --}}
    @vite([
        'resources/js/admin/dashboard/detail.js'
    ])
    <script src="{{ asset('offlines/offline-js/chart.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const clientEventData = @json($clientEventData);
            const totalClientData = {{ $totalClientData }};
        });

        const month = @json($month);
        const labels = @json($register_sources);
        const registers = @json($registers);

        const barChart = new Chart(document.getElementById('barChart'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    // label: 'Số Lượng',
                    data: registers,
                    backgroundColor: ['#fd7e14', '#0d6efd', '#198754', 'rgba(255,0,0,0.8)'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: false
                    }
                },
                legend: {
                    display: false // filter
                },
                title: {
                    display: false,
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // const lineChart = new Chart(document.getElementById('lineChart'), {
        //     type: 'line',
        //     data: {
        //         labels: labels,
        //         datasets: [{
        //                 label: 'Đăng ký landing page',
        //                 data: [7, 12, 5, 9],
        //                 borderColor: '#8B5CF6', // Purple
        //                 backgroundColor: 'rgba(139, 92, 246, 0.2)',
        //                 fill: true,
        //                 tension: 0.1
        //             },
        //             {
        //                 label: 'Gửi email thư mời',
        //                 data: [5, 3, 9, 10],
        //                 borderColor: '#3B82F6', // Blue
        //                 backgroundColor: 'rgba(59, 130, 246, 0.2)',
        //                 fill: true,
        //                 tension: 0.1
        //             },
        //         ]
        //     },
        //     options: {
        //         responsive: true,
        //         scales: {
        //             y: {
        //                 beginAtZero: true,
        //                 title: {
        //                     display: true,
        //                     text: 'Số Lượng Sản Phẩm'
        //                 }
        //             }
        //         },
        //         plugins: {
        //             legend: {
        //                 position: 'top'
        //             },
        //             // title: {
        //             //     display: true,
        //             //     text: `Xu Hướng đầu vào sự kiện (Tháng ${month})`
        //             // }
        //         }
        //     }
        // });
    </script>
    @admin()
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Retrieve data from the controller
                const provinceEventData = @json($provinceEventData);
                const totalQuantity = {{ $totalQuantity }};

                // Create pie chart
                const pieChart2 = new Chart(document.getElementById('pieChartProviceEventData'), {
                    type: 'pie',
                    data: {
                        labels: provinceEventData.map(product =>
                            `${product.name} (${totalQuantity > 0 ? ((product.quantity / totalQuantity) * 100).toFixed(1) : 0}%)`
                        ),
                        datasets: [{
                            data: provinceEventData.map(product => product.quantity),
                            backgroundColor: [
                                '#fd7e14',
                                '#0d6efd',
                                '#adb5bd',
                                '#198754',
                                '#dc3545',
                                '#ffc107',
                                '#20c997',
                                '#6f42c1',
                                '#e83e8c',
                                '#6c757d', // Add more colors if needed, for "Others"
                            ].slice(0, provinceEventData.length) // Ensure the number of colors matches the data length
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const value = context.parsed;
                                        const percentage = totalQuantity > 0 ? ((value / totalQuantity) * 100).toFixed(1) : 0;
                                        return `${context.label}: ${value} (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            });
        </script>
    @endadmin
@endpush

@push('admin_css')
    {{-- tailwind --}}
    <link href="{{ asset('offlines/offline-css/2.2.19-tailwind.min.css') }}" rel="stylesheet">
    <style>
        .content-block {
            height: 140px;
            max-height: 140px;
        }
        .step-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            padding: 10px 5px;
            overflow-x: auto;
        }
        .step {
            position: relative;
            text-align: center;
            flex: 1;
            min-width: 10%;
            z-index: 1;
        }
        .step-point {
            position: relative;
            text-align: center;
            /* flex: 1; */
            min-width: 5%;
            z-index: 1;
        }
        .step a {
            text-decoration: none;
            color: #333;
            display: block;
            transition: transform 0.3s ease-in-out;
        }
        .step a:hover {
            transform: translateY(-5px);
            color: #0d6efd;
        }
        .circle {
            width: 30px;
            height: 30px;
            background-color: #0d6efd;
            color: #fff;
            border-radius: 50%;
            margin: 0 auto 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
            font-size: 18px;
            transition: transform 0.3s ease-in-out;
        }
        .circle-sm {
            width: 15px;
            height: 15px;
            background-color: #0d6efd;
            color: #fff;
            border-radius: 50%;
            margin: 0 auto 5px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
            font-size: 18px;
            transition: transform 0.3s ease-in-out;
        }
        .step a:hover .circle {
            transform: scale(1.1);
            background-color: #0b5ed7;
        }
        .line {
            position: absolute;
            top: 30%;
            height: 4px;
            background-color: #dee2e6;
            left: 0;
            right: 0;
            z-index: 0;
        }
        @media (max-width: 576px) {
            .step-wrapper {
                flex-wrap: nowrap;
                overflow-x: scroll;
            }
            .step {
                flex: 0 0 auto;
                margin: 0 30px;
            }
        }
    </style>
@endpush
