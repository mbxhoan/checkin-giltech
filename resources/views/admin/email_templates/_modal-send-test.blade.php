<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Gửi mail test</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.email_templates.send-test-postmark-template', $templateId) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-12 text-sm mb-3">
                                @include('components.select', [
                                    'label'         => "Gửi từ",
                                    'fieldName'     => 'from_mail',
                                    'id'            => 'from_mail',
                                    'options'       => [
                                        env('FROM_MAIL')                    => env('FROM_MAIL'),
                                    ],
                                    'selected'      => null,
                                    'required'      => true,
                                ])
                            </div>
                            @include('components.form-groups.input-group', [
                                'id'                => "from_name",
                                'model'             => null,
                                'type'              => "text",
                                'label'             => "Tên người gửi",
                                'formClass'         => 'form-group text-sm mb-3 col-md-6',
                                'placeholder'       => "Tên",
                                'required'          => true,
                            ])
                        </div>
                        <div class="row">
                            @include('components.form-groups.input-group', [
                                'id'                => "to_mail",
                                'model'             => null,
                                'type'              => "email",
                                'label'             => "Email",
                                'formClass'         => 'form-group text-sm mb-3 col-md-12',
                                'placeholder'       => "Email nhận",
                                'required'          => true,
                            ])
                        </div>
                        <div class="row">
                            @include('components.form-groups.input-group', [
                                'id'                => "cc",
                                'model'             => null,
                                'type'              => "email",
                                'label'             => "cc",
                                'formClass'         => 'form-group text-sm mb-3 col-md-6',
                                'placeholder'       => "Email",
                                'required'          => false,
                            ])
                            @include('components.form-groups.input-group', [
                                'id'                => "bcc",
                                'model'             => null,
                                'type'              => "email",
                                'label'             => "bcc",
                                'formClass'         => 'form-group text-sm mb-3 col-md-6',
                                'placeholder'       => "Email",
                                'required'          => false,
                            ])
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <h5>
                                    Thông tin:
                                </h5>
                            </div>
                        </div>
                        @foreach ($placeholders as $index => $placeholder)
                            <div class="row">
                                @include('components.form-groups.input-group', [
                                    'id'                => "fields.$placeholder",
                                    'fieldName'         => "fields[$placeholder]",
                                    'model'             => null,
                                    'type'              => "text",
                                    'formClass'         => 'form-group text-sm mb-3 col-md-12',
                                    'placeholder'       => $placeholder,
                                ])
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">
                        Gửi test
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
