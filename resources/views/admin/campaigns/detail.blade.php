@extends('admin.layouts.templates.page-col', [
    'pageTitle' => $model->isNew() ? "{$event->code} - {$event->name}" : "Campaign {$model->name}",
    'colLeft'   => 'col-md-12',
    'colRight'  => 'd-none',
])

@section('form-action', $model->isNew() ? route('admin.campaigns.store') : route('admin.campaigns.update', $model))
@section('form-back', route('admin.campaigns.index'))

@section('buttons')
    <div class="buttons text-end">
        @if (!$model->isNew())
            <a target="_blank" href="{{ route('admin.events.edit', $event) }}" class="btn btn-primary btn-sm align-self-center mb-lg-0 mb-2">
                <x-icon name="calendar-days"/>
                Sự kiện
            </a>
            <a href="{{ route('admin.clients.index', $event) }}" class="btn btn-sm btn-primary">
                <x-icon name="users"/>
                Danh sách khách mời
            </a>
            <a target="_blank" href="{{ route('admin.email_templates.edit-postmark-template', $model->template_id) }}"
                class="btn btn-sm btn-primary"
            >
                Nội dung mail
            </a>
            @include('components.btn-del-alert', [
                'route'     => route('admin.campaigns.destroy', $model),
                'class'     => 'btn btn-sm btn-danger align-self-center',
                'confirm'   => 'Bạn có chắc chắn muốn xoá campaign này?',
                'text'      => 'Xoá',
                'modalId'   => "campaign-{$model->id}",
            ])
        @else

        @endif

        <a href="" class="btn btn-sm btn-primary align-self-center mb-lg-0 mb-2"
            data-bs-toggle="modal"
            data-bs-target="#selectEventModal"
        >
            <x-icon name="plus-square" prefix="fa-regular"/>
            {{ $model->isNew() ? "Đổi sự kiện" : "Thêm mới" }}
        </a>
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
                    <form action="{{ route('admin.campaigns.select-event-to-create', $event) }}" method="GET">
                        <div class="modal-body text-sm">
                            <div class="row">
                                <div class="col-md-12">
                                    @include('components.select', [
                                        'fieldName'     => 'event_id',
                                        'id'            => 'event_id',
                                        'options'       => $eventArray,
                                        'selected'      => $event->id,
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
    </div>
@endsection

@section('primary-content')
    <div class="bg-light rounded shadow-sm p-2">
        <div class="d-flex align-items-center">
            <h5>
                1. Cập nhật thông tin: &nbsp;
            </h5>
            @include('components.form-groups.input-group', [
                'id'                => "",
                'value'             => $model->name,
                'type'              => "text",
                'formClass'         => 'd-inline w-25',
                'inputClass'        => 'w-100',
            ])
        </div>
        @include('admin/campaigns/_form', [
            'model'                 => $model,
            'event'                 => $event,
            'types'                 => $types,
            'templates'             => $templates,
            'fromEmails'            => $fromEmails,
            'fromNames'             => $fromNames,
        ])
    </div>
@endsection

@if (!$model->isNew())
    @section('table')
        <div class="row">
            <div class="col-md-6">
                <div class="bg-light rounded shadow-sm p-2">
                    <div class="row mb-2">
                        <div class="col-md-4">
                            <h5>
                                2. Chi tiết:
                                <span class="text-danger">
                                    {{ $model->campaign_details->count() }}
                                </span>
                            </h5>
                        </div>
                        <div class="col-md-8 text-end">
                            <form action="{{ route('admin.campaigns.sync-campaign-detail', $model) }}" method="POST" class="form-inline">
                                @csrf
                                <button type="submit" class="btn btn-xs btn-primary btn-submit-form">
                                    <x-icon name="rotate"/>
                                    Đồng bộ danh sách khách mời
                                </button>
                            </form>
                            @if ($model->campaign_details->count())
                                @if ($emailErrors->count() > 0)
                                    <a href="{{ route('admin.emails.export-error-emails', $model) }}" class="btn btn-xs btn-danger ms-1">
                                        <x-icon name="circle-exclamation"/>
                                        Email lỗi
                                    </a>
                                @endif
                                @include('components.btn-alert', [
                                    'route'     => route('admin.campaign_details.send-mail', $model),
                                    'class'     => 'btn btn-xs btn-success ms-1',
                                    'confirm'   => 'Bạn có chắc chắn muốn gửi toàn bộ email trong campaign này? Tiến trình của campaign này (nếu có) sẽ dừng nếu bạn xác nhận tiếp tục gửi',
                                    'text'      => 'Gửi mail',
                                    'icon'      => '<i class="fa-solid fa-paper-plane"></i>',
                                    'modalId'   => "campaign-send-mail-{$model->id}",
                                    'label'     => 'VUI LÒNG NHẬP <b>"SEND"</b> ĐỂ XÁC NHẬN GỬI',
                                ])
                                {{-- <form action="" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success ms-2">
                                        <x-icon name="paper-plane"/>
                                        Gửi mail
                                    </button>
                                </form> --}}
                                {{-- <form action="" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-danger ms-2">
                                        <x-icon name="eraser"/>
                                        Reset
                                    </button>
                                </form> --}}
                            @endif
                        </div>
                    </div>
                    @if (!empty($dataTable))
                        {!! $dataTable->table() !!}
                    @endif
                </div>
            </div>
            <div class="col-md-6">
                <div class="bg-light rounded shadow-sm p-2 h-100">
                    <div class="d-flex justify-content-between">
                        <h5>
                            3. Tiến trình gửi:
                            <span class="text-danger">
                                {{ $emails->count() }}
                            </span>
                        </h5>
                        <div class="">
                            @if ($emails->count())
                                <a href="{{ route('admin.emails.export-report', [
                                        'event'         => $event,
                                        'campaign_id'   => $model->id,
                                    ]) }}"
                                    class="btn btn-xs btn-success"
                                >
                                    <x-icon name="file-excel" prefix="fa-solid"/>
                                    @lang('imports.export')
                                </a>
                                <a href="{{ route('admin.campaigns.history', $model) }}" class="btn btn-xs btn-danger">
                                    <x-icon name="clock-rotate-left"/>
                                    Lịch sử
                                </a>
                            @endif
                            @if ($emailSending->count())
                                @include('components.btn-alert', [
                                    'route'     => route('admin.emails.cancel-by-campaign', $model),
                                    'class'     => 'btn btn-xs btn-danger',
                                    'confirm'   => 'Bạn có chắc chắn muốn dừng tiến trình gửi toàn bộ email trong campaign này?',
                                    'text'      => 'Dừng tiến trình',
                                    'icon'      => '<i class="fa-solid fa-stop"></i>',
                                    'modalId'   => "campaign-cancel-{$model->id}",
                                    'label'     => 'VUI LÒNG NHẬP <b>"STOP"</b> ĐỂ XÁC NHẬN DỪNG',
                                ])
                            @endif
                        </div>
                    </div>
                    @if ($emails->count())
                        <div id="progress">
                            @include('components._progress', [
                                'completed' => $emailCompleted->count(),
                                // 'total'     => $emailSending->count(),
                                'total'     => $emails->count(),
                                'dataTime'  => 3, // giây
                                'dataEle'   => '#progress',
                                'dataUrl'   => route('admin.campaigns.progress', $model),
                            ])
                        </div>
                        <div id="table-send-mail" class="table table-responsive mt-3"
                            data-url="{{ route('admin.campaigns.send-mail-table', $model) }}"
                            data-time="3"
                        >
                            @include('admin.emails.tables._sub-send-mail', [
                                'emails' => $emails
                            ])
                        </div>
                    @else
                        <div class="fst-italic text-sm">
                            Chưa có email
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endsection
@endif

@push('admin_js')
    @if (!empty($dataTable))
        {!! $dataTable->scripts() !!}
    @endif
    @vite([
        'resources/js/admin/campaigns/detail.js'
    ])
@endpush
