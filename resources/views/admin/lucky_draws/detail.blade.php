@extends('admin.layouts.templates.page-save', [
    'pageTitle'     => "Chỉnh sửa mẫu in",
    'colLeft'       => 'col-md-6',
    'colRight'      => 'col-md-6 pt-1',
    'buttonsTop'    => true,
    'formId'        => 'formUpdateLabel',
])

@section('form-action', $model->isNew() ? route('admin.lucky_draws.store') : route('admin.lucky_draws.update', $model))
@section('form-back', route('admin.events.edit', $event))

@section('buttons')
    <div class="buttons text-end">
        <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-primary btn-sm">
            <x-icon name="calendar-days"/>
            Sự kiện
        </a>
        <a href="{{ route('admin.clients.index', [
                'event' => $event
            ]) }}"
            class="btn btn-sm btn-primary"
        >
            <x-icon name="users"/>
            Danh sách khách mời
        </a>
        <a href="{{ route('admin.lucky_draws.create', $event) }}" class="btn btn-sm btn-primary">
            <x-icon name="plus-square" prefix="fa-regular"/>
            Thêm mới
        </a>
    </div>
@endsection

@section('primary-content')
    @include('admin/lucky_draws/_form', [
        'event'             => $event,
        'luckyDraws'        => $luckyDraws,
        'model'             => $model,
        'types'             => $types,
    ])
@endsection

@section('customs')
    @if (!$model->isNew())
        <div class="p-2 bg-light rounded shadow-sm">
            <form action="{{ route('admin.lucky_draw_clients.sync', $model) }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <h6>
                            3. Danh sách tham dự:
                            <span class="text-danger">
                                {{ $luckyDrawClients ? $luckyDrawClients->count() : 0 }}
                            </span>
                        </h6>
                    </div>
                    <div class="col-md-6 text-end">
                        <button type="submit" class="btn btn-xs btn-primary btn-submit-form">
                            <x-icon name="rotate"/>
                            Đồng bộ danh sách khách mời
                        </button>
                    </div>
                </div>
                <div class="row">
                    <div class="mb-3 col-md-6">
                        @include('components.select', [
                            'label'         => "Nhóm khách",
                            'id'            => 'client_type',
                            'fieldName'     => 'client_type',
                            'options'       => ["" => "- Tất cả -"] + $types,
                            'selected'      => null,
                        ])
                    </div>
                    <div class="mb-3 col-md-6">
                        @include('components.select', [
                            'label'         => "Lọc",
                            'id'            => 'group',
                            'fieldName'     => 'group',
                            'options'       => $groups ?? [],
                            'selected'      => null,
                        ])
                    </div>
                </div>
            </form>

            <div class="table table-responsive">
                @if (!empty($dataTable))
                    {!! $dataTable->table() !!}
                @endif
            </div>
        </div>
    @endif
@endsection

@section('secondary-content')
    @if (!$model->isNew())
        @php
            $winnersClients = $luckyDrawClients->whereNotNull('reward_id');
        @endphp
        {{-- Danh sách người đã trúng thưởng --}}
        <div class="p-2 bg-light rounded shadow-sm mb-3">
            <div class="row mb-2">
                <div class="col-md-8">
                    <h6 class="mb-0">
                        <x-icon name="trophy" class="text-warning"/>
                        Danh sách trúng thưởng:
                        <span class="badge bg-success">{{ $winnersClients->count() }}</span>
                    </h6>
                </div>
                <div class="col-md-4 text-end">
                    <button id="resetRewardClient" class="btn btn-warning btn-xs" data-lucky-draw-id="{{ $model->id }}">
                        <x-icon name="rotate"/>
                        Reset kết quả quay
                    </button>
                </div>
            </div>
            @if($winnersClients->count() > 0)
                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                    <table class="table table-sm table-striped table-bordered mb-0">
                        <thead class="table-dark sticky-top">
                            <tr>
                                <th class="text-center" style="width: 40px;">#</th>
                                <th>Mã QR</th>
                                <th>Họ tên</th>
                                <th>Giải thưởng</th>
                                <th class="text-center" style="width: 60px;">Huỷ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($winnersClients as $index => $winner)
                                @php
                                    $reward = $luckyDrawRewards->firstWhere('id', $winner->reward_id);
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td><code>{{ $winner->qrcode }}</code></td>
                                    <td>{{ $winner->name }}</td>
                                    <td>
                                        @if($reward)
                                            <span class="badge bg-primary">{{ $reward->order_name ?? $reward->order }}</span>
                                            {{ $reward->name }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-xs btn-outline-danger btn-cancel-winner"
                                            data-id="{{ $winner->reward_id }}"
                                            data-url="{{ route('admin.lucky-draw.cancel-reward') }}"
                                            title="Huỷ giải cho {{ $winner->name }}">
                                            <x-icon name="times"/>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-muted fst-italic">Chưa có ai trúng thưởng</div>
            @endif
        </div>

        {{-- Danh sách giải thưởng --}}
        <div class="p-2 bg-light rounded shadow-sm">
            <div class="row">
                <div class="col-md-5">
                    <h6>
                        4. Danh sách giải:
                        <span class="text-danger">
                            {{ !empty($luckyDrawRewards) ? $luckyDrawRewards->count() : 0 }}
                        </span>
                        <button id="resetButton" class="btn btn-danger btn-xs" data-lucky-draw-id="{{ $model->id }}">
                            <x-icon name="eraser"/>
                            Xoá tất cả giải
                        </button>
                    </h6>
                </div>
                <div class="col-md-7 text-end">
                    <a href="{{ route('admin.lucky_draws.view-raffle', $model) }}" class="btn btn-warning btn-xs" target="_blank">
                        <x-icon name="gift"/>
                        Raffle
                    </a>
                    <a href="{{ route('admin.lucky_draws.export-raffle', $model) }}" class="btn btn-success btn-xs">
                        <x-icon name="file-excel"/>
                        Export
                    </a>
                    @include('admin.lucky_draw_rewards._modal-upsert', [
                        'model'     => null,
                        'modalId'   => 'createRewardModal',
                        'text'      => 'Thêm mới giải thưởng',
                        'textBtn'   => 'Thêm mới',
                        'textIcon'  => '<i class="fa-regular fa-plus-square"></i>',
                        'route'     => route('admin.lucky_draw_rewards.store', $model),
                    ])
                    @include('admin.lucky_draw_rewards._modal-upload', [
                        'modalId'   => 'uploadRewardsModal',
                        'text'      => 'Nạp danh sách giải thưởng',
                        'textBtn'   => 'Nạp file',
                        'textIcon'  => '<i class="fa-solid fa-upload"></i>',
                        'route'     => route('admin.lucky_draw_rewards.upload', $model),
                    ])
                </div>
            </div>
            <div class="">
                @include('admin.lucky_draw_rewards._list', [
                    'luckyDraw'         => $model,
                    'luckyDrawRewards'  => $luckyDrawRewards,
                    'assignees'         => $assignees
                ])
            </div>
        </div>
    @endif
@endsection

@push('admin_js')
    @if (!empty($dataTable))
        {!! $dataTable->scripts() !!}
    @endif
    @vite([
        'resources/js/admin/lucky_draws/detail.js'
    ])
@endpush
