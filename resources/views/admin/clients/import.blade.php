@extends('admin.layouts.templates.page-store', [
    'pageTitle' => 'Nạp danh sách'
])

@if (!empty($file) && ($file->status == $file::STATUS_IMPORTED) || empty($file))
    @section('form-action', route('admin.clients.upload', $event))
@endif

@section('form-back', route('admin.clients.index', $event))

@section('buttons')
    <div class="buttons">
        <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-primary btn-sm align-self-center">
            <x-icon name="calendar-days"/>
            Sự kiện
        </a>
    </div>
@endsection

@section('primary-content')
    <div class="row">
        <div class="col-md-6 text-sm">
            <div class="">
                <a href="{{ route('admin.clients.export-template-import', $event) }}" class="fst-italic">
                    Tải template
                    <x-icon name="download" />
                </a>
            </div>
            <div class="row">
                @include('components.form-groups.input-group', [
                    'id'        => "file",
                    'model'     => $model,
                    'type'      => "file",
                    'accept'    => ".xlsx",
                    'formClass' => 'mb-3 col-md-6'
                ])
                <div class="col-md-6">
                    @if (session()->has("import_clients_errors_{$model->event_id}"))
                        <a href=""
                            class="btn btn-danger btn-sm align-self-center mb-lg-0 mb-2"
                            data-bs-toggle="modal"
                            data-bs-target="#errorLogFile"
                        >
                            Xem lỗi nạp file
                            <x-icon name="filter"/>
                        </a>
                    @endif
                    @if (!empty($file) && $file->error_log)
                        <a href=""
                            class="btn btn-danger btn-sm align-self-center mb-lg-0 mb-2"
                            data-bs-toggle="modal"
                            data-bs-target="#errorLogFile"
                        >
                            Xem lỗi nạp file
                            <x-icon name="filter"/>
                        </a>
                    @endif
                </div>
            </div>
            @include('components.form-groups.input-group', [
                'id'            => "event_id",
                'fieldName'     => "event_id",
                'value'         => $event->id,
                'type'          => "hidden",
                'formClass'     => 'd-none',
            ])
        </div>
        @if (!empty($file) && ($file->status == $file::STATUS_NEW))
            <div class="col-md-6">
                <h6>
                    Tiến trình tải file
                </h6>
                <div id="progress">
                    @include('components._progress', [
                        'total'     => $file->total_record,
                        'completed' => $file->total_record_before,
                        'dataTime'  => 5, // giây
                        'dataEle'   => '#progress',
                        'dataUrl'   => route('admin.imp_exp_files.progress', [
                            'imp_exp_file' => $file,
                        ]),
                    ])
                </div>
            </div>
            <!-- Modal -->
            <div class="modal fade" id="autoShowModal" tabindex="-1" aria-labelledby="autoShowModalLabel" aria-hidden="true"
                data-bs-backdrop="static"
                data-bs-keyboard="false"
            >
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            {{-- <h1 class="modal-title fs-5" id="autoShowModalLabel">

                            </h1> --}}
                        </div>
                        <div class="modal-body">
                            <div class="fst-italic">
                                <i class="fa-solid fa-spinner fa-spin"></i>
                                Vui lòng chờ trong giây lát...
                            </div>
                        </div>
                        <div class="modal-footer">
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@section('customs')
    {{-- để dưới này là vì đưa lên form thì ko bấm submit được??? --}}
    @if (session()->has("import_clients_errors_{$model->event_id}"))
        @include('components.modal-content', [
            'modalId'           => 'errorLogFile',
            'title'             => "Lỗi nạp file",
            'modalClass'        => 'modal-lg modal-dialog-centered modal-dialog-scrollable',
            'modalBodyClass'    => 'text-sm',
            'content'           => view('components.tables.import-session-errors', [
                'key'           => "import_clients_errors_{$model->event_id}"
            ])->render()
        ])
    @endif
    @if (!empty($file) && $file->error_log)
        @include('components.modal-content', [
            'modalId'           => 'errorLogFile',
            'title'             => "Lỗi nạp file",
            'modalClass'        => 'modal-lg modal-dialog-centered modal-dialog-scrollable',
            'modalBodyClass'    => 'text-sm',
            'content'           => view('components.tables.import-errors', [
                'errors'        => json_decode($file->error_log, true)
            ])->render()
        ])
    @endif
@endsection

@section('table')
    <span class="fw-bold">
        Số lượng khách mời:
        <span class="text-danger">
            {{ $total ?? 0 }}
        </span>
    </span>
    {{ $dataTable->table() }}
@endsection

@push('admin_js')
    {{ $dataTable->scripts() }}
    @vite([
        'resources/js/admin/clients/import.js'
    ])
@endpush
