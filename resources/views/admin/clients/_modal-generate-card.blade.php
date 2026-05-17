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
            <form action="{{ route('admin.clients.generate-card', $client) }}" method="POST">
                @csrf
                @method('POST')
                <div class="modal-body {{ $modalBodyClass ?? '' }}">
                    <input type="hidden" name="client_id" value="{{ $client->id }}">
                    <div class="row">
                        <div class="col-md-6">
                            @include('components.select', [
                                'label'         => "Chọn mẫu thiệp",
                                'id'            => 'card_id',
                                'fieldName'     => 'card_id',
                                'options'       => $cards->pluck('code', 'id')->toArray(),
                                'selected'      => null,
                                'formClass'     => 'form-control ',
                            ])
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                        Đóng
                    </button>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fa-solid fa-paper-plane"></i>
                        Tạo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
