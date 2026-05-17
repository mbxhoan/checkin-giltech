@extends('admin.layouts.templates.page-col-custom-btn', [
    'pageTitle' => "Chỉnh sửa khách mời",
    'colLeft'   => 'col-md-12',
    'colRight'  => 'd-none',
    'formId'    => 'save-form',
])

@section('form-action', $model->isNew() ? route('admin.clients.store') : route('admin.clients.update', $model))
@section('form-back', route('admin.clients.index', $event))

@section('custom-buttons')
    @if (!empty($label) && $model->isNew())
        <a href="" class="btn btn-primary" id="savePrintBtn">
            <x-icon name="print"/>
            Cập nhật & In ({{ $label->name }})
        </a>
        <div class="" id="print-block">
            {{-- @include('components.label_details.to-print', [
                'label'         => $label,
                'labelDetails'  => $labelDetails,
                'event'         => $event,
                'client'        => $client ?? null,
                'display'       => true,
            ]) --}}
        </div>
    @endif
    @if (!empty($campaigns))
        <a href="" class="btn btn-primary"
            data-bs-toggle="modal"
            data-bs-target="#modalLabelSendMail-{{ $model->id }}"
        >
            <x-icon name="paper-plane"/>
            Gửi mail
        </a>
    @endif
    @if (!empty($cards))
        <a href="" class="btn btn-primary"
            data-bs-toggle="modal"
            data-bs-target="#modalLabelGenCard-{{ $model->id }}"
        >
            <x-icon name="images"/>
            Tạo thiệp
        </a>
    @endif
@endsection

@section('customs')
    @if (!empty($campaigns))
        @include('admin.clients._modal-send-mail', [
            'modalId'           => "modalLabelSendMail-{$model->id}",
            'title'             => "Gửi mail",
            'modalClass'        => 'modal-dialog-scrollable modal-dialog-centered',
            'modalBodyClass'    => 'text-sm',
            'campaigns'         => $campaigns,
            'event'             => $event,
            'client'            => $model,
            'display'           => true,
        ])
    @endif
    @if (!empty($cards))
        @include('admin.clients._modal-generate-card', [
            'modalId'           => "modalLabelGenCard-{$model->id}",
            'title'             => "Tạo thiệp",
            'modalClass'        => 'modal-dialog-scrollable modal-dialog-centered',
            'modalBodyClass'    => 'text-sm',
            'cards'             => $cards,
            'event'             => $event,
            'client'            => $model,
            'display'           => true,
        ])
    @endif
@endsection

@section('buttons')
    <div class="buttons">
        @if (!$model->isNew())
            @if (!empty($labels) && !empty($label))
                <a href="{{ route('admin.clients.create', $event) }}" class="btn btn-primary btn-sm align-self-center mb-lg-0 mb-2">
                    <x-icon name="plus-square" prefix="fa-regular"/>
                    @lang('forms.actions.add')
                </a>
                <a class="btn btn-sm btn-warning"
                    data-bs-toggle="modal"
                    data-bs-target="#modalLabelPrint-{{ $model->id }}"
                >
                    <i class="fa-solid fa-print"></i>
                    In tem
                </a>
                @include('admin.clients._modal-print', [
                    'modalId'           => "modalLabelPrint-{$model->id}",
                    'title'             => "In tem",
                    'modalClass'        => 'modal-dialog-scrollable modal-dialog-centered',
                    'modalBodyClass'    => 'text-sm',
                    'labels'            => $labels,
                    'label'             => $label,
                    'labelDetails'      => $label->label_details->where('status', '!=', "DELETED") ?? null,
                    'event'             => $event,
                    'client'            => $model,
                    'display'           => true,
                ])
            @endif
            @include('components.btn-del-alert', [
                'route'     => route('admin.clients.destroy', $model),
                'class'     => 'btn btn-sm btn-danger align-self-center',
                'confirm'   => 'Bạn có chắc chắn muốn xoá khách hàng này?',
                'text'      => 'Xoá',
                'modalId'   => "client-{$model->id}",
            ])
        @endif
        <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-primary btn-sm align-self-center">
            <x-icon name="calendar-days"/>
            Sự kiện
        </a>
    </div>
@endsection

@section('primary-content')
    @include('admin/clients/_form', [
        'event'                 => $event,
        'model'                 => $model,
        'cfTemplate'            => $cfTemplate,
        'customFieldTemplates'  => $customFieldTemplates,
    ])
@endsection

@if (!$model->isNew())
    @section('table')
        @if (!empty($ticketHistory['orders']) || !empty($ticketHistory['ticket_lines']))
            @include('admin.clients._ticket-history', [
                'ticketHistory' => $ticketHistory,
            ])
        @endif
        <div class="d-lg-flex justify-content-between">
            <h5>
                Đã checkin:
                <span class="text-danger">
                    {{ $totalCheckedIn }}
                </span>
            </h5>
            <div class="">
                @if ($totalCheckedIn && $totalCheckedIn > 0)
                    @include('components.btn-del-alert', [
                        // 'route'         => route('admin.checkins.destroy-by-qrcode', [
                        //     'event'     => $event,
                        //     'qrcode'    => $model->qrcode,
                        // ]),
                        'route'         => route('admin.checkins.destroy-by-client', [
                            'clientId'  => $model->id,
                        ]),
                        'class'         => 'btn btn-sm btn-danger align-self-center',
                        'confirm'       => 'Bạn có chắc chắn reset dữ liệu checkin của khách hàng này?',
                        'text'          => 'Reset',
                        'modalId'       => "checkin-{$model->id}",
                        'redirect'      => url()->current(),
                    ])
                @endif
                <a href="{{ route('admin.checkins.export-check-in-out', [
                        'event'     => $event,
                        'qrcode'    => $model->qrcode,
                    ]) }}" class="btn btn-sm btn-success"
                >
                    <x-icon name="file-excel" prefix="fa-solid"/>
                    Xuất
                </a>
            </div>
        </div>
        @if (!empty($dataTable))
            {!! $dataTable->table() !!}
        @endif
    @endsection
@endif

@push('admin_js')
    @if (!empty($dataTable))
        {!! $dataTable->scripts() !!}
    @endif
    @vite([
        'resources/js/admin/clients/detail.js'
    ])
@endpush
