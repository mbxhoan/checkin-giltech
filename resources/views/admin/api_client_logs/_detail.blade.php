<div class="d-flex gap-2 flex-wrap">
    <a href="" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#{{ $requestModalId }}">
        Request
    </a>
    <a href="" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#{{ $responseModalId }}">
        Response
    </a>
</div>

@include('admin.api_client_logs._modal-info', [
    'modalId' => $requestModalId,
    'title' => 'Request',
    'data' => $model->request ?? [],
])

@include('admin.api_client_logs._modal-info', [
    'modalId' => $responseModalId,
    'title' => 'Response',
    'data' => $model->response ?? [],
])
