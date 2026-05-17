@extends('admin.layouts.templates.page-save', [
    'pageTitle'     => 'Chỉnh sửa nội dung mail '.'"'.$object['Name'].'"',
    'colLeft'       => 'col-md-6',
    'colRight'      => 'col-md-6',
    'buttonsTop'    => true,
    'formId'        => "form-edit-template",
    'btnSubmitId'   => 'btn-submit-email-template',
])

@section('form-action', route('admin.email_templates.update-postmark-template', $object['TemplateId']))
@section('form-back', route('admin.email_templates.index'))

@section('buttons')
    <div class="">
        @sys_admin
            <a href="{{ route('admin.email_templates.sync-postmark-template', $object['TemplateId']) }}">
                <x-icon name="rotate"/>
            </a>
        @endsys_admin
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
            Gửi test
        </button>
    </div>
@endsection

@section('primary-content')
    @include('components.form-groups.input-group', [
        'id'                => "name",
        'model'             => $object['Name'],
        'type'              => "hidden",
        'formClass'         => 'd-none',
    ])
    {{-- @include('components.form-groups.input-group', [
        'id'                => "alias",
        'model'             => $object['Alias'],
        'type'              => "hidden",
        'formClass'         => 'd-none',
    ]) --}}
    @include('components.form-groups.input-group', [
        'id'                => "template_id",
        'model'             => $object['TemplateId'],
        'type'              => "hidden",
        'formClass'         => 'd-none',
    ])
    {{-- @include('components.form-groups.input-group', [
        'id'                => "text_body",
        'model'             => $object['TextBody'],
        'type'              => "hidden",
        'formClass'         => 'd-none',
    ]) --}}
    <div class="row mb-2">
        <div class="col-md-12">
            <textarea id="html" name="html_body">{!! $object['HtmlBody'] !!}</textarea>
        </div>
    </div>
    <div class="row mt-2">
        @include('components.form-groups.input-group', [
            'id'                => "subject",
            'model'             => $object['Subject'] ?? null,
            'type'              => "text",
            'formClass'         => 'mb-3 col-md-6',
            'placeholder'       => "Tiêu đề",
        ])
    </div>
    <div class="row">
        <div class="main-container col-md-12">
			<div
				class="editor-container editor-container_classic-editor editor-container_include-style editor-container_include-fullscreen"
				id="editor-container"
			>
				<div class="editor-container__editor">
                    <div id="editor">

                    </div>
                </div>
			</div>
		</div>
    </div>
    <div class="row mt-4">
        @php
            // $compiledText = str_replace(
            //     ['{{ action_url }}', '{{ invite_sender_name }}', '{{ product_name }}', '{{ help_url }}'],
            //     ['https://example.com/setup', 'John Doe', 'AwesomeApp', 'https://example.com/help'],
            //     $object['TextBody']
            // );
        @endphp
        <div class="col-md-12">
            <h3>
                Text body:
            </h3>
        </div>
        <div class="col-md-12">
            <div class="w-100">
                <pre>{{ $object['TextBody'] }}</pre>
            </div>
        </div>
    </div>
@endsection

@section('secondary-content')
    @include('admin.email_templates._modal-send-test', [
        'placeholders'  => $object['placeholders'],
        'templateId'    => $object['TemplateId'],
    ])
    <iframe id="html-template-preview" style="width: 100%; min-height: 100%; border: none;"></iframe>
@endsection

@push('admin_js')
    <script src="https://cdn.tiny.cloud/1/x6ycqq54irgc2638wc0pwmsbj1abzol3eryoncmpjstoikdz/tinymce/7/tinymce.min.js" referrerpolicy="origin" crossorigin="anonymous"></script>
    {{-- <script src="{{ asset('offlines/offline-js/tinymce.min.js') }}" referrerpolicy="origin" crossorigin="anonymous"></script> --}}
    <script>
        tinymce.init({
            selector: 'textarea#html',
            // referrer_policy: 'origin',
            // content_css_cors: true,
            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
        });
    </script>
@endpush

@push('admin_css')
    <style>
        .template-preview * {
            all: revert;
        }

        /* Optional: re-apply base styles */
        .template-preview body,
        .template-preview p,
        .template-preview h1 {
            font-family: inherit;
            color: inherit;
        }
    </style>
@endpush
