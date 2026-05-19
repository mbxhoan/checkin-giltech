@extends('admin.layouts.app', [
    'pageTitle' => 'Dashboard',
])

@php
    $user = auth()->user();
    $isAdminView = $user->isSysAdmin() || $user->isAdmin();
    $eventsCollection = collect($events ?? []);
    $eventsTotal = $eventsCollection->count();
    $clientsTotal = $clients ?? 0;
    $landingPagesTotal = $landingPages ?? 0;
    $campaignsTotal = $campaigns ?? 0;
    $emailsTotal = $emails ?? 0;
    $checkedInCount = empty($totalCheckedIn) ? 0 : $totalCheckedIn->count();
    $roleLabel = $user->isSysAdmin() ? 'System admin' : ($user->isAdmin() ? 'Company admin' : 'Event operator');
    $heroTitle = $isAdminView ? 'Tổng quan điều hành hệ thống' : 'Theo dõi vận hành sự kiện';
    $heroDescription = $isAdminView
        ? 'Theo dõi nhanh số liệu chính, tiến độ triển khai và các khu vực cần xử lý tiếp theo.'
        : 'Nắm tình hình checkin, khách mời và hoạt động tại sự kiện từ một màn hình gọn hơn.';
    $topEventRows = collect($clientEventData ?? [])->take(10);
@endphp

