<a href="" class="btn btn-xs btn-primary"
    data-bs-toggle="modal"
    data-bs-target="#{{ $modalId }}"
>
    {!! $textIcon !!}
    {{ $textBtn ?? null }}
</a>

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $modalId }}Label">
                    {{ $text }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form
                action="{{ $route }}"
                method="POST"
                enctype="multipart/form-data"
            >
                @csrf
                <div class="modal-body text-start">
                    <div class="row">
                        @include('components.form-groups.input-group', [
                            'id'        => "file",
                            'fieldName' => "file",
                            'label'     => "Nạp file tại đây <b>(.xlsx)</b>",
                            'value'     => null,
                            'type'      => "file",
                            'accept'    => ".xlsx",
                            'formClass' => 'mb-2'
                        ])
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('common.cancel')</button>
                    <button type="submit" class="btn btn-danger">Xác nhận</button>
                </div>
            </form>
        </div>
    </div>
</div>
