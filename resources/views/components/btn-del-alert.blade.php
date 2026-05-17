<button type="button" class="{{ $class ?? 'btn btn-danger btn-sm' }}" data-bs-toggle="modal" data-bs-target="#{{ $modalId }}Modal">
    <x-icon name="trash" />
    {{ $text ?? null }}
</button>

<!-- Modal Xác nhận Reset -->
<div class="modal fade" id="{{ $modalId }}Modal" tabindex="-1" aria-labelledby="{{ $modalId }}ModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $modalId }}ModalLabel">
                    {{ $alert ?? "Xác nhận" }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form
                action="{{ $route }}"
                method="POST" class="form-inline" data-confirm="{{ $confirm ?? __('forms.common.delete') }}">
                @method('DELETE')
                @csrf
                <div class="modal-body text-start">
                    {{ $confirm }}
                    <div class="row my-2">
                        @include('components.form-groups.input-group', [
                            'id'                => "confirm",
                            'fieldName'         => "confirm",
                            'value'             => '',
                            'label'             => 'VUI LÒNG NHẬP <b>"DELETE"</b> ĐỂ XÁC NHẬN XOÁ',
                            'type'              => "text",
                            'formClass'         => 'mb-3 col-md-12',
                        ])
                    </div>
                    <input type="hidden" name="_redirect" value="{{ $redirect ?? url()->current() }}">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('common.cancel')</button>
                    <button type="submit" class="btn btn-danger">Xác nhận xoá</button>
                </div>
            </form>
        </div>
    </div>
</div>

