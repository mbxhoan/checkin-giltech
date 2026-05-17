<div class="" id="">
    <form
        action="{{ route('admin.label_details.store') }}"
        id="empty-row"
        class="mb-4"
        method="POST"
    >
        @csrf
        <div class="row">
            <div class="col-md-3 text-sm"></div>
            <div class="col-md-3 text-sm">Loại</div>
            <div class="col-md-4 text-sm">Trường thông tin</div>
        </div>
        <div class="row align-items-center">
            <div class="col-md-3 text-xs">
                Thêm mới thành phần trên mẫu in:
            </div>
            {{-- @dd($labelDetail->getTypes(), $labelDetail::TYPE_FIELD, $cfTemplatesArray, $labelDetail->field) --}}
            <div class="col-md-3">
                @include('components.select', [
                    'label'         => null,
                    'fieldName'     => 'type',
                    'id'            => "type",
                    'options'       => $labelDetail->getTypes(),
                    'selected'      => $labelDetail::TYPE_FIELD,
                    'placeholder'   => null,
                    'formClass'     => 'text-sm w-100',
                ])
            </div>
            <div class="col-md-4">
                @include('components.select', [
                    'label'         => null,
                    'fieldName'     => 'field',
                    'id'            => "field",
                    'options'       => $cfTemplatesArray,
                    'selected'      => $labelDetail->field ?? null,
                    'placeholder'   => null,
                    'formClass'     => 'text-sm w-100',
                ])
            </div>
            @include('components.form-groups.input-group', [
                'id'                => "label_id",
                'value'             => $label->id,
                'type'              => "hidden",
                'formClass'         => 'd-none',
            ])
            <div class="col-md-2">
                <button type="submit" class="btn btn-xs btn-primary">
                    <x-icon name="save" />
                </button>
            </div>
        </div>
    </form>
    @if (($labelDetails && $labelDetails->count()))
        <div class="row">
            <div class="col-md-3 fw-bold text-sm">
                Trường
            </div>
            <div class="col-md-3 fw-bold text-sm">

            </div>
            <div class="col-md-2 fw-bold text-sm">

            </div>
            <div class="col-md-3 fw-bold text-sm">
                {{-- Checkin --}}
            </div>
            <div class="col-md-1"></div>
        </div>
        @foreach ($labelDetails as $labelDetail)
            @php
                $order = $labelDetail->order;
            @endphp
            <div class="to-sort" id="sortable">
                <form action="{{ route('admin.label_details.update', [
                        'label_detail' => $labelDetail
                    ]) }}"
                    id="label-detail-{{ $labelDetail->id }}"
                    class="mb-2 pb-2 px-2 bg-light rounded shadow-sm"
                    method="POST"
                >
                    @method('PUT')
                    @csrf
                    <div class="row pt-2" data-bs-toggle="collapse" href="#collapse-{{ $labelDetail->id }}" role="button" aria-expanded="false" aria-controls="collapse-{{ $labelDetail->id }}">
                        @include('components.form-groups.input-group', [
                            'label'             => null,
                            'id'                => "label-detail-{$labelDetail->id}",
                            'fieldName'         => "field",
                            'value'             => $labelDetail->field,
                            'type'              => "text",
                            'formClass'         => 'mb-2 col-md-3',
                            'inputClass'        => "text-sm edit-change-field w-100",
                            'disabled'          => $labelDetail->is_default ? true : false,
                            'placeholder'       => "Tên",
                            'errorPop'          => false,
                            'readonly'          => true,
                        ])
                        <input type="hidden" name="is_show" value="0">
                        @include('components.form-groups.input-group', [
                            'id'                => "label-detail-{$labelDetail->id}",
                            'fieldName'         => "is_show",
                            'label'             => "Hiển thị",
                            'showLabelTop'      => true,
                            'labelClass'        => 'form-check-label text-sm',
                            'model'             => $labelDetail,
                            'type'              => "switch",
                            'value'             => in_array($labelDetail->status, [
                                $labelDetail::STATUS_ACTIVE
                            ]),
                            'formClass'         => 'mb-0 col-md-3',
                            'inputClass'        => 'form-check-input text-sm edit-change-field',
                        ])
                        @if ($labelDetail->type == $labelDetail::TYPE_IMG)
                            <div class="col">
                                <x-icon name="image" />
                            </div>
                        @endif
                    </div>
                    <div class="collapse" id="collapse-{{ $labelDetail->id }}">
                        <div class="row align-items-center mb-2">
                            @if ($labelDetail->type == $labelDetail::TYPE_FIELD)
                                @include('components.form-groups.input-group', [
                                    'fieldName'     => "color",
                                    'id'            => "label-detail-{$labelDetail->id}",
                                    'label'         => "Màu chữ",
                                    'labelClass'    => 'form-check-label text-sm',
                                    'model'         => $labelDetail,
                                    'type'          => "color",
                                    'value'         => $labelDetail->color ?? "#000000",
                                    'formClass'     => 'mb-0 col-md-3',
                                    'inputClass'    => 'form-control text-sm w-50 edit-change-field',
                                ])
                                <div class="col-md-3">
                                    <input type="hidden" name="bold" value="0">
                                    @include('components.form-groups.input-group', [
                                        'fieldName'     => "bold",
                                        'id'            => "label-detail-{$labelDetail->id}",
                                        'label'         => "<b>In đậm</b>",
                                        'labelClass'    => 'form-check-label text-sm',
                                        'showLabelTop'  => true,
                                        'model'         => $labelDetail,
                                        'type'          => "switch",
                                        'value'         => $labelDetail->bold ?? false,
                                        'formClass'     => 'mb-0',
                                        'inputClass'    => 'form-check-input text-sm edit-change-field',
                                    ])
                                    <input type="hidden" name="italic" value="0">
                                    @include('components.form-groups.input-group', [
                                        'fieldName'     => "italic",
                                        'id'            => "label-detail-{$labelDetail->id}",
                                        'label'         => "<i>In nghiêng</i>",
                                        'labelClass'    => 'form-check-label text-sm',
                                        'showLabelTop'  => true,
                                        'model'         => $labelDetail,
                                        'type'          => "switch",
                                        'value'         => $labelDetail->italic ?? false,
                                        'formClass'     => 'mb-0',
                                        'inputClass'    => 'form-check-input text-sm edit-change-field',
                                    ])
                                    <input type="hidden" name="uppercase" value="0">
                                    @include('components.form-groups.input-group', [
                                        'fieldName'     => "uppercase",
                                        'id'            => "label-detail-{$labelDetail->id}",
                                        'label'         => "IN HOA",
                                        'labelClass'    => 'form-check-label text-sm',
                                        'showLabelTop'  => true,
                                        'model'         => $labelDetail,
                                        'type'          => "switch",
                                        'value'         => $labelDetail->uppercase ?? false,
                                        'formClass'     => 'mb-0',
                                        'inputClass'    => 'form-check-input text-sm edit-change-field',
                                    ])
                                </div>
                            @endif
                        </div>
                        <div class="row mb-2">
                            @if ($labelDetail->type == $labelDetail::TYPE_FIELD)
                                {{-- <div class="col-md-4">
                                    @include('components.select', [
                                        'labelClass'    => 'text-sm',
                                        'label'         => 'Font chữ',
                                        'fieldName'     => "font",
                                        'id'            => "label-detail-{$labelDetail->id}",
                                        'options'       => $fonts,
                                        'selected'      => $labelDetail->font ?? null,
                                        'placeholder'   => null,
                                        'formClass'     => 'text-sm edit-change-field w-100',
                                    ])
                                </div> --}}
                            @endif
                            @if ($labelDetail->type == $labelDetail::TYPE_FIELD)
                                @include('components.form-groups.input-group', [
                                    'fieldName'     => "size",
                                    'id'            => "label-detail-{$labelDetail->id}",
                                    'label'         => "Cỡ chữ (%)",
                                    'labelClass'    => 'form-check-label text-sm',
                                    'model'         => $labelDetail,
                                    'type'          => "number",
                                    'value'         => $labelDetail->size ?? 50,
                                    'formClass'     => 'mb-0 col-md-3',
                                    'inputClass'    => 'text-sm w-100 edit-change-field',
                                ])
                                @include('components.form-groups.input-group', [
                                    'fieldName'     => "width",
                                    'id'            => "label-detail-{$labelDetail->id}",
                                    'label'         => "Chiều rộng (%)",
                                    'labelClass'    => 'form-check-label text-sm',
                                    'model'         => $labelDetail,
                                    'type'          => "number",
                                    'value'         => $labelDetail->width ?? 50,
                                    'formClass'     => 'mb-0 col-md-3',
                                    'inputClass'    => 'text-sm w-100 edit-change-field',
                                ])
                            @else
                                @include('components.form-groups.input-group', [
                                    'fieldName'     => "width",
                                    'id'            => "label-detail-{$labelDetail->id}",
                                    'label'         => "Chiều rộng",
                                    'labelClass'    => 'form-check-label text-sm',
                                    'model'         => $labelDetail,
                                    'type'          => "number",
                                    'value'         => $labelDetail->width ?? 50,
                                    'formClass'     => 'mb-0 col-md-3',
                                    'inputClass'    => 'text-sm w-100 edit-change-field',
                                ])
                                {{-- @include('components.form-groups.input-group', [
                                    'fieldName'     => "height",
                                    'id'            => "label-detail-{$labelDetail->id}",
                                    'label'         => "Chiều cao",
                                    'labelClass'    => 'form-check-label text-sm',
                                    'model'         => $labelDetail,
                                    'type'          => "number",
                                    'value'         => $labelDetail->height ?? 50,
                                    'formClass'     => 'mb-0 col-md-3',
                                    'inputClass'    => 'text-sm w-100 edit-change-field',
                                ]) --}}
                            @endif
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                @include('components.select', [
                                    'labelClass'    => 'text-sm',
                                    'label'         => 'Canh',
                                    'fieldName'     => "h_align",
                                    'id'            => "label-detail-{$labelDetail->id}",
                                    'options'       => $labelDetail->getHAligns(),
                                    'selected'      => $labelDetail->h_align ?? ($labelDetail->type == $labelDetail::TYPE_IMG ? $labelDetail::H_ALIGN_LEFT : null),
                                    'placeholder'   => null,
                                    'formClass'     => 'text-sm edit-change-field w-100',
                                ])
                            </div>
                            @include('components.form-groups.input-group', [
                                'fieldName'     => "pos_x",
                                'id'            => "label-detail-{$labelDetail->id}",
                                'label'         => "Canh ngang",
                                'labelClass'    => 'form-check-label text-sm',
                                'model'         => $labelDetail,
                                'type'          => "number",
                                'value'         => $labelDetail->pos_x ?? 0,
                                'formClass'     => 'mb-0 col-md-3',
                                'inputClass'    => 'text-sm w-100 edit-change-field',
                            ])
                            @include('components.form-groups.input-group', [
                                'fieldName'     => "pos_y",
                                'id'            => "label-detail-{$labelDetail->id}",
                                'label'         => "Canh dọc",
                                'labelClass'    => 'form-check-label text-sm',
                                'model'         => $labelDetail,
                                'type'          => "number",
                                'value'         => $labelDetail->pos_y ?? 0,
                                'formClass'     => 'mb-0 col-md-3',
                                'inputClass'    => 'text-sm w-100 edit-change-field',
                            ])
                        </div>
                    </div>
                </form>
            </div>
        @endforeach
    @endif
</div>

