<a id="offline-offcanvas" href="#offcanvasExample" class="text-xs" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasExample"
    aria-controls="offcanvasExample"
    style="
        position: absolute;
        bottom: 1%;
        right: 1%;
    "
>
    OFFLINE
</a>
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasExample" aria-labelledby="offcanvasExampleLabel"
    data-bs-scroll="true"
    data-bs-backdrop="false"
>
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasExampleLabel">Checkin</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="row">
            <div class="col-md-6 text-xs">
                <div class="">
                    Email: {{ auth()->user()->email }}
                </div>
                <div class="">
                    Username: {{ auth()->user()->username }}
                </div>
                <div class="">
                    Name: {{ auth()->user()->name }}
                </div>
            </div>
            <div class="col-md-6">
                @include('components.form-groups.input-group', [
                    'fieldName'     => "toggle_online",
                    'id'            => "toggle_online",
                    'label'         => 'Online',
                    'type'          => "toggle",
                    'checked'       => 1,
                    'value'         => 1,
                    'labelClass'    => 'form-check-label form-label text-sm',
                    'formClass'     => '',
                    'inputClass'    => 'form-check-input text-sm',
                ])
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-6">
                <div class="fw-bold">
                    Số lượng:
                    <span id="checkins_count" class="text-danger"></span>
                </div>
            </div>
            <div class="col-md-6 text-end">
                <button id="btn-clear-offline-checkins" class="btn btn-sm btn-danger">
                    Reset
                </button>
                <button id="btn-sync-offline-checkins" class="btn btn-sm btn-primary">
                    Đồng bộ
                </button>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="fw-bold mb-2">Chế độ đọc mã</div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <button
                        type="button"
                        class="btn btn-sm btn-outline-secondary"
                        id="btn-scan-mode-keyboard"
                        title="Keyboard wedge"
                    >
                        Keyboard
                    </button>
                    <button
                        type="button"
                        class="btn btn-sm btn-outline-secondary"
                        id="btn-scan-mode-clipboard"
                        title="Clipboard"
                    >
                        Clipboard
                    </button>
                    <button
                        type="button"
                        class="btn btn-sm btn-primary"
                        id="btn-read-clipboard"
                        title="Đọc clipboard ngay"
                    >
                        Đọc clipboard
                    </button>
                    <span class="badge bg-dark align-self-center" id="scan-input-mode-indicator">KB</span>
                </div>
                <div class="text-muted small mt-2">
                    Keyboard wedge giữ focus trên ô quét. Clipboard dùng khi đầu đọc hoặc app đẩy chuỗi qua clipboard.
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <table id="checkins-table" class="text-xs table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>QR Code</th>
                            <th>Scan Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
