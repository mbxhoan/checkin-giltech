
@extends('admin.layouts.templates.page-index', [
    'pageTitle' => "Mẫu in"
])

@section('title')
    Mẫu in:
    <span class="text-danger">{{ $total ?? 0 }}</span>
@endsection

@section('buttons')
    <div class="buttons">
        <a href="" class="btn btn-sm btn-primary align-self-center mb-lg-0 mb-2"
            data-bs-toggle="modal"
            data-bs-target="#selectEventModal"
        >
            <x-icon name="plus-square" prefix="fa-regular"/>
            Thêm mới
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
                    <form action="{{ route('admin.labels.select-event-to-create') }}" method="GET">
                        <div class="modal-body text-sm">
                            <div class="row">
                                <div class="col-md-12">
                                    @include('components.select', [
                                        'fieldName'     => 'event_id',
                                        'id'            => 'event_id',
                                        'options'       => $eventArray,
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
    </div>
@endsection

@section('primary-content')
    <div class="mb-2 d-lg-flex justify-content-between">
        <div class="">

        </div>
    </div>

    {{ $dataTable->table() }}
@endsection

@push('admin_js')
    {{ $dataTable->scripts() }}
@endpush
