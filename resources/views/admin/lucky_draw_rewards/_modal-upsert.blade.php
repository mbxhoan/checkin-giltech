<a href="" class="btn btn-xs btn-primary"
    data-bs-toggle="modal"
    data-bs-target="#{{ $modalId }}"
>
    {!! $textIcon !!}
    {{ $textBtn ?? null }}
</a>

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $modalId }}Label">
                    {{ $text }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form
                action="{{ $route }}"
                method="POST">
                @csrf
                <div class="modal-body text-start">
                    <div class="row">
                        @include('components.form-groups.input-group', [
                            'id'                => "reward.code",
                            'fieldName'         => "reward[code]",
                            'placeholder'       => 'REWARD0001',
                            'label'             => 'Mã giải',
                            'type'              => "text",
                            'formClass'         => 'mb-3 col-md-6',
                            'unique'            => true,
                            'required'          => true,
                            'value'             => $model->code ?? null,
                            'readonly'          => !empty($model->code) ? true : false,
                        ])
                        @include('components.form-groups.input-group', [
                            'id'                => "reward.name",
                            'fieldName'         => "reward[name]",
                            'placeholder'       => 'Tủ lạnh Panasonic/TV LG 8K',
                            'label'             => 'Tên giải',
                            'type'              => "text",
                            'formClass'         => 'mb-3 col-md-6',
                            'required'          => true,
                            'value'             => $model->name ?? null,
                        ])
                    </div>
                    <div class="row">
                        @include('components.form-groups.input-group', [
                            'id'                => "reward.value",
                            'fieldName'         => "reward[value]",
                            'placeholder'       => '25,000,000đ',
                            'label'             => 'Giá trị',
                            'type'              => "text",
                            'formClass'         => 'mb-3 col-md-6',
                            'required'          => true,
                            'value'             => $model->value ?? null,
                        ])
                        @include('components.form-groups.input-group', [
                            'id'                => "reward.img_link",
                            'fieldName'         => "reward[img_link]",
                            'placeholder'       => 'https://cdn.mediamart.vn/images/product/tu-lanh-4-cua-inverter-362l-coex-rm-4007mis-inox-bac_e61f6423.jpg',
                            'label'             => 'Link hình ảnh',
                            'type'              => "text",
                            'formClass'         => 'mb-3 col-md-6',
                            'required'          => true,
                            'value'             => $model->img_link ?? null,
                        ])
                    </div>
                    <div class="row">
                        @include('components.form-groups.input-group', [
                            'id'                => "reward.order",
                            'fieldName'         => "reward[order]",
                            'placeholder'       => 10,
                            'label'             => 'Thứ tự giải',
                            'type'              => "number",
                            'formClass'         => 'mb-3 col-md-6',
                            'unique'            => true,
                            'required'          => true,
                            'value'             => $model->order ?? null,
                        ])
                        @include('components.form-groups.input-group', [
                            'id'                => "reward.order_name",
                            'fieldName'         => "reward[order_name]",
                            'placeholder'       => 'Giải nhất',
                            'label'             => 'Tên cho thứ tự giải',
                            'type'              => "text",
                            'formClass'         => 'mb-3 col-md-6',
                            'required'          => true,
                            'value'             => $model->order_name ?? null,
                        ])
                    </div>
                    <div class="row">
                        @include('components.form-groups.input-group', [
                            'id'                => "reward.time",
                            'fieldName'         => "reward[time]",
                            'placeholder'       => 10,
                            'label'             => 'Thời gian quay (s)',
                            'type'              => "number",
                            'formClass'         => 'mb-3 col-md-6',
                            'required'          => true,
                            'value'             => $model->time ?? null,
                        ])
                        @include('components.form-groups.input-group', [
                            'id'                => "reward.probability",
                            'fieldName'         => "reward[probability]",
                            'placeholder'       => '25',
                            'label'             => 'Xác suất trúng (%)',
                            'type'              => "number",
                            'formClass'         => 'mb-3 col-md-6',
                            'required'          => true,
                            'value'             => $model->probability ?? null,
                        ])
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('common.cancel')</button>
                    <button type="submit" class="btn btn-danger">Lưu</button>
                </div>
            </form>
        </div>
    </div>
</div>
