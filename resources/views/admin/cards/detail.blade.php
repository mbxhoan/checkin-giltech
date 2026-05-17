@extends('admin.layouts.templates.page-save', [
    'pageTitle'     => "Chỉnh sửa thư/thiệp mời",
    'colLeft'       => 'col-md-6',
    'colRight'      => 'col-md-6 pt-1',
    'buttonsTop'    => true,
])

@section('form-action', $model->isNew() ? route('admin.cards.store') : route('admin.cards.update', $model))
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
        <a href="{{ route('admin.cards.create', $event) }}" class="btn btn-sm btn-primary">
            <x-icon name="plus-square" prefix="fa-regular"/>
            Thêm mới
        </a>
    </div>
@endsection

@section('primary-content')
    @include('admin/cards/_form', [
        'event'             => $event,
        'cards'             => $cards,
        'model'             => $model,
    ])
@endsection

@section('customs')
    @if (!$model->isNew())
        <div class="px-2">
            <h5>
                3. Hiển thị trường thông tin:
                <a href="{{ route('admin.cards.get-full-screen', $model) }}" class="btn btn-xs btn-primary">
                    Xem toàn màn hình
                </a>
            </h5>
        </div>
        <div class="row">
            @include('admin.cards.card_details._list', [
                'event'                 => $event,
                'card'                  => $model,
                'cardDetails'           => $cardDetails,
                'cfTemplatesArray'      => $cfTemplatesArray,
                'fonts'                 => $fonts,
            ])
        </div>
    @endif
@endsection

@section('secondary-content')
    @if (!$model->isNew())
        <div class="bg-light rounded shadow-sm pb-5 mt-2">
            <div class="row">
                @if (!empty($model->backgroundUrl))
                    <div class="col-md-12" id="backgroundContainer">
                        @include('admin.cards._background', [
                            'card'                  => $model,
                            'event'                 => $event,
                            'mainBg'                => $mainBg ?? null,
                            'cardDetails'           => $cardDetails->where('status', '!=', $cardDetail::STATUS_DELETED) ?? null,
                        ])
                    </div>
                @endif
            </div>
            <div class="row mt-2">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-6 px-4">
                            <p class="text-sm fw-bold">
                                Số lượng:
                                <span class="text-danger">
                                    {{ $totalClients->count() }}
                                </span>
                            </p>
                            {{-- <a href="" class="btn btn-sm btn-secondary disabled mb-2">
                                <i class="fa-solid fa-spinner fa-spin-pulse"></i>
                                Loading
                            </a> --}}
                            @if ($generatedClients > 0 && $generatedClients != $totalClients->count())
                            @else
                            @endif
                            {{-- temp --}}
                            @include('components.btn-alert', [
                                'route'     => route('admin.cards.generate', $model),
                                'class'     => 'btn btn-sm mb-2 '.($model->status == $model::STATUS_EDIT ? 'btn-secondary' : 'btn-primary'),
                                'confirm'   => "Bạn có chắc chắn tạo thiệp/thư mời cho {$totalClients->count()} khách này? Tiến trình sẽ bắt đầu và chạy mất một lúc, bạn vui lòng đợi nhé...",
                                'text'      => 'Tạo thiệp/thư mời hàng loạt',
                                'icon'      => '<i class="fa-solid fa-start"></i>',
                                'modalId'   => "card-generate-{$model->id}",
                                'label'     => 'VUI LÒNG NHẬP <b>"OK"</b> ĐỂ XÁC NHẬN GỬI',
                            ])
                        </div>
                        <div class="col-md-6 text-end px-4">
                            <a href="{{ route('admin.cards.download-images', $model) }}"
                                title="Tải xuống"
                                class="btn btn-primary btn-sm mb-2"
                            >
                                <x-icon name="download" />
                                Tải tệp thiệp/thư mời
                            </a>
                            {{-- @if ($totalClients->count() > 0 && $generatedClients == $totalClients->count())
                            @endif --}}
                        </div>
                    </div>
                    @if (in_array($model->status, [
                        $model::STATUS_EDIT,
                        $model::STATUS_INPROCESS,
                        $model::STATUS_COMPLETED,
                    ]))
                        <div class="row my-2">
                            <div class="col-md-12">
                                <div id="progress">
                                    @include('components._progress', [
                                        'completed'     => $generatedClients,
                                        'total'         => $totalClients->count(),
                                        'dataTime'      => 3, // giây
                                        'dataEle'       => '#progress',
                                        'dataUrl'       => route('admin.cards.progress', $model),
                                    ])
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="table table-responsive">
                        @if (!empty($dataTable))
                            {!! $dataTable->table() !!}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('admin_js')
    @if (!empty($dataTable))
        {!! $dataTable->scripts() !!}
    @endif
    @vite([
        'resources/js/admin/cards/detail.js'
    ])
@endpush
