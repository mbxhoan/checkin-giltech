@extends('admin.layouts.templates.page-save', [
    'pageTitle'     => "Chỉnh sửa mẫu in",
    'colLeft'       => 'col-md-6',
    'colRight'      => 'col-md-6 pt-1',
    'buttonsTop'    => true,
    'formId'        => 'formUpdateLabel',
])

@section('form-action', $model->isNew() ? route('admin.labels.store') : route('admin.labels.update', $model))
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
        <a href="{{ route('admin.labels.create', $event) }}" class="btn btn-sm btn-primary">
            <x-icon name="plus-square" prefix="fa-regular"/>
            Thêm mới
        </a>
    </div>
@endsection

@section('primary-content')
    @include('admin/labels/_form', [
        'event'             => $event,
        'labels'            => $labels,
        'model'             => $model,
        'types'             => $types,
    ])
@endsection

@section('customs')
    @if (!$model->isNew())
        <div class="modal fade" id="cloneLabelModal" tabindex="-1" aria-labelledby="cloneLabelModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="cloneLabelModalLabel">
                            Nhân bản mẫu in
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form
                        action="{{ route('admin.labels.clone', $model) }}"
                        method="POST" class="form-inline">
                        @csrf
                        <div class="modal-body text-start">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    @include('components.select', [
                                        'label'         => "Sự kiện",
                                        'fieldName'     => 'event_id',
                                        'id'            => 'event_id',
                                        'options'       => $eventArray,
                                        'selected'      => $model->event_id,
                                        'placeholder'   => null,
                                        'required'      => true,
                                    ])
                                </div>
                                @include('components.form-groups.input-group', [
                                    'id'                => "name",
                                    'fieldName'         => "name",
                                    'value'             => $model->name,
                                    'label'             => "Thông tin mẫu in mới",
                                    'type'              => "text",
                                    'formClass'         => 'mb-3 col-md-6',
                                ])
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('common.cancel')</button>
                            <button type="submit" class="btn btn-primary">Xác nhận nhân bản</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="px-2">
            <h5>
                3. Thông tin:
            </h5>
        </div>
        <div class="row">
            @include('admin.labels.label_details._list', [
                'event'                 => $event,
                'label'                 => $model,
                'labelDetails'          => $labelDetails,
                'cfTemplatesArray'      => $cfTemplatesArray,
            ])
        </div>
    @endif
@endsection

@section('secondary-content')
    @if (!$model->isNew())
        <div class="bg-light rounded shadow-sm pb-5 mt-2">
            <div class="row">
                <div class="col-md-12" id="backgroundContainer">
                    <input type="hidden" id="url" value="{{ route('admin.labels.render-label', [
                            'label'     => $model,
                            'client_id' => $client->id ?? null,
                        ]) }}"
                    >
                    <div id="printContainer">
                        @include('components.label_details.to-print', [
                            // 'label'                  => $model,
                            // 'event'                 => $event,
                            // 'mainBg'                => $mainBg ?? null,
                            // 'labelDetails'           => $labelDetails->where('status', '!=', $labelDetail::STATUS_DELETED) ?? null,
                            // 'events'            => $events,
                            
                            'clients'           => $clients,
                            'label'             => $model,
                            'labelDetails'      => $labelDetails->where('status', '!=', $labelDetail::STATUS_DELETED) ?? null,
                            'event'             => $event,
                            'client'            => $client ?? null,
                            'display'           => true,
                        ])
                    </div>
                </div>
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
                        </div>
                        <div class="col-md-6 text-end px-4">
                            <a href="" id="btn-multi-print" class="btn btn-danger btn-sm">
                                <x-icon name="print"></x-icon>
                                In toàn bộ
                            </a>
                            <a href="" id="btn-print" class="btn btn-primary btn-sm">
                                <x-icon name="print"></x-icon>
                                In thử
                            </a>
                            {{-- @if ($totalClients->count() > 0)
                                <a
                                    href="{{ route('admin.labels.download-images', $model) }}"
                                    title="Tải xuống"
                                    class="btn btn-primary btn-sm mb-2"
                                >
                                    <x-icon name="download" />
                                    Tải tệp thiệp/thư mời
                                </a>
                            @endif --}}
                        </div>
                    </div>
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
        'resources/js/admin/labels/detail.js'
    ])
@endpush
