
@extends('admin.layouts.templates.page-index', [
    'pageTitle' => "Templates"
])

@section('title')
    Templates: <span class="text-danger">{{ $total ?? 0 }}</span>
@endsection

@section('buttons')
    <div class="buttons">
        @sys_admin
            <a href="{{ route('admin.email_templates.re-sync-postmark-templates') }}" class="">
                <x-icon name="rotate" />
            </a>
        @endsys_admin
        <a href="{{ route('admin.campaigns.index') }}" class="btn btn-sm btn-primary">
            <x-icon name="arrow-left" />
            Campaigns
        </a>
    </div>
@endsection

@section('primary-content')
    <div class="mb-2 d-lg-flex justify-content-between">
        <div class="">
            {{-- <a href=""
                class="btn {{ request()->hasAny([
                    'customer_id',
                    'status',
                    'type',
                    'register_source',
                    'field_date',
                    'from_date',
                    'to_date'
                ]) ? 'btn-outline-warning' : 'btn-warning' }}
                btn-sm align-self-center mb-lg-0 mb-2"
                data-bs-toggle="modal"
                data-bs-target="#filterModal"
            >
                Bộ lọc
                <x-icon name="filter"/>
            </a> --}}
            @include('admin.email_templates._modal-filter', [
                'modalId'       => 'filterModal',
                'title'         => "Bộ lọc",
                'submitBtn'     => "Lọc",
                'model'         => \App\Models\EmailTemplate::getModel(),
                'route'         => route('admin.email_templates.index'),
            ])
        </div>
    </div>
    <div class="row">
        @foreach ($templates as $template)
            @if (isset($template['TemplateId']))
                <div class="col-md-3 mb-3 text-sm">
                    <div
                        {{-- href="{{ route('admin.email_templates.view-postmark-template', $template['TemplateId']) }}" --}}
                        {{-- target="_blank" --}}
                        style="width: 275px !important; height: 275px !important;"
                        class=" border rounded shadow-sm p-3 mx-auto"
                    >
                        <div class="row">
                            <div class="col-md-9">
                                <div class="">
                                    {{ $template['Name'] ?? "UNNAMED" }}
                                </div>
                            </div>
                            <div class="col-md-3 text-end">
                                <a target="_blank" href="{{ route('admin.email_templates.view-postmark-template', $template['TemplateId']) }}" class="">
                                    <x-icon name="up-right-from-square" />
                                </a>
                                <a href="{{ route('admin.email_templates.edit-postmark-template', $template['TemplateId']) }}" class="">
                                    <x-icon name="edit" />
                                </a>
                            </div>
                        </div>
                        @sys_admin
                            <div class="fw-bold">
                                Alias:
                                <span class="fst-italic">
                                    {{ $template['Alias'] }}
                                </span>
                            </div>
                            <div class="fw-bold">
                                Postmark ID:
                                <span id="postmark-template-id-{{ $template['TemplateId'] }}">
                                    {{ $template['TemplateId'] }}
                                </span>
                                @include('components.btn-copy', [
                                    'class'     => '',
                                    'targetId'  => "postmark-template-id-{$template['TemplateId']}"
                                ])
                            </div>
                            <div class="">
                                TemplateType: {{ $template['TemplateType'] }}
                            </div>
                            <div class="">
                                LayoutTemplate: {{ $template['LayoutTemplate'] }}
                            </div>
                        @endsys_admin
                        <div class="fw-bold">
                            Subject: {{ $template['Subject'] ?? null }}
                        </div>
                        <div class="d-none">
                            <input
                                type="hidden"
                                name=""
                                class="input-full-html-body d-none"
                                id="{{ $template['TemplateId'] }}"
                                value="{{ $template['HtmlBody'] }}"
                            >
                        </div>
                        <div class="">
                            <iframe id="{{ $template['TemplateId'] }}" class="html-template-preview"
                                style="
                                    width: 100%;
                                    /* min-height: 100% !important; */
                                    max-height: {{ auth()->user()->isSysAdmin() ? "80" : "140" }}px !important;
                                    overflow-y: visible;
                                    border: none;
                                    "
                            ></iframe>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
@endsection

@push('admin_js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Select all iframes with the class 'html-template-preview'
            const iframes = document.querySelectorAll('iframe.html-template-preview');

            iframes.forEach(iframe => {
                // Get the TemplateId from the iframe's ID
                const templateId = iframe.id;

                // Find the corresponding input element with the matching TemplateId
                const input = document.querySelector(`input.input-full-html-body[id="${templateId}"]`);

                if (input) {
                    // Get the HTML content from the input's value
                    const html = input.value;

                    // Write the HTML content into the iframe
                    const doc = iframe.contentDocument || iframe.contentWindow.document;
                    doc.open();
                    doc.write(html);
                    doc.close();
                }
            });
        });
    </script>
@endpush
