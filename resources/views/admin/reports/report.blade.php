
@extends('admin.layouts.templates.page-index', [
    'pageTitle' => "Báo cáo sự kiện {$event->code}"
])

@section('title')
    Số lượng khách mời: <a href="{{ route('admin.clients.index', $event) }}" class="text-danger">{{ $clients->total() ?? 0 }} <i class="fa-solid fa-users text-sm"></i></a>
@endsection

@section('buttons')
    <div class="buttons">
        @admin
            <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-primary btn-sm align-self-center mb-lg-0 mb-2">
                <x-icon name="calendar-days"/>
                Sự kiện
            </a>
        @endadmin
    </div>
@endsection

@section('primary-content')
    <ul class="nav nav-tabs w-100 d-flex" id="settingsTabs" role="tablist">
        @foreach ([
            'data'          => [
                'title'     => 'Thống kê',
            ],
        ] as $key => $attr)
            <li class="nav-item col px-0" role="presentation">
                <button
                    class="nav-link rounded text-center text-sm fw-bold text-decoration-none text-dark h-100 w-100
                    {{ request()->has('types') ? ($key == 'email' ? 'active' : '') : ($key == 'data' ? 'active' : '') }}
                    "
                    id="{{ $key }}-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#{{ $key }}"
                    type="button"
                    role="tab"
                >
                    {!! $attr['icon'] ?? null !!}&nbsp;{{ $attr['title'] }}
                </button>
            </li>
        @endforeach
        @unless ($showTicketSummary ?? false)
            <li class="nav-item col px-0" role="presentation">
                <button
                    class="nav-link rounded text-center text-sm fw-bold text-decoration-none text-dark h-100 w-100
                    {{ request()->has('types') ? 'active' : '' }}
                    "
                    id="email-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#email"
                    type="button"
                    role="tab"
                >
                    &nbsp;Lịch sử gửi mail
                </button>
            </li>
        @endunless
    </ul>
    <div class="tab-content mt-2" id="settingsTabsContent">
        <div class="tab-pane fade show {{ request()->has('types') ? '' : 'active' }}" id="data" role="tabpanel">
            <div id="report">
                @include('admin.reports._report')
            </div>
        </div>
        @unless ($showTicketSummary ?? false)
            <div class="tab-pane fade show {{ request()->has('types') ? 'active' : '' }}" id="email" role="tabpanel">
                <div id="email">
                    @include('admin.reports._email')
                </div>
            </div>
        @endunless
    </div>
@endsection

@push('admin_css')
    <style>
        #email-history-table tr.email-webhook-collapse.collapse.show {
            display: table-row !important;
        }

        #email-history-table tr.email-webhook-collapse.collapse:not(.show) {
            display: none !important;
        }

        #email-history-table .email-history-toggle {
            min-width: 1.5rem;
        }
    </style>
@endpush

@push('admin_js')
    @vite([
        'resources/js/admin/reports/detail.js'
    ])
    <script>
        document.addEventListener('click', function (event) {
            let row = event.target.closest('#email-history-table tbody tr[data-email-history-row="true"]');
            if (!row) {
                return;
            }

            if (event.target.closest('button, a')) {
                return;
            }

            let toggle = row.querySelector('.email-history-toggle');
            if (toggle) {
                toggle.click();
            }
        });

        document.addEventListener('keyup', function (event) {
            if (!event.target || event.target.id !== 'searchBox') {
                return;
            }

            let input = event.target.value.toLowerCase();

            // Find all main rows in the mail history table
            let mainRows = document.querySelectorAll('#email-history-table tbody tr[data-email-history-row="true"]');

            mainRows.forEach(row => {
                let text = row.textContent.toLowerCase();
                let collapseId = row.getAttribute('data-email-history-target'); // e.g. "#collapseWebhook..."

                // find the collapse row
                let collapseRow = document.querySelector(collapseId);
                let isOpen = collapseRow ? collapseRow.classList.contains('show') : false;

                if (text.includes(input)) {
                    row.style.display = '';
                    if (collapseRow) {
                        collapseRow.style.display = isOpen ? 'table-row' : 'none';
                    }
                } else {
                    row.style.display = 'none';
                    if (collapseRow) collapseRow.style.display = 'none'; // hide paired collapse row
                }
            });
        });
    </script>
    <script>
        window.onload = function () {
            // window.scrollTo(0, document.body.scrollHeight);
        };
        window.onload = function () {
            const urlParams = new URLSearchParams(window.location.search);
            const page = urlParams.get('page');

            if (page !== null) {
                const el = document.getElementById('pagination-links');
                if (el) {
                    el.scrollIntoView({ behavior: 'smooth' });
                }
            }
        };

        function fetchData(page = 1, datas) {
            let name = $('#filter-name').val();

            $.ajax({
                url: "{{ route('admin.clients.data', $event) }}?page=" + page,
                type: "GET",
                data: datas,
                success: function (response) {
                    console.log(response.data);

                    $('#clients-table-body').html(response.data.html);
                    // $('#pagination-links').html(response.data.pagination);
                    // $('#pagination-links').html($(data).find('#pagination-links').html());
                }
            });
        }

        $('.filter-input').on('input change', function () {
            let datas = {};

            $('.filter-input').each(function () {
                const name = $(this).attr('name'); // e.g., custom_fields[company]
                const value = $(this).val();
                datas[name] = value;
            });

            // fetchData(1, datas);
        });

        // Handle pagination links
        // $(document).on('click', '#pagination-links a', function (e) {
        //     e.preventDefault();
        //     let page = $(this).attr('href').split('page=')[1];
        //     fetchData(page);
        // });
    </script>
@endpush
