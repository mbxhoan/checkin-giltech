<div class="collapse bg-light rounded shadow-sm p-2" id="collapseDatas">
    <div class="text-sm">
        <form action="{{ route('admin.clients.get-template-qrcodes', $event) }}" method="GET">
            <label>
                1. Template nhập file bao gồm
                <input type="number" id="count" name="count" min="1" max="5000" value="100" placeholder="Số lượng qrcode">
                mã <b>QRCODE</b> có sẵn.
            </label>
            <div class="mb-2">
                <input type="text" id="type" name="type" min="1" max="5000" value="" placeholder="Tên nhóm">
            </div>
            <div class="">
                <button type="submit" class="btn btn-primary btn-xs">
                    <x-icon name="download" />
                    Tải xuống
                </button>
                <button type="button"
                    id="btn-generate-clients"
                    class="btn btn-warning btn-xs"
                    data-confirm="Bạn có chắc chắn muốn tạo mới khách hàng cho sự kiện {{ $event->name }} không?"
                    data-url="{{ route('admin.clients.generate', $event) }}"
                >
                    <x-icon name="plus" />
                    Tạo mới
                </button>
            </div>
        </form>
    </div>

    <div class="text-sm mt-2">
        <form action="{{ route('admin.clients.upload', $event) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row">
                @include('components.form-groups.input-group', [
                    'id'        => "file",
                    'label'     => '2. Nạp file tại đây',
                    'model'     => $event,
                    'type'      => "file",
                    'accept'    => ".xlsx",
                    'formClass' => 'mb-2 col-md-6'
                ])

                @include('components.form-groups.input-group', [
                    'id'        => "event_id",
                    'fieldName' => "event_id",
                    'value'     => $event->id,
                    'type'      => "hidden",
                    'formClass' => 'd-none',
                ])
            </div>

            <div class="row">
                <div class="col-md-6">
                    <button type="submit" class="btn btn-primary btn-xs">
                        <x-icon name="upload"/>
                        Nạp file
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
