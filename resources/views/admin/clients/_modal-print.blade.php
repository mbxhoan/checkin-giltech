<div class="modal fade" id="{{ $modalId }}" data-bs-keyboard="true" tabindex="-1"
    aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog {{ $modalClass ?? 'modal-dialog-centered modal-dialog-scrollable' }} ">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="{{ $modalId }}Label">
                    {{ $title }}
                </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body {{ $modalBodyClass ?? '' }}">
                <div class="row mb-3">
                    <div class="col-md-6">
                        @include('components.select', [
                            'label'         => "Các mẫu tem",
                            'id'            => 'label_id',
                            'fieldName'     => 'label_id',
                            'options'       => $labels->pluck('name', 'id')->toArray(),
                            'selected'      => $label->id,
                            'formClass'     => 'form-control ',
                            'changeUrl'     => route('admin.clients.edit', [
                                'client'    => $client,
                            ])
                        ])
                    </div>
                    @include('components.form-groups.input-group', [
                        'id'                => "client_id",
                        'value'             => $client->id,
                        'type'              => "hidden",
                        'formClass'         => 'd-none',
                    ])
                </div>
                <div id="printContainer">
                    @include('components.label_details.to-print', [
                        'label'         => $label,
                        'labelDetails'  => $labelDetails,
                        'event'         => $event,
                        'client'        => $client,
                        'display'       => true,
                    ])
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                    Đóng
                </button>
                <button type="button" id="btn-print" class="btn btn-sm btn-primary btn-print"
                    data-modal_id="{{ $modalId }}"
                    data-qrcode={{ $client->qrcode }}
                    data-event_code={{ $client->event_code }}
                >
                    <i class="fa-solid fa-print"></i>
                    In tem
                </button>
            </div>
        </div>
    </div>
</div>
