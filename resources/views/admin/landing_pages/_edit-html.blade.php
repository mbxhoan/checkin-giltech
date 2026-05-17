<a id="{{ $id ?? null }}"
    href=""
    class="text-sm edit-detail-landing-page"
    data-bs-toggle="modal"
    data-bs-target="#editHtmlLandingPageDetail-{{ $id }}"
>
    <x-icon name="edit" />
</a>

<div class="modal fade" id="editHtmlLandingPageDetail-{{ $id }}" data-bs-keyboard="true" tabindex="-1" aria-labelledby="editHtmlLandingPageDetail-{{ $id }}Label"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="editHtmlLandingPageDetail-{{ $id }}Label">
                    Chỉnh sửa thông tin
                </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-{{ $id }}" action="{{ route('admin.language_defines.edit-value') }}" method="POST">
                @csrf
                <div class="modal-body text-sm text-start">
                    <div class="">
                        <span class="fw-bold">
                            Ngôn ngữ:
                        </span>
                        {{ $language->description }}
                    </div>
                    @sys_admin
                        <div class="mb-2">
                            <span class="fw-bold">
                                Key:
                            </span>
                            {{ $id }}
                        </div>
                    @endsys_admin
                    <div class="row mb-2">
                        <div class="col-md-12">
                            <textarea id="html" name="value">{!! $content !!}</textarea>
                        </div>
                    </div>
                    <div class="row">
                        @include('components.form-groups.input-group', [
                            'id'                => "event_id",
                            'value'             => $eventId,
                            'type'              => "hidden",
                            'formClass'         => "d-none",
                        ])
                        @include('components.form-groups.input-group', [
                            'id'                => "name",
                            'value'             => $id,
                            'type'              => "hidden",
                            'formClass'         => "d-none",
                        ])
                        @include('components.form-groups.input-group', [
                            'id'                => "language_id",
                            'value'             => $language->id,
                            'type'              => "hidden",
                            'formClass'         => "d-none",
                        ])
                    </div>
                    <div class="row">
                        {{-- hiển thị chữ như thế nào --}}
                        @if (!in_array($id, [
                            'btn_submit',
                            'btn_back',
                            'btn_download',
                        ]))
                            @include('components.form-groups.input-group', [
                                'id'                => "customs.{$model->id}.{$id}.color]",
                                'fieldName'         => "customs[{$model->id}][{$id}][color]",
                                'value'             => $model->getCustomByKey($id, "color"),
                                'type'              => "color",
                                'formClass'         => "mb-3 col-md-3",
                                'label'             => "Màu chữ",
                                'inputClass'        => 'form-control',
                            ])
                            @include('components.form-groups.input-group', [
                                'id'                => "customs.{$model->id}.{$id}.bg_color]",
                                'fieldName'         => "customs[{$model->id}][{$id}][bg_color]",
                                'value'             => $model->getCustomByKey($id, "bg_color"),
                                'type'              => "color",
                                'formClass'         => "mb-3 col-md-3",
                                'label'             => "Màu nền",
                                'inputClass'        => 'form-control',
                            ])
                        @endif
                        <div class="col-md-3">
                            @include('components.select', [
                                'id'            => "customs.{$model->id}.{$id}.font]",
                                'fieldName'     => "customs[{$model->id}][{$id}][font]",
                                'options'       => $model->event->getFonts(),
                                'selected'      => $model->getCustomByKey($id, "font"),
                                'label'         => "Font",
                                'formClass'     => 'w-100',
                            ])
                        </div>
                        @include('components.form-groups.input-group', [
                            'id'                => "customs.{$model->id}.{$id}.font_size]",
                            'fieldName'         => "customs[{$model->id}][{$id}][font_size]",
                            'value'             => $model->getCustomByKey($id, "font_size"),
                            'type'              => "number",
                            'formClass'         => "mb-3 col-md-3",
                            'label'             => "Cỡ chữ",
                            'inputClass'        => 'w-100',
                        ])
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                        @lang('common.close')
                    </button>
                    <button type="submit" class="btn btn-sm btn-primary">
                        Lưu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('admin_js')
    <script src="{{ asset('offlines/offline-js/tinymce.min.js') }}" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: 'textarea#html',
            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
        });
    </script>
@endpush
