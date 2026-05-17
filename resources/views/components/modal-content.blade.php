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
            <form method="GET" action="{{ $route ?? null }}">
                <div class="modal-body {{ $modalBodyClass ?? '' }}">
                    {!! $content ?? '<span class="fst-italic text-xs text-secondary">Đang cập nhật...</span>' !!}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                        Đóng
                    </button>

                    @if (isset($submitBtn))
                        <button type="submit" class="btn btn-sm btn-primary">
                            {{ $submitBtn }}
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