@section('content')
    <div class="dashboard-stack">
        <div class="row g-2">
            <div class="{{ $isAdminView ? 'col-12 col-xxl-8' : 'col-12' }}">
                <section class="dashboard-panel dashboard-hero-card">
                    <span class="dashboard-kicker">{{ $roleLabel }}</span>
                    <h2>{{ $heroTitle }}</h2>
                    <p>
                        Xin chào {{ $user->name }}. {{ $heroDescription }}
                    </p>

                    <div class="dashboard-highlight-grid">
                        <div class="dashboard-highlight">
                            <span>{{ $isAdminView ? 'Cụm sự kiện đang quản lý' : 'Khách đã checkin' }}</span>
                            <strong>{{ $isAdminView ? $eventsTotal : $checkedInCount }}</strong>
                            <small>{{ $isAdminView ? 'Sự kiện đang hiển thị trong phạm vi tài khoản' : 'So với tổng số khách hiện có' }}</small>
                        </div>
                        <div class="dashboard-highlight">
                            <span>Tổng khách hiện tại</span>
                            <strong>{{ $clientsTotal }}</strong>
                            <small>Dữ liệu khách mời/đăng ký đang có trong hệ thống</small>
                        </div>
                        <div class="dashboard-highlight">
                            <span>{{ $isAdminView ? 'Landing pages' : 'Campaigns' }}</span>
                            <strong>{{ $isAdminView ? $landingPagesTotal : $campaignsTotal }}</strong>
                            <small>{{ $isAdminView ? 'Bề mặt thu lead và đăng ký đang mở' : 'Hoạt động truyền thông đang gắn với sự kiện' }}</small>
                        </div>
                        <div class="dashboard-highlight">
                            <span>{{ $isAdminView ? 'Email đã gửi' : 'Khách đăng ký tháng này' }}</span>
                            <strong>{{ $isAdminView ? $emailsTotal : ($clientsThisMonth ?? 0) }}</strong>
                            <small>{{ $isAdminView ? 'Tổng lượng email chiến dịch đã ghi nhận' : 'Số khách mới trong tháng hiện tại' }}</small>
                        </div>
                    </div>

                    <div class="dashboard-actions">
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-primary btn-sm">
                            <x-icon name="chart-column" />
                            Làm mới tổng quan
                        </a>
                        @admin
                            <a href="{{ route('admin.events.index') }}" class="btn btn-outline-primary btn-sm">
                                <x-icon name="calendar-days" />
                                Xem sự kiện
                            </a>
                            <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-primary btn-sm">
                                <x-icon name="chart-pie" />
                                Xem báo cáo
                            </a>
                        @endadmin
                        @unless($isAdminView)
                            <a href="{{ route('admin.clients.index', $event) }}" class="btn btn-outline-primary btn-sm">
                                <x-icon name="users" />
                                Danh sách khách
                            </a>
                            <a href="{{ route('admin.reports.report', $event) }}" class="btn btn-outline-primary btn-sm">
                                <x-icon name="file-lines" />
                                Báo cáo sự kiện
                            </a>
                        @endunless
                    </div>
                </section>
            </div>

            @if ($isAdminView)
                <div class="col-12 col-xxl-4">
                    <section class="dashboard-panel dashboard-panel--muted">
                        <div class="dashboard-section-title">
                            <div>
                                <h3>Luồng thao tác nhanh</h3>
                                <p>Đi từ cấu hình hệ thống đến triển khai event mà không bỏ sót bước.</p>
                            </div>
                        </div>

                        <div class="dashboard-stepper">
                            <div class="dashboard-step">
                                <a href="{{ route('admin.dashboard') }}">
                                    <span class="dashboard-step__dot">
                                        <x-icon name="house" />
                                    </span>
                                    <div class="dashboard-step__text">Dashboard</div>
                                </a>
                            </div>
                            @foreach (config('info.dashboard.steps') as $index => $step)
                                <div class="dashboard-step">
                                    <a href="{{ isset($step['route']) ? route($step['route']) : '#' }}">
                                        <span class="dashboard-step__dot">{{ $index + 1 }}</span>
                                        <div class="dashboard-step__text">{!! $step['text'] !!}</div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </section>
                </div>
            @endif
        </div>

        <div class="dashboard-metric-grid my-2">
            <section class="dashboard-panel dashboard-metric-card">
                <div class="dashboard-stat">
                    <div>
                        <span>{{ $isAdminView ? 'Sự kiện' : 'Checkin' }}</span>
                        <strong>{{ $isAdminView ? $eventsTotal : $checkedInCount }}</strong>
                    </div>
                    <span class="dashboard-stat__icon">
                        <x-icon name="{{ $isAdminView ? 'calendar-days' : 'circle-check' }}" />
                    </span>
                </div>
                <div class="dashboard-meta">
                    {{ $isAdminView ? ($eventsThisMonth ?? 0).' sự kiện bắt đầu trong tháng này' : ($clientsTotal > 0 ? $checkedInCount.'/'.$clientsTotal.' khách đã được xử lý' : 'Chưa có dữ liệu checkin') }}
                </div>
            </section>

            <section class="dashboard-panel dashboard-metric-card">
                <div class="dashboard-stat">
                    <div>
                        <span>Landing pages</span>
                        <strong>{{ $landingPagesTotal }}</strong>
                    </div>
                    <span class="dashboard-stat__icon">
                        <x-icon name="window-maximize" />
                    </span>
                </div>
                <div class="dashboard-meta">
                    {{ $clientsRegisterLp ?? 0 }} khách đến từ landing page.
                </div>
            </section>

            <section class="dashboard-panel dashboard-metric-card">
                <div class="dashboard-stat">
                    <div>
                        <span>Campaigns & email</span>
                        <strong>{{ $campaignsTotal }}</strong>
                    </div>
                    <span class="dashboard-stat__icon">
                        <x-icon name="paper-plane" />
                    </span>
                </div>
                <div class="dashboard-meta">
                    {{ $emailsTotal }} email đã ghi nhận trong hệ thống.
                </div>
            </section>

            <section class="dashboard-panel dashboard-metric-card">
                <div class="dashboard-stat">
                    <div>
                        <span>Tháng {{ $month }}</span>
                        <strong>{{ $clientsThisMonth ?? 0 }}</strong>
                    </div>
                    <span class="dashboard-stat__icon">
                        <x-icon name="chart-line" />
                    </span>
                </div>
                <div class="dashboard-meta">
                    {{ $emailsThisMonth ?? 0 }} email và {{ $isAdminView ? ($eventsThisMonth ?? 0).' sự kiện' : ($campaignsTotal ?? 0).' campaign' }} cập nhật trong tháng.
                </div>
            </section>
        </div>

        @if (!empty($videcAnalytics))
            <div class="row g-2">
                <div class="col-12">
                    @include('admin.videc._ticket-analytics', [
                        'analytics' => $videcAnalytics,
                    ])
                </div>
            </div>
        @endif

        <div class="row g-2">
            @if ($isAdminView)
                <div class="col-12 col-xxl-4">
                    <section class="dashboard-panel">
                        <div class="dashboard-section-title">
                            <div>
                                <h3>Sự kiện có nhiều khách nhất</h3>
                                <p>Ưu tiên theo số lượng khách hiện đang gắn với event.</p>
                            </div>
                        </div>

                        <div class="dashboard-ranking-list">
                            @forelse ($topEventRows as $index => $eventRow)
                                <a
                                    href="{{ isset($eventRow['id']) ? route('admin.events.edit', $eventRow['id']) : '#' }}"
                                    class="dashboard-ranking-item"
                                >
                                    <span class="dashboard-ranking-item__index">{{ $index + 1 }}</span>
                                    <span class="dashboard-ranking-item__body">
                                        <span class="dashboard-ranking-item__title">{{ $eventRow['name'] ?? 'Sự kiện chưa đặt tên' }}</span>
                                        <span class="dashboard-ranking-item__meta">{{ $eventRow['code'] ?? 'N/A' }}</span>
                                    </span>
                                    <strong>{{ $eventRow['quantity'] ?? 0 }}</strong>
                                </a>
                            @empty
                                <div class="dashboard-note">Chưa có dữ liệu sự kiện để xếp hạng.</div>
                            @endforelse
                        </div>
                    </section>
                </div>

                <div class="col-12 col-xxl-3 col-lg-5">
                    <section class="dashboard-panel dashboard-chart-card">
                        <div class="dashboard-section-title">
                            <div>
                                <h3>Sự kiện theo tỉnh/thành</h3>
                                <p>Tỷ trọng sự kiện theo địa bàn đang hoạt động.</p>
                            </div>
                        </div>
                        <div style="height: 20rem;">
                            <canvas id="pieChartProviceEventData"></canvas>
                        </div>
                    </section>
                </div>

                <div class="col-12 col-xxl-5 col-lg-7">
                    <section class="dashboard-panel dashboard-chart-card">
                        <div class="dashboard-section-title">
                            <div>
                                <h3>Xu hướng đầu vào sự kiện</h3>
                                <p>Phân bổ nguồn đăng ký trong tháng {{ $month }}.</p>
                            </div>
                        </div>
                        <div style="height: 20rem;">
                            <canvas id="barChart"></canvas>
                        </div>
                    </section>
                </div>
            @else
                <div class="col-12 col-xl-5">
                    <section class="dashboard-panel dashboard-chart-card">
                        <div class="dashboard-section-title">
                            <div>
                                <h3>Theo dõi checkin</h3>
                                <p>Báo cáo theo thời gian thực trong lúc sự kiện đang diễn ra.</p>
                            </div>
                        </div>
                        <div
                            id="checkin-chart"
                            data-x="{{ json_encode($dateTimes) }}"
                            data-y="{{ json_encode($checkins) }}"
                            style="height: 20rem;"
                        >
                            <canvas id="checkinChart" style="height: 100% !important;"></canvas>
                        </div>
                    </section>
                </div>

                <div class="col-12 col-xl-3">
                    <section class="dashboard-panel dashboard-chart-card">
                        <div class="dashboard-section-title">
                            <div>
                                <h3>Đã checkin / tổng khách</h3>
                                <p>Mức độ hoàn thành checkin tại thời điểm hiện tại.</p>
                            </div>
                        </div>
                        <div
                            id="checked-chart"
                            data-checked="{{ count($checked) }}"
                            data-total="{{ $clientsTotal }}"
                            style="height: 20rem;"
                        >
                            <canvas id="pieChart" style="height: 100% !important;"></canvas>
                        </div>
                    </section>
                </div>

                <div class="col-12 col-xl-4">
                    <section class="dashboard-panel dashboard-chart-card">
                        <div class="dashboard-section-title">
                            <div>
                                <h3>Đầu vào sự kiện</h3>
                                <p>Phân bổ khách theo nguồn đăng ký trong tháng.</p>
                            </div>
                        </div>
                        <div style="height: 20rem;">
                            <canvas id="barChart" style="height: 100% !important;"></canvas>
                        </div>
                    </section>
                </div>
            @endif
        </div>

        <div class="row g-2 mt-2">
            <div class="{{ $isAdminView ? 'col-12 col-xl-7' : 'col-12 col-xl-6' }}">
                <section class="dashboard-panel">
                    <div class="dashboard-section-title">
                        <div>
                            <h3>Khách mời được đăng ký/nhập gần đây</h3>
                            <p>Dùng để kiểm tra nhanh trạng thái dữ liệu mới cập nhật.</p>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table dashboard-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Qrcode</th>
                                    <th>Thông tin</th>
                                    <th>Ngày tạo</th>
                                    <th>Trạng thái</th>
                                    <th>Cập nhật</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($clientsx5 as $client)
                                    <tr data-href="{{ route('admin.clients.edit', ['client' => $client]) }}">
                                        <td class="dashboard-table__truncate">{{ $client->qrcode }}</td>
                                        <td>
                                            <div class="fw-semibold dashboard-table__truncate">{{ $client->name }}</div>
                                        </td>
                                        <td>{{ humanize_date($client->created_at, 'd/m/Y H:i:s') }}</td>
                                        <td>
                                            <label class="btn btn-sm {{ $client->getStatusClass() }}">{{ $client->getStatusText() }}</label>
                                        </td>
                                        <td>
                                            {{ $client->updated_by ? $client->user?->name : 'Không có' }}
                                            <div class="text-muted small">{{ humanize_date($client->updated_at, 'd/m/Y H:i:s') }}</div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">Chưa có dữ liệu khách mới.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            @if ($isAdminView)
                <div class="col-12 col-xl-5">
                    <section class="dashboard-panel">
                        <div class="dashboard-section-title">
                            <div>
                                <h3>Sự kiện đang triển khai</h3>
                                <p>{{ !empty($eventsOnGoing) && $eventsOnGoing->count() ? $eventsOnGoing->count() : 0 }} sự kiện cần theo dõi tiếp.</p>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table dashboard-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Thông tin</th>
                                        <th>Tiến độ</th>
                                        <th>Diễn ra</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($eventsOnGoing as $event)
                                        <tr data-href="{{ route('admin.events.edit', $event) }}">
                                            <td>
                                                <div class="fw-semibold dashboard-table__truncate">{{ $event->name }}</div>
                                                <div class="text-muted small">{{ $event->user?->name ?? '—' }}</div>
                                            </td>
                                            <td>
                                                @include('components._progress', [
                                                    'completed' => $event->progress,
                                                    'total' => $event->total,
                                                ])
                                            </td>
                                            <td>
                                                @if ($event->from_date == $event->to_date)
                                                    {{ humanize_date($event->from_date, 'd/m/Y') }}
                                                @else
                                                    {{ humanize_date($event->from_date, 'd/m/Y') }}
                                                    <br>
                                                    {{ humanize_date($event->to_date, 'd/m/Y') }}
                                                @endif
                                            </td>
                                            <td>
                                                <label class="btn btn-sm {{ $event->getStatusClass() }}">
                                                    {{ $event->getStatusText() }}
                                                </label>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">Không có sự kiện đang triển khai.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
            @else
                <div class="col-12 col-xl-6">
                    <section class="dashboard-panel">
                        <div class="dashboard-section-title">
                            <div>
                                <h3>Bảng số lượng checkin theo loại khách mời</h3>
                                <p>So sánh số lượng đăng ký và số lượng đã checkin theo nhóm khách.</p>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table dashboard-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Loại khách mời</th>
                                        <th class="text-center">Đăng ký</th>
                                        <th class="text-center">Checkin</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $types = array_unique(array_merge(
                                            array_keys($registerByType ?? []),
                                            array_keys($checkinByType ?? [])
                                        ));
                                    @endphp

                                    @forelse ($types as $type)
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
                    </section>

                    <section class="dashboard-panel mt-2">
                        <div class="dashboard-section-title">
                            <div>
                                <h3>Bảng số lượng checkout theo loại khách mời</h3>
                                <p>Giúp phát hiện nhóm khách đã checkout ít hơn dự kiến.</p>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table dashboard-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Loại khách mời</th>
                                        <th class="text-center">Đăng ký</th>
                                        <th class="text-center">Checkout</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $checkoutTypes = array_unique(array_merge(
                                            array_keys($registerByType ?? []),
                                            array_keys($checkoutByType ?? [])
                                        ));
                                    @endphp

                                    @forelse ($checkoutTypes as $type)
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
                    </section>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('admin_js')
    @vite([
        'resources/js/admin/dashboard/detail.js',
    ])
    <script src="{{ asset('offlines/offline-js/chart.js') }}"></script>
    <script>
        const month = @json($month);
        const labels = @json($register_sources);
        const registers = @json($registers);

        const barChart = new Chart(document.getElementById('barChart'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    data: registers,
                    backgroundColor: ['#fd7e14', '#0d6efd', '#198754', 'rgba(255,0,0,0.8)'],
                    borderWidth: 1,
                    borderRadius: 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
    @admin()
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const provinceEventData = @json($provinceEventData);
                const totalQuantity = {{ $totalQuantity }};

                new Chart(document.getElementById('pieChartProviceEventData'), {
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
                                '#6c757d',
                            ].slice(0, provinceEventData.length)
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
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
