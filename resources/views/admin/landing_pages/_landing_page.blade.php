<div class="row">
    <div class="col-md-2">
        <a href="{{ route('admin.language_defines.generate-lang', $event) }}" class="text-sm">
            <x-icon name="rotate"/>
        </a>
    </div>
    <div class="col-md-10 mb-2 text-end">
        <div class="d-flex align-items-center justify-content-end">
            @include('components.form-groups.input-group', [
                'fieldName'     => "show_language_selection",
                'id'            => "show_language_selection",
                'model'         => $model,
                'type'          => "switch",
                'value'         => $model->show_language_selection ?? 0,
                'formClass'     => '',
                'inputClass'    => 'form-check-input text-sm toggle-language-selection',
                'changeUrl'     => route('admin.landing_pages.update-show-language-selection', $model),
            ])
            @include('components.selects.languages.lang', [
                'languages'     => $model->getLanguages(),
                'event'         => $event,
                'edit'          => true,
            ])
        </div>
    </div>
</div>
@include('web.landing_pages.components.subject', [
    'divClass'      => 'h3',
    'id'            => 'register_subject',
    'text'          => $model->getTranslate('register_subject', $languageCode)->translate ?? 'Tiêu đề',
    'edit'          => $languageCode ? true : false,
    'language'      => $model->getLanguageByCode($languageCode),
    'eventId'       => $event->id,
    'model'         => $model,
])
@if (true)
    @include('web.landing_pages.components.subject', [
        'divClass'      => 'h4',
        'text'          => $model->getTranslate('sub_register_subject', $languageCode)->translate ?? "Tiêu đề phụ",
        'id'            => 'sub_register_subject',
        'edit'          => $languageCode ? true : false,
        'language'      => $model->getLanguageByCode($languageCode),
        'eventId'       => $event->id,
        'model'         => $model,
    ])
@endif
{{-- @if (true)
    @include('web.landing_pages.components.custom', [
        'html'      => 'Thông tin tuỳ chỉnh 1',
        'class'     => 'text-center',
    ])
@endif --}}
@if ($event->getEventSetting("ENABLE_FORM", null)->value ?? null)
    <div class="row mb-4">
        @if (($customFieldTemplates && $customFieldTemplates->count()))
            @include('admin.landing_pages.custom_field_templates._list', [
                'event'                 => $event,
                'customFieldTemplates'  => $customFieldTemplates,
                'language'              => $model->getLanguageByCode($languageCode),
                'languageCode'          => $languageCode,
            ])
        @endif
    </div>
    @if ($openCaptcha)
        <div class="row">
            @include('components.form-groups.input-group', [
                'id'                => "g-recaptcha-response",
                'type'              => "recaptcha",
                'formClass'         => 'text-center',
            ])
        </div>
    @endif
@else
    <div class="fst-italic fw-bold">
        Bạn chưa mở form
    </div>
@endif
{{-- <div class="row">
    <div class="col-md-12">
        <textarea id="html"></textarea>
    </div>
</div> --}}
<div class="row pt-4">
    <div class="col-12 text-center">
        @include('web.landing_pages.components.submit', [
            'btnId'         => 'btn_submit-text',
            'btnText'       => $model->getTranslate('btn_submit', $languageCode)->translate ?? 'Đăng ký',
            'btnClass'      => 'btn btn-primary',
            'id'            => 'btn_submit',
            'edit'          => $languageCode ? true : false,
            'model'         => $model,
            'language'      => $model->getLanguageByCode($languageCode),
            'eventId'       => $event->id,
        ])
    </div>
</div>
@include('web.landing_pages.components.html', [
    'divClass'      => 'text-sm',
    'id'            => 'html_behide_submit_btn',
    'text'          => $model->getTranslate('html_behide_submit_btn', $languageCode)->translate ?? 'Nội dung',
    'content'       => $model->getTranslate('html_behide_submit_btn', $languageCode)->translate ?? '',
    'edit'          => $languageCode ? true : false,
    'language'      => $model->getLanguageByCode($languageCode),
    'eventId'       => $event->id,
    'model'         => $model,
])
@include('web.landing_pages.components.credit', [
    // 'logo'          => $event->logoUrl ? $event->logoUrl->getUrl('thumb') : null,
    'logo'          => $event->logoUrl ? $event->logoUrl->getUrl() : null,
    'creditName'    => $model->contact_name,
    'creditPhone'   => $model->contact_phone,
    'creditEmail'   => $model->contact_email,
    'creditAddress' => $model->contact_address,
])

@push('admin_css')
    {!! $model->generateCssFromCustoms() !!}
@endpush

@push('admin_js')
    <script src="https://cdn.tiny.cloud/1/x6ycqq54irgc2638wc0pwmsbj1abzol3eryoncmpjstoikdz/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: 'textarea#html',
            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
        });
    </script>
@endpush
