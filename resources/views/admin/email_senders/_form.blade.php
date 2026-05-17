<div class="row">
    <div class="col-md-8">
        <div class="row">
            @include('components.form-groups.input-group', [
                'id'                => "email",
                'value'             => $sender->EmailAddress,
                'type'              => "text",
                'label'             => "Email",
                'formClass'         => 'mb-3 col-md-6',
                'readonly'          => true
            ])
            @include('components.form-groups.input-group', [
                'id'                => "name",
                'value'             => $sender->Name,
                'type'              => "text",
                'label'             => "Tên người gửi",
                'formClass'         => 'mb-3 col-md-6',
            ])
        </div>
    </div>
</div>
@include('components.form-groups.input-group', [
    'id'                => "id",
    'fieldName'         => "id",
    'value'             => $sender->ID,
    'type'              => "hidden",
    'formClass'         => 'd-none',
])
